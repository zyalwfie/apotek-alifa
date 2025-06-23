<?php
require_once '../../order_functions.php';

requireAdmin();

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if (!$order_id) {
    header('Location: ?page=order.index');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $admin_id = $_SESSION['user_id'];

    if ($action === 'approve' && isset($_POST['order_id'])) {
        if (updateOrderStatus($order_id, 'berhasil', $admin_id)) {
            $_SESSION['success'] = 'Pesanan berhasil disetujui';
        } else {
            $_SESSION['error'] = 'Gagal menyetujui pesanan';
        }
    } elseif ($action === 'cancel' && isset($_POST['order_id'])) {
        if (updateOrderStatus($order_id, 'gagal', $admin_id)) {
            $_SESSION['success'] = 'Pesanan berhasil dibatalkan';
        } else {
            $_SESSION['error'] = 'Gagal membatalkan pesanan';
        }
    } elseif ($action === 'complete' && isset($_POST['order_id'])) {
        if (updateOrderStatus($order_id, 'selesai', $admin_id)) {
            $_SESSION['success'] = 'Pesanan berhasil diselesaikan';
        } else {
            $_SESSION['error'] = 'Gagal menyelesaikan pesanan';
        }
    }

    header('Location: ?page=order.show&order_id=' . $order_id);
    exit;
}

$order = getOrderByIdForAdmin($order_id);

if (!$order) {
    $_SESSION['error'] = 'Pesanan tidak ditemukan';
    header('Location: ?page=order.index');
    exit;
}

$conn = connectDB();
$query = "SELECT oi.*, p.name, p.price, p.image 
          FROM order_items oi 
          JOIN products p ON oi.product_id = p.id 
          WHERE oi.order_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order_items = [];
while ($row = $result->fetch_assoc()) {
    $order_items[] = $row;
}
$stmt->close();

$query = "SELECT * FROM payments WHERE order_id = ? ORDER BY created_at DESC LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$payment_result = $stmt->get_result();
$payment = $payment_result->fetch_assoc();
$stmt->close();
$conn->close();
?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="?page=order.index">Pesanan</a></li>
        <li class="breadcrumb-item active">Detail #<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></li>
    </ol>
</nav>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1 text-gray-800">Detail Pesanan #<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></h1>
        <p class="mb-0">Customer: <strong><?= htmlspecialchars($order['order_username']) ?></strong> (<?= htmlspecialchars($order['user_email']) ?>)</p>
    </div>
    <div>
        <?php
        $badge_info = getOrderStatusBadge($order['status']);
        ?>
        <span class="badge bg-<?= $badge_info['class'] ?> fs-6">
            <i class="ti ti-<?= $badge_info['icon'] ?> me-1"></i>
            <?= $badge_info['text'] ?>
        </span>
    </div>
</div>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="ti ti-check me-2"></i><?= $_SESSION['success'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="ti ti-x me-2"></i><?= $_SESSION['error'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<div class="row">
    <!-- Customer & Shipping Info -->
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-light">
                <h5 class="mb-0">Informasi Customer & Pengiriman</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="text-muted small">Customer</label>
                        <p class="mb-2"><strong><?= htmlspecialchars($order['order_username']) ?></strong></p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Email Customer</label>
                        <p class="mb-2"><?= htmlspecialchars($order['user_email']) ?></p>
                    </div>
                </div>

                <hr>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="text-muted small">Nama Penerima</label>
                        <p class="mb-2"><strong><?= htmlspecialchars($order['recipient_name']) ?></strong></p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Email Penerima</label>
                        <p class="mb-2"><?= htmlspecialchars($order['recipient_email']) ?></p>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="text-muted small">Nomor Telepon</label>
                    <p class="mb-2"><?= htmlspecialchars($order['recipient_phone']) ?></p>
                </div>

                <div class="mb-3">
                    <label class="text-muted small">Alamat Pengiriman</label>
                    <p class="mb-2"><?= htmlspecialchars($order['street_address']) ?></p>
                </div>

                <div class="mb-0">
                    <label class="text-muted small">Catatan</label>
                    <p class="mb-0"><?= htmlspecialchars($order['notes'] ?: 'Tidak ada catatan') ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Details -->
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-light">
                <h5 class="mb-0">Detail Pesanan</h5>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    <i class="ti ti-calendar me-1"></i>
                    Tanggal Pesanan: <?= formatOrderDate($order['created_at']) ?>
                </p>

                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Harga</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($order_items as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['name']) ?></td>
                                    <td class="text-center"><?= $item['quantity'] ?></td>
                                    <td class="text-end">Rp<?= number_format($item['price'], 0, ',', '.') ?></td>
                                    <td class="text-end">Rp<?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                <td class="text-end">
                                    <strong class="text-primary">Rp<?= number_format($order['total_price'], 0, ',', '.') ?></strong>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payment Proof & Actions -->
<div class="row">
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0">Bukti Pembayaran</h5>
            </div>
            <div class="card-body">
                <?php if ($payment && !empty($payment['proof_of_payment'])): ?>
                    <div class="row">
                        <div class="col-md-6">
                            <img id="paymentProofImg"
                                src="/apotek-alifa/assets/img/payments/<?= htmlspecialchars($payment['proof_of_payment']) ?>"
                                alt="Bukti Pembayaran"
                                class="img-fluid rounded border"
                                style="cursor: pointer; max-height: 400px; width: 100%; object-fit: contain;">
                        </div>
                        <div class="col-md-6">
                            <div class="alert alert-info">
                                <h6 class="alert-heading">Informasi Pembayaran</h6>
                                <p class="mb-1">
                                    <strong>Status:</strong>
                                    <?php if ($order['status'] === 'berhasil'): ?>
                                        <span class="badge bg-success">Disetujui</span>
                                    <?php elseif ($order['status'] === 'gagal'): ?>
                                        <span class="badge bg-danger">Ditolak</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">Menunggu Verifikasi</span>
                                    <?php endif; ?>
                                </p>
                                <p class="mb-1">
                                    <strong>Tanggal Upload:</strong><br>
                                    <?= formatOrderDate($payment['created_at']) ?>
                                </p>
                                <p class="mb-0">
                                    <strong>File:</strong> <?= htmlspecialchars($payment['proof_of_payment']) ?>
                                </p>
                            </div>

                            <div class="d-grid gap-2">
                                <a href="/apotek-alifa/assets/img/payments/<?= htmlspecialchars($payment['proof_of_payment']) ?>"
                                    class="btn btn-outline-primary"
                                    target="_blank">
                                    <i class="ti ti-external-link me-1"></i>
                                    Buka di Tab Baru
                                </a>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning mb-0">
                        <i class="ti ti-alert-circle me-1"></i>
                        Customer belum mengunggah bukti pembayaran
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Admin Actions -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0">Aksi Admin</h5>
            </div>
            <div class="card-body">
                <?php if ($order['status'] === 'tertunda'): ?>
                    <?php if ($payment && !empty($payment['proof_of_payment'])): ?>
                        <p class="text-muted mb-3">Verifikasi pembayaran dan ubah status pesanan:</p>

                        <form method="POST" class="mb-2">
                            <input type="hidden" name="action" value="approve">
                            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                            <button type="submit" class="btn btn-success w-100"
                                onclick="return confirm('Apakah Anda yakin ingin menyetujui pesanan ini?')">
                                <i class="ti ti-check me-1"></i>
                                Setujui Pesanan
                            </button>
                        </form>

                        <form method="POST">
                            <input type="hidden" name="action" value="cancel">
                            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                            <button type="submit" class="btn btn-danger w-100"
                                onclick="return confirm('Apakah Anda yakin ingin membatalkan pesanan ini?')">
                                <i class="ti ti-x me-1"></i>
                                Batalkan Pesanan
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-info mb-0">
                            <i class="ti ti-info-circle me-1"></i>
                            Menunggu customer upload bukti pembayaran
                        </div>
                    <?php endif; ?>

                <?php elseif ($order['status'] === 'berhasil'): ?>
                    <p class="text-muted mb-3">Pesanan sudah disetujui. Anda dapat menandai sebagai selesai:</p>

                    <form method="POST">
                        <input type="hidden" name="action" value="complete">
                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                        <button type="submit" class="btn btn-primary w-100"
                            onclick="return confirm('Tandai pesanan sebagai selesai?')">
                            <i class="ti ti-circle-check me-1"></i>
                            Tandai Selesai
                        </button>
                    </form>

                <?php elseif ($order['status'] === 'gagal'): ?>
                    <div class="alert alert-danger mb-0">
                        <i class="ti ti-x me-1"></i>
                        Pesanan ini telah dibatalkan
                    </div>

                <?php elseif ($order['status'] === 'selesai'): ?>
                    <div class="alert alert-success mb-0">
                        <i class="ti ti-circle-check me-1"></i>
                        Pesanan telah selesai
                    </div>
                <?php endif; ?>

                <hr class="my-3">

                <div class="d-grid gap-2">
                    <a href="?page=order.index" class="btn btn-secondary">
                        <i class="ti ti-arrow-left me-1"></i>
                        Kembali ke Daftar
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Viewer.js for image preview -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.11.6/viewer.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.11.6/viewer.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const img = document.getElementById('paymentProofImg');
        if (img) {
            const viewer = new Viewer(img, {
                toolbar: true,
                navbar: false,
                title: true,
                movable: true,
                zoomable: true,
                scalable: true,
                transition: true,
                fullscreen: true
            });
        }
    });
</script>

<style>
    .card {
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .card-header {
        border-bottom: 1px solid #e0e0e0;
        padding: 1rem 1.25rem;
    }

    .table-sm td,
    .table-sm th {
        padding: 0.5rem;
    }

    .badge {
        padding: 0.35rem 0.75rem;
        font-weight: normal;
    }

    .alert {
        font-size: 0.875rem;
    }

    .btn {
        font-size: 0.875rem;
        padding: 0.5rem 1rem;
    }

    #paymentProofImg {
        transition: all 0.3s ease;
    }

    #paymentProofImg:hover {
        transform: scale(1.02);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
</style>