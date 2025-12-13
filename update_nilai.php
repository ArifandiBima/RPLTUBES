<?php
require "conn.php";

$namaTugasBesar = $_POST["namaTugasBesar"];
$kodeMatkul     = $_POST["kodeMatkul"];
$kelas          = $_POST["kelas"];
$semester       = (int) $_POST["semester"];
$kelompok       = $_POST["kelompok"]; // Grab the group number for the redirect

$nilai1 = $_POST['nilai_1'];
$nilai2 = $_POST['nilai_2'];

foreach ($nilai1 as $npm => $n1) {

    // ---------- UPDATE / INSERT KOMPONEN 1 ----------
    $sql = "SELECT 1 FROM nilai
            WHERE namaTugasBesar=? AND kodeMataKuliah=? 
              AND kodeKelas=? AND semester=? 
              AND nomorKomponen=1 AND npmPeserta=?";
    $check = $conn->prepare($sql);
    $check->bind_param("sssis", $namaTugasBesar, $kodeMatkul, $kelas, $semester, $npm);
    $check->execute();
    $exists = $check->get_result()->num_rows > 0;

    if ($exists) {
        $sql = "UPDATE nilai 
                SET nilai=? 
                WHERE namaTugasBesar=? AND kodeMataKuliah=? 
                  AND kodeKelas=? AND semester=? 
                  AND nomorKomponen=1 AND npmPeserta=?";
        $stmt = $conn->prepare($sql);
        // Fixed type string: "dsssis" (double, string, string, string, integer, string)
        $stmt->bind_param("dsssis", $n1, $namaTugasBesar, $kodeMatkul, $kelas, $semester, $npm);
        $stmt->execute();
    } else {
        $sql = "INSERT INTO nilai 
                (namaTugasBesar, kodeMataKuliah, kodeKelas, semester, nomorKomponen, nilai, npmPeserta)
                VALUES (?, ?, ?, ?, 1, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssids", $namaTugasBesar, $kodeMatkul, $kelas, $semester, $n1, $npm);
        $stmt->execute();
    }

    // ---------- UPDATE / INSERT KOMPONEN 2 ----------
    $n2 = $nilai2[$npm];

    $sql = "SELECT 1 FROM nilai
            WHERE namaTugasBesar=? AND kodeMataKuliah=? 
              AND kodeKelas=? AND semester=? 
              AND nomorKomponen=2 AND npmPeserta=?";
    $check = $conn->prepare($sql);
    $check->bind_param("sssis", $namaTugasBesar, $kodeMatkul, $kelas, $semester, $npm);
    $check->execute();
    $exists = $check->get_result()->num_rows > 0;

    if ($exists) {
        $sql = "UPDATE nilai 
                SET nilai=? 
                WHERE namaTugasBesar=? AND kodeMataKuliah=? 
                  AND kodeKelas=? AND semester=? 
                  AND nomorKomponen=2 AND npmPeserta=?";
        $stmt = $conn->prepare($sql);
        // Fixed type string: "dsssis"
        $stmt->bind_param("dsssis", $n2, $namaTugasBesar, $kodeMatkul, $kelas, $semester, $npm);
        $stmt->execute();
    } else {
        $sql = "INSERT INTO nilai 
                (namaTugasBesar, kodeMataKuliah, kodeKelas, semester, nomorKomponen, nilai, npmPeserta)
                VALUES (?, ?, ?, ?, 2, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssids", $namaTugasBesar, $kodeMatkul, $kelas, $semester, $n2, $npm);
        $stmt->execute();
    }
}

// FIX: Construct a full URL so 'nilai_dosen.php' knows what data to fetch
$redirectURL = "nilai_dosen.php?" . http_build_query([
    "kodeMataKuliah" => $kodeMatkul,
    "namaTugasBesar" => $namaTugasBesar,
    "kodeKelas"      => $kelas,
    "semester"       => $semester,
    "nomorKelompok"  => $kelompok,
    "saved"          => 1
]);

header("Location: " . $redirectURL);
exit;
?>