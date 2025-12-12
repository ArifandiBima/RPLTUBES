
<?php
session_destroy();
session_start();
$_SESSION["nik"] = "1980010123456789";
$_SESSION["tipePengguna"]="2";
$_SESSION["nama"]="Budi Santoso";
$data = array(
    'namaMataKuliah' => 'Algoritma',
    'kodeMataKuliah' => 'IF101',
    'kodeKelas'   => 'A',
    'semester'       => 1,
    'namaTugasBesar' => "Project Algoritma"
);
$location_url = "dosen_edit_kelompok_controller.php?" . http_build_query($data);
header("Location: ".$location_url);
exit();
?>