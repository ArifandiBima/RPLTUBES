<?php
// include somthing

$role = $_SESSION['role'];
$username = $_SESSION['username'];
?>
    <!DOCTYPE html>
    <html>
        <head>
            <title>Dasboard <?php echo ($role == 1) ? "admin" : "dosen" ?></title>
            <?php if ($role == 1): ?>
                <link href="../assets/admin.css" rel="stylesheet">
            <?php else: ?>
                <link href="../assets/dosen.css" rel="stylesheet">
            <?php endif; ?>
        </head>
        <body>
            <?php if($role == 1):?>
            <div id="btnBack">
                <p> <a href="logout.php">⮌</a></p>
            </div>

            <div id="container">    
                <div>
                    <button id="expExcel"><a href="convert.php">Export excel</a></button>
                </div>
                <div id="subBtn">
                    <div id="btnUser">
                        <button><a href="kelola_user.php">Kelola Pengguna</a></button>
                    </div>

                    <div id="btnSemester">
                        <button><a href="kelola_semester.php">Kelola Semester</a></button>
                    </div>
                </div>
            </div>

            <?php elseif ($role == 2): ?>
            <div id="navbar">
                <p> <a href="login.html">⮌</a></p>
                <!-- <P>isi dengan php untuk mengambil nama matkul mengikuti session</p> --><br>
                <!-- <P>isi dengan php untuk mengambil kode matakuliah mengikuti session</p> -->
            </div>

            <p>Profile</p>
            
            <!-- <P>isi dengan php untuk mengambil tugas besar dari session saat ini</p> -->
            
            <div id="btn-group">
                  <div id="btn-edit-kelompok">
                        <button><a href="kelola_user.php">Edit kelompok</a></button>
                    </div>

                    <div id="btn-edit-rubrik">
                        <button><a href="rubrik.php">Edit Rubrik</a></button>
                    </div>

                    <div id="btn-edit-nilai">
                        <button><a href="nilai_dosen.php">Edit Nilai</a></button>
                    </div>
            </div>
            </div>
        </body>
    </html>