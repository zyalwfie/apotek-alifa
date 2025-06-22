<?php
require_once '../../order_functions.php';

requireLogin();

$user_id = $_SESSION['user_id'];
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if (!$order_id) {
    header('Location: ?page=order.index');
    exit;
}

// Get order details
$order = getOrderById($order_id, $user_id);

if (!$order) {
    $_SESSION['error'] = 'Pesanan tidak ditemukan';
    header('Location: ?page=order.index');
    exit;
}

// Get order items detail
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

// Get payment info
$query = "SELECT * FROM payments WHERE order_id = ? ORDER BY created_at DESC LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$payment_result = $stmt->get_result();
$payment = $payment_result->fetch_assoc();
$stmt->close();
$conn->close();

// Handle proof of payment upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['proof_of_payment'])) {
    $upload_dir = '/apotek-alifa/assets/img/payments/';
    $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];

    if (in_array($_FILES['proof_of_payment']['type'], $allowed_types)) {
        $file_extension = pathinfo($_FILES['proof_of_payment']['name'], PATHINFO_EXTENSION);
        $file_name = 'payment_' . $order_id . '_' . time() . '.' . $file_extension;
        $upload_path = $_SERVER['DOCUMENT_ROOT'] . $upload_dir . $file_name;

        if (move_uploaded_file($_FILES['proof_of_payment']['tmp_name'], $upload_path)) {
            // Delete old file if updating
            if ($payment && !empty($payment['proof_of_payment'])) {
                $old_file = $_SERVER['DOCUMENT_ROOT'] . $upload_dir . $payment['proof_of_payment'];
                if (file_exists($old_file)) {
                    unlink($old_file);
                }
            }

            // Update payment record
            $conn = connectDB();
            if ($payment) {
                // Update existing payment
                $query = "UPDATE payments SET proof_of_payment = ?, updated_at = NOW() WHERE order_id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("si", $file_name, $order_id);
            } else {
                // Create new payment record
                $query = "INSERT INTO payments (order_id, proof_of_payment, created_at) VALUES (?, ?, NOW())";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("is", $order_id, $file_name);
            }

            if ($stmt->execute()) {
                $_SESSION['success'] = 'Bukti pembayaran berhasil diunggah. Menunggu persetujuan admin.';
            }
            $stmt->close();
            $conn->close();

            header('Location: ?page=order.show&order_id=' . $order_id);
            exit;
        } else {
            $_SESSION['error'] = 'Gagal mengunggah file. Silakan coba lagi.';
        }
    } else {
        $_SESSION['error'] = 'Tipe file tidak didukung. Gunakan JPG, PNG, atau PDF.';
    }
}
?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="?page=order.index">Pesanan</a></li>
        <li class="breadcrumb-item active">Detail</li>
    </ol>
</nav>

<h1 class="h3 mb-2 text-gray-800">Detail Pesanan #<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></h1>
<p class="mb-4">Informasi lengkap pesanan atas nama <span class="fw-semibold text-capitalize"><?= htmlspecialchars($order['recipient_name']) ?></span></p>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= $_SESSION['success'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= $_SESSION['error'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<div class="row">
    <div class="col-lg-6 mb-4">
        <h2 class="h3 mb-3 text-black">Rincian Pengiriman</h2>
        <div class="p-3 p-lg-5 border bg-white">
            <div class="form-group mb-3 row">
                <div class="col">
                    <label class="text-black">Nama Penerima</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($order['recipient_name']) ?>" disabled>
                </div>
                <div class="col">
                    <label class="text-black">Email</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($order['recipient_email']) ?>" disabled>
                </div>
            </div>

            <div class="form-group mb-3">
                <label class="text-black">Alamat Penerima</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($order['street_address']) ?>" disabled>
            </div>

            <div class="form-group mb-3">
                <label class="text-black">Nomor Telepon</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($order['recipient_phone']) ?>" disabled>
            </div>

            <div class="form-group">
                <label class="text-black">Catatan</label>
                <textarea class="form-control" rows="5" disabled><?= htmlspecialchars($order['notes'] ?: 'Tidak ada catatan') ?></textarea>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="row">
            <div class="col-12 mb-4">
                <div class="row align-items-center justify-content-between mb-3">
                    <div class="col">
                        <h2 class="h3 mb-0 text-black">Pesananmu</h2>
                    </div>
                    <div class="col-auto">
                        <?php
                        $badge_info = getOrderStatusBadge($order['status']);
                        ?>
                        <span class="badge bg-<?= $badge_info['class'] ?> text-capitalize">
                            <i class="ti ti-<?= $badge_info['icon'] ?> me-1"></i>
                            <?= $badge_info['text'] ?>
                        </span>
                    </div>
                </div>
                <div class="p-3 p-lg-5 border bg-white">
                    <p class="lead fs-6">
                        <i class="ti ti-calendar me-1"></i>
                        <?= formatOrderDate($order['created_at']) ?>
                    </p>
                    <table class="table site-block-order-table mb-5">
                        <thead>
                            <th>Produk</th>
                            <th class="text-end">Total</th>
                        </thead>
                        <tbody>
                            <?php foreach ($order_items as $item): ?>
                                <tr>
                                    <td>
                                        <?= htmlspecialchars($item['name']) ?>
                                        <strong class="mx-2">x</strong><?= $item['quantity'] ?>
                                    </td>
                                    <td class="text-end">
                                        Rp<?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td class="text-black font-weight-bold"><strong>Total Pesanan</strong></td>
                                <td class="text-black font-weight-bold text-end">
                                    <strong>Rp<?= number_format($order['total_price'], 0, ',', '.') ?></strong>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="col-12">
                <div class="row align-items-center justify-content-between mb-3">
                    <div class="col">
                        <h2 class="h3 mb-0 text-black">Bukti Pembayaran</h2>
                    </div>
                </div>

                <div class="p-3 p-lg-5 border bg-white">
                    <div class="row">
                        <?php if ($payment && !empty($payment['proof_of_payment'])): ?>
                            <div class="col-md-6 mb-3">
                                <img id="paymentProofImg"
                                    src="/apotek-alifa/assets/img/payments/<?= htmlspecialchars($payment['proof_of_payment']) ?>"
                                    alt="Bukti Pembayaran"
                                    class="img-fluid rounded border"
                                    style="cursor: pointer; max-height: 300px; width: 100%; object-fit: cover;">
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <p class="text-muted mb-1">Bukti pembayaran telah diunggah pada:</p>
                                    <p class="fw-semibold"><?= formatOrderDate($payment['created_at']) ?></p>

                                    <?php if ($order['status'] === 'berhasil'): ?>
                                        <div class="alert alert-success">
                                            <i class="ti ti-circle-check me-1"></i>
                                            Pembayaran Anda telah disetujui oleh admin
                                        </div>
                                    <?php elseif ($order['status'] === 'tertunda'): ?>
                                        <div class="alert alert-warning">
                                            <i class="ti ti-info-circle me-1"></i>
                                            Bukti pembayaran sedang diverifikasi oleh admin
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <?php if ($order['status'] !== 'berhasil'): ?>
                                    <hr class="my-3">
                                    <p class="text-muted mb-3">Perlu memperbarui bukti pembayaran?</p>
                                    <form method="POST" enctype="multipart/form-data">
                                        <div class="mb-3">
                                            <label for="proof_of_payment" class="form-label">
                                                Perbarui Bukti Pembayaran
                                            </label>
                                            <input class="form-control" type="file"
                                                id="proof_of_payment"
                                                name="proof_of_payment"
                                                accept="image/*,application/pdf"
                                                onchange="previewProof(event)"
                                                required>
                                            <small class="text-muted">
                                                Format: JPG, PNG, PDF (Maks. 5MB)
                                            </small>
                                        </div>
                                        <div class="mb-3" id="previewContainer"></div>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="ti ti-upload me-1"></i>Perbarui Bukti
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="col-12">
                                <div class="alert alert-warning mb-4">
                                    <i class="ti ti-alert-circle me-1"></i>
                                    Belum ada bukti pembayaran. Silakan unggah bukti pembayaran Anda.
                                </div>

                                <form method="POST" enctype="multipart/form-data">
                                    <div class="mb-3">
                                        <label for="proof_of_payment" class="form-label">
                                            File Bukti Pembayaran <span class="text-danger">*</span>
                                        </label>
                                        <input class="form-control" type="file"
                                            id="proof_of_payment"
                                            name="proof_of_payment"
                                            accept="image/*,application/pdf"
                                            onchange="previewProof(event)"
                                            required>
                                        <small class="text-muted">
                                            Format yang didukung: JPG, PNG, PDF (Maks. 5MB)
                                        </small>
                                    </div>
                                    <div class="mb-3" id="previewContainer"></div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ti ti-upload me-1"></i>Unggah Bukti
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Back to orders button -->
<div class="mt-4">
    <a href="?page=order.index" class="btn btn-secondary">
        <i class="ti ti-arrow-left me-1"></i>Kembali ke Daftar Pesanan
    </a>
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
                fullscreen: true,
                viewed() {
                    viewer.zoomTo(1);
                }
            });
        }
    });

    function previewProof(event) {
        const file = event.target.files[0];
        const previewContainer = document.getElementById('previewContainer');
        previewContainer.innerHTML = '';

        if (!file) return;

        // Check file size (5MB limit)
        if (file.size > 5 * 1024 * 1024) {
            previewContainer.innerHTML = '<div class="alert alert-danger">File terlalu besar. Maksimal 5MB.</div>';
            event.target.value = '';
            return;
        }

        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'img-fluid border rounded';
                img.style.maxHeight = '200px';
                previewContainer.appendChild(img);
            };
            reader.readAsDataURL(file);
        } else if (file.type === 'application/pdf') {
            previewContainer.innerHTML = '<div class="alert alert-info"><i class="ti ti-file-text me-1"></i>File PDF: ' + file.name + '</div>';
        } else {
            previewContainer.innerHTML = '<div class="alert alert-danger">Tipe file tidak didukung.</div>';
            event.target.value = '';
        }
    }
</script>

<style>
    .site-block-order-table {
        border: none;
    }

    .site-block-order-table th {
        border-top: none;
        border-bottom: 1px solid #dee2e6;
        font-weight: 600;
        padding: 1rem 0;
    }

    .site-block-order-table td {
        padding: 0.75rem 0;
        border: none;
    }

    .site-block-order-table tfoot td {
        border-top: 2px solid #dee2e6;
        padding-top: 1rem;
    }

    .badge {
        padding: 0.5rem 1rem;
        font-weight: normal;
    }

    .form-control:disabled {
        background-color: #f8f9fa;
        cursor: not-allowed;
    }

    .alert {
        font-size: 0.875rem;
    }

    @media (max-width: 768px) {
        .p-lg-5 {
            padding: 1rem !important;
        }
    }
</style>