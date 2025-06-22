<section>
    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 60vh;">
        <form action="#" method="post" enctype="multipart/form-data" style="width: 100%; max-width: 400px; text-align: center;">
            <label for="buktiPembayaran" style="font-weight: 500; margin-bottom: 1rem; display: block;">Unggah Bukti Pembayaran</label>
            <input type="file" id="buktiPembayaran" name="buktiPembayaran" class="form-control" accept="image/*,application/pdf" style="margin-bottom: 1rem; display: block; margin-left: auto; margin-right: auto;" required>
            <div style="display: flex; justify-content: center; gap: 1rem;">
                <button type="button" onclick="window.location.href='shop.php'" class="btn btn-secondary">Lakukan Nanti</button>
                <button type="submit" class="btn btn-primary">Unggah Bukti</button>
            </div>
        </form>
    </div>
</section>