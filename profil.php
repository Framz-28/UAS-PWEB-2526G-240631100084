<?php
// profil.php — Halaman profil: ubah nama & ganti password
require 'auth.php';
require 'koneksi.php';
require 'functions.php';

$error = '';
$toast = null;

// Ambil data user saat ini
$stmt = mysqli_prepare($koneksi, "SELECT * FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($res);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $password_lama = $_POST['password_lama'] ?? '';
    $password_baru = $_POST['password_baru'] ?? '';

    if ($nama === '') {
        $error = 'Nama tidak boleh kosong.';
    } else {
        // Update nama
        $up = mysqli_prepare($koneksi, "UPDATE users SET nama = ? WHERE id = ?");
        mysqli_stmt_bind_param($up, "si", $nama, $user_id);
        mysqli_stmt_execute($up);
        $_SESSION['nama'] = $nama;

        // Jika user ingin ganti password
        if ($password_baru !== '') {
            if (!password_verify($password_lama, $user['password'])) {
                $error = 'Password lama salah, perubahan password dibatalkan (nama tetap tersimpan).';
            } elseif (strlen($password_baru) < 5) {
                $error = 'Password baru minimal 5 karakter (nama tetap tersimpan).';
            } else {
                $hash = password_hash($password_baru, PASSWORD_DEFAULT);
                $upp = mysqli_prepare($koneksi, "UPDATE users SET password = ? WHERE id = ?");
                mysqli_stmt_bind_param($upp, "si", $hash, $user_id);
                mysqli_stmt_execute($upp);
            }
        }

        if ($error === '') {
            $toast = ['msg' => 'Profil berhasil diperbarui.', 'type' => 'success'];
        }

        // Refresh data
        $user['nama'] = $nama;
    }
}

$page_title = 'Profil Saya';
$active = 'profil';
require 'partials/header.php';
?>

<section class="panel panel-form">
    <div class="panel-head"><h2>Informasi Akun</h2></div>

    <?php if ($error !== ''): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="profile-head">
        <div class="profile-ava"><?= inisial($user['nama'] ?: $user['username']) ?></div>
        <div>
            <strong><?= bersih($user['nama']) ?></strong>
            <span>@<?= bersih($user['username']) ?></span>
        </div>
    </div>

    <form method="post" class="form">
        <div class="form-group">
            <label>Nama Lengkap</label>
            <input type="text" name="nama" value="<?= bersih($user['nama']) ?>" required>
        </div>
        <div class="form-group">
            <label>Username</label>
            <input type="text" value="<?= bersih($user['username']) ?>" disabled>
            <small class="hint">Username tidak dapat diubah.</small>
        </div>

        <hr class="form-divider">
        <p class="form-section-title">Ganti Password (opsional)</p>

        <div class="form-row">
            <div class="form-group">
                <label>Password Lama</label>
                <input type="password" name="password_lama" placeholder="kosongkan jika tidak ganti">
            </div>
            <div class="form-group">
                <label>Password Baru</label>
                <input type="password" name="password_baru" placeholder="min. 5 karakter">
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </div>
    </form>
</section>

<?php require 'partials/footer.php'; ?>
