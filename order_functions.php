<?php
require_once 'auth_functions.php';

function getUserOrdersWithPagination($user_id, $search = '', $status = '', $page = 1, $limit = 5)
{
    $conn = connectDB();

    $offset = ($page - 1) * $limit;
    $conditions = ['o.user_id = ?'];
    $params = [$user_id];
    $types = 'i';

    if (!empty($search)) {
        $conditions[] = "(o.recipient_name LIKE ? OR o.recipient_email LIKE ? OR o.id LIKE ?)";
        $searchTerm = "%$search%";
        $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
        $types .= 'sss';
    }

    if (!empty($status)) {
        $conditions[] = "o.status = ?";
        $params[] = $status;
        $types .= 's';
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
    $countStmt->close();

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
                     COUNT(oi.id) as item_count,
                     MAX(p.proof_of_payment) as proof_of_payment,
                     GROUP_CONCAT(CONCAT(pr.name, ' (', oi.quantity, 'x)') SEPARATOR ', ') as items_detail
              FROM orders o
              LEFT JOIN order_items oi ON o.id = oi.order_id
              LEFT JOIN products pr ON oi.product_id = pr.id
              LEFT JOIN payments p ON o.id = p.order_id
              WHERE $whereClause
              GROUP BY o.id, o.user_id, o.status, o.total_price, o.street_address, 
                       o.recipient_name, o.recipient_email, o.recipient_phone, 
                       o.notes, o.created_at, o.updated_at
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

    $stmt->close();
    $conn->close();

    return [
        'orders' => $orders,
        'total' => $totalRows,
        'total_pages' => ceil($totalRows / $limit),
        'current_page' => $page,
        'limit' => $limit
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
    $conn = connectDB();

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

    $stmt->close();
    $conn->close();

    return $order;
}

function getAllOrdersWithPagination($search = '', $status = '', $page = 1, $limit = 10)
{
    $conn = connectDB();

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
    if (isset($countStmt)) $countStmt->close();

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

    $stmt->close();
    $conn->close();

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
    $conn = connectDB();

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

    $stmt->close();
    $conn->close();

    return $order;
}

function updateOrderStatus($order_id, $status, $admin_id)
{
    $conn = connectDB();

    $conn->begin_transaction();

    try {
        $query = "UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $status, $order_id);
        $stmt->execute();

        $log_query = "INSERT INTO order_status_logs (order_id, status, changed_by, created_at) VALUES (?, ?, ?, NOW())";
        $log_stmt = $conn->prepare($log_query);
        $log_stmt->bind_param("isi", $order_id, $status, $admin_id);
        $log_stmt->execute();

        $conn->commit();

        $stmt->close();
        if (isset($log_stmt)) $log_stmt->close();
        $conn->close();

        return true;
    } catch (Exception $e) {
        $conn->rollback();
        $conn->close();
        return false;
    }
}

function isAdmin($user_id)
{
    $conn = connectDB();
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

    $stmt->close();
    $conn->close();

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
    $conn = connectDB();
    
    $query = "SELECT 
                SUM(CASE WHEN status = 'berhasil' THEN total_price ELSE 0 END) as total_revenue,
                COUNT(CASE WHEN status = 'tertunda' THEN 1 END) as pending_orders,
                COUNT(CASE WHEN status = 'berhasil' THEN 1 END) as successful_orders,
                COUNT(CASE WHEN status = 'gagal' THEN 1 END) as failed_orders,
                COUNT(*) as total_orders
              FROM orders";
    
    $result = $conn->query($query);
    $stats = $result->fetch_assoc();
    
    $conn->close();
    
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
    $conn = connectDB();
    
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
    
    $stmt->close();
    $conn->close();
    
    return $orders;
}

function getAdminDashboardStats($user_id)
{
    $conn = connectDB();
    
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
    
    $stmt->close();
    $conn->close();
    
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
    $conn = connectDB();
    
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
    
    $stmt->close();
    $conn->close();
    
    return $orders;
}

function getUserDashboardStats()
{
    $conn = connectDB();
    
    $query = "SELECT 
                SUM(CASE WHEN status = 'berhasil' THEN total_price ELSE 0 END) as total_spending,
                COUNT(CASE WHEN status = 'tertunda' THEN 1 END) as pending_orders,
                COUNT(CASE WHEN status = 'berhasil' THEN 1 END) as successful_orders,
                COUNT(CASE WHEN status = 'gagal' THEN 1 END) as failed_orders,
                COUNT(*) as total_orders
              FROM orders";
    
    $result = $conn->query($query);
    $stats = $result->fetch_assoc();
    
    $conn->close();
    
    return [
        'total_spending' => $stats['total_spending'] ?? 0,
        'pending_orders' => $stats['pending_orders'] ?? 0,
        'successful_orders' => $stats['successful_orders'] ?? 0,
        'failed_orders' => $stats['failed_orders'] ?? 0,
        'total_orders' => $stats['total_orders'] ?? 0
    ];
}