<?php
/**
 * koneksi.php
 * Koneksi ke database MySQL menggunakan MySQLi.
 * Sesuaikan $host, $user, $pass jika berbeda (default XAMPP: root tanpa password).
 */

$host = "localhost";
$user = "root";
$pass = "";
$nama_db = "db_keuangan";

// Membuat koneksi
$koneksi = mysqli_connect($host, $user, $pass, $nama_db);

// Percabangan: cek apakah koneksi berhasil
if (!$koneksi) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}

// Set charset agar mendukung karakter Indonesia / simbol
mysqli_set_charset($koneksi, "utf8mb4");
