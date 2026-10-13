
<?php
session_start();
require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.php?action=signup');
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$recovery_key = trim($_POST['recovery_key'] ?? '');

if ($username === '' || $password === '' || $recovery_key === '') {
    die('Please fill all fields.');
}

if ($password === $recovery_key) {
    die("<script>alert('Password and Recovery Pass must be different!'); history.back();</script>");
}

try {
    $check_stmt = $conn->prepare(
        "SELECT id FROM users WHERE username = ?"
    );
    $check_stmt->bind_param("s", $username);
    $check_stmt->execute();

    if ($check_stmt->get_result()->num_rows > 0) {
        $check_stmt->close();
        echo "<script>alert('Username already taken!'); history.back();</script>";
        exit;
    }
    $check_stmt->close();

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $hashed_recovery = password_hash($recovery_key, PASSWORD_DEFAULT);

    $stmt = $conn->prepare(
        "INSERT INTO users (username, password, recovery_key)
         VALUES (?, ?, ?)"
    );
    $stmt->bind_param(
        "sss",
        $username,
        $hashed_password,
        $hashed_recovery
    );
    $stmt->execute();
    $stmt->close();
    $conn->close();

    echo "<script>
        alert('Registration successful! Keep your recovery key safe.');
        window.location.href = '../login.php';
    </script>";
    exit;

} catch (mysqli_sql_exception $e) {
    error_log('Registration database error: ' . $e->getMessage());
    http_response_code(500);
    exit('Registration failed. Please try again later.');
}
?>
