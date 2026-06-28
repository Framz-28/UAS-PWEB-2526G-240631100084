<?php
// tambah.php — Halaman Tambah Data (CREATE, Form Processing POST)
require 'auth.php';
require 'koneksi.php';
require 'functions.php';

$error = '';
$old = ['tanggal' => date('Y-m-d'), 'keterangan' => '', 'kategori' => '', 'jenis' => 'pengeluaran', 'jumlah' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['tanggal'] = $_POST['tanggal'] ?? '';
    $old['keterangan'] = trim($_POST['keterangan'] ?? '');
    $old['kategori'] = trim($_POST['kategori'] ?? '');
    $old['jenis'] = $_POST['jenis'] ?? '';
    $old['jumlah'] = $_POST['jumlah'] ?? '';

    // Validasi (percabangan)
    if ($old['tanggal'] === '' || $old['keterangan'] === '' || $old['jenis'] === '' || $old['jumlah'] === '') {
        $error = 'Field tanggal, keterangan, jenis, dan jumlah wajib diisi.';
    } elseif (!in_array($old['jenis'], ['pemasukan', 'pengeluaran'])) {
        $error = 'Jenis transaksi tidak valid.';
    } elseif (!is_numeric($old['jumlah']) || (float) $old['jumlah'] <= 0) {
        $error = 'Jumlah harus berupa angka lebih dari 0.';
    } else {
        // Simpan ke database (prepared statement) — disertai user_id pemilik
        $stmt = mysqli_prepare($koneksi, "INSERT INTO transaksi (user_id, tanggal, keterangan, kategori, jenis, jumlah) VALUES (?, ?, ?, ?, ?, ?)");
        $jumlah = (float) $old['jumlah'];
        mysqli_stmt_bind_param($stmt, "issssd", $user_id, $old['tanggal'], $old['keterangan'], $old['kategori'], $old['jenis'], $jumlah);
        if (mysqli_stmt_execute($stmt)) {
            header("Location: daftar.php?msg=tambah");
            exit;
        } else {
            $error = 'Gagal menyimpan data: ' . mysqli_error($koneksi);
        }
    }
}

$page_title = 'Tambah Data';
$active = 'tambah';
require 'partials/header.php';
?>

<section class="panel panel-form">
    <div class="panel-head">
        <h2>Tambah Transaksi Baru</h2>
        <a href="daftar.php" class="btn btn-ghost">&larr; Kembali</a>
    </div>

    <?php if ($error !== ''): ?>
        <div class="alert alert-error"><?= $error ?></div>
    <?php endif; ?>

    <form method="post" class="form">
        <div class="form-row">
            <div class="form-group">
                <label>Tanggal</label>
                <input type="date" name="tanggal" value="<?= bersih($old['tanggal']) ?>" required>
            </div>
            <div class="form-group">
                <label>Jenis</label>
                <select name="jenis" required>
                    <option value="pemasukan" <?= $old['jenis'] === 'pemasukan' ? 'selected' : '' ?>>Pemasukan</option>
                    <option value="pengeluaran" <?= $old['jenis'] === 'pengeluaran' ? 'selected' : '' ?>>Pengeluaran</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label>Keterangan</label>
            <input type="text" name="keterangan" placeholder="cth: Uang saku bulanan" value="<?= bersih($old['keterangan']) ?>" required>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Kategori</label>
                <input type="text" name="kategori" placeholder="cth: Konsumsi" value="<?= bersih($old['kategori']) ?>">
            </div>
            <div class="form-group">
                <label>Jumlah (Rp)</label>
                <input type="number" name="jumlah" min="0" step="any" placeholder="cth: 150000" value="<?= bersih($old['jumlah']) ?>" required>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Simpan Data</button>
            <a href="daftar.php" class="btn btn-ghost">Batal</a>
        </div>
    </form>
</section>

<?php require 'partials/footer.php'; ?>
