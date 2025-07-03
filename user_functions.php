<?php
require_once 'auth_functions.php';

function getUsersForAdmin($search = '', $role = '', $page = 1, $limit = 10)
{
    global $conn;

    $offset = ($page - 1) * $limit;
    $conditions = [];
    $params = [];
    $types = '';

    if (!empty($search)) {
        $conditions[] = "(nama_pengguna LIKE ? OR surel LIKE ? OR nama_lengkap LIKE ?)";
        $searchTerm = "%$search%";
        $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
        $types .= 'sss';
    }

    if (!empty($role)) {
        $conditions[] = "peran = ?";
        $params[] = $role;
        $types .= 's';
    }

    $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

    $countQuery = "SELECT COUNT(*) as total FROM pengguna $whereClause";
    if (!empty($params)) {
        $countStmt = $conn->prepare($countQuery);
        $countStmt->bind_param($types, ...$params);
        $countStmt->execute();
        $countResult = $countStmt->get_result();
        $totalRows = $countResult->fetch_object()->total;
    } else {
        $countResult = $conn->query($countQuery);
        $totalRows = $countResult->fetch_object()->total;
    }

    $query = "SELECT id, nama_pengguna, surel, nama_lengkap, avatar, peran, waktu_dibuat 
              FROM pengguna 
              $whereClause 
              ORDER BY waktu_dibuat DESC 
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

    return [
        'users' => $users,
        'total' => $totalRows,
        'total_pages' => ceil($totalRows / $limit),
        'current_page' => $page,
        'limit' => $limit
    ];
}

function addUser($userData)
{
    global $conn;

    try {
        if (strlen($userData['username']) < 3) {
            return ['success' => false, 'message' => 'Username minimal 3 karakter'];
        }

        if (!preg_match('/^[a-zA-Z0-9_]+$/', $userData['username'])) {
            return ['success' => false, 'message' => 'Username hanya boleh mengandung huruf, angka, dan underscore'];
        }

        if (!filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Format email tidak valid'];
        }

        if (strlen($userData['password']) < 6) {
            return ['success' => false, 'message' => 'Password minimal 6 karakter'];
        }

        $checkQuery = "SELECT id FROM pengguna WHERE nama_pengguna = ?";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bind_param("s", $userData['username']);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows > 0) {
            return ['success' => false, 'message' => 'Username sudah digunakan'];
        }

        $checkQuery = "SELECT id FROM users WHERE email = ?";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bind_param("s", $userData['email']);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows > 0) {
            return ['success' => false, 'message' => 'Email sudah digunakan'];
        }

        $hashedPassword = password_hash($userData['password'], PASSWORD_DEFAULT);

        $query = "INSERT INTO pengguna (nama_lengkap, nama_pengguna, surel, sandi, avatar, peran, waktu_dibuat) 
                  VALUES (?, ?, ?, ?, 'user-1.svg', ?, NOW())";

        $stmt = $conn->prepare($query);
        $stmt->bind_param(
            "sssss",
            $userData['full_name'],
            $userData['username'],
            $userData['email'],
            $hashedPassword,
            $userData['role']
        );

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Pengguna berhasil ditambahkan'];
        } else {
            return ['success' => false, 'message' => 'Gagal menambahkan pengguna'];
        }
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

function updateUser($userId, $userData)
{
    global $conn;

    try {
        if (strlen($userData['username']) < 3) {
            return ['success' => false, 'message' => 'Username minimal 3 karakter'];
        }

        if (!preg_match('/^[a-zA-Z0-9_]+$/', $userData['username'])) {
            return ['success' => false, 'message' => 'Username hanya boleh mengandung huruf, angka, dan underscore'];
        }

        if (!filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Format email tidak valid'];
        }

        $checkQuery = "SELECT id FROM pengguna WHERE nama_pengguna = ? AND id != ?";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bind_param("si", $userData['username'], $userId);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows > 0) {
            return ['success' => false, 'message' => 'Username sudah digunakan'];
        }

        $checkQuery = "SELECT id FROM pengguna WHERE surel = ? AND id != ?";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bind_param("si", $userData['email'], $userId);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows > 0) {
            return ['success' => false, 'message' => 'Email sudah digunakan'];
        }

        if (isset($userData['password']) && !empty($userData['password'])) {
            if (strlen($userData['password']) < 6) {
                return ['success' => false, 'message' => 'Password minimal 6 karakter'];
            }

            $hashedPassword = password_hash($userData['password'], PASSWORD_DEFAULT);
            $query = "UPDATE pengguna SET nama_pengguna = ?, surel = ?, password = ?, nama_lengkap = ?, peran = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param(
                "sssssi",
                $userData['username'],
                $userData['email'],
                $hashedPassword,
                $userData['full_name'],
                $userData['role'],
                $userId
            );
        } else {
            $query = "UPDATE pengguna SET nama_pengguna = ?, surel = ?, nama_lengkap = ?, peran = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param(
                "ssssi",
                $userData['username'],
                $userData['email'],
                $userData['full_name'],
                $userData['role'],
                $userId
            );
        }

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Pengguna berhasil diperbarui'];
        } else {
            return ['success' => false, 'message' => 'Gagal memperbarui pengguna'];
        }
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

function deleteUser($userId)
{
    global $conn;

    try {
        $checkQuery = "SELECT COUNT(*) as count FROM pesanan WHERE user_id = ?";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bind_param("i", $userId);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        $orderCount = $checkResult->fetch_assoc()['count'];

        if ($orderCount > 0) {
            return ['success' => false, 'message' => 'Tidak dapat menghapus pengguna yang memiliki riwayat pesanan'];
        }

        $deleteCartQuery = "DELETE FROM keranjang WHERE id_pengguna = ?";
        $deleteCartStmt = $conn->prepare($deleteCartQuery);
        $deleteCartStmt->bind_param("i", $userId);
        $deleteCartStmt->execute();

        $query = "DELETE FROM users WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $userId);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Pengguna berhasil dihapus'];
        } else {
            return ['success' => false, 'message' => 'Gagal menghapus pengguna'];
        }
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}
