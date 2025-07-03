<?php
require_once '../../order_functions.php';

requireAdmin();

$stats = getDashboardStats();
$recentOrders = getRecentOrdersForDashboard();
?>

<div class="row">
    <div class="col-lg-8">
        <div class="card overflow-hidden">
            <div class="card-body pb-0">
                <div class="d-flex align-items-start">
                    <div>
                        <h4 class="card-title">Status Terkini</h4>
                        <p class="card-subtitle">Informasi tentang kegiatanmu</p>
                    </div>
                </div>
                <div class="mt-4 pb-3 d-flex align-items-center">
                    <span class="btn btn-primary rounded-circle round-48 hstack justify-content-center">
                        <i class="ti ti-moneybag"></i>
                    </span>
                    <div class="ms-3">
                        <h5 class="mb-0 fw-bolder fs-4">Total Pendapatan</h5>
                        <span class="text-muted fs-3">Total rupiah dari pesanan berhasil</span>
                    </div>
                    <div class="ms-auto">
                        <span class="badge bg-info-subtle text-muted"><?= formatRupiah($stats['total_revenue']) ?></span>
                    </div>
                </div>
                <div class="py-3 d-flex align-items-center">
                    <span class="btn btn-warning rounded-circle round-48 hstack justify-content-center">
                        <i class="ti ti-clock-hour-12"></i>
                    </span>
                    <div class="ms-3">
                        <h5 class="mb-0 fw-bolder fs-4">Total Pesanan yang Tertunda</h5>
                        <span class="text-muted fs-3">Status pesanan yang masih tertunda</span>
                    </div>
                    <div class="ms-auto">
                        <span class="badge bg-warning-subtle text-muted"><?= $stats['pending_orders'] ?></span>
                    </div>
                </div>
                <div class="py-3 d-flex align-items-center">
                    <span class="btn btn-success rounded-circle round-48 hstack justify-content-center">
                        <i class="ti ti-checks fs-6"></i>
                    </span>
                    <div class="ms-3">
                        <h5 class="mb-0 fw-bolder fs-4">Total Pesanan yang Berhasil</h5>
                        <span class="text-muted fs-3">Status pesanan yang sudah berhasil</span>
                    </div>
                    <div class="ms-auto">
                        <span class="badge bg-success-subtle text-muted"><?= $stats['successful_orders'] ?></span>
                    </div>
                </div>
                <div class="pt-3 mb-7 d-flex align-items-center">
                    <span class="btn btn-danger rounded-circle round-48 hstack justify-content-center">
                        <i class="ti ti-x fs-6"></i>
                    </span>
                    <div class="ms-3">
                        <h5 class="mb-0 fw-bolder fs-4">Total Pesanan yang Gagal</h5>
                        <span class="text-muted fs-3">Status pesanan yang dibatalkan/gagal</span>
                    </div>
                    <div class="ms-auto">
                        <span class="badge bg-danger-subtle text-muted"><?= $stats['failed_orders'] ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card pt-4">
            <img src="/apotek-alifa/assets/img/profile/user-1.svg" style="width: 81%; margin: auto;">
            <div class="card-body">
                <div class="d-flex gap-2 justify-content-center align-items-center">
                    <div>
                        <i class="ti ti-user-check"></i>
                        <span><?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?></span>
                    </div>
                    <div>
                        <i class="ti ti-mail"></i>
                        <span><?= htmlspecialchars($_SESSION['email'] ?? 'admin@example.com') ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-md-flex align-items-center">
                    <div>
                        <h4 class="card-title">Pesanan Terbaru</h4>
                        <p class="card-subtitle">
                            Daftar pesanan terbaru dari pengguna
                        </p>
                    </div>
                    <div class="ms-auto">
                        <a href="?page=admin.orders" class="btn btn-primary btn-sm">
                            <i class="ti ti-eye me-1"></i>Lihat Semua
                        </a>
                    </div>
                </div>
                <div class="table-responsive mt-4">
                    <table class="table mb-0 text-nowrap varient-table align-middle fs-3">
                        <thead>
                            <tr>
                                <th scope="col" class="px-0 text-muted">
                                    Pemesan
                                </th>
                                <th scope="col" class="px-0 text-muted">
                                    Status
                                </th>
                                <th scope="col" class="px-0 text-muted text-end">
                                    Total Pembayaran
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentOrders)): ?>
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">
                                        Belum ada pesanan yang dibuat
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recentOrders as $order): ?>
                                    <tr>
                                        <td class="px-0">
                                            <div class="d-flex align-items-center">
                                                <img src="/apotek-alifa/assets/img/profile/user-1.svg"
                                                    class="rounded-circle" width="40" alt="Avatar" />
                                                <div class="ms-3">
                                                    <h6 class="mb-0 fw-bolder">
                                                        <?= htmlspecialchars($order['nama_penerima']) ?>
                                                    </h6>
                                                    <span class="text-muted">
                                                        <?= htmlspecialchars($order['nomor_telepon_penerima']) ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-0">
                                            <?php
                                            $statusInfo = getOrderStatusBadge($order['status']);
                                            ?>
                                            <span class="badge bg-<?= $statusInfo['class'] ?>">
                                                <?= $statusInfo['text'] ?>
                                            </span>
                                        </td>
                                        <td class="px-0 text-dark fw-medium text-end">
                                            <?= formatRupiah($order['harga_total']) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>