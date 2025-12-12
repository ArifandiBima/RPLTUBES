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
        <?php echo $_POST["namaMataKuliah"]?><br>
        <?php echo $_POST["kodeMataKuliah"]?>
    </div>
</div>
<form action="processKomponen.php" method="post">
    <input type="hidden" name="namaTugasBesar" value="<?php echo $_POST["namaTugasBesar"]?>" >
    <input type="hidden" name="kodeMataKuliah" value="<?php echo $_POST["kodeMataKuliah"]?>" >
    <input type="hidden" name="kodeKelas" value="<?php echo $_POST["kodeKelas"]?>" >
    <input type="hidden" name="semester" value="<?php echo $_POST["semester"]?>" >
    <input type="hidden" name="auto" value="<?php echo $_POST["auto"]??0?>" >
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
