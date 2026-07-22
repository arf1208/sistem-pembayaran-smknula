<?php
session_start(); // Memulai session agar token CSRF dapat divalidasi dengan benar
require_once 'config/database.php';

if (isset($_SESSION['id_petugas'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        die("CSRF Token validation failed.");
    }

    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS);
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM petugas WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && (password_verify($password, $user['password']) || $password === $user['password'])) {
        $_SESSION['id_petugas'] = $user['id_petugas'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
        $_SESSION['role'] = $user['role'];
        
        header("Location: dashboard.php");
        exit();
    } else {
        $error = 'Username atau Password salah!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SMK NU Lamongan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="d-flex flex-column justify-content-between" style="min-height: 100vh; background: linear-gradient(135deg, #113527 0%, #071912 100%);">

<!-- Spacer Atas agar posisi kartu tetap presisi di tengah layar -->
<div class="py-3"></div>

<div class="container my-auto">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card card-custom p-4 shadow-lg text-center" style="background: rgba(255, 255, 255, 0.98); border-radius: 20px; border: none;">
                <!-- Logo SMK NU dengan background putih bulat -->
                <img src="assets/images/logo-smknu.png" alt="Logo" class="mb-3 mx-auto d-block" style="max-height: 100px; width: auto; background: white; border-radius: 50%; padding: 5px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);" onerror="this.src='https://www.image2url.com/r2/default/files/1784209762264-dc5b6327-3897-4e36-b9f1-200ff68df13f.png'">
                <h4 class="fw-bold text-success m-0">SISTEM KASIR & ADM</h4>
                <p class="text-muted small">SMK NU Lamongan</p>
                <hr>

                <?php if($error): ?>
                    <div class="alert alert-danger text-start py-2 small"><i class="fa-solid fa-triangle-exclamation me-2"></i><?=$error?></div>
                <?php endif; ?>

                <form action="" method="POST" id="loginForm">
                    <input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>">
                    
                    <div class="mb-3 text-start">
                        <label class="form-label small fw-bold">Username</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-user text-success"></i></span>
                            <input type="text" name="username" class="form-control border-start-0 ps-0" required placeholder="Masukkan username">
                        </div>
                    </div>

                    <div class="mb-3 text-start">
                        <label class="form-label small fw-bold">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-lock text-success"></i></span>
                            <input type="password" name="password" id="inputPassword" class="form-control border-start-0 ps-0 border-end-0" required placeholder="••••••••">
                            <button class="btn border border-start-0" style="background: white;" type="button" id="togglePassword"><i class="fa-solid fa-eye text-muted"></i></button>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mb-4 small">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="rememberMe">
                            <label class="form-check-label text-muted" for="rememberMe">Ingat Saya</label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success w-100 py-2 fw-bold" id="btnLogin" style="border-radius: 10px;">
                        <span id="loginText">MASUK KE SYSTEM <i class="fa-solid fa-arrow-right ms-2"></i></span>
                        <span id="loginSpinner" class="spinner-border spinner-border-sm d-none" role="status"></span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Footer Elegan Khusus Halaman Login (Semi-Transparent White) -->
<footer class="py-4 text-center text-white-50 small mt-auto">
    <div class="container">
        <p class="m-0 mb-1">&copy; <?= date('Y'); ?> <span class="fw-bold text-white">SMK NU Lamongan</span>. Dikembangkan untuk Kemudahan Layanan Administrasi.</p>
        <p class="m-0 d-flex align-items-center justify-content-center gap-2 flex-wrap">
            <span>Made with <i class="fa-solid fa-heart text-danger"></i> by <strong class="text-white">Siswa SMKNULA</strong></span>
            <span>&bull;</span>
            <span class="badge bg-white bg-opacity-25 text-white fw-semibold px-2 py-1" style="font-size: 10px; border: 1px solid rgba(255,255,255,0.15);">
                <i class="fa-solid fa-code-branch me-1"></i>v1.0.0
            </span>
        </p>
    </div>
</footer>

<script>
    const togglePassword = document.querySelector('#togglePassword');
    const password = document.querySelector('#inputPassword');
    togglePassword.addEventListener('click', function () {
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        
        // Ubah ikon mata
        const icon = this.querySelector('i');
        icon.classList.toggle('fa-eye');
        icon.classList.toggle('fa-eye-slash');
    });

    document.getElementById('loginForm').addEventListener('submit', function() {
        document.getElementById('loginText').classList.add('d-none');
        document.getElementById('loginSpinner').classList.remove('d-none');
        document.getElementById('btnLogin').setAttribute('disabled', 'true');
    });
</script>
</body>
</html>