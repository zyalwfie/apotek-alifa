<?php
// cart_functions.php
require_once 'auth_functions.php';

function addToCart($product_id, $quantity = 1)
{
    if (!isLoggedIn()) {
        return ['success' => false, 'message' => 'Silakan login terlebih dahulu!'];
    }

    $conn = connectDB();
    $user_id = $_SESSION['user_id'];

    // Get product details dan harga
    $productQuery = "SELECT * FROM products WHERE id = ?";
    $productStmt = $conn->prepare($productQuery);
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

    // Check if product already in cart
    $checkQuery = "SELECT * FROM carts WHERE user_id = ? AND product_id = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param("ii", $user_id, $product_id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        // Update quantity if already exists
        $existingCart = $checkResult->fetch_assoc();
        $newQuantity = $existingCart['quantity'] + $quantity;

        $updateQuery = "UPDATE carts SET quantity = ?, price_at_add = ? WHERE user_id = ? AND product_id = ?";
        $updateStmt = $conn->prepare($updateQuery);
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
            return ['success' => false, 'message' => 'Gagal memperbarui keranjang!'];
        }
    } else {
        // Add new item to cart
        $insertQuery = "INSERT INTO carts (user_id, product_id, quantity, price_at_add, created_at) VALUES (?, ?, ?, ?, NOW())";
        $insertStmt = $conn->prepare($insertQuery);
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
            return ['success' => false, 'message' => 'Gagal menambahkan produk ke keranjang!'];
        }
    }
}

function getCartItems($user_id)
{
    $conn = connectDB();

    $query = "SELECT c.*, p.name, p.image, p.price as current_price 
              FROM carts c 
              JOIN products p ON c.product_id = p.id 
              WHERE c.user_id = ? 
              ORDER BY c.created_at DESC";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $cartItems = [];
    while ($row = $result->fetch_assoc()) {
        $cartItems[] = $row;
    }

    $stmt->close();
    $conn->close();

    return $cartItems;
}

function updateCartQuantity($cart_id, $quantity)
{
    if (!isLoggedIn()) {
        return ['success' => false, 'message' => 'Silakan login terlebih dahulu!'];
    }

    $conn = connectDB();
    $user_id = $_SESSION['user_id'];

    if ($quantity <= 0) {
        return removeFromCart($cart_id);
    }

    $query = "UPDATE carts SET quantity = ? WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iii", $quantity, $cart_id, $user_id);

    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        return ['success' => true, 'message' => 'Kuantitas berhasil diperbarui!'];
    } else {
        $stmt->close();
        $conn->close();
        return ['success' => false, 'message' => 'Gagal memperbarui kuantitas!'];
    }
}

function removeFromCart($cart_id)
{
    if (!isLoggedIn()) {
        return ['success' => false, 'message' => 'Silakan login terlebih dahulu!'];
    }

    $conn = connectDB();
    $user_id = $_SESSION['user_id'];

    $query = "DELETE FROM carts WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $cart_id, $user_id);

    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        return ['success' => true, 'message' => 'Produk berhasil dihapus dari keranjang!'];
    } else {
        $stmt->close();
        $conn->close();
        return ['success' => false, 'message' => 'Gagal menghapus produk dari keranjang!'];
    }
}

function getCartTotal($user_id)
{
    $conn = connectDB();

    $query = "SELECT SUM(c.quantity * c.price_at_add) as total 
              FROM carts c 
              WHERE c.user_id = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $stmt->close();
    $conn->close();

    return $row['total'] ?? 0;
}

// function getCartCount($user_id = null)
// {
//     if (!isLoggedIn()) {
//         return 0;
//     }

//     if ($user_id === null) {
//         $user_id = $_SESSION['user_id'];
//     }

//     $conn = connectDB();
//     $query = "SELECT SUM(quantity) as total FROM cart WHERE user_id = ?";
//     $stmt = $conn->prepare($query);
//     $stmt->bind_param("i", $user_id);
//     $stmt->execute();
//     $result = $stmt->get_result();
//     $count = $result->fetch_assoc()['total'] ?? 0;

//     $stmt->close();
//     $conn->close();

//     return $count;
// }

function clearCart($user_id)
{
    $conn = connectDB();

    $query = "DELETE FROM carts WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);

    $success = $stmt->execute();
    $stmt->close();
    $conn->close();

    return $success;
}
