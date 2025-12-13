<?php
require "conn.php";
session_start();

// --- 1. Get ALL data for database and redirect ---
$npm            = $_POST['npm'] ?? '';
$komponen       = (int)($_POST['komponen'] ?? 0);
$komentar       = $_POST['komentar'] ?? '';

// Ensure these key identifiers are present (they must be passed as hidden fields)
$namaTugasBesar = $_POST["namaTugasBesar"] ?? '';
$kodeMatkul     = $_POST["kodeMatkul"] ?? '';
$kelas          = $_POST["kelas"] ?? '';
$semester       = (int)($_POST["semester"] ?? 0);
$kelompok       = (int)($_POST["kelompok"] ?? 0);

// --- 2. Database Logic: UPDATE / INSERT into the 'komentar' table ---
// Check if a comment already exists for this specific combination
$sql_check = "SELECT 1 FROM nilai
              WHERE npmPeserta = ? AND nomorKomponen = ? 
                AND namaTugasBesar = ? AND kodeMataKuliah = ? 
                AND kodeKelas = ? AND semester = ?";

$check = $conn->prepare($sql_check);
$check->bind_param("sisssi", $npm, $komponen, $namaTugasBesar, $kodeMatkul, $kelas, $semester);
$check->execute();
$exists = $check->get_result()->num_rows > 0;
$check->close();

if ($exists) {
    // UPDATE existing comment
    $sql_stmt = "UPDATE nilai 
                 SET komentar = ?   
                 WHERE npmPeserta = ? AND nomorKomponen = ? 
                   AND namaTugasBesar = ? AND kodeMataKuliah = ? 
                   AND kodeKelas = ? AND semester = ?";
    $stmt = $conn->prepare($sql_stmt);
    // Bind parameters: (string, string, integer, string, string, string, integer)
    $stmt->bind_param("ssisssi", $komentar, $npm, $komponen, $namaTugasBesar, $kodeMatkul, $kelas, $semester);
} else {
    // INSERT new comment
    $sql_stmt = "INSERT INTO nilai 
                 (npmPeserta, nomorKomponen, komentar, namaTugasBesar, kodeMataKuliah, kodeKelas, semester)
                 VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql_stmt);
    // Bind parameters: (string, integer, string, string, string, string, integer)
    $stmt->bind_param("sissssi", $npm, $komponen, $komentar, $namaTugasBesar, $kodeMatkul, $kelas, $semester);
}

// Execute the statement
if ($stmt->execute()) {
    $comment_saved_flag = 1;
} else {
    // Handle error if insertion/update failed
    // Optionally log the error: error_log($stmt->error);
    $comment_saved_flag = 0;
}

$stmt->close();
$conn->close();

// --- 3. Redirect back to nilai_dosen.php with ALL parameters ---
$redirectURL = "nilai_dosen.php?" . http_build_query([
    "kodeMataKuliah" => $kodeMatkul,
    "namaTugasBesar" => $namaTugasBesar,
    "kodeKelas"      => $kelas,
    "semester"       => $semester,
    "nomorKelompok"  => $kelompok,
    "comment_saved"  => $comment_saved_flag // Pass success status
]);

// Perform the server-side redirect
header("Location: " . $redirectURL);
exit;
?>