<?php
// edit.php — Halaman Edit Data (UPDATE), hanya untuk data milik user yang login
require 'auth.php';
require 'koneksi.php';
require 'functions.php';

$error = '';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    header("Location: daftar.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tanggal = $_POST['tanggal'] ?? '';
    $keterangan = trim($_POST['keterangan'] ?? '');
    $kategori = trim($_POST['kategori'] ?? '');
    $jenis = $_POST['jenis'] ?? '';
    $jumlah = $_POST['jumlah'] ?? '';

    if ($tanggal === '' || $keterangan === '' || $jenis === '' || $jumlah === '') {
        $error = 'Field tanggal, keterangan, jenis, dan jumlah wajib diisi.';
    } elseif (!in_array($jenis, ['pemasukan', 'pengeluaran'])) {
        $error = 'Jenis transaksi tidak valid.';
    } elseif (!is_numeric($jumlah) || (float) $jumlah <= 0) {
        $error = 'Jumlah harus berupa angka lebih dari 0.';
    } else {
        $stmt = mysqli_prepare($koneksi, "UPDATE transaksi SET tanggal=?, keterangan=?, kategori=?, jenis=?, jumlah=? WHERE id=? AND user_id=?");
        $jml = (float) $jumlah;
        mysqli_stmt_bind_param($stmt, "ssssdii", $tanggal, $keterangan, $kategori, $jenis, $jml, $id, $user_id);
        if (mysqli_stmt_execute($stmt)) {
            header("Location: daftar.php?msg=edit");
            exit;
        } else {
            $error = 'Gagal memperbarui data: ' . mysqli_error($koneksi);
        }
    }
}

// Ambil data lama (hanya milik user)
$stmt = mysqli_prepare($koneksi, "SELECT * FROM transaksi WHERE id=? AND user_id=?");
mysqli_stmt_bind_param($stmt, "ii", $id, $user_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($res);

if (!$data) {
    header("Location: daftar.php");
    exit;
}

$page_title = 'Edit Data';
$active = 'daftar';
require 'partials/header.php';
?>

<section class="panel panel-form">
    <div class="panel-head">
        <h2>Edit Transaksi #<?= $data['id'] ?></h2>
        <a href="daftar.php" class="btn btn-ghost">&larr; Kembali</a>
    </div>

    <?php if ($error !== ''): ?>
        <div class="alert alert-error"><?= $error ?></div>
    <?php endif; ?>

    <form method="post" class="form">
        <div class="form-row">
            <div class="form-group">
                <label>Tanggal</label>
                <input type="date" name="tanggal" value="<?= bersih($data['tanggal']) ?>" required>
            </div>
            <div class="form-group">
                <label>Jenis</label>
                <select name="jenis" required>
                    <option value="pemasukan" <?= $data['jenis'] === 'pemasukan' ? 'selected' : '' ?>>Pemasukan</option>
                    <option value="pengeluaran" <?= $data['jenis'] === 'pengeluaran' ? 'selected' : '' ?>>Pengeluaran</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label>Keterangan</label>
            <input type="text" name="keterangan" value="<?= bersih($data['keterangan']) ?>" required>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Kategori</label>
                <input type="text" name="kategori" value="<?= bersih($data['kategori']) ?>">
            </div>
            <div class="form-group">
                <label>Jumlah (Rp)</label>
                <input type="number" name="jumlah" min="0" step="any" value="<?= bersih($data['jumlah']) ?>" required>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Perbarui Data</button>
            <a href="daftar.php" class="btn btn-ghost">Batal</a>
        </div>
    </form>
</section>

<?php require 'partials/footer.php'; ?>
