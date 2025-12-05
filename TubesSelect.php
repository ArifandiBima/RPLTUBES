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
            <b>Nama:</b> <?php ?><br>
            <b>NIK:</b> 2009181391<br>
            <b>Nama Matkul: </b> MatkulA
        </div>
    </div>

</div>

<div class="container">
    <div class="card">
        AIF12313123
    </div>
    <?php
    $queryTubes = "
        SELECT namaTugasBesar
        FROM tugasBesar
        WHERE kodeMataKuliah = ? AND semester = ? AND kodeKelas = ?
    ";
    $stmt = $conn->prepare($queryTubes);
    $stmt->bind_param("sis", $_GET["kodeMataKuliah"], $_GET["semester"], $_GET["kodeKelas"]);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        echo '<div class="card">'.
            $row["namaTugasBesar"].
            '</div>'
        ;
    }
    ?>
</div>

</body>
</html>