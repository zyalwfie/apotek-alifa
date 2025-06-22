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
