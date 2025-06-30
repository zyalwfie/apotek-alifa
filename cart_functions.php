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

        $productQuery = "SELECT * FROM products WHERE id = ?";
        $productStmt = $conn->prepare($productQuery);

        if (!$productStmt) {
            $conn->close();
            return ['success' => false, 'message' => 'Gagal prepare statement: ' . $conn->error];
        }

        $productStmt->bind_param("i", $product_id);
        $productStmt->execute();
        $productResult = $productStmt->get_result();

        if ($productResult->num_rows === 0) {
            $productStmt->close();
            $conn->close();
            return ['success' => false, 'message' => 'Produk tidak ditemukan!'];
        }

        $product = $productResult->fetch_assoc();
        $price_at_add = $product['price'];

        $checkQuery = "SELECT * FROM carts WHERE user_id = ? AND product_id = ?";
        $checkStmt = $conn->prepare($checkQuery);

        if (!$checkStmt) {
            $productStmt->close();
            $conn->close();
            return ['success' => false, 'message' => 'Gagal prepare check statement: ' . $conn->error];
        }

        $checkStmt->bind_param("ii", $user_id, $product_id);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows > 0) {
            $existingCart = $checkResult->fetch_assoc();
            $newQuantity = $existingCart['quantity'] + $quantity;

            $updateQuery = "UPDATE carts SET quantity = ?, price_at_add = ? WHERE user_id = ? AND product_id = ?";
            $updateStmt = $conn->prepare($updateQuery);

            if (!$updateStmt) {
                $checkStmt->close();
                $productStmt->close();
                $conn->close();
                return ['success' => false, 'message' => 'Gagal prepare update statement: ' . $conn->error];
            }

            $updateStmt->bind_param("idii", $newQuantity, $price_at_add, $user_id, $product_id);

            if ($updateStmt->execute()) {
                $updateStmt->close();
                $checkStmt->close();
                $productStmt->close();
                $conn->close();
                return ['success' => true, 'message' => 'Kuantitas produk di keranjang telah diperbarui!'];
            } else {
                $updateStmt->close();
                $checkStmt->close();
                $productStmt->close();
                $conn->close();
                return ['success' => false, 'message' => 'Gagal memperbarui keranjang: ' . $updateStmt->error];
            }
        } else {
            $insertQuery = "INSERT INTO carts (user_id, product_id, quantity, price_at_add, created_at) VALUES (?, ?, ?, ?, NOW())";
            $insertStmt = $conn->prepare($insertQuery);

            if (!$insertStmt) {
                $checkStmt->close();
                $productStmt->close();
                $conn->close();
                return ['success' => false, 'message' => 'Gagal prepare insert statement: ' . $conn->error];
            }

            $insertStmt->bind_param("iiid", $user_id, $product_id, $quantity, $price_at_add);

            if ($insertStmt->execute()) {
                $insertStmt->close();
                $checkStmt->close();
                $productStmt->close();
                $conn->close();
                return ['success' => true, 'message' => 'Produk berhasil ditambahkan ke keranjang!'];
            } else {
                $insertStmt->close();
                $checkStmt->close();
                $productStmt->close();
                $conn->close();
                return ['success' => false, 'message' => 'Gagal menambahkan produk ke keranjang: ' . $insertStmt->error];
            }
        }
    } catch (Exception $e) {
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

        $query = "SELECT c.*, p.name, p.image, p.price as current_price 
                  FROM carts c 
                  JOIN products p ON c.product_id = p.id 
                  WHERE c.user_id = ? 
                  ORDER BY c.created_at DESC";

        $stmt = $conn->prepare($query);
        if (!$stmt) {
            $conn->close();
            return [];
        }

        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $cartItems = [];
        while ($row = $result->fetch_assoc()) {
            $cartItems[] = $row;
        }

        // $stmt->close();
        // $conn->close();

        return $cartItems;
    } catch (Exception $e) {
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

        $query = "UPDATE carts SET quantity = ? WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iii", $quantity, $cart_id, $user_id);

        if ($stmt->execute()) {
            // $stmt->close();
            // $conn->close();
            return ['success' => true, 'message' => 'Kuantitas berhasil diperbarui!'];
        } else {
            // $stmt->close();
            // $conn->close();
            return ['success' => false, 'message' => 'Gagal memperbarui kuantitas!'];
        }
    } catch (Exception $e) {
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

        $query = "DELETE FROM carts WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $cart_id, $user_id);

        if ($stmt->execute()) {
            // $stmt->close();
            // $conn->close();
            return ['success' => true, 'message' => 'Produk berhasil dihapus dari keranjang!'];
        } else {
            // $stmt->close();
            // $conn->close();
            return ['success' => false, 'message' => 'Gagal menghapus produk dari keranjang!'];
        }
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

function getCartTotal($user_id)
{
    global $conn;
    
    try {
        $query = "SELECT SUM(c.quantity * c.price_at_add) as total 
                  FROM carts c 
                  WHERE c.user_id = ?";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        // $stmt->close();
        // $conn->close();

        return $row['total'] ?? 0;
    } catch (Exception $e) {
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

        $query = "SELECT SUM(quantity) as total FROM carts WHERE user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $count = $result->fetch_assoc()['total'] ?? 0;

        // $stmt->close();
        // $conn->close();

        return $count;
    } catch (Exception $e) {
        return 0;
    }
}

function clearCart($user_id)
{
    global $conn;
    
    try {
        $query = "DELETE FROM carts WHERE user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $user_id);

        $success = $stmt->execute();
        // $stmt->close();
        // $conn->close();

        return $success;
    } catch (Exception $e) {
        return false;
    }
}
