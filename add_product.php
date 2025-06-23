<?php
include 'auth_functions.php';

$conn = connectDB();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $name = $_POST['name'];
    $category_id = $_POST['category_id'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $description = $_POST['description'];

    $image = 'default.png';

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);

        if (in_array(strtolower($file_extension), $allowed_extensions)) {
            $upload_dir = 'assets/img/product/uploads/';
            $image_name = uniqid() . '.' . $file_extension;
            $upload_file = $upload_dir . $image_name;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_file)) {
                $image = $image_name;
            } else {
                $_SESSION['message'] = 'Gagal mengunggah gambar.';
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

    $sql = "INSERT INTO products (name, category_id, price, stock, description, image) 
            VALUES (?, ?, ?, ?, ?, ?)";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("sssiii", $name, $category_id, $price, $stock, $description, $image);

        if ($stmt->execute()) {
            $_SESSION['message'] = 'Produk berhasil ditambahkan!';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'Terjadi kesalahan: ' . $stmt->error;
            $_SESSION['message_type'] = 'danger';
        }

        $stmt->close();
    } else {
        $_SESSION['message'] = 'Terjadi kesalahan: ' . $conn->error;
        $_SESSION['message_type'] = 'danger';
    }

    $conn->close();

    header('Location:/apotek-alifa/layouts/dashboard?page=product.index');
    exit();
}
