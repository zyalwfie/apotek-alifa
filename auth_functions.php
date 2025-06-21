<?php
session_start();

function connectDB()
{
    $conn = new mysqli('localhost', 'root', '', 'apotek_alifa');
    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }
    return $conn;
}

function login($username, $password)
{
    $conn = connectDB();

    $query = "SELECT * FROM users WHERE username = ? OR email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'] ?? 'user';
            $_SESSION['logged_in'] = true;

            $stmt->close();
            $conn->close();
            return ['success' => true, 'message' => 'Login berhasil!'];
        } else {
            $stmt->close();
            $conn->close();
            return ['success' => false, 'message' => 'Password salah!'];
        }
    } else {
        $stmt->close();
        $conn->close();
        return ['success' => false, 'message' => 'Username atau email tidak ditemukan!'];
    }
}

function register($username, $email, $password, $confirm_password)
{
    $conn = connectDB();

    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        return ['success' => false, 'message' => 'Semua field harus diisi!'];
    }

    if ($password !== $confirm_password) {
        return ['success' => false, 'message' => 'Konfirmasi password tidak cocok!'];
    }

    if (strlen($password) < 6) {
        return ['success' => false, 'message' => 'Password minimal 6 karakter!'];
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Format email tidak valid!'];
    }

    $checkQuery = "SELECT * FROM users WHERE username = ? OR email = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param("ss", $username, $email);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        $checkStmt->close();
        $conn->close();
        return ['success' => false, 'message' => 'Username atau email sudah digunakan!'];
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert user baru
    $insertQuery = "INSERT INTO users (username, email, password, avatar, role, created_at) VALUES (?, ?, ?, 'default.svg', 'user', NOW())";
    $insertStmt = $conn->prepare($insertQuery);
    $insertStmt->bind_param("sss", $username, $email, $hashedPassword);

    if ($insertStmt->execute()) {
        $checkStmt->close();
        $insertStmt->close();
        $conn->close();
        return ['success' => true, 'message' => 'Registrasi berhasil! Silakan login.'];
    } else {
        $checkStmt->close();
        $insertStmt->close();
        $conn->close();
        return ['success' => false, 'message' => 'Terjadi kesalahan saat registrasi!'];
    }
}

function logout()
{
    session_destroy();
    header("Location: /apotek-alifa/layouts/landing/");
    exit();
}

function isLoggedIn()
{
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

function requireLogin()
{
    if (!isLoggedIn()) {
        header("Location: /apotek-alifa/auth/login.php");
        exit();
    }
}

function redirectIfLoggedIn()
{
    if (isLoggedIn()) {
        header("Location: /apotek-alifa/layouts/landing/");
        exit();
    }
}

function getUserData()
{
    if (isLoggedIn()) {
        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'email' => $_SESSION['email'],
            'role' => $_SESSION['role']
        ];
    }
    return null;
}

function getCartCount()
{
    if (!isLoggedIn()) {
        return 0;
    }

    $conn = connectDB();
    $query = "SELECT COUNT(*) as total FROM carts WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['total'];

    $stmt->close();
    $conn->close();

    return $count;
}
