<?php
// hapus.php — Menghapus data (DELETE) milik user yang sedang login
require 'auth.php';
require 'koneksi.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id > 0) {
    // AND user_id memastikan user hanya bisa menghapus datanya sendiri
    $stmt = mysqli_prepare($koneksi, "DELETE FROM transaksi WHERE id = ? AND user_id = ?");
    mysqli_stmt_bind_param($stmt, "ii", $id, $user_id);
    mysqli_stmt_execute($stmt);
}

header("Location: daftar.php?msg=hapus");
exit;
