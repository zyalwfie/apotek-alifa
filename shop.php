<?php
include 'functions.php';

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
						<div class="card h-100">
							<!-- Product image-->
							<img class="card-img-top"
								src="/apotek-alifa/assets/img/product/uploads/<?= htmlspecialchars($product->image) ?>"
								alt="<?= htmlspecialchars($product->name) ?>"
								style="height: 200px; object-fit: cover;" />
							<!-- Product details-->
							<div class="card-body p-4">
								<div class="text-center">
									<!-- Product name-->
									<h5 class="fw-bolder"><?= htmlspecialchars($product->name) ?></h5>
									<!-- Product price-->
									<p class="text-muted mb-0">Rp<?= number_format($product->price, 0, '.', ',') ?></p>
								</div>
							</div>
							<!-- Product actions-->
							<div class="card-footer p-4 pt-0 border-top-0 bg-transparent">
								<div class="text-center d-flex gap-2 align-items-center justify-content-center">
									<a class="btn btn-dark mt-auto" href="#">
										Lihat Detail
									</a>
									<a class="btn btn-outline-dark mt-auto" href="#">
										<i class="bi bi-cart-plus"></i>
									</a>
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