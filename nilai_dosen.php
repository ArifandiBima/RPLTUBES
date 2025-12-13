<?php
require "conn.php";
session_start();

// 1. Get data from URL (using ?? to avoid errors if keys are missing)
$namaTugasBesar = $_GET["namaTugasBesar"] ?? "";
$kodeMatkul     = $_GET["kodeMataKuliah"] ?? "";
$kelas          = $_GET["kodeKelas"] ?? "";
$semester       = (int)($_GET["semester"] ?? 0);
$kelompok       = (int)($_GET["nomorKelompok"] ?? 0);

// 2. Simplified SQL: Joining mahasiswa directly to anggotaKelompok to fetch current scores
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
$stmt->bind_param("sssii", $namaTugasBesar, $kodeMatkul, $kelas, $semester, $kelompok);
$stmt->execute();
$result = $stmt->get_result();
$mahasiswa = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// --- 3. Fetch Component Visibility Status (Database Check) ---
$sql_vis = "SELECT nomorKomponen, isHidden 
            FROM komponenpenilaian 
            WHERE namaTugasBesar = ? AND kodeMataKuliah = ? 
              AND kodeKelas = ? AND semester = ?";
$stmt_vis = $conn->prepare($sql_vis);
$stmt_vis->bind_param("sssi", $namaTugasBesar, $kodeMatkul, $kelas, $semester);
$stmt_vis->execute();
$result_vis = $stmt_vis->get_result();

$visibility = [];
while ($row = $result_vis->fetch_assoc()) {
    // is_visible will be 1 or 0 from the DB, cast to bool
    $visibility[$row['nomorKomponen']] = (bool)$row['isHidden'];
}
$stmt_vis->close();

// Default values (assuming visible if no entry exists)
$isK1Visible = $visibility[1] ?? true;
$isK2Visible = $visibility[2] ?? true;
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="nilaiStyles.css">
    <style>
        /* Extra safety to hide cells */
        .hidden-col { display: none !important; }
        .hide-controls { 
            margin: 10px 0 20px; 
            border: 1px dashed #ccc; 
            padding: 10px;
            border-radius: 5px;
        }
        .hide-controls label { margin-right: 20px; font-size: 14px; display: inline-block; }
        .comment-success {
            background-color: #e8f5e9; 
            border: 1px solid #c8e6c9; 
            color: #1b5e20; 
            padding: 10px; 
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
        }
    </style>
</head>

<body>
<div class="container">

    <a href="admin/admin.php" class="back-btn">&#8592;</a>

    <h2 class="header-course">
        <?= $namaTugasBesar ?><br>
        <?= $kodeMatkul ?> - Kelas <?= $kelas ?> (Smt <?= $semester ?>)  
        <br><small>Kelompok <?= chr(64 + $kelompok) ?></small>
    </h2>

    <?php if (isset($_GET['comment_saved']) && $_GET['comment_saved'] == 1): ?>
        <div class="comment-success">
            Komentar berhasil disimpan!
        </div>
    <?php endif; ?>
    
    <div class="hide-controls">
        <h4>⚙️ Kontrol Visibilitas Nilai Mahasiswa (Diperlukan `toggle_visibility.php`)</h4>
        <p>Status saat ini: Komponen 1: 
        <strong><?= $isK1Visible ? 'TAMPIL' : 'TERSEMBUNYI' ?></strong>, Komponen 2: 
        <strong><?= $isK2Visible ? 'TAMPIL' : 'TERSEMBUNYI' ?></strong></p>

        <form action="toggle_visibility.php" method="POST" style="display:inline-block;">
            <input type="hidden" name="namaTugasBesar" value="<?= $namaTugasBesar ?>">
            <input type="hidden" name="kodeMatkul" value="<?= $kodeMatkul ?>">
            <input type="hidden" name="kelas" value="<?= $kelas ?>">
            <input type="hidden" name="semester" value="<?= $semester ?>">
            <input type="hidden" name="kelompok" value="<?= $kelompok ?>">
            <input type="hidden" name="komponen" value="1">
            
            <label>
                <input 
                    type="checkbox" 
                    name="is_visible" 
                    value="<?= $isK1Visible ? 0 : 1 ?>" 
                    <?= $isK1Visible ? '' : 'checked' ?>
                    onchange="this.form.submit()"
                >
                Sembunyikan K1 dari Mahasiswa
            </label>
        </form>
        
        &nbsp; | &nbsp;

        <form action="toggle_visibility.php" method="POST" style="display:inline-block;">
            <input type="hidden" name="namaTugasBesar" value="<?= $namaTugasBesar ?>">
            <input type="hidden" name="kodeMatkul" value="<?= $kodeMatkul ?>">
            <input type="hidden" name="kelas" value="<?= $kelas ?>">
            <input type="hidden" name="semester" value="<?= $semester ?>">
            <input type="hidden" name="kelompok" value="<?= $kelompok ?>">
            <input type="hidden" name="komponen" value="2">
            
            <label>
                <input 
                    type="checkbox" 
                    name="is_visible" 
                    value="<?= $isK2Visible ? 0 : 1 ?>" 
                    <?= $isK2Visible ? '' : 'checked' ?>
                    onchange="this.form.submit()"
                >
                Sembunyikan K2 dari Mahasiswa
            </label>
        </form>
        
        <hr style="margin-top: 15px;">
        <p style="font-style: italic; font-size: 12px; margin-bottom: 0;">
            * Checklist di bawah hanya menyembunyikan kolom di tampilan Anda (Dosen).
            <label><input type="checkbox" id="hideK1"> Sembunyikan K1 (Visual)</label>
            <label><input type="checkbox" id="hideK2"> Sembunyikan K2 (Visual)</label>
        </p>
    </div>

    <form action="update_nilai.php" method="POST">

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

                <td class="k1">
                    <input 
                        type="number" 
                        name="nilai_1[<?= $m["npm"] ?>]" 
                        value="<?= $m["komponen1"] ?>"
                        min="0"
                        step="1"
                    >
                </td>

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


    <div class="form-area">
        <h3>💬 Beri Komentar</h3>
        <form action="submit_komentar.php" method="POST">

            <input type="hidden" name="namaTugasBesar" value="<?= $namaTugasBesar ?>">
            <input type="hidden" name="kodeMatkul" value="<?= $kodeMatkul ?>">
            <input type="hidden" name="kelas" value="<?= $kelas ?>">
            <input type="hidden" name="semester" value="<?= $semester ?>">
            <input type="hidden" name="kelompok" value="<?= $kelompok ?>">
            
            <select name="npm" class="select-small" required>
                <option value="">-- Pilih NPM --</option>
                <?php foreach ($mahasiswa as $m): ?>
                <option value="<?= $m["npm"] ?>"><?= $m["npm"] ?> - <?= $m["nama"] ?></option>
                <?php endforeach; ?>
            </select>
            
            <select name="komponen" class="select-small" required>
                <option value="">-- Pilih Komponen --</option>
                <option value="1">Komponen 1</option>
                <option value="2">Komponen 2</option>
            </select>

            <textarea name="komentar" placeholder="Tulis komentar..." required></textarea>

            <button type="submit" class="submit-btn">Submit Komentar</button>

        </form>
    </div>

</div>

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