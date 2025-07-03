<?php
require_once '../../product_functions.php';
require_once '../../order_functions.php';

requireAdmin();

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$currentPage = isset($_GET['p']) ? max(1, intval($_GET['p'])) : 1;
$itemsPerPage = 5;

$result = getAllProductsWithPagination($search, $category, $currentPage, $itemsPerPage);
$products = $result['products'];
$totalPages = $result['total_pages'];
$totalItems = $result['total'];

$categories = getAllCategories();

$success_message = isset($_SESSION['success']) ? $_SESSION['success'] : '';
$error_message = isset($_SESSION['error']) ? $_SESSION['error'] : '';
unset($_SESSION['success'], $_SESSION['error']);
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">

                <div class="d-md-flex align-items-center justify-content-between mb-4">
                    <div>
                        <h4 class="card-title">Kelola Produk</h4>
                        <p class="card-subtitle">
                            Manajemen semua produk apotek
                            <?php if ($totalItems > 0): ?>
                                <span class="badge bg-primary ms-2"><?= $totalItems ?> produk</span>
                            <?php endif; ?>
                        </p>
                    </div>

                    <div class="d-flex gap-3 align-items-center">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                            <i class="ti ti-plus me-1"></i>Tambah
                        </button>

                        <form method="get" class="d-flex align-items-center gap-3">
                            <input type="hidden" name="page" value="product.index">

                            <div class="position-relative">
                                <input type="text"
                                    class="form-control"
                                    name="search"
                                    placeholder="Cari produk..."
                                    value="<?= htmlspecialchars($search) ?>"
                                    style="min-width: 250px;">
                                <button type="submit" class="btn btn-sm position-absolute end-0 top-50 translate-middle-y me-1">
                                    <i class="ti ti-search"></i>
                                </button>
                            </div>

                            <select class="form-select" name="category" style="min-width: 150px;" onchange="this.form.submit()">
                                <option value="">Semua Kategori</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= $category == $cat['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['nama_kategori']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <?php if (!empty($search) || !empty($category)): ?>
                                <a href="?page=product.index" class="btn btn-outline-secondary">
                                    <i class="ti ti-x"></i>
                                </a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

                <?php if (isset($_SESSION['message'])) : ?>
                    <div class="alert alert-<?= $_SESSION['message_type'] ?> d-flex gap-1 align-items-center" role="alert">
                        <?php if ($_SESSION['message_type'] === 'success') : ?>
                            <i class="ti ti-check"></i>
                        <?php else : ?>
                            <i class="ti ti-x"></i>
                        <?php endif; ?>
                        <div>
                            <?= $_SESSION['message'] ?>
                        </div>
                    </div>
                    <?php
                    unset($_SESSION['message']);
                    unset($_SESSION['message_type']);
                    ?>
                <?php endif; ?>

                <div class="table-responsive mt-4">
                    <?php if (empty($products)): ?>
                        <div class="text-center py-5">
                            <i class="ti ti-package-off" style="font-size: 4rem; color: #6c757d;"></i>
                            <h5 class="mt-3 text-muted">Tidak ada produk ditemukan</h5>
                            <?php if (!empty($search) || !empty($category)): ?>
                                <p class="text-muted">Coba ubah kriteria pencarian atau filter</p>
                                <a href="?page=product.index" class="btn btn-outline-primary">Lihat Semua Produk</a>
                            <?php else: ?>
                                <p class="text-muted">Belum ada produk yang ditambahkan</p>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                                    <i class="ti ti-plus me-1"></i>Tambah Produk Pertama
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <table class="table mb-4 text-nowrap align-middle">
                            <thead>
                                <tr>
                                    <th scope="col">Produk</th>
                                    <th scope="col">Kategori</th>
                                    <th scope="col">Harga</th>
                                    <th scope="col">Stok</th>
                                    <th scope="col" class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $product): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="/apotek-alifa/assets/img/product/uploads/<?= htmlspecialchars($product['gambar']) ?>"
                                                    alt="<?= htmlspecialchars($product['nama_obat']) ?>"
                                                    class="rounded me-3"
                                                    style="width: 50px; height: 50px; object-fit: cover;">
                                                <div>
                                                    <h6 class="mb-0"><?= htmlspecialchars($product['nama_obat']) ?></h6>
                                                    <small class="text-muted">
                                                        <?= htmlspecialchars(substr($product['deskripsi'], 0, 50)) ?>...
                                                    </small>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars($product['nama_kategori'] ?: 'Tidak ada kategori') ?></td>
                                        <td>
                                            <strong>Rp<?= number_format($product['harga'], 0, ',', '.') ?></strong>
                                        </td>
                                        <td>
                                            <?php if ($product['stok'] > 20): ?>
                                                <span class="badge bg-success"><?= $product['stok'] ?></span>
                                            <?php elseif ($product['stok'] > 0): ?>
                                                <span class="badge bg-warning"><?= $product['stok'] ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Habis</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-outline-info"
                                                onclick="viewProduct(<?= $product['id'] ?>)">
                                                <i class="ti ti-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-primary edit-btn"
                                                data-product='<?= json_encode($product) ?>' data-bs-target="#editProductModal" data-bs-toggle="modal">
                                                <i class="ti ti-pencil"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger delete-btn" data-bs-target="#deleteProductModal" data-bs-toggle="modal" data-product='<?= json_encode($product) ?>'>
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <!-- PAGINATION -->
                        <?php if ($totalPages > 1): ?>
                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <div>
                                    <small class="text-muted">
                                        Menampilkan <?= count($products) ?> dari <?= $totalItems ?> produk
                                    </small>
                                </div>

                                <nav aria-label="Product pagination">
                                    <ul class="pagination pagination-sm mb-0">
                                        <?php if ($currentPage > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="<?= buildProductPaginationUrl($currentPage - 1, $search, $category) ?>">
                                                    <i class="ti ti-chevron-left"></i>
                                                </a>
                                            </li>
                                        <?php endif; ?>

                                        <?php
                                        $startPage = max(1, $currentPage - 2);
                                        $endPage = min($totalPages, $currentPage + 2);

                                        for ($i = $startPage; $i <= $endPage; $i++):
                                        ?>
                                            <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                                                <a class="page-link" href="<?= buildProductPaginationUrl($i, $search, $category) ?>"><?= $i ?></a>
                                            </li>
                                        <?php endfor; ?>

                                        <?php if ($currentPage < $totalPages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="<?= buildProductPaginationUrl($currentPage + 1, $search, $category) ?>">
                                                    <i class="ti ti-chevron-right"></i>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Produk Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addProductForm" method="POST" enctype="multipart/form-data" action="/apotek-alifa/add_products.php">
                <input type="hidden" name="action" value="add">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Produk <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kategori <span class="text-danger">*</span></label>
                            <select class="form-select" name="category_id" required>
                                <option value="">Pilih Kategori</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Harga <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="price" min="0" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Stok <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="stock" min="0" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Deskripsi</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Gambar Produk</label>
                            <input type="file" class="form-control" name="image" accept="image/*" onchange="previewImage(event, 'addPreview')">
                            <div id="addPreview" class="mt-2"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-device-floppy me-1"></i>Simpan Produk
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Product Modal -->
<div class="modal fade" id="deleteProductModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Yakin ingin menghapus?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Perbuatan ini tidak dapat dibatalkan, perhatikan dengan baik-baik.</p>
            </div>
            <div class="modal-footer">
                <form action="/apotek-alifa/delete_product.php" method="post">
                    <input type="hidden" name="product_id" id="deleteProductId">
                    <button type="button" data-bs-dismiss="modal" class="btn btn-secondary">Kembali</button>
                    <button type="submit" class="btn btn-danger">Ya, Hapus sekarang</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Produk Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addProductForm" method="POST" enctype="multipart/form-data" action="/apotek-alifa/add_products.php">
                <input type="hidden" name="action" value="add">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Produk <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kategori <span class="text-danger">*</span></label>
                            <select class="form-select" name="category_id" required>
                                <option value="">Pilih Kategori</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['nama_kategori']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Harga <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="price" min="0" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Stok <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="stock" min="0" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Deskripsi</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Gambar Produk</label>
                            <input type="file" class="form-control" name="image" accept="image/*" onchange="previewImage(event, 'addPreview')">
                            <div id="addPreview" class="mt-2"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-device-floppy me-1"></i>Simpan Produk
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Produk</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editProductForm" method="POST" enctype="multipart/form-data" action="/apotek-alifa/edit_product.php">
                <input type="hidden" name="product_id" id="productId">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Produk</label>
                            <input type="text" class="form-control" name="name" id="editName" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kategori</label>
                            <select class="form-select" name="category_id" id="editCategory" required>
                                <option>Pilih Kategori</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nama_kategori']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Harga</label>
                            <input type="number" class="form-control" name="price" id="editPrice" min="0" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Stok</label>
                            <input type="number" class="form-control" name="stock" id="editStock" min="0" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Deskripsi</label>
                            <textarea class="form-control" name="description" id="editDescription" rows="3"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Gambar Produk</label>
                            <input type="file" class="form-control" name="image" accept="image/*" onchange="previewImage(event, 'editPreview')">
                            <div id="editCurrentImage" class="mt-2"></div>
                            <div id="editPreview" class="mt-2"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-device-floppy me-1"></i>Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Product Modal -->
<div class="modal fade" id="viewProductModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Produk</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4">
                        <img id="viewImage" src="" alt="" class="img-fluid rounded">
                    </div>
                    <div class="col-md-8">
                        <h4 id="viewName"></h4>
                        <div class="row mb-3">
                            <div class="col-6">
                                <small class="text-muted">Kategori</small>
                                <p class="mb-0" id="viewCategory"></p>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Harga</small>
                                <p class="mb-0 fw-bold text-primary" id="viewPrice"></p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-6">
                                <small class="text-muted">Stok</small>
                                <p class="mb-0" id="viewStock"></p>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Total Terjual</small>
                                <p class="mb-0" id="viewTotalOrders"></p>
                            </div>
                        </div>
                        <div>
                            <small class="text-muted">Deskripsi</small>
                            <p id="viewDescription"></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
    function viewProduct(productId) {
        const allProducts = <?= json_encode($products) ?>;
        const product = allProducts.find(p => p.id == productId);

        if (!product) {
            console.error('Product not found');
            return;
        }

        document.getElementById('viewImage').src = `/apotek-alifa/assets/img/product/uploads/${product.gambar}`;
        document.getElementById('viewImage').alt = product.nama_obat;
        document.getElementById('viewName').textContent = product.nama_obat;
        document.getElementById('viewCategory').textContent = product.nama_kategori || 'Tidak ada kategori';
        document.getElementById('viewPrice').textContent = `Rp${new Intl.NumberFormat('id-ID').format(product.harga)}`;
        document.getElementById('viewStock').textContent = product.stok;
        document.getElementById('viewDescription').textContent = product.deskripsi || 'Tidak ada deskripsi';

        fetch(`/apotek-alifa/get_product_orders.php?product_id=${productId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.text();
            })
            .then(text => {
                try {
                    const data = JSON.parse(text);
                    if (data.success) {
                        document.getElementById('viewTotalOrders').textContent = `${data.total} terjual`;
                    } else {
                        document.getElementById('viewTotalOrders').textContent = '0 terjual';
                    }
                } catch (parseError) {
                    console.error('JSON parse error:', parseError);
                    document.getElementById('viewTotalOrders').textContent = '0 terjual';
                }
            })
            .catch(error => {
                console.error('Error fetching product orders:', error);
                document.getElementById('viewTotalOrders').textContent = '0 terjual';
            });

        const modal = new bootstrap.Modal(document.getElementById('viewProductModal'));
        modal.show();
    }

    function previewImage(event, previewId) {
        const file = event.target.files[0];
        const previewContainer = document.getElementById(previewId);

        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewContainer.innerHTML = `
                <img src="${e.target.result}" alt="Preview" class="img-thumbnail" style="max-height: 150px;">
            `;
            };
            reader.readAsDataURL(file);
        } else {
            previewContainer.innerHTML = '';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {

        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const product = JSON.parse(this.getAttribute('data-product'));

                document.getElementById('productId').value = product.id;
                document.getElementById('editName').value = product.nama_obat;
                document.getElementById('editPrice').value = product.harga;
                document.getElementById('editStock').value = product.stok;
                document.getElementById('editDescription').value = product.deskripsi;
                document.getElementById('editCategory').value = product.id_kategori;

                if (product.gambar) {
                    document.getElementById('editCurrentImage').innerHTML =
                        `<img src="/apotek-alifa/assets/img/product/uploads/${product.gambar}" 
                     alt="${product.nama_obat}" class="img-thumbnail" style="max-height: 100px;">
                     <p class="small text-muted mt-1">Gambar saat ini</p>`;
                }
            });
        });

        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const product = JSON.parse(this.getAttribute('data-product'));
                document.getElementById('deleteProductId').value = product.id;
            });
        });

        const addForm = document.getElementById('addProductForm');
        if (addForm) {
            addForm.addEventListener('submit', function(e) {
                const name = this.querySelector('[name="name"]').value.trim();
                const price = this.querySelector('[name="price"]').value;
                const stock = this.querySelector('[name="stock"]').value;
                const category = this.querySelector('[name="category_id"]').value;

                if (!name || !price || !stock || !category) {
                    e.preventDefault();
                    alert('Mohon lengkapi semua field yang wajib diisi');
                    return false;
                }

                if (parseFloat(price) < 0 || parseFloat(stock) < 0) {
                    e.preventDefault();
                    alert('Harga dan stok tidak boleh negatif');
                    return false;
                }
            });
        }

        const editForm = document.getElementById('editProductForm');
        if (editForm) {
            editForm.addEventListener('submit', function(e) {
                const name = this.querySelector('[name="name"]').value.trim();
                const price = this.querySelector('[name="price"]').value;
                const stock = this.querySelector('[name="stock"]').value;

                if (!name || !price || !stock) {
                    e.preventDefault();
                    alert('Mohon lengkapi semua field yang wajib diisi');
                    return false;
                }

                if (parseFloat(price) < 0 || parseFloat(stock) < 0) {
                    e.preventDefault();
                    alert('Harga dan stok tidak boleh negatif');
                    return false;
                }
            });
        }
    });
</script>

<style>
    .badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.75rem;
    }

    .table td {
        vertical-align: middle;
    }

    .modal-body .row {
        --bs-gutter-y: 1rem;
    }

    .btn-sm {
        padding: 0.25rem 0.5rem;
    }
</style>