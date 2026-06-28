<?php
// export.php — Export data transaksi (milik user) ke file CSV (dapat dibuka di Excel)
require 'auth.php';
require 'koneksi.php';
require 'functions.php';

// Ikuti filter yang aktif (sama seperti di daftar.php)
$filter = isset($_GET['jenis']) ? $_GET['jenis'] : 'semua';
$cari = isset($_GET['cari']) ? trim($_GET['cari']) : '';
$bulan = isset($_GET['bulan']) ? trim($_GET['bulan']) : '';

$kondisi = ["user_id = " . (int) $user_id];
if ($filter === 'pemasukan' || $filter === 'pengeluaran') {
    $kondisi[] = "jenis = '" . mysqli_real_escape_string($koneksi, $filter) . "'";
}
if ($cari !== '') {
    $kondisi[] = "keterangan LIKE '%" . mysqli_real_escape_string($koneksi, $cari) . "%'";
}
if ($bulan !== '') {
    $kondisi[] = "DATE_FORMAT(tanggal, '%Y-%m') = '" . mysqli_real_escape_string($koneksi, $bulan) . "'";
}

$sql = "SELECT tanggal, keterangan, kategori, jenis, jumlah FROM transaksi WHERE " . implode(' AND ', $kondisi) . " ORDER BY tanggal DESC, id DESC";
$result = mysqli_query($koneksi, $sql);

// Header HTTP agar browser mengunduh file CSV
$namafile = 'laporan-keuangan-' . date('Ymd-His') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $namafile . '"');

$output = fopen('php://output', 'w');
// BOM agar karakter & Rupiah terbaca benar di Excel
fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

// Baris judul kolom
fputcsv($output, ['No', 'Tanggal', 'Keterangan', 'Kategori', 'Jenis', 'Jumlah (Rp)']);

$no = 1;
while ($row = mysqli_fetch_assoc($result)) {
    fputcsv($output, [
        $no++,
        $row['tanggal'],
        $row['keterangan'],
        $row['kategori'],
        ucfirst($row['jenis']),
        $row['jumlah'],
    ]);
}

fclose($output);
exit;
