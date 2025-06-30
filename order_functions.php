<?php
require_once 'auth_functions.php';
require_once 'connect.php';

function getUserPendingOrdersWithPagination($user_id, $search = '', $page = 1, $limit = 5)
{
    global $conn;

    $offset = ($page - 1) * $limit;

    $pendingStatus = 'tertunda';
    $conditions = ['o.user_id = ?', 'o.status = ?'];
    $params = [$user_id, $pendingStatus];
    $types = 'is';

    if (!empty($search)) {
        $conditions[] = "(o.recipient_name LIKE ? OR o.recipient_email LIKE ? OR o.id LIKE ?)";
        $searchTerm = "%$search%";
        $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
        $types .= 'sss';
    }

    $whereClause = implode(' AND ', $conditions);

    $countQuery = "SELECT COUNT(*) as total 
                   FROM orders o 
                   WHERE $whereClause";

    $countStmt = $conn->prepare($countQuery);
    $countStmt->bind_param($types, ...$params);
    $countStmt->execute();
    $countResult = $countStmt->get_result();
    $totalRows = $countResult->fetch_object()->total;

    $query = "SELECT o.id, o.user_id, o.status, o.total_price, o.street_address,
                     o.recipient_name, o.recipient_email, o.recipient_phone,
                     o.notes, o.created_at, o.updated_at,
                     COUNT(oi.id) as item_count,
                     MAX(p.proof_of_payment) as proof_of_payment,
                     GROUP_CONCAT(CONCAT(pr.name, ' (', oi.quantity, 'x)') SEPARATOR ', ') as items_detail
              FROM orders o
              LEFT JOIN order_items oi ON o.id = oi.order_id
              LEFT JOIN products pr ON oi.product_id = pr.id
              LEFT JOIN payments p ON o.id = p.order_id
              WHERE $whereClause
              GROUP BY o.id
              ORDER BY o.created_at DESC
              LIMIT ? OFFSET ?";

    $params[] = $limit;
    $params[] = $offset;
    $types .= 'ii';

    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }

    return [
        'orders' => $orders,
        'total' => $totalRows,
        'total_pages' => ceil($totalRows / $limit),
        'current_page' => (int)$page,
        'limit' => (int)$limit
    ];
}

function getUserOrderHistoryWithPagination($user_id, $search = '', $page = 1, $limit = 5)
{
    global $conn;
    
    $offset = ($page - 1) * $limit;

    $conditions = ['o.user_id = ?'];
    $params = [$user_id];
    $types = 'i';

    if (!empty($search)) {
        $conditions[] = "(o.recipient_name LIKE ? OR o.recipient_email LIKE ? OR o.order_id LIKE ?)";
        $searchTerm = "%$search%";
        $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
        $types .= 'sss';
    }

    $whereClause = implode(' AND ', $conditions);

    $countQuery = "SELECT COUNT(*) as total 
                   FROM order_histories o 
                   WHERE $whereClause";

    $countStmt = $conn->prepare($countQuery);
    $countStmt->bind_param($types, ...$params);
    $countStmt->execute();
    $countResult = $countStmt->get_result();
    $totalRows = $countResult->fetch_object()->total;
    $countStmt->close();


    $query = "SELECT o.id, o.order_id, o.user_id, o.status_at_approval,
                     o.total_price, o.street_address,
                     o.recipient_name, o.recipient_email, o.recipient_phone,
                     o.notes, o.order_created_at, o.approved_at,
                     o.items_snapshot,
                     JSON_LENGTH(o.items_snapshot) as item_count
              FROM order_histories o
              WHERE $whereClause
              ORDER BY o.approved_at DESC
              LIMIT ? OFFSET ?";

    $params[] = $limit;
    $params[] = $offset;
    $types .= 'ii';

    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        error_log('Prepare statement gagal untuk getUserOrderHistory: ' . $conn->error);
        return false;
    }

    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $row['items_snapshot'] = json_decode($row['items_snapshot'], true);
        $orders[] = $row;
    }
    $stmt->close();

    return [
        'orders' => $orders,
        'total' => (int)$totalRows,
        'total_pages' => ceil($totalRows / $limit),
        'current_page' => (int)$page,
        'limit' => (int)$limit
    ];
}

function getOrderStatusBadge($status)
{
    $statuses = [
        'tertunda' => ['text' => 'Tertunda', 'class' => 'warning', 'icon' => 'clock'],
        'berhasil' => ['text' => 'Berhasil', 'class' => 'success', 'icon' => 'check'],
        'gagal' => ['text' => 'Dibatalkan', 'class' => 'danger', 'icon' => 'x-circle'],
    ];

    return $statuses[$status] ?? ['text' => 'Unknown', 'class' => 'secondary', 'icon' => 'question'];
}

function buildOrderPaginationUrl($page, $search = '', $status = '')
{
    $params = ['page' => 'order.index', 'p' => $page];
    if (!empty($search)) {
        $params['search'] = $search;
    }
    if (!empty($status)) {
        $params['status'] = $status;
    }
    return '?' . http_build_query($params);
}

function formatOrderDate($date)
{
    return date('d M Y, H:i', strtotime($date));
}

function getOrderById($order_id, $user_id)
{
    global $conn;

    $query = "SELECT o.id,
                     o.user_id,
                     o.status,
                     o.total_price,
                     o.street_address,
                     o.recipient_name,
                     o.recipient_email,
                     o.recipient_phone,
                     o.notes,
                     o.created_at,
                     o.updated_at,
                     MAX(p.proof_of_payment) as proof_of_payment,
                     GROUP_CONCAT(CONCAT(pr.name, ' (', oi.quantity, 'x) - Rp', FORMAT(pr.price * oi.quantity, 0)) SEPARATOR '<br>') as items_detail
              FROM orders o
              LEFT JOIN order_items oi ON o.id = oi.order_id
              LEFT JOIN products pr ON oi.product_id = pr.id
              LEFT JOIN payments p ON o.id = p.order_id
              WHERE o.id = ? AND o.user_id = ?
              GROUP BY o.id, o.user_id, o.status, o.total_price, o.street_address, 
                       o.recipient_name, o.recipient_email, o.recipient_phone, 
                       o.notes, o.created_at, o.updated_at";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $order = null;
    if ($result->num_rows > 0) {
        $order = $result->fetch_assoc();
    }


    return $order;
}

function getAllOrdersWithPagination($search = '', $status = '', $page = 1, $limit = 10)
{
    global $conn;

    $offset = ($page - 1) * $limit;
    $conditions = [];
    $params = [];
    $types = '';

    if (!empty($search)) {
        $conditions[] = "(o.recipient_name LIKE ? OR o.recipient_email LIKE ? OR o.recipient_phone LIKE ? OR o.id LIKE ? OR u.username LIKE ?)";
        $searchTerm = "%$search%";
        $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        $types .= 'sssss';
    }

    // Add status filter
    if (!empty($status)) {
        $conditions[] = "o.status = ?";
        $params[] = $status;
        $types .= 's';
    }

    $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

    // Get total count
    $countQuery = "SELECT COUNT(*) as total 
                   FROM orders o 
                   JOIN users u ON o.user_id = u.id 
                   $whereClause";

    if (!empty($params)) {
        $countStmt = $conn->prepare($countQuery);
        $countStmt->bind_param($types, ...$params);
        $countStmt->execute();
        $countResult = $countStmt->get_result();
    } else {
        $countResult = $conn->query($countQuery);
    }

    $totalRows = $countResult->fetch_object()->total;

    // Get orders with items count and payment info
    $query = "SELECT o.id,
                     o.user_id,
                     o.status,
                     o.total_price,
                     o.street_address,
                     o.recipient_name,
                     o.recipient_email,
                     o.recipient_phone,
                     o.notes,
                     o.created_at,
                     o.updated_at,
                     u.username as order_username,
                     COUNT(DISTINCT oi.id) as item_count,
                     MAX(p.proof_of_payment) as proof_of_payment,
                     GROUP_CONCAT(DISTINCT CONCAT(pr.name, ' (', oi.quantity, 'x)') SEPARATOR ', ') as items_detail
              FROM orders o
              JOIN users u ON o.user_id = u.id
              LEFT JOIN order_items oi ON o.id = oi.order_id
              LEFT JOIN products pr ON oi.product_id = pr.id
              LEFT JOIN payments p ON o.id = p.order_id
              $whereClause
              GROUP BY o.id, o.user_id, o.status, o.total_price, o.street_address, 
                       o.recipient_name, o.recipient_email, o.recipient_phone, 
                       o.notes, o.created_at, o.updated_at, u.username
              ORDER BY o.created_at DESC
              LIMIT ? OFFSET ?";

    if (!empty($params)) {
        $params[] = $limit;
        $params[] = $offset;
        $types .= 'ii';

        $stmt = $conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $stmt = $conn->prepare($query);
        $stmt->bind_param('ii', $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
    }

    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }


    return [
        'orders' => $orders,
        'total' => $totalRows,
        'total_pages' => ceil($totalRows / $limit),
        'current_page' => $page,
        'limit' => $limit
    ];
}

function getOrderByIdForAdmin($order_id)
{
    global $conn;

    $query = "SELECT o.id,
                     o.user_id,
                     o.status,
                     o.total_price,
                     o.street_address,
                     o.recipient_name,
                     o.recipient_email,
                     o.recipient_phone,
                     o.notes,
                     o.created_at,
                     o.updated_at,
                     u.username as order_username,
                     u.email as user_email,
                     MAX(p.proof_of_payment) as proof_of_payment,
                     MAX(p.created_at) as payment_date
              FROM orders o
              JOIN users u ON o.user_id = u.id
              LEFT JOIN payments p ON o.id = p.order_id
              WHERE o.id = ?
              GROUP BY o.id, o.user_id, o.status, o.total_price, o.street_address, 
                       o.recipient_name, o.recipient_email, o.recipient_phone, 
                       o.notes, o.created_at, o.updated_at, u.username, u.email";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $order = null;
    if ($result->num_rows > 0) {
        $order = $result->fetch_assoc();
    }


    return $order;
}

function updateOrderStatus($order_id, $status, $admin_id)
{
    global $conn;

    $conn->begin_transaction();

    try {
        $query = "UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $status, $order_id);
        $stmt->execute();

        $order_details_query = "SELECT * FROM orders WHERE id = ?";
        $order_stmt = $conn->prepare($order_details_query);
        $order_stmt->bind_param("i", $order_id);
        $order_stmt->execute();
        $order_data = $order_stmt->get_result()->fetch_assoc();

        $items_query = "SELECT oi.quantity, p.name 
                            FROM order_items oi 
                            JOIN products p ON oi.product_id = p.id 
                            WHERE oi.order_id = ?";
        $items_stmt = $conn->prepare($items_query);
        $items_stmt->bind_param("i", $order_id);
        $items_stmt->execute();
        $items_result = $items_stmt->get_result();

        $items_snapshot = [];
        while ($item_row = $items_result->fetch_assoc()) {
            $items_snapshot[] = $item_row;
        }

        $items_json = json_encode($items_snapshot);

        $history_query = "INSERT INTO order_histories (order_id, user_id, admin_id_approved, status_at_approval, total_price, street_address, recipient_name, recipient_email, recipient_phone, notes, items_snapshot, order_created_at) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $history_stmt = $conn->prepare($history_query);
        $history_stmt->bind_param(
            "iiisssssssss",
            $order_data['id'],
            $order_data['user_id'],
            $admin_id,
            $status,
            $order_data['total_price'],
            $order_data['street_address'],
            $order_data['recipient_name'],
            $order_data['recipient_email'],
            $order_data['recipient_phone'],
            $order_data['notes'],
            $items_json,
            $order_data['created_at']
        );
        $history_stmt->execute();

        $conn->commit();

        return true;
    } catch (Exception $e) {
        echo $e;

        $conn->rollback();

        return false;
    }
}

function isAdmin($user_id)
{
    global $conn;
    $query = "SELECT role FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $is_admin = false;
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $is_admin = ($user['role'] === 'admin');
    }


    return $is_admin;
}

function requireAdmin()
{
    requireLogin();

    if (!isAdmin($_SESSION['user_id'])) {
        $_SESSION['error'] = 'Akses ditolak. Halaman ini hanya untuk admin.';
        header('Location: /apotek-alifa/layouts/dashboard');
        exit;
    }
}

function getDashboardStats()
{
    global $conn;

    $query = "SELECT 
                SUM(CASE WHEN status = 'berhasil' THEN total_price ELSE 0 END) as total_revenue,
                COUNT(CASE WHEN status = 'tertunda' THEN 1 END) as pending_orders,
                COUNT(CASE WHEN status = 'berhasil' THEN 1 END) as successful_orders,
                COUNT(CASE WHEN status = 'gagal' THEN 1 END) as failed_orders,
                COUNT(*) as total_orders
              FROM orders";

    $result = $conn->query($query);
    $stats = $result->fetch_assoc();


    return [
        'total_revenue' => $stats['total_revenue'] ?? 0,
        'pending_orders' => $stats['pending_orders'] ?? 0,
        'successful_orders' => $stats['successful_orders'] ?? 0,
        'failed_orders' => $stats['failed_orders'] ?? 0,
        'total_orders' => $stats['total_orders'] ?? 0
    ];
}

function formatRupiah($amount)
{
    return 'Rp' . number_format($amount, 0, ',', '.');
}

function getRecentOrdersForDashboard($limit = 5)
{
    global $conn;

    $query = "SELECT o.id,
                     o.status,
                     o.total_price,
                     o.recipient_name,
                     o.recipient_phone,
                     o.created_at
              FROM orders o
              ORDER BY o.created_at DESC
              LIMIT ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();

    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }


    return $orders;
}

function getAdminDashboardStats($user_id)
{
    global $conn;

    $query = "SELECT 
                SUM(CASE WHEN status = 'berhasil' THEN total_price ELSE 0 END) as total_spending,
                COUNT(CASE WHEN status = 'tertunda' THEN 1 END) as pending_orders,
                COUNT(CASE WHEN status = 'berhasil' THEN 1 END) as successful_orders,
                COUNT(CASE WHEN status = 'gagal' THEN 1 END) as failed_orders,
                COUNT(*) as total_orders
              FROM orders 
              WHERE user_id = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stats = $result->fetch_assoc();


    return [
        'total_spending' => $stats['total_spending'] ?? 0,
        'pending_orders' => $stats['pending_orders'] ?? 0,
        'successful_orders' => $stats['successful_orders'] ?? 0,
        'failed_orders' => $stats['failed_orders'] ?? 0,
        'total_orders' => $stats['total_orders'] ?? 0
    ];
}

function getUserRecentOrders($user_id, $limit = 5)
{
    global $conn;

    $query = "SELECT o.id,
                     o.status,
                     o.total_price,
                     o.recipient_name,
                     o.recipient_phone,
                     o.created_at
              FROM orders o
              WHERE o.user_id = ?
              ORDER BY o.created_at DESC
              LIMIT ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $user_id, $limit);
    $stmt->execute();
    $result = $stmt->get_result();

    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }


    return $orders;
}

function getUserDashboardStats()
{
    global $conn;

    $query = "SELECT 
                SUM(CASE WHEN status = 'berhasil' THEN total_price ELSE 0 END) as total_spending,
                COUNT(CASE WHEN status = 'tertunda' THEN 1 END) as pending_orders,
                COUNT(CASE WHEN status = 'berhasil' THEN 1 END) as successful_orders,
                COUNT(CASE WHEN status = 'gagal' THEN 1 END) as failed_orders,
                COUNT(*) as total_orders
              FROM orders";

    $result = $conn->query($query);
    $stats = $result->fetch_assoc();


    return [
        'total_spending' => $stats['total_spending'] ?? 0,
        'pending_orders' => $stats['pending_orders'] ?? 0,
        'successful_orders' => $stats['successful_orders'] ?? 0,
        'failed_orders' => $stats['failed_orders'] ?? 0,
        'total_orders' => $stats['total_orders'] ?? 0
    ];
}
