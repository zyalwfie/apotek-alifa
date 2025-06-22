<?php

function getData($query, $params = [])
{
    $conn = new mysqli('localhost', 'root', '', 'apotek_alifa');

    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }

    // Prepare statement if parameters are provided
    if (!empty($params)) {
        $stmt = $conn->prepare($query);
        if ($stmt) {
            // Create types string based on parameter count
            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
            $result = $stmt->execute() ? $stmt->get_result() : false;
            $stmt->close();
        } else {
            $result = false;
        }
    } else {
        $result = $conn->query($query);
    }

    $data = array();

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_object()) {
            $data[] = $row;
        }
    }

    $conn->close();
    return !empty($data) ? $data : false;
}

function getProductsWithPagination($search = '', $page = 1, $limit = 12)
{
    $conn = new mysqli('localhost', 'root', '', 'apotek_alifa');

    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }

    $offset = ($page - 1) * $limit;
    $searchCondition = '';
    $params = [];
    $types = '';

    // Build search condition
    if (!empty($search)) {
        $searchCondition = "WHERE name LIKE ? OR description LIKE ?";
        $searchTerm = "%$search%";
        $params = [$searchTerm, $searchTerm];
        $types = 'ss';
    }

    // Get total count for pagination
    $countQuery = "SELECT COUNT(*) as total FROM products $searchCondition";
    if (!empty($params)) {
        $countStmt = $conn->prepare($countQuery);
        $countStmt->bind_param($types, ...$params);
        $countStmt->execute();
        $countResult = $countStmt->get_result();
        $totalRows = $countResult->fetch_object()->total;
        $countStmt->close();
    } else {
        $countResult = $conn->query($countQuery);
        $totalRows = $countResult->fetch_object()->total;
    }

    // Get products with pagination
    $query = "SELECT * FROM products $searchCondition ORDER BY name ASC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $types .= 'ii';

    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();

        $products = [];
        while ($row = $result->fetch_object()) {
            $products[] = $row;
        }
        $stmt->close();
    } else {
        $products = [];
    }

    $conn->close();

    return [
        'products' => $products,
        'total' => $totalRows,
        'total_pages' => ceil($totalRows / $limit),
        'current_page' => $page,
        'limit' => $limit
    ];
}

function getProductDetail($product_id)
{
    $conn = new mysqli('localhost', 'root', '', 'apotek_alifa');

    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }

    $query = "SELECT * FROM products WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $product = null;
    if ($result->num_rows > 0) {
        $product = $result->fetch_object();
    }

    $stmt->close();
    $conn->close();

    return $product;
}

function getRelatedProducts($product_id, $limit = 4)
{
    $conn = new mysqli('localhost', 'root', '', 'apotek_alifa');

    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }

    // Get related products (excluding current product)
    $query = "SELECT * FROM products WHERE id != ? ORDER BY RAND() LIMIT ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $product_id, $limit);
    $stmt->execute();
    $result = $stmt->get_result();

    $products = [];
    while ($row = $result->fetch_object()) {
        $products[] = $row;
    }

    $stmt->close();
    $conn->close();

    return $products;
}

function buildPaginationUrl($page, $query = '')
{
    $params = ['page' => 'shop', 'p' => $page];
    if (!empty($query)) {
        $params['query'] = $query;
    }
    return '?' . http_build_query($params);
}

function formatPrice($price)
{
    return 'Rp' . number_format($price, 0, '.', ',');
}

function formatDate($date)
{
    return date('d F Y', strtotime($date));
}

function truncateText($text, $length = 100)
{
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . '...';
}

function generateSKU($product_id)
{
    return 'PRD-' . str_pad($product_id, 6, '0', STR_PAD_LEFT);
}

function calculateDiscount($original_price, $current_price)
{
    if ($original_price <= $current_price) {
        return 0;
    }
    return round((($original_price - $current_price) / $original_price) * 100);
}

function isProductAvailable($stock)
{
    return isset($stock) && $stock > 0;
}

function getStockStatus($stock)
{
    if (!isset($stock)) {
        return ['status' => 'unknown', 'text' => 'Stok tidak diketahui', 'class' => 'secondary'];
    }

    if ($stock <= 0) {
        return ['status' => 'out', 'text' => 'Habis', 'class' => 'danger'];
    } elseif ($stock <= 5) {
        return ['status' => 'low', 'text' => 'Stok Menipis', 'class' => 'warning'];
    } else {
        return ['status' => 'available', 'text' => 'Tersedia', 'class' => 'success'];
    }
}
