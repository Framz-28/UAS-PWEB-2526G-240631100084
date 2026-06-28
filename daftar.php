<?php
require 'auth.php';
require 'koneksi.php';
require 'functions.php';

$uid = (int) $user_id;

$filter = isset($_GET['jenis']) ? $_GET['jenis'] : 'semua';
$cari = isset($_GET['cari']) ? trim($_GET['cari']) : '';
$bulan = isset($_GET['bulan']) ? trim($_GET['bulan']) : '';

$kondisi = ["user_id = $uid"];
if ($filter === 'pemasukan' || $filter === 'pengeluaran') {
    $kondisi[] = "jenis = '" . mysqli_real_escape_string($koneksi, $filter) . "'";
}
if ($cari !== '') {
    $kondisi[] = "keterangan LIKE '%" . mysqli_real_escape_string($koneksi, $cari) . "%'";
}
if ($bulan !== '') {
    $kondisi[] = "DATE_FORMAT(tanggal, '%Y-%m') = '" . mysqli_real_escape_string($koneksi, $bulan) . "'";
}

$sql = "SELECT * FROM transaksi WHERE " . implode(' AND ', $kondisi) . " ORDER BY tanggal DESC, id DESC";
$result = mysqli_query($koneksi, $sql);

$qs = http_build_query(['jenis' => $filter, 'cari' => $cari, 'bulan' => $bulan]);

$toast = null;
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'tambah') $toast = ['msg' => 'Data transaksi berhasil ditambahkan.', 'type' => 'success'];
    elseif ($_GET['msg'] === 'edit') $toast = ['msg' => 'Data transaksi berhasil diperbarui.', 'type' => 'info'];
    elseif ($_GET['msg'] === 'hapus') $toast = ['msg' => 'Data transaksi berhasil dihapus.', 'type' => 'error'];
}

$page_title = 'Daftar Transaksi';
$active = 'daftar';
require 'partials/header.php';
?>

<section class="panel">
    <div class="panel-head">
        <h2>Semua Transaksi</h2>
        <div class="head-actions">
            <a href="cetak.php" class="btn btn-ghost">&#128424; Cetak / PDF</a>
            <a href="export.php?<?= $qs ?>" class="btn btn-secondary">&#8681; Export CSV</a>
            <a href="tambah.php" class="btn btn-primary">+ Tambah Data</a>
        </div>
    </div>

    <form method="get" class="toolbar">
        <input type="text" name="cari" placeholder="Cari keterangan..." value="<?= bersih($cari) ?>">
        <input type="month" name="bulan" value="<?= bersih($bulan) ?>">
        <select name="jenis">
            <option value="semua" <?= $filter === 'semua' ? 'selected' : '' ?>>Semua Jenis</option>
            <option value="pemasukan" <?= $filter === 'pemasukan' ? 'selected' : '' ?>>Pemasukan</option>
            <option value="pengeluaran" <?= $filter === 'pengeluaran' ? 'selected' : '' ?>>Pengeluaran</option>
        </select>
        <button type="submit" class="btn btn-secondary">Filter</button>
        <a href="daftar.php" class="btn btn-ghost">Reset</a>
    </form>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Keterangan</th>
                    <th>Kategori</th>
                    <th>Jenis</th>
                    <th class="ta-right">Jumlah</th>
                    <th class="ta-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && mysqli_num_rows($result) > 0): ?>
                    <?php $no = 1; ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= tanggal_indo($row['tanggal']) ?></td>
                            <td><?= bersih($row['keterangan']) ?></td>
                            <td><?= bersih($row['kategori']) ?: '-' ?></td>
                            <td><?= badge_jenis($row['jenis']) ?></td>
                            <td class="ta-right <?= $row['jenis'] === 'pemasukan' ? 'txt-in' : 'txt-out' ?>">
                                <?= ($row['jenis'] === 'pemasukan' ? '+ ' : '- ') . rupiah($row['jumlah']) ?>
                            </td>
                            <td class="ta-center actions">
                                <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-edit">Edit</a>
                                <a href="hapus.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-del" onclick="return confirm('Yakin ingin menghapus data ini?')">Hapus</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="7" class="empty">Tidak ada data yang cocok.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require 'partials/footer.php'; ?>
