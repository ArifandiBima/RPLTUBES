<?php
// 1. Koneksi Database
require 'conn.php';
session_start();

// 2. Definisi Konteks (Simulasi Input Mahasiswa/URL Parameter)
// ------------------------------------------
// **HARUS SESUAI DENGAN DATA DUMMY DI setup_db.php**
// $kode_mk = 'IF101';           
// $kode_kelas = 'A';            
// $semester = 1;                
// $nama_tb = 'Project Algoritma'; 
// $npm_mahasiswa = '2200000002'; // <-- PERUBAHAN: Menggunakan NPM Siti Aminah

$kode_mk = $_GET['kodeMataKuliah'] ?? '';
$kode_kelas = $_GET['kodeKelas'] ?? '';
// Khusus semester, pastikan di-cast ke integer
$semester = isset($_GET['semester']) ? (int)$_GET['semester'] : 0;
$nama_tb = $_GET['namaTugasBesar'] ?? '';
$npm_mahasiswa = $_SESSION['npm'] ?? '';

$data = array(
    'namaMataKuliah' => 'Algoritma',
    'kodeMataKuliah' => 'IF101',
    'kodeKelas'   => 'A',
    'semester'       => 1,
    'namaTugasBesar' => "Project Algoritma"

);
// Inisialisasi variabel
$tugas_besar = [];
$data_kelompok = [];
$kelompok_mahasiswa_saat_ini = null; 
$message = ''; 
$notification_class = ''; 
$nama_mahasiswa = 'Mahasiswa'; 

// Fungsi utilitas untuk label kelompok (1=A, 2=B, ...)
$kelompok_label = function($nomor) {
    return chr(64 + $nomor);
};


// ------------------------------------------
// 3. Ambil Data Mahasiswa (Nama Mahasiswa yang sedang login)
// ------------------------------------------
$sql_select_mhs = "SELECT nama FROM mahasiswa WHERE npm = ?";
$stmt_mhs = $conn->prepare($sql_select_mhs);
if ($stmt_mhs) {
    $stmt_mhs->bind_param("s", $npm_mahasiswa);
    $stmt_mhs->execute();
    $result_mhs = $stmt_mhs->get_result();
    if ($row = $result_mhs->fetch_assoc()) {
        $nama_mahasiswa = $row['nama'];
    }
    $stmt_mhs->close();
}


// ------------------------------------------
// 4. Ambil Data Tugas Besar
// ------------------------------------------
$sql_select_tb = "
    SELECT 
        tb.namaTugasBesar AS nama,
        tb.kodeMataKuliah AS kodeMK,
        tb.kodeKelas AS kodeKelas,
        tb.semester AS semester,
        tb.banyakAnggotaKelompok AS max_anggota,
        tb.isLocked AS is_locked,
        mk.namaMataKuliah AS namaMK
    FROM 
        tugasBesar tb
    JOIN 
        mataKuliah mk ON tb.kodeMataKuliah = mk.kodeMataKuliah
    WHERE 
        tb.namaTugasBesar = ? AND tb.kodeMataKuliah = ? AND tb.kodeKelas = ? AND tb.semester = ?;
";

$stmt_tb = $conn->prepare($sql_select_tb);

if ($stmt_tb === false) {
    die("Error preparing statement (TB): " . $conn->error);
}

// Urutan tipe data: namaTugasBesar(s), kodeMataKuliah(s), kodeKelas(s), semester(i)
$stmt_tb->bind_param("sssi", $nama_tb, $kode_mk, $kode_kelas, $semester);
$stmt_tb->execute();
$result_tb = $stmt_tb->get_result();

if ($result_tb->num_rows > 0) {
    $tugas_besar = $result_tb->fetch_assoc();
}
$stmt_tb->close();

if (empty($tugas_besar)) {
    // Jika Tugas Besar tidak ditemukan, hentikan query data kelompok.
    // View akan menampilkan "Data Tugas Besar Tidak Ditemukan"
} else {
    // 5. Cek Kelompok Mahasiswa Saat Ini
    $sql_cek_kelompok = "
        SELECT nomorKelompok 
        FROM anggotaKelompok
        WHERE npmPeserta = ? AND namaTugasBesar = ? AND kodeMataKuliah = ? AND kodeKelas = ? AND semester = ?
    ";
    $stmt_cek = $conn->prepare($sql_cek_kelompok);
    $stmt_cek->bind_param("sssis", $npm_mahasiswa, $nama_tb, $kode_mk, $kode_kelas, $semester);
    $stmt_cek->execute();
    $result_cek = $stmt_cek->get_result();

    if ($row = $result_cek->fetch_assoc()) {
        $kelompok_mahasiswa_saat_ini = $row['nomorKelompok'];
    }
    $stmt_cek->close();

    // 6. Ambil Data Semua Kelompok dan Anggota
    $sql_kelompok = "
        SELECT 
            k.nomorKelompok,
            m.npm,
            m.nama
        FROM 
            kelompok k
        LEFT JOIN 
            anggotaKelompok ak ON k.namaTugasBesar = ak.namaTugasBesar AND k.kodeMataKuliah = ak.kodeMataKuliah AND k.kodeKelas = ak.kodeKelas AND k.semester = ak.semester AND k.nomorKelompok = ak.nomorKelompok
        LEFT JOIN
            mahasiswa m ON ak.npmPeserta = m.npm
        WHERE 
            k.namaTugasBesar = ? AND k.kodeMataKuliah = ? AND k.kodeKelas = ? AND k.semester = ?
        ORDER BY
            k.nomorKelompok, m.npm;
    ";
    
    $stmt_k = $conn->prepare($sql_kelompok);
    $stmt_k->bind_param("sssi", $nama_tb, $kode_mk, $kode_kelas, $semester);
    $stmt_k->execute();
    $result_k = $stmt_k->get_result();

    // Re-struktur data kelompok
    while ($row = $result_k->fetch_assoc()) {
        $nomor = $row['nomorKelompok'];
        
        // Inisialisasi kelompok jika belum ada
        if (!isset($data_kelompok[$nomor])) {
            $data_kelompok[$nomor] = [
                'nomor' => $nomor,
                'anggota' => [],
                'kapasitas' => $tugas_besar['max_anggota'],
                'terisi' => 0
            ];
        }

        // Tambahkan anggota jika ada
        if ($row['npm'] !== null) {
            $data_kelompok[$nomor]['anggota'][] = ['npm' => $row['npm'], 'nama' => $row['nama']];
            $data_kelompok[$nomor]['terisi']++;
        }
    }
    $stmt_k->close();

}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === 'join_group') {
    $target_kelompok = (int)($_POST['nomor_kelompok'] ?? 0);
    
    if ($target_kelompok > 0 && isset($data_kelompok[$target_kelompok])) {
        
        $is_full = ($data_kelompok[$target_kelompok]['terisi'] ?? 0) >= ($tugas_besar['max_anggota'] ?? 1);
        $is_locked = $tugas_besar['is_locked'] ?? 0;
        
        if ($is_full) {
            $message = "Gagal: Kelompok " . $kelompok_label($target_kelompok) . " sudah penuh.";
        } elseif ($is_locked) {
            $message = "Gagal: Pemilihan kelompok sedang dikunci oleh dosen.";
        } else {
            // Proses gabung atau pindah kelompok
            $conn->begin_transaction();
            try {
                // 1. Hapus dari kelompok lama (jika ada)
                $sql_delete = "
                    DELETE FROM anggotaKelompok 
                    WHERE npmPeserta = ? AND namaTugasBesar = ? AND kodeMataKuliah = ? AND kodeKelas = ? AND semester = ?
                ";
                $stmt_delete = $conn->prepare($sql_delete);
                $stmt_delete->bind_param("sssis", $npm_mahasiswa, $nama_tb, $kode_mk, $kode_kelas, $semester);
                $stmt_delete->execute();
                $stmt_delete->close();
                
                // 2. Masukkan ke kelompok baru
                $sql_insert = "
                    INSERT INTO anggotaKelompok (namaTugasBesar, kodeMataKuliah, kodeKelas, semester, nomorKelompok, npmPeserta) 
                    VALUES (?, ?, ?, ?, ?, ?)";
                $stmt_insert = $conn->prepare($sql_insert);
                // Urutan: namaTugasBesar(s), kodeMataKuliah(s), kodeKelas(s), semester(i), nomorKelompok(i), npmPeserta(s)
                $stmt_insert->bind_param("sssiis", $nama_tb, $kode_mk, $kode_kelas, $semester, $target_kelompok, $npm_mahasiswa);
                $stmt_insert->execute();
                $stmt_insert->close();

                $conn->commit();
                $message_text = $kelompok_mahasiswa_saat_ini ? 'pindah' : 'bergabung';
                $message = "success:Anda telah berhasil $message_text ke Kelompok " . $kelompok_label($target_kelompok) . ".";
                
                // Setelah sukses, reload untuk update tampilan
                header("Location: " . $_SERVER['PHP_SELF'] . "?" . http_build_query($data)); exit;
                exit;

            } catch (mysqli_sql_exception $exception) {
                $conn->rollback();
                $message = "error:Terjadi error database. " . $exception->getMessage();
            }
        }
    } else {
        $message = "error:Kelompok target tidak valid.";
    }
}


// Tutup koneksi sebelum menampilkan view
$conn->close();


// ************************************************
// LOGIKA UNTUK MENGAMBIL PESAN DARI URL
// ************************************************
$message = $message ?? ''; 
if (isset($_GET['msg'])) {
    $get_message = htmlspecialchars(urldecode($_GET['msg']));
    
    // Hapus prefix 'success:' atau 'error:' jika ada
    $parts = explode(':', $get_message, 2);
    $type = $parts[0] ?? '';
    if (count($parts) > 1 && ($type === 'success' || $type === 'error')) {
        $message = $parts[1];
        
        // Atur kelas notifikasi
        if ($type === 'success') {
            $notification_class = 'success';
        } elseif ($type === 'error') {
            $notification_class = 'error';
        }
    } else {
        $message = $get_message; // Jika tidak ada prefix
        $notification_class = 'info'; // Default
    }
}
require 'mahasiswa_pilih_kelompok_view.php';
?>