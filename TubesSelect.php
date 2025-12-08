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
    <div class="back-btn">⮌</div>

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
    <div class = "card">
        Pembuatan Keju
    </div>
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

    while ($row = $result->fetch_assoc()) {
        echo '<div class="card">'.$row["namaTugasBesar"].'</div>';
    }
    ?>
</div>
<?php
if ($_SESSION["tipePengguna"]="dosen"){
    echo '<button type="button"> Make a New Tubes</button>';
}
?>

</body>
</html>