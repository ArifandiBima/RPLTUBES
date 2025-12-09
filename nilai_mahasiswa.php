<<<<<<< HEAD
<?php
// contoh data, ganti jd query database
$komponen = [
    ["Komponen1", "10/8/2025", 120, "Keju kurang banyak"],
    ["Komponen2", "10/10/2025", 13, ""]
];
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="nilaiStyles.css">
</head>

<body>
<div class="container">

    <a href="home.php" class="back-btn">&#8592;</a>

    <h2 class="header-course">Matkul90<br>AIF123123</h2>

    <h1>Tugas Besar Pembuatan Keju<br>initialized 8/23</h1>

    <table>
        <tr>
            <th>Komponen</th>
            <th>Deadline</th>
            <th>Nilai</th>
            <th>Komentar</th>
        </tr>

        <?php foreach ($komponen as $row): ?>
        <tr>
            <td><?= $row[0] ?></td>
            <td><?= $row[1] ?></td>
            <td><?= $row[2] ?></td>
            <td><?= $row[3] ?></td>
        </tr>
        <?php endforeach; ?>

        <tr>
            <td><b>Nilai Akhir</b></td>
            <td></td>
            <td><?= $komponen[0][2] + $komponen[1][2] ?></td>
            <td></td>
        </tr>
    </table>

</div>
</body>
</html>
=======
<?php
// contoh data, ganti jd query database
$komponen = [
    ["Komponen1", "10/8/2025", 120, "Keju kurang banyak"],
    ["Komponen2", "10/10/2025", 13, ""]
];
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="nilaiStyles.css">
</head>

<body>
<div class="container">

    <a href="home.php" class="back-btn">&#8592;</a>

    <h2 class="header-course">Matkul90<br>AIF123123</h2>

    <h1>Tugas Besar Pembuatan Keju<br>initialized 8/23</h1>

    <table>
        <tr>
            <th>Komponen</th>
            <th>Deadline</th>
            <th>Nilai</th>
            <th>Komentar</th>
        </tr>

        <?php foreach ($komponen as $row): ?>
        <tr>
            <td><?= $row[0] ?></td>
            <td><?= $row[1] ?></td>
            <td><?= $row[2] ?></td>
            <td><?= $row[3] ?></td>
        </tr>
        <?php endforeach; ?>

        <tr>
            <td><b>Nilai Akhir</b></td>
            <td></td>
            <td><?= $komponen[0][2] + $komponen[1][2] ?></td>
            <td></td>
        </tr>
    </table>

</div>
</body>
</html>
>>>>>>> 29117f52e4cf377569b6e6d0fe975986e375bb66
