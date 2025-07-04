<?php
require_once '../../auth_functions.php';
require_once '../../cart_functions.php';
$user = getUserData();
$cartCount = isLoggedIn() ? getCartCount() : 0;
?>

<header id="header" class="header sticky-top">
    <div class="branding d-flex align-items-center">
        <div class="container position-relative d-flex align-items-center justify-content-between">
            <a href="/apotek-alifa/layouts/landing/" class="logo d-flex align-items-center">
                <img src="../../assets/img/logo.png" alt="Apotek Alifa Logo" width="150">
            </a>

            <nav id="navmenu" class="navmenu">
                <ul>
                    <li><a href="<?= isset($_GET['page']) ? '/apotek-alifa/layouts/landing#hero' : '#hero' ?>" class="<?= isset($_GET['page']) ? '' : 'active' ?>">Beranda<br></a></li>
                    <li><a href="<?= isset($_GET['page']) ? '/apotek-alifa/layouts/landing#about' : '#about' ?>">Tentang</a></li>
                    <li><a href="<?= isset($_GET['page']) ? '/apotek-alifa/layouts/landing#services' : '#services' ?>">Layanan</a></li>
                    <li><a href="<?= isset($_GET['page']) ? '/apotek-alifa/layouts/landing#faq' : '#faq' ?>">Pertanyaan</a></li>
                    <li><a href="<?= isset($_GET['page']) ? '/apotek-alifa/layouts/landing#contact' : '#contact' ?>">Kontak</a></li>
                    <li><a href="?page=shop" class="<?= isset($_GET['page']) && $_GET['page'] == 'shop' ? 'active' : '' ?>">Produk</a></li>
                </ul>
                <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
            </nav>

            <div class="header-actions d-flex align-items-center gap-3">
                <?php if (isLoggedIn()): ?>

                    <?php if ($user['peran'] === 'user') : ?>
                        <!-- Cart Button (hanya tampil jika login) -->
                        <a href="?page=cart" class="btn btn-outline-primary position-relative">
                            <i class="bi bi-cart"></i>
                            <?php if ($cartCount > 0): ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                    <?= $cartCount > 99 ? '99+' : $cartCount ?>
                                    <span class="visually-hidden">items in cart</span>
                                </span>
                            <?php endif; ?>
                        </a>
                    <?php endif; ?>

                    <!-- User Dropdown -->
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle d-flex align-items-center"
                            type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle me-2"></i>
                            <span class="d-none d-md-inline"><?= htmlspecialchars($user['nama_pengguna']) ?></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li>
                                <h6 class="dropdown-header">
                                    <i class="bi bi-person-circle me-2"></i>
                                    <?= htmlspecialchars($user['nama_pengguna']) ?>
                                </h6>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <a class="dropdown-item" href="/apotek-alifa/layouts/dashboard?page=profile.index">
                                    <i class="bi bi-person me-2"></i>Profil Saya
                                </a>
                            </li>
                            <?php if ($user['peran'] === 'user') : ?>
                                <li>
                                    <a class="dropdown-item" href="?page=cart">
                                        <i class="bi bi-cart me-2"></i>Keranjang
                                        <?php if ($cartCount > 0): ?>
                                            <span class="badge bg-primary ms-1"><?= $cartCount ?></span>
                                        <?php endif; ?>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="/apotek-alifa/layouts/dashboard/">
                                        <i class="bi bi-grid-1x2 me-2"></i>Dasbor
                                    </a>
                                </li>
                            <?php endif; ?>
                            <?php if ($user['peran'] == 'admin'): ?>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <a class="dropdown-item" href="/apotek-alifa/layouts/dashboard/">
                                        <i class="bi bi-speedometer2 me-2"></i>Dashboard Admin
                                    </a>
                                </li>
                            <?php endif; ?>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <a class="dropdown-item text-danger" href="../../logout.php"
                                    onclick="return confirm('Yakin ingin keluar?')">
                                    <i class="bi bi-box-arrow-right me-2"></i>Keluar
                                </a>
                            </li>
                        </ul>
                    </div>
                <?php else: ?>
                    <!-- Login Button (hanya tampil jika belum login) -->
                    <a class="cta-btn d-none d-sm-block" href="/apotek-alifa/auth/login.php">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Masuk
                    </a>

                    <!-- Mobile Login Button -->
                    <a class="btn btn-primary d-sm-none" href="/apotek-alifa/auth/login.php">
                        <i class="bi bi-box-arrow-in-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>

<!-- Toast Notification (optional) -->
<?php if (isset($_SESSION['notification'])): ?>
    <div class="position-fixed top-0 end-0 p-3" style="z-index: 1100;">
        <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <i class="bi bi-info-circle-fill text-primary me-2"></i>
                <strong class="me-auto">Notifikasi</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">
                <?= htmlspecialchars($_SESSION['notification']) ?>
            </div>
        </div>
    </div>
    <?php unset($_SESSION['notification']); ?>
<?php endif; ?>

<style>
    .header-actions .btn {
        border-radius: 8px;
        padding: 8px 12px;
    }

    .header-actions .dropdown-toggle::after {
        margin-left: 0.5em;
    }

    .dropdown-menu {
        min-width: 200px;
        border: 1px solid #dee2e6;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    .dropdown-item {
        padding: 0.5rem 1rem;
        display: flex;
        align-items: center;
    }

    .dropdown-item:hover {
        background-color: #f8f9fa;
    }

    .badge {
        font-size: 0.75em;
    }

    @media (max-width: 576px) {
        .header-actions {
            gap: 8px;
        }

        .header-actions .btn {
            padding: 6px 10px;
        }
    }
</style>