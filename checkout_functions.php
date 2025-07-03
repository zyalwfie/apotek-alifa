<?php
require_once 'auth_functions.php';
require_once 'cart_functions.php';
require_once 'connect.php';

function createOrder($orderData)
{
    global $conn;

    if (!isLoggedIn()) {
        return ['success' => false, 'message' => 'Silakan login terlebih dahulu!'];
    }

    $user_id = $_SESSION['user_id'];

    try {
        $conn->autocommit(false);

        $cartItems = getCartItems($user_id);
        if (empty($cartItems)) {
            throw new Exception('Keranjang kosong!');
        }

        $total_price = 0;
        foreach ($cartItems as $item) {
            $total_price += $item['kuantitas'] * $item['harga_saat_ditambah'];
        }

        $orderQuery = "INSERT INTO pesanan (id_pengguna, status, harga_total, alamat, nama_penerima, surel_penerima, nomor_telepon_penerima, catatan, waktu_dibuat, waktu_diubah) VALUES (?, 'tertunda', ?, ?, ?, ?, ?, ?, NOW(), NOW())";

        $orderStmt = $conn->prepare($orderQuery);
        if (!$orderStmt) {
            throw new Exception('Failed to prepare order statement: ' . $conn->error);
        }

        $orderStmt->bind_param(
            "iisssss",
            $user_id,
            $total_price,
            $orderData['street_address'],
            $orderData['recipient_name'],
            $orderData['recipient_email'],
            $orderData['recipient_phone'],
            $orderData['notes']
        );

        if (!$orderStmt->execute()) {
            throw new Exception('Failed to create order: ' . $orderStmt->error);
        }

        $order_id = $conn->insert_id;
        $orderStmt->close();

        $orderItemQuery = "INSERT INTO barang_pesanan (id_pesanan, id_obat, kuantitas, waktu_dibuat, waktu_diubah) VALUES (?, ?, ?, NOW(), NOW())";
        $orderItemStmt = $conn->prepare($orderItemQuery);

        if (!$orderItemStmt) {
            throw new Exception('Failed to prepare order items statement: ' . $conn->error);
        }

        foreach ($cartItems as $item) {
            $orderItemStmt->bind_param("iii", $order_id, $item['id_obat'], $item['kuantitas']);
            if (!$orderItemStmt->execute()) {
                throw new Exception('Failed to insert order item: ' . $orderItemStmt->error);
            }

            $updateStockQuery = "UPDATE obat SET stok = stok - ? WHERE id = ? AND stok >= ?";
            $updateStockStmt = $conn->prepare($updateStockQuery);
            $updateStockStmt->bind_param("iii", $item['kuantitas'], $item['id_obat'], $item['kuantitas']);

            if (!$updateStockStmt->execute()) {
                throw new Exception('Failed to update stock for product ID: ' . $item['id_obat']);
            }

            if ($updateStockStmt->affected_rows === 0) {
                throw new Exception('Insufficient stock for product: ' . $item['nama_obat']);
            }
            $updateStockStmt->close();
        }
        $orderItemStmt->close();

        $paymentQuery = "INSERT INTO pembayaran (id_pesanan, bukti_pembayaran, waktu_dibuat, waktu_diubah) VALUES (?, NULL, NOW(), NOW())";
        $paymentStmt = $conn->prepare($paymentQuery);

        if (!$paymentStmt) {
            throw new Exception('Failed to prepare payment statement: ' . $conn->error);
        }

        $paymentStmt->bind_param("i", $order_id);
        if (!$paymentStmt->execute()) {
            throw new Exception('Failed to create payment record: ' . $paymentStmt->error);
        }
        $paymentStmt->close();

        if (!clearCart($user_id)) {
            throw new Exception('Failed to clear cart');
        }

        $conn->commit();
        $conn->autocommit(true);

        return [
            'success' => true,
            'message' => 'Pesanan berhasil dibuat!',
            'order_id' => $order_id,
            'total_price' => $total_price
        ];
    } catch (Exception $e) {
        $conn->rollback();
        $conn->autocommit(true);

        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

function getOrderDetails($order_id)
{
    global $conn;

    try {
        if (!$conn) {
            return null;
        }

        if (isLoggedIn()) {
            $user_id = $_SESSION['user_id'];
            $query = "SELECT * FROM pesanan WHERE id = ? AND id_pengguna = ?";
            $stmt = $conn->prepare($query);

            if (!$stmt) {
                return null;
            }

            $stmt->bind_param("ii", $order_id, $user_id);
        } else {
            $query = "SELECT * FROM pesanan WHERE id = ?";
            $stmt = $conn->prepare($query);

            if (!$stmt) {
                return null;
            }

            $stmt->bind_param("i", $order_id);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $stmt->close();
            return null;
        }

        $order = $result->fetch_assoc();
        $stmt->close();

        return $order;
    } catch (Exception $e) {
        error_log("Error in getOrderDetails: " . $e->getMessage());
        return null;
    }
}

function hasPaymentProof($order_id)
{
    global $conn;

    try {
        if (!$conn) {
            return false;
        }

        $query = "SELECT bukti_pembayaran FROM pembayaran WHERE id_pesanan = ? AND bukti_pembayaran IS NOT NULL AND bukti_pembayaran != '' ORDER BY waktu_dibuat DESC LIMIT 1";
        $stmt = $conn->prepare($query);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $hasProof = $result->num_rows > 0;
        $stmt->close();

        return $hasProof;
    } catch (Exception $e) {
        error_log("Error in hasPaymentProof: " . $e->getMessage());
        return false;
    }
}

function getPaymentDetails($order_id)
{
    global $conn;

    try {
        if (!$conn) {
            return null;
        }

        $query = "SELECT * FROM pembayaran WHERE id_pesanan = ? ORDER BY waktu_dibuat DESC LIMIT 1";
        $stmt = $conn->prepare($query);

        if (!$stmt) {
            return null;
        }

        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $stmt->close();
            return null;
        }

        $payment = $result->fetch_assoc();
        $stmt->close();

        return $payment;
    } catch (Exception $e) {
        error_log("Error in getPaymentDetails: " . $e->getMessage());
        return null;
    }
}

function getOrderItemsSummary($order_id)
{
    global $conn;

    try {
        if (!$conn) {
            return '';
        }

        $query = "SELECT oi.kuantitas, p.nama_obat 
                  FROM barang_pesanan oi 
                  JOIN obat p ON oi.id_obat = p.id 
                  WHERE oi.id_pesanan = ?";

        $stmt = $conn->prepare($query);

        if (!$stmt) {
            return '';
        }

        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row['kuantitas'] . 'x ' . $row['nama_obat'];
        }

        $stmt->close();

        return implode(', ', $items);
    } catch (Exception $e) {
        error_log("Error in getOrderItemsSummary: " . $e->getMessage());
        return '';
    }
}

function uploadPaymentProof($order_id, $file)
{
    global $conn;

    if (!isLoggedIn()) {
        return ['success' => false, 'message' => 'Silakan login terlebih dahulu!'];
    }

    $user_id = $_SESSION['user_id'];

    try {
        $verifyQuery = "SELECT id FROM pesanan WHERE id = ? AND id_pengguna = ?";
        $verifyStmt = $conn->prepare($verifyQuery);
        $verifyStmt->bind_param("ii", $order_id, $user_id);
        $verifyStmt->execute();
        $verifyResult = $verifyStmt->get_result();

        if ($verifyResult->num_rows === 0) {
            $verifyStmt->close();
            throw new Exception('Order not found or access denied');
        }
        $verifyStmt->close();

        $uploadDir = '../../assets/img/payments/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];

        if (!in_array($fileExtension, $allowedExtensions)) {
            throw new Exception('File type not allowed. Please upload JPG, PNG, or PDF');
        }

        if ($file['size'] > 5 * 1024 * 1024) {
            throw new Exception('File size too large. Maximum 5MB allowed');
        }

        $fileName = 'payment_' . $order_id . '_' . time() . '.' . $fileExtension;
        $uploadPath = $uploadDir . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            throw new Exception('Failed to upload file');
        }

        $updateQuery = "UPDATE pembayaran SET bukti_pembayaran = ?, waktu_diubah = NOW() WHERE id_pesanan = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("si", $fileName, $order_id);

        if (!$updateStmt->execute()) {
            unlink($uploadPath);
            $updateStmt->close();
            throw new Exception('Failed to update payment record');
        }
        $updateStmt->close();

        return [
            'success' => true,
            'message' => 'Bukti pembayaran berhasil diupload!',
            'filename' => $fileName
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

function getUserOrders($user_id, $limit = 10, $offset = 0)
{
    global $conn;

    try {
        $query = "SELECT o.*, 
                         COUNT(oi.id) as item_count,
                         p.bukti_pembayaran
                  FROM pesanan o
                  LEFT JOIN barang_pesanan oi ON o.id = oi.id_pesanan
                  LEFT JOIN pembayaran p ON o.id = p.id_pesanan
                  WHERE o.id_pengguna = ?
                  GROUP BY o.id
                  ORDER BY o.waktu_dibuat DESC
                  LIMIT ? OFFSET ?";

        $stmt = $conn->prepare($query);

        if (!$stmt) {
            return [];
        }

        $stmt->bind_param("iii", $user_id, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();

        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }

        $stmt->close();
        return $orders;
    } catch (Exception $e) {
        error_log("Error in getUserOrders: " . $e->getMessage());
        return [];
    }
}

function getOrderStatus($status)
{
    $statuses = [
        'tertunda' => [
            'text' => 'Menunggu Pembayaran',
            'class' => 'warning',
            'icon' => 'clock'
        ],
        'berhasil' => [
            'text' => 'Pembayaran Berhasil',
            'class' => 'success',
            'icon' => 'check-circle'
        ],
        'gagal' => [
            'text' => 'Pembayaran Gagal',
            'class' => 'danger',
            'icon' => 'x-circle'
        ],
        'selesai' => [
            'text' => 'Pesanan Selesai',
            'class' => 'primary',
            'icon' => 'check-all'
        ]
    ];

    return $statuses[$status] ?? [
        'text' => 'Status Tidak Diketahui',
        'class' => 'secondary',
        'icon' => 'question-circle'
    ];
}
