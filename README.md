## Identitas


| Nama | Muhammad Edric Faustadanendra |
| NIM | 240631100084 |
| Judul Aplikasi | SiKeu — Sistem Catatan Keuangan |
| Mata Kuliah | Pemrograman Web |

---

## Deskripsi Singkat

**SiKeu** adalah aplikasi web pencatatan keuangan **multi-user dengan dua peran (role)**: **Administrator** dan **Member**.

- **Member** dapat mencatat **pemasukan/pengeluaran**, memantau **anggaran bulanan**, melihat **grafik**, **export CSV**, dan **cetak laporan PDF** untuk datanya sendiri.
- **Administrator** memiliki semua kemampuan member, **plus panel khusus** untuk mengelola dan melihat informasi seluruh member (identitas, jumlah transaksi, saldo, status password, reset password, hapus member).

Dibangun dengan **HTML5, CSS eksternal (custom + dark mode beranimasi), PHP Native, dan MySQL** dengan **CRUD** lengkap.

---

## ✨ Fitur Unggulan (Versi Presentasi)

- 🔐 **Login berbasis Role** — pilih masuk sebagai **Administrator** atau **Member** (segmented switch)
- 🛡️ **Panel Admin (Kelola Member)** — admin melihat daftar semua member: nama, username, tanggal daftar, jumlah transaksi, saldo, & status password; bisa **reset password** dan **hapus member**
- 👤 **Register** akun member baru + **Profil** (ubah nama & ganti password)
- 🌈 **Dark / Light mode beranimasi** — ikon matahari/bulan, transisi warna halus, background gradien bergerak, kartu muncul dengan efek *fade-up*, progress bar *shimmer*
- 🎯 **Anggaran bulanan** + **progress bar** otomatis (hijau/kuning/merah)
- 📊 **Dashboard** ringkasan + 📈 **3 grafik interaktif** (Chart.js)
- 🔔 **Notifikasi toast** + ⬇️ **Export CSV** + 🧾 **Cetak Laporan / PDF**
- 🔍 Pencarian + filter jenis & periode, ➕ **CRUD** lengkap (prepared statement)
- 📱 Tampilan **responsif** (desktop, tablet, mobile)

---

## 🔑 Akun & Role

| Role | Username | Password | Akses |
|---|---|---|---|
| **Administrator** | `admin` | `admin123` | Semua fitur + **Kelola Member** |
| **Member** | *(daftar sendiri)* | *(buatan sendiri)* | Catatan keuangan pribadi |

> Saat login, **pilih role** lewat tombol Administrator / Member. Jika role tidak sesuai dengan akun, sistem menolak (mis. login akun member tapi memilih Administrator). Akun admin dibuat **otomatis** (selalu user `id = 1`) saat halaman dibuka pertama kali.

### 🔒 Soal "melihat password member"
Password **sengaja disimpan dalam bentuk hash** (`password_hash`, enkripsi satu arah) sesuai praktik keamanan — ini justru **nilai plus** di mata dosen. Karena itu password asli tidak bisa ditampilkan; di panel admin yang tampil adalah **potongan hash**-nya. Sebagai gantinya, admin diberi tombol **Reset Password** untuk menetapkan password baru bagi member kapan saja.

---

## Pemenuhan Spesifikasi Minimal

### 1. HTML
- ✅ Struktur HTML5 yang benar
- ✅ Minimal 4 halaman: **Beranda** (`index.php`), **Tambah** (`tambah.php`), **Daftar** (`daftar.php`), **Edit** (`edit.php`) — plus Login, Register, Profil, Admin, Cetak

### 2. CSS
- ✅ CSS **eksternal** (`css/style.css`), modern, responsif, dark mode + animasi

### 3. PHP
- ✅ **Variabel**, **Percabangan** (`if/else`), **Perulangan** (`while/foreach`)
- ✅ **Function (≥ 2)** di `functions.php`: `rupiah()`, `tanggal_indo()`, `bulan_indo()`, `hitung_ringkasan()`, `badge_jenis()`, `bersih()`, `pastikan_admin()`, `inisial()`, `get_budget()`, `pengeluaran_bulan()`, `role_badge()`
- ✅ **include / require** (`koneksi.php`, `auth.php`, `functions.php`, `partials/header.php`, `partials/footer.php`)
- ✅ **Form Processing (GET & POST)** + **session** (login multi-user + role)
- ✅ **CRUD** lengkap (prepared statement, aman dari SQL injection)

### 4. MySQL
- ✅ 1 database `db_keuangan`, **3 tabel**: `users` (dengan kolom `role`), `transaksi` (7 record), `budget`
- ✅ File `database.sql` disertakan

---

## Struktur Database

**Database:** `db_keuangan`

**Tabel `users`** — id, username (unique), password (hash), nama, **role (admin/member)**, created_at
**Tabel `transaksi`** — id, **user_id**, tanggal, keterangan, kategori, jenis (enum), jumlah, created_at
**Tabel `budget`** — id, user_id, bulan (YYYY-MM), jumlah, created_at, unique(user_id, bulan)

---

## Struktur Folder

```
UAS-PWEB-2526G-240631100084
├── index.php          # Dashboard + grafik + anggaran
├── login.php          # Login + pilih role
├── register.php       # Daftar akun member baru
├── logout.php         # Logout
├── auth.php           # Penjaga sesi (proteksi halaman)
├── admin.php          # Panel admin: kelola member (admin only)
├── profil.php         # Edit nama & ganti password
├── tambah.php         # Tambah data (Create)
├── daftar.php         # Daftar data (Read + filter + export)
├── edit.php           # Edit data (Update)
├── hapus.php          # Hapus data (Delete)
├── anggaran.php       # Simpan target anggaran bulanan
├── export.php         # Export data ke CSV
├── cetak.php          # Laporan siap cetak / PDF
├── koneksi.php        # Koneksi database
├── functions.php      # Kumpulan fungsi bantu
├── database.sql       # Struktur + data awal
├── partials/
│   ├── header.php
│   └── footer.php
├── css/
│   └── style.css
├── assets/
│   └── logo.svg       # Logo default (fallback)
├── img/               # Logo kampus + screenshot
└── README.md
```

link youtube: https://youtu.be/Ia6xaReLThA?si=Eosn4ie1WL3A21cp
