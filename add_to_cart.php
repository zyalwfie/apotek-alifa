<?php
// add_to_cart.php

// Prevent any output before JSON
ob_start();

// Disable error display to prevent HTML output
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Set JSON header
header('Content-Type: application/json');

try {
    // Clear any previous output
    ob_clean();

    // Include required files
    if (!file_exists('cart_functions.php')) {
        throw new Exception('cart_functions.php not found');
    }

    require_once 'cart_functions.php';

    // Check request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed');
    }

    // Check if user is logged in
    if (!function_exists('isLoggedIn') || !isLoggedIn()) {
        echo json_encode([
            'success' => false,
            'message' => 'Silakan login terlebih dahulu!',
            'redirect' => '/apotek-alifa/auth/login.php'
        ]);
        exit;
    }

    // Validate input
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

    if ($product_id <= 0) {
        throw new Exception('ID produk tidak valid!');
    }

    if ($quantity <= 0) {
        throw new Exception('Kuantitas harus lebih dari 0!');
    }

    // Check if function exists
    if (!function_exists('addToCart')) {
        throw new Exception('Function addToCart not found');
    }

    // Add to cart
    $result = addToCart($product_id, $quantity);

    // Add cart count if successful
    if ($result['success'] && function_exists('getCartCount')) {
        $result['cart_count'] = getCartCount();
    }

    // Return JSON response
    echo json_encode($result);
} catch (Exception $e) {
    // Log the error
    error_log("Add to cart error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());

    // Return error response
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug' => [
            'file' => basename($e->getFile()),
            'line' => $e->getLine()
        ]
    ]);
} catch (Error $e) {
    // Handle fatal errors
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

// End output buffering and send
ob_end_flush();
