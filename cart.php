<section>
    <div class="container" style="margin-bottom: 8rem;">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Gambar</th>
                    <th scope="col">Nama</th>
                    <th scope="col">Harga</th>
                    <th scope="col">Kuantitas</th>
                    <th scope="col">Hapus</th>
                </tr>
            </thead>
            <tbody class="table-group-divider">
                <tr class="align-middle">
                    <th scope="row">1</th>
                    <td>
                        <img src="/apotek-alifa/assets/img/product/uploads/default.png" alt="Gambar Produk" width="100" height="100" style="object-fit: cover;">
                    </td>
                    <td>Amoksilin</td>
                    <td>Rp30,000</td>
                    <td>
                        <div class="input-group mb-3 d-flex align-items-center quantity-container" style="max-width: 120px">
                            <div class="input-group-prepend">
                                <form>
                                    <button class="btn btn-outline-black decrease" type="submit">
                                        <i class="bi bi-dash-lg"></i>
                                    </button>
                                </form>
                            </div>
                            <input type="text" class="form-control text-center quantity-amount" value="1" aria-label="Show quantity amount" aria-describedby="button" readonly />
                            <div class="input-group-append">
                                <form>
                                    <button
                                        class="btn btn-outline-black increase"
                                        type="submit">
                                        <i class="bi bi-plus-lg"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </td>
                    <td>
                        <form>
                            <button
                                type="submit"
                                class="btn btn-black btn-sm">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="container">
        <form class="row g-5">
            <div class="col-md-5 order-md-last">
                <h4
                    class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-primary">Keranjangmu</span>
                    <span class="badge bg-primary rounded-pill">3</span>
                </h4>
                <ul class="list-group mb-3">
                    <li
                        class="list-group-item d-flex justify-content-between lh-sm">
                        <div>
                            <h6 class="my-0">Nama produk</h6>
                            <small class="text-body-secondary">Deskripsi singkat</small>
                        </div>
                        <span class="text-body-secondary">Rp10,900</span>
                    </li>
                    <li
                        class="list-group-item d-flex justify-content-between lh-sm">
                        <div>
                            <h6 class="my-0">Nama produk</h6>
                            <small class="text-body-secondary">Deskripsi singkat</small>
                        </div>
                        <span class="text-body-secondary">Rp90,000</span>
                    </li>
                    <li
                        class="list-group-item d-flex justify-content-between lh-sm">
                        <div>
                            <h6 class="my-0">Nama produk</h6>
                            <small class="text-body-secondary">Deskripsi singkat</small>
                        </div>
                        <span class="text-body-secondary">Rp5,000</span>
                    </li>
                    <li
                        class="list-group-item d-flex justify-content-between">
                        <span>Total (IDR)</span> <strong>Rp20,000</strong>
                    </li>
                </ul>
                <hr class="my-4" />
                <h4 class="mb-3">Transfer ke rekening di bawah</h4>
                <div class="my-3">
                    <div class="d-flex gap-4 align-items-center">
                        <img src="/apotek-alifa/assets/img/bank/bca.svg" alt="Logo Bank" width="100">
                        <div class="d-flex flex-column">
                            <span class="fw-semibold">0987654</span>
                            <small>atas nama Salman Alfarizi</small>
                        </div>
                    </div>
                </div>
                <hr class="my-4" />
                <button
                    class="w-100 btn btn-primary btn-lg"
                    type="submit">
                    Lanjutkan ke Pembayaran
                </button>
            </div>
            <div class="col-md-7">
                <h4 class="mb-3">Alamat pengiriman</h4>
                <div class="row g-3">
                    <div class="col-sm-12">
                        <label for="recipient_name" class="form-label">Nama penerima</label>
                        <input
                            type="text"
                            class="form-control"
                            id="recipient_name"
                            placeholder=""
                            value=""
                            required />
                        <div class="invalid-feedback">
                            Pesan tidak valid
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <label for="email" class="form-label">Surel</label>
                        <div class="input-group has-validation">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input
                                type="text"
                                class="form-control"
                                id="email"
                                required />
                            <div class="invalid-feedback">
                                Pesan tidak valid
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <label for="address" class="form-label">Alamat lengkap</label>
                        <textarea class="form-control" id="address" rows="4" required></textarea>
                        <div class="invalid-feedback">
                            Pesan tidak valid
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <label for="recipient_phone" class="form-label">Nomor telepon</label>
                        <div class="input-group has-validation">
                            <span class="input-group-text"><i class="bi bi-phone"></i></span>
                            <input
                                type="number"
                                class="form-control"
                                id="recipient_phone"
                                required />
                            <div class="invalid-feedback">
                                Pesan tidak valid
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <label for="notes" class="form-label">Catatan</label>
                        <textarea class="form-control" id="notes" rows="4" required></textarea>
                        <div class="invalid-feedback">
                            Pesan tidak valid
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>