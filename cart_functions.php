<?php
require_once 'auth_functions.php';
require_once 'connect.php';

function addToCart($product_id, $quantity = 1)
{
    global $conn;

    try {
        if (!function_exists('isLoggedIn') || !isLoggedIn()) {
            return ['success' => false, 'message' => 'Silakan login terlebih dahulu!'];
        }

        if (!$conn) {
            return ['success' => false, 'message' => 'Gagal koneksi database!'];
        }

        $user_id = $_SESSION['user_id'];

        $productQuery = "SELECT * FROM obat WHERE id = ?";
        $productStmt = $conn->prepare($productQuery);

        if (!$productStmt) {
            return ['success' => false, 'message' => 'Gagal prepare statement: ' . $conn->error];
        }

        $productStmt->bind_param("i", $product_id);
        $productStmt->execute();
        $productResult = $productStmt->get_result();

        if ($productResult->num_rows === 0) {
            return ['success' => false, 'message' => 'Produk tidak ditemukan!'];
        }

        $product = $productResult->fetch_assoc();
        $price_at_add = $product['harga'];

        $checkQuery = "SELECT * FROM keranjang WHERE id_pengguna = ? AND id_obat = ?";
        $checkStmt = $conn->prepare($checkQuery);

        if (!$checkStmt) {
            return ['success' => false, 'message' => 'Gagal prepare check statement: ' . $conn->error];
        }

        $checkStmt->bind_param("ii", $user_id, $product_id);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows > 0) {
            $existingCart = $checkResult->fetch_assoc();
            $newQuantity = $existingCart['kuantitas'] + $quantity;

            $updateQuery = "UPDATE keranjang SET kuantitas = ?, harga_saat_ditambah = ? WHERE id_pengguna = ? AND id_obat = ?";
            $updateStmt = $conn->prepare($updateQuery);

            if (!$updateStmt) {
                return ['success' => false, 'message' => 'Gagal prepare update statement: ' . $conn->error];
            }

            $updateStmt->bind_param("idii", $newQuantity, $price_at_add, $user_id, $product_id);

            if ($updateStmt->execute()) {
                return ['success' => true, 'message' => 'Kuantitas produk di keranjang telah diperbarui!'];
            } else {
                $error = $updateStmt->error;
                return ['success' => false, 'message' => 'Gagal memperbarui keranjang: ' . $error];
            }
        } else {

            $insertQuery = "INSERT INTO keranjang (id_pengguna, id_obat, kuantitas, harga_saat_ditambah, waktu_dibuat) VALUES (?, ?, ?, ?, NOW())";
            $insertStmt = $conn->prepare($insertQuery);

            if (!$insertStmt) {
                return ['success' => false, 'message' => 'Gagal prepare insert statement: ' . $conn->error];
            }

            $insertStmt->bind_param("iiid", $user_id, $product_id, $quantity, $price_at_add);

            if ($insertStmt->execute()) {
                return ['success' => true, 'message' => 'Produk berhasil ditambahkan ke keranjang!'];
            } else {
                $error = $insertStmt->error;
                return ['success' => false, 'message' => 'Gagal menambahkan produk ke keranjang: ' . $error];
            }
        }
    } catch (Exception $e) {
        error_log("Error in addToCart: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

function getCartItems($user_id)
{
    global $conn;

    try {
        if (!$conn) {
            return [];
        }

        $query = "SELECT c.*, p.nama_obat, p.gambar, p.harga as current_price 
                  FROM keranjang c 
                  JOIN obat p ON c.id_obat = p.id 
                  WHERE c.id_pengguna = ? 
                  ORDER BY c.waktu_dibuat DESC";

        $stmt = $conn->prepare($query);
        if (!$stmt) {
            return [];
        }

        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $cartItems = [];
        while ($row = $result->fetch_assoc()) {
            $cartItems[] = $row;
        }

        return $cartItems;
    } catch (Exception $e) {
        error_log("Error in getCartItems: " . $e->getMessage());
        return [];
    }
}

function updateCartQuantity($cart_id, $quantity)
{
    global $conn;

    try {
        if (!function_exists('isLoggedIn') || !isLoggedIn()) {
            return ['success' => false, 'message' => 'Silakan login terlebih dahulu!'];
        }

        $user_id = $_SESSION['user_id'];

        if ($quantity <= 0) {
            return removeFromCart($cart_id);
        }

        $query = "UPDATE keranjang SET kuantitas = ? WHERE id = ? AND id_pengguna = ?";
        $stmt = $conn->prepare($query);

        if (!$stmt) {
            return ['success' => false, 'message' => 'Gagal prepare statement: ' . $conn->error];
        }

        $stmt->bind_param("iii", $quantity, $cart_id, $user_id);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Kuantitas berhasil diperbarui!'];
        } else {
            $error = $stmt->error;
            return ['success' => false, 'message' => 'Gagal memperbarui kuantitas: ' . $error];
        }
    } catch (Exception $e) {
        error_log("Error in updateCartQuantity: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

function removeFromCart($cart_id)
{
    global $conn;

    try {
        if (!function_exists('isLoggedIn') || !isLoggedIn()) {
            return ['success' => false, 'message' => 'Silakan login terlebih dahulu!'];
        }

        $user_id = $_SESSION['user_id'];

        $query = "DELETE FROM keranjang WHERE id = ? AND id_pengguna = ?";
        $stmt = $conn->prepare($query);

        if (!$stmt) {
            return ['success' => false, 'message' => 'Gagal prepare statement: ' . $conn->error];
        }

        $stmt->bind_param("ii", $cart_id, $user_id);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Produk berhasil dihapus dari keranjang!'];
        } else {
            $error = $stmt->error;
            return ['success' => false, 'message' => 'Gagal menghapus produk: ' . $error];
        }
    } catch (Exception $e) {
        error_log("Error in removeFromCart: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

function getCartTotal($user_id)
{
    global $conn;

    try {
        $query = "SELECT SUM(c.kuantitas * c.harga_saat_ditambah) as total 
                  FROM keranjang c 
                  WHERE c.id_pengguna = ?";

        $stmt = $conn->prepare($query);

        if (!$stmt) {
            return 0;
        }

        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        return $row['total'] ?? 0;
    } catch (Exception $e) {
        error_log("Error in getCartTotal: " . $e->getMessage());
        return 0;
    }
}

function getCartCount($user_id = null)
{
    global $conn;

    try {
        if (!function_exists('isLoggedIn') || !isLoggedIn()) {
            return 0;
        }

        if ($user_id === null) {
            $user_id = $_SESSION['user_id'];
        }

        $query = "SELECT SUM(kuantitas) as total FROM keranjang WHERE id_pengguna = ?";
        $stmt = $conn->prepare($query);

        if (!$stmt) {
            return 0;
        }

        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $count = $result->fetch_assoc()['total'] ?? 0;

        return (int)$count;
    } catch (Exception $e) {
        error_log("Error in getCartCount: " . $e->getMessage());
        return 0;
    }
}

function clearCart($user_id)
{
    global $conn;

    try {
        $query = "DELETE FROM keranjang WHERE id_pengguna = ?";
        $stmt = $conn->prepare($query);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $user_id);
        $success = $stmt->execute();

        return $success;
    } catch (Exception $e) {
        error_log("Error in clearCart: " . $e->getMessage());
        return false;
    }
}
