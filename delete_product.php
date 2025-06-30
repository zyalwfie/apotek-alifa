<?php
include 'auth_functions.php';
require_once 'connect.php';

if (isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];

    $sql = "SELECT image FROM products WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $product = $result->fetch_assoc();
            $old_image = $product['image'];
        } else {
            $_SESSION['message'] = 'Produk tidak ditemukan.';
            $_SESSION['message_type'] = 'danger';
            header('Location:/apotek-alifa/layouts/dashboard?page=product.index');
            exit();
        }
        // $stmt->close();
    } else {
        $_SESSION['message'] = 'Terjadi kesalahan saat mengambil data produk.';
        $_SESSION['message_type'] = 'danger';
        header('Location:/apotek-alifa/layouts/dashboard?page=product.index');
        exit();
    }

    if ($old_image && $old_image != 'default.png') {
        $upload_dir = 'uploads/products/';
        $old_image_path = $upload_dir . $old_image;
        if (file_exists($old_image_path)) {
            unlink($old_image_path);
        }
    }

    $sql = "DELETE FROM products WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $product_id);

        if ($stmt->execute()) {
            $_SESSION['message'] = 'Produk berhasil dihapus!';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'Terjadi kesalahan saat menghapus produk.';
            $_SESSION['message_type'] = 'danger';
        }

        // $stmt->close();
    } else {
        $_SESSION['message'] = 'Terjadi kesalahan saat menghapus produk.';
        $_SESSION['message_type'] = 'danger';
    }

    // $conn->close();

    header('Location:/apotek-alifa/layouts/dashboard?page=product.index');
    exit();
} else {
    $_SESSION['message'] = 'ID produk tidak ditemukan.';
    $_SESSION['message_type'] = 'danger';
    header('Location:/apotek-alifa/layouts/dashboard?page=product.index');
    exit();
}
