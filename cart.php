<?php
require_once 'cart_functions.php';
require_once 'auth_functions.php';

// Redirect if not logged in
requireLogin();

$user_id = $_SESSION['user_id'];
$cartItems = getCartItems($user_id);
$cartTotal = getCartTotal($user_id);
$cartCount = getCartCount($user_id);
?>

<section>
    <div class="container" style="margin-bottom: 2rem;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Keranjang Belanja</h2>
            <a href="?page=shop" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left me-2"></i>Lanjut Belanja
            </a>
        </div>

        <?php if (empty($cartItems)): ?>
            <!-- Empty Cart -->
            <div class="text-center py-5">
                <i class="bi bi-cart-x" style="font-size: 5rem; color: #6c757d;"></i>
                <h4 class="mt-3 text-muted">Keranjang Anda Kosong</h4>
                <p class="text-muted">Belum ada produk yang ditambahkan ke keranjang</p>
                <a href="?page=shop" class="btn btn-primary">
                    <i class="bi bi-bag me-2"></i>Mulai Belanja
                </a>
            </div>
        <?php else: ?>
            <!-- Cart Table -->
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Gambar</th>
                            <th scope="col">Nama Produk</th>
                            <th scope="col">Harga</th>
                            <th scope="col">Kuantitas</th>
                            <th scope="col">Subtotal</th>
                            <th scope="col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1;
                        foreach ($cartItems as $item): ?>
                            <tr id="cart-item-<?= $item['id'] ?>">
                                <th scope="row"><?= $no++ ?></th>
                                <td>
                                    <img src="/apotek-alifa/assets/img/product/uploads/<?= htmlspecialchars($item['image']) ?>"
                                        alt="<?= htmlspecialchars($item['name']) ?>"
                                        width="80" height="80"
                                        style="object-fit: cover; border-radius: 8px;">
                                </td>
                                <td>
                                    <h6 class="mb-1"><?= htmlspecialchars($item['name']) ?></h6>
                                    <small class="text-muted">
                                        Harga saat ditambah: Rp<?= number_format($item['price_at_add'], 0, '.', ',') ?>
                                    </small>
                                    <?php if ($item['current_price'] != $item['price_at_add']): ?>
                                        <br><small class="text-info">
                                            Harga sekarang: Rp<?= number_format($item['current_price'], 0, '.', ',') ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="fw-bold">Rp<?= number_format($item['price_at_add'], 0, '.', ',') ?></span>
                                </td>
                                <td>
                                    <div class="input-group" style="max-width: 140px">
                                        <button class="btn btn-outline-secondary btn-sm quantity-btn"
                                            type="button"
                                            data-action="decrease"
                                            data-cart-id="<?= $item['id'] ?>">
                                            <i class="bi bi-dash"></i>
                                        </button>
                                        <input type="number"
                                            class="form-control form-control-sm text-center quantity-input"
                                            value="<?= $item['quantity'] ?>"
                                            min="1"
                                            data-cart-id="<?= $item['id'] ?>"
                                            readonly>
                                        <button class="btn btn-outline-secondary btn-sm quantity-btn"
                                            type="button"
                                            data-action="increase"
                                            data-cart-id="<?= $item['id'] ?>">
                                            <i class="bi bi-plus"></i>
                                        </button>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-bold text-primary item-subtotal">
                                        Rp<?= number_format($item['quantity'] * $item['price_at_add'], 0, '.', ',') ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-outline-danger btn-sm remove-btn"
                                        data-cart-id="<?= $item['id'] ?>"
                                        data-product-name="<?= htmlspecialchars($item['name']) ?>">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <?php if (!empty($cartItems)): ?>
        <!-- Checkout Section -->
        <div class="container">
            <form class="row g-5" id="checkoutForm">
                <div class="col-md-5 order-md-last">
                    <h4 class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-primary">Ringkasan Pesanan</span>
                        <span class="badge bg-primary rounded-pill" id="cart-count"><?= $cartCount ?></span>
                    </h4>

                    <ul class="list-group mb-3" id="order-summary">
                        <?php foreach ($cartItems as $item): ?>
                            <li class="list-group-item d-flex justify-content-between lh-sm" id="summary-item-<?= $item['id'] ?>">
                                <div>
                                    <h6 class="my-0"><?= htmlspecialchars($item['name']) ?></h6>
                                    <small class="text-body-secondary">Qty: <?= $item['quantity'] ?></small>
                                </div>
                                <span class="text-body-secondary">
                                    Rp<?= number_format($item['quantity'] * $item['price_at_add'], 0, '.', ',') ?>
                                </span>
                            </li>
                        <?php endforeach; ?>

                        <li class="list-group-item d-flex justify-content-between">
                            <span class="fw-bold">Total (IDR)</span>
                            <strong id="cart-total">Rp<?= number_format($cartTotal, 0, '.', ',') ?></strong>
                        </li>
                    </ul>

                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Informasi Pembayaran</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex gap-3 align-items-center mb-3">
                                <img src="/apotek-alifa/assets/img/bank/bca.svg" alt="Logo Bank BCA" width="60">
                                <div>
                                    <div class="fw-semibold">BCA - 0987654321</div>
                                    <small class="text-muted">a.n. Salman Alfarizi</small>
                                </div>
                            </div>
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                <small>Transfer sesuai total pembayaran dan upload bukti transfer pada form checkout</small>
                            </div>
                        </div>
                    </div>

                    <button class="w-100 btn btn-primary btn-lg mt-3" type="submit">
                        <i class="bi bi-credit-card me-2"></i>Lanjutkan ke Pembayaran
                    </button>
                </div>

                <div class="col-md-7">
                    <h4 class="mb-3">
                        <i class="bi bi-truck me-2"></i>Alamat Pengiriman
                    </h4>

                    <div class="row g-3">
                        <div class="col-sm-12">
                            <label for="recipient_name" class="form-label">
                                Nama Penerima <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="recipient_name" name="recipient_name"
                                placeholder="Masukkan nama penerima" required>
                            <div class="invalid-feedback">Nama penerima harus diisi</div>
                        </div>

                        <div class="col-sm-12">
                            <label for="email" class="form-label">
                                Email <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" class="form-control" id="email" name="email"
                                    value="<?= htmlspecialchars($_SESSION['email']) ?>" required>
                                <div class="invalid-feedback">Email harus valid</div>
                            </div>
                        </div>

                        <div class="col-12">
                            <label for="address" class="form-label">
                                Alamat Lengkap <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control" id="address" name="address" rows="4"
                                placeholder="Masukkan alamat lengkap untuk pengiriman" required></textarea>
                            <div class="invalid-feedback">Alamat harus diisi</div>
                        </div>

                        <div class="col-sm-12">
                            <label for="recipient_phone" class="form-label">
                                Nomor Telepon <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-phone"></i></span>
                                <input type="tel" class="form-control" id="recipient_phone" name="recipient_phone"
                                    placeholder="08xxxxxxxxxx" required>
                                <div class="invalid-feedback">Nomor telepon harus diisi</div>
                            </div>
                        </div>

                        <div class="col-12">
                            <label for="notes" class="form-label">Catatan Tambahan</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"
                                placeholder="Catatan khusus untuk pesanan (opsional)"></textarea>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    <?php endif; ?>
</section>

<!-- Toast Container -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1100;">
    <div id="cartToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <i class="bi bi-cart text-primary me-2"></i>
            <strong class="me-auto">Keranjang</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body">
            <!-- Toast message will be inserted here -->
        </div>
    </div>
</div>

<style>
    .quantity-btn {
        padding: 0.25rem 0.5rem;
    }

    .quantity-input {
        max-width: 60px;
    }

    .table td {
        vertical-align: middle;
    }

    .remove-btn:hover {
        transform: scale(1.1);
        transition: transform 0.2s ease;
    }

    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }

    .list-group-item {
        border-left: none;
        border-right: none;
    }

    .list-group-item:first-child {
        border-top: none;
    }

    .list-group-item:last-child {
        border-bottom: none;
    }
</style>

<script>
    // Cart management functions
    function updateCartItem(cartId, action, quantity = null) {
        const formData = new FormData();
        formData.append('cart_id', cartId);
        formData.append('action', action);

        if (quantity !== null) {
            formData.append('quantity', quantity);
        }

        fetch('/apotek-alifa/cart_update.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (action === 'remove') {
                        // Remove item from DOM
                        document.getElementById(`cart-item-${cartId}`).remove();
                        document.getElementById(`summary-item-${cartId}`).remove();

                        // Check if cart is empty
                        const remainingItems = document.querySelectorAll('[id^="cart-item-"]');
                        if (remainingItems.length === 0) {
                            location.reload(); // Reload to show empty cart message
                        }
                    } else {
                        // Update quantities and totals
                        location.reload(); // Simple reload for now
                    }

                    // Update cart count in header
                    updateHeaderCartCount(data.cart_count);

                    showToast(data.message, 'success');
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Terjadi kesalahan!', 'error');
            });
    }

    function updateHeaderCartCount(count) {
        // Update cart badge in header
        const cartBadges = document.querySelectorAll('.badge');
        cartBadges.forEach(badge => {
            if (badge.closest('[href*="cart"]')) {
                badge.textContent = count > 99 ? '99+' : count;
                badge.style.display = count > 0 ? 'inline-block' : 'none';
            }
        });

        // Update local cart count
        const localCartCount = document.getElementById('cart-count');
        if (localCartCount) {
            localCartCount.textContent = count;
        }
    }

    function showToast(message, type = 'success') {
        const toastEl = document.getElementById('cartToast');
        const toastBody = toastEl.querySelector('.toast-body');
        const toastIcon = toastEl.querySelector('.toast-header i');

        toastBody.textContent = message;

        if (type === 'success') {
            toastIcon.className = 'bi bi-check-circle text-success me-2';
        } else {
            toastIcon.className = 'bi bi-exclamation-triangle text-danger me-2';
        }

        const toast = new bootstrap.Toast(toastEl);
        toast.show();
    }

    // Event listeners
    document.addEventListener('click', function(e) {
        // Quantity buttons
        if (e.target.closest('.quantity-btn')) {
            const btn = e.target.closest('.quantity-btn');
            const cartId = btn.getAttribute('data-cart-id');
            const action = btn.getAttribute('data-action');

            updateCartItem(cartId, action);
        }

        // Remove buttons
        if (e.target.closest('.remove-btn')) {
            const btn = e.target.closest('.remove-btn');
            const cartId = btn.getAttribute('data-cart-id');
            const productName = btn.getAttribute('data-product-name');

            if (confirm(`Hapus "${productName}" dari keranjang?`)) {
                updateCartItem(cartId, 'remove');
            }
        }
    });

    // Form validation and submission
    document.getElementById('checkoutForm')?.addEventListener('submit', function(e) {
        e.preventDefault();

        // Basic form validation
        const requiredFields = ['recipient_name', 'email', 'address', 'recipient_phone'];
        let isValid = true;

        requiredFields.forEach(fieldName => {
            const field = document.getElementById(fieldName);
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                isValid = false;
            } else {
                field.classList.remove('is-invalid');
                field.classList.add('is-valid');
            }
        });

        // Email validation
        const email = document.getElementById('email');
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (email.value && !emailRegex.test(email.value)) {
            email.classList.add('is-invalid');
            isValid = false;
        }

        // Phone validation
        const phone = document.getElementById('recipient_phone');
        const phoneRegex = /^[0-9+\-\s()]+$/;
        if (phone.value && !phoneRegex.test(phone.value)) {
            phone.classList.add('is-invalid');
            isValid = false;
        }

        if (isValid) {
            // Process checkout
            alert('Fitur checkout akan segera tersedia!');
            // Here you would typically submit to a checkout processing script
        } else {
            showToast('Mohon lengkapi semua field yang diperlukan!', 'error');
        }
    });

    // Real-time validation
    document.querySelectorAll('input, textarea').forEach(field => {
        field.addEventListener('blur', function() {
            if (this.hasAttribute('required') && !this.value.trim()) {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            }
        });
    });
</script>