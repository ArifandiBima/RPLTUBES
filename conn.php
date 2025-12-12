<?php
$serverName = "localhost";
$username   = "root";
$password   = "";
<<<<<<< HEAD
$database   = "dbrpl";
=======
$database   = "rpl_test";
>>>>>>> 38493eba33db5d4095c3f5550699c3ae35a41754

// Create connection
$conn = mysqli_connect($serverName, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>