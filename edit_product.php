<?php
include 'auth_functions.php';
require 'connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];
    $name = $_POST['name'];
    $category_id = $_POST['category_id'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $description = $_POST['description'];

    $sql = "SELECT image FROM products WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $old_image = $result->fetch_assoc()['image'];
        } else {
            $_SESSION['message'] = 'Produk tidak ditemukan.';
            $_SESSION['message_type'] = 'danger';
            header('Location:/apotek-alifa/layouts/dashboard?page=product.index');
            exit();
        }
        // $stmt->close();
    }

    $image = $old_image;

    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);

        if (in_array(strtolower($file_extension), $allowed_extensions)) {
            $upload_dir = 'assets/img/product/uploads/';
            if (file_exists($upload_dir . $old_image) && $old_image != 'default.png') {
                unlink($upload_dir . $old_image);
            }

            $image_name = uniqid() . '.' . $file_extension;
            $upload_file = $upload_dir . $image_name;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_file)) {
                $image = $image_name;
            } else {
                $_SESSION['message'] = 'Gagal mengunggah gambar baru.';
                $_SESSION['message_type'] = 'danger';
                header('Location:/apotek-alifa/layouts/dashboard?page=product.index');
                exit();
            }
        } else {
            $_SESSION['message'] = 'Ekstensi file gambar tidak diperbolehkan.';
            $_SESSION['message_type'] = 'danger';
            header('Location:/apotek-alifa/layouts/dashboard?page=product.index');
            exit();
        }
    }

    $sql = "UPDATE products SET name = ?, category_id = ?, price = ?, stock = ?, description = ?, image = ? WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("siisssi", $name, $category_id, $price, $stock, $description, $image, $product_id);

        if ($stmt->execute()) {
            $_SESSION['message'] = 'Produk berhasil diperbarui!';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'Terjadi kesalahan saat memperbarui produk.';
            $_SESSION['message_type'] = 'danger';
        }

        // $stmt->close();
    } else {
        $_SESSION['message'] = 'Terjadi kesalahan saat memperbarui produk.';
        $_SESSION['message_type'] = 'danger';
    }

    // $conn->close();

    header('Location:/apotek-alifa/layouts/dashboard?page=product.index');
    exit();
} else {
    $_SESSION['message'] = 'Tidak ada product yang sesuai.';
    $_SESSION['message_type'] = 'danger';
    header('Location:/apotek-alifa/layouts/dashboard?page=product.index');
    exit();
}
