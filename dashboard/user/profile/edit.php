<?php
$user = getUserData();
?>

<form action="" class="row" method="post">
    <div class="col-lg-4">
        <div class="card pt-4">
            <img id="imgPreview" src="/apotek-alifa/assets/img/profile/<?= $user['avatar'] ?>" alt="<?= $user['username'] ?>" style="width: 81%; margin: auto;">
            <div class="card-body">
                <div class="row row-cols-4 justify-content-center align-items-center gy-3">
                    <?php for ($i = 1; $i <= 8; $i++) : ?>
                        <div class="col input-container">
                            <input class="radio-input" type="radio" name="avatar" value="user-<?= $i ?>.svg" id="avatar-<?= $i ?>">
                            <label for="avatar-<?= $i ?>" class="avatar-label">
                                <img src="/apotek-alifa/assets/img/profile/user-<?= $i ?>.svg" alt="Profile <?= $i ?>" style="width: 100%; object-fit: cover;">
                            </label>
                        </div>
                    <?php endfor ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <h5 class="card-title fw-semibold mb-4">Detail Profil</h5>
        <div class="card">
            <div class="card-body">
                <div class="form-group mb-3">
                    <label for="full_name" class="form-label">Name Lengkap</label>
                    <input
                        type="text"
                        class="form-control" name="full_name" value="" name="full_name"
                        placeholder="Belum ada nama lengkap">
                    <div class="invalid-feedback">
                        Pesan kesalahan
                    </div>
                </div>
                <div class="form-group mb-3">
                    <label for="full_name" class="form-label">Name Pengguna</label>
                    <input type="text" class="form-control" name="username" value="" required>
                    <div class="invalid-feedback">
                        Pesan kesalahan
                    </div>
                </div>
                <div class="form-group mb-3">
                    <label for="full_name" class="form-label">Surel</label>
                    <input type="email" class="form-control" name="email" value="" required>
                    <div class="invalid-feedback">
                        Pesan kesalahan
                    </div>
                </div>
                <div class="d-flex gap-2 align-items-center justify-content-end">
                    <a href="/apotek-alifa/layouts/dashboard?page=profile.index" class="btn btn-outline-secondary">Kembali</a>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    const imgPreview = document.querySelector('#imgPreview');
    const inputRadios = document.querySelectorAll('input[type="radio"');

    inputRadios.forEach(radio => {
        radio.addEventListener('click', () => {
            imgPreview.src = `/apotek-alifa/assets/img/profile/${radio.value}`
        })
    });
</script>