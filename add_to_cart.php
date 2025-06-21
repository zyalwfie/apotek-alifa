<?php
// add_to_cart.php
header('Content-Type: application/json');
require 'cart_functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Silakan login terlebih dahulu!', 'redirect' => '/apotek-alifa/auth/login.php']);
    exit;
}

$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID produk tidak valid!']);
    exit;
}

if ($quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Kuantitas harus lebih dari 0!']);
    exit;
}

$result = addToCart($product_id, $quantity);

if ($result['success']) {
    $result['cart_count'] = getCartCount();
}

echo json_encode($result);
