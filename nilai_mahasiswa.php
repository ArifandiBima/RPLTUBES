<?php
require "conn.php";
session_start();

// --- 1. Get student identity (assuming logged-in student) ---
// **IMPORTANT:** Replace '1234567890' with the actual session variable if possible.
$npmPeserta = $_SESSION['npm'] ?? '1234567890'; // Use logged-in student's NPM

// --- 2. Get Task/Course details from URL ---
// Assuming these are passed via the URL, e.g., matkul.php?kodeMataKuliah=IF100&namaTugasBesar=Tugas+Besar...
$namaTugasBesar = $_GET["namaTugasBesar"] ?? "";
$kodeMatkul     = $_GET["kodeMataKuliah"] ?? "";
$kelas          = $_GET["kodeKelas"] ?? "";
$semester       = (int)($_GET["semester"] ?? 0);

// Fetch Student Name (optional, but good for display)
$stmt_mahasiswa = $conn->prepare("SELECT nama FROM mahasiswa WHERE npm = ?");
$stmt_mahasiswa->bind_param("s", $npmPeserta);
$stmt_mahasiswa->execute();
$namaMahasiswa = $stmt_mahasiswa->get_result()->fetch_assoc()['nama'] ?? 'Mahasiswa';


// --- 3. Query to fetch all component details, scores, and comments ---
$sql = "
    SELECT 
        kp.namaKomponen,
        kp.deadline,
        n.nilai,
        c.komentar,
        kp.is_visible  /* Check visibility status (for security/hiding) */
    FROM komponenpenilaian kp
    
    /* Left Join to get the student's score for this component */
    LEFT JOIN nilai n ON n.namaTugasBesar = kp.namaTugasBesar
        AND n.kodeMataKuliah = kp.kodeMataKuliah
        AND n.kodeKelas = kp.kodeKelas
        AND n.semester = kp.semester
        AND n.nomorKomponen = kp.nomorKomponen
        AND n.npmPeserta = ? /* Filter by student's NPM */
        
    /* Left Join to get the student's comment for this component */
    LEFT JOIN komentar c ON c.namaTugasBesar = kp.namaTugasBesar
        AND c.kodeMataKuliah = kp.kodeMataKuliah
        AND c.kodeKelas = kp.kodeKelas
        AND c.semester = kp.semester
        AND c.nomorKomponen = kp.nomorKomponen
        AND c.npmPeserta = ? /* Filter by student's NPM */
        
    WHERE kp.namaTugasBesar = ?
      AND kp.kodeMataKuliah = ?
      AND kp.kodeKelas = ?
      AND kp.semester = ?
    ORDER BY kp.nomorKomponen
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssi", $npmPeserta, $npmPeserta, $namaTugasBesar, $kodeMatkul, $kelas, $semester);$stmt->execute();
$result = $stmt->get_result();

$komponen = $result->fetch_all(MYSQLI_ASSOC);

$totalNilai = 0;
// Calculate the total score for components that are visible and have a score
foreach ($komponen as $row) {
    if ($row['is_visible'] && $row['nilai'] !== NULL) {
        $totalNilai += $row['nilai'];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="nilaiStyles.css">
</head>

<body>
<div class="container">

    <a href="matkul.php" class="back-btn">&#8592;</a>

    <h2 class="header-course">
        <?= $kodeMatkul ?> - Kelas <?= $kelas ?> (Smt <?= $semester ?>)<br>
        <small>NPM: <?= $npmPeserta ?> - <?= $namaMahasiswa ?></small>
    </h2>

    <h1><?= $namaTugasBesar ?></h1>

    <table>
        <tr>
            <th>Komponen</th>
            <th>Deadline</th>
            <th>Nilai</th>
            <th>Komentar</th>
        </tr>

        <?php foreach ($komponen as $row): ?>
        <tr>
            <td><?= $row['namaKomponen'] ?></td>
            <td><?= $row['deadline'] ?? '-' ?></td>
            <td>
                <?php 
                // Security Check: Only display score if Dosen set is_visible = TRUE (1)
                if ($row['is_visible']) {
                    echo $row['nilai'] ?? '0'; // Display 0 if null
                } else {
                    echo '-'; // Score is hidden by Dosen
                }
                ?>
            </td>
            <td><?= $row['komentar'] ?? '' ?></td>
        </tr>
        <?php endforeach; ?>

        <tr>
            <td><b>Nilai Akhir</b></td>
            <td></td>
            <td><b><?= $totalNilai ?></b></td>
            <td></td>
        </tr>
    </table>

</div>
</body>
</html>