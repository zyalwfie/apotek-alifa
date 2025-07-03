<?php
header('Content-Type: application/json');

while (ob_get_level()) {
    ob_end_clean();
}
ob_start();

ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

try {
    if (!file_exists('connect.php')) {
        throw new Exception('connect.php not found');
    }
    require_once 'connect.php';

    if (!file_exists('auth_functions.php')) {
        throw new Exception('auth_functions.php not found');
    }
    require_once 'auth_functions.php';

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
        throw new Exception('User not logged in');
    }

    if ($_SESSION['user_role'] !== 'admin') {
        throw new Exception('Unauthorized access - Admin required');
    }

    if (!isset($_GET['product_id']) || empty($_GET['product_id'])) {
        throw new Exception('Product ID is required');
    }

    $productId = intval($_GET['product_id']);

    if ($productId <= 0) {
        throw new Exception('Invalid Product ID');
    }

    if (!$conn) {
        throw new Exception('Database connection failed');
    }

    $query = "SELECT SUM(bp.kuantitas) as total_sold 
              FROM barang_pesanan bp 
              JOIN pesanan p ON bp.id_pesanan = p.id 
              WHERE bp.id_obat = ? AND p.status IN ('berhasil', 'selesai')";

    $stmt = $conn->prepare($query);

    if (!$stmt) {
        throw new Exception('Failed to prepare statement: ' . $conn->error);
    }

    $stmt->bind_param("i", $productId);

    if (!$stmt->execute()) {
        throw new Exception('Failed to execute query: ' . $stmt->error);
    }

    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $totalSold = $row ? (int)$row['total_sold'] : 0;

    $stmt->close();

    ob_clean();
    echo json_encode([
        'success' => true,
        'total' => $totalSold
    ]);
} catch (Exception $e) {
    error_log("Get product orders error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());

    ob_clean();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'total' => 0
    ]);
} catch (Error $e) {
    error_log("Fatal error in get product orders: " . $e->getMessage());

    ob_clean();
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error',
        'total' => 0
    ]);
}

ob_end_flush();
