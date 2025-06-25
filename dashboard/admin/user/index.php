<?php
require_once '../../auth_functions.php';
require_once '../../user_functions.php';
require_once '../../order_functions.php';

requireAdmin();

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$currentPage = isset($_GET['p']) ? max(1, intval($_GET['p'])) : 1;
$itemsPerPage = 5;

$result = getUsersForAdmin($search, 'user', $currentPage, $itemsPerPage);
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
                            Kelola semua pengguna dengan role User
                            <?php if ($totalItems > 0): ?>
                                <span class="badge bg-primary ms-2"><?= $totalItems ?> pengguna</span>
                            <?php endif; ?>
                        </p>
                    </div>

                    <div class="d-flex gap-3 align-items-center">
                        <form method="get" class="d-flex align-items-center gap-3" id="filterForm">
                            <input type="hidden" name="page" value="user.index">

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

                            <?php if (!empty($search)): ?>
                                <a href="?page=user.index" class="btn btn-outline-secondary">
                                    <i class="ti ti-x"></i>
                                </a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

                <?php if (!empty($search)): ?>
                    <div class="alert alert-info">
                        <i class="ti ti-info-circle me-2"></i>
                        Menampilkan <?= count($users) ?> dari <?= $totalItems ?> pengguna
                        untuk pencarian "<?= htmlspecialchars($search) ?>"
                    </div>
                <?php endif; ?>

                <div class="table-responsive mt-4">
                    <?php if (empty($users)): ?>
                        <div class="text-center py-5">
                            <i class="ti ti-users-off" style="font-size: 4rem; color: #6c757d;"></i>
                            <h5 class="mt-3 text-muted">Tidak ada pengguna ditemukan</h5>
                            <?php if (!empty($search)): ?>
                                <p class="text-muted">Coba ubah kriteria pencarian</p>
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
                                            <small><?= date('d M Y', strtotime($user['created_at'])) ?></small>
                                        </td>
                                        <td class="text-end">
                                            <button class="btn btn-sm btn-outline-info view-btn"
                                                data-user='<?= json_encode($user) ?>'>
                                                <i class="ti ti-eye"></i>
                                            </button>
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
                                                <a class="page-link" href="?page=user.index&p=<?= $currentPage - 1 ?>&search=<?= urlencode($search) ?>">
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
                                                <a class="page-link" href="?page=user.index&p=<?= $i ?>&search=<?= urlencode($search) ?>">
                                                    <?= $i ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>

                                        <?php if ($currentPage < $totalPages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=user.index&p=<?= $currentPage + 1 ?>&search=<?= urlencode($search) ?>">
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
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Pengguna</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4 text-center">
                        <img id="viewAvatar" src="" alt="" class="rounded-circle mb-3" width="100" height="100">
                        <p class="fw-bold mb-1" id="viewUsername"></p>
                        <span class="badge bg-primary">User</span>
                    </div>
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label class="form-label text-muted">Nama Lengkap</label>
                            <p class="mb-0" id="viewFullName">-</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Email</label>
                            <p class="mb-0" id="viewEmail"></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Tanggal Bergabung</label>
                            <p class="mb-0" id="viewJoinDate"></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Total Pesanan</label>
                            <p class="mb-0" id="viewTotalOrders">0 pesanan</p>
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
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.view-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const user = JSON.parse(this.getAttribute('data-user'));

                document.getElementById('viewAvatar').src = `/apotek-alifa/assets/img/profile/${user.avatar || 'user-1.svg'}`;
                document.getElementById('viewUsername').textContent = user.username;
                document.getElementById('viewFullName').textContent = user.full_name || '-';
                document.getElementById('viewEmail').textContent = user.email;

                const joinDate = new Date(user.created_at);
                document.getElementById('viewJoinDate').textContent = joinDate.toLocaleDateString('id-ID', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });

                fetch(`/apotek-alifa/get_user_orders.php?user_id=${user.id}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('viewTotalOrders').textContent = `${data.total} pesanan`;
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching user orders:', error);
                    });

                const modal = new bootstrap.Modal(document.getElementById('viewUserModal'));
                modal.show();
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

    .modal-body .form-label {
        font-weight: 600;
        margin-bottom: 0.25rem;
    }
</style>