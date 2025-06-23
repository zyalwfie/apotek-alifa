<?php
require_once '../../auth_functions.php';
require_once '../../user_functions.php';
require_once '../../order_functions.php';

requireAdmin();

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$role = isset($_GET['role']) ? trim($_GET['role']) : '';
$currentPage = isset($_GET['p']) ? max(1, intval($_GET['p'])) : 1;
$itemsPerPage = 5;

$result = getUsersForAdmin($search, $role, $currentPage, $itemsPerPage);
$users = $result['users'];
$totalPages = $result['total_pages'];
$totalItems = $result['total'];

$success_message = isset($_SESSION['success']) ? $_SESSION['success'] : '';
$error_message = isset($_SESSION['error']) ? $_SESSION['error'] : '';
unset($_SESSION['success'], $_SESSION['error']);
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <?php if ($success_message): ?>
                    <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                        <i class="ti ti-check me-2"></i><?= htmlspecialchars($success_message) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($error_message): ?>
                    <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                        <i class="ti ti-x me-2"></i><?= htmlspecialchars($error_message) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="d-md-flex align-items-center justify-content-between mb-4">
                    <div>
                        <h4 class="card-title">Manajemen Pengguna</h4>
                        <p class="card-subtitle">
                            Kelola semua pengguna sistem
                            <?php if ($totalItems > 0): ?>
                                <span class="badge bg-primary ms-2"><?= $totalItems ?> pengguna</span>
                            <?php endif; ?>
                        </p>
                    </div>

                    <div class="d-flex gap-3 align-items-center">
                        <!-- Add User Button -->
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                            <i class="ti ti-user-plus me-2"></i>Tambah
                        </button>

                        <!-- Filter Form -->
                        <form method="get" class="d-flex align-items-center gap-3" id="filterForm">
                            <input type="hidden" name="page" value="user.index">

                            <!-- Role Filter -->
                            <select class="form-select" name="role" style="min-width: 150px;" onchange="this.form.submit()">
                                <option value="">Semua Role</option>
                                <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Admin</option>
                                <option value="user" <?= $role === 'user' ? 'selected' : '' ?>>User</option>
                            </select>

                            <!-- Search Input -->
                            <div class="position-relative">
                                <input type="text"
                                    class="form-control"
                                    name="search"
                                    placeholder="Cari pengguna..."
                                    value="<?= htmlspecialchars($search) ?>"
                                    style="min-width: 250px;">
                                <button type="submit" class="btn btn-sm position-absolute end-0 top-50 translate-middle-y me-1">
                                    <i class="ti ti-search"></i>
                                </button>
                            </div>
                        </form>

                        <?php if (!empty($search) || !empty($role)): ?>
                            <a href="?page=user.index" class="btn btn-outline-secondary">
                                <i class="ti ti-x"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (!empty($search) || !empty($role)): ?>
                    <div class="alert alert-info">
                        <i class="ti ti-info-circle me-2"></i>
                        Menampilkan <?= count($users) ?> dari <?= $totalItems ?> pengguna
                        <?php if (!empty($search)): ?>
                            untuk pencarian "<?= htmlspecialchars($search) ?>"
                        <?php endif; ?>
                        <?php if (!empty($role)): ?>
                            dengan role "<?= ucfirst($role) ?>"
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="table-responsive mt-4">
                    <?php if (empty($users)): ?>
                        <div class="text-center py-5">
                            <i class="ti ti-users-off" style="font-size: 4rem; color: #6c757d;"></i>
                            <h5 class="mt-3 text-muted">Tidak ada pengguna ditemukan</h5>
                            <?php if (!empty($search) || !empty($role)): ?>
                                <p class="text-muted">Coba ubah kriteria pencarian atau filter</p>
                                <a href="?page=user.index" class="btn btn-outline-primary">Lihat Semua Pengguna</a>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <table class="table mb-4 text-nowrap align-middle">
                            <thead>
                                <tr>
                                    <th scope="col">ID</th>
                                    <th scope="col">Avatar</th>
                                    <th scope="col">Pengguna</th>
                                    <th scope="col">Email</th>
                                    <th scope="col">Role</th>
                                    <th scope="col">Terdaftar</th>
                                    <th scope="col" class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr id="user-row-<?= $user['id'] ?>">
                                        <td>
                                            <strong>#<?= str_pad($user['id'], 4, '0', STR_PAD_LEFT) ?></strong>
                                        </td>
                                        <td>
                                            <img src="/apotek-alifa/assets/img/profile/<?= htmlspecialchars($user['avatar'] ?: 'user-1.svg') ?>"
                                                alt="<?= htmlspecialchars($user['username']) ?>"
                                                width="40" height="40"
                                                class="rounded-circle">
                                        </td>
                                        <td>
                                            <h6 class="mb-0"><?= htmlspecialchars($user['username']) ?></h6>
                                            <?php if ($user['full_name']): ?>
                                                <small class="text-muted"><?= htmlspecialchars($user['full_name']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($user['email']) ?>
                                        </td>
                                        <td>
                                            <?php if ($user['role'] === 'admin'): ?>
                                                <span class="badge bg-danger">Admin</span>
                                            <?php else: ?>
                                                <span class="badge bg-primary">User</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <small><?= date('d M Y', strtotime($user['created_at'])) ?></small>
                                        </td>
                                        <td class="text-end">
                                            <button class="btn btn-sm btn-outline-primary edit-btn"
                                                data-user='<?= json_encode($user) ?>'>
                                                <i class="ti ti-pencil"></i>
                                            </button>
                                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                <button class="btn btn-sm btn-outline-danger delete-btn"
                                                    data-id="<?= $user['id'] ?>"
                                                    data-name="<?= htmlspecialchars($user['username']) ?>">
                                                    <i class="ti ti-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <!-- Pagination -->
                        <?php if ($totalPages > 1): ?>
                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <div>
                                    <small class="text-muted">
                                        Menampilkan <?= count($users) ?> dari <?= $totalItems ?> pengguna
                                    </small>
                                </div>

                                <nav aria-label="User pagination">
                                    <ul class="pagination pagination-sm mb-0">
                                        <?php if ($currentPage > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=user.index&p=<?= $currentPage - 1 ?>&search=<?= urlencode($search) ?>&role=<?= urlencode($role) ?>">
                                                    <i class="ti ti-chevron-left"></i>
                                                </a>
                                            </li>
                                        <?php endif; ?>

                                        <?php
                                        $startPage = max(1, $currentPage - 2);
                                        $endPage = min($totalPages, $currentPage + 2);
                                        ?>

                                        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                                            <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                                                <a class="page-link" href="?page=user.index&p=<?= $i ?>&search=<?= urlencode($search) ?>&role=<?= urlencode($role) ?>">
                                                    <?= $i ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>

                                        <?php if ($currentPage < $totalPages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=user.index&p=<?= $currentPage + 1 ?>&search=<?= urlencode($search) ?>&role=<?= urlencode($role) ?>">
                                                    <i class="ti ti-chevron-right"></i>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Pengguna Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addUserForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="username" required>
                        <small class="text-muted">Minimal 3 karakter, hanya huruf, angka, dan underscore</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" name="full_name">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" name="password" required>
                        <small class="text-muted">Minimal 6 karakter</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select class="form-select" name="role">
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-user-plus me-1"></i>Tambah Pengguna
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Pengguna</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editUserForm">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="username" id="edit_username" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" name="email" id="edit_email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" name="full_name" id="edit_full_name">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password Baru</label>
                        <input type="password" class="form-control" name="password">
                        <small class="text-muted">Kosongkan jika tidak ingin mengubah password</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select class="form-control" name="role" id="edit_role">
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-device-floppy me-1"></i>Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="toast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <i class="ti ti-bell me-2"></i>
            <strong class="me-auto">Notifikasi</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body"></div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Add User
        document.getElementById('addUserForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="ti ti-loader me-1"></i>Menyimpan...';

            fetch('/apotek-alifa/user_handler.php?action=add', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showToast(data.message || 'Gagal menambah pengguna', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Terjadi kesalahan sistem', 'error');
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="ti ti-user-plus me-1"></i>Tambah Pengguna';
                });
        });

        // Edit User
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const user = JSON.parse(this.getAttribute('data-user'));

                document.getElementById('edit_id').value = user.id;
                document.getElementById('edit_username').value = user.username;
                document.getElementById('edit_email').value = user.email;
                document.getElementById('edit_full_name').value = user.full_name || '';
                document.getElementById('edit_role').value = user.role;

                const modal = new bootstrap.Modal(document.getElementById('editUserModal'));
                modal.show();
            });
        });

        document.getElementById('editUserForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="ti ti-loader me-1"></i>Menyimpan...';

            fetch('/apotek-alifa/user_handler.php?action=edit', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showToast(data.message || 'Gagal mengubah pengguna', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Terjadi kesalahan sistem', 'error');
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="ti ti-device-floppy me-1"></i>Simpan Perubahan';
                });
        });

        // Delete User
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');

                if (confirm(`Apakah Anda yakin ingin menghapus pengguna "${name}"?`)) {
                    fetch('/apotek-alifa/user_handler.php?action=delete', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `id=${id}`
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showToast(data.message, 'success');
                                document.getElementById(`user-row-${id}`).remove();
                            } else {
                                showToast(data.message || 'Gagal menghapus pengguna', 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            showToast('Terjadi kesalahan sistem', 'error');
                        });
                }
            });
        });
    });

    function showToast(message, type = 'info') {
        const toast = document.getElementById('toast');
        const toastBody = toast.querySelector('.toast-body');

        toastBody.textContent = message;
        toast.className = `toast bg-${type === 'success' ? 'success' : 'danger'} text-white`;

        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();
    }
</script>

<style>
    .badge {
        font-size: 0.75rem;
        padding: 0.35rem 0.65rem;
    }

    .btn-sm {
        padding: 0.375rem 0.75rem;
    }

    .rounded-circle {
        border: 2px solid #e9ecef;
    }

    .toast {
        min-width: 300px;
    }
</style>