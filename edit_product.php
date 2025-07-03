<?php
include 'auth_functions.php';
require 'connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['product_id'])) {
    $product_id = intval($_POST['product_id']);
    $name = trim($_POST['name']);
    $category_id = intval($_POST['category_id']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $description = trim($_POST['description']);

    if (empty($name) || $category_id <= 0 || $price < 0 || $stock < 0) {
        $_SESSION['message'] = 'Mohon lengkapi semua field yang wajib diisi dengan benar.';
        $_SESSION['message_type'] = 'danger';
        header('Location: /apotek-alifa/layouts/dashboard?page=product.index');
        exit();
    }

    $sql = "SELECT gambar FROM obat WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        $_SESSION['message'] = 'Terjadi kesalahan database.';
        $_SESSION['message_type'] = 'danger';
        header('Location: /apotek-alifa/layouts/dashboard?page=product.index');
        exit();
    }

    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $_SESSION['message'] = 'Produk tidak ditemukan.';
        $_SESSION['message_type'] = 'danger';
        header('Location: /apotek-alifa/layouts/dashboard?page=product.index');
        exit();
    }

    $current_product = $result->fetch_assoc();
    $old_image = $current_product['gambar'];
    $stmt->close();

    $new_image = $old_image;

    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

        if (in_array($file_extension, $allowed_extensions)) {
            if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
                $_SESSION['message'] = 'Ukuran file gambar terlalu besar. Maksimal 5MB.';
                $_SESSION['message_type'] = 'danger';
                header('Location: /apotek-alifa/layouts/dashboard?page=product.index');
                exit();
            }

            $upload_dir = 'assets/img/product/uploads/';

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $image_name = uniqid() . '.' . $file_extension;
            $upload_file = $upload_dir . $image_name;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_file)) {
                if (
                    $old_image &&
                    $old_image !== 'default.png' &&
                    file_exists($upload_dir . $old_image)
                ) {
                    unlink($upload_dir . $old_image);
                }

                $new_image = $image_name;
            } else {
                $_SESSION['message'] = 'Gagal mengunggah gambar baru.';
                $_SESSION['message_type'] = 'danger';
                header('Location: /apotek-alifa/layouts/dashboard?page=product.index');
                exit();
            }
        } else {
            $_SESSION['message'] = 'Ekstensi file gambar tidak diperbolehkan. Gunakan JPG, JPEG, PNG, atau GIF.';
            $_SESSION['message_type'] = 'danger';
            header('Location: /apotek-alifa/layouts/dashboard?page=product.index');
            exit();
        }
    } elseif (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $error_messages = [
            UPLOAD_ERR_INI_SIZE => 'File terlalu besar (melebihi upload_max_filesize).',
            UPLOAD_ERR_FORM_SIZE => 'File terlalu besar (melebihi MAX_FILE_SIZE).',
            UPLOAD_ERR_PARTIAL => 'File hanya sebagian yang terupload.',
            UPLOAD_ERR_NO_TMP_DIR => 'Direktori temporary tidak ditemukan.',
            UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file ke disk.',
            UPLOAD_ERR_EXTENSION => 'Upload dihentikan oleh ekstensi PHP.'
        ];

        $error_message = $error_messages[$_FILES['image']['error']] ?? 'Terjadi kesalahan saat upload gambar.';
        $_SESSION['message'] = $error_message;
        $_SESSION['message_type'] = 'danger';
        header('Location: /apotek-alifa/layouts/dashboard?page=product.index');
        exit();
    }

    $sql = "UPDATE obat SET nama_obat = ?, id_kategori = ?, harga = ?, stok = ?, deskripsi = ?, gambar = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        $_SESSION['message'] = 'Terjadi kesalahan saat menyiapkan query update.';
        $_SESSION['message_type'] = 'danger';
        header('Location: /apotek-alifa/layouts/dashboard?page=product.index');
        exit();
    }

    $stmt->bind_param("siisssi", $name, $category_id, $price, $stock, $description, $new_image, $product_id);

    if ($stmt->execute()) {
        $_SESSION['message'] = 'Produk berhasil diperbarui!';
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = 'Terjadi kesalahan saat memperbarui produk: ' . $stmt->error;
        $_SESSION['message_type'] = 'danger';

        if ($new_image !== $old_image && $new_image !== 'default.png' && file_exists('assets/img/product/uploads/' . $new_image)) {
            unlink('assets/img/product/uploads/' . $new_image);
        }
    }

    $stmt->close();
    $conn->close();
} else {
    $_SESSION['message'] = 'Data produk tidak valid atau tidak lengkap.';
    $_SESSION['message_type'] = 'danger';
}

header('Location: /apotek-alifa/layouts/dashboard?page=product.index');
exit();
