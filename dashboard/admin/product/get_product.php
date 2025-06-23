<?php
header('Content-Type: application/json');

require_once '../../../product_functions.php';
require_once '../../../order_functions.php';

requireAdmin();

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Product ID is required']);
    exit;
}

$product_id = intval($_GET['id']);
$product = getProductById($product_id);

if ($product) {
    $conn = connectDB();
    $query = "SELECT COUNT(*) as total_orders FROM order_items WHERE product_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $order_data = $result->fetch_assoc();
    $product['total_orders'] = $order_data['total_orders'];
    $stmt->close();
    $conn->close();

    echo json_encode(['success' => true, 'product' => $product]);
} else {
    echo json_encode(['success' => false, 'message' => 'Product not found']);
}