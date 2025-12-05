<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tugas Besar Form</title>
    <link href="buatTubesStyles.css" rel="stylesheet">
</head>

<body>

<div class="topBar">
    <div class="backBtn">←</div>

    <div class="titleBox">
        <?php 
        echo $_GET["namaMataKuliah"]??"matkul boongan".'<br>';
        echo $_GET["kodeMataKuliah"]??"aiforthewin";
        ?>
    </div>

    <div class="profileIcon">👤</div>
</div>

<form class="formContainer" method="POST" action="mintaKomponen.php">

    <label>Nama Tugas Besar:</label>
    <input type="text" placeholder="">

    <label>Banyak Komponen Penilaian:</label>
    <input type="number" class="smallInput" name="banyakKomponen">

    <label>Banyak Anggota dalam Kelompok:</label>
    <input type="number" class="smallInput">

    <div class="checkboxRow">
        <input type="checkbox" id="auto">
        <label for="auto">Auto Generate Teams</label>
    </div>

    <button class="saveBtn">Save</button>

</form>

</body>
</html>
