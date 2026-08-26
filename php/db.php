<?php
$servername = "localhost:3307"; // ya 127.0.0.1:3307
$username   = "root";
$password   = "";
$dbname     = "edithub";

// Variable ka naam $conn hona chahiye
$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>