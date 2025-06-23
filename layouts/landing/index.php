<?php ob_start() ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Apotek Alifa</title>
    <meta name="description" content="Website Apotek Alifa adalah platform digital yang menyediakan informasi lengkap mengenai layanan apotek, produk kesehatan, konsultasi dengan apoteker, serta pemesanan obat secara online. Dengan tampilan modern dan navigasi yang mudah, pengunjung dapat mengetahui berbagai layanan, jadwal dokter, serta melakukan janji temu atau konsultasi secara praktis. Website ini juga menampilkan profil apotek, galeri, testimoni pelanggan, serta informasi kontak untuk memudahkan komunikasi dan pelayanan kepada masyarakat.">
    <meta name="keywords" content="apotek, apotek alifa, obat, kesehatan, konsultasi apoteker, beli obat online, layanan kesehatan, resep dokter, farmasi, toko obat, vitamin, suplemen, konsultasi kesehatan, pelayanan apotek, informasi obat, apotek terpercaya">

    <!-- Favicons -->
    <link rel="shortcut icon" href="../../assets/img/favicon.ico" type="image/x-icon">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="../../assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../assets/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../../assets/aos/aos.css" rel="stylesheet">
    <link href="../../assets/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="../../assets/glightbox/css/glightbox.min.css" rel="stylesheet">
    <link href="../../assets/swiper/swiper-bundle.min.css" rel="stylesheet">

    <!-- Main CSS File -->
    <link href="../../assets/css/main.css" rel="stylesheet">
</head>

<body class="index-page">

    <?php include('partials/header.php') ?>

    <main class="main">
        <?php include('content.php') ?>
    </main>

    <?php include('partials/footer.php') ?>

    <!-- Scroll Top -->
    <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

    <!-- Preloader -->
    <div id="preloader"></div>

    <!-- Vendor JS Files -->
    <script src="../../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/php-email-form/validate.js"></script>
    <script src="../../assets/aos/aos.js"></script>
    <script src="../../assets/glightbox/js/glightbox.min.js"></script>
    <script src="../../assets/purecounter/purecounter_vanilla.js"></script>
    <script src="../../assets/swiper/swiper-bundle.min.js"></script>

    <!-- Main JS File -->
    <script src="../../assets/js/main.js"></script>

</body>

</html>
<?php ob_end_flush() ?>