<?php
function getAllProductsWithPagination($search = '', $category = '', $page = 1, $limit = 5)
{
    $conn = connectDB();

    $offset = ($page - 1) * $limit;
    $conditions = [];
    $params = [];
    $types = '';

    if (!empty($search)) {
        $conditions[] = "(p.name LIKE ? OR p.description LIKE ?)";
        $searchTerm = "%$search%";
        $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
        $types .= 'sss';
    }

    if (!empty($category)) {
        $conditions[] = "p.category_id = ?";
        $params[] = $category;
        $types .= 'i';
    }

    $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

    $countQuery = "SELECT COUNT(*) as total FROM products p $whereClause";

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

    $query = "SELECT p.*, c.name as category_name,
              (SELECT COUNT(*) FROM order_items WHERE product_id = p.id) as total_orders
              FROM products p
              LEFT JOIN categories c ON p.category_id = c.id
              $whereClause
              ORDER BY p.name DESC
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

    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }

    $stmt->close();
    $conn->close();

    return [
        'products' => $products,
        'total' => $totalRows,
        'total_pages' => ceil($totalRows / $limit),
        'current_page' => $page,
        'limit' => $limit
    ];
}

function getProductById($product_id)
{
    $conn = connectDB();

    $query = "SELECT p.*, c.name as category_name
              FROM products p
              LEFT JOIN categories c ON p.category_id = c.id
              WHERE p.id = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $product = $result->fetch_assoc();

    $stmt->close();
    $conn->close();

    return $product;
}

function getAllCategories()
{
    $conn = connectDB();

    $query = "SELECT * FROM categories ORDER BY name ASC";
    $result = $conn->query($query);

    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }

    $conn->close();

    return $categories;
}

function addProduct($data, $image_file = null)
{
    $conn = connectDB();

    $image_name = 'default.jpg';
    if ($image_file && $image_file['error'] === 0) {
        $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/apotek-alifa/assets/img/product/uploads';
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];

        if (in_array($image_file['type'], $allowed_types)) {
            $extension = pathinfo($image_file['name'], PATHINFO_EXTENSION);
            $image_name = 'product_' . time() . '_' . uniqid() . '.' . $extension;
            $upload_path = $upload_dir . $image_name;

            if (!move_uploaded_file($image_file['tmp_name'], $upload_path)) {
                $image_name = 'default.jpg';
            }
        }
    }

    $query = "INSERT INTO products (image, name, description, category_id, price, stock) 
              VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($query);
    $stmt->bind_param(
        "sssiii",
        $image_name,
        $data['name'],
        $data['description'],
        $data['category_id'],
        $data['price'],
        $data['stock'],
    );

    $success = $stmt->execute();
    $product_id = $conn->insert_id;

    $stmt->close();
    $conn->close();

    return $success ? $product_id : false;
}

function updateProduct($product_id, $data, $image_file = null)
{
    $conn = connectDB();

    $current = getProductById($product_id);
    if (!$current) return false;

    $image_name = $current['image'];

    if ($image_file && $image_file['error'] === 0) {
        $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/apotek-alifa/assets/img/product/uploads';
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];

        if (in_array($image_file['type'], $allowed_types)) {
            $extension = pathinfo($image_file['name'], PATHINFO_EXTENSION);
            $new_image_name = 'product_' . time() . '_' . uniqid() . '.' . $extension;
            $upload_path = $upload_dir . $new_image_name;

            if (move_uploaded_file($image_file['tmp_name'], $upload_path)) {
                if ($image_name !== 'default.jpg' && file_exists($upload_dir . $image_name)) {
                    unlink($upload_dir . $image_name);
                }
                $image_name = $new_image_name;
            }
        }
    }

    $query = "UPDATE products 
              SET image = ?, name = ?, description = ?,category_id = ?, price = ?, stock = ?
              WHERE id = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param(
        "ssdisssi",
        $image_name,
        $data['name'],
        $data['description'],
        $data['category_id'],
        $data['price'],
        $data['stock'],
        $product_id
    );

    $success = $stmt->execute();

    $stmt->close();
    $conn->close();

    return $success;
}

function deleteProduct($product_id)
{
    $conn = connectDB();

    $check_query = "SELECT COUNT(*) as count FROM order_items WHERE product_id = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("i", $product_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $count = $result->fetch_object()->count;
    $check_stmt->close();

    if ($count > 0) {
        $conn->close();
        return ['success' => false, 'message' => 'Produk tidak dapat dihapus karena sudah memiliki pesanan'];
    }

    $product = getProductById($product_id);

    $query = "DELETE FROM products WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $product_id);
    $success = $stmt->execute();
    $stmt->close();

    if ($success && $product && $product['image'] !== 'default.jpg') {
        $image_path = $_SERVER['DOCUMENT_ROOT'] . '/apotek-alifa/assets/img/products/' . $product['image'];
        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }

    $conn->close();

    return ['success' => $success, 'message' => $success ? 'Produk berhasil dihapus' : 'Gagal menghapus produk'];
}

function buildProductPaginationUrl($page, $search = '', $category = '')
{
    $params = ['page' => 'product.index', 'p' => $page];
    if (!empty($search)) {
        $params['search'] = $search;
    }
    if (!empty($category)) {
        $params['category'] = $category;
    }
    return '?' . http_build_query($params);
}
