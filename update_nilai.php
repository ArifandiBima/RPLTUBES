<?php
require "conn.php";

<<<<<<< HEAD
$namaTugasBesar = $_POST["namaTugasBesar"];
$kodeMatkul     = $_POST["kodeMatkul"];
$kelas          = $_POST["kelas"];
$semester       = (int) $_POST["semester"];
=======
// These should come from hidden inputs in the form
$namaTugasBesar = $_POST["namaTugasBesar"];
$kodeMatkul     = $_POST["kodeMatkul"];
$kelas          = $_POST["kelas"];
$semester       = $_POST["semester"];
>>>>>>> 29117f52e4cf377569b6e6d0fe975986e375bb66

$nilai1 = $_POST['nilai_1'];
$nilai2 = $_POST['nilai_2'];

foreach ($nilai1 as $npm => $n1) {

<<<<<<< HEAD
    // ---------- UPDATE / INSERT KOMPONEN 1 ----------
    $sql = "SELECT 1 FROM nilai
            WHERE namaTugasBesar=? AND kodeMataKuliah=? 
              AND kodeKelas=? AND semester=? 
              AND nomorKomponen=1 AND npmPeserta=?";
    $check = $conn->prepare($sql);
    $check->bind_param("sssis", $namaTugasBesar,$kodeMatkul,$kelas,$semester,$npm);
    $check->execute();
    $exists = $check->get_result()->num_rows > 0;

    if ($exists) {
        $sql = "UPDATE nilai 
                SET nilai=? 
                WHERE namaTugasBesar=? AND kodeMataKuliah=? 
                  AND kodeKelas=? AND semester=? 
                  AND nomorKomponen=1 AND npmPeserta=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("dsssds",$n1,$namaTugasBesar,$kodeMatkul,$kelas,$semester,$npm);
        $stmt->execute();
    } else {
        $sql = "INSERT INTO nilai 
                (namaTugasBesar, kodeMataKuliah, kodeKelas, semester, nomorKomponen, nilai, npmPeserta)
                VALUES (?, ?, ?, ?, 1, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssids",$namaTugasBesar,$kodeMatkul,$kelas,$semester,$n1,$npm);
        $stmt->execute();
    }

    // ---------- UPDATE / INSERT KOMPONEN 2 ----------
    $n2 = $nilai2[$npm];

    $sql = "SELECT 1 FROM nilai
            WHERE namaTugasBesar=? AND kodeMataKuliah=? 
              AND kodeKelas=? AND semester=? 
              AND nomorKomponen=2 AND npmPeserta=?";
    $check = $conn->prepare($sql);
    $check->bind_param("sssis", $namaTugasBesar,$kodeMatkul,$kelas,$semester,$npm);
    $check->execute();
    $exists = $check->get_result()->num_rows > 0;

    if ($exists) {
        $sql = "UPDATE nilai 
                SET nilai=? 
                WHERE namaTugasBesar=? AND kodeMataKuliah=? 
                  AND kodeKelas=? AND semester=? 
                  AND nomorKomponen=2 AND npmPeserta=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("dsssds",$n2,$namaTugasBesar,$kodeMatkul,$kelas,$semester,$npm);
        $stmt->execute();
    } else {
        $sql = "INSERT INTO nilai 
                (namaTugasBesar, kodeMataKuliah, kodeKelas, semester, nomorKomponen, nilai, npmPeserta)
                VALUES (?, ?, ?, ?, 2, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssids",$namaTugasBesar,$kodeMatkul,$kelas,$semester,$n2,$npm);
        $stmt->execute();
    }
}


=======
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

>>>>>>> 29117f52e4cf377569b6e6d0fe975986e375bb66
header("Location: nilai_dosen.php?saved=1");
exit;
?>
