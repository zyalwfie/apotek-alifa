<?php
require_once '../auth_functions.php';

redirectIfLoggedIn();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $username = trim($_POST['username']);
  $password = $_POST['password'];

  if (!empty($username) && !empty($password)) {
    $result = login($username, $password);

    if ($result['success']) {
      $success = $result['message'];
      header("refresh:2;url=/apotek-alifa/layouts/landing/");
    } else {
      $error = $result['message'];
    }
  } else {
    $error = 'Username dan password harus diisi!';
  }
}
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Apotek Alifa | Halaman Masuk</title>
  <link rel="shortcut icon" type="image/png" href="/apotek-alifa/assets/img/favicon.ico" />
  <link rel="stylesheet" href="/apotek-alifa/assets/css/styles.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
</head>

<body>
  <!--  Body Wrapper -->
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    <div
      class="position-relative overflow-hidden text-bg-light min-vh-100 d-flex align-items-center justify-content-center">
      <div class="d-flex align-items-center justify-content-center w-100">
        <div class="row justify-content-center w-100">
          <div class="col-md-8 col-xxl-5">
            <div class="card mb-0">
              <div class="card-body">
                <a href="/apotek-alifa/layouts/landing/" class="text-nowrap logo-img text-center d-block py-3 w-100">
                  <img src="/apotek-alifa/assets/img/logo.png" alt="Logo" width="200">
                </a>

                <?php if (!empty($error)): ?>
                  <div class="alert alert-danger d-flex align-items-center" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <div>
                      <?= htmlspecialchars($error) ?>
                    </div>
                  </div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                  <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <?= htmlspecialchars($success) ?>
                    <div class="mt-2">
                      <small>Mengalihkan ke halaman utama...</small>
                    </div>
                  </div>
                <?php endif; ?>

                <form method="POST" action="">
                  <div class="mb-3">
                    <label for="username" class="form-label">Username atau Email</label>
                    <input type="text" class="form-control" id="username" name="username"
                      value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>"
                      required>
                  </div>
                  <div class="mb-4">
                    <label for="password" class="form-label">Sandi</label>
                    <div class="position-relative">
                      <input type="password" class="form-control" id="password" name="password" required>
                      <button type="button" class="btn btn-sm position-absolute end-0 top-50 translate-middle-y me-2"
                        onclick="togglePassword()">
                        <i class="bi bi-eye" id="toggleIcon"></i>
                      </button>
                    </div>
                  </div>
                  <div class="d-flex align-items-center justify-content-between mb-4">
                    <div class="form-check">
                      <input class="form-check-input primary" type="checkbox" value="" id="remember" name="remember">
                      <label class="form-check-label text-dark" for="remember">
                        Ingat Saya
                      </label>
                    </div>
                    <a class="text-primary fw-bold" href="#" onclick="alert('Fitur lupa password belum tersedia')">Lupa Password?</a>
                  </div>
                  <button type="submit" class="btn btn-primary w-100 py-8 fs-4 mb-4 rounded-2">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Masuk
                  </button>
                  <div class="d-flex align-items-center justify-content-center">
                    <p class="fs-4 mb-0 fw-bold">Belum punya akun?</p>
                    <a class="text-primary fw-bold ms-2" href="register.php">Daftar Sekarang</a>
                  </div>
                </form>

              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="/apotek-alifa/assets/libs/jquery/dist/jquery.min.js"></script>
  <script src="/apotek-alifa/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>

  <script>
    function togglePassword() {
      const passwordInput = document.getElementById('password');
      const toggleIcon = document.getElementById('toggleIcon');

      if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.className = 'bi bi-eye-slash';
      } else {
        passwordInput.type = 'password';
        toggleIcon.className = 'bi bi-eye';
      }
    }
  </script>
</body>

</html>