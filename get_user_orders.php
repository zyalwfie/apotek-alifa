<?php
header('Content-Type: application/json');

ob_start();
ob_clean();

ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

require_once 'connect.php';

try {
    require_once 'auth_functions.php';

    if (!isLoggedIn() || !isAdmin($_SESSION['user_id'])) {
        throw new Exception('Unauthorized access');
    }

    if (!isset($_GET['user_id']) || empty($_GET['user_id'])) {
        throw new Exception('User ID is required');
    }

    $userId = intval($_GET['user_id']);

    $query = "SELECT COUNT(*) as total FROM orders WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $total = $result->fetch_assoc()['total'];

    // $stmt->close();
    // $conn->close();

    echo json_encode([
        'success' => true,
        'total' => $total
    ]);
} catch (Exception $e) {
    error_log("Get user orders error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'total' => 0
    ]);
}

ob_end_flush();
