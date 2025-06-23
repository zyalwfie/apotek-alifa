<?php
require_once '../../auth_functions.php';

requireLogin();

$user = getUserData($_SESSION['user_id']);

$success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
unset($_SESSION['success_message']);
?>

<div class="row">
    <div class="col-lg-4">
        <h5 class="card-title fw-semibold mb-3">Avatar</h5>
        <div class="card py-4">
            <img id="imgPreview" src="/apotek-alifa/assets/img/profile/<?= htmlspecialchars($user['avatar'] ?: 'user-1.svg') ?>"
                alt="<?= htmlspecialchars($user['username']) ?>"
                style="width: 81%; margin: auto;">
        </div>
    </div>
    <div class="col-lg-8">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="card-title fw-semibold">Detail Profil</h5>
            <?php if ($success_message): ?>
                <div class="alert alert-success d-flex align-items-center gap-2 alert-dismissible fade show" role="alert">
                    <i class="ti ti-checks"></i>
                    <?= htmlspecialchars($success_message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="form-group mb-3">
                    <label for="full_name" class="form-label">Nama Lengkap</label>
                    <input type="text"
                        class="form-control"
                        disabled
                        value="<?= htmlspecialchars($user['full_name'] ?: '') ?>"
                        placeholder="Belum ada nama lengkap">
                </div>
                <div class="form-group mb-3">
                    <label for="username" class="form-label">Nama Pengguna</label>
                    <input type="text"
                        class="form-control"
                        disabled
                        value="<?= htmlspecialchars($user['username']) ?>">
                </div>
                <div class="form-group mb-3">
                    <label for="email" class="form-label">Surel</label>
                    <input type="email"
                        class="form-control"
                        disabled
                        value="<?= htmlspecialchars($user['email']) ?>">
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .card {
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .form-control:disabled {
        background-color: #f8f9fa;
        cursor: not-allowed;
    }

    #imgPreview {
        border-radius: 50%;
        object-fit: cover;
    }
</style>