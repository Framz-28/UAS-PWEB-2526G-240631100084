<?php
/**
 * functions.php
 * Kumpulan fungsi bantu (helper) untuk aplikasi SiKeu.
 */

// Fungsi 1: Format angka menjadi format Rupiah
function rupiah($angka)
{
    return "Rp " . number_format((float) $angka, 0, ',', '.');
}

// Fungsi 2: Format tanggal (YYYY-MM-DD) menjadi format Indonesia (cth: 01 Juni 2026)
function tanggal_indo($tanggal)
{
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $ts = strtotime($tanggal);
    if (!$ts) {
        return $tanggal;
    }
    return date('d', $ts) . ' ' . $bulan[(int) date('n', $ts)] . ' ' . date('Y', $ts);
}

// Fungsi 3: Format "YYYY-MM" menjadi "Juni 2026"
function bulan_indo($ym)
{
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $parts = explode('-', $ym);
    if (count($parts) < 2) {
        return $ym;
    }
    $b = (int) $parts[1];
    return ($bulan[$b] ?? $ym) . ' ' . $parts[0];
}

// Fungsi 4: Menghitung ringkasan keuangan milik 1 user
function hitung_ringkasan($koneksi, $user_id)
{
    $user_id = (int) $user_id;
    $data = ['pemasukan' => 0, 'pengeluaran' => 0, 'saldo' => 0, 'jumlah' => 0];
    $query = mysqli_query($koneksi, "SELECT jenis, SUM(jumlah) AS total, COUNT(*) AS n FROM transaksi WHERE user_id = $user_id GROUP BY jenis");

    // Perulangan: membaca tiap baris hasil query
    while ($row = mysqli_fetch_assoc($query)) {
        if ($row['jenis'] === 'pemasukan') {
            $data['pemasukan'] = $row['total'];
        } else {
            $data['pengeluaran'] = $row['total'];
        }
        $data['jumlah'] += (int) $row['n'];
    }

    $data['saldo'] = $data['pemasukan'] - $data['pengeluaran'];
    return $data;
}

// Fungsi 5: Menampilkan badge/label sesuai jenis transaksi
function badge_jenis($jenis)
{
    // Percabangan
    if ($jenis === 'pemasukan') {
        return '<span class="badge badge-in">Pemasukan</span>';
    }
    return '<span class="badge badge-out">Pengeluaran</span>';
}

// Fungsi 6: Membersihkan input dari karakter berbahaya (mencegah XSS sederhana)
function bersih($teks)
{
    return htmlspecialchars(trim((string) $teks), ENT_QUOTES, 'UTF-8');
}

// Fungsi 7: Memastikan akun admin default tersedia (auto-seed, selalu menjadi user id = 1)
function pastikan_admin($koneksi)
{
    $res = mysqli_query($koneksi, "SELECT COUNT(*) AS n FROM users");
    if (!$res) {
        return;
    }
    $row = mysqli_fetch_assoc($res);
    if ((int) $row['n'] === 0) {
        $username = 'admin';
        $nama = 'Administrator';
        $role = 'admin';
        $hash = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($koneksi, "INSERT INTO users (username, password, nama, role) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssss", $username, $hash, $nama, $role);
        mysqli_stmt_execute($stmt);
    }
}

// Fungsi 8: Inisial nama untuk avatar
function inisial($nama)
{
    $nama = trim((string) $nama);
    if ($nama === '') {
        return 'U';
    }
    return strtoupper(substr($nama, 0, 1));
}

// Fungsi 9: Ambil nominal anggaran (budget) user untuk 1 bulan
function get_budget($koneksi, $user_id, $bulan)
{
    $stmt = mysqli_prepare($koneksi, "SELECT jumlah FROM budget WHERE user_id = ? AND bulan = ?");
    mysqli_stmt_bind_param($stmt, "is", $user_id, $bulan);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    return $row ? (float) $row['jumlah'] : 0.0;
}

// Fungsi 10: Total pengeluaran user pada 1 bulan (format YYYY-MM)
function pengeluaran_bulan($koneksi, $user_id, $bulan)
{
    $stmt = mysqli_prepare($koneksi, "SELECT SUM(jumlah) AS total FROM transaksi WHERE user_id = ? AND jenis = 'pengeluaran' AND DATE_FORMAT(tanggal, '%Y-%m') = ?");
    mysqli_stmt_bind_param($stmt, "is", $user_id, $bulan);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    return $row && $row['total'] ? (float) $row['total'] : 0.0;
}

// Fungsi 11: Badge untuk role user
function role_badge($role)
{
    if ($role === 'admin') {
        return '<span class="badge badge-admin">&#128737; Administrator</span>';
    }
    return '<span class="badge badge-member">&#128100; Member</span>';
}
