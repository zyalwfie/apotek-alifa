<?php
header('Content-Type: application/json');

require_once '../../product_functions.php';
require_once '../../order_functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$response = ['success' => false, 'message' => ''];

if (isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'add':
            $data = [
                'name' => $_POST['name'],
                'description' => $_POST['description'],
                'price' => $_POST['price'],
                'stock' => $_POST['stock'],
                'category_id' => $_POST['category_id'],
                'sku' => $_POST['sku']
            ];

            $result = addProduct($data, $_FILES['image'] ?? null);
            $response['success'] = $result !== false;
            $response['message'] = $response['success'] ? 'Produk berhasil ditambahkan' : 'Gagal menambahkan produk';
            break;

        case 'update':
            $data = [
                'name' => $_POST['name'],
                'description' => $_POST['description'],
                'price' => $_POST['price'],
                'stock' => $_POST['stock'],
                'category_id' => $_POST['category_id'],
                'sku' => $_POST['sku']
            ];

            $result = updateProduct($_POST['product_id'], $data, $_FILES['image'] ?? null);
            $response['success'] = $result;
            $response['message'] = $response['success'] ? 'Produk berhasil diperbarui' : 'Gagal memperbarui produk';
            break;

        case 'delete':
            $result = deleteProduct($_POST['product_id']);
            $response = $result;
            break;

        default:
            $response['message'] = 'Invalid action';
            break;
    }
}

echo json_encode($response);
exit;
