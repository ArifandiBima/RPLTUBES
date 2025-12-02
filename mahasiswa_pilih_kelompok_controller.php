<?php
// FILE: mahasiswa_pilih_kelompok_controller.php

// ------------------------------------------
// 1. Konfigurasi Koneksi Database
// ------------------------------------------
$servername = "localhost";
$username = "root"; 
$password = "";     
$dbname = "rpl_test"; 

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// ------------------------------------------
// 2. Definisi Konteks (Simulasi Input Mahasiswa/URL Parameter)
// ------------------------------------------
$kode_mk = 'RPL101'; 
$kode_kelas = 'A';   
$semester = 20251;   
$nama_tb = 'Aplikasi Inventory'; 
$npm_mahasiswa = '19010006'; // **SIMULASI: Mahasiswa yang sedang login (npm 19010006 belum berkelompok)**

// Inisialisasi variabel
$tugas_besar = [];
$data_kelompok = [];
$kelompok_mahasiswa_saat_ini = null; // Nomor kelompok Mahasiswa saat ini
$message = ''; // Untuk notifikasi setelah aksi

// Fungsi utilitas untuk label kelompok (1=A, 2=B, ...)
$kelompok_label = function($nomor) {
    return chr(64 + $nomor);
};


// ------------------------------------------
// 3. Mengambil Data Tugas Besar (Termasuk Status Kunci)
// ------------------------------------------
$sql_tb = "
    SELECT 
        tb.namaTugasBesar AS nama,
        tb.kodeMataKuliah AS kodeMK,
        mk.namaMataKuliah AS namaMK,
        tb.kodeKelas AS kelas,
        tb.semester,
        tb.banyakAnggotaKelompok AS maks_anggota,
        'mandiri' AS mode_pemilihan,
        -- Asumsi kolom is_locked ada di tabel tugasBesar, jika tidak ada, gunakan 0
        0 AS is_locked 
    FROM tugasBesar tb
    JOIN mataKuliah mk ON tb.kodeMataKuliah = mk.kodeMataKuliah
    WHERE tb.kodeMataKuliah = ? AND tb.kodeKelas = ? AND tb.semester = ? AND tb.namaTugasBesar = ?";

if ($stmt = $conn->prepare($sql_tb)) {
    $stmt->bind_param("ssis", $kode_mk, $kode_kelas, $semester, $nama_tb);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $tugas_besar = $row;
    }
    $stmt->close();
}


// ------------------------------------------
// 4. Mengambil Data Kelompok, Anggota, dan Kelompok Mahasiswa Saat Ini
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
    $stmt->bind_param("ssis", $kode_mk, $kode_kelas, $semester, $nama_tb);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $nomor = $row['nomorKelompok'];
        if (isset($data_kelompok[$nomor])) {
            $data_kelompok[$nomor]['anggota'][] = ['npm' => $row['npm'], 'nama' => $row['nama']];
            // Cek apakah mahasiswa yang sedang login ada di kelompok ini
            if ($row['npm'] === $npm_mahasiswa) {
                $kelompok_mahasiswa_saat_ini = $nomor;
            }
        }
    }
    $stmt->close();
}


// ------------------------------------------
// 5. Logika Pemrosesan Form (JOIN/MOVE)
// ------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    $action = $_POST['action'];
    $target_kelompok = $_POST['nomor_kelompok'] ?? null;
    $is_locked = $tugas_besar['is_locked'] ?? 0;
    
    // Cek apakah pemilihan kelompok terkunci
    if ($is_locked) {
        $message = "Gagal: Pemilihan kelompok saat ini sedang dikunci oleh dosen.";
    } 
    // Cek kelompok target valid dan tidak penuh
    elseif ($target_kelompok && isset($data_kelompok[$target_kelompok])) {
        $kelompok = $data_kelompok[$target_kelompok];
        $terisi = count($kelompok['anggota']);
        $kapasitas = $kelompok['kapasitas'] ?? 0;
        
        if ($kapasitas > 0 && $terisi >= $kapasitas) {
            $message = "Gagal: Kelompok " . $kelompok_label($target_kelompok) . " sudah penuh.";
        } else {
            $conn->begin_transaction();
            try {
                // 1. Hapus (Jika sudah ada di kelompok lain)
                $sql_delete = "
                    DELETE FROM anggotaKelompok 
                    WHERE npmPeserta = ? AND kodeMataKuliah = ? AND kodeKelas = ? AND semester = ? AND namaTugasBesar = ?";
                $stmt_delete = $conn->prepare($sql_delete);
                $stmt_delete->bind_param("sssis", $npm_mahasiswa, $kode_mk, $kode_kelas, $semester, $nama_tb);
                $stmt_delete->execute();
                $stmt_delete->close();

                // 2. Masukkan ke kelompok baru
                $sql_insert = "
                    INSERT INTO anggotaKelompok (namaTugasBesar, kodeMataKuliah, kodeKelas, semester, nomorKelompok, npmPeserta) 
                    VALUES (?, ?, ?, ?, ?, ?)";
                $stmt_insert = $conn->prepare($sql_insert);
                $stmt_insert->bind_param("ssisis", $nama_tb, $kode_mk, $kode_kelas, $semester, $target_kelompok, $npm_mahasiswa);
                $stmt_insert->execute();
                $stmt_insert->close();

                $conn->commit();
                $message = "Sukses: Anda telah berhasil bergabung ke Kelompok " . $kelompok_label($target_kelompok) . ".";
                
                // Setelah sukses, reload untuk update tampilan
                header("Location: " . $_SERVER['PHP_SELF'] . "?msg=" . urlencode($message));
                exit;

            } catch (mysqli_sql_exception $exception) {
                $conn->rollback();
                $message = "Gagal: Terjadi error database. " . $exception->getMessage();
            }
        }
    } else {
        $message = "Gagal: Kelompok target tidak valid.";
    }
} elseif (isset($_GET['msg'])) {
    $message = htmlspecialchars($_GET['msg']);
}

// Tutup koneksi sebelum menampilkan view
$conn->close();

// Tampilkan View
include 'mahasiswa_pilih_kelompok_view.php'; 
?>