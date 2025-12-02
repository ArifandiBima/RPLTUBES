<?php
$npm = $_POST['npm'];
$komponen = $_POST['komponen'];
$komentar = $_POST['komentar'];

// contoh simpan ke DB
// mysqli_query($conn,
//   "UPDATE nilai SET komentar='$komentar'
//    WHERE npm='$npm' AND komponen='$komponen'");

echo "Komentar berhasil disimpan!<br><br>";
echo "<a href='nilai_dosen.php'>Kembali</a>";
?>
