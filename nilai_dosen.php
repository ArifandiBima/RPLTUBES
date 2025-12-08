<?php
require "conn.php";
session_start();

/*
    Data harusnya dpt dari page sebelumnya (GET/POST).
    Hardcode buat demo, ganti jd real value nnti
*/
$namaTugasBesar = "Project Algoritma";
$kodeMatkul     = "IF101";
$kelas          = "A";
$semester       = 1;

/*
    Query mahasiswa + nilai per komponen
*/
$sql = "
    SELECT m.npm, m.nama,
           n1.nilai AS komponen1,
           n2.nilai AS komponen2
    FROM peserta p
    JOIN mahasiswa m ON p.npmPeserta = m.npm
    LEFT JOIN nilai n1 ON 
        n1.npmPeserta = m.npm 
        AND n1.nomorKomponen = 1
        AND n1.namaTugasBesar = ?
        AND n1.kodeMataKuliah = ?
        AND n1.kodeKelas = ?
        AND n1.semester = ?
    LEFT JOIN nilai n2 ON 
        n2.npmPeserta = m.npm 
        AND n2.nomorKomponen = 2
        AND n2.namaTugasBesar = ?
        AND n2.kodeMataKuliah = ?
        AND n2.kodeKelas = ?
        AND n2.semester = ?
    WHERE p.kodeMataKuliah = ?
      AND p.kodeKelas = ?
      AND p.semester = ?
    ORDER BY m.npm
";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "sssssssssss",
    $namaTugasBesar, $kodeMatkul, $kelas, $semester,
    $namaTugasBesar, $kodeMatkul, $kelas, $semester,
    $kodeMatkul, $kelas, $semester
);
$stmt->execute();
$result = $stmt->get_result();

$mahasiswa = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="nilaiStyles.css">
</head>

<body>
<div class="container">

    <a href="home_dosen.php" class="back-btn">&#8592;</a>

    <h2 class="header-course">
        <?= $namaTugasBesar ?><br>
        <?= $kodeMatkul ?> - Kelas <?= $kelas ?> (Smt <?= $semester ?>)
    </h2>

    <label>
        <input type="checkbox"> Sembunyikan Nilai
    </label>

    <form action="update_nilai.php" method="POST">

        <!-- Send all primary key info -->
        <input type="hidden" name="namaTugasBesar" value="<?= $namaTugasBesar ?>">
        <input type="hidden" name="kodeMatkul" value="<?= $kodeMatkul ?>">
        <input type="hidden" name="kelas" value="<?= $kelas ?>">
        <input type="hidden" name="semester" value="<?= $semester ?>">

        <table>
            <tr>
                <th>NPM</th>
                <th>Nama</th>
                <th>Komponen 1</th>
                <th>Komponen 2</th>
                <th>Nilai Akhir</th>
            </tr>

            <?php foreach ($mahasiswa as $m): 
                $na = ($m["komponen1"] ?? 0) + ($m["komponen2"] ?? 0);
            ?>
            <tr>
                <td><?= $m["npm"] ?></td>
                <td><?= $m["nama"] ?></td>

                <td>
                    <input 
                        type="number" 
                        name="nilai_1[<?= $m["npm"] ?>]" 
                        value="<?= $m["komponen1"] ?>"
                        min="0"
                        step="1"
                    >
                </td>

                <td>
                    <input 
                        type="number" 
                        name="nilai_2[<?= $m["npm"] ?>]" 
                        value="<?= $m["komponen2"] ?>"
                        min="0"
                        step="1"
                    >
                </td>

                <td><?= $na ?></td>
            </tr>
            <?php endforeach; ?>

        </table>

        <button type="submit" class="submit-btn">
            Simpan Semua Nilai
        </button>

    </form>


    <!-- FORM KOMENTAR -->
    <div class="form-area">
        <form action="submit_komentar.php" method="POST">

            <select name="npm" class="select-small">
                <?php foreach ($mahasiswa as $m): ?>
                <option value="<?= $m["npm"] ?>"><?= $m["npm"] ?></option>
                <?php endforeach; ?>
            </select>

            <select name="komponen" class="select-small">
                <option value="1">Komponen 1</option>
                <option value="2">Komponen 2</option>
            </select>

            <textarea name="komentar" placeholder="Tulis komentar..."></textarea>

            <button type="submit" class="submit-btn">Submit Komentar</button>

        </form>
    </div>

</div>
</body>
</html>
