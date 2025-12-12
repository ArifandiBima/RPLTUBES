<?php
session_start();
include '../config/conn.php'; 

$role = $_SESSION['tipePengguna'];
$username = $_SESSION['username']; // NIK Dosen ATAU NPM Mahasiswa
if ($role ==2) $username = $_SESSION["nik"];
if ($role ==3) $username = $_SESSION["npm"];

// 2. AMBIL NAMA LENGKAP BERDASARKAN ROLE
$nama_lengkap = $username; // Default

if ($role == 2) {
    // Jika Dosen, ambil dari tabel dosen
    $q = mysqli_query($conn, "SELECT nama FROM dosen WHERE nik = '$username'");
    if ($row = mysqli_fetch_assoc($q)) $nama_lengkap = $row['nama'];
    $label_id = "NIK";

} elseif ($role == 3) {
    // Jika Mahasiswa, ambil dari tabel mahasiswa
    $q = mysqli_query($conn, "SELECT nama FROM mahasiswa WHERE npm = '$username'");
    if ($row = mysqli_fetch_assoc($q)) $nama_lengkap = $row['nama'];
    $label_id = "NPM";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <?php if ($role == 1): ?>
        <link href="../assets/admin.css?v=<?= time(); ?>" rel="stylesheet">
    <?php else: ?>
        <link href="../assets/dosen.css?v=<?= time(); ?>" rel="stylesheet">
    <?php endif; ?>
</head>

<body>

    <?php if ($role == 1): ?>
        <div id="btnBack"><a href="../index.php" style="text-decoration:none;">⮌</a></div>
        <div id="container">    
            <div><button id="expExcel"><a href="convert.php">Export excel</a></button></div>
            <div id="subBtn">
                <div id="btnUser"><button><a href="kelola_user.php">Kelola Pengguna</a></button></div>
                <div id="btnSemester"><button><a href="kelola_semester.php">Kelola Semester</a></button></div>
            </div>
        </div>

    <?php elseif ($role == 2 || $role == 3): ?>
        
        <?php
        // A. AMBIL DATA DARI URL
        $pilih_tubes = $_GET['namaTugasBesar'] ?? '';
        $pilih_mk    = $_GET['kodeMataKuliah'] ?? '';
        $pilih_kls   = $_GET['kodeKelas'] ?? '';
        $pilih_sem   = $_GET['semester'] ?? '';

        // Validasi: Jika URL kosong, lempar ke tubesSelect
        if(empty($pilih_tubes) || empty($pilih_mk)) {
            echo "<script>
                    alert('Silakan pilih Tugas Besar terlebih dahulu!');
                    window.location.href = '../matkul.php';
                  </script>";
            exit();
        }
        ?>

        <div class="profile-card">
            <div class="profile-icon">
                <?= strtoupper(substr($nama_lengkap, 0, 1)); ?>
            </div>
            <div class="profile-text">
                <h3>Halo, <?= $nama_lengkap; ?></h3>
                <p><?= $label_id ?>: <?= $username; ?></p>
                <p style="font-size: 12px; margin-top:5px; color:#2a7a38;">
                    Aktif di: <b><?= $pilih_tubes ?></b>
                </p>
            </div>
            <a href="../index.php" class="btn-logout">Logout</a>
        </div>

        <div class="container">
            
            <?php
            // B. LOGIKA QUERY SQL BERDASARKAN ROLE
            // Kita harus membedakan JOIN-nya agar data valid
            
            if ($role == 2) {
                // QUERY DOSEN: Cek tabel 'pengampu'
                $sql = "SELECT tb.*, mk.namaMataKuliah 
                        FROM tugasBesar tb
                        JOIN mataKuliah mk ON tb.kodeMataKuliah = mk.kodeMataKuliah
                        JOIN pengampu p ON tb.kodeMataKuliah = p.kodeMataKuliah 
                                       AND tb.kodeKelas = p.kodeKelas 
                                       AND tb.semester = p.semester
                        WHERE p.nikPengampu = '$username' ";
            } else {
                // QUERY MAHASISWA: Cek tabel 'peserta'
                $sql = "SELECT tb.*, mk.namaMataKuliah 
                        FROM tugasBesar tb
                        JOIN mataKuliah mk ON tb.kodeMataKuliah = mk.kodeMataKuliah
                        JOIN peserta p ON tb.kodeMataKuliah = p.kodeMataKuliah 
                                      AND tb.kodeKelas = p.kodeKelas 
                                      AND tb.semester = p.semester
                        WHERE p.npmPeserta = '$username' ";
            }

            // Lanjutan Filter (Sama untuk keduanya)
            $sql .= "AND tb.namaTugasBesar = '$pilih_tubes' 
                     AND tb.kodeMataKuliah = '$pilih_mk'
                     AND tb.kodeKelas = '$pilih_kls'
                     AND tb.semester = '$pilih_sem'";
            
            $result = mysqli_query($conn, $sql);
            
            // Cek Data
            if (mysqli_num_rows($result) > 0) {
                
                $row = mysqli_fetch_assoc($result);
                
            
                $params = http_build_query([
                    'kodeMataKuliah' => $row['kodeMataKuliah'],
                    'kodeKelas'      => $row['kodeKelas'],
                    'semester'       => $row['semester'],
                    'namaTugasBesar' => $row['namaTugasBesar']
                ]);
            ?>
                <div class="card">
                    
                    <div class="card-header">
                        <span class="matkul-name"><?= $row['namaMataKuliah'] ?></span>
                        <span class="matkul-code"><?= $row['kodeMataKuliah'] ?></span>
                    </div>

                    <div class="card-body">
                        <h2><?= $row['namaTugasBesar'] ?></h2>
                        <span class="card-info">Kelas <?= $row['kodeKelas'] ?> • Semester <?= $row['semester'] ?></span>
                    </div>

                    <div class="card-actions">
                        
                        <?php if ($role == 2): ?>
                            
                            <a href="../dosen_edit_kelompok_controller.php?<?= $params ?>" class="btn-action btn-kelompok">Edit Kelompok</a>
                            <a href="../rubrik.php?<?= $params ?>" class="btn-action btn-rubrik">Edit Rubrik</a>
                            <a href="../nilai_dosen.php?<?= $params ?>" class="btn-action btn-nilai">Edit Nilai</a>

                        <?php elseif ($role == 3): ?>

                            <a href="../mahasiswa_pilih_kelompok_controller.php?<?= $params ?>" class="btn-action btn-kelompok">
                                Lihat Kelompok
                            </a>

                            <a href="../nilai_mahasiswa.php?<?= $params ?>" class="btn-action btn-nilai">
                                Lihat Nilai
                            </a>

                        <?php endif; ?>

                    </div>

                </div>
                <div style="margin-top: 20px; text-align: center;">
                    <a href="../tubesSelect.php?kodeMataKuliah=<?= $pilih_mk ?>&kodeKelas=<?= $pilih_kls ?>&semester=<?= $pilih_sem ?>&namaMataKuliah=<?= urlencode($row['namaMataKuliah']) ?>" 
                       style="color: #666; text-decoration: none; font-weight: bold;">
                       ⮌
                    </a>
                </div>

            <?php
            } else {
                echo '<p style="text-align:center; color:red;">Data tidak ditemukan atau Anda tidak terdaftar di kelas ini.</p>';
            }
            ?>
        </div>

    <?php endif; ?>
</body>
</html>