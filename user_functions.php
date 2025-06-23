<?php
require_once 'auth_functions.php';

function getAllUsersWithPagination($search = '', $role = '', $page = 1, $limit = 5)
{
    $conn = connectDB();

    $offset = ($page - 1) * $limit;
    $conditions = [];
    $params = [];
    $types = '';

    if (!empty($search)) {
        $conditions[] = "(u.username LIKE ? OR u.email LIKE ? OR u.full_name LIKE ?)";
        $searchTerm = "%$search%";
        $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
        $types .= 'sss';
    }

    if (!empty($role)) {
        $conditions[] = "u.role = ?";
        $params[] = $role;
        $types .= 's';
    }

    $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

    $countQuery = "SELECT COUNT(*) as total FROM users u $whereClause";

    if (!empty($params)) {
        $countStmt = $conn->prepare($countQuery);
        $countStmt->bind_param($types, ...$params);
        $countStmt->execute();
        $countResult = $countStmt->get_result();
    } else {
        $countResult = $conn->query($countQuery);
    }

    $totalRows = $countResult->fetch_object()->total;
    if (isset($countStmt)) $countStmt->close();

    $query = "SELECT u.*,
              (SELECT COUNT(*) FROM orders WHERE user_id = u.id) as total_orders,
              (SELECT SUM(total_price) FROM orders WHERE user_id = u.id AND status = 'berhasil') as total_spent
              FROM users u
              $whereClause
              ORDER BY u.created_at DESC
              LIMIT ? OFFSET ?";

    if (!empty($params)) {
        $params[] = $limit;
        $params[] = $offset;
        $types .= 'ii';

        $stmt = $conn->prepare($query);
        $stmt->bind_param($types, ...$params);
    } else {
        $stmt = $conn->prepare($query);
        $stmt->bind_param('ii', $limit, $offset);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }

    $stmt->close();
    $conn->close();

    return [
        'users' => $users,
        'total' => $totalRows,
        'total_pages' => ceil($totalRows / $limit),
        'current_page' => $page,
        'limit' => $limit
    ];
}

function getUserByIdDetailed($user_id)
{
    $conn = connectDB();

    $query = "SELECT u.*,
              (SELECT COUNT(*) FROM orders WHERE user_id = u.id) as total_orders,
              (SELECT COUNT(*) FROM orders WHERE user_id = u.id AND status = 'berhasil') as successful_orders,
              (SELECT SUM(total_price) FROM orders WHERE user_id = u.id AND status = 'berhasil') as total_spent,
              (SELECT MAX(created_at) FROM orders WHERE user_id = u.id) as last_order_date
              FROM users u
              WHERE u.id = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $user = $result->fetch_assoc();

    $stmt->close();
    $conn->close();

    return $user;
}

function deleteUser($user_id, $admin_id)
{
    $conn = connectDB();

    if ($user_id == $admin_id) {
        return ['success' => false, 'message' => 'Anda tidak dapat menghapus akun Anda sendiri'];
    }

    $check_query = "SELECT COUNT(*) as count FROM orders WHERE user_id = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("i", $user_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $count = $result->fetch_object()->count;
    $check_stmt->close();

    if ($count > 0) {
        $conn->close();
        return ['success' => false, 'message' => 'User tidak dapat dihapus karena sudah memiliki pesanan'];
    }

    $query = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $success = $stmt->execute();
    $stmt->close();

    $conn->close();

    return ['success' => $success, 'message' => $success ? 'User berhasil dihapus' : 'Gagal menghapus user'];
}

function buildUserPaginationUrl($page, $search = '', $role = '')
{
    $params = ['page' => 'user.index', 'p' => $page];
    if (!empty($search)) {
        $params['search'] = $search;
    }
    if (!empty($role)) {
        $params['role'] = $role;
    }
    return '?' . http_build_query($params);
}
