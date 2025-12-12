<?php require "conn.php";
session_start();
if ($_SESSION["tipePengguna"]==2){
    $id = $_SESSION["nik"];
}
else{
    $id = $_SESSION["npm"];
}
$nextTarget = "TubesSelect.php";
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
            <b>NIK:</b> <?php echo $id;?>
        </div>
    </div>

</div>
<div id="year-container">
    <form class = "year-select-form" action="matkul.php" method="GET">

        <select class="year-select" onchange="this.form.submit();">
        <?php
    $semester=0;
    if ($_SESSION["tipePengguna"]==3)
        $querySemester = "
    SELECT DISTINCT semester
    FROM peserta
    WHERE npmPeserta = ?
    ORDER BY semester
    ";
    else if ($_SESSION["tipePengguna"]==2)
        $querySemester = "
    SELECT DISTINCT semester
    FROM pengampu
    WHERE nikPengampu = ? 
    ORDER BY semester
    ";
    else{
        $querySemester = "
        SELECT DISTINCT semester
        FROM kelas
        ORDER BY semester
        ";
    }
    $stmt = $conn->prepare($querySemester);
    $stmt->bind_param("s", $id);
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
    </form>
    </select>
</div>

<div class="container">
    <?php
    if ($_SESSION["tipePengguna"]==3){
        $queryMatkul = "
            SELECT kodeMataKuliah, namaMataKuliah
            FROM MataKuliah
            WHERE kodeMataKuliah in (
                SELECT kodeMataKuliah
                FROM peserta
                WHERE npmPeserta = ?  and semester = ?
            
            );
        ";
            
    }
    else if ($_SESSION["tipePengguna"]==2)
        $queryMatkul = "
            SELECT kodeMataKuliah, namaMataKuliah
            FROM MataKuliah
            WHERE kodeMataKuliah in (
                SELECT kodeMataKuliah
                FROM pengampu
                WHERE nikPengampu = ?  and semester = ?
            
            );
        ";
    else{
        $queryMatkul = "
            SELECT kodeMataKuliah, namaMataKuliah
            FROM MataKuliah
            WHERE kodeMataKuliah in (
                SELECT kodeMataKuliah
                FROM peserta
                WHERE ((npmPeserta = ?) OR True)  and semester = ?
            
            );
        ";
    }
    $stmt = $conn->prepare($queryMatkul);
    $stmt->bind_param("si", $id, $semester);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = array(
        'namaMataKuliah' => 'Algoritma',
        'kodeMataKuliah' => 'IF101',
        'kodeKelas'   => 'A',
        'semester'       => 1
    );
    while ($row = $result->fetch_assoc()) {
        echo '<a href = "'.$nextTarget.'?'.http_build_query($data).'"><div class="card">'.
        $row["kodeMataKuliah"].'<br>'.
        $row["namaMataKuliah"].'</div></a>';
    }
    
    ?>
</div>
        
</body>
</html>