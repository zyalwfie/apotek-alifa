<?php
// auth_functions.php
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

    // Query untuk mencari user berdasarkan username atau email
    $query = "SELECT * FROM users WHERE username = ? OR email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verifikasi password
        if (password_verify($password, $user['password'])) {
            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['avatar'] = $user['avatar'];
            $_SESSION['role'] = $user['role'];
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

function register($full_name, $username, $email, $password, $confirm_password)
{
    $conn = connectDB();

    // Validasi input
    if (empty($full_name) || empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
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

    if (strlen($username) < 3) {
        return ['success' => false, 'message' => 'Username minimal 3 karakter!'];
    }

    if (strlen($full_name) < 2) {
        return ['success' => false, 'message' => 'Nama lengkap minimal 2 karakter!'];
    }

    // Cek apakah username atau email sudah ada
    $checkQuery = "SELECT * FROM users WHERE username = ? OR email = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param("ss", $username, $email);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        $existing = $checkResult->fetch_assoc();
        $checkStmt->close();
        $conn->close();

        if ($existing['username'] === $username) {
            return ['success' => false, 'message' => 'Username sudah digunakan!'];
        } else {
            return ['success' => false, 'message' => 'Email sudah digunakan!'];
        }
    }

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert user baru dengan avatar default
    $insertQuery = "INSERT INTO users (full_name, username, email, password, avatar, role, created_at) VALUES (?, ?, ?, ?, 'default.png', 'user', NOW())";
    $insertStmt = $conn->prepare($insertQuery);
    $insertStmt->bind_param("ssss", $full_name, $username, $email, $hashedPassword);

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
            'full_name' => $_SESSION['full_name'],
            'email' => $_SESSION['email'],
            'avatar' => $_SESSION['avatar'],
            'role' => $_SESSION['role']
        ];
    }
    return null;
}

function validateUsername($username)
{
    return preg_match('/^[a-zA-Z0-9_-]+$/', $username);
}

function validateFullName($full_name)
{
    return preg_match('/^[a-zA-Z\s]+$/', $full_name);
}
