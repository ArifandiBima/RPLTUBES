<?php require "conn.php";
session_start();?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href = "tubesSelectStyles.css" rel="stylesheet">
    <title>Document</title>
</head>
<body>

<div class="header">
    <a class="back-btn" href="matkul.php">⮌</a>

    <div class="profile-card">
        <div class="profile-icon"></div>
        <div class="profile-text">
            <b>Nama:</b> <?php echo $_SESSION["nama"]??"not set";?><br>
            <b>NIK:</b> <?php echo $_SESSION["nik"]?? "not set";?><br>
            <b>Nama Matkul: </b> MatkulA
        </div>
    </div>

</div>

<div class="container">
    <?php
    $queryTubes = "
    SELECT namaTugasBesar
    FROM tugasBesar
    WHERE kodeMataKuliah = ? AND kodeKelas =? AND semester = ? 
    ";
    $stmt = $conn->prepare($queryTubes);
    $stmt->bind_param("ssi",$_GET["kodeMataKuliah"], $_GET["kodeKelas"],$_GET["semester"]);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = array(
        'namaMataKuliah' => $_GET["namaMataKuliah"],
        'kodeMataKuliah' => $_GET["kodeMataKuliah"],
        'kodeKelas'   => $_GET["kodeKelas"],
        'semester'       => $_GET["semester"]
    );
    $targetLocation = "admin/admin.php";
    while ($row = $result->fetch_assoc()) {
        $data["namaTugasBesar"] = $row["namaTugasBesar"];
        echo '<a class="card" href ="'.$targetLocation.'?'.http_build_query($data).'">'.$row["namaTugasBesar"].'</a>';
    }
    ?>
</div>
<div>
    <form action="BuatTubes.php" method="GET">
    <input type="hidden" name="kodeMataKuliah" value="<?= $_GET['kodeMataKuliah'] ?>">
    <input type="hidden" name="namaMataKuliah" value="<?= $_GET['namaMataKuliah'] ?>">
    <input type="hidden" name="kelas" value="<?= $_GET['kodeKelas']?>">
    <input type="hidden" name="semester" value="<?= $_GET['semester']?>">
    <?php
        if ($_SESSION["tipePengguna"]=="2"){
        echo '<button type="submit"> Make a New Tubes</button>';
        };
    ?>
    </form>
</div>

</body>
</html>