<?php
/**
 * File: setup_db.php
 * Deskripsi: Skrip PHP untuk menghubungkan ke database, 
 * menghapus/membuat ulang tabel, dan memasukkan data dummy sesuai permintaan user.
 * * FIX: Menghapus karakter spasi non-standar, menambahkan backticks (` `) pada nama kolom,
 * dan mengganti tipe data 'boolean' menjadi 'TINYINT(1)' untuk kompatibilitas MariaDB/MySQL.
 */

// ------------------------------------------
// 1. Konfigurasi Koneksi Database
// ------------------------------------------
$servername = "localhost";
$username = "root"; 
$password = "";     
$dbname = "rpl_test"; // GANTI DENGAN NAMA DATABASE YANG AKTIF

// Membuat koneksi
$conn = new mysqli($servername, $username, $password, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    // Tambahkan die() agar skrip berhenti jika koneksi gagal
    die("Koneksi gagal: " . $conn->connect_error);
}
echo "<h2>✅ Koneksi Database Berhasil</h2>";

// ------------------------------------------
// 2. Skrip SQL: DROP dan CREATE TABLES (FIXED)
// ------------------------------------------
$sql_schema = "
-- Nonaktifkan pemeriksaan kunci asing sementara
SET FOREIGN_KEY_CHECKS = 0;

-- DROPPING TABLES (Urutan Dibalik dari CREATE)
DROP TABLE IF EXISTS nilai;
DROP TABLE IF EXISTS komponenPenilaian;
DROP TABLE IF EXISTS anggotaKelompok;
DROP TABLE IF EXISTS kelompok;
DROP TABLE IF EXISTS tugasBesar;
DROP TABLE IF EXISTS peserta;
DROP TABLE IF EXISTS pengampu;
DROP TABLE IF EXISTS kelas;
DROP TABLE IF EXISTS mataKuliah;
DROP TABLE IF EXISTS dosen;
DROP TABLE IF EXISTS mahasiswa;
DROP TABLE IF EXISTS pengguna;

-- CREATING TABLES (Sintaks dibersihkan dan ditambahkan backticks)
CREATE TABLE IF NOT EXISTS pengguna(
    `username` VARCHAR(20) PRIMARY KEY,
    `pass` VARCHAR(255),
    `tipePengguna` INT
);
CREATE TABLE IF NOT EXISTS mahasiswa(
    `npm` char(10) PRIMARY KEY,
    `nama` varchar(30),
    `username` VARCHAR(20),
    FOREIGN KEY (`username`) REFERENCES pengguna(`username`)
);
CREATE TABLE IF NOT EXISTS dosen(
    `nik` char(20) PRIMARY KEY,
    `nama` varchar(30),
    `username` VARCHAR(20),
    FOREIGN KEY (`username`) REFERENCES pengguna(`username`)
);
CREATE TABLE IF NOT EXISTS mataKuliah(
    `kodeMataKuliah` varchar(10) PRIMARY KEY,
    `namaMataKuliah` varchar(30)
);
CREATE TABLE IF NOT EXISTS kelas(
    `kodeMataKuliah` varchar(10),
    `kodeKelas` varchar(3),
    `semester` int,
    PRIMARY KEY(`kodeMataKuliah`,`kodeKelas`,`semester`),
    FOREIGN KEY(`kodeMataKuliah`) REFERENCES mataKuliah(`kodeMataKuliah`)
);
CREATE TABLE IF NOT EXISTS pengampu(
    `nikPengampu` char(20),
    `kodeMataKuliah` varchar(10),
    `kodeKelas` varchar(3),
    `semester` int,
    PRIMARY KEY(`kodeMataKuliah`,`kodeKelas`,`semester`),
    FOREIGN KEY(`nikPengampu`) REFERENCES dosen(`nik`),
    FOREIGN KEY(`kodeMataKuliah`, `kodeKelas`, `semester`) REFERENCES kelas(`kodeMataKuliah`,`kodeKelas`,`semester`)
);
CREATE TABLE IF NOT EXISTS peserta(
    `npmPeserta` char(20),
    `kodeMataKuliah` varchar(10),
    `kodeKelas` varchar(3),
    `semester` int,
    PRIMARY KEY(`npmPeserta`, `kodeMataKuliah`, `kodeKelas`, `semester`),
    FOREIGN KEY (`npmPeserta`) REFERENCES mahasiswa(`npm`),
    FOREIGN KEY(`kodeMataKuliah`, `kodeKelas`, `semester`) REFERENCES kelas(`kodeMataKuliah`,`kodeKelas`,`semester`)
);
CREATE TABLE IF NOT EXISTS tugasBesar(
    `namaTugasBesar` varchar(30),
    `kodeMataKuliah` varchar(10),
    `kodeKelas` varchar(3),
    `semester` int,
    `banyakAnggotaKelompok` int,
    `isLocked` TINYINT(1), -- FIX: Menggunakan TINYINT(1)
    PRIMARY KEY(`namaTugasBesar`,`kodeMataKuliah`,`kodeKelas`,`semester`),
    FOREIGN KEY(`kodeMataKuliah`, `kodeKelas`, `semester`) REFERENCES kelas(`kodeMataKuliah`,`kodeKelas`,`semester`)
);
CREATE TABLE IF NOT EXISTS kelompok(
    `namaTugasBesar` varchar(30),
    `kodeMataKuliah` varchar(10),
    `kodeKelas` varchar(3),
    `semester` int,
    `nomorKelompok` int NOT NULL,
    PRIMARY KEY(`namaTugasBesar`,`kodeMataKuliah`,`kodeKelas`,`semester`,`nomorKelompok`),
    FOREIGN KEY(`namaTugasBesar`,`kodeMataKuliah`, `kodeKelas`, `semester`) REFERENCES tugasBesar(`namaTugasBesar`,`kodeMataKuliah`,`kodeKelas`,`semester`)
);
CREATE TABLE IF NOT EXISTS anggotaKelompok(
    `namaTugasBesar` varchar(30),
    `kodeMataKuliah` varchar(10),
    `kodeKelas` varchar(3),
    `semester` int,
    `nomorKelompok` int NOT NULL,
    `npmPeserta` char(10),
    PRIMARY KEY(`namaTugasBesar`,`kodeMataKuliah`,`kodeKelas`,`semester`,`npmPeserta`),
    FOREIGN KEY(`npmPeserta`) REFERENCES mahasiswa(`npm`),
    -- FIX: Menambahkan nomorKelompok ke FK
    FOREIGN KEY(`namaTugasBesar`,`kodeMataKuliah`, `kodeKelas`, `semester`, `nomorKelompok`) REFERENCES kelompok(`namaTugasBesar`,`kodeMataKuliah`,`kodeKelas`,`semester`,`nomorKelompok`)
);
CREATE TABLE IF NOT EXISTS komponenPenilaian(
    `namaTugasBesar` varchar(30),
    `kodeMataKuliah` varchar(10),
    `kodeKelas` varchar(3),
    `semester` int,
    `nomorKomponen` int,
    `bobot` float,
    `rubrik` varchar(256),
    `tanggalPenilaian` date,
    `isHidden` TINYINT(1), -- FIX: Menggunakan TINYINT(1)
    PRIMARY KEY(`namaTugasBesar`,`kodeMataKuliah`,`kodeKelas`,`semester`,`nomorKomponen`),
    FOREIGN KEY(`namaTugasBesar`,`kodeMataKuliah`, `kodeKelas`, `semester`) REFERENCES tugasBesar(`namaTugasBesar`,`kodeMataKuliah`,`kodeKelas`,`semester`)
);
CREATE TABLE IF NOT EXISTS nilai(
    `namaTugasBesar` varchar(30),
    `kodeMataKuliah` varchar(10),
    `kodeKelas` varchar(3),
    `semester` int,
    `nomorKomponen` int,
    `nilai` float,
    `komentar` varchar(256),
    `npmPeserta` char(10),
    PRIMARY KEY(`namaTugasBesar`,`kodeMataKuliah`,`kodeKelas`,`semester`,`nomorKomponen`, `npmPeserta`),
    FOREIGN KEY(`npmPeserta`) REFERENCES mahasiswa(`npm`),
    FOREIGN KEY(`namaTugasBesar`,`kodeMataKuliah`, `kodeKelas`, `semester`,`nomorKomponen`) REFERENCES komponenPenilaian(`namaTugasBesar`,`kodeMataKuliah`,`kodeKelas`,`semester`,`nomorKomponen`)
);

SET FOREIGN_KEY_CHECKS = 1;
";

if ($conn->multi_query($sql_schema)) {
    // Memastikan semua hasil query (dari multi_query) dikosongkan
    do {
        if ($res = $conn->store_result()) {
            $res->free();
        }
    } while ($conn->more_results() && $conn->next_result());
    echo "<h2>✅ Skema Tabel Berhasil Dibuat Ulang (DROP & CREATE)</h2>";
} else {
    echo "<h2>❌ Error saat membuat skema tabel:</h2><pre>" . $conn->error . "</pre>";
    $conn->close();
    exit;
}

// ------------------------------------------
// 3. Skrip SQL: INSERT Data Dummy (FIXED: Nilai boolean 0/1)
// ------------------------------------------
$sql_data = "
-- DATA DUMMY
INSERT INTO pengguna VALUES
('user_mhs1', 'pass123', 1),
('user_mhs2', 'pass123', 1),
('user_dsn1', 'pass123', 2),
('user_dsn2', 'pass123', 2);

INSERT INTO mahasiswa VALUES
('2200000001', 'Budi Santoso', 'user_mhs1'),
('2200000002', 'Siti Aminah', 'user_mhs2');

INSERT INTO dosen VALUES
('1980010123456789', 'Dr. Andri Wijaya', 'user_dsn1'),
('1975122334567890', 'Prof. Rina Dewi', 'user_dsn2');

INSERT INTO mataKuliah VALUES
('IF101', 'Algoritma'),
('IF102', 'Basis Data');

INSERT INTO kelas VALUES
('IF101', 'A', 1),
('IF102', 'A', 1);

INSERT INTO pengampu VALUES
('1980010123456789', 'IF101', 'A', 1),
('1975122334567890', 'IF102', 'A', 1);

INSERT INTO peserta VALUES
('2200000001', 'IF101', 'A', 1),
('2200000002', 'IF101', 'A', 1),
('2200000001', 'IF102', 'A', 1),
('2200000002', 'IF102', 'A', 1);

-- FIX: Menggunakan 0 untuk FALSE pada kolom TINYINT(1) (isLocked)
INSERT INTO tugasBesar (namaTugasBesar, kodeMataKuliah, kodeKelas, semester, banyakAnggotaKelompok, isLocked) VALUES
('Project Algoritma', 'IF101', 'A', 1, 2, 0), 
('Project Basis Data', 'IF102', 'A', 1, 2, 0);

INSERT INTO kelompok VALUES
('Project Algoritma', 'IF101', 'A', 1, 1),
('Project Algoritma', 'IF101', 'A', 1, 2),
('Project Basis Data', 'IF102', 'A', 1, 1);

INSERT INTO anggotaKelompok VALUES
('Project Algoritma', 'IF101', 'A', 1, 1, '2200000001'),
('Project Algoritma', 'IF101', 'A', 1, 1, '2200000002'),
('Project Basis Data', 'IF102', 'A', 1, 1, '2200000001');

-- FIX: Menggunakan 0 untuk FALSE pada kolom TINYINT(1) (isHidden)
INSERT INTO komponenPenilaian (namaTugasBesar, kodeMataKuliah, kodeKelas, semester, nomorKomponen, bobot, rubrik, tanggalPenilaian, isHidden) VALUES
('Project Algoritma', 'IF101', 'A', 1, 1, 40, 'Kualitas Kode', '2025-01-15', 0), 
('Project Algoritma', 'IF101', 'A', 1, 2, 60, 'Laporan Akhir', '2025-01-20', 0), 
('Project Basis Data', 'IF102', 'A', 1, 1, 50, 'Desain ERD', '2025-01-14', 0);

INSERT INTO nilai VALUES
('Project Algoritma', 'IF101', 'A', 1, 1, 85, 'Baik', '2200000001'),
('Project Algoritma', 'IF101', 'A', 1, 1, 90, 'Sangat baik', '2200000002'),
('Project Algoritma', 'IF101', 'A', 1, 2, 88, 'Rapi dan lengkap', '2200000001'),
('Project Basis Data', 'IF102', 'A', 1, 1, 92, 'Desain sangat baik', '2200000001');
";

if ($conn->multi_query($sql_data)) {
    // Memastikan semua hasil query (dari multi_query) dikosongkan
    do {
        if ($res = $conn->store_result()) {
            $res->free();
        }
    } while ($conn->more_results() && $conn->next_result());
    echo "<h2>✅ Data Dummy Berhasil Dimasukkan (INSERT INTO)</h2>";
} else {
    echo "<h2>❌ Error saat memasukkan data dummy:</h2><pre>" . $conn->error . "</pre>";
}

// Tutup koneksi
$conn->close();
echo "<h2>✅ Proses Setup Database Selesai</h2>";
?>