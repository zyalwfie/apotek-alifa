<?php
ob_start();

ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

try {
    ob_clean();

    if (!file_exists('cart_functions.php')) {
        throw new Exception('cart_functions.php not found');
    }

    require_once 'cart_functions.php';

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed');
    }

    if (!function_exists('isLoggedIn') || !isLoggedIn()) {
        echo json_encode([
            'success' => false,
            'message' => 'Silakan login terlebih dahulu!',
            'redirect' => '/apotek-alifa/auth/login.php'
        ]);
        exit;
    }

    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

    if ($product_id <= 0) {
        throw new Exception('ID produk tidak valid!');
    }

    if ($quantity <= 0) {
        throw new Exception('Kuantitas harus lebih dari 0!');
    }

    if (!function_exists('addToCart')) {
        throw new Exception('Function addToCart not found');
    }

    $result = addToCart($product_id, $quantity);

    if ($result['success'] && function_exists('getCartCount')) {
        $result['cart_count'] = getCartCount();
    }

    echo json_encode($result);
} catch (Exception $e) {
    error_log("Add to cart error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug' => [
            'file' => basename($e->getFile()),
            'line' => $e->getLine()
        ]
    ]);
} catch (Error $e) {
    error_log("Fatal error in add to cart: " . $e->getMessage());

    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan sistem',
        'debug' => [
            'error' => $e->getMessage(),
            'file' => basename($e->getFile()),
            'line' => $e->getLine()
        ]
    ]);
}

ob_end_flush();
