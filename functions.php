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

function getProductsWithPagination($search = '', $page = 1, $limit = 8)
{
    $conn = new mysqli('localhost', 'root', '', 'apotek_alifa');

    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }

    $offset = ($page - 1) * $limit;
    $searchCondition = '';
    $params = [];
    $types = '';

    if (!empty($search)) {
        $searchCondition = "WHERE name LIKE ? OR description LIKE ?";
        $searchTerm = "%$search%";
        $params = [$searchTerm, $searchTerm];
        $types = 'ss';
    }

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

function buildPaginationUrl($page, $query = '')
{
    $params = ['page' => 'shop', 'p' => $page];
    if (!empty($query)) {
        $params['query'] = $query;
    }
    return '?' . http_build_query($params);
}
