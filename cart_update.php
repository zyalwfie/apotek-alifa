<?php
header('Content-Type: application/json');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    require_once 'cart_functions.php';

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        exit;
    }

    if (!isLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'Silakan login terlebih dahulu!']);
        exit;
    }

    $action = $_POST['action'] ?? '';
    $cart_id = isset($_POST['cart_id']) ? intval($_POST['cart_id']) : 0;

    if ($cart_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID keranjang tidak valid!']);
        exit;
    }

    $result = ['success' => false, 'message' => 'Aksi tidak valid!'];

    switch ($action) {
        case 'increase':
            $cartItems = getCartItems($_SESSION['user_id']);
            $currentItem = null;
            foreach ($cartItems as $item) {
                if ($item['id'] == $cart_id) {
                    $currentItem = $item;
                    break;
                }
            }

            if ($currentItem) {
                $result = updateCartQuantity($cart_id, $currentItem['kuantitas'] + 1);
            } else {
                $result = ['success' => false, 'message' => 'Item tidak ditemukan!'];
            }
            break;

        case 'decrease':
            $cartItems = getCartItems($_SESSION['user_id']);
            $currentItem = null;
            foreach ($cartItems as $item) {
                if ($item['id'] == $cart_id) {
                    $currentItem = $item;
                    break;
                }
            }

            if ($currentItem) {
                $newQuantity = $currentItem['kuantitas'] - 1;
                if ($newQuantity <= 0) {
                    $result = removeFromCart($cart_id);
                } else {
                    $result = updateCartQuantity($cart_id, $newQuantity);
                }
            } else {
                $result = ['success' => false, 'message' => 'Item tidak ditemukan!'];
            }
            break;

        case 'remove':
            $result = removeFromCart($cart_id);
            break;

        case 'update_quantity':
            $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
            $result = updateCartQuantity($cart_id, $quantity);
            break;
    }

    if ($result['success']) {
        $result['cart_count'] = getCartCount();
        $result['cart_total'] = getCartTotal($_SESSION['user_id']);
    }

    echo json_encode($result);
} catch (Exception $e) {
    error_log("Cart update error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()]);
}
