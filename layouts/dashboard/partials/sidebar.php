<?php
$user = getUserData();
?>

<!-- Sidebar Start -->
<aside class="left-sidebar">
    <div class="brand-logo d-flex align-items-center justify-content-between">
        <a href="./index.html" class="text-nowrap logo-img">
            <img src="/apotek-alifa/assets/img/logo.png" alt="Apotek Alifa Logo" class="w-100" width="100" style="object-fit: cover;" />
        </a>
        <div class="close-btn d-xl-none d-block sidebartoggler cursor-pointer" id="sidebarCollapse">
            <i class="ti ti-x fs-6"></i>
        </div>
    </div>
    <!-- Sidebar navigation-->
    <nav class="sidebar-nav scroll-sidebar" data-simplebar="">
        <ul id="sidebarnav">
            <li class="nav-small-cap">
                <iconify-icon icon="solar:menu-dots-linear" class="nav-small-cap-icon fs-4"></iconify-icon>
                <span class="hide-menu">Beranda</span>
            </li>
            <li class="sidebar-item">
                <a class="sidebar-link" href="/apotek-alifa/layouts/landing/" aria-expanded="false">
                    <i class="ti ti-home"></i>
                    <span class="hide-menu">Kembali</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a class="sidebar-link <?= !isset($_GET['page']) ? 'active' : '' ?>" href="<?= $user['peran'] === 'admin' ? '/apotek-alifa/layouts/dashboard/' : '/apotek-alifa/layouts/dashboard/' ?>" aria-expanded="false">
                    <i class="ti ti-layout"></i>
                    <span class="hide-menu">Dasbor</span>
                </a>
            </li>
            <li>
                <span class="sidebar-divider lg"></span>
            </li>
            <li class="nav-small-cap">
                <iconify-icon icon="solar:menu-dots-linear" class="nav-small-cap-icon fs-4"></iconify-icon>
                <span class="hide-menu">Manajemen</span>
            </li>
            <?php if ($user['peran'] === 'admin') : ?>
                <li class="sidebar-item">
                    <a class="sidebar-link <?php if (isset($_GET['page'])) : ?> <?= $_GET['page'] === 'user.index' ? 'active' : '' ?> <?php endif; ?> justify-content-between"
                        href="/apotek-alifa/layouts/dashboard?page=user.index"
                        aria-expanded="false">
                        <div class="d-flex align-items-center gap-3">
                            <span class="d-flex">
                                <i class="ti ti-users"></i>
                            </span>
                            <span class="hide-menu">Pengguna</span>
                        </div>

                    </a>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link <?php if (isset($_GET['page'])) : ?> <?= $_GET['page'] === 'product.index' ? 'active' : '' ?> <?php endif; ?> justify-content-between"
                        href="/apotek-alifa/layouts/dashboard?page=product.index" aria-expanded="false">
                        <div class="d-flex align-items-center gap-3">
                            <span class="d-flex">
                                <i class="ti ti-briefcase"></i>
                            </span>
                            <span class="hide-menu">Produk</span>
                        </div>

                    </a>
                </li>
            <?php endif; ?>

            <li class="sidebar-item">
                <a class="sidebar-link justify-content-between <?php if (isset($_GET['page'])) : ?> <?= $_GET['page'] === 'order.index' || $_GET['page'] === 'order.show' ? 'active' : '' ?> <?php endif; ?>"
                    href="/apotek-alifa/layouts/dashboard?page=order.index" aria-expanded="false">
                    <div class="d-flex align-items-center gap-3">
                        <span class="d-flex">
                            <i class="ti ti-shopping-cart"></i>
                        </span>
                        <span class="hide-menu">Pesanan</span>
                    </div>

                </a>
            </li>

            <?php if ($user['peran'] === 'user') : ?>
                <li class="sidebar-item">
                    <a class="sidebar-link justify-content-between <?php if (isset($_GET['page'])) : ?> <?= $_GET['page'] === 'order_history.index' || $_GET['page'] === 'order_history.show' ? 'active' : '' ?> <?php endif; ?>"
                        href="/apotek-alifa/layouts/dashboard?page=order_history.index" aria-expanded="false">
                        <div class="d-flex align-items-center gap-3">
                            <span class="d-flex">
                                <i class="ti ti-history"></i>
                            </span>
                            <span class="hide-menu">Riwayat Pesanan</span>
                        </div>
                    </a>
                </li>
            <?php endif; ?>

            <li>
                <span class="sidebar-divider lg"></span>
            </li>
            <li class="nav-small-cap">
                <iconify-icon icon="solar:menu-dots-linear" class="nav-small-cap-icon fs-4"></iconify-icon>
                <span class="hide-menu">Pengaturan</span>
            </li>
            <li class="sidebar-item">
                <a class="sidebar-link <?php if (isset($_GET['page'])) : ?> <?= $_GET['page'] === 'profile.index' ? 'active' : '' ?> <?php endif; ?> justify-content-between" href="/apotek-alifa/layouts/dashboard?page=profile.index" aria-expanded="false">
                    <div class="d-flex align-items-center gap-3">
                        <span class="d-flex">
                            <i class="ti ti-user"></i>
                        </span>
                        <span class="hide-menu">Profil Saya</span>
                    </div>

                </a>
            </li>
            <li class="sidebar-item">
                <a class="sidebar-link justify-content-between <?php if (isset($_GET['page'])) : ?> <?= $_GET['page'] === 'profile.edit' ? 'active' : '' ?> <?php endif; ?>"
                    href="/apotek-alifa/layouts/dashboard?page=profile.edit"
                    aria-expanded="false">
                    <div class="d-flex align-items-center gap-3">
                        <span class="d-flex">
                            <i class="ti ti-pencil"></i>
                        </span>
                        <span class="hide-menu">Ubah Profil</span>
                    </div>

                </a>
            </li>
            <li class="sidebar-item">
                <a class="sidebar-link justify-content-between"
                    href="/apotek-alifa/logout.php" aria-expanded="false">
                    <div class="d-flex align-items-center gap-3">
                        <span class="d-flex">
                            <i class="ti ti-transfer-out"></i>
                        </span>
                        <span class="hide-menu">Keluar</span>
                    </div>

                </a>
            </li>
        </ul>
    </nav>
    <!-- End Sidebar navigation -->
</aside>
<!--  Sidebar End -->