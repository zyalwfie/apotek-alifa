<?php
include 'functions.php';
require_once 'auth_functions.php';

$user = getUserData();

$searchQuery = isset($_GET['query']) ? trim($_GET['query']) : '';
$currentPage = isset($_GET['p']) ? max(1, intval($_GET['p'])) : 1;
$itemsPerPage = 8;

$result = getProductsWithPagination($searchQuery, $currentPage, $itemsPerPage);
$products = $result['products'];
$totalPages = $result['total_pages'];
$totalItems = $result['total'];
?>

<section class="py-5">
	<div class="container d-flex justify-content-between align-items-center">
		<div>
			<h3>Daftar Semua Obat</h3>
			<a href="/apotek-alifa/layouts/landing/" class="btn btn-primary">Kembali</a>
			<?php if (!empty($searchQuery)): ?>
				<div class="mt-2">
					<small class="text-muted">
						Menampilkan <?= count($products) ?> dari <?= $totalItems ?> hasil untuk "<?= htmlspecialchars($searchQuery) ?>"
						<a href="?page=shop" class="text-decoration-none ms-2">Hapus Filter</a>
					</small>
				</div>
			<?php endif; ?>
		</div>
		<form method="get" class="d-flex">
			<div class="position-relative">
				<input type="hidden" name="page" value="shop">
				<input type="text" class="form-control py-2 pe-5" name="query"
					placeholder="Cari obat di sini" value="<?= htmlspecialchars($searchQuery) ?>">
				<button class="position-absolute top-50 end-0 translate-middle-y btn z-1 bg-white border-0" type="submit">
					<i class="bi bi-search"></i>
				</button>
			</div>
		</form>
	</div>

	<div class="container mt-5">
		<?php if ($products && count($products) > 0): ?>
			<div class="row gx-4 gx-lg-5 row-cols-2 row-cols-md-3 row-cols-xl-4 justify-content-center">
				<?php foreach ($products as $product): ?>
					<div class="col mb-5">
						<div class="card h-100 product-card">
							<!-- Product image-->
							<div class="position-relative">
								<img class="card-img-top"
									src="/apotek-alifa/assets/img/product/uploads/<?= htmlspecialchars($product->image) ?>"
									alt="<?= htmlspecialchars($product->name) ?>"
									style="height: 200px; object-fit: cover; cursor: pointer;"
									onclick="window.location.href='?page=show&id=<?= $product->id ?>'" />

								<!-- Stock badge -->
								<?php if (isset($product->stock)): ?>
									<?php if ($product->stock > 0): ?>
										<div class="badge bg-success position-absolute" style="top: 0.5rem; right: 0.5rem">
											<i class="bi bi-check-circle me-1"></i>Tersedia
										</div>
									<?php else: ?>
										<div class="badge bg-danger position-absolute" style="top: 0.5rem; right: 0.5rem">
											<i class="bi bi-x-circle me-1"></i>Habis
										</div>
									<?php endif; ?>
								<?php endif; ?>
							</div>

							<!-- Product details-->
							<div class="card-body p-4">
								<div class="text-center">
									<!-- Product name-->
									<h5 class="fw-bolder mb-2">
										<a href="?page=show&id=<?= $product->id ?>" class="text-decoration-none text-dark">
											<?= htmlspecialchars($product->name) ?>
										</a>
									</h5>

									<!-- Product price-->
									<p class="text-primary fw-bold mb-2">Rp<?= number_format($product->price, 0, '.', ',') ?></p>

									<!-- Product description -->
									<?php if (!empty($product->description)): ?>
										<p class="text-muted small mb-2"><?= htmlspecialchars(substr($product->description, 0, 50)) ?>...</p>
									<?php endif; ?>

									<!-- Product rating -->
									<div class="d-flex justify-content-center align-items-center mb-2">
										<div class="d-flex text-warning me-2" style="font-size: 0.8rem;">
											<i class="bi bi-star-fill"></i>
											<i class="bi bi-star-fill"></i>
											<i class="bi bi-star-fill"></i>
											<i class="bi bi-star-fill"></i>
											<i class="bi bi-star"></i>
										</div>
										<small class="text-muted">(4.2)</small>
									</div>
								</div>
							</div>

							<!-- Product actions-->
							<div class="card-footer p-4 pt-0 border-top-0 bg-transparent">
								<div class="text-center d-flex gap-2 align-items-center justify-content-center">
									<a class="btn btn-outline-primary btn-sm flex-fill" href="?page=show&id=<?= $product->id ?>">
										<i class="bi bi-eye me-1"></i>Detail
									</a>

									<?php if (isLoggedIn()): ?>
										<?php if ($user['role'] === 'user') : ?>
											<?php if (!isset($product->stock) || $product->stock > 0): ?>
												<button class="btn btn-primary btn-sm flex-fill add-to-cart-btn"
													data-product-id="<?= $product->id ?>"
													data-product-name="<?= htmlspecialchars($product->name) ?>">
													<i class="bi bi-cart-plus me-1"></i>Tambah
												</button>
											<?php else: ?>
												<button class="btn btn-secondary btn-sm flex-fill" disabled>
													<i class="bi bi-x-circle me-1"></i>Habis
												</button>
											<?php endif; ?>
										<?php endif; ?>
									<?php else: ?>
										<button class="btn btn-outline-secondary btn-sm flex-fill" onclick="loginRequired()">
											<i class="bi bi-box-arrow-in-right me-1"></i>Login
										</button>
									<?php endif; ?>
								</div>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>

			<!-- Pagination -->
			<?php if ($totalPages > 1): ?>
				<nav aria-label="Product pagination" class="mt-5">
					<ul class="pagination justify-content-center">
						<!-- Previous Page -->
						<?php if ($currentPage > 1): ?>
							<li class="page-item">
								<a class="page-link" href="<?= buildPaginationUrl($currentPage - 1, $searchQuery) ?>">
									<i class="bi bi-chevron-left"></i>
								</a>
							</li>
						<?php else: ?>
							<li class="page-item disabled">
								<span class="page-link">
									<i class="bi bi-chevron-left"></i>
								</span>
							</li>
						<?php endif; ?>

						<!-- Page Numbers -->
						<?php
						$startPage = max(1, $currentPage - 2);
						$endPage = min($totalPages, $currentPage + 2);

						// Show first page if we're not close to it
						if ($startPage > 1):
						?>
							<li class="page-item">
								<a class="page-link" href="<?= buildPaginationUrl(1, $searchQuery) ?>">1</a>
							</li>
							<?php if ($startPage > 2): ?>
								<li class="page-item disabled">
									<span class="page-link">...</span>
								</li>
							<?php endif; ?>
						<?php endif; ?>

						<!-- Current range of pages -->
						<?php for ($i = $startPage; $i <= $endPage; $i++): ?>
							<li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
								<a class="page-link" href="<?= buildPaginationUrl($i, $searchQuery) ?>"><?= $i ?></a>
							</li>
						<?php endfor; ?>

						<!-- Show last page if we're not close to it -->
						<?php if ($endPage < $totalPages): ?>
							<?php if ($endPage < $totalPages - 1): ?>
								<li class="page-item disabled">
									<span class="page-link">...</span>
								</li>
							<?php endif; ?>
							<li class="page-item">
								<a class="page-link" href="<?= buildPaginationUrl($totalPages, $searchQuery) ?>"><?= $totalPages ?></a>
							</li>
						<?php endif; ?>

						<!-- Next Page -->
						<?php if ($currentPage < $totalPages): ?>
							<li class="page-item">
								<a class="page-link" href="<?= buildPaginationUrl($currentPage + 1, $searchQuery) ?>">
									<i class="bi bi-chevron-right"></i>
								</a>
							</li>
						<?php else: ?>
							<li class="page-item disabled">
								<span class="page-link">
									<i class="bi bi-chevron-right"></i>
								</span>
							</li>
						<?php endif; ?>
					</ul>
				</nav>

				<!-- Pagination Info -->
				<div class="text-center mt-3">
					<small class="text-muted">
						Halaman <?= $currentPage ?> dari <?= $totalPages ?>
						(<?= $totalItems ?> total obat)
					</small>
				</div>
			<?php endif; ?>

		<?php else: ?>
			<!-- No products found -->
			<div class="text-center py-5">
				<div class="mb-4">
					<i class="bi bi-search" style="font-size: 4rem; color: #6c757d;"></i>
				</div>
				<h4 class="text-muted">Tidak ada obat ditemukan</h4>
				<?php if (!empty($searchQuery)): ?>
					<p class="text-muted">Tidak ada hasil untuk pencarian "<?= htmlspecialchars($searchQuery) ?>"</p>
					<a href="?page=shop" class="btn btn-outline-primary">Lihat Semua Obat</a>
				<?php else: ?>
					<p class="text-muted">Belum ada obat yang tersedia</p>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</section>

<!-- Toast Container for Notifications -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1100;">
	<div id="addToCartToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
		<div class="toast-header">
			<i class="bi bi-cart-check text-success me-2"></i>
			<strong class="me-auto">Keranjang</strong>
			<button type="button" class="btn-close" data-bs-dismiss="toast"></button>
		</div>
		<div class="toast-body">
			Produk berhasil ditambahkan ke keranjang!
		</div>
	</div>
</div>

<style>
	.product-card {
		transition: transform 0.3s ease, box-shadow 0.3s ease;
		border: none;
		box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
	}

	.product-card:hover {
		transform: translateY(-8px);
		box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
	}

	.product-card .card-img-top {
		transition: transform 0.3s ease;
	}

	.product-card:hover .card-img-top {
		transform: scale(1.05);
	}

	.add-to-cart-btn {
		position: relative;
		overflow: hidden;
	}

	.add-to-cart-btn.loading {
		pointer-events: none;
	}

	.add-to-cart-btn.loading::after {
		content: '';
		position: absolute;
		top: 50%;
		left: 50%;
		width: 16px;
		height: 16px;
		margin: -8px 0 0 -8px;
		border: 2px solid #ffffff;
		border-radius: 50%;
		border-top-color: transparent;
		animation: spin 1s linear infinite;
	}

	@keyframes spin {
		to {
			transform: rotate(360deg);
		}
	}

	.toast {
		min-width: 300px;
	}

	.card-footer .btn {
		font-size: 0.875rem;
		padding: 0.5rem 0.75rem;
	}

	.badge {
		font-size: 0.7rem;
	}

	.product-card h5 a:hover {
		color: #0d6efd !important;
		transition: color 0.3s ease;
	}
</style>

<script>
	function addToCart(productId, productName) {
		const btn = document.querySelector(`[data-product-id="${productId}"]`);

		// Show loading state
		btn.classList.add('loading');
		btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Loading...';
		btn.disabled = true;

		fetch('/apotek-alifa/add_to_cart.php', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
				},
				body: `product_id=${productId}&quantity=1`
			})
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
						if (data.cart_count !== undefined) {
							updateCartCount(data.cart_count);
						}

						showToast(data.message || 'Produk berhasil ditambahkan ke keranjang!', 'success');

						btn.innerHTML = '<i class="bi bi-cart-check me-1"></i>Ditambah!';
						btn.classList.remove('btn-primary');
						btn.classList.add('btn-success');

						setTimeout(() => {
							btn.innerHTML = '<i class="bi bi-cart-plus me-1"></i>Tambah';
							btn.classList.remove('btn-success');
							btn.classList.add('btn-primary');
						}, 2000);
					} else {
						if (data.redirect) {
							window.location.href = data.redirect;
							return;
						}
						showToast(data.message || 'Terjadi kesalahan', 'error');
						btn.innerHTML = '<i class="bi bi-cart-plus me-1"></i>Tambah';
					}
				} catch (parseError) {
					console.error('JSON parse error:', parseError);
					console.error('Response text:', text);
					showToast('Server response error', 'error');
					btn.innerHTML = '<i class="bi bi-cart-plus me-1"></i>Tambah';
				}
			})
			.catch(error => {
				console.error('Fetch error:', error);
				showToast('Network error: ' + error.message, 'error');
				btn.innerHTML = '<i class="bi bi-cart-plus me-1"></i>Tambah';
			})
			.finally(() => {
				btn.classList.remove('loading');
				btn.disabled = false;
			});
	}

	function updateCartCount(count) {
		const cartBadges = document.querySelectorAll('.badge');
		cartBadges.forEach(badge => {
			if (badge.closest('[href*="cart"]')) {
				badge.textContent = count > 99 ? '99+' : count;
				badge.style.display = count > 0 ? 'inline-block' : 'none';
			}
		});
	}

	function showToast(message, type = 'success') {
		const toastEl = document.getElementById('addToCartToast');
		const toastBody = toastEl.querySelector('.toast-body');
		const toastIcon = toastEl.querySelector('.toast-header i');

		toastBody.textContent = message;

		if (type === 'success') {
			toastIcon.className = 'bi bi-cart-check text-success me-2';
		} else {
			toastIcon.className = 'bi bi-exclamation-triangle text-danger me-2';
		}

		const toast = new bootstrap.Toast(toastEl);
		toast.show();
	}

	function loginRequired() {
		if (confirm('Anda harus login untuk menambahkan produk ke keranjang. Login sekarang?')) {
			window.location.href = '/apotek-alifa/auth/login.php';
		}
	}

	document.addEventListener('click', function(e) {
		if (e.target.closest('.add-to-cart-btn')) {
			const btn = e.target.closest('.add-to-cart-btn');
			const productId = btn.getAttribute('data-product-id');
			const productName = btn.getAttribute('data-product-name');

			addToCart(productId, productName);
		}
	});
</script>