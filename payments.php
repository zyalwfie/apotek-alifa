<?php
require_once 'checkout_functions.php';

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : ($_SESSION['last_order_id'] ?? 0);

if ($order_id <= 0) {
    header('Location: ?page=shop');
    exit;
}

$order = getOrderDetails($order_id);
if (!$order) {
    header('Location: ?page=shop');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['buktiPembayaran'])) {
    $result = uploadPaymentProof($order_id, $_FILES['buktiPembayaran']);

    if ($result['success']) {
        $success = $result['message'];
        header("refresh:2;url=?page=thanks&order_id=$order_id");
    } else {
        $error = $result['message'];
    }
}
?>

<section class="py-5">
    <div class="container">
        <!-- Order Summary -->
        <div class="row justify-content-center mb-5">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-receipt me-2"></i>Ringkasan Pesanan
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="fw-bold">Detail Pesanan:</h6>
                                <p class="mb-1"><strong>Order ID:</strong> #<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></p>
                                <p class="mb-1"><strong>Tanggal:</strong> <?= date('d F Y, H:i', strtotime($order['waktu_dibuat'])) ?></p>
                                <p class="mb-1"><strong>Total:</strong> <span class="text-primary fw-bold">Rp<?= number_format($order['harga_total'], 0, '.', ',') ?></span></p>
                                <p class="mb-1"><strong>Status:</strong>
                                    <?php $status = getOrderStatus($order['status']); ?>
                                    <span class="badge bg-<?= $status['class'] ?>">
                                        <i class="bi bi-<?= $status['icon'] ?> me-1"></i><?= $status['text'] ?>
                                    </span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="fw-bold">Alamat Pengiriman:</h6>
                                <p class="mb-1"><strong><?= htmlspecialchars($order['nama_penerima']) ?></strong></p>
                                <p class="mb-1"><?= htmlspecialchars($order['surel_penerima']) ?></p>
                                <p class="mb-1"><?= htmlspecialchars($order['nomor_telepon_penerima']) ?></p>
                                <p class="mb-1"><?= nl2br(htmlspecialchars($order['alamat'])) ?></p>
                                <?php if (!empty($order['catatan'])): ?>
                                    <p class="mb-1"><small class="text-muted">Catatan: <?= htmlspecialchars($order['catatan']) ?></small></p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if (!empty($order['items'])): ?>
                            <hr>
                            <h6 class="fw-bold">Item Pesanan:</h6>
                            <p class="text-muted"><?= htmlspecialchars($order['items']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Instructions -->
        <div class="row justify-content-center mb-4">
            <div class="col-md-8">
                <div class="card border-info">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-info-circle me-2"></i>Instruksi Pembayaran
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h6 class="fw-bold">Transfer ke rekening berikut:</h6>
                                <div class="d-flex align-items-center mb-3">
                                    <img src="/apotek-alifa/assets/img/bank/bca.svg" alt="Logo Bank BCA" width="80" class="me-3">
                                    <div>
                                        <div class="fw-bold">Bank BCA</div>
                                        <div class="fw-bold text-primary">0987654321</div>
                                        <small class="text-muted">a.n. Salman Alfarizi</small>
                                    </div>
                                </div>
                                <div class="alert alert-warning">
                                    <small>
                                        <i class="bi bi-exclamation-triangle me-1"></i>
                                        Transfer tepat sebesar <strong>Rp<?= number_format($order['harga_total'], 0, '.', ',') ?></strong>
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-4 text-center">
                                <div class="bg-light p-3 rounded">
                                    <h4 class="text-primary fw-bold mb-0">Rp<?= number_format($order['harga_total'], 0, '.', ',') ?></h4>
                                    <small class="text-muted">Total Pembayaran</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upload Payment Proof -->
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-body text-center p-5">
                        <i class="bi bi-cloud-upload" style="font-size: 3rem; color: #0d6efd; margin-bottom: 1rem;"></i>
                        <h4 class="mb-3">Upload Bukti Pembayaran</h4>

                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <?= htmlspecialchars($error) ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle me-2"></i>
                                <?= htmlspecialchars($success) ?>
                                <div class="mt-2">
                                    <small>Mengalihkan ke halaman terima kasih...</small>
                                </div>
                            </div>
                        <?php endif; ?>

                        <form action="" method="POST" enctype="multipart/form-data" id="uploadForm">
                            <div class="mb-4">
                                <label for="buktiPembayaran" class="form-label fw-bold">Pilih File Bukti Transfer</label>
                                <input type="file"
                                    id="buktiPembayaran"
                                    name="buktiPembayaran"
                                    class="form-control form-control-lg"
                                    accept="image/*,.pdf"
                                    required>
                                <div class="form-text">
                                    <small>Format yang diterima: JPG, PNG, PDF (Maksimal 5MB)</small>
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg" id="uploadBtn">
                                    <i class="bi bi-cloud-upload me-2"></i>Upload Bukti Pembayaran
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="window.location.href='?page=shop'">
                                    <i class="bi bi-arrow-left me-2"></i>Lakukan Nanti
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Info -->
        <div class="row justify-content-center mt-4">
            <div class="col-md-8">
                <div class="card bg-light">
                    <div class="card-body">
                        <h6 class="fw-bold">Informasi Penting:</h6>
                        <ul class="list-unstyled mb-0">
                            <li><i class="bi bi-check-circle text-success me-2"></i>Pesanan akan diproses setelah pembayaran dikonfirmasi</li>
                            <li><i class="bi bi-check-circle text-success me-2"></i>Konfirmasi pembayaran maksimal 1x24 jam</li>
                            <li><i class="bi bi-check-circle text-success me-2"></i>Barang akan dikirim dalam 2-3 hari kerja</li>
                            <li><i class="bi bi-check-circle text-success me-2"></i>Anda dapat mengecek status pesanan di dashboard</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
</section>

<style>
    .card {
        border: none;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }

    .card:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        transition: box-shadow 0.3s ease;
    }

    .btn-lg {
        padding: 0.75rem 1.5rem;
        font-size: 1.1rem;
    }

    .form-control-lg {
        padding: 0.75rem 1rem;
    }

    #uploadBtn.loading {
        pointer-events: none;
    }

    #uploadBtn.loading::after {
        content: '';
        display: inline-block;
        width: 16px;
        height: 16px;
        margin-left: 8px;
        border: 2px solid #ffffff;
        border-radius: 50%;
        border-top-color: transparent;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }
</style>

<script>
    document.getElementById('uploadForm').addEventListener('submit', function(e) {
        const fileInput = document.getElementById('buktiPembayaran');
        const uploadBtn = document.getElementById('uploadBtn');

        if (!fileInput.files[0]) {
            e.preventDefault();
            alert('Silakan pilih file bukti pembayaran!');
            return;
        }

        if (fileInput.files[0].size > 5 * 1024 * 1024) {
            e.preventDefault();
            alert('Ukuran file terlalu besar! Maksimal 5MB.');
            return;
        }

        uploadBtn.classList.add('loading');
        uploadBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Mengupload...';
        uploadBtn.disabled = true;
    });

    document.getElementById('buktiPembayaran').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const fileSize = (file.size / 1024 / 1024).toFixed(2);
            const fileName = file.name;

            let fileInfo = document.querySelector('.file-info');
            if (!fileInfo) {
                fileInfo = document.createElement('div');
                fileInfo.className = 'file-info mt-2 p-2 bg-light rounded';
                e.target.parentNode.appendChild(fileInfo);
            }

            fileInfo.innerHTML = `
            <small>
                <i class="bi bi-file-earmark me-1"></i>
                <strong>${fileName}</strong> (${fileSize} MB)
            </small>
        `;
        }
    });
</script>