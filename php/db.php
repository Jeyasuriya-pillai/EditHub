<?php
// Ye line har mysqli error ko turant exception ke roop mein dikhayegi (debugging ke liye)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$servername = "127.0.0.1";
$port       = 3307;
$username   = "root";
$password   = "";
$dbname     = "edithub";

$conn = mysqli_connect($servername, $username, $password, $dbname, $port);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>