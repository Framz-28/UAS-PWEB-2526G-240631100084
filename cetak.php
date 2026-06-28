<?php
require 'auth.php';
require 'koneksi.php';
require 'functions.php';

$ringkasan = hitung_ringkasan($koneksi, $user_id);
$rows = mysqli_query($koneksi, "SELECT * FROM transaksi WHERE user_id = " . (int) $user_id . " ORDER BY tanggal ASC, id ASC");
$nama = $_SESSION['nama'] ?? 'Pengguna';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Keuangan &mdash; SiKeu</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="print-page">
    <div class="print-bar no-print">
        <a href="daftar.php" class="btn btn-ghost">&larr; Kembali</a>
        <button onclick="window.print()" class="btn btn-primary">&#128424; Cetak / Simpan PDF</button>
    </div>

    <div class="print-sheet">
        <div class="print-head">
            <h1>Laporan Keuangan</h1>
            <p>SiKeu — Sistem Catatan Keuangan Mahasiswa</p>
            <p>Pemilik: <strong><?= bersih($nama) ?></strong> &middot; Dicetak: <?= tanggal_indo(date('Y-m-d')) ?></p>
        </div>

        <table class="print-summary">
            <tr>
                <td>Total Pemasukan</td>
                <td class="ta-right txt-in"><?= rupiah($ringkasan['pemasukan']) ?></td>
            </tr>
            <tr>
                <td>Total Pengeluaran</td>
                <td class="ta-right txt-out"><?= rupiah($ringkasan['pengeluaran']) ?></td>
            </tr>
            <tr class="print-saldo">
                <td>Saldo Akhir</td>
                <td class="ta-right"><?= rupiah($ringkasan['saldo']) ?></td>
            </tr>
        </table>

        <table class="table print-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Keterangan</th>
                    <th>Kategori</th>
                    <th>Jenis</th>
                    <th class="ta-right">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; ?>
                <?php while ($row = mysqli_fetch_assoc($rows)): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= tanggal_indo($row['tanggal']) ?></td>
                        <td><?= bersih($row['keterangan']) ?></td>
                        <td><?= bersih($row['kategori']) ?: '-' ?></td>
                        <td><?= ucfirst($row['jenis']) ?></td>
                        <td class="ta-right"><?= ($row['jenis'] === 'pemasukan' ? '+ ' : '- ') . rupiah($row['jumlah']) ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div class="print-foot">
            <p>Dokumen ini dihasilkan otomatis oleh aplikasi SiKeu.</p>
        </div>
    </div>
</body>
</html>
