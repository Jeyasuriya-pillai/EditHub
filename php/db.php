
<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$host = getenv('MYSQL_ADDON_HOST');
$port = (int) (getenv('MYSQL_ADDON_PORT') ?: 3306);
$user = getenv('MYSQL_ADDON_USER');
$password = getenv('MYSQL_ADDON_PASSWORD');
$database = getenv('MYSQL_ADDON_DB');

if (!$host || !$user || !$password || !$database) {
    error_log('Database configuration missing: check Clever Cloud MySQL environment variables.');
    http_response_code(500);
    exit('Database configuration is missing. Please try again later.');
}

$conn = mysqli_init();

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
