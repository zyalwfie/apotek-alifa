<?php
header('Content-Type: application/json');

ob_start();
ob_clean();

ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

try {
    require_once 'checkout_functions.php';

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed');
    }

    if (!isLoggedIn()) {
        echo json_encode([
            'success' => false,
            'message' => 'Silakan login terlebih dahulu!',
            'redirect' => '/apotek-alifa/auth/login.php'
        ]);
        exit;
    }

    $required_fields = ['recipient_name', 'recipient_email', 'street_address', 'recipient_phone'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception('Field ' . $field . ' harus diisi!');
        }
    }

    $orderData = [
        'recipient_name' => trim($_POST['recipient_name']),
        'recipient_email' => trim($_POST['recipient_email']),
        'street_address' => trim($_POST['street_address']),
        'recipient_phone' => trim($_POST['recipient_phone']),
        'notes' => trim($_POST['notes'] ?? '')
    ];

    if (!filter_var($orderData['recipient_email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Format email tidak valid!');
    }

    if (!preg_match('/^[0-9+\-\s()]+$/', $orderData['recipient_phone'])) {
        throw new Exception('Format nomor telepon tidak valid!');
    }

    $result = createOrder($orderData);

    if ($result['success']) {
        $_SESSION['last_order_id'] = $result['order_id'];

        echo json_encode([
            'success' => true,
            'message' => $result['message'],
            'order_id' => $result['order_id'],
            'total_price' => $result['total_price'],
            'redirect' => '?page=payments&order_id=' . $result['order_id']
        ]);
    } else {
        echo json_encode($result);
    }
} catch (Exception $e) {
    error_log("Checkout error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

ob_end_flush();
