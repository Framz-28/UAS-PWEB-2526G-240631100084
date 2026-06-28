<?php
require 'auth.php';
require 'koneksi.php';
require 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bulan = trim($_POST['bulan'] ?? date('Y-m'));
    $jumlah = $_POST['jumlah'] ?? 0;

    if (is_numeric($jumlah) && (float) $jumlah >= 0 && $bulan !== '') {
        $jml = (float) $jumlah;
        // Upsert: simpan / perbarui anggaran bulan tsb (unique user_id + bulan)
        $stmt = mysqli_prepare(
            $koneksi,
            "INSERT INTO budget (user_id, bulan, jumlah) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE jumlah = VALUES(jumlah)"
        );
        mysqli_stmt_bind_param($stmt, "isd", $user_id, $bulan, $jml);
        mysqli_stmt_execute($stmt);
    }
}

header("Location: index.php?msg=budget");
exit;
