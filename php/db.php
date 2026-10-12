
<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$host = getenv('DB_HOST');
$port = (int) getenv('DB_PORT');
$user = getenv('DB_USER');
$password = getenv('DB_PASSWORD');
$database = getenv('DB_NAME');

// CA certificate is stored in the project root's certs folder.
$ca = __DIR__ . '/../certs/ca.pem';

$conn = mysqli_init();

if (!$conn) {
    die('Database connection initialization failed.');
}

mysqli_ssl_set($conn, null, null, $ca, null, null);

mysqli_real_connect(
    $conn,
    $host,
    $user,
    $password,
    $database,
    $port,
    null,
    MYSQLI_CLIENT_SSL
);

mysqli_set_charset($conn, 'utf8mb4');
?>
