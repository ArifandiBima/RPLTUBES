<?php
require "conn.php";
$data=array(
    'namaTugasBesar' => $_POST["namaTugasBesar"],
    'kodeMataKuliah' => $_POST["kodeMataKuliah"],
    'kodeKelas' =>$_POST["kodeKelas"],
    'semester' =>$_POST["semester"],
    'auto'=>$_POST["auto"]
);
$namaTB     = $_POST["namaTugasBesar"];
$kodeMatkul = $_POST["kodeMataKuliah"];
$kelas      = $_POST["kodeKelas"];
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
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "sssissds",
        $namaTB, $kodeMatkul, $kelas, $semester,
        $i, $nama, $bobot, $deadline, 0
    );
    $stmt->execute();
}

header("Location: nilai_dosen.php?".http_build_query($data));
exit;
?>