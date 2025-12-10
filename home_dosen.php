<?php session_start(); ?>
<h1>Welcome, Dosen: <?= $_SESSION['username'] ?></h1>
<a href="matkul.php">Lihat Mata Kuliah</a>