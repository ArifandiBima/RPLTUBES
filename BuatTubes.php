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

    <input type="hidden" name="kodeMatkul" value="<?= $_GET['kodeMataKuliah'] ?>">
    <input type="hidden" name="namaMatkul" value="<?= $_GET['namaMataKuliah'] ?>">
    <input type="hidden" name="kelas" value="<?= $_GET['kelas']?>">
    <input type="hidden" name="semester" value="<?= $_GET['semester']?>">

    <label>Nama Tugas Besar:</label>
    <input type="text" name="namaTB" required>

    <label>Banyak Komponen Penilaian:</label>
    <input type="number" class="smallInput" name="banyakKomponen" required>

    <label>Banyak Anggota dalam Kelompok:</label>
    <input type="number" class="smallInput" name="banyakAnggota" required>

    <div class="checkboxRow">
        <input type="checkbox" id="auto" name="auto" value="1"> 
        <label for="auto">Auto Generate Teams</label>
    </div>

    <button class="saveBtn">Save</button>
</form>


</body>
</html>
