<?php
require_once '../auth_functions.php';

redirectIfLoggedIn();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $full_name = trim($_POST['full_name']);
  $username = trim($_POST['username']);
  $email = trim($_POST['email']);
  $password = $_POST['password'];
  $confirm_password = $_POST['confirm_password'];

  if (!validateFullName($full_name)) {
    $error = 'Nama lengkap hanya boleh mengandung huruf dan spasi!';
  } elseif (!validateUsername($username)) {
    $error = 'Username hanya boleh mengandung huruf, angka, underscore, dan hyphen!';
  } else {
    $result = register($full_name, $username, $email, $password, $confirm_password);

    if ($result['success']) {
      $success = $result['message'];
    } else {
      $error = $result['message'];
    }
  }
}
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Apotek Alifa | Halaman Daftar</title>
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
                  <img src="/apotek-alifa/assets/img/logo.png" alt="Apotek Alifa Logo" width="200">
                </a>

                <div class="text-center mb-4">
                  <h4 class="fw-semibold">Bergabung dengan Apotek Alifa</h4>
                  <p class="text-muted">Daftar untuk mulai berbelanja obat dan produk kesehatan</p>
                </div>

                <?php if (!empty($error)): ?>
                  <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                  </div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                  <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <?= htmlspecialchars($success) ?>
                    <div class="mt-2">
                      <a href="login.php" class="btn btn-sm btn-primary">
                        <i class="bi bi-box-arrow-in-right me-1"></i>Login Sekarang
                      </a>
                    </div>
                  </div>
                <?php endif; ?>

                <form method="POST" action="" id="registerForm">
                  <div class="mb-3">
                    <label for="full_name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="full_name" name="full_name"
                      value="<?= isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : '' ?>"
                      placeholder="Masukkan nama lengkap Anda" required>
                    <div class="form-text">Gunakan nama asli sesuai identitas</div>
                    <div class="invalid-feedback"></div>
                  </div>

                  <div class="mb-3">
                    <label for="username" class="form-label">Nama Pengguna <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="username" name="username"
                      value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>"
                      placeholder="Pilih username unik" required>
                    <div class="form-text">Minimal 3 karakter, hanya huruf, angka, _ dan -</div>
                    <div class="invalid-feedback"></div>
                  </div>

                  <div class="mb-3">
                    <label for="email" class="form-label">Surel <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="email" name="email"
                      value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"
                      placeholder="contoh@email.com" required>
                    <div class="form-text">Email akan digunakan untuk verifikasi akun</div>
                    <div class="invalid-feedback"></div>
                  </div>

                  <div class="mb-3">
                    <label for="password" class="form-label">Sandi <span class="text-danger">*</span></label>
                    <div class="position-relative">
                      <input type="password" class="form-control" id="password" name="password"
                        placeholder="Buat password yang kuat" required>
                      <button type="button" class="btn btn-sm position-absolute end-0 top-50 translate-middle-y me-2"
                        onclick="togglePassword('password', 'toggleIcon1')">
                        <i class="bi bi-eye" id="toggleIcon1"></i>
                      </button>
                    </div>
                    <div class="form-text">Minimal 6 karakter untuk keamanan</div>
                    <div class="invalid-feedback"></div>
                  </div>

                  <div class="mb-3">
                    <label for="confirm_password" class="form-label">Konfirmasi Sandi <span class="text-danger">*</span></label>
                    <div class="position-relative">
                      <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                        placeholder="Ulangi password yang sama" required>
                      <button type="button" class="btn btn-sm position-absolute end-0 top-50 translate-middle-y me-2"
                        onclick="togglePassword('confirm_password', 'toggleIcon2')">
                        <i class="bi bi-eye" id="toggleIcon2"></i>
                      </button>
                    </div>
                    <div class="invalid-feedback"></div>
                  </div>

                  <!-- Password Strength Indicator -->
                  <div class="mb-3" style="display: none;" id="passwordStrength">
                    <div class="form-text mb-2">Kekuatan Password:</div>
                    <div class="progress" style="height: 5px;">
                      <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                    </div>
                    <small class="text-muted" id="strengthText"></small>
                  </div>

                  <button type="submit" class="btn btn-primary w-100 py-8 fs-4 mb-4 rounded-2" id="submitBtn">
                    <i class="bi bi-person-plus me-2"></i>Daftar Sekarang
                  </button>

                  <div class="d-flex align-items-center justify-content-center">
                    <p class="fs-4 mb-0 fw-bold">Sudah memiliki akun?</p>
                    <a class="text-primary fw-bold ms-2" href="/apotek-alifa/auth/login.php">
                      <i class="bi bi-box-arrow-in-right me-1"></i>Masuk di sini
                    </a>
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
    function togglePassword(inputId, iconId) {
      const passwordInput = document.getElementById(inputId);
      const toggleIcon = document.getElementById(iconId);

      if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.className = 'bi bi-eye-slash';
      } else {
        passwordInput.type = 'password';
        toggleIcon.className = 'bi bi-eye';
      }
    }

    document.getElementById('confirm_password').addEventListener('input', function() {
      const password = document.getElementById('password').value;
      const confirmPassword = this.value;

      if (confirmPassword && password !== confirmPassword) {
        this.setCustomValidity('Password tidak cocok');
        this.classList.add('is-invalid');
      } else {
        this.setCustomValidity('');
        this.classList.remove('is-invalid');
        if (confirmPassword) this.classList.add('is-valid');
      }
    });

    document.getElementById('username').addEventListener('input', function() {
      const username = this.value;
      const regex = /^[a-zA-Z0-9_-]+$/;

      if (username.length < 3) {
        this.setCustomValidity('Username minimal 3 karakter');
        this.classList.add('is-invalid');
      } else if (!regex.test(username)) {
        this.setCustomValidity('Username hanya boleh mengandung huruf, angka, _ dan -');
        this.classList.add('is-invalid');
      } else {
        this.setCustomValidity('');
        this.classList.remove('is-invalid');
        this.classList.add('is-valid');
      }
    });

    document.getElementById('full_name').addEventListener('input', function() {
      const fullName = this.value;
      const regex = /^[a-zA-Z\s]+$/;

      if (fullName.length < 2) {
        this.setCustomValidity('Nama minimal 2 karakter');
        this.classList.add('is-invalid');
      } else if (!regex.test(fullName)) {
        this.setCustomValidity('Nama hanya boleh mengandung huruf dan spasi');
        this.classList.add('is-invalid');
      } else {
        this.setCustomValidity('');
        this.classList.remove('is-invalid');
        this.classList.add('is-valid');
      }
    });

    document.getElementById('password').addEventListener('input', function() {
      const password = this.value;
      const strengthDiv = document.getElementById('passwordStrength');
      const progressBar = strengthDiv.querySelector('.progress-bar');
      const strengthText = document.getElementById('strengthText');

      if (password.length > 0) {
        strengthDiv.style.display = 'block';

        let strength = 0;
        let text = '';
        let color = '';

        if (password.length >= 6) strength += 25;
        if (/[a-z]/.test(password)) strength += 25;
        if (/[A-Z]/.test(password)) strength += 25;
        if (/[0-9]/.test(password)) strength += 25;

        if (strength <= 25) {
          text = 'Lemah';
          color = 'bg-danger';
        } else if (strength <= 50) {
          text = 'Sedang';
          color = 'bg-warning';
        } else if (strength <= 75) {
          text = 'Kuat';
          color = 'bg-info';
        } else {
          text = 'Sangat Kuat';
          color = 'bg-success';
        }

        progressBar.style.width = strength + '%';
        progressBar.className = 'progress-bar ' + color;
        strengthText.textContent = text;
      } else {
        strengthDiv.style.display = 'none';
      }
    });

    document.getElementById('registerForm').addEventListener('submit', function(e) {
      const password = document.getElementById('password').value;
      const confirmPassword = document.getElementById('confirm_password').value;
      const terms = document.getElementById('terms').checked;

      if (password !== confirmPassword) {
        e.preventDefault();
        alert('Password dan konfirmasi password tidak cocok!');
        return false;
      }

      if (!terms) {
        e.preventDefault();
        alert('Anda harus menyetujui syarat dan ketentuan!');
        return false;
      }

      const submitBtn = document.getElementById('submitBtn');
      submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Mendaftar...';
      submitBtn.disabled = true;
    });
  </script>
</body>

</html>