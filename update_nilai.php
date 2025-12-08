<?php
require "conn.php";

// These should come from hidden inputs in the form
$namaTugasBesar = $_POST["namaTugasBesar"];
$kodeMatkul     = $_POST["kodeMatkul"];
$kelas          = $_POST["kelas"];
$semester       = $_POST["semester"];

$nilai1 = $_POST['nilai_1'];
$nilai2 = $_POST['nilai_2'];

foreach ($nilai1 as $npm => $n1) {

    // ========================
    // UPDATE KOMPONEN 1
    // ========================
    $sql1 = "UPDATE nilai
            SET nilai = ?
            WHERE namaTugasBesar = ?
              AND kodeMataKuliah = ?
              AND kodeKelas = ?
              AND semester = ?
              AND nomorKomponen = 1
              AND npmPeserta = ?";

    $stmt1 = $conn->prepare($sql1);

    // d s s s i s
    $stmt1->bind_param(
        "dssssis",
        $n1,
        $namaTugasBesar,
        $kodeMatkul,
        $kelas,
        $semester,
        $npm
    );

    $stmt1->execute();

    // ========================
    // UPDATE KOMPONEN 2
    // ========================
    $n2 = $nilai2[$npm];

    $sql2 = "UPDATE nilai
            SET nilai = ?
            WHERE namaTugasBesar = ?
              AND kodeMataKuliah = ?
              AND kodeKelas = ?
              AND semester = ?
              AND nomorKomponen = 2
              AND npmPeserta = ?";

    $stmt2 = $conn->prepare($sql2);

    $stmt2->bind_param(
        "dssssis",
        $n2,
        $namaTugasBesar,
        $kodeMatkul,
        $kelas,
        $semester,
        $npm
    );

    $stmt2->execute();
}

header("Location: nilai_dosen.php?saved=1");
exit;
?>
