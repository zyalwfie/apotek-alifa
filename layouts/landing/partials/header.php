<header id="header" class="header sticky-top">

    <div class="branding d-flex align-items-center">

        <div class="container position-relative d-flex align-items-center justify-content-between">
            <a href="index.php" class="logo d-flex align-items-center me-auto">
                <img src="../../assets/img/logo.png" alt="Apotek Alifa Logo" width="150">
            </a>

            <nav id="navmenu" class="navmenu">
                <ul>
                    <li><a href="#hero" class="<?= isset($_GET['page']) ? '' : 'active' ?>">Beranda<br></a></li>
                    <li><a href="#about">Tentang</a></li>
                    <li><a href="#services">Layanan</a></li>
                    <li><a href="?page=shop" class="<?= isset($_GET['page']) ? 'active' : '' ?>">Produk</a></li>
                    <li><a href="#contact">Kontak</a></li>
                </ul>
                <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
            </nav>

            <a class="cta-btn d-none d-sm-block" href="/apotek-alifa/auth/login.php">Masuk</a>

            <!-- <a href="?page=cart" class="btn">
                <div class="position-relative">
                    <i class="bi bi-cart"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill text-bg-danger">0<span class="visually-hidden">total carts</span></span>
                </div>
            </a>

            <a href="/apotek-alifa/layouts/dashboard/" class="btn">
                <i class="bi bi-person"></i>
            </a> -->
        </div>

    </div>

</header>