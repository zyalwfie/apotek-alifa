<?php
header('Content-Type: application/json');

ob_start();
ob_clean();

ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

try {
    require_once 'auth_functions.php';
    require_once 'user_functions.php';

    if (!isLoggedIn() || !isAdmin($_SESSION['user_id'])) {
        throw new Exception('Unauthorized access');
    }

    $action = isset($_GET['action']) ? $_GET['action'] : '';
    $response = ['success' => false, 'message' => 'Invalid action'];

    switch ($action) {
        case 'add':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Method not allowed');
            }

            $required = ['username', 'email', 'password'];
            foreach ($required as $field) {
                if (empty($_POST[$field])) {
                    throw new Exception("Field '$field' is required");
                }
            }

            $userData = [
                'username' => trim($_POST['username']),
                'email' => trim($_POST['email']),
                'password' => $_POST['password'],
                'full_name' => trim($_POST['full_name'] ?? ''),
                'role' => $_POST['role'] ?? 'user'
            ];

            $result = addUser($userData);
            $response = $result;
            break;

        case 'edit':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Method not allowed');
            }

            $required = ['id', 'username', 'email'];
            foreach ($required as $field) {
                if (empty($_POST[$field])) {
                    throw new Exception("Field '$field' is required");
                }
            }

            $userId = intval($_POST['id']);

            $userData = [
                'username' => trim($_POST['username']),
                'email' => trim($_POST['email']),
                'full_name' => trim($_POST['full_name'] ?? ''),
                'role' => $_POST['role'] ?? 'user'
            ];

            if (!empty($_POST['password'])) {
                $userData['password'] = $_POST['password'];
            }

            $result = updateUser($userId, $userData);
            $response = $result;
            break;

        case 'delete':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Method not allowed');
            }

            if (empty($_POST['id'])) {
                throw new Exception('User ID is required');
            }

            $userId = intval($_POST['id']);

            if ($userId == $_SESSION['user_id']) {
                throw new Exception('Anda tidak dapat menghapus akun sendiri');
            }

            $result = deleteUser($userId);
            $response = $result;
            break;

        default:
            throw new Exception('Invalid action');
    }

    echo json_encode($response);
} catch (Exception $e) {
    error_log("User handler error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

ob_end_flush();
