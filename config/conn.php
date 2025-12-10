<?php
$serverName = "localhost";
$username   = "root";
$password   = "";
$database   = "projectrpl";

// Create connection
$conn = mysqli_connect($serverName, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>  