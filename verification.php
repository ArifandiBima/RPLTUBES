<<<<<<< HEAD
<?php 
session_start();
=======
<?php
session_start();

>>>>>>> 29117f52e4cf377569b6e6d0fe975986e375bb66
require_once 'conn.php';

$input_username = $_POST['username'];
$input_password = $_POST['password'];

<<<<<<< HEAD
$sql = "SELECT username, tipePengguna FROM pengguna  
        WHERE username = ? AND pass = ?";

=======
$sql = "SELECT username, tipePengguna FROM pengguna 
        WHERE username = ? AND pass = ?";
        
>>>>>>> 29117f52e4cf377569b6e6d0fe975986e375bb66
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $input_username, $input_password);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    
<<<<<<< HEAD
    $_SESSION['username'] = $user['username'];
    $_SESSION['user_type'] = $user['tipePengguna'];
    $_SESSION['login_time'] = date('Y-m-d H:i:s');

    // Redirect based on user type
    if ($user['tipePengguna'] == 1) {
        header("Location: home.php");       // mahasiswa
    } else {
        header("Location: home_dosen.php"); // dosen
    }
    exit;
} else {
    header("Location: login.html?error=true");
    exit;
}
?>
=======
    $_SESSION['username'] = $row['username'];
    $_SESSION['user_type'] = $row['tipePengguna'];
    $_SESSION['login_time'] = date('Y-m-d H:i:s'); 

} else {
    header("Location: login.html?error=true");
}

$stmt->close();
$conn->close();
<<<<<<< HEAD
?>
=======
?>
>>>>>>> 76d24fb (update nilai_mahasiswa dan nilai_dosen)
>>>>>>> 29117f52e4cf377569b6e6d0fe975986e375bb66
