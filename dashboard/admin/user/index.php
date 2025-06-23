<?php
require_once '../../user_functions.php';
require_once '../../order_functions.php';

requireAdmin();

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$role = isset($_GET['role']) ? trim($_GET['role']) : '';
$currentPage = isset($_GET['p']) ? max(1, intval($_GET['p'])) : 1;
$itemsPerPage = 10;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $response = deleteUser($_POST['user_id'], $_SESSION['user_id']);

    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    $_SESSION[$response['success'] ? 'success' : 'error'] = $response['message'];
    header('Location: ?page=user.index');
    exit;
}

$result = getAllUsersWithPagination($search, $role, $currentPage, $itemsPerPage);
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
                        <h4 class="card-title">Kelola User</h4>
                        <p class="card-subtitle">
                            Manajemen semua pengguna sistem
                            <?php if ($totalItems > 0): ?>
                                <span class="badge bg-primary ms-2"><?= $totalItems ?> user</span>
                            <?php endif; ?>
                        </p>
                    </div>

                    <div class="d-flex gap-3 align-items-center">
                        <form method="get" class="d-flex align-items-center gap-3">
                            <input type="hidden" name="page" value="user.index">

                            <div class="position-relative">
                                <input type="text"
                                    class="form-control"
                                    name="search"
                                    placeholder="Cari user..."
                                    value="<?= htmlspecialchars($search) ?>"
                                    style="min-width: 250px;">
                                <button type="submit" class="btn btn-sm position-absolute end-0 top-50 translate-middle-y me-1">
                                    <i class="ti ti-search"></i>
                                </button>
                            </div>

                            <select class="form-select" name="role" style="min-width: 150px;" onchange="this.form.submit()">
                                <option value="">Semua Role</option>
                                <option value="user" <?= $role === 'user' ? 'selected' : '' ?>>User</option>
                                <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Admin</option>
                            </select>

                            <?php if (!empty($search) || !empty($role)): ?>
                                <a href="?page=user.index" class="btn btn-outline-secondary">
                                    <i class="ti ti-x"></i>
                                </a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

                <div class="table-responsive mt-4">
                    <?php if (empty($users)): ?>
                        <div class="text-center py-5">
                            <i class="ti ti-users-off" style="font-size: 4rem; color: #6c757d;"></i>
                            <h5 class="mt-3 text-muted">Tidak ada user ditemukan</h5>
                            <?php if (!empty($search) || !empty($role)): ?>
                                <p class="text-muted">Coba ubah kriteria pencarian atau filter</p>
                                <a href="?page=user.index" class="btn btn-outline-primary">Lihat Semua User</a>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <table class="table mb-4 text-nowrap align-middle">
                            <thead>
                                <tr>
                                    <th scope="col">User</th>
                                    <th scope="col">Email</th>
                                    <th scope="col">Role</th>
                                    <th scope="col">Total Pesanan</th>
                                    <th scope="col">Total Belanja</th>
                                    <th scope="col">Terdaftar</th>
                                    <th scope="col" class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="/apotek-alifa/assets/img/profile/<?= htmlspecialchars($user['avatar'] ?: 'user-1.svg') ?>"
                                                    alt="<?= htmlspecialchars($user['username']) ?>"
                                                    class="rounded-circle me-3"
                                                    style="width: 40px; height: 40px; object-fit: cover;">
                                                <div>
                                                    <h6 class="mb-0"><?= htmlspecialchars($user['username']) ?></h6>
                                                    <small class="text-muted">
                                                        <?= htmlspecialchars($user['full_name'] ?: 'Nama tidak tersedia') ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars($user['email']) ?></td>
                                        <td>
                                            <?php if ($user['role'] === 'admin'): ?>
                                                <span class="badge bg-danger">Admin</span>
                                            <?php else: ?>
                                                <span class="badge bg-info">User</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="text-muted"><?= $user['total_orders'] ?: 0 ?></span>
                                        </td>
                                        <td>
                                            <?php if ($user['total_spent']): ?>
                                                <strong>Rp<?= number_format($user['total_spent'], 0, ',', '.') ?></strong>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <small><?= date('d M Y', strtotime($user['created_at'])) ?></small>
                                        </td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-outline-info"
                                                onclick="viewUser(<?= $user['id'] ?>)">
                                                <i class="ti ti-eye"></i>
                                            </button>
                                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                <button type="button" class="btn btn-sm btn-outline-danger"
                                                    onclick="deleteUser(<?= $user['id'] ?>, '<?= htmlspecialchars($user['username']) ?>')">
                                                    <i class="ti ti-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <!-- PAGINATION -->
                        <?php if ($totalPages > 1): ?>
                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <div>
                                    <small class="text-muted">
                                        Menampilkan <?= count($users) ?> dari <?= $totalItems ?> user
                                    </small>
                                </div>

                                <nav aria-label="User pagination">
                                    <ul class="pagination pagination-sm mb-0">
                                        <?php if ($currentPage > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="<?= buildUserPaginationUrl($currentPage - 1, $search, $role) ?>">
                                                    <i class="ti ti-chevron-left"></i>
                                                </a>
                                            </li>
                                        <?php endif; ?>

                                        <?php
                                        $startPage = max(1, $currentPage - 2);
                                        $endPage = min($totalPages, $currentPage + 2);

                                        for ($i = $startPage; $i <= $endPage; $i++):
                                        ?>
                                            <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                                                <a class="page-link" href="<?= buildUserPaginationUrl($i, $search, $role) ?>"><?= $i ?></a>
                                            </li>
                                        <?php endfor; ?>

                                        <?php if ($currentPage < $totalPages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="<?= buildUserPaginationUrl($currentPage + 1, $search, $role) ?>">
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

<!-- View User Modal -->
<div class="modal fade" id="viewUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4 text-center">
                        <img id="viewAvatar" src="" alt="" class="rounded-circle mb-3" style="width: 120px; height: 120px; object-fit: cover;">
                        <h5 id="viewUsername"></h5>
                        <p class="text-muted" id="viewFullName"></p>
                        <div id="viewRole"></div>
                    </div>
                    <div class="col-md-8">
                        <div class="row mb-3">
                            <div class="col-6">
                                <small class="text-muted">Email</small>
                                <p class="mb-0" id="viewEmail"></p>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Terdaftar Sejak</small>
                                <p class="mb-0" id="viewCreatedAt"></p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-6">
                                <small class="text-muted">Total Pesanan</small>
                                <p class="mb-0 fw-bold" id="viewTotalOrders"></p>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Pesanan Berhasil</small>
                                <p class="mb-0 fw-bold text-success" id="viewSuccessfulOrders"></p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-6">
                                <small class="text-muted">Total Belanja</small>
                                <p class="mb-0 fw-bold text-primary" id="viewTotalSpent"></p>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Pesanan Terakhir</small>
                                <p class="mb-0" id="viewLastOrder"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
    // View user details
    function viewUser(userId) {
        fetch(`/apotek-alifa/dashboard/admin/user/get_user.php?id=${userId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const user = data.user;
                    document.getElementById('viewUsername').textContent = user.username;
                    document.getElementById('viewFullName').textContent = user.full_name || 'Nama tidak tersedia';
                    document.getElementById('viewEmail').textContent = user.email;
                    document.getElementById('viewCreatedAt').textContent = new Date(user.created_at).toLocaleDateString('id-ID', {
                        day: 'numeric',
                        month: 'long',
                        year: 'numeric'
                    });
                    document.getElementById('viewRole').innerHTML = user.role === 'admin' ?
                        '<span class="badge bg-danger">Admin</span>' :
                        '<span class="badge bg-info">User</span>';
                    document.getElementById('viewAvatar').src = '/apotek-alifa/assets/img/profile/' + (user.avatar || 'user-1.svg');

                    // Order statistics
                    document.getElementById('viewTotalOrders').textContent = user.total_orders || '0';
                    document.getElementById('viewSuccessfulOrders').textContent = user.successful_orders || '0';
                    document.getElementById('viewTotalSpent').textContent = user.total_spent ?
                        'Rp' + new Intl.NumberFormat('id-ID').format(user.total_spent) :
                        'Rp0';
                    document.getElementById('viewLastOrder').textContent = user.last_order_date ?
                        new Date(user.last_order_date).toLocaleDateString('id-ID') :
                        'Belum ada pesanan';

                    new bootstrap.Modal(document.getElementById('viewUserModal')).show();
                }
            });
    }

    // Delete user
    function deleteUser(userId, username) {
        if (confirm(`Apakah Anda yakin ingin menghapus user "${username}"?\n\nPeringatan: User yang sudah memiliki pesanan tidak dapat dihapus.`)) {
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('user_id', userId);

            fetch('?page=user.index', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message);
                    }
                });
        }
    }
</script>

<style>
    .badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.75rem;
    }

    .table td {
        vertical-align: middle;
    }

    .btn-sm {
        padding: 0.25rem 0.5rem;
    }

    .modal-body .row {
        --bs-gutter-y: 1rem;
    }
</style>