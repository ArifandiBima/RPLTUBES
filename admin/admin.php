<?php
session_start();
include '../config/conn.php'; 

$role = $_SESSION['role'];
$username = $_SESSION['username']; // NIK Dosen

// Ambil Nama Lengkap Dosen
$nama_lengkap = $username;
if ($role == 2) {
    $q = mysqli_query($conn, "SELECT nama FROM dosen WHERE nik = '$username'");
    if ($row = mysqli_fetch_assoc($q)) $nama_lengkap = $row['nama'];
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
        <div id="btnBack"><a href="login.html" style="text-decoration:none;">⮌</a></div>
        <div id="container">    
            <div><button id="expExcel"><a href="convert.php">Export excel</a></button></div>
            <div id="subBtn">
                <div id="btnUser"><button><a href="kelola_user.php">Kelola Pengguna</a></button></div>
                <div id="btnSemester"><button><a href="kelola_semester.php">Kelola Semester</a></button></div>
            </div>
        </div>

 <?php elseif ($role == 2): ?>
        
        <?php
        // 1. AMBIL DATA DARI URL (SESSION PILIHAN SAAT INI)
        // Kita butuh data ini untuk memfilter database
        $pilih_tubes = $_GET['namaTugasBesar'] ?? '';
        $pilih_mk    = $_GET['kodeMataKuliah'] ?? '';
        $pilih_kls   = $_GET['kodeKelas'] ?? '';
        $pilih_sem   = $_GET['semester'] ?? '';

        // Validasi Sederhana: Jika tidak ada data di URL, suruh pilih dulu
        if(empty($pilih_tubes) || empty($pilih_mk)) {
            echo "<script>
                    alert('Silakan pilih Tugas Besar terlebih dahulu!');
                    window.location.href = 'tubesSelect.php'; // Atau halaman daftar kelas
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
                <p>Sedang Mengelola: <b><?= $pilih_tubes ?></b></p>
            </div>
            <a href="logout.php" class="btn-logout">Logout</a>
        </div>

        <div class="container">
            
            <?php
            // 2. QUERY SPESIFIK (HANYA 1 DATA)
            // Perhatikan bagian WHERE yang saya tambahkan filter AND
            $sql = "SELECT 
                        tb.namaTugasBesar, 
                        tb.kodeMataKuliah, 
                        mk.namaMataKuliah, 
                        tb.kodeKelas, 
                        tb.semester
                    FROM tugasBesar tb
                    JOIN mataKuliah mk ON tb.kodeMataKuliah = mk.kodeMataKuliah
                    JOIN pengampu p ON tb.kodeMataKuliah = p.kodeMataKuliah 
                                   AND tb.kodeKelas = p.kodeKelas 
                                   AND tb.semester = p.semester
                    WHERE p.nikPengampu = '$username'
                    AND tb.namaTugasBesar = '$pilih_tubes' 
                    AND tb.kodeMataKuliah = '$pilih_mk'
                    AND tb.kodeKelas = '$pilih_kls'
                    AND tb.semester = '$pilih_sem'";
            
            $result = mysqli_query($conn, $sql);
            
            // Cek apakah data ditemukan
            if (mysqli_num_rows($result) > 0) {
                // Ambil datanya (pasti cuma 1 baris)
                $row = mysqli_fetch_assoc($result);
                
                // Siapkan Parameter untuk tombol-tombol di bawah
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
                        <!-- <span class="matkul-code"><?= $row['kodeMataKuliah'] ?></span> -->
                    </div>

                    <div class="card-body">
                        <h2><?= $row['namaTugasBesar'] ?></h2>
                        <span class="card-info">Kelas <?= $row['kodeKelas'] ?> • Semester <?= $row['semester'] ?></span>
                    </div>

                    <div class="card-actions">
                        <a href="kelola_user.php?<?= $params ?>" class="btn-action btn-kelompok">Edit Kelompok</a>
                        <a href="rubrik.php?<?= $params ?>" class="btn-action btn-rubrik">Edit Rubrik</a>
                        <a href="nilai_dosen.php?<?= $params ?>" class="btn-action btn-nilai">Edit Nilai</a>
                    </div>

                </div>
                <div style="margin-top: 20px; text-align: center;">
                    <a href="tubesSelect.php?kodeMataKuliah=<?= $pilih_mk ?>&kodeKelas=<?= $pilih_kls ?>&semester=<?= $pilih_sem ?>&namaMataKuliah=<?= urlencode($row['namaMataKuliah']) ?>" 
                       style="color: #666; text-decoration: none; font-weight: bold;">
                       ⮌
                    </a>
                </div>

            <?php
            } else {
                echo '<p style="text-align:center; color:red;">Data Tugas Besar tidak ditemukan atau Anda tidak memiliki akses.</p>';
            }
            ?>
        </div>

    <?php endif; ?>
</body>
</html>