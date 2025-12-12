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

    $_SESSION['username']  = $user['username'];
    $_SESSION['tipePengguna'] = $user['tipePengguna'];
    $_SESSION['login_time'] = date('Y-m-d H:i:s');
    $_SESSION['nama'] = $user['username'];
    if ($user['tipePengguna'] == 1) {
        header("Location: admin/admin.php");
    } else {
        if ($user['tipePengguna'] == 2) {
            $sql = "SELECT nama, nik FROM dosen  
                    WHERE username = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $input_username);
            $stmt->execute();
            $result = $stmt->get_result();

            $dosen = $result->fetch_assoc();
            $_SESSION['tipePengguna'] = $user['tipePengguna'];
            $_SESSION['nik'] = $dosen['nik'];
            $_SESSION['nama'] = $dosen['nama'];
        } else{
            $sql = "SELECT nama, npm FROM mahasiswa  
                    WHERE username = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $input_username);
            $stmt->execute();
            $result = $stmt->get_result();

            $dosen = $result->fetch_assoc();
            $_SESSION['tipePengguna'] = $user['tipePengguna'];
            $_SESSION['npm'] = $dosen['npm'];
            $_SESSION['nama'] = $dosen['nama'];
        }

        header("Location: matkul.php");
    } 
    exit;

} else {
    header("Location: login.html?error=true");
    exit;
}
?>
