<?php
// login.php — Halaman login (autentikasi + pilih role)
require 'koneksi.php';
require 'functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['login']) && $_SESSION['login'] === true) {
    header("Location: index.php");
    exit;
}

// Pastikan akun admin default tersedia (auto-seed, aman dengan hash)
pastikan_admin($koneksi);

$error = '';
$role_pilih = 'member';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role_pilih = $_POST['role'] ?? 'member';

    if ($username === '' || $password === '') {
        $error = 'Username dan password wajib diisi.';
    } else {
        $stmt = mysqli_prepare($koneksi, "SELECT * FROM users WHERE username = ?");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($res);

        if ($user && password_verify($password, $user['password'])) {
            // Percabangan: cek kesesuaian role yang dipilih
            if ($user['role'] !== $role_pilih) {
                $label = $role_pilih === 'admin' ? 'Administrator' : 'Member';
                $error = 'Akun ini tidak terdaftar sebagai ' . $label . '. Silakan pilih role yang benar.';
            } else {
                $_SESSION['login'] = true;
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['nama'] = $user['nama'];
                $_SESSION['role'] = $user['role'];
                header("Location: index.php");
                exit;
            }
        } else {
            $error = 'Username atau password salah.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login &mdash; SiKeu</title>
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
            <h1>SiKeu</h1>
            <p>Sistem Catatan Keuangan Mahasiswa</p>
        </div>

        <?php if ($error !== ''): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" class="form">
            <div class="role-switch">
                <label class="role-opt">
                    <input type="radio" name="role" value="member" <?= $role_pilih !== 'admin' ? 'checked' : '' ?>>
                    <span>&#128100; Member</span>
                </label>
                <label class="role-opt">
                    <input type="radio" name="role" value="admin" <?= $role_pilih === 'admin' ? 'checked' : '' ?>>
                    <span>&#128737; Administrator</span>
                </label>
            </div>

            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" placeholder="masukkan username" autofocus required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="masukkan password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Masuk</button>
        </form>

        <p class="login-switch">Belum punya akun? <a href="register.php">Daftar sebagai Member</a></p>

        <div class="login-hint">
            <strong>Akun admin default:</strong> <code>admin</code> / <code>admin123</code><br>
            Pilih role <strong>Administrator</strong> untuk masuk sebagai admin.
        </div>
    </div>
</body>
</html>
