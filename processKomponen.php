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

$sql = "INSERT INTO tugasBesar 
            (namaTugasBesar, kodeMataKuliah, kodeKelas, semester, banyakAnggotaKelompok)
            VALUES (?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "sssii",
    $namaTB, $kodeMatkul, $kelas, $semester, $_POST["banyakAnggota"]
);
$stmt->execute();

$sql = "SELECT count(npmPeserta) as cnt
        from peserta
        where kodeMataKuliah = ? and semester = ? and kodeKelas=?"
        ;
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "sis",
            $kodeMatkul,$semester, $kelas
        );
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $banyakPeserta = (int)($row["cnt"]);
        $banyakPeserta = ($banyakPeserta+$_POST["banyakAnggota"]-1)/$_POST["banyakAnggota"];
for ($i=1;$i<=$banyakPeserta;$i++){
    $sql = "INSERT INTO kelompok 
    (namaTugasBesar, kodeMataKuliah, kodeKelas, semester, nomorKelompok)
    VALUES (?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "sssii",
        $namaTB, $kodeMatkul, $kelas, $semester, $i
    );
    $stmt->execute();
}

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

header("Location: dosen_edit_kelompok_controller.php?".http_build_query($data));
exit;
?>