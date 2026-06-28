<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$nama_user = $_SESSION['nama'] ?? 'Pengguna';
$role_user = $_SESSION['role'] ?? 'member';
$is_admin = ($role_user === 'admin');
$active = $active ?? '';
function nav_active($a, $b) { return $a === $b ? 'active' : ''; }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? htmlspecialchars($page_title) . ' — ' : '' ?>SiKeu</title>
    <!-- Set tema sebelum render untuk mencegah kedip (flash) -->
    <script>(function(){try{if(localStorage.getItem('theme')==='dark'){document.documentElement.setAttribute('data-theme','dark');}}catch(e){}})();</script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div id="toastBox"></div>
<script>
    function showToast(msg, type){
        if(!msg){return;}
        var box=document.getElementById('toastBox');
        var el=document.createElement('div');
        el.className='toast toast-'+(type||'success');
        el.textContent=msg;
        box.appendChild(el);
        setTimeout(function(){el.classList.add('show');},10);
        setTimeout(function(){el.classList.remove('show');setTimeout(function(){el.remove();},300);},3200);
    }
    function toggleTheme(){
        var h=document.documentElement;
        document.body.classList.add('theme-anim');
        if(h.getAttribute('data-theme')==='dark'){h.removeAttribute('data-theme');try{localStorage.setItem('theme','light');}catch(e){}}
        else{h.setAttribute('data-theme','dark');try{localStorage.setItem('theme','dark');}catch(e){}}
        setTimeout(function(){document.body.classList.remove('theme-anim');},600);
    }
    function toggleSidebar(){document.body.classList.toggle('sidebar-open');}
</script>
<?php if (!empty($toast['msg'])): ?>
<script>showToast(<?= json_encode($toast['msg']) ?>, <?= json_encode($toast['type'] ?? 'success') ?>);</script>
<?php endif; ?>

<button class="menu-toggle no-print" onclick="toggleSidebar()">&#9776;</button>
<div class="overlay" onclick="toggleSidebar()"></div>

<div class="layout">
    <aside class="sidebar no-print">
        <div class="brand">
            <img src="img/logo-kampus.png" alt="Logo" class="brand-logo-img" onerror="this.onerror=null;this.src='assets/logo.svg'">
            <div class="brand-text">
                <strong>SiKeu</strong>
                <span>Catatan Keuangan</span>
            </div>
        </div>

        <nav class="menu">
            <a href="index.php" class="<?= nav_active($active, 'home') ?>"><span class="ic">&#9632;</span> Dashboard</a>
            <a href="daftar.php" class="<?= nav_active($active, 'daftar') ?>"><span class="ic">&#9776;</span> Daftar Transaksi</a>
            <a href="tambah.php" class="<?= nav_active($active, 'tambah') ?>"><span class="ic">&#43;</span> Tambah Data</a>
            <a href="cetak.php" class="<?= nav_active($active, 'cetak') ?>"><span class="ic">&#128424;</span> Cetak Laporan</a>
            <?php if ($is_admin): ?>
            <a href="admin.php" class="<?= nav_active($active, 'admin') ?>"><span class="ic">&#128737;</span> Kelola Member</a>
            <?php endif; ?>
            <a href="profil.php" class="<?= nav_active($active, 'profil') ?>"><span class="ic">&#9737;</span> Profil</a>
        </nav>

        <div class="sidebar-foot">
            <a href="logout.php" class="logout-link">&#8631; Keluar</a>
        </div>
    </aside>

    <div class="content">
        <header class="topbar no-print">
            <div>
                <h1><?= isset($page_title) ? htmlspecialchars($page_title) : 'SiKeu' ?></h1>
                <span class="subtitle">Sistem Catatan Keuangan Mahasiswa</span>
            </div>
            <div class="topbar-actions">
                <button class="theme-toggle" onclick="toggleTheme()" title="Ganti tema terang/gelap"><span class="sun">&#9728;</span><span class="moon">&#9789;</span></button>
                <a href="tambah.php" class="btn btn-primary btn-top">+ Transaksi Baru</a>
                <a href="profil.php" class="user-chip">
                    <span class="user-ava"><?= inisial($nama_user) ?></span>
                    <span class="user-meta">
                        <strong><?= htmlspecialchars($nama_user) ?></strong>
                        <small><?= $is_admin ? 'Administrator' : 'Member' ?></small>
                    </span>
                </a>
            </div>
        </header>

        <main class="container">
