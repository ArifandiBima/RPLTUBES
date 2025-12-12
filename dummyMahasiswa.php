<?php
session_destroy();
session_start();
$_SESSION["npm"] = "2200000001";
$_SESSION["tipePengguna"]="3";
$_SESSION["nama"]="Budi Santoso";
$data = array(
    'namaMataKuliah' => 'Algoritma',
    'kodeMataKuliah' => 'IF101',
    'kodeKelas'   => 'A',
    'semester'       => 1,
    'namaTugasBesar' => "Project Algoritma"

);
$location_url = "mahasiswa_pilih_kelompok_controller.php?" . http_build_query($data);
header("Location: ".$location_url);
exit();
?>