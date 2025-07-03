<?php
function getAllProductsWithPagination($search = '', $category = '', $page = 1, $limit = 5)
{
    global $conn;

    $offset = ($page - 1) * $limit;
    $conditions = [];
    $params = [];
    $types = '';

    if (!empty($search)) {
        $conditions[] = "(p.name LIKE ? OR p.description LIKE ?)";
        $searchTerm = "%$search%";
        $params = array_merge($params, [$searchTerm, $searchTerm]);
        $types .= 'ss';
    }

    if (!empty($category)) {
        $conditions[] = "p.category_id = ?";
        $params[] = $category;
        $types .= 'i';
    }

    $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

    $countQuery = "SELECT COUNT(*) as total FROM obat p $whereClause";

    if (!empty($params)) {
        $countStmt = $conn->prepare($countQuery);
        $countStmt->bind_param($types, ...$params);
        $countStmt->execute();
        $countResult = $countStmt->get_result();
    } else {
        $countResult = $conn->query($countQuery);
    }

    $totalRows = $countResult->fetch_object()->total;

    $query = "SELECT p.*, c.nama_kategori as nama_kategori,
              (SELECT COUNT(*) FROM barang_pesanan WHERE id_obat = p.id) as total_orders
              FROM obat p
              LEFT JOIN kategori c ON p.id_kategori = c.id
              $whereClause
              ORDER BY p.nama_obat DESC
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
    global $conn;

    $query = "SELECT p.*, c.nama_kategori as nama_kategori
              FROM obat p
              LEFT JOIN kategori c ON p.id_kategori = c.id
              WHERE p.id = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $product = $result->fetch_assoc();

    return $product;
}

function getAllCategories()
{
    global $conn;

    $query = "SELECT * FROM kategori ORDER BY nama_kategori ASC";
    $result = $conn->query($query);

    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }

    return $categories;
}

function addProduct($data, $image_file = null)
{
    global $conn;

    $image_name = 'default.jpg';
    if ($image_file && $image_file['error'] === 0) {
        $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/apotek-alifa/assets/img/product/uploads/';
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

    $query = "INSERT INTO obat (gambar, nama_obat, deskripsi, id_kategori, harga, stok) 
              VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($query);
    $stmt->bind_param(
        "sssiii",
        $image_name,
        $data['name'],
        $data['description'],
        $data['category_id'],
        $data['price'],
        $data['stock']
    );

    $success = $stmt->execute();
    $product_id = $conn->insert_id;

    return $success ? $product_id : false;
}

function updateProduct($product_id, $data, $image_file = null)
{
    global $conn;

    $current = getProductById($product_id);
    if (!$current) return false;

    $image_name = $current['image'];

    if ($image_file && $image_file['error'] === 0) {
        $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/apotek-alifa/assets/img/product/uploads/';
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

    $query = "UPDATE obat 
              SET gambar = ?, nama_obat = ?, deskripsi = ?, id_kategori = ?, harga = ?, stok = ?
              WHERE id = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param(
        "sssiiii",
        $image_name,
        $data['name'],
        $data['description'],
        $data['category_id'],
        $data['price'],
        $data['stock'],
        $product_id
    );

    $success = $stmt->execute();

    return $success;
}

function deleteProduct($product_id)
{
    global $conn;

    $check_query = "SELECT COUNT(*) as count FROM barang_pesanan WHERE id_obat = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("i", $product_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $count = $result->fetch_object()->count;

    if ($count > 0) {
        return ['success' => false, 'message' => 'Produk tidak dapat dihapus karena sudah memiliki pesanan'];
    }

    $product = getProductById($product_id);

    $query = "DELETE FROM products WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $product_id);
    $success = $stmt->execute();

    if ($success && $product && $product['image'] !== 'default.jpg') {
        $image_path = $_SERVER['DOCUMENT_ROOT'] . '/apotek-alifa/assets/img/product/uploads/' . $product['image'];
        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }

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
