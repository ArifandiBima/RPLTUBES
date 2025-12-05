<?php require "conn.php";
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href = "matkulStyles.css" rel="stylesheet">
    <title>Document</title>
</head>
<body>

<div class="header">
    <div class="back-btn">⮌</div>

    <div class="profile-card">
        <div class="profile-icon"></div>
        <div class="profile-text">
            <b>Nama:</b> <?php echo$_SESSION["nama"]?><br>
            <b>NIK:</b> <?php echo $_SESSION["npm"];?>
        </div>
    </div>

</div>
<div id="year-container">
    <select class="year-select">
    <?php
    $semester=0;
    $querySemester = "
        SELECT DISTINCT semester
        FROM peserta
        WHERE npmPeserta = ?
        ORDER BY semester
    ";
    $stmt = $conn->prepare($querySemester);
    $stmt->bind_param("s", $_SESSION["npm"]);
    $stmt->execute();
    $result = $stmt->get_result();
    if (isset($_GET["semester"])) {
        $semester = $GET["semester"];
    }
    while ($row = $result->fetch_assoc()) {
        if ($semester==0) $semester = $row["semester"];
        if ($semester==$row["semester"]) echo"<option selected>";
        else echo"<option>";
        echo $row["semester"]."</option>";
    }
    ?>
    </select>
</div>

<div class="container">
    <?php
    $queryMatkul = "
        SELECT kodeMataKuliah, namaMataKuliah
        FROM MataKuliah
        WHERE kodeMataKuliah in (
            SELECT kodeMataKuliah
            FROM peserta
            WHERE npmPeserta = ?  and semester = ?
        
        );
    ";
    $stmt = $conn->prepare($queryMatkul);
    $stmt->bind_param("si", $_SESSION["npm"], $semester);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        echo '<div class="card">'.
        $row["kodeMataKuliah"].'<br>'.
        $row["namaMataKuliah"].'</div>';
    }
    
    ?>
</div>

</body>
</html>