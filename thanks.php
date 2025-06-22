<?php
require_once 'checkout_functions.php';

// Check if user is logged in
requireLogin();

// Get order ID from URL or session
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : ($_SESSION['last_order_id'] ?? 0);

$order = null;
if ($order_id > 0) {
    $order = getOrderDetails($order_id);
}
?>

<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 text-center">
                <!-- Success Icon -->
                <div class="mb-4">
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
                </div>

                <!-- Thank You Message -->
                <h1 class="display-6 fw-bold text-success mb-3">Terima Kasih!</h1>
                <h4 class="mb-4">Pesanan Anda Telah Berhasil Dibuat</h4>

                <?php if ($order): ?>
                    <!-- Order Details Card -->
                    <div class="card shadow-lg mb-4">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0 text-white">
                                <i class="bi bi-receipt me-2"></i>Detail Pesanan
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 text-start">
                                    <h6 class="fw-bold">Informasi Pesanan:</h6>
                                    <p class="mb-2">
                                        <strong>Order ID:</strong>
                                        <span class="badge bg-primary">#<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></span>
                                    </p>
                                    <p class="mb-2"><strong>Tanggal:</strong> <?= date('d F Y, H:i', strtotime($order['created_at'])) ?></p>
                                    <p class="mb-2"><strong>Total Pembayaran:</strong>
                                        <span class="text-success fw-bold fs-5">Rp<?= number_format($order['total_price'], 0, '.', ',') ?></span>
                                    </p>
                                    <p class="mb-2"><strong>Status:</strong>
                                        <?php $status = getOrderStatus($order['status']); ?>
                                        <span class="badge bg-<?= $status['class'] ?>">
                                            <i class="bi bi-<?= $status['icon'] ?> me-1"></i><?= $status['text'] ?>
                                        </span>
                                    </p>
                                </div>
                                <div class="col-md-6 text-start">
                                    <h6 class="fw-bold">Alamat Pengiriman:</h6>
                                    <address class="mb-0">
                                        <strong><?= htmlspecialchars($order['recipient_name']) ?></strong><br>
                                        <?= nl2br(htmlspecialchars($order['street_address'])) ?><br>
                                        <i class="bi bi-telephone me-1"></i><?= htmlspecialchars($order['recipient_phone']) ?><br>
                                        <i class="bi bi-envelope me-1"></i><?= htmlspecialchars($order['recipient_email']) ?>
                                    </address>
                                    <?php if (!empty($order['notes'])): ?>
                                        <hr>
                                        <small class="text-muted">
                                            <strong>Catatan:</strong> <?= htmlspecialchars($order['notes']) ?>
                                        </small>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <?php if (!empty($order['items'])): ?>
                                <hr>
                                <h6 class="fw-bold text-start">Item Pesanan:</h6>
                                <p class="text-muted text-start"><?= htmlspecialchars($order['items']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Next Steps -->
                <div class="card border-info mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0 text-white">
                            <i class="bi bi-info-circle me-2"></i>Langkah Selanjutnya
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="d-flex flex-column align-items-center">
                                    <i class="bi bi-credit-card text-primary mb-2" style="font-size: 2rem;"></i>
                                    <h6 class="fw-bold">1. Lakukan Pembayaran</h6>
                                    <p class="small text-muted text-center">Transfer ke rekening yang telah disediakan</p>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="d-flex flex-column align-items-center">
                                    <i class="bi bi-cloud-upload text-warning mb-2" style="font-size: 2rem;"></i>
                                    <h6 class="fw-bold">2. Upload Bukti</h6>
                                    <p class="small text-muted text-center">Upload bukti transfer untuk konfirmasi</p>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="d-flex flex-column align-items-center">
                                    <i class="bi bi-truck text-success mb-2" style="font-size: 2rem;"></i>
                                    <h6 class="fw-bold">3. Tunggu Pengiriman</h6>
                                    <p class="small text-muted text-center">Pesanan akan diproses dan dikirim</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                    <?php if ($order && $order['status'] === 'tertunda'): ?>
                        <a href="?page=payments&order_id=<?= $order['id'] ?>" class="btn btn-primary btn-lg me-md-2">
                            <i class="bi bi-credit-card me-2"></i>Upload Bukti Pembayaran
                        </a>
                    <?php endif; ?>

                    <a href="?page=orders" class="btn btn-outline-primary btn-lg me-md-2">
                        <i class="bi bi-list-ul me-2"></i>Lihat Semua Pesanan
                    </a>

                    <a href="?page=shop" class="btn btn-outline-secondary btn-lg">
                        <i class="bi bi-bag me-2"></i>Lanjut Berbelanja
                    </a>
                </div>

                <!-- Contact Information -->
                <div class="mt-5">
                    <hr>
                    <h6 class="fw-bold">Butuh Bantuan?</h6>
                    <p class="text-muted">
                        Jika Anda memiliki pertanyaan tentang pesanan, silakan hubungi kami:<br>
                        <i class="bi bi-telephone me-1"></i> 087815509458<br>
                        <i class="bi bi-envelope me-1"></i> apotekalifa@gmail.com<br>
                        <i class="bi bi-whatsapp me-1"></i> WhatsApp: 087815509458
                    </p>
                </div>

                <!-- Important Notes -->
                <div class="alert alert-warning text-start">
                    <h6 class="fw-bold">
                        <i class="bi bi-exclamation-triangle me-2"></i>Informasi Penting:
                    </h6>
                    <ul class="mb-0">
                        <li>Pesanan akan diproses setelah pembayaran dikonfirmasi</li>
                        <li>Konfirmasi pembayaran maksimal 1x24 jam</li>
                        <li>Pengiriman dilakukan dalam 2-3 hari kerja setelah konfirmasi</li>
                        <li>Anda akan mendapat notifikasi via email untuk setiap update status pesanan</li>
                        <li>Simpan Order ID untuk referensi dan pelacakan pesanan</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    .card {
        border: none;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }

    .btn-lg {
        padding: 0.75rem 1.5rem;
        font-size: 1.1rem;
    }

    address {
        font-style: normal;
        line-height: 1.6;
    }

    .text-success {
        color: #198754 !important;
    }

    .bg-success {
        background-color: #198754 !important;
    }

    .border-info {
        border-color: #0dcaf0 !important;
    }

    .bg-info {
        background-color: #0dcaf0 !important;
    }

    @media (max-width: 768px) {
        .btn-lg {
            width: 100%;
            margin-bottom: 0.5rem;
        }

        .d-md-flex {
            flex-direction: column;
        }
    }
</style>

<script>
    // Auto-scroll to top on page load
    window.addEventListener('load', function() {
        window.scrollTo(0, 0);
    });

    // Confetti effect (optional)
    function createConfetti() {
        const colors = ['#ff6b6b', '#4ecdc4', '#45b7d1', '#96ceb4', '#ffeaa7'];
        const confettiCount = 50;

        for (let i = 0; i < confettiCount; i++) {
            const confetti = document.createElement('div');
            confetti.style.position = 'fixed';
            confetti.style.width = '10px';
            confetti.style.height = '10px';
            confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
            confetti.style.left = Math.random() * 100 + 'vw';
            confetti.style.top = '-10px';
            confetti.style.zIndex = '9999';
            confetti.style.borderRadius = '50%';
            confetti.style.pointerEvents = 'none';

            document.body.appendChild(confetti);

            const fallDuration = Math.random() * 3 + 2;
            const horizontalMovement = (Math.random() - 0.5) * 200;

            confetti.animate([{
                    transform: 'translateY(-10px) translateX(0px) rotate(0deg)',
                    opacity: 1
                },
                {
                    transform: `translateY(100vh) translateX(${horizontalMovement}px) rotate(360deg)`,
                    opacity: 0
                }
            ], {
                duration: fallDuration * 1000,
                easing: 'ease-out'
            }).onfinish = () => {
                confetti.remove();
            };
        }
    }

    // Trigger confetti on page load (optional)
    setTimeout(createConfetti, 500);
</script>