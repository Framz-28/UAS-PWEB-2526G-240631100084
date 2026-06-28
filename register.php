<?php
// register.php — Halaman pendaftaran user baru (selalu sebagai member)
require 'koneksi.php';
require 'functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['login']) && $_SESSION['login'] === true) {
    header("Location: index.php");
    exit;
}

// Pastikan admin (id=1) sudah ada lebih dulu
pastikan_admin($koneksi);

$error = '';
$old = ['nama' => '', 'username' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['nama'] = trim($_POST['nama'] ?? '');
    $old['username'] = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $konfirmasi = $_POST['konfirmasi'] ?? '';

    if ($old['nama'] === '' || $old['username'] === '' || $password === '') {
        $error = 'Semua field wajib diisi.';
    } elseif (strlen($password) < 5) {
        $error = 'Password minimal 5 karakter.';
    } elseif ($password !== $konfirmasi) {
        $error = 'Konfirmasi password tidak cocok.';
    } else {
        // Cek username sudah dipakai atau belum
        $cek = mysqli_prepare($koneksi, "SELECT id FROM users WHERE username = ?");
        mysqli_stmt_bind_param($cek, "s", $old['username']);
        mysqli_stmt_execute($cek);
        mysqli_stmt_store_result($cek);
        if (mysqli_stmt_num_rows($cek) > 0) {
            $error = 'Username sudah digunakan, pilih yang lain.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $role = 'member';
            $stmt = mysqli_prepare($koneksi, "INSERT INTO users (username, password, nama, role) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "ssss", $old['username'], $hash, $old['nama'], $role);
            if (mysqli_stmt_execute($stmt)) {
                // Auto login setelah daftar
                $_SESSION['login'] = true;
                $_SESSION['user_id'] = mysqli_insert_id($koneksi);
                $_SESSION['username'] = $old['username'];
                $_SESSION['nama'] = $old['nama'];
                $_SESSION['role'] = 'member';
                header("Location: index.php");
                exit;
            } else {
                $error = 'Gagal mendaftar: ' . mysqli_error($koneksi);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar &mdash; SiKeu</title>
    <script>(function(){try{if(localStorage.getItem('theme')==='dark'){document.documentElement.setAttribute('data-theme','dark');}}catch(e){}})();</script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="login-page">
    <button class="theme-toggle theme-toggle-float" onclick="(function(h){if(h.getAttribute('data-theme')==='dark'){h.removeAttribute('data-theme');localStorage.setItem('theme','light');}else{h.setAttribute('data-theme','dark');localStorage.setItem('theme','dark');}})(document.documentElement)" title="Ganti tema"><span class="sun">&#9728;</span><span class="moon">&#9789;</span></button>

    <div class="login-card anim-pop">
        <div class="login-brand">
            <img src="img/logo-kampus.png" alt="Logo" class="login-logo" onerror="this.onerror=null;this.src='assets/logo.svg'">
            <h1>Buat Akun</h1>
            <p>Daftar untuk mulai mencatat keuangan</p>
        </div>

        <?php if ($error !== ''): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" class="form">
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="nama" value="<?= bersih($old['nama']) ?>" placeholder="cth: Budi Santoso" required>
            </div>
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" value="<?= bersih($old['username']) ?>" placeholder="cth: budi" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="min. 5 karakter" required>
                </div>
                <div class="form-group">
                    <label>Konfirmasi</label>
                    <input type="password" name="konfirmasi" placeholder="ulangi password" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Daftar</button>
        </form>

        <p class="login-switch">Sudah punya akun? <a href="login.php">Masuk di sini</a></p>
    </div>
</body>
</html>
