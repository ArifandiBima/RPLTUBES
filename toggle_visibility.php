<?php
require "conn.php";
session_start();

// Get POST data
$kodeMatkul     = $_POST["kodeMatkul"];
$namaTugasBesar = $_POST["namaTugasBesar"];
$kelas          = $_POST["kelas"];
$semester       = (int) $_POST["semester"];
$kelompok       = (int) $_POST["kelompok"]; // Not strictly needed for komp.penilaian, but good for redirect
$komponen       = (int) $_POST["komponen"]; // 1 or 2
$is_visible     = (int) $_POST["is_visible"]; // 0 (Hidden) or 1 (Visible)

// 1. UPDATE the visibility status in the database
$sql = "UPDATE komponenpenilaian 
        SET is_visible = ? 
        WHERE namaTugasBesar = ? 
          AND kodeMataKuliah = ? 
          AND kodeKelas = ? 
          AND semester = ? 
          AND nomorKomponen = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("isssii", $is_visible, $namaTugasBesar, $kodeMatkul, $kelas, $semester, $komponen);
$stmt->execute();

// 2. Redirect back to nilai_dosen.php with all required parameters
$redirectURL = "nilai_dosen.php?" . http_build_query([
    "kodeMataKuliah" => $kodeMatkul,
    "namaTugasBesar" => $namaTugasBesar,
    "kodeKelas"      => $kelas,
    "semester"       => $semester,
    "nomorKelompok"  => $kelompok,
    "saved"          => 1 // Optional confirmation
]);

header("Location: " . $redirectURL);
exit;
?>