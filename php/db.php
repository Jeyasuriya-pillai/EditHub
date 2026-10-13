
<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$host = getenv('MYSQL_ADDON_HOST');
$port = (int) getenv('MYSQL_ADDON_PORT');
$user = getenv('MYSQL_ADDON_USER');
$password = getenv('MYSQL_ADDON_PASSWORD');
$database = getenv('MYSQL_ADDON_DB');

$conn = mysqli_init();

if (!$conn) {
    die('Database connection initialization failed.');
}

mysqli_real_connect(
    $conn,
    $host,
    $user,
    $password,
    $database,
    $port
);

mysqli_set_charset($conn, 'utf8mb4');
?>
