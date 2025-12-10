    <?php
        // session_start();

        // if(!isset($_SESSION['status']) || $_SESSION['status'] != "login"){
        //     header("Location: ../login.html");
        //     exit();
        // }

        // if($_SESSION['role'] != 1){
        //     echo "<script>
        //             alert('Tidak punya akses ke halaman admin!');
        //             window.location.href = 'login.html'
        //         </script>";
        //     exit();
        // }
    ?>
    <!DOCTYPE html>
    <html>
        <head>
            <title>Admin page</title>
            <link href="../assets/admin.css"rel="stylesheet">
        </head>
        <body>
            <div id="btnBack">
                <p> <a href="../login.html">⮌</a></p>
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
        </body>
    </html>