<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-md-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="card-title">Daftar Pesanan</h4>
                        <p class="card-subtitle">
                            Semua pesanan yang telah kamu buat
                        </p>
                    </div>
                    <div class="d-flex gap-3 align-items-center">
                        <div>Urutkan pesanan berdasarkan status</div>
                        <form method="get" class="d-flex align-items-center justify-content-end gap-3" id="formEl">
                            <select class="form-select" name="category" id="selectEl" aria-label="Status select">
                                <option selected value="">Pilih status</option>
                                <option value="berhasil">>Berhasil</option>
                                <option value="tertunda">>Tertunda</option>
                                <option value="gagal">>Gagal</option>
                            </select>
                        </form>
                    </div>
                </div>
                <div class="table-responsive mt-4">
                    <table class="table mb-4 text-nowrap varient-table align-middle fs-3">
                        <thead>
                            <tr>
                                <th scope="col" class="px-0 text-muted">
                                    Nama Penerima
                                </th>
                                <th scope="col" class="px-0 text-muted">
                                    Total Harga
                                </th>
                                <th scope="col" class="px-0 text-muted">
                                    Status
                                </th>
                                <th scope="col" class="px-0 text-muted text-end">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- <tr>
                                <th colspan="4" class="text-center">
                                    Pesanan tidak ditemukan.
                                </th>
                            </tr> -->
                            <tr>
                                <td class="px-0">
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <h6 class="mb-0 fw-bolder">Nama Penerima</h6>
                                            <span class="text-muted">surel@contoh.net</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-0">Rp23,999</td>
                                <td class="px-0">
                                    <span class="badge bg-primary text-capitalize">Berhasil</span>
                                </td>
                                <td class="px-0 text-dark fw-medium text-end">
                                    <a href="/apotek-alifa/dashboard/user?page=order.show&order_id=1" class="text-info">Lihat</a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <nav aria-label="Page navigation">
                        <ul class="pagination">
                            <li class="page-item">
                                <a class="page-link" href="?page=order.index&pagination=1"><i class="ti ti-chevron-left"></i></a>
                            </li>
                            <li class="page-item">
                                <a class="page-link" href="?page=order.index&pagination=2">1</a>
                            </li>
                            <li class="page-item">
                                <a class="page-link" href="?page=order.index&pagination=3"><i class="ti ti-chevron-right"></i></a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>