<?php
require_once 'functions.php';
require_once 'auth_functions.php';

$user = getUserData();

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($product_id <= 0) {
    header('Location: ?page=shop');
    exit;
}

$product = getData("SELECT * FROM products WHERE id = ?", [$product_id]);
if (!$product) {
    header('Location: ?page=shop');
    exit;
}
$product = $product[0];

$related_query = "SELECT * FROM products WHERE id != ? ORDER BY RAND() LIMIT 4";
$related_products = getData($related_query, [$product_id]) ?: [];
?>

<!-- Product section-->
<section class="py-5">
    <div class="container px-4 px-lg-5 my-5">
        <div class="row gx-4 gx-lg-5 align-items-center">
            <div class="col-md-6">
                <div class="position-relative">
                    <img class="card-img-top mb-5 mb-md-0 rounded shadow"
                        src="/apotek-alifa/assets/img/product/uploads/<?= htmlspecialchars($product->image) ?>"
                        alt="<?= htmlspecialchars($product->name) ?>"
                        style="width: 100%; height: 400px; object-fit: cover;" />

                    <!-- Stock badge -->
                    <?php if (isset($product->stock)): ?>
                        <?php if ($product->stock > 0): ?>
                            <div class="badge bg-success position-absolute" style="top: 1rem; left: 1rem">
                                <i class="bi bi-check-circle me-1"></i>Tersedia
                            </div>
                        <?php else: ?>
                            <div class="badge bg-danger position-absolute" style="top: 1rem; left: 1rem">
                                <i class="bi bi-x-circle me-1"></i>Habis
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-md-6">
                <!-- Breadcrumb -->
                <nav aria-label="breadcrumb" class="mb-3">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/apotek-alifa/layouts/landing/" class="text-decoration-none">Beranda</a></li>
                        <li class="breadcrumb-item"><a href="?page=shop" class="text-decoration-none">Produk</a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($product->name) ?></li>
                    </ol>
                </nav>

                <!-- Product category -->
                <?php if (isset($product->category)): ?>
                    <div class="small mb-2 text-muted">
                        <i class="bi bi-tag me-1"></i>
                        <?= htmlspecialchars($product->category) ?>
                    </div>
                <?php endif; ?>

                <!-- Product name -->
                <h1 class="display-6 fw-bolder mb-3"><?= htmlspecialchars($product->name) ?></h1>

                <!-- Product price -->
                <div class="fs-4 mb-4">
                    <span class="text-primary fw-bold">Rp<?= number_format($product->price, 0, '.', ',') ?></span>
                    <?php if (isset($product->original_price) && $product->original_price > $product->price): ?>
                        <span class="text-muted text-decoration-line-through ms-2">
                            Rp<?= number_format($product->original_price, 0, '.', ',') ?>
                        </span>
                        <span class="badge bg-danger ms-2">
                            <?= round((($product->original_price - $product->price) / $product->original_price) * 100) ?>% OFF
                        </span>
                    <?php endif; ?>
                </div>

                <!-- Product description -->
                <?php if (!empty($product->description)): ?>
                    <p class="lead mb-4"><?= nl2br(htmlspecialchars($product->description)) ?></p>
                <?php endif; ?>

                <!-- Product specifications -->
                <div class="mb-4">
                    <h6 class="fw-bold">Spesifikasi Produk:</h6>
                    <ul class="list-unstyled">
                        <?php if (isset($product->stock)): ?>
                            <li><i class="bi bi-box me-2 text-primary"></i>Stok: <span class="fw-bold"><?= $product->stock ?> unit</span></li>
                        <?php endif; ?>
                        <?php if (isset($product->weight)): ?>
                            <li><i class="bi bi-weight me-2 text-primary"></i>Berat: <?= $product->weight ?>g</li>
                        <?php endif; ?>
                        <?php if (isset($product->expired_date)): ?>
                            <li><i class="bi bi-calendar me-2 text-primary"></i>Kedaluwarsa: <?= date('d/m/Y', strtotime($product->expired_date)) ?></li>
                        <?php endif; ?>
                        <li><i class="bi bi-shield-check me-2 text-primary"></i>Produk Original & Terjamin</li>
                        <li><i class="bi bi-truck me-2 text-primary"></i>Gratis Ongkir untuk pembelian >Rp100.000</li>
                    </ul>
                </div>

                <?php if ($user['role'] === 'user') : ?>
                    <!-- Add to cart section -->
                    <div class="card border-0 bg-light p-4">
                        <div class="row align-items-center">
                            <div class="col-4">
                                <div class="input-group">
                                    <button class="btn btn-outline-secondary" type="button" id="decreaseQty">
                                        <i class="bi bi-dash"></i>
                                    </button>
                                    <input class="form-control text-center" id="inputQuantity" type="number" value="1" min="1" max="<?= $product->stock ?? 99 ?>" />
                                    <button class="btn btn-outline-secondary" type="button" id="increaseQty">
                                        <i class="bi bi-plus"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="col-8">
                                <?php if (isLoggedIn()): ?>
                                    <?php if (isset($product->stock) && $product->stock > 0): ?>
                                        <button class="btn btn-primary btn-lg w-100 add-to-cart-btn"
                                            data-product-id="<?= $product->id ?>"
                                            data-product-name="<?= htmlspecialchars($product->name) ?>">
                                            <i class="bi bi-cart-plus me-2"></i>
                                            Tambah ke Keranjang
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-secondary btn-lg w-100" disabled>
                                            <i class="bi bi-x-circle me-2"></i>
                                            Stok Habis
                                        </button>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <button class="btn btn-outline-primary btn-lg w-100" onclick="loginRequired()">
                                        <i class="bi bi-box-arrow-in-right me-2"></i>
                                        Login untuk Beli
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Additional info -->
                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="bi bi-info-circle me-1"></i>
                                Konsultasikan dengan apoteker jika Anda memiliki kondisi medis tertentu
                            </small>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Back to shop button -->
                <div class="mt-4">
                    <a href="?page=shop" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Kembali ke Produk
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Related items section-->
<?php if (!empty($related_products)): ?>
    <section class="py-5">
        <div class="container px-4 px-lg-5 mt-5">
            <h2 class="fw-bolder mb-4">Produk Terkait</h2>
            <div class="row gx-4 gx-lg-5 row-cols-2 row-cols-md-3 row-cols-xl-4 justify-content-center">
                <?php foreach ($related_products as $related): ?>
                    <div class="col mb-5">
                        <div class="card h-100">
                            <!-- Product image-->
                            <img class="card-img-top"
                                src="/apotek-alifa/assets/img/product/uploads/<?= htmlspecialchars($related->image) ?>"
                                alt="<?= htmlspecialchars($related->name) ?>"
                                style="height: 200px; object-fit: cover;" />
                            <!-- Product details-->
                            <div class="card-body p-4">
                                <div class="text-center">
                                    <!-- Product name-->
                                    <h5 class="fw-bolder"><?= htmlspecialchars($related->name) ?></h5>
                                    <!-- Product price-->
                                    <p class="text-primary fw-bold">Rp<?= number_format($related->price, 0, '.', ',') ?></p>
                                </div>
                            </div>
                            <!-- Product actions-->
                            <div class="card-footer p-4 pt-0 border-top-0 bg-transparent">
                                <div class="text-center d-flex gap-2">
                                    <a class="btn btn-outline-primary btn-sm" href="?page=show&id=<?= $related->id ?>">
                                        <i class="bi bi-eye me-1"></i>Detail
                                    </a>
                                    <?php if (isLoggedIn() && $user['role'] === 'user'): ?>
                                        <button class="btn btn-primary btn-sm add-to-cart-btn"
                                            data-product-id="<?= $related->id ?>"
                                            data-product-name="<?= htmlspecialchars($related->name) ?>">
                                            <i class="bi bi-cart-plus me-1"></i>Tambah
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<!-- Toast Container -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1100;">
    <div id="productToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <i class="bi bi-cart-check text-success me-2"></i>
            <strong class="me-auto">Produk</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body">
            <!-- Toast message will be inserted here -->
        </div>
    </div>
</div>

<style>
    .card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .add-to-cart-btn {
        position: relative;
        overflow: hidden;
    }

    .add-to-cart-btn.loading::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 20px;
        height: 20px;
        margin: -10px 0 0 -10px;
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

    .nav-tabs .nav-link {
        color: #6c757d;
        border: none;
        border-bottom: 3px solid transparent;
    }

    .nav-tabs .nav-link.active {
        color: #0d6efd;
        border-bottom-color: #0d6efd;
        background: none;
    }

    .breadcrumb-item+.breadcrumb-item::before {
        content: "›";
        color: #6c757d;
    }
</style>

<script>
    // Quantity controls
    document.getElementById('decreaseQty').addEventListener('click', function() {
        const input = document.getElementById('inputQuantity');
        const currentValue = parseInt(input.value);
        if (currentValue > 1) {
            input.value = currentValue - 1;
        }
    });

    document.getElementById('increaseQty').addEventListener('click', function() {
        const input = document.getElementById('inputQuantity');
        const currentValue = parseInt(input.value);
        const maxValue = parseInt(input.max);
        if (currentValue < maxValue) {
            input.value = currentValue + 1;
        }
    });

    // Add to cart functionality
    function addToCart(productId, productName) {
        const btn = document.querySelector(`[data-product-id="${productId}"]`);
        const quantity = document.getElementById('inputQuantity').value;

        btn.classList.add('loading');
        btn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Menambahkan...';
        btn.disabled = true;

        fetch('/apotek-alifa/add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `product_id=${productId}&quantity=${quantity}`
            })
            .then(response => response.text())
            .then(text => {
                try {
                    const data = JSON.parse(text);

                    if (data.success) {
                        // Update cart count if provided
                        if (data.cart_count !== undefined) {
                            updateCartCount(data.cart_count);
                        }

                        // Show success message
                        showToast(data.message || 'Produk berhasil ditambahkan ke keranjang!', 'success');

                        // Success state
                        btn.innerHTML = '<i class="bi bi-cart-check me-2"></i>Berhasil Ditambah!';
                        btn.classList.remove('btn-primary');
                        btn.classList.add('btn-success');

                        // Reset after 3 seconds
                        setTimeout(() => {
                            btn.innerHTML = '<i class="bi bi-cart-plus me-2"></i>Tambah ke Keranjang';
                            btn.classList.remove('btn-success');
                            btn.classList.add('btn-primary');
                            btn.disabled = false;
                        }, 3000);
                    } else {
                        if (data.redirect) {
                            window.location.href = data.redirect;
                            return;
                        }
                        showToast(data.message || 'Terjadi kesalahan', 'error');
                        resetButton(btn);
                    }
                } catch (parseError) {
                    console.error('JSON parse error:', parseError);
                    showToast('Terjadi kesalahan sistem', 'error');
                    resetButton(btn);
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                showToast('Terjadi kesalahan jaringan', 'error');
                resetButton(btn);
            })
            .finally(() => {
                btn.classList.remove('loading');
            });
    }

    function resetButton(btn) {
        btn.innerHTML = '<i class="bi bi-cart-plus me-2"></i>Tambah ke Keranjang';
        btn.disabled = false;
    }

    function updateCartCount(count) {
        const cartBadges = document.querySelectorAll('.badge');
        cartBadges.forEach(badge => {
            if (badge.closest('[href*="cart"]')) {
                badge.textContent = count > 99 ? '99+' : count;
                badge.style.display = count > 0 ? 'inline-block' : 'none';
            }
        });
    }

    function showToast(message, type = 'success') {
        const toastEl = document.getElementById('productToast');
        const toastBody = toastEl.querySelector('.toast-body');
        const toastIcon = toastEl.querySelector('.toast-header i');

        toastBody.textContent = message;

        if (type === 'success') {
            toastIcon.className = 'bi bi-cart-check text-success me-2';
        } else {
            toastIcon.className = 'bi bi-exclamation-triangle text-danger me-2';
        }

        const toast = new bootstrap.Toast(toastEl);
        toast.show();
    }

    function loginRequired() {
        if (confirm('Anda harus login untuk menambahkan produk ke keranjang. Login sekarang?')) {
            window.location.href = '/apotek-alifa/auth/login.php';
        }
    }

    document.addEventListener('click', function(e) {
        if (e.target.closest('.add-to-cart-btn')) {
            const btn = e.target.closest('.add-to-cart-btn');
            const productId = btn.getAttribute('data-product-id');
            const productName = btn.getAttribute('data-product-name');

            addToCart(productId, productName);
        }
    });
</script>