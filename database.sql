CREATE DATABASE IF NOT EXISTS db_keuangan
    DEFAULT CHARACTER SET utf8mb4
    COLLATE utf8mb4_general_ci;

USE db_keuangan;

DROP TABLE IF EXISTS transaksi;
DROP TABLE IF EXISTS budget;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    username    VARCHAR(50) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,
    nama        VARCHAR(100) DEFAULT NULL,
    role        ENUM('admin','member') NOT NULL DEFAULT 'member',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE transaksi (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NOT NULL DEFAULT 1,
    tanggal     DATE NOT NULL,
    keterangan  VARCHAR(150) NOT NULL,
    kategori    VARCHAR(50) DEFAULT NULL,
    jenis       ENUM('pemasukan','pengeluaran') NOT NULL,
    jumlah      DECIMAL(15,2) NOT NULL DEFAULT 0,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


INSERT INTO transaksi (user_id, tanggal, keterangan, kategori, jenis, jumlah) VALUES
(1, '2026-06-01', 'Uang saku bulanan',        'Pemasukan Rutin', 'pemasukan',   2000000),
(1, '2026-06-02', 'Beli buku kuliah',         'Pendidikan',      'pengeluaran',  150000),
(1, '2026-06-05', 'Bayar kos bulanan',        'Tempat Tinggal',  'pengeluaran',  700000),
(1, '2026-06-10', 'Honor freelance desain',   'Sampingan',       'pemasukan',    500000),
(1, '2026-06-12', 'Makan & jajan harian',     'Konsumsi',        'pengeluaran',  250000),
(1, '2026-06-18', 'Top up pulsa & internet',  'Komunikasi',      'pengeluaran',  100000),
(1, '2026-06-22', 'Bonus project kelompok',   'Sampingan',       'pemasukan',    350000);


CREATE TABLE budget (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NOT NULL,
    bulan       CHAR(7) NOT NULL,            -- format YYYY-MM
    jumlah      DECIMAL(15,2) NOT NULL DEFAULT 0,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_user_bulan (user_id, bulan)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO budget (user_id, bulan, jumlah) VALUES
(1, '2026-06', 1500000);
