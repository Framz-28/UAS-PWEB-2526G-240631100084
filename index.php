<?php
// index.php — Halaman Beranda (Dashboard) + Grafik + Anggaran
require 'auth.php';
require 'koneksi.php';
require 'functions.php';

$uid = (int) $user_id;

// Ringkasan keuangan user
$ringkasan = hitung_ringkasan($koneksi, $uid);

// Anggaran bulan ini
$bulan_ini = date('Y-m');
$budget = get_budget($koneksi, $uid, $bulan_ini);
$peng_bulan = pengeluaran_bulan($koneksi, $uid, $bulan_ini);
$persen = $budget > 0 ? min(100, round($peng_bulan / $budget * 100)) : 0;
$sisa = $budget - $peng_bulan;
if ($persen < 70) { $progress_class = 'ok'; }
elseif ($persen < 100) { $progress_class = 'warn'; }
else { $progress_class = 'danger'; }

// 5 transaksi terbaru
$terbaru = mysqli_query($koneksi, "SELECT * FROM transaksi WHERE user_id = $uid ORDER BY tanggal DESC, id DESC LIMIT 5");

// Grafik: pengeluaran per kategori
$kat_labels = [];
$kat_values = [];
$qk = mysqli_query($koneksi, "SELECT COALESCE(NULLIF(kategori, ''), 'Lainnya') AS kat, SUM(jumlah) AS total FROM transaksi WHERE user_id = $uid AND jenis = 'pengeluaran' GROUP BY kat ORDER BY total DESC");
while ($r = mysqli_fetch_assoc($qk)) {
    $kat_labels[] = $r['kat'];
    $kat_values[] = (float) $r['total'];
}

// Grafik: arus kas per bulan
$bln_map = [];
$qm = mysqli_query($koneksi, "SELECT DATE_FORMAT(tanggal, '%Y-%m') AS bln, jenis, SUM(jumlah) AS total FROM transaksi WHERE user_id = $uid GROUP BY bln, jenis ORDER BY bln");
while ($r = mysqli_fetch_assoc($qm)) {
    $b = $r['bln'];
    if (!isset($bln_map[$b])) { $bln_map[$b] = ['pemasukan' => 0, 'pengeluaran' => 0]; }
    $bln_map[$b][$r['jenis']] = (float) $r['total'];
}
$bln_labels = array_keys($bln_map);
$bln_in = [];
$bln_out = [];
foreach ($bln_map as $b) { $bln_in[] = $b['pemasukan']; $bln_out[] = $b['pengeluaran']; }

// Toast notifikasi
$toast = null;
if (isset($_GET['msg']) && $_GET['msg'] === 'budget') {
    $toast = ['msg' => 'Anggaran bulan ini berhasil disimpan.', 'type' => 'success'];
}

$page_title = 'Dashboard';
$active = 'home';
require 'partials/header.php';
?>

<section class="stats">
    <div class="stat-card stat-in">
        <div class="stat-ic">&#8593;</div>
        <div class="stat-body">
            <span class="stat-label">Total Pemasukan</span>
            <strong class="stat-value"><?= rupiah($ringkasan['pemasukan']) ?></strong>
        </div>
    </div>
    <div class="stat-card stat-out">
        <div class="stat-ic">&#8595;</div>
        <div class="stat-body">
            <span class="stat-label">Total Pengeluaran</span>
            <strong class="stat-value"><?= rupiah($ringkasan['pengeluaran']) ?></strong>
        </div>
    </div>
    <div class="stat-card stat-balance">
        <div class="stat-ic">&#9776;</div>
        <div class="stat-body">
            <span class="stat-label">Saldo Akhir</span>
            <strong class="stat-value"><?= rupiah($ringkasan['saldo']) ?></strong>
        </div>
    </div>
    <div class="stat-card stat-count">
        <div class="stat-ic">&#931;</div>
        <div class="stat-body">
            <span class="stat-label">Jumlah Transaksi</span>
            <strong class="stat-value"><?= $ringkasan['jumlah'] ?> data</strong>
        </div>
    </div>
</section>

<section class="panel budget-panel">
    <div class="panel-head">
        <h2>&#127919; Anggaran <?= bulan_indo($bulan_ini) ?></h2>
        <button type="button" class="btn btn-ghost" onclick="document.getElementById('budgetForm').classList.toggle('show')">Atur Anggaran</button>
    </div>

    <?php if ($budget > 0): ?>
        <div class="budget-info">
            <span>Terpakai <strong><?= rupiah($peng_bulan) ?></strong> dari <strong><?= rupiah($budget) ?></strong></span>
            <span class="budget-sisa <?= $sisa < 0 ? 'txt-out' : 'txt-in' ?>">
                <?= $sisa < 0 ? 'Lebih ' . rupiah(abs($sisa)) : 'Sisa ' . rupiah($sisa) ?>
            </span>
        </div>
        <div class="progress">
            <div class="progress-bar bar-<?= $progress_class ?>" style="width: <?= $persen ?>%"><?= $persen ?>%</div>
        </div>
    <?php else: ?>
        <p class="budget-empty">Belum ada anggaran untuk bulan ini. Klik <strong>Atur Anggaran</strong> untuk menetapkan target pengeluaran.</p>
    <?php endif; ?>

    <form id="budgetForm" method="post" action="anggaran.php" class="budget-form">
        <input type="hidden" name="bulan" value="<?= $bulan_ini ?>">
        <input type="number" name="jumlah" min="0" step="any" placeholder="Target pengeluaran bulan ini (Rp)" value="<?= $budget > 0 ? (int) $budget : '' ?>" required>
        <button type="submit" class="btn btn-primary">Simpan</button>
    </form>
</section>

<section class="charts">
    <div class="panel chart-card">
        <div class="panel-head"><h2>Komposisi Keuangan</h2></div>
        <canvas id="chartKomposisi" height="220"></canvas>
    </div>
    <div class="panel chart-card">
        <div class="panel-head"><h2>Pengeluaran per Kategori</h2></div>
        <canvas id="chartKategori" height="220"></canvas>
    </div>
</section>

<section class="panel">
    <div class="panel-head"><h2>Arus Kas per Bulan</h2></div>
    <canvas id="chartBulan" height="90"></canvas>
</section>

<section class="panel">
    <div class="panel-head">
        <h2>Transaksi Terbaru</h2>
        <a href="daftar.php" class="btn btn-ghost">Lihat Semua &rarr;</a>
    </div>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Keterangan</th>
                    <th>Kategori</th>
                    <th>Jenis</th>
                    <th class="ta-right">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($terbaru) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($terbaru)): ?>
                        <tr>
                            <td><?= tanggal_indo($row['tanggal']) ?></td>
                            <td><?= bersih($row['keterangan']) ?></td>
                            <td><?= bersih($row['kategori']) ?: '-' ?></td>
                            <td><?= badge_jenis($row['jenis']) ?></td>
                            <td class="ta-right <?= $row['jenis'] === 'pemasukan' ? 'txt-in' : 'txt-out' ?>">
                                <?= ($row['jenis'] === 'pemasukan' ? '+ ' : '- ') . rupiah($row['jumlah']) ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="empty">Belum ada data transaksi. Mulai dengan menambah data!</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    const fmtRp = function (v) { return 'Rp ' + Number(v).toLocaleString('id-ID'); };
    if (window.Chart) {
        new Chart(document.getElementById('chartKomposisi'), {
            type: 'doughnut',
            data: { labels: ['Pemasukan', 'Pengeluaran'], datasets: [{ data: [<?= (float) $ringkasan['pemasukan'] ?>, <?= (float) $ringkasan['pengeluaran'] ?>], backgroundColor: ['#16a34a', '#dc2626'], borderWidth: 0 }] },
            options: { plugins: { legend: { position: 'bottom' }, tooltip: { callbacks: { label: function (c) { return c.label + ': ' + fmtRp(c.parsed); } } } } }
        });
        new Chart(document.getElementById('chartKategori'), {
            type: 'bar',
            data: { labels: <?= json_encode($kat_labels) ?>, datasets: [{ label: 'Pengeluaran', data: <?= json_encode($kat_values) ?>, backgroundColor: '#7c3aed', borderRadius: 6 }] },
            options: { plugins: { legend: { display: false } }, scales: { y: { ticks: { callback: function (v) { return fmtRp(v); } } } } }
        });
        new Chart(document.getElementById('chartBulan'), {
            type: 'bar',
            data: { labels: <?= json_encode($bln_labels) ?>, datasets: [ { label: 'Pemasukan', data: <?= json_encode($bln_in) ?>, backgroundColor: '#16a34a', borderRadius: 6 }, { label: 'Pengeluaran', data: <?= json_encode($bln_out) ?>, backgroundColor: '#dc2626', borderRadius: 6 } ] },
            options: { plugins: { legend: { position: 'bottom' } }, scales: { y: { ticks: { callback: function (v) { return fmtRp(v); } } } } }
        });
    }
</script>

<?php require 'partials/footer.php'; ?>
