<?php
// FILE: dosen_edit_kelompok_controller.php

// ------------------------------------------
// 1. Konfigurasi Koneksi Database (Ganti dengan detail Anda!)
// ------------------------------------------
$servername = "localhost";
$username = "root"; 
$password = "";     
$dbname = "rpl_test"; // Asumsi nama database Anda

// Membuat koneksi
$conn = new mysqli($servername, $username, $password, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    // Tambahkan die() agar skrip berhenti jika koneksi gagal
    die("Koneksi gagal: " . $conn->connect_error);
}

// ------------------------------------------
// 2. Definisi Konteks Tugas Besar (Simulasi Input Dosen/URL Parameter)
// ------------------------------------------
// GANTI NILAI INI SESUAI DATA YANG ADA DI DATABASE ANDA
$kode_mk = 'RPL101'; 
$kode_kelas = 'A';   
$semester = 20251;   
$nama_tb = 'Aplikasi Inventory'; 
$nik_dosen = '20010001'; // NIK Dosen yang sedang login

// Inisialisasi variabel untuk menghindari error
$tugas_besar = [];
$data_kelompok = [];
$mahasiswa_belum_kelompok = [];

// Fungsi utilitas untuk label kelompok (1=A, 2=B, ...)
$kelompok_label = function($nomor) {
    return chr(64 + $nomor); // ASCII 65 = A
};


// ------------------------------------------
// 3. Mengambil Data Tugas Besar (Untuk Header dan Kapasitas)
// ------------------------------------------
$sql_tb = "
    SELECT 
        tb.namaTugasBesar AS nama,
        tb.kodeMataKuliah AS kodeMK,
        mk.namaMataKuliah AS namaMK,
        tb.kodeKelas AS kelas,
        tb.semester,
        tb.banyakAnggotaKelompok AS maks_anggota,
        -- Simulasikan mode_pemilihan dan is_locked karena tidak ada di skema
        'mandiri' AS mode_pemilihan,
        0 AS is_locked 
    FROM tugasBesar tb
    JOIN mataKuliah mk ON tb.kodeMataKuliah = mk.kodeMataKuliah
    WHERE tb.kodeMataKuliah = ? AND tb.kodeKelas = ? AND tb.semester = ? AND tb.namaTugasBesar = ?";

if ($stmt = $conn->prepare($sql_tb)) {
    // 4 variabel: s, s, i, s
    $stmt->bind_param("ssis", $kode_mk, $kode_kelas, $semester, $nama_tb);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $tugas_besar = $row;
    }
    $stmt->close();
}


// ------------------------------------------
// 4. Mengambil Data Kelompok dan Anggotanya
// ------------------------------------------

// Query 1: Ambil semua kelompok dan kapasitasnya
$sql_kelompok = "
    SELECT 
        k.nomorKelompok,
        tb.banyakAnggotaKelompok AS kapasitas
    FROM kelompok k
    JOIN tugasBesar tb ON k.namaTugasBesar = tb.namaTugasBesar 
        AND k.kodeMataKuliah = tb.kodeMataKuliah 
        AND k.kodeKelas = tb.kodeKelas 
        AND k.semester = tb.semester
    WHERE k.kodeMataKuliah = ? AND k.kodeKelas = ? AND k.semester = ? AND k.namaTugasBesar = ?
    ORDER BY k.nomorKelompok";
    
if ($stmt = $conn->prepare($sql_kelompok)) {
    // 4 variabel: s, s, i, s
    $stmt->bind_param("ssis", $kode_mk, $kode_kelas, $semester, $nama_tb);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Inisialisasi struktur $data_kelompok
    while ($row = $result->fetch_assoc()) {
        $data_kelompok[$row['nomorKelompok']] = [
            'nama' => 'Kelompok ' . $kelompok_label($row['nomorKelompok']),
            'kapasitas' => $row['kapasitas'],
            'anggota' => []
        ];
    }
    $stmt->close();
}


// Query 2: Ambil semua anggota kelompok
$sql_anggota = "
    SELECT 
        ak.nomorKelompok,
        ak.npmPeserta AS npm,
        m.nama
    FROM anggotaKelompok ak
    JOIN mahasiswa m ON ak.npmPeserta = m.npm
    WHERE ak.kodeMataKuliah = ? AND ak.kodeKelas = ? AND ak.semester = ? AND ak.namaTugasBesar = ?";
    
if ($stmt = $conn->prepare($sql_anggota)) {
    // 4 variabel: s, s, i, s
    $stmt->bind_param("ssis", $kode_mk, $kode_kelas, $semester, $nama_tb);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Masukkan anggota ke dalam kelompok yang sudah diinisialisasi
    while ($row = $result->fetch_assoc()) {
        $nomor = $row['nomorKelompok'];
        if (isset($data_kelompok[$nomor])) {
            $data_kelompok[$nomor]['anggota'][] = ['npm' => $row['npm'], 'nama' => $row['nama']];
        }
    }
    $stmt->close();
}


// ------------------------------------------
// 5. Mengambil Mahasiswa Belum Berkelompok (FIXED BIND PARAM)
// ------------------------------------------
$sql_belum_kelompok = "
    SELECT 
        m.npm, 
        m.nama 
    FROM mahasiswa m
    -- Pastikan mahasiswa adalah peserta di kelas ini
    JOIN peserta p ON m.npm = p.npmPeserta
    WHERE p.kodeMataKuliah = ? AND p.kodeKelas = ? AND p.semester = ?
    -- Dan mahasiswa ini TIDAK ada di anggotaKelompok untuk tugas besar ini
    AND m.npm NOT IN (
        SELECT npmPeserta 
        FROM anggotaKelompok
        WHERE kodeMataKuliah = ? AND kodeKelas = ? AND semester = ? AND namaTugasBesar = ?
    )";

if ($stmt = $conn->prepare($sql_belum_kelompok)) {
    // Perbaikan: 7 placeholder (3 di atas, 4 di sub-query).
    // Tipe: s (mk), s (kelas), i (semester) | s (mk), s (kelas), i (semester), s (nama_tb)
    // Bind string yang benar: "ssissis" (7 karakter)
    $stmt->bind_param("ssissis", $kode_mk, $kode_kelas, $semester, $kode_mk, $kode_kelas, $semester, $nama_tb);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $mahasiswa_belum_kelompok[] = ['npm' => $row['npm'], 'nama' => $row['nama']];
    }
    $stmt->close();
}

// ------------------------------------------
// 6. Logika Pemrosesan Form (Jika ada POST) - Simulasi
// ------------------------------------------
// Anda perlu menambahkan fungsi database di sini untuk INSERT/UPDATE/DELETE.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['insert_manual']) && !empty($_POST['npm_target']) && !empty($_POST['kelompok_target'])) {
        $npm = $_POST['npm_target'];
        $kelompok = $_POST['kelompok_target'];
        
        // ** Tempatkan logika DB INSERT ke tabel anggotaKelompok di sini **
        // Contoh: $sql_insert = "INSERT INTO anggotaKelompok (namaTugasBesar, kodeMataKuliah, kodeKelas, semester, nomorKelompok, npmPeserta) VALUES (?, ?, ?, ?, ?, ?)";
        
        echo "<script>alert('Simulasi: Mahasiswa {$npm} dimasukkan ke Kelompok {$kelompok}. [PERLU IMPLEMENTASI DB]');</script>";
        // Refresh data setelah aksi (perlu redirect atau reload data fetch)
        header("Location: " . $_SERVER['PHP_SELF']); 
        exit;
    } 
    // ... Tambahkan logika untuk auto_fill dan lock_team
    
    if (isset($_POST['action']) && $_POST['action'] === 'remove_member') {
        $npm = $_POST['npm'];
        // ** Tempatkan logika DB DELETE dari tabel anggotaKelompok di sini **
        // Contoh: $sql_delete = "DELETE FROM anggotaKelompok WHERE npmPeserta = ? AND kodeMataKuliah = ? AND kodeKelas = ? AND semester = ? AND namaTugasBesar = ?";
        
        echo "<script>alert('Simulasi: Mahasiswa {$npm} dikeluarkan dari kelompok. [PERLU IMPLEMENTASI DB]');</script>";
        header("Location: " . $_SERVER['PHP_SELF']); 
        exit;
    }
}

// Tutup koneksi sebelum menampilkan view
$conn->close();

// Tampilkan View
include 'dosen_edit_kelompok_view.php'; 
?>