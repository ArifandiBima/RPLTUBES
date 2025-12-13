<?php
require "conn.php";
session_start();

/*
    GET parameters from pilih_kelompok.php
*/
$namaTugasBesar = $_GET["namaTB"];
$kodeMatkul     = $_GET["kodeMatkul"];
$kelas          = $_GET["kelas"];
$semester       = $_GET["semester"];
$kelompok       = $_GET["kelompok"];
/*
    Query mahasiswa + nilai per komponen FROM THIS GROUP ONLY
*/
$sql = "
    SELECT m.npm, m.nama,
           n1.nilai AS komponen1,
           n2.nilai AS komponen2
    FROM peserta p
    JOIN mahasiswa m 
         ON p.npmPeserta = m.npm
    JOIN anggotaKelompok ag
         ON ag.npmPeserta = m.npm
        AND ag.namaTugasBesar = ?
        AND ag.kodeMataKuliah = ?
        AND ag.kodeKelas = ?
        AND ag.semester = ?
        AND ag.nomorKelompok = ?
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
    "sssissssssssssss",
    $namaTugasBesar, $kodeMatkul, $kelas, $semester, $kelompok,
    $namaTugasBesar, $kodeMatkul, $kelas, $semester,
    $namaTugasBesar, $kodeMatkul, $kelas, $semester,
    $kodeMatkul, $kelas, $semester
);
$stmt->execute();
$result = $stmt->get_result();

$mahasiswa = $result->fetch_all(MYSQLI_ASSOC);

/* FALLBACK DUMMY DATA IF QUERY RETURNS NOTHING
   HAPUS KALO UDH KONEK SAMA DB */
if (empty($mahasiswa)) {
    $mahasiswa = [
        [
            "npm" => "2200000001",
            "nama" => "Budi Santoso",
            "komponen1" => 32,
            "komponen2" => 32
        ],
        [
            "npm" => "2200000002",
            "nama" => "Siti Aminah",
            "komponen1" => 45,
            "komponen2" => 0    // you mentioned Komponen 2 was blank earlier
        ]
    ];
}

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

    <a class="back-btn">&#8592;</a>

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
