<?php
header('Content-Type: application/json');

require_once '../../user_functions.php';

requireAdmin();

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'User ID is required']);
    exit;
}

$user_id = intval($_GET['id']);
$user = getUserByIdDetailed($user_id);

if ($user) {
    echo json_encode(['success' => true, 'user' => $user]);
} else {
    echo json_encode(['success' => false, 'message' => 'User not found']);
}
