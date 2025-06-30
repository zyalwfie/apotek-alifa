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
            $total_price += $item['quantity'] * $item['price_at_add'];
        }

        $orderQuery = "INSERT INTO orders (user_id, status, total_price, street_address, recipient_name, recipient_email, recipient_phone, notes, created_at, updated_at) VALUES (?, 'tertunda', ?, ?, ?, ?, ?, ?, NOW(), NOW())";

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
        // $orderStmt->close();

        $orderItemQuery = "INSERT INTO order_items (order_id, product_id, quantity, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())";
        $orderItemStmt = $conn->prepare($orderItemQuery);

        if (!$orderItemStmt) {
            throw new Exception('Failed to prepare order items statement: ' . $conn->error);
        }

        foreach ($cartItems as $item) {
            $orderItemStmt->bind_param("iii", $order_id, $item['product_id'], $item['quantity']);
            if (!$orderItemStmt->execute()) {
                throw new Exception('Failed to insert order item: ' . $orderItemStmt->error);
            }

            $updateStockQuery = "UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?";
            $updateStockStmt = $conn->prepare($updateStockQuery);
            $updateStockStmt->bind_param("iii", $item['quantity'], $item['product_id'], $item['quantity']);

            if (!$updateStockStmt->execute()) {
                throw new Exception('Failed to update stock for product ID: ' . $item['product_id']);
            }

            if ($updateStockStmt->affected_rows === 0) {
                throw new Exception('Insufficient stock for product: ' . $item['name']);
            }

            // $updateStockStmt->close();
        }

        // $orderItemStmt->close();

        $paymentQuery = "INSERT INTO payments (order_id, proof_of_payment, created_at, updated_at) VALUES (?, NULL, NOW(), NOW())";
        $paymentStmt = $conn->prepare($paymentQuery);

        if (!$paymentStmt) {
            throw new Exception('Failed to prepare payment statement: ' . $conn->error);
        }

        $paymentStmt->bind_param("i", $order_id);
        if (!$paymentStmt->execute()) {
            throw new Exception('Failed to create payment record: ' . $paymentStmt->error);
        }
        // $paymentStmt->close();

        if (!clearCart($user_id)) {
            throw new Exception('Failed to clear cart');
        }

        $conn->commit();
        $conn->autocommit(true);
        // $conn->close();

        return [
            'success' => true,
            'message' => 'Pesanan berhasil dibuat!',
            'order_id' => $order_id,
            'total_price' => $total_price
        ];
    } catch (Exception $e) {
        $conn->rollback();
        $conn->autocommit(true);
        // $conn->close();

        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

function getOrderDetails($order_id)
{
    global $conn;
    
    if (!isLoggedIn()) {
        return null;
    }

    $user_id = $_SESSION['user_id'];

    $query = "SELECT o.*, 
                     GROUP_CONCAT(CONCAT(p.name, ' (', oi.quantity, 'x)') SEPARATOR ', ') as items
              FROM orders o
              LEFT JOIN order_items oi ON o.id = oi.order_id
              LEFT JOIN products p ON oi.product_id = p.id
              WHERE o.id = ? AND o.user_id = ?
              GROUP BY o.id";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $order = null;
    if ($result->num_rows > 0) {
        $order = $result->fetch_assoc();
    }

    // $stmt->close();
    // $conn->close();

    return $order;
}

function uploadPaymentProof($order_id, $file)
{
    global $conn;
    
    if (!isLoggedIn()) {
        return ['success' => false, 'message' => 'Silakan login terlebih dahulu!'];
    }

    $user_id = $_SESSION['user_id'];

    try {
        $verifyQuery = "SELECT id FROM orders WHERE id = ? AND user_id = ?";
        $verifyStmt = $conn->prepare($verifyQuery);
        $verifyStmt->bind_param("ii", $order_id, $user_id);
        $verifyStmt->execute();
        $verifyResult = $verifyStmt->get_result();

        if ($verifyResult->num_rows === 0) {
            throw new Exception('Order not found or access denied');
        }
        // $verifyStmt->close();

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

        $updateQuery = "UPDATE payments SET proof_of_payment = ?, updated_at = NOW() WHERE order_id = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("si", $fileName, $order_id);

        if (!$updateStmt->execute()) {
            unlink($uploadPath);
            throw new Exception('Failed to update payment record');
        }

        // $updateStmt->close();

        // $statusQuery = "UPDATE orders SET status = 'berhasil', updated_at = NOW() WHERE id = ?";
        // $statusStmt = $conn->prepare($statusQuery);
        // $statusStmt->bind_param("i", $order_id);
        // $statusStmt->execute();
        // $statusStmt->close();

        // $conn->close();

        return [
            'success' => true,
            'message' => 'Bukti pembayaran berhasil diupload!',
            'filename' => $fileName
        ];
    } catch (Exception $e) {
        // $conn->close();
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

function getUserOrders($user_id, $limit = 10, $offset = 0)
{
    global $conn;

    $query = "SELECT o.*, 
                     COUNT(oi.id) as item_count,
                     p.proof_of_payment
              FROM orders o
              LEFT JOIN order_items oi ON o.id = oi.order_id
              LEFT JOIN payments p ON o.id = p.order_id
              WHERE o.user_id = ?
              GROUP BY o.id
              ORDER BY o.created_at DESC
              LIMIT ? OFFSET ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("iii", $user_id, $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();

    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }

    // $stmt->close();
    // $conn->close();

    return $orders;
}

function getOrderStatus($status)
{
    $statuses = [
        'tertunda' => ['text' => 'Tertunda', 'class' => 'warning', 'icon' => 'clock'],
        'berhasil' => ['text' => 'Berhasil', 'class' => 'info', 'icon' => 'gear'],
        'gagal' => ['text' => 'Dibatalkan', 'class' => 'danger', 'icon' => 'x-circle']
    ];

    return $statuses[$status] ?? ['text' => 'Unknown', 'class' => 'secondary', 'icon' => 'question'];
}
