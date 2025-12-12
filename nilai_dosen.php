<?php
require "conn.php";
session_start();

// 1. Get data from URL (using ?? to avoid errors if keys are missing)
$namaTugasBesar = $_GET["namaTugasBesar"] ?? "";
$kodeMatkul     = $_GET["kodeMataKuliah"] ?? "";
$kelas          = $_GET["kodeKelas"] ?? "";
$semester       = (int)($_GET["semester"] ?? 0);
$kelompok       = (int)($_GET["nomorKelompok"] ?? 0);

// 2. Simplified SQL: Joining mahasiswa directly to anggotaKelompok
$sql = "
    SELECT m.npm, m.nama,
           n1.nilai AS komponen1,
           n2.nilai AS komponen2
    FROM anggotaKelompok ag
    JOIN mahasiswa m ON ag.npmPeserta = m.npm
    LEFT JOIN nilai n1 ON n1.npmPeserta = m.npm 
        AND n1.nomorKomponen = 1
        AND n1.namaTugasBesar = ag.namaTugasBesar
        AND n1.kodeMataKuliah = ag.kodeMataKuliah
        AND n1.kodeKelas      = ag.kodeKelas
        AND n1.semester       = ag.semester
    LEFT JOIN nilai n2 ON n2.npmPeserta = m.npm 
        AND n2.nomorKomponen = 2
        AND n2.namaTugasBesar = ag.namaTugasBesar
        AND n2.kodeMataKuliah = ag.kodeMataKuliah
        AND n2.kodeKelas      = ag.kodeKelas
        AND n2.semester       = ag.semester
    WHERE ag.namaTugasBesar = ?
      AND ag.kodeMataKuliah = ?
      AND ag.kodeKelas      = ?
      AND ag.semester       = ?
      AND ag.nomorKelompok  = ?
    ORDER BY m.npm
";

$stmt = $conn->prepare($sql);

// 3. CORRECTED bind_param: 
// Matches variables defined at the top and uses 'i' for integers (semester, kelompok)
$stmt->bind_param("sssii", $namaTugasBesar, $kodeMatkul, $kelas, $semester, $kelompok);

$stmt->execute();
$result = $stmt->get_result();
$mahasiswa = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="nilaiStyles.css">
    <style>
        /* Extra safety to hide cells */
        .hidden-col { display: none !important; }
        .hide-controls { margin: 10px 0 20px; }
        .hide-controls label { margin-right: 20px; font-size: 16px; }
    </style>
</head>

<body>
<div class="container">

    <a href="home_dosen.php" class="back-btn">&#8592;</a>

    <h2 class="header-course">
        <?= $namaTugasBesar ?><br>
        <?= $kodeMatkul ?> - Kelas <?= $kelas ?> (Smt <?= $semester ?>)  
        <br><small>Kelompok <?= chr(64 + $kelompok) ?></small>
    </h2>

    <!-- NEW: PER-KOMPONEN HIDE SWITCHES -->
    <div class="hide-controls">
        <label><input type="checkbox" id="hideK1"> Sembunyikan Komponen 1</label>
        <label><input type="checkbox" id="hideK2"> Sembunyikan Komponen 2</label>
    </div>

    <form action="update_nilai.php" method="POST">

        <!-- Send all key info -->
        <input type="hidden" name="namaTugasBesar" value="<?= $namaTugasBesar ?>">
        <input type="hidden" name="kodeMatkul" value="<?= $kodeMatkul ?>">
        <input type="hidden" name="kelas" value="<?= $kelas ?>">
        <input type="hidden" name="semester" value="<?= $semester ?>">
        <input type="hidden" name="kelompok" value="<?= $kelompok ?>">

        <table>
            <tr>
                <th>NPM</th>
                <th>Nama</th>
                <th class="k1">Komponen 1</th>
                <th class="k2">Komponen 2</th>
                <th>Nilai Akhir</th>
            </tr>

            <?php foreach ($mahasiswa as $m): 
                $na = ($m["komponen1"] ?? 0) + ($m["komponen2"] ?? 0);
            ?>
            <tr>
                <td><?= $m["npm"] ?></td>
                <td><?= $m["nama"] ?></td>

                <!-- KOMPONEN 1 -->
                <td class="k1">
                    <input 
                        type="number" 
                        name="nilai_1[<?= $m["npm"] ?>]" 
                        value="<?= $m["komponen1"] ?>"
                        min="0"
                        step="1"
                    >
                </td>

                <!-- KOMPONEN 2 -->
                <td class="k2">
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

<!-- JS FOR HIDING EACH KOMPONEN COLUMN -->
<script>
document.getElementById('hideK1').addEventListener('change', function() {
    document.querySelectorAll('.k1').forEach(c => {
        c.classList.toggle('hidden-col', this.checked);
    });
});

document.getElementById('hideK2').addEventListener('change', function() {
    document.querySelectorAll('.k2').forEach(c => {
        c.classList.toggle('hidden-col', this.checked);
    });
});
</script>

</body>
</html>
