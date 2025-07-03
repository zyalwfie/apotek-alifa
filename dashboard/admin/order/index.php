<?php
require_once '../../order_functions.php';

requireAdmin();

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status = isset($_GET['status']) ? trim($_GET['status']) : '';
$currentPage = isset($_GET['p']) ? max(1, intval($_GET['p'])) : 1;
$itemsPerPage = 10;

$result = getAllOrdersWithPagination($search, $status, $currentPage, $itemsPerPage);
$orders = $result['orders'];
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
                        <h4 class="card-title">Manajemen Pesanan</h4>
                        <p class="card-subtitle">
                            Kelola semua pesanan customer
                            <?php if ($totalItems > 0): ?>
                                <span class="badge bg-primary ms-2"><?= $totalItems ?> pesanan</span>
                            <?php endif; ?>
                        </p>
                    </div>

                    <!-- Search and Filter Form -->
                    <div class="d-flex gap-3 align-items-center">
                        <form method="get" class="d-flex align-items-center gap-3" id="filterForm">
                            <input type="hidden" name="page" value="order.index">

                            <!-- Search Input -->
                            <div class="position-relative">
                                <input type="text"
                                    class="form-control"
                                    name="search"
                                    placeholder="Cari pesanan/customer..."
                                    value="<?= htmlspecialchars($search) ?>"
                                    style="min-width: 250px;">
                                <button type="submit" class="btn btn-sm position-absolute end-0 top-50 translate-middle-y me-1">
                                    <i class="ti ti-search"></i>
                                </button>
                            </div>

                            <!-- Status Filter -->
                            <select class="form-select" name="status" style="min-width: 150px;" onchange="this.form.submit()">
                                <option value="">Semua Status</option>
                                <option value="tertunda" <?= $status === 'tertunda' ? 'selected' : '' ?>>Tertunda</option>
                                <option value="berhasil" <?= $status === 'berhasil' ? 'selected' : '' ?>>Berhasil</option>
                                <option value="gagal" <?= $status === 'gagal' ? 'selected' : '' ?>>Gagal</option>
                                <option value="selesai" <?= $status === 'selesai' ? 'selected' : '' ?>>Selesai</option>
                            </select>

                            <?php if (!empty($search) || !empty($status)): ?>
                                <a href="?page=order.index" class="btn btn-outline-secondary">
                                    <i class="ti ti-x"></i>
                                </a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

                <?php if (!empty($search) || !empty($status)): ?>
                    <div class="alert alert-info">
                        <i class="ti ti-info-circle me-2"></i>
                        Menampilkan <?= count($orders) ?> dari <?= $totalItems ?> pesanan
                        <?php if (!empty($search)): ?>
                            untuk pencarian "<?= htmlspecialchars($search) ?>"
                        <?php endif; ?>
                        <?php if (!empty($status)): ?>
                            dengan status "<?= ucfirst($status) ?>"
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="table-responsive mt-4">
                    <?php if (empty($orders)): ?>
                        <!-- Empty State -->
                        <div class="text-center py-5">
                            <i class="ti ti-shopping-cart-off" style="font-size: 4rem; color: #6c757d;"></i>
                            <h5 class="mt-3 text-muted">Tidak ada pesanan ditemukan</h5>
                            <?php if (!empty($search) || !empty($status)): ?>
                                <p class="text-muted">Coba ubah kriteria pencarian atau filter</p>
                                <a href="?page=order.index" class="btn btn-outline-primary">Lihat Semua Pesanan</a>
                            <?php else: ?>
                                <p class="text-muted">Belum ada pesanan yang masuk</p>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <table class="table mb-4 text-nowrap align-middle">
                            <thead>
                                <tr>
                                    <th scope="col">Order ID</th>
                                    <th scope="col">Customer</th>
                                    <th scope="col">Penerima</th>
                                    <th scope="col">Total</th>
                                    <th scope="col">Pembayaran</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Tanggal</th>
                                    <th scope="col" class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td>
                                            <strong>#<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></strong>
                                        </td>

                                        <td>
                                            <div>
                                                <h6 class="mb-0"><?= htmlspecialchars($order['order_username']) ?></h6>
                                                <small class="text-muted">ID: <?= $order['id_pengguna'] ?></small>
                                            </div>
                                        </td>

                                        <td>
                                            <div>
                                                <h6 class="mb-0"><?= htmlspecialchars($order['nama_penerima']) ?></h6>
                                                <small class="text-muted">
                                                    <i class="ti ti-mail align-middle"></i>
                                                    <?= htmlspecialchars($order['surel_penerima']) ?>
                                                </small>
                                            </div>
                                        </td>

                                        <td>
                                            <div>
                                                <h6 class="mb-0 text-primary">
                                                    Rp<?= number_format($order['harga_total'], 0, ',', '.') ?>
                                                </h6>
                                                <small class="text-muted">
                                                    <?= $order['item_count'] ?> item
                                                </small>
                                            </div>
                                        </td>

                                        <td>
                                            <?php if (!empty($order['bukti_pembayaran'])): ?>
                                                <span class="badge bg-success-subtle text-success">
                                                    <i class="ti ti-check"></i> Sudah Upload
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-warning-subtle text-warning">
                                                    <i class="ti ti-clock"></i> Belum Upload
                                                </span>
                                            <?php endif; ?>
                                        </td>

                                        <td>
                                            <?php
                                            $badge_info = getOrderStatusBadge($order['status']);
                                            ?>
                                            <span class="badge bg-<?= $badge_info['class'] ?>">
                                                <i class="ti ti-<?= $badge_info['icon'] ?> me-1"></i>
                                                <?= $badge_info['text'] ?>
                                            </span>
                                        </td>

                                        <td>
                                            <small><?= date('d M Y', strtotime($order['waktu_dibuat'])) ?></small>
                                            <br>
                                            <small class="text-muted"><?= date('H:i', strtotime($order['waktu_dibuat'])) ?></small>
                                        </td>

                                        <td class="text-end">
                                            <a href="/apotek-alifa/layouts/dashboard?page=order.show&order_id=<?= $order['id'] ?>"
                                                class="btn btn-sm btn-outline-primary">
                                                <i class="ti ti-eye"></i> Detail
                                            </a>
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
                                        Menampilkan <?= count($orders) ?> dari <?= $totalItems ?> pesanan
                                    </small>
                                </div>

                                <nav aria-label="Order pagination">
                                    <ul class="pagination pagination-sm mb-0">
                                        <!-- Previous Page -->
                                        <?php if ($currentPage > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="<?= buildOrderPaginationUrl($currentPage - 1, $search, $status) ?>">
                                                    <i class="ti ti-chevron-left"></i>
                                                </a>
                                            </li>
                                        <?php else: ?>
                                            <li class="page-item disabled">
                                                <span class="page-link"><i class="ti ti-chevron-left"></i></span>
                                            </li>
                                        <?php endif; ?>

                                        <!-- Page Numbers -->
                                        <?php
                                        $startPage = max(1, $currentPage - 2);
                                        $endPage = min($totalPages, $currentPage + 2);

                                        if ($startPage > 1):
                                        ?>
                                            <li class="page-item">
                                                <a class="page-link" href="<?= buildOrderPaginationUrl(1, $search, $status) ?>">1</a>
                                            </li>
                                            <?php if ($startPage > 2): ?>
                                                <li class="page-item disabled">
                                                    <span class="page-link">...</span>
                                                </li>
                                            <?php endif; ?>
                                        <?php endif; ?>

                                        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                                            <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                                                <a class="page-link" href="<?= buildOrderPaginationUrl($i, $search, $status) ?>"><?= $i ?></a>
                                            </li>
                                        <?php endfor; ?>

                                        <?php if ($endPage < $totalPages): ?>
                                            <?php if ($endPage < $totalPages - 1): ?>
                                                <li class="page-item disabled">
                                                    <span class="page-link">...</span>
                                                </li>
                                            <?php endif; ?>
                                            <li class="page-item">
                                                <a class="page-link" href="<?= buildOrderPaginationUrl($totalPages, $search, $status) ?>"><?= $totalPages ?></a>
                                            </li>
                                        <?php endif; ?>

                                        <!-- Next Page -->
                                        <?php if ($currentPage < $totalPages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="<?= buildOrderPaginationUrl($currentPage + 1, $search, $status) ?>">
                                                    <i class="ti ti-chevron-right"></i>
                                                </a>
                                            </li>
                                        <?php else: ?>
                                            <li class="page-item disabled">
                                                <span class="page-link"><i class="ti ti-chevron-right"></i></span>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            </div>
                        <?php else: ?>
                            <div class="text-center mt-3">
                                <small class="text-muted">
                                    Menampilkan semua <?= $totalItems ?> pesanan
                                </small>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.75rem;
    }

    .table td {
        vertical-align: middle;
        padding: 1rem 0.5rem;
    }

    .table th {
        font-weight: 600;
        color: #5a5a5a;
    }

    .pagination .page-link {
        padding: 0.5rem 0.75rem;
    }

    .pagination-sm .page-link {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }

    .alert {
        border: none;
        border-radius: 8px;
    }

    .btn-outline-primary:hover {
        transform: translateY(-1px);
        transition: all 0.2s ease;
    }

    @media (max-width: 768px) {
        .d-md-flex {
            flex-direction: column;
            align-items: stretch !important;
            gap: 1rem;
        }

        .d-flex.gap-3 {
            flex-direction: column;
            gap: 0.5rem !important;
        }

        .form-control,
        .form-select {
            min-width: auto !important;
        }

        .table {
            font-size: 0.875rem;
        }
    }
</style>

<script>
    document.querySelector('input[name="search"]').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            this.form.submit();
        }
    });
</script>