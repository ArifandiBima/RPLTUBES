<?php session_start(); ?>
<h1>Welcome, Mahasiswa: <?= $_SESSION['username'] ?></h1>
<a href="nilai_mahasiswa.php">Lihat Nilai</a>
