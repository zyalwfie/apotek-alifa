<?php
require_once 'auth_functions.php';
require_once 'connect.php';

function getUserPendingOrdersWithPagination($user_id, $search = '', $page = 1, $limit = 5)
{
    global $conn;

    $offset = ($page - 1) * $limit;

    $pendingStatus = 'tertunda';
    $conditions = ['o.id_pengguna = ?', 'o.status = ?'];
    $params = [$user_id, $pendingStatus];
    $types = 'is';

    if (!empty($search)) {
        $conditions[] = "(o.nama_penerima LIKE ? OR o.surel_penerima LIKE ? OR o.id LIKE ?)";
        $searchTerm = "%$search%";
        $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
        $types .= 'sss';
    }

    $whereClause = implode(' AND ', $conditions);

    $countQuery = "SELECT COUNT(*) as total 
                   FROM pesanan o 
                   WHERE $whereClause";

    $countStmt = $conn->prepare($countQuery);
    $countStmt->bind_param($types, ...$params);
    $countStmt->execute();
    $countResult = $countStmt->get_result();
    $totalRows = $countResult->fetch_object()->total;

    $query = "SELECT o.id, o.id_pengguna, o.status, o.harga_total, o.alamat,
                     o.nama_penerima, o.surel_penerima, o.nomor_telepon_penerima,
                     o.catatan, o.waktu_dibuat, o.waktu_diubah,
                     COUNT(oi.id) as item_count,
                     MAX(p.bukti_pembayaran) as bukti_pembayaran,
                     GROUP_CONCAT(CONCAT(pr.nama_obat, ' (', oi.kuantitas, 'x)') SEPARATOR ', ') as items_detail
              FROM pesanan o
              LEFT JOIN barang_pesanan oi ON o.id = oi.id_pesanan
              LEFT JOIN obat pr ON oi.id_obat = pr.id
              LEFT JOIN pembayaran p ON o.id = p.id_pesanan
              WHERE $whereClause
              GROUP BY o.id
              ORDER BY o.waktu_dibuat DESC
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

    $conditions = ['o.id_pengguna = ?'];
    $params = [$user_id];
    $types = 'i';

    if (!empty($search)) {
        $conditions[] = "(o.nama_penerima LIKE ? OR o.surel_penerima LIKE ? OR o.id_pesanan LIKE ?)";
        $searchTerm = "%$search%";
        $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
        $types .= 'sss';
    }

    $whereClause = implode(' AND ', $conditions);

    $countQuery = "SELECT COUNT(*) as total 
                   FROM riwayat_pesanan o 
                   WHERE $whereClause";

    $countStmt = $conn->prepare($countQuery);
    $countStmt->bind_param($types, ...$params);
    $countStmt->execute();
    $countResult = $countStmt->get_result();
    $totalRows = $countResult->fetch_object()->total;
    $countStmt->close();


    $query = "SELECT o.id, o.id_pengguna, o.id_pengguna, o.status_saat_disetujui,
                     o.harga_total, o.alamat,
                     o.nama_penerima, o.surel_penerima, o.nomor_telepon_penerima,
                     o.catatan, o.waktu_dibuat_pesanan, o.waktu_disetujui,
                     o.cuplikan_barang,
                     JSON_LENGTH(o.cuplikan_barang) as item_count
              FROM riwayat_pesanan o
              WHERE $whereClause
              ORDER BY o.waktu_disetujui DESC
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
        $row['cuplikan_barang'] = json_decode($row['cuplikan_barang'], true);
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
                     o.id_pengguna,
                     o.status,
                     o.harga_total,
                     o.alamat,
                     o.nama_penerima,
                     o.surel_penerima,
                     o.nomor_telepon_penerima,
                     o.catatan,
                     o.waktu_dibuat,
                     o.waktu_diubah,
                     MAX(p.bukti_pembayaran) as bukti_pembayaran,
                     GROUP_CONCAT(CONCAT(pr.nama_obat, ' (', oi.kuantitas, 'x) - Rp', FORMAT(pr.harga * oi.kuantitas, 0)) SEPARATOR '<br>') as items_detail
              FROM pesanan o
              LEFT JOIN barang_pesanan oi ON o.id = oi.id_pesanan
              LEFT JOIN obat pr ON oi.id_obat = pr.id
              LEFT JOIN pembayaran p ON o.id = p.id_pesanan
              WHERE o.id = ? AND o.id_pengguna = ?
              GROUP BY o.id, o.id_pengguna, o.status, o.harga_total, o.alamat, 
                       o.nama_penerima, o.surel_penerima, o.nomor_telepon_penerima, 
                       o.catatan, o.waktu_dibuat, o.waktu_diubah";

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
        $conditions[] = "(o.nama_penerima LIKE ? OR o.surel_penerima LIKE ? OR o.nomor_telepon_penerima LIKE ? OR o.id LIKE ? OR u.nama_pengguna LIKE ?)";
        $searchTerm = "%$search%";
        $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        $types .= 'sssss';
    }

    if (!empty($status)) {
        $conditions[] = "o.status = ?";
        $params[] = $status;
        $types .= 's';
    }

    $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

    $countQuery = "SELECT COUNT(*) as total 
                   FROM pesanan o 
                   JOIN pengguna u ON o.id_pengguna = u.id 
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

    $query = "SELECT o.id,
                     o.id_pengguna,
                     o.status,
                     o.harga_total,
                     o.alamat,
                     o.nama_penerima,
                     o.surel_penerima,
                     o.nomor_telepon_penerima,
                     o.catatan,
                     o.waktu_dibuat,
                     o.waktu_diubah,
                     u.nama_pengguna as order_username,
                     COUNT(DISTINCT oi.id) as item_count,
                     MAX(p.bukti_pembayaran) as bukti_pembayaran,
                     GROUP_CONCAT(DISTINCT CONCAT(pr.nama_obat, ' (', oi.kuantitas, 'x)') SEPARATOR ', ') as items_detail
              FROM pesanan o
              JOIN pengguna u ON o.id_pengguna = u.id
              LEFT JOIN barang_pesanan oi ON o.id = oi.id_pesanan
              LEFT JOIN obat pr ON oi.id_obat = pr.id
              LEFT JOIN pembayaran p ON o.id = p.id_pesanan
              $whereClause
              GROUP BY o.id, o.id_pengguna, o.status, o.harga_total, o.alamat, 
                       o.nama_penerima, o.surel_penerima, o.nomor_telepon_penerima, 
                       o.catatan, o.waktu_dibuat, o.waktu_diubah, u.nama_pengguna
              ORDER BY o.waktu_dibuat DESC
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
                     o.id_pengguna,
                     o.status,
                     o.harga_total,
                     o.alamat,
                     o.nama_penerima,
                     o.surel_penerima,
                     o.nomor_telepon_penerima,
                     o.catatan,
                     o.waktu_dibuat,
                     o.waktu_diubah,
                     u.nama_pengguna as order_username,
                     u.surel as user_email,
                     MAX(p.bukti_pembayaran) as bukti_pembayaran,
                     MAX(p.waktu_dibuat) as payment_date
              FROM pesanan o
              JOIN pengguna u ON o.id_pengguna = u.id
              LEFT JOIN pembayaran p ON o.id = p.id_pesanan
              WHERE o.id = ?
              GROUP BY o.id, o.id_pengguna, o.status, o.harga_total, o.alamat, 
                       o.nama_penerima, o.surel_penerima, o.nomor_telepon_penerima, 
                       o.catatan, o.waktu_dibuat, o.waktu_diubah, u.nama_pengguna, u.surel";

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
        $query = "UPDATE pesanan SET status = ?, waktu_diubah = NOW() WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $status, $order_id);
        $stmt->execute();

        $order_details_query = "SELECT * FROM pesanan WHERE id = ?";
        $order_stmt = $conn->prepare($order_details_query);
        $order_stmt->bind_param("i", $order_id);
        $order_stmt->execute();
        $order_data = $order_stmt->get_result()->fetch_assoc();

        $items_query = "SELECT oi.kuantitas, p.nama_obat 
                            FROM barang_pesanan oi 
                            JOIN obat p ON oi.id_obat = p.id 
                            WHERE oi.id_pesanan = ?";
        $items_stmt = $conn->prepare($items_query);
        $items_stmt->bind_param("i", $order_id);
        $items_stmt->execute();
        $items_result = $items_stmt->get_result();

        $items_snapshot = [];
        while ($item_row = $items_result->fetch_assoc()) {
            $items_snapshot[] = $item_row;
        }

        $items_json = json_encode($items_snapshot);

        $history_query = "INSERT INTO riwayat_pesanan (id_pesanan, id_pengguna, id_admin_yang_menyetujui, status_saat_disetujui, harga_total, alamat, nama_penerima, surel_penerima, nomor_telepon_penerima, catatan, cuplikan_barang, waktu_dibuat_pesanan) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $history_stmt = $conn->prepare($history_query);
        $history_stmt->bind_param(
            "iiisssssssss",
            $order_data['id'],
            $order_data['id_pengguna'],
            $admin_id,
            $status,
            $order_data['harga_total'],
            $order_data['alamat'],
            $order_data['nama_penerima'],
            $order_data['surel_penerima'],
            $order_data['nomor_telepon_penerima'],
            $order_data['catatan'],
            $items_json,
            $order_data['waktu_dibuat']
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
    $query = "SELECT peran FROM pengguna WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $is_admin = false;
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $is_admin = ($user['peran'] === 'admin');
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
                SUM(CASE WHEN status = 'berhasil' THEN harga_total ELSE 0 END) as total_revenue,
                COUNT(CASE WHEN status = 'tertunda' THEN 1 END) as pending_orders,
                COUNT(CASE WHEN status = 'berhasil' THEN 1 END) as successful_orders,
                COUNT(CASE WHEN status = 'gagal' THEN 1 END) as failed_orders,
                COUNT(*) as total_orders
              FROM pesanan";

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
                     o.harga_total,
                     o.nama_penerima,
                     o.nomor_telepon_penerima,
                     o.waktu_dibuat
              FROM pesanan o
              ORDER BY o.waktu_dibuat DESC
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
                     o.harga_total,
                     o.nama_penerima,
                     o.nomor_telepon_penerima,
                     o.waktu_dibuat
              FROM pesanan o
              WHERE o.id_pengguna = ?
              ORDER BY o.waktu_dibuat DESC
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
                SUM(CASE WHEN status = 'berhasil' THEN harga_total ELSE 0 END) as total_spending,
                COUNT(CASE WHEN status = 'tertunda' THEN 1 END) as pending_orders,
                COUNT(CASE WHEN status = 'berhasil' THEN 1 END) as successful_orders,
                COUNT(CASE WHEN status = 'gagal' THEN 1 END) as failed_orders,
                COUNT(*) as total_orders
              FROM pesanan";

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
