<?php
    if(!isset($_SESSION['status']) || $_SESSION['status'] != "login"){
        header("Location: ../login.html");
        exit();
    }

    include '../config/conn.php';

    if(isset($_POST['reset_login'])){
        $target_user = $_POST['username_target'];
        $password_baru = $_POST['password_baru'];

        if(!empty($target_user) && !empty($password_baru))
        $sql = "update pengguna set pass = '$password_baru' where username ='$target_user";

        if(mysqli_query($conn, $sql)){
            echo "<script>
                    alert('Password $target_user berhasil diganti')
                </script>";
        }
        else{
            echo "Eror" . mysqli_error(($conn));
        }
    }
    else{
        echo "<script>
                alert('Username dan password tidak boleh kosong')        
            </script>";
    }
?>  
<!DOCTYPE html>
<html>
    <head>
        <title>Kelola User</title>
        <link href="../assets/kelola_user.css" rel="stylesheet">
    </head>
        
    <body>

        <a href="index.php" class="btn-back">⮌</a>

        <div class="form-container">
            
            <form method="POST" action="">
                
                <div class="form-row">
                    
                    <div class="dropdown-group">
                        <select name="pilih_user" onchange="document.getElementById('inputUser').value = this.value">
                            <option value="">-- Pilih User --</option>
                            <?php
                                $query = "SELECT username, tipePengguna FROM pengguna";
                                $result = mysqli_query($conn, $query);
                                while($row = mysqli_fetch_assoc($result)) {
                                    $role = ($row['tipePengguna'] == 2) ? "Dosen" : "Mhs";
                                    echo "<option value='".$row['username']."'>".$row['username']." ($role)</option>";
                                }
                            ?>
                        </select>
                    </div>

                    <div class="input-group">
                        <label>Username Target:</label>
                        <input type="text" id="inputUser" name="username_target" placeholder="Pilih/Ketik username" required>
                    </div>

                    <div class="input-group">
                        <label>Password Baru:</label>
                        <input type="text" name="password_baru" placeholder="Ketik password baru" required>
                    </div>

                </div>

                <div class="button-row">
                    <button type="submit" name="reset_login" id="res_log">Ubah Password</button>
                </div>

            </form>

        </div>

    </body>
</html>