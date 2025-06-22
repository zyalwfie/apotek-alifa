<?php
$user = getUserData();
?>

<div class="row">
    <div class="col-lg-4">
        <h5 class="card-title fw-semibold mb-3">Avatar</h5>
        <div class="card py-4">
            <img id="imgPreview" src="/apotek-alifa/assets/img/profile/<?= $user['avatar'] ?>" alt="<?= $user['username'] ?>" style="width: 81%; margin: auto;">
        </div>
    </div>
    <div class="col-lg-8">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="card-title fw-semibold">Detail Profil</h5>
            <!-- <div class="alert alert-success d-flex align-items-center gap-2" role="alert">
                <i class="ti ti-checks"></i>
                Berhasil ubah profil
            </div> -->
        </div>
        <div class="card">
            <div class="card-body">
                <div class="form-group mb-3">
                    <label for="full_name" class="form-label">Name Lengkap</label>
                    <input
                        type="text"
                        class="form-control" disabled name="full_name" value="" name="full_name"
                        placeholder="Belum ada nama lengkap">
                    <div class="invalid-feedback">
                        Pesan kesalahan
                    </div>
                </div>
                <div class="form-group mb-3">
                    <label for="full_name" class="form-label">Name Pengguna</label>
                    <input type="text" class="form-control" disabled name="username" value="">
                    <div class="invalid-feedback">
                        Pesan kesalahan
                    </div>
                </div>
                <div class="form-group mb-3">
                    <label for="full_name" class="form-label">Surel</label>
                    <input type="email" class="form-control" disabled name="email" value="">
                    <div class="invalid-feedback">
                        Pesan kesalahan
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>