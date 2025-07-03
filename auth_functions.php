<?php
session_start();

require_once 'connect.php';

function login($username, $password)
{
    global $conn;
    
    $query = "SELECT * FROM pengguna WHERE nama_pengguna = ? OR surel = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['sandi'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['nama_pengguna'];
            $_SESSION['full_name'] = $user['nama_lengkap'];
            $_SESSION['email'] = $user['surel'];
            $_SESSION['avatar'] = $user['avatar'];
            $_SESSION['role'] = $user['peran'];
            $_SESSION['logged_in'] = true;

            return ['success' => true, 'message' => 'Login berhasil!'];
        } else {
            return ['success' => false, 'message' => 'Password salah!'];
        }
    } else {
        return ['success' => false, 'message' => 'Username atau email tidak ditemukan!'];
    }
}

function register($full_name, $username, $email, $password, $confirm_password)
{
    global $conn;

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

    $checkQuery = "SELECT * FROM pengguna WHERE nama_pengguna = ? OR surel = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param("ss", $username, $email);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        $existing = $checkResult->fetch_assoc();

        if ($existing['username'] === $username) {
            return ['success' => false, 'message' => 'Username sudah digunakan!'];
        } else {
            return ['success' => false, 'message' => 'Email sudah digunakan!'];
        }
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $insertQuery = "INSERT INTO pengguna (nama_lengkap, nama_pengguna, surel, sandi, avatar, peran, waktu_dibuat) VALUES (?, ?, ?, ?, 'user-1.png', 'user', NOW())";
    $insertStmt = $conn->prepare($insertQuery);
    $insertStmt->bind_param("ssss", $full_name, $username, $email, $hashedPassword);

    if ($insertStmt->execute()) {
        return ['success' => true, 'message' => 'Registrasi berhasil! Silakan login.'];
    } else {
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

function getUserData($user_id = null)
{
    global $conn;
    
    if ($user_id === null && isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
    }

    if (!$user_id) {
        return null;
    }

    $query = "SELECT id, nama_pengguna, surel, nama_lengkap, avatar, peran, waktu_dibuat
              FROM pengguna 
              WHERE id = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $user = null;
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
    }

    return $user;
}

function validateUsername($username)
{
    return preg_match('/^[a-zA-Z0-9_-]+$/', $username);
}

function validateFullName($full_name)
{
    return preg_match('/^[a-zA-Z\s]+$/', $full_name);
}
