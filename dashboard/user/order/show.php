<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="?page=order.index">Pesanan</a></li>
        <li class="breadcrumb-item"><a href="#" class="text-secondary">Detail</a></li>
    </ol>
</nav>
<h1 class="h3 mb-2 text-gray-800">Detail Pesanan</h1>
<p class="mb-4">Dapatkan informasi lengkap mengenai pesanan dan kelola pesanan atas nama <span class="fw-semibold text-capitalize">Nama Penerima</span></p>

<div class="row">
    <div class="col">
        <h2 class="h3 mb-3 text-black">Rincian Pengiriman</h2>
        <div class="p-3 p-lg-5 border bg-white">

            <div class="form-group mb-3 row">
                <div class="col">
                    <label for="recipient_name" class="text-black">Nama Penerima</label>
                    <input type="text" class="form-control" id="recipient_name" name="recipient_name" value="Nama Penerima" disabled>
                </div>
                <div class="col">
                    <label for="recipient_email" class="text-black">Email</label>
                    <input type="text" class="form-control" id="recipient_email" name="recipient_email" value="surel@net.org" disabled>
                </div>
            </div>

            <div class="form-group mb-3">
                <label for="street_address" class="text-black">Alamat Penerima</label>
                <input type="text" class="form-control" id="street_address" name="street_address" value="Alamat lengkap" disabled>
            </div>

            <div class="form-group mb-3">
                <label for="recipient_phone" class="text-black">Nomor Telepon</label>
                <input type="text" class="form-control" id="recipient_phone" name="recipient_phone" aria-describedby="phoneHelp" value="0876545433" disabled>
            </div>

            <div class="form-group">
                <label for="notes" class="text-black">Catatan</label>
                <textarea name="notes" id="notes" cols="30" rows="5" name="notes" class="form-control" disabled>Catatan</textarea>
            </div>

        </div>
    </div>

    <div class="col">
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="row align-items-center justify-content-between">
                    <div class="col">
                        <h2 class="h3 mb-3 text-black">Pesananmu</h2>
                    </div>
                    <div class="col text-end">
                        <span class="badge bg-primary text-capitalize">
                            berhasil
                        </span>
                    </div>
                </div>
                <div class="p-3 p-lg-5 border bg-white">
                    <p class="lead fs-6">
                        12 Juni 2025, 12:00 PM
                    </p>
                    <table class="table site-block-order-table mb-5">
                        <thead>
                            <th>Produk</th>
                            <th>Total</th>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Nama Produk <strong class="mx-2">x</strong>4</td>
                                <td>Rp34,980</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td class="text-black font-weight-bold"><strong>Total Pesanan</strong></td>
                                <td class="text-black font-weight-bold"><strong>Rp56,000</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="col-md-12">
                <div class="row align-items-center justify-content-between">
                    <div class="col">
                        <h2 class="h3 mb-3 text-black">Bukti Pembayaran</h2>
                    </div>
                    <div class="col">
                        <div class="alert alert-primary alert-dismissible fade show" role="alert">
                            Telah disetujui
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                </div>
                <div class="p-3 p-lg-5 border bg-white">
                    <div class="row">
                        <div class="col">
                            <img id="paymentProofImg" src="/apotek-alifa/assets/img/payments/payment_3_1750592861.jpg" alt="Bukti Pembayaran" style="width: 100%; height: auto; object-fit: cover; cursor: pointer;">
                        </div>
                        <div class="col">
                            <div class="mb-3">
                                <p>Gambar di samping adalah bukti pembayaran yang telah diunggah</p>
                            </div>
                            <form>
                                <div class="mb-3">
                                    <label for="proof_of_payment" class="form-label">File Bukti Pembayaran <span class="text-danger">*</span></label>
                                    <input class="form-control" type="file" id="proof_of_payment" name="proof_of_payment" accept="image/*,application/pdf" onchange="previewProof(event)">
                                    <div class="invalid-feedback">
                                        invalid message
                                    </div>
                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                </div>
                                <div class="mb-3" id="previewContainer"></div>
                                <button type="submit" class="btn btn-primary">Perbarui Bukti</button>
                            </form>
                            <form>
                                <div id="previewContainer" class="lead text-danger mb-4">Belum ada bukti pembayaran!</div>
                                <div class="mb-3">
                                    <label for="proof_of_payment" class="form-label">File Bukti Pembayaran <span class="text-danger">*</span></label>
                                    <input class="form-control" type="file" id="proof_of_payment" name="proof_of_payment" accept="image/*,application/pdf" onchange="previewProof(event)">
                                    <div class="invalid-feedback">
                                        Inlalid message
                                    </div>
                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                </div>
                                <button type="submit" class="btn btn-primary">Unggah Bukti</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const img = document.getElementById('paymentProofImg');
        let viewer;
        if (img) {
            viewer = new Viewer(img, {
                toolbar: true,
                navbar: false,
                title: false,
                movable: true,
                zoomable: true,
                scalable: true,
                transition: true,
                fullscreen: true,
            });
            img.addEventListener('click', function() {
                viewer.show();
            });
        }
    });

    function previewProof(event) {
        const file = event.target.files[0];
        const previewContainer = document.getElementById('previewContainer');
        previewContainer.innerHTML = '';
        if (!file) return;
        if (file.type.startsWith('image/')) {
            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.style.maxWidth = '100%';
            img.style.maxHeight = '300px';
            img.className = 'img-fluid border rounded';
            previewContainer.appendChild(img);
        } else {
            previewContainer.innerHTML = '<span class="text-danger">File tidak didukung.</span>';
        }
    }
</script>