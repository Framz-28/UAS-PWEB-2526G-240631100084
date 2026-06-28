<?php
require 'auth.php';
require 'koneksi.php';
require 'functions.php';

// Hanya administrator yang boleh mengakses halaman ini
if (($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: index.php");
    exit;
}

$toast = null;

// Proses aksi admin (POST): reset password / hapus member
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';
    $target = (int) ($_POST['target_id'] ?? 0);

    if ($aksi === 'reset' && $target > 0) {
        $pw = $_POST['password_baru'] ?? '';
        if (strlen($pw) >= 5) {
            $hash = password_hash($pw, PASSWORD_DEFAULT);
            $st = mysqli_prepare($koneksi, "UPDATE users SET password = ? WHERE id = ?");
            mysqli_stmt_bind_param($st, "si", $hash, $target);
            mysqli_stmt_execute($st);
            $toast = ['msg' => 'Password member berhasil direset.', 'type' => 'success'];
        } else {
            $toast = ['msg' => 'Password minimal 5 karakter.', 'type' => 'error'];
        }
    } elseif ($aksi === 'hapus' && $target > 1) {
        // Hapus data milik member lalu akunnya (admin id=1 dilindungi)
        mysqli_query($koneksi, "DELETE FROM transaksi WHERE user_id = " . $target);
        mysqli_query($koneksi, "DELETE FROM budget WHERE user_id = " . $target);
        $st = mysqli_prepare($koneksi, "DELETE FROM users WHERE id = ? AND id <> 1");
        mysqli_stmt_bind_param($st, "i", $target);
        mysqli_stmt_execute($st);
        $toast = ['msg' => 'Member beserta seluruh datanya berhasil dihapus.', 'type' => 'error'];
    }
}

// Statistik ringkas
$stat = ['total' => 0, 'admin' => 0, 'member' => 0, 'trx' => 0];
$rs = mysqli_query($koneksi, "SELECT role, COUNT(*) AS n FROM users GROUP BY role");
while ($r = mysqli_fetch_assoc($rs)) {
    $stat['total'] += (int) $r['n'];
    $stat[$r['role']] = (int) $r['n'];
}
$rt = mysqli_query($koneksi, "SELECT COUNT(*) AS n FROM transaksi");
$stat['trx'] = (int) mysqli_fetch_assoc($rt)['n'];

// Daftar semua user + jumlah transaksi & saldo masing-masing
$users = mysqli_query($koneksi, "
    SELECT u.*,
        (SELECT COUNT(*) FROM transaksi t WHERE t.user_id = u.id) AS jml_trx,
        (SELECT COALESCE(SUM(CASE WHEN jenis='pemasukan' THEN jumlah ELSE -jumlah END), 0)
         FROM transaksi t WHERE t.user_id = u.id) AS saldo
    FROM users u
    ORDER BY u.role = 'admin' DESC, u.id ASC
");

$page_title = 'Kelola Member';
$active = 'admin';
require 'partials/header.php';
?>

<section class="stats">
    <div class="stat-card stat-balance">
        <div class="stat-ic">&#128101;</div>
        <div class="stat-body"><span class="stat-label">Total Pengguna</span><strong class="stat-value"><?= $stat['total'] ?> akun</strong></div>
    </div>
    <div class="stat-card stat-in">
        <div class="stat-ic">&#128100;</div>
        <div class="stat-body"><span class="stat-label">Member</span><strong class="stat-value"><?= $stat['member'] ?> orang</strong></div>
    </div>
    <div class="stat-card stat-count">
        <div class="stat-ic">&#128737;</div>
        <div class="stat-body"><span class="stat-label">Administrator</span><strong class="stat-value"><?= $stat['admin'] ?> orang</strong></div>
    </div>
    <div class="stat-card stat-out">
        <div class="stat-ic">&#931;</div>
        <div class="stat-body"><span class="stat-label">Total Transaksi</span><strong class="stat-value"><?= $stat['trx'] ?> data</strong></div>
    </div>
</section>

<div class="alert alert-info">
    &#128274; <strong>Catatan keamanan:</strong> Password disimpan dalam bentuk <em>hash</em> (terenkripsi satu arah) sesuai praktik keamanan, jadi password asli tidak dapat ditampilkan. Sebagai admin, kamu bisa <strong>mereset</strong> password member kapan saja melalui tombol di bawah.
</div>

<section class="member-grid">
    <?php while ($u = mysqli_fetch_assoc($users)): ?>
        <div class="member-card">
            <div class="member-top">
                <div class="member-ava"><?= inisial($u['nama'] ?: $u['username']) ?></div>
                <div class="member-id">
                    <strong><?= bersih($u['nama'] ?: '-') ?></strong>
                    <span>@<?= bersih($u['username']) ?></span>
                </div>
                <?= role_badge($u['role']) ?>
            </div>

            <div class="member-detail">
                <div class="md-row"><span>ID Pengguna</span><strong>#<?= $u['id'] ?></strong></div>
                <div class="md-row"><span>Terdaftar</span><strong><?= tanggal_indo($u['created_at']) ?></strong></div>
                <div class="md-row"><span>Jumlah Transaksi</span><strong><?= $u['jml_trx'] ?> data</strong></div>
                <div class="md-row"><span>Saldo</span><strong class="<?= $u['saldo'] < 0 ? 'txt-out' : 'txt-in' ?>"><?= rupiah($u['saldo']) ?></strong></div>
                <div class="md-row md-hash"><span>Password (hash)</span><code><?= bersih(substr($u['password'], 0, 28)) ?>&hellip;</code></div>
            </div>

            <div class="member-actions">
                <button type="button" class="btn btn-sm btn-ghost" onclick="this.closest('.member-card').querySelector('.reset-form').classList.toggle('show')">Reset Password</button>
                <?php if ((int) $u['id'] !== 1): ?>
                    <form method="post" onsubmit="return confirm('Hapus member &quot;<?= bersih($u['username']) ?>&quot; beserta semua datanya?')" style="display:inline">
                        <input type="hidden" name="aksi" value="hapus">
                        <input type="hidden" name="target_id" value="<?= $u['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-del">Hapus</button>
                    </form>
                <?php else: ?>
                    <span class="badge badge-admin">Akun utama</span>
                <?php endif; ?>
            </div>

            <form method="post" class="reset-form">
                <input type="hidden" name="aksi" value="reset">
                <input type="hidden" name="target_id" value="<?= $u['id'] ?>">
                <input type="password" name="password_baru" placeholder="Password baru (min 5)" required>
                <button type="submit" class="btn btn-sm btn-primary">Simpan</button>
            </form>
        </div>
    <?php endwhile; ?>
</section>

<?php require 'partials/footer.php'; ?>
