<?php
/**
 * auth.php
 * Penjaga halaman: sertakan (require) di bagian paling atas tiap halaman privat.
 * Jika belum login, pengguna diarahkan ke halaman login.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: login.php");
    exit;
}

// ID user yang sedang login (dipakai untuk memfilter data per-pengguna)
$user_id = (int) ($_SESSION['user_id'] ?? 0);
