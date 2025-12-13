<?php
session_start();
include '../config/conn.php'; // Pastikan path ini benar

// LOGIKA UPLOAD & IMPORT
if(isset($_POST['send'])){
    
    if(isset($_FILES['myDocument']) && $_FILES['myDocument']['size'] > 0){
        
        $file = fopen($_FILES['myDocument']['tmp_name'], "r");
        
        // --- DETEKSI OTOMATIS PEMISAH (KOMA atau TITIK KOMA) ---
        $firstLine = fgets($file);
        rewind($file); 
        $separator = (strpos($firstLine, ';') !== false) ? ";" : ",";
        
        // Skip Header (Baris Judul)
        fgetcsv($file, 1000, $separator);
        
        $berhasil = 0;

        while (($data = fgetcsv($file, 1000, $separator)) !== FALSE) {
            
            // Validasi: Pastikan kolom cukup
            if(count($data) < 8) continue;

            // Ambil & Rapikan Data (Trim & Substr)
            $kodeMK     = substr(trim($data[0]), 0, 10);  
            $namaMK     = substr(trim($data[1]), 0, 30);  
            $kelas      = substr(trim($data[2]), 0, 3);   
            $semester   = (int)trim($data[3]);            
            $namaMhs    = substr(trim($data[4]), 0, 30);  
            $npmMhs     = substr(trim($data[5]), 0, 10);  
            $namaDosen  = substr(trim($data[6]), 0, 30);  
            $nikDosen   = substr(trim($data[7]), 0, 20);  

            // Pastikan data kunci tidak kosong
            if(!empty($kodeMK) && !empty($npmMhs) && !empty($nikDosen)){
                
                // 1. INPUT DOSEN (User & Profil)
                mysqli_query($conn, "INSERT IGNORE INTO pengguna (username, pass, tipePengguna) VALUES ('$nikDosen', 'dosen', 2)");
                mysqli_query($conn, "INSERT INTO dosen (nik, nama, username) VALUES ('$nikDosen', '$namaDosen', '$nikDosen') ON DUPLICATE KEY UPDATE nama='$namaDosen'");

                // 2. INPUT MAHASISWA (User & Profil)
                mysqli_query($conn, "INSERT IGNORE INTO pengguna (username, pass, tipePengguna) VALUES ('$npmMhs','mahasiswa', 3)");
                mysqli_query($conn, "INSERT INTO mahasiswa (npm, nama, username) VALUES ('$npmMhs', '$namaMhs', '$npmMhs') ON DUPLICATE KEY UPDATE nama='$namaMhs'");

                // 3. INPUT MATKUL & KELAS
                mysqli_query($conn, "INSERT INTO mataKuliah (kodeMataKuliah, namaMataKuliah) VALUES ('$kodeMK', '$namaMK') ON DUPLICATE KEY UPDATE namaMataKuliah='$namaMK'");
                mysqli_query($conn, "INSERT IGNORE INTO kelas (kodeMataKuliah, kodeKelas, semester) VALUES ('$kodeMK', '$kelas', '$semester')");
                
                // 4. INPUT RELASI (Pengampu & Peserta)
                mysqli_query($conn, "INSERT IGNORE INTO pengampu (nikPengampu, kodeMataKuliah, kodeKelas, semester) VALUES ('$nikDosen', '$kodeMK', '$kelas', '$semester')");
                mysqli_query($conn, "INSERT IGNORE INTO peserta (npmPeserta, kodeMataKuliah, kodeKelas, semester) VALUES ('$npmMhs', '$kodeMK', '$kelas', '$semester')");

                $berhasil++;
            }
        }
        fclose($file);
        
        // Tampilkan Alert Sukses dan Redirect
        echo "<script>
                alert('Selesai! $berhasil data berhasil diproses.'); 
                window.location.href='admin.php'; 
              </script>";
        
    } else {
        echo "<script>alert('Gagal! Pilih file CSV dulu!');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Import Data</title>
        <link href="../assets/convert.css" rel="stylesheet">
    </head>
    <body>  
        <div id="container">
            <div id="header">
                <h3>Import Data Perkuliahan</h3>
                <p>Pastikan file berbentuk <strong>.csv</strong> dan urutan kolomnya seperti ini:</p>
            </div>
            <table>
                <tr>
                    <th>KodeMK</th>
                    <th>NamaMK</th>
                    <th>Kls</th>
                    <th>Sem</th>
                    <th>NamaMhs</th>
                    <th>NPM</th>
                    <th>NamaDosen</th>
                    <th>NIK</th>
                </tr>
                <tr>
                    <td>AIF01</td>
                    <td>Web</td>
                    <td>A</td>
                    <td>20231</td>
                    <td>Budi</td>
                    <td>618..</td>
                    <td>Siska</td>
                    <td>199..</td>
                </tr>
            </table>
            <form action="" method="POST" enctype="multipart/form-data">
                <div id="subHeader">Pilih File CSV:</div>
                <input type="file" name="myDocument" accept=".csv" required>
                <button type="submit" name="send" id="btn-submit">Upload & Proses Database</button>
            </form>
            
            <a href="admin.php" class="btn-back">⮌</a>
         </div>
    </body>
</html>