<?php
require_once '../../auth_functions.php';

requireLogin();

$user_id = $_SESSION['user_id'];
$user = getUserData($user_id);

$errors = [];
$full_name = $user['full_name'] ?: '';
$username = $user['username'];
$email = $user['email'];
$avatar = $user['avatar'] ?: 'user-1.svg';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $avatar = $_POST['avatar'] ?? $user['avatar'];

    if (empty($username)) {
        $errors['username'] = 'Nama pengguna harus diisi';
    } elseif (strlen($username) < 3) {
        $errors['username'] = 'Nama pengguna minimal 3 karakter';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors['username'] = 'Nama pengguna hanya boleh huruf, angka, dan underscore';
    }

    if (empty($email)) {
        $errors['email'] = 'Email harus diisi';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Format email tidak valid';
    }

    if (empty($errors)) {
        $conn = connectDB();

        if ($username !== $user['username']) {
            $check_username = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
            $check_username->bind_param("si", $username, $user_id);
            $check_username->execute();
            if ($check_username->get_result()->num_rows > 0) {
                $errors['username'] = 'Username sudah digunakan';
            }
            $check_username->close();
        }

        if ($email !== $user['email']) {
            $check_email = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $check_email->bind_param("si", $email, $user_id);
            $check_email->execute();
            if ($check_email->get_result()->num_rows > 0) {
                $errors['email'] = 'Email sudah digunakan';
            }
            $check_email->close();
        }

        if (empty($errors)) {
            $update_query = "UPDATE users SET full_name = ?, username = ?, email = ?, avatar = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("ssssi", $full_name, $username, $email, $avatar, $user_id);

            if ($update_stmt->execute()) {
                $_SESSION['username'] = $username;
                $_SESSION['success_message'] = 'Profil berhasil diperbarui';

                $update_stmt->close();
                $conn->close();

                header('Location: /apotek-alifa/layouts/dashboard?page=profile.index');
                exit;
            } else {
                $errors['general'] = 'Gagal memperbarui profil. Silakan coba lagi.';
            }

            $update_stmt->close();
        }

        $conn->close();
    }
}
?>

<form action="" class="row" method="post">
    <?php if (!empty($errors['general'])): ?>
        <div class="col-12">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($errors['general']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    <?php endif; ?>

    <div class="col-lg-4">
        <h5 class="card-title fw-semibold mb-3">Pilih Avatar</h5>
        <div class="card pt-4">
            <img id="imgPreview"
                src="/apotek-alifa/assets/img/profile/<?= htmlspecialchars($avatar) ?>"
                alt="<?= htmlspecialchars($username) ?>"
                style="width: 81%; margin: auto; border-radius: 50%;">
            <div class="card-body">
                <div class="row row-cols-4 justify-content-center align-items-center gy-3">
                    <?php for ($i = 1; $i <= 8; $i++) : ?>
                        <div class="col input-container">
                            <input class="radio-input"
                                type="radio"
                                name="avatar"
                                value="user-<?= $i ?>.svg"
                                id="avatar-<?= $i ?>"
                                <?= ($avatar === "user-$i.svg") ? 'checked' : '' ?>>
                            <label for="avatar-<?= $i ?>" class="avatar-label">
                                <img src="/apotek-alifa/assets/img/profile/user-<?= $i ?>.svg"
                                    alt="Profile <?= $i ?>"
                                    style="width: 100%; object-fit: cover; border-radius: 50%;">
                            </label>
                        </div>
                    <?php endfor ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <h5 class="card-title fw-semibold mb-4">Detail Profil</h5>
        <div class="card">
            <div class="card-body">
                <div class="form-group mb-3">
                    <label for="full_name" class="form-label">Nama Lengkap</label>
                    <input type="text"
                        class="form-control <?= isset($errors['full_name']) ? 'is-invalid' : '' ?>"
                        name="full_name"
                        id="full_name"
                        value="<?= htmlspecialchars($full_name) ?>"
                        placeholder="Masukkan nama lengkap (opsional)">
                    <?php if (isset($errors['full_name'])): ?>
                        <div class="invalid-feedback">
                            <?= htmlspecialchars($errors['full_name']) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="form-group mb-3">
                    <label for="username" class="form-label">Nama Pengguna <span class="text-danger">*</span></label>
                    <input type="text"
                        class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>"
                        name="username"
                        id="username"
                        value="<?= htmlspecialchars($username) ?>"
                        required>
                    <?php if (isset($errors['username'])): ?>
                        <div class="invalid-feedback">
                            <?= htmlspecialchars($errors['username']) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="form-group mb-3">
                    <label for="email" class="form-label">Surel <span class="text-danger">*</span></label>
                    <input type="email"
                        class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                        name="email"
                        id="email"
                        value="<?= htmlspecialchars($email) ?>"
                        required>
                    <?php if (isset($errors['email'])): ?>
                        <div class="invalid-feedback">
                            <?= htmlspecialchars($errors['email']) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="d-flex gap-2 align-items-center justify-content-end mt-4">
                    <a href="/apotek-alifa/layouts/dashboard?page=profile.index" class="btn btn-outline-secondary">
                        <i class="ti ti-arrow-left me-1"></i>
                        Kembali
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-device-floppy me-1"></i>
                        Simpan Perubahan
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<style>
    .card {
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .radio-input {
        display: none;
    }

    .avatar-label {
        display: block;
        cursor: pointer;
        padding: 5px;
        border: 2px solid transparent;
        border-radius: 50%;
        transition: all 0.3s ease;
    }

    .avatar-label:hover {
        border-color: #5D87FF;
        transform: scale(1.1);
    }

    .radio-input:checked+.avatar-label {
        border-color: #5D87FF;
        box-shadow: 0 0 10px rgba(93, 135, 255, 0.3);
    }

    .avatar-label img {
        display: block;
        width: 100%;
        height: 100%;
    }

    #imgPreview {
        transition: all 0.3s ease;
    }

    .form-control:focus {
        border-color: #5D87FF;
        box-shadow: 0 0 0 0.2rem rgba(93, 135, 255, 0.25);
    }

    .btn-primary {
        background-color: #5D87FF;
        border-color: #5D87FF;
    }

    .btn-primary:hover {
        background-color: #4A6FDB;
        border-color: #4A6FDB;
    }
</style>

<script>
    const imgPreview = document.querySelector('#imgPreview');
    const inputRadios = document.querySelectorAll('input[type="radio"]');

    inputRadios.forEach(radio => {
        radio.addEventListener('click', () => {
            imgPreview.src = `/apotek-alifa/assets/img/profile/${radio.value}`;
        });
    });
</script>