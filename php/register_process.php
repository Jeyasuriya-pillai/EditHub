
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

// Validate input
if ($username === '' || $password === '' || $recovery_key === '') {
    echo "<script>
        alert('Please fill all fields.');
        history.back();
    </script>";
    exit;
}

// Password and recovery key must be different
if ($password === $recovery_key) {
    echo "<script>
        alert('Password and Recovery Pass must be different!');
        history.back();
    </script>";
    exit;
}

try {
    // Check database connection
    if (!isset($conn) || !($conn instanceof mysqli)) {
        throw new RuntimeException('Database connection is unavailable.');
    }

    // Check whether username already exists
    $check_stmt = $conn->prepare(
        "SELECT id FROM users WHERE username = ? LIMIT 1"
    );

    $check_stmt->bind_param("s", $username);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        $check_stmt->close();
        $conn->close();

        echo "<script>
            alert('Username already taken!');
            history.back();
        </script>";
        exit;
    }

    $check_stmt->close();

    // Hash password and recovery key
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $hashed_recovery = password_hash($recovery_key, PASSWORD_DEFAULT);

    if ($hashed_password === false || $hashed_recovery === false) {
        throw new RuntimeException('Unable to secure registration credentials.');
    }

    // Insert new user
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

    // Registration successful
    echo "<script>
        alert('Registration successful! Keep your recovery key safe.');
        window.location.href = '../login.php';
    </script>";
    exit;

} catch (Throwable $e) {
    // Record detailed error in server logs
    error_log(
        'EditHub registration error: ' .
        get_class($e) . ': ' . $e->getMessage()
    );

    http_response_code(500);

    // Temporary debugging message: remove after fixing the issue
    echo 'Registration error: ' .
        htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');

    exit;
}
?>
