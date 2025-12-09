<<<<<<< HEAD
<?php
$npm = $_POST['npm'];
$komponen = $_POST['komponen'];
$komentar = $_POST['komentar'];

// ===== Example DB save =====
// mysqli_query($conn,
//   "UPDATE nilai SET komentar='$komentar'
//    WHERE npm='$npm' AND komponen='$komponen'");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Komentar Disimpan</title>

<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #e3e3e3;
        margin: 0;
        padding: 40px;
        color: #004d26;
    }

    .container {
        max-width: 700px;
        margin: auto;
        background: white;
        padding: 40px;
        border-radius: 14px;
        box-shadow: 0 0 10px rgba(0,0,0,0.15);
        text-align: center;
    }

    h1 {
        margin-top: 0;
        color: #184f17;
        font-size: 32px;
        font-weight: 700;
    }

    .success-box {
        background: #e8f5e9;
        border-left: 8px solid #1b5e20;
        padding: 20px;
        margin: 25px 0;
        border-radius: 8px;
        font-size: 18px;
        line-height: 1.5;
        color: #1b5e20;
    }

    .label {
        font-weight: bold;
        color: #004d26;
    }

    .back-btn {
        display: inline-block;
        margin-top: 25px;
        padding: 12px 20px;
        background-color: #4d8dff;
        color: white;
        font-size: 18px;
        border: none;
        border-radius: 10px;
        text-decoration: none;
        cursor: pointer;
    }

    .back-btn:hover {
        background-color: #2f6ef5;
    }
</style>
</head>

<body>

<div class="container">

    <h1>Komentar Berhasil Disimpan</h1>

    <div class="success-box">
        <p><span class="label">NPM:</span> <?= htmlspecialchars($npm) ?></p>
        <p><span class="label">Komponen:</span> <?= htmlspecialchars($komponen) ?></p>
        <p><span class="label">Komentar:</span><br>
        <?= nl2br(htmlspecialchars($komentar)) ?></p>
    </div>

    <a href="nilai_dosen.php" class="back-btn">Kembali ke Halaman Nilai</a>

</div>

</body>
</html>
=======
<?php
$npm = $_POST['npm'];
$komponen = $_POST['komponen'];
$komentar = $_POST['komentar'];

// ===== Example DB save =====
// mysqli_query($conn,
//   "UPDATE nilai SET komentar='$komentar'
//    WHERE npm='$npm' AND komponen='$komponen'");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Komentar Disimpan</title>

<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #e3e3e3;
        margin: 0;
        padding: 40px;
        color: #004d26;
    }

    .container {
        max-width: 700px;
        margin: auto;
        background: white;
        padding: 40px;
        border-radius: 14px;
        box-shadow: 0 0 10px rgba(0,0,0,0.15);
        text-align: center;
    }

    h1 {
        margin-top: 0;
        color: #184f17;
        font-size: 32px;
        font-weight: 700;
    }

    .success-box {
        background: #e8f5e9;
        border-left: 8px solid #1b5e20;
        padding: 20px;
        margin: 25px 0;
        border-radius: 8px;
        font-size: 18px;
        line-height: 1.5;
        color: #1b5e20;
    }

    .label {
        font-weight: bold;
        color: #004d26;
    }

    .back-btn {
        display: inline-block;
        margin-top: 25px;
        padding: 12px 20px;
        background-color: #4d8dff;
        color: white;
        font-size: 18px;
        border: none;
        border-radius: 10px;
        text-decoration: none;
        cursor: pointer;
    }

    .back-btn:hover {
        background-color: #2f6ef5;
    }
</style>
</head>

<body>

<div class="container">

    <h1>Komentar Berhasil Disimpan</h1>

    <div class="success-box">
        <p><span class="label">NPM:</span> <?= htmlspecialchars($npm) ?></p>
        <p><span class="label">Komponen:</span> <?= htmlspecialchars($komponen) ?></p>
        <p><span class="label">Komentar:</span><br>
        <?= nl2br(htmlspecialchars($komentar)) ?></p>
    </div>

    <a href="nilai_dosen.php" class="back-btn">Kembali ke Halaman Nilai</a>

</div>

</body>
</html>
>>>>>>> 29117f52e4cf377569b6e6d0fe975986e375bb66
