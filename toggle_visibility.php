<?php
require "conn.php";
session_start();

// Get POST data
$kodeMatkul     = $_POST["kodeMatkul"] ?? '';
$namaTugasBesar = $_POST["namaTugasBesar"] ?? '';
$kelas          = $_POST["kelas"] ?? '';
$semester       = (int)($_POST["semester"] ?? 0);
$kelompok       = (int)($_POST["kelompok"] ?? 0);
$komponen       = (int)($_POST["komponen"] ?? 0); // 1 or 2
$isHidden     = (int)($_POST["isHidden"] ?? 0); // 1 (Visible) or 0 (Hidden)

// 1. UPDATE the visibility status in the database
$sql = "UPDATE komponenpenilaian 
        SET isHidden = ? 
        WHERE namaTugasBesar = ? 
          AND kodeMataKuliah = ? 
          AND kodeKelas = ? 
          AND semester = ? 
          AND nomorKomponen = ?";

$stmt = $conn->prepare($sql);
// Bind: (integer, string, string, string, integer, integer)
$stmt->bind_param("isssii", $isHidden, $namaTugasBesar, $kodeMatkul, $kelas, $semester, $komponen);
$stmt->execute();
$stmt->close();
$conn->close();

// 2. Redirect back to nilai_dosen.php with all required parameters
$redirectURL = "nilai_dosen.php?" . http_build_query([
    "kodeMataKuliah" => $kodeMatkul,
    "namaTugasBesar" => $namaTugasBesar,
    "kodeKelas"      => $kelas,
    "semester"       => $semester,
    "nomorKelompok"  => $kelompok,
    "visibility_updated" => 1
]);

header("Location: " . $redirectURL);
exit;
?>