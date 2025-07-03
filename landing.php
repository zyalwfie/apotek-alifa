<?php
include('functions.php');

$products = getData('select * from obat limit 4');
?>

<!-- Hero Section -->
<section id="hero" class="hero section light-background">

    <img src="../../assets/img/hero-bg.jpg" alt="" data-aos="fade-in">

    <div class="container position-relative">

        <div class="welcome position-relative" data-aos="fade-down" data-aos-delay="100">
            <h2>SELAMAT DATANG DI APOTEK ALIFA</h2>
            <p>Tempat terbaik untuk kebutuhan obat dan kesehatan Anda</p>
        </div><!-- End Welcome -->

        <div class="content row gy-4">
            <div class="col-lg-4 d-flex align-items-stretch">
                <div class="why-box" data-aos="zoom-out" data-aos-delay="200">
                    <h3>Mengapa Pilih Kami?</h3>
                    <p>
                        Apotek Alifa hadir sebagai solusi praktis untuk kebutuhan obat-obatanmu. Kami menyediakan layanan pembelian obat secara online yang cepat, aman, dan terpercaya. Tanpa antre, tanpa ribet, cukup pesan dari rumah dan kami yang antar. Yuk, jaga kesehatan lebih mudah bareng Apotek Alifa!
                    </p>
                    <div class="text-center">
                        <a href="/apotek-alifa/layouts/landing?page=shop" class="more-btn"><span>Lihat Obat</span> <i class="bi bi-chevron-right"></i></a>
                    </div>
                </div>
            </div><!-- End Why Box -->

            <div class="col-lg-8 d-flex align-items-stretch">
                <div class="d-flex flex-column justify-content-center">
                    <div class="row gy-4">

                        <div class="col-xl-4 d-flex align-items-stretch">
                            <div class="icon-box" data-aos="zoom-out" data-aos-delay="300">
                                <i class="bi bi-truck"></i>
                                <h4>Pengiriman Cepat & Aman</h4>
                                <p>Kami bekerja sama dengan layanan ekspedisi terpercaya agar obat sampai tepat waktu dan dalam kondisi terbaik.</p>
                            </div>
                        </div><!-- End Icon Box -->

                        <div class="col-xl-4 d-flex align-items-stretch">
                            <div class="icon-box" data-aos="zoom-out" data-aos-delay="400">
                                <i class="bi bi-capsule"></i>
                                <h4>Produk Asli & Lengkap</h4>
                                <p>Semua produk kami 100% original dan tersedia lengkap dari obat bebas sampai resep dokter.</p>
                            </div>
                        </div><!-- End Icon Box -->

                        <div class="col-xl-4 d-flex align-items-stretch">
                            <div class="icon-box" data-aos="zoom-out" data-aos-delay="500">
                                <i class="bi bi-wallet2"></i>
                                <h4>Harga Terjangkau</h4>
                                <p>Kami percaya semua orang berhak sehat. Maka dari itu, harga obat di Apotek Alifa dijamin terjangkau dan transparan.</p>
                            </div>
                        </div><!-- End Icon Box -->

                    </div>
                </div>
            </div>
        </div><!-- End  Content-->

    </div>

</section><!-- /Hero Section -->

<!-- About Section -->
<section id="about" class="about section">

    <div class="container">

        <div class="row gy-4 gx-5">

            <div class="col-lg-6 position-relative align-self-start" data-aos="fade-up" data-aos-delay="200">
                <img src="../../assets/img/about.png" class="img-fluid" alt="Gambar Tentang Kami">
            </div>

            <div class="col-lg-6 content" data-aos="fade-up" data-aos-delay="100">
                <h3>Tentang Kami</h3>
                <p>
                    Apotek Alifa adalah apotek lokal yang berlokasi di pinggir jalan utama dan telah melayani masyarakat sekitar dengan sepenuh hati sejak pertama kali dibuka. Berangkat dari semangat untuk membantu lebih banyak orang mendapatkan akses obat dengan mudah dan cepat, kini Apotek Alifa hadir juga secara online!
                </p>
                <ul>
                    <li>
                        <i class="fa-solid fa-capsules"></i>
                        <div>
                            <h5>Obat-Obatan Lengkap & Terpercaya</h5>
                            <p>Kami menyediakan berbagai jenis obat, dari resep dokter hingga obat bebas yang telah terdaftar resmi dan aman digunakan.</p>
                        </div>
                    </li>
                    <li>
                        <i class="fa-solid fa-headset"></i>
                        <div>
                            <h5>Layanan Cepat & Ramah</h5>
                            <p>Tim kami yang berpengalaman siap melayani kamu dengan ramah, baik langsung di apotek maupun melalui layanan online.</p>
                        </div>
                    </li>
                    <li>
                        <i class="fa-solid fa-shield-heart"></i>
                        <div>
                            <h5>Peduli Kesehatan Keluarga</h5>
                            <p>Kami bukan hanya tempat membeli obat, tapi juga mitra kesehatan keluarga yang siap mendengarkan dan membantu kapan pun dibutuhkan.</p>
                        </div>
                    </li>
                </ul>
            </div>

        </div>

    </div>

</section><!-- /About Section -->

<!-- Services Section -->
<section id="services" class="services section">

    <!-- Section Title -->
    <div class="container section-title" data-aos="fade-up">
        <h2>Layanan</h2>
        <p>Kami hadir nggak cuma untuk jualan obat, tapi juga bantu kamu dan keluarga tetap sehat dengan layanan yang praktis dan terpercaya</p>
    </div><!-- End Section Title -->

    <div class="container">

        <div class="row gy-4">

            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="service-item  position-relative">
                    <div class="icon">
                        <i class="fas fa-prescription-bottle-alt"></i>
                    </div>
                    <a href="index.php" class="stretched-link">
                        <h3>Penjualan Obat Resep & Bebas</h3>
                    </a>
                    <p>Kami menyediakan obat-obatan lengkap, mulai dari obat resep dokter hingga obat bebas yang aman dan legal. Cukup tunjukkan resep atau konsultasi dengan kami!</p>
                </div>
            </div><!-- End Service Item -->

            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                <div class="service-item position-relative">
                    <div class="icon">
                        <i class="fas fa-truck-medical"></i>
                    </div>
                    <a href="#" class="stretched-link">
                        <h3>Layanan Antar Obat ke Rumah</h3>
                    </a>
                    <p>Nggak sempat ke apotek? Tenang! Kami bisa antar obat langsung ke rumah kamu dengan cepat dan aman. Bisa pesan via WhatsApp atau website.</p>
                </div>
            </div><!-- End Service Item -->

            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
                <div class="service-item position-relative">
                    <div class="icon">
                        <i class="fas fa-user-doctor"></i>
                    </div>
                    <a href="#" class="stretched-link">
                        <h3>Konsultasi Kesehatan Gratis</h3>
                    </a>
                    <p>Punya pertanyaan soal obat, dosis, atau gejala ringan? Tim apoteker kami siap bantu jawab secara gratis dan ramah, baik langsung maupun online.</p>
                </div>
            </div><!-- End Service Item -->

            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="400">
                <div class="service-item position-relative">
                    <div class="icon">
                        <i class="fas fa-heartbeat"></i>
                    </div>
                    <a href="#" class="stretched-link">
                        <h3>Produk Kesehatan Keluarga & Bayi</h3>
                    </a>
                    <p>Selain obat, kami juga menyediakan kebutuhan kesehatan lainnya seperti vitamin, suplemen, alat kesehatan, dan perlengkapan bayi.</p>
                    <a href="#" class="stretched-link"></a>
                </div>
            </div><!-- End Service Item -->

            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="500">
                <div class="service-item position-relative">
                    <div class="icon">
                        <i class="fas fa-stethoscope"></i>
                    </div>
                    <a href="#" class="stretched-link">
                        <h3>Cek Tekanan Darah & Gula Darah</h3>
                    </a>
                    <p>Datang langsung ke apotek dan kamu bisa cek tekanan darah atau gula darah dengan cepat. Hasilnya bisa kamu gunakan sebagai acuan ke dokter.</p>
                    <a href="#" class="stretched-link"></a>
                </div>
            </div><!-- End Service Item -->

            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="600">
                <div class="service-item position-relative">
                    <div class="icon">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <a href="#" class="stretched-link">
                        <h3>Pembayaran Mudah via Transfer Bank</h3>
                    </a>
                    <p>Kami menyediakan metode pembayaran yang aman dan praktis melalui transfer bank. Setelah melakukan pembayaran, Anda cukup mengunggah bukti transfer untuk proses verifikasi yang cepat.</p>
                    <a href="#" class="stretched-link"></a>
                </div>
            </div><!-- End Service Item -->

        </div>

    </div>

</section><!-- /Services Section -->

<!-- Faq Section -->
<section id="faq" class="faq section light-background">

    <!-- Section Title -->
    <div class="container section-title" data-aos="fade-up">
        <h2>Pertanyaan yang Sering Diajukan</h2>
        <p>Necessitatibus eius consequatur ex aliquid fuga eum quidem sint consectetur velit</p>
    </div><!-- End Section Title -->

    <div class="container">

        <div class="row justify-content-center">

            <div class="col-lg-10" data-aos="fade-up" data-aos-delay="100">

                <div class="faq-container">

                    <div class="faq-item faq-active">
                        <h3>Apa saja jenis obat yang tersedia di apotek ini?</h3>
                        <div class="faq-content">
                            <p>Kami menyediakan berbagai obat resep dan non-resep, mulai dari obat umum, vitamin, hingga produk perawatan kesehatan.</p>
                        </div>
                        <i class="faq-toggle bi bi-chevron-right"></i>
                    </div><!-- End Faq item-->

                    <div class="faq-item">
                        <h3>Apakah saya bisa membeli obat tanpa resep dokter?</h3>
                        <div class="faq-content">
                            <p>Ya, kami menyediakan berbagai obat yang bisa dibeli tanpa resep dokter, seperti obat batuk, flu, dan vitamin.</p>
                        </div>
                        <i class="faq-toggle bi bi-chevron-right"></i>
                    </div><!-- End Faq item-->

                    <div class="faq-item">
                        <h3>Bagaimana cara memesan obat secara online?</h3>
                        <div class="faq-content">
                            <p>Anda dapat memesan obat melalui website kami, memilih produk yang diinginkan, dan melakukan pembayaran melalui metode yang tersedia.</p>
                        </div>
                        <i class="faq-toggle bi bi-chevron-right"></i>
                    </div><!-- End Faq item-->

                    <div class="faq-item">
                        <h3>Apakah apotek ini menyediakan layanan pengantaran?</h3>
                        <div class="faq-content">
                            <p>Ya, kami menyediakan layanan pengantaran untuk area tertentu. Anda bisa menghubungi kami untuk informasi lebih lanjut.</p>
                        </div>
                        <i class="faq-toggle bi bi-chevron-right"></i>
                    </div><!-- End Faq item-->

                    <div class="faq-item">
                        <h3>Apakah harga obat di apotek ini bersaing?</h3>
                        <div class="faq-content">
                            <p>Kami selalu berusaha memberikan harga yang kompetitif dengan kualitas obat yang terjamin.</p>
                        </div>
                        <i class="faq-toggle bi bi-chevron-right"></i>
                    </div><!-- End Faq item-->

                    <div class="faq-item">
                        <h3>Apa yang harus saya lakukan jika obat yang saya cari tidak tersedia?</h3>
                        <div class="faq-content">
                            <p>Jika obat yang Anda cari tidak tersedia, Anda dapat menghubungi kami untuk mengetahui apakah kami bisa melakukan pemesanan khusus atau memberikan alternatif produk.</p>
                        </div>
                        <i class="faq-toggle bi bi-chevron-right"></i>
                    </div><!-- End Faq item-->

                </div>

            </div><!-- End Faq Column-->

        </div>

    </div>

</section><!-- /Faq Section -->

<!-- Contact Section -->
<section id="contact" class="contact section">

    <!-- Section Title -->
    <div class="container section-title" data-aos="fade-up">
        <h2>Kontak Kami</h2>
        <p>Hubungi kami untuk informasi lebih lanjut atau kunjungi lokasi kami</p>
    </div><!-- End Section Title -->

    <div class="mb-5" data-aos="fade-up" data-aos-delay="200">
        <iframe style="border:0; width: 100%; height: 270px;" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3944.115736790266!2d116.2299152750619!3d-8.68054279136755!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2dcdbb236e7f2cbd%3A0xd0e84afaf1f975fb!2sApotek%20Alifa!5e0!3m2!1sen!2sid!4v1750042353219!5m2!1sen!2sid" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
    </div><!-- End Google Maps -->

    <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="row gy-4">

            <div class="col-lg-4">
                <div class="info-item d-flex" data-aos="fade-up" data-aos-delay="300">
                    <i class="bi bi-geo-alt flex-shrink-0"></i>
                    <div>
                        <h3>Lokasi</h3>
                        <p>Jl. Raden Puguh, Nyerot, Kec. Praya, Kabupaten Lombok Tengah, Nusa Tenggara Bar.</p>
                    </div>
                </div><!-- End Info Item -->

                <div class="info-item d-flex" data-aos="fade-up" data-aos-delay="400">
                    <i class="bi bi-telephone flex-shrink-0"></i>
                    <div>
                        <h3>Telepon</h3>
                        <p>087815509458</p>
                    </div>
                </div><!-- End Info Item -->

                <div class="info-item d-flex" data-aos="fade-up" data-aos-delay="500">
                    <i class="bi bi-envelope flex-shrink-0"></i>
                    <div>
                        <h3>Email</h3>
                        <p>apotekalifa@gmail.com</p>
                    </div>
                </div><!-- End Info Item -->

            </div>

            <div class="col-lg-8">
                <form action="forms/contact.php" method="post" class="php-email-form" data-aos="fade-up" data-aos-delay="200">
                    <div class="row gy-4">

                        <div class="col-md-6">
                            <input type="text" name="name" class="form-control" placeholder="Tulis namamu di sini" required="">
                        </div>

                        <div class="col-md-6 ">
                            <input type="email" class="form-control" name="email" placeholder="Tulis namamu di sini" required="">
                        </div>

                        <div class="col-md-12">
                            <input type="text" class="form-control" name="subject" placeholder="Tulis subjeknya di sini" required="">
                        </div>

                        <div class="col-md-12">
                            <textarea class="form-control" name="message" rows="6" placeholder="Apa pesanmu" required=""></textarea>
                        </div>

                        <div class="col-md-12 text-center">
                            <div class="loading">Memuat</div>
                            <div class="error-message"></div>
                            <div class="sent-message">Pesan Anda telah terkirim. Terima kasih!</div>

                            <button type="submit">Kirim Pesan</button>
                        </div>

                    </div>
                </form>
            </div><!-- End Contact Form -->

        </div>

    </div>

</section><!-- /Contact Section -->