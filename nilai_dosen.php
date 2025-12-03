<?php
// data dummy — ganti dengan database
$mahasiswa = [
    ["618618118", "Dan Druff", 180, "?", "?"],
    ["618103", "Poli Ester", 282, "?", "?"],
];
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css">
</head>

<body>
<div class="container">

    <a href="home_dosen.php" class="back-btn">&#8592;</a>

    <h2 class="header-course">Matkul90<br>AIF123123</h2>

    <label>
        <input type="checkbox"> Sembunyikan Nilai
    </label>

    <table>
        <tr>
            <th>NPM</th>
            <th>Nama</th>
            <th>Komponen 1 (7/8)</th>
            <th>Komponen 2 (10/8)</th>
            <th>Nilai Akhir</th>
        </tr>

        <?php foreach ($mahasiswa as $m): ?>
        <tr>
            <td><?= $m[0] ?></td>
            <td><?= $m[1] ?></td>
            <td><?= $m[2] ?></td>
            <td><?= $m[3] ?></td>
            <td><?= $m[4] ?></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <!-- FORM KOMENTAR -->
    <div class="form-area">
        <form action="submit_komentar.php" method="POST">

            <select name="npm" class="select-small">
                <?php foreach ($mahasiswa as $m): ?>
                <option value="<?= $m[0] ?>"><?= $m[0] ?></option>
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
