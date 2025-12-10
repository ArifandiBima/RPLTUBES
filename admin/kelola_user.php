<?php
    session_start();
    include '../config/conn.php'; 

    if(isset($_POST['reset_login'])){
        
        $target_user = mysqli_real_escape_string($conn, $_POST['username_target']);
        $password_baru = mysqli_real_escape_string($conn, $_POST['password_baru']);

        if(!empty($target_user) && !empty($password_baru)){
            
            // 1. CEK DULU
            $cek_user = mysqli_query($conn, "SELECT * FROM pengguna WHERE username='$target_user'");
            
            if(mysqli_num_rows($cek_user) > 0){
                
                // 2. UPDATE
                $sql = "UPDATE pengguna SET pass = '$password_baru' WHERE username = '$target_user'";

                if(mysqli_query($conn, $sql)){
                    echo "<script>
                            alert('SUKSES! Password untuk user $target_user berhasil diubah.');
                            window.location.href = 'kelola_user.php'; // <--- INI KUNCINYA
                          </script>";
                } else {
                    echo "<script>
                            alert('Gagal update: ".mysqli_error($conn)."');
                            window.location.href = 'kelola_user.php'; // Redirect juga biar bersih
                          </script>";
                }
                
            } else {
                echo "<script>
                        alert('GAGAL! Username \"$target_user\" tidak ditemukan di database.');
                        window.location.href = 'kelola_user.php'; // Redirect biar inputan 'a' hilang
                      </script>";
            }

        } else {
            echo "<script>
                    alert('Username dan password baru tidak boleh kosong!');
                    window.location.href = 'kelola_user.php';
                  </script>";
        }
    }
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Kelola User</title>
        <link href="../assets/kelola_user.css" rel="stylesheet">
    </head>
        
    <body>
<body>

    <a href="admin.php" class="btn-back">⮌</a>

    <div class="form-container">
        
        <h3 style="text-align:center; color:#333; margin-top:0;">Reset Password</h3>

        <form method="POST" action="">
            
            <div class="form-row">
                
                <div class="input-group">
                    <label>Username Target:</label>
                    <input type="text" id="inputUser" name="username_target" placeholder="Contoh: 19900101" required>
                </div>

                <div class="input-group">
                    <label>Password Baru:</label>
                    <input type="text" name="password_baru" placeholder="Masukkan password baru..." required>
                </div>

            </div>

            <div class="button-row">
                <button type="submit" name="reset_login" id="res_log">Simpan Perubahan</button>
            </div>

        </form>

    </div>

</body>
</html>