<?php
require "conn.php";

$namaTB     = $_POST["namaTB"];
$kodeMatkul = $_POST["kodeMatkul"];
$kelas      = $_POST["kelas"];
$semester   = $_POST["semester"];

$banyak = 0;
foreach ($_POST as $key => $value) {
    if (strpos($key, "komponen_nama_") === 0) {
        $banyak++;
    }
}

for ($i = 1; $i <= $banyak; $i++) {
    $nama = $_POST["komponen_nama_$i"];
    $deadline = $_POST["deadline_$i"];
    $bobot = $_POST["bobot_$i"];

    $sql = "INSERT INTO komponenPenilaian 
            (namaTugasBesar, kodeMataKuliah, kodeKelas, semester, nomorKomponen, 
             namaKomponen, bobot, tanggalPenilaian, isHidden)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "sssissds",
        $namaTB, $kodeMatkul, $kelas, $semester,
        $i, $nama, $bobot, $deadline
    );
    $stmt->execute();
}

header("Location: nilai_dosen.php");
exit;
?>
