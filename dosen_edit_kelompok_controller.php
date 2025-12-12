<?php
// 1. Koneksi Database
require 'conn.php';
session_start();

// 2. Input Dosen
// GANTI NILAI SESUAI DI DATABASE NANTI!
// $kode_mk = 'IF101'; 
// $kode_kelas = 'A';   
// $semester = 1;      
// $nama_tb = 'Project Algoritma'; 
// $nik_dosen = '1980010123456789'; // NIK Dosen yang sedang login

// // BARIS BARU (Mengambil nilai dari URL menggunakan GET)
// // Gunakan operator Null Coalescing (??) untuk nilai default agar tidak error jika parameter tidak ada.
$kode_mk = $_GET['kodeMataKuliah'] ?? ''; 
$kode_kelas = $_GET['kodeKelas'] ?? ''; 
// Khusus semester, tambahkan pengecekan isset dan cast ke integer (int)
$semester = isset($_GET['semester']) ? (int)$_GET['semester'] : 0;
$nama_tb = $_GET['namaTugasBesar'] ?? ''; 
$nik_dosen = $_SESSION['nik'] ?? ''; // NIK Dosen yang sedang login, diambil dari URL
$data = array(
    'namaMataKuliah' => 'Algoritma',
    'kodeMataKuliah' => 'IF101',
    'kodeKelas'   => 'A',
    'semester'       => 1,
    'namaTugasBesar' => "Project Algoritma"
);
// Inisialisasi variabel untuk menghindari error
$tugas_besar = [];
$data_kelompok = [];
$mahasiswa_belum_kelompok = [];
$message = ''; 
$notification_class = ''; 
$message_type = ''; // Untuk view

// Fungsi untuk label kelompok (1=A, 2=B, ...)
$kelompok_label = function($nomor) {
    return chr(64 + $nomor);
};

// 3. Ambil Data Tugas Besar & Konteks Dosen
$sql_select_tb = "
    SELECT 
        tb.namaTugasBesar AS nama,
        tb.kodeMataKuliah AS kodeMK,
        tb.kodeKelas AS kodeKelas,
        tb.semester AS semester,
        tb.banyakAnggotaKelompok AS max_anggota,
        tb.isLocked AS is_locked,
        mk.namaMataKuliah AS namaMK,
        dsn.nama AS namaDosen
    FROM 
        tugasBesar tb
    JOIN 
        mataKuliah mk ON tb.kodeMataKuliah = mk.kodeMataKuliah
    JOIN 
        pengampu pg ON tb.kodeMataKuliah = pg.kodeMataKuliah AND tb.kodeKelas = pg.kodeKelas AND tb.semester = pg.semester
    JOIN 
        dosen dsn ON pg.nikPengampu = dsn.nik
    WHERE 
        tb.namaTugasBesar = ? AND tb.kodeMataKuliah = ? AND tb.kodeKelas = ? AND tb.semester = ? AND pg.nikPengampu = ?;
";

$stmt_tb = $conn->prepare($sql_select_tb);

if ($stmt_tb === false) {
    die("Error preparing statement (TB): " . $conn->error);
}

$stmt_tb->bind_param("sssis", $nama_tb, $kode_mk, $kode_kelas, $semester, $nik_dosen);
$stmt_tb->execute();
$result_tb = $stmt_tb->get_result();

if ($result_tb->num_rows > 0) {
    $tugas_besar = $result_tb->fetch_assoc();
}
$stmt_tb->close();

// Pastikan tugas besar ditemukan sebelum lanjut
if (!empty($tugas_besar)) {
    // 4. Ambil Data Kelompok dan Anggota
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

    $data_kelompok_temp = [];
    $max_anggota = $tugas_besar['max_anggota'] ?? 1;

    // Re-struktur data kelompok
    while ($row = $result_k->fetch_assoc()) {
        $nomor = $row['nomorKelompok'];
        
        if (!isset($data_kelompok_temp[$nomor])) {
            $data_kelompok_temp[$nomor] = [
                'nomor' => $nomor,
                'anggota' => [],
                'kapasitas' => $max_anggota,
                'terisi' => 0
            ];
        }

        if ($row['npm'] !== null) {
            $data_kelompok_temp[$nomor]['anggota'][] = ['npm' => $row['npm'], 'nama' => $row['nama']];
            $data_kelompok_temp[$nomor]['terisi']++;
        }
    }
    $stmt_k->close();
    $data_kelompok = $data_kelompok_temp;

    // 5. Ambil Mahasiswa Belum Berkelompok
    $sql_belum = "
        SELECT 
            m.npm, m.nama 
        FROM 
            mahasiswa m
        JOIN 
            peserta p ON m.npm = p.npmPeserta
        LEFT JOIN 
            anggotaKelompok ak ON m.npm = ak.npmPeserta 
                                AND ak.kodeMataKuliah = p.kodeMataKuliah 
                                AND ak.kodeKelas = p.kodeKelas 
                                AND ak.semester = p.semester
                                AND ak.namaTugasBesar = ? -- Tambahkan filter namaTugasBesar
        WHERE 
            p.kodeMataKuliah = ? AND p.kodeKelas = ? AND p.semester = ? 
            AND ak.npmPeserta IS NULL
        ORDER BY
            m.npm;
    ";

    $stmt_b = $conn->prepare($sql_belum);
    $stmt_b->bind_param("sssi", $nama_tb, $kode_mk, $kode_kelas, $semester);
    $stmt_b->execute();
    $result_b = $stmt_b->get_result();

    while ($row = $result_b->fetch_assoc()) {
        $mahasiswa_belum_kelompok[] = $row;
    }
    $stmt_b->close();
}


// ACTION FORM
if ($_SERVER["REQUEST_METHOD"] === "POST" && !empty($tugas_besar)) {
    $action = $_POST['action'] ?? '';
    
    $conn->begin_transaction();
    $temp_message = '';

    try {
        if ($action === 'create_group') {
            // CREATE GROUP
            $sql_max_nomor = "SELECT MAX(nomorKelompok) AS max_nomor FROM kelompok WHERE namaTugasBesar = ? AND kodeMataKuliah = ? AND kodeKelas = ? AND semester = ?";
            $stmt_max = $conn->prepare($sql_max_nomor);
            $stmt_max->bind_param("sssi", $nama_tb, $kode_mk, $kode_kelas, $semester);
            $stmt_max->execute();
            $result_max = $stmt_max->get_result();
            $row_max = $result_max->fetch_assoc();
            $next_nomor = ($row_max['max_nomor'] ?? 0) + 1;
            $stmt_max->close();
            
            $sql_insert_kelompok = "INSERT INTO kelompok (namaTugasBesar, kodeMataKuliah, kodeKelas, semester, nomorKelompok) VALUES (?, ?, ?, ?, ?)";
            $stmt_insert = $conn->prepare($sql_insert_kelompok);
            $stmt_insert->bind_param("sssi", $nama_tb, $kode_mk, $kode_kelas, $semester, $next_nomor);
            $stmt_insert->execute();
            $stmt_insert->close();
            
            $temp_message = 'success:Kelompok ' . $kelompok_label($next_nomor) . ' berhasil ditambahkan.';

        } elseif ($action === 'remove_member') {
            // REMOVE MEMBER
            $npm_target = $_POST['npm'] ?? '';
            
            $sql_delete = "
                DELETE FROM anggotaKelompok 
                WHERE npmPeserta = ? AND namaTugasBesar = ? AND kodeMataKuliah = ? AND kodeKelas = ? AND semester = ?
            ";
            $stmt_delete = $conn->prepare($sql_delete);
            $stmt_delete->bind_param("ssssi", $npm_target, $nama_tb, $kode_mk, $kode_kelas, $semester);
            $stmt_delete->execute();
            $stmt_delete->close();
            
            $temp_message = 'success:Mahasiswa ' . $npm_target . ' berhasil dikeluarkan dari kelompok.';

        } elseif ($action === 'manual_insert') {
            // MANUAL INSERT
            $npm_target = $_POST['npm_target'] ?? '';
            $kelompok_target = (int)($_POST['kelompok_target'] ?? 0); 
            
            if (empty($npm_target) || $kelompok_target == 0) {
                $temp_message = 'error:Pilih mahasiswa dan kelompok tujuan.';
            } else {
                // 1. Cek apakah kelompok sudah penuh
                $is_full = ($data_kelompok[$kelompok_target]['terisi'] ?? 0) >= ($tugas_besar['max_anggota'] ?? 1);
                
                if ($is_full) {
                    $temp_message = 'error:Kelompok ' . $kelompok_label($kelompok_target) . ' sudah penuh. Gagal memasukkan.';
                } else {
                    // 2. Hapus dari kelompok lama (jika ada)
                    $sql_delete_old = "
                        DELETE FROM anggotaKelompok 
                        WHERE npmPeserta = ? AND namaTugasBesar = ? AND kodeMataKuliah = ? AND kodeKelas = ? AND semester = ?
                    ";
                    $stmt_delete_old = $conn->prepare($sql_delete_old);
                    $stmt_delete_old->bind_param("ssssi", $npm_target, $nama_tb, $kode_mk, $kode_kelas, $semester);
                    $stmt_delete_old->execute();
                    $stmt_delete_old->close();
                    
                    // 3. Masukkan ke kelompok baru
                    $sql_insert = "
                        INSERT INTO anggotaKelompok (namaTugasBesar, kodeMataKuliah, kodeKelas, semester, nomorKelompok, npmPeserta) 
                        VALUES (?, ?, ?, ?, ?, ?)
                    ";
                    $stmt_insert = $conn->prepare($sql_insert);
                    $stmt_insert->bind_param("sssiis", $nama_tb, $kode_mk, $kode_kelas, $semester, $kelompok_target, $npm_target);
                    $stmt_insert->execute();
                    $stmt_insert->close();

                    $temp_message = 'success:Mahasiswa ' . $npm_target . ' berhasil dimasukkan ke Kelompok ' . $kelompok_label($kelompok_target) . '.';
                }
            }

        } elseif ($action === 'toggle_lock') {
            // LOCK/UNLOCK TUGAS BESAR
            $current_lock_status = $tugas_besar['is_locked'] ?? 0;
            $new_lock_status = 1 - $current_lock_status; 
            
            // SET LOCK/NOT LOCKED
            $sql_update_lock = "UPDATE tugasBesar SET isLocked = ? WHERE namaTugasBesar = ? AND kodeMataKuliah = ? AND kodeKelas = ? AND semester = ?";
            $stmt_update = $conn->prepare($sql_update_lock);
            
            $stmt_update->bind_param("isssi", $new_lock_status, $nama_tb, $kode_mk, $kode_kelas, $semester);
            $stmt_update->execute();
            $stmt_update->close();
            
            $action_text = ($new_lock_status == 1) ? 'Terkunci' : 'Tidak Terkunci';
            $temp_message = 'success:Status pemilihan kelompok berhasil diubah menjadi: ' . $action_text . '.';

        } elseif ($action === 'auto_fill') {
            // AUTO FILL
            $mahasiswa_yang_dimasukkan = [];        
            // Urutkan kelompok berdasarkan sisa slot
            $kelompok_sorted = $data_kelompok;
            usort($kelompok_sorted, function($a, $b) {
                $sisa_a = $a['kapasitas'] - $a['terisi'];
                $sisa_b = $b['kapasitas'] - $b['terisi'];
                // Urutkan dari slot terbanyak ke tersedikit
                return $sisa_b - $sisa_a; 
            });
            
            $idx_mhs = 0;
            
            foreach ($kelompok_sorted as $kelompok) {
                $nomor = $kelompok['nomor'];
                $sisa_slot = $kelompok['kapasitas'] - $kelompok['terisi'];
                
                if ($sisa_slot <= 0) continue;

                // MEMASUKAN MAHASISWA KE KELOMPOK 
                for ($i = 0; $i < $sisa_slot; $i++) {
                    if (isset($mahasiswa_belum_kelompok[$idx_mhs])) {
                        $mhs_target = $mahasiswa_belum_kelompok[$idx_mhs];
                        $npm_target = $mhs_target['npm'];
                        
                        // Masukkan ke kelompok
                        $sql_insert = "
                            INSERT INTO anggotaKelompok (namaTugasBesar, kodeMataKuliah, kodeKelas, semester, nomorKelompok, npmPeserta) 
                            VALUES (?, ?, ?, ?, ?, ?)
                        ";
                        $stmt_insert = $conn->prepare($sql_insert);
                        $stmt_insert->bind_param("sssiis", $nama_tb, $kode_mk, $kode_kelas, $semester, $nomor, $npm_target);
                        $stmt_insert->execute();
                        $stmt_insert->close();

                        $mahasiswa_yang_dimasukkan[] = $npm_target;
                        $idx_mhs++;
                    } else {
                        // Mahasiswa belum berkelompok habis
                        break 2;
                    }
                }
            }

            if (empty($mahasiswa_yang_dimasukkan)) {
                $temp_message = 'info:Semua kelompok sudah penuh atau tidak ada mahasiswa yang belum berkelompok.';
            } else {
                $count = count($mahasiswa_yang_dimasukkan);
                $temp_message = 'success:' . $count . ' mahasiswa berhasil dimasukkan secara otomatis ke kelompok yang tersedia.';
            }
            
        } 
        
        $conn->commit();
        
    } catch (mysqli_sql_exception $e) {
        $conn->rollback();
        $temp_message = 'error:Gagal saat menjalankan aksi: ' . $e->getMessage();
    }
    
    // Redirect dengan pesan
    header("Location: " . $_SERVER['PHP_SELF'] . "?" . http_build_query($data));    exit;
}


// Tutup koneksi sebelum menampilkan view
$conn->close();


// MENGAMBIL PESAN DARI URL
if (isset($_GET['msg'])) {
    $get_message = htmlspecialchars(urldecode($_GET['msg']));
    
    // Memecah prefix 'success:' atau 'error:'
    $parts = explode(':', $get_message, 2);
    $type = $parts[0] ?? '';
    
    if (count($parts) > 1 && ($type === 'success' || $type === 'error' || $type === 'info')) {
        $message = $parts[1];
        $message_type = $type;
    } else {
        $message = $get_message; 
        $message_type = 'info'; 
    }
}


// 6. Tampilkan View (HTML)
require 'dosen_edit_kelompok_view.php'; 
?>