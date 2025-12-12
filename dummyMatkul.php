<?php
session_start();
$_SESSION["nik"] = "1980010123456789";
$_SESSION["tipePengguna"]="2";
$_SESSION["nama"]="Dr. Andri Wijaya";
header("Location: matkul.php");
?>