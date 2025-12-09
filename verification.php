<?php 
session_start();
require_once 'conn.php';

$input_username = $_POST['username'];
$input_password = $_POST['password'];

$sql = "SELECT username, tipePengguna FROM pengguna  
        WHERE username = ? AND pass = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $input_username, $input_password);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    
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
