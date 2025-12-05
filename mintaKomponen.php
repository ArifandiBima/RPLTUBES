<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Komponen Penilaian</title>
<link href="mintaKomponenStyles.css" rel = "stylesheet">

</head>

<body>

<!-- HEADER -->
<div class="header">
    <div class="back-btn">←</div>
    <div class="title">
        Matkul90<br>
        AIF123123
    </div>
</div>
<form action="processKomponen.php" method="post">
    <?php 
    $banyak = $_POST["banyakKomponen"] ?? 2;

    for ($i = 1; $i <= $banyak; $i++) {
    echo '
    <div class="card">
    <label>Nama Komponen:</label>
    <input type="text" name="komponen_nama_'.$i.'">

    <label>Deadline:</label>
    <input type="date" name="deadline_'.$i.'">

    <label>Bobot:</label>
    <input type="number" name="bobot_'.$i.'" min="0">
    </div>';
    }
    ?>
    <div class="save-container">
    <button class="save-btn" type="submit">Save</button>
    </div>
</form>

</body>
</html>
