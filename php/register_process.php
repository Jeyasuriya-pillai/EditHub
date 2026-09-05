<?php
session_start();
require_once 'db.php';

/** @var mysqli $conn */
global $conn;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username     = trim($_POST['username']);
    $password     = $_POST['password'];
    $recovery_key = trim($_POST['recovery_key']);

    if (empty($username) || empty($password) || empty($recovery_key)) {
        die("Please fill all fields.");
    }

    if ($password === $recovery_key) {
        die("<script>alert('Password and Recovery Pass must be different!'); window.history.back();</script>");
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $hashed_recovery = password_hash($recovery_key, PASSWORD_DEFAULT);

    $check_stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $check_stmt->bind_param("s", $username);
    $check_stmt->execute();

    if ($check_stmt->get_result()->num_rows > 0) {
        echo "<script>alert('Username already taken!'); window.history.back();</script>";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (username, password, recovery_key) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $hashed_password, $hashed_recovery);

        if ($stmt->execute()) {
            echo "<script>alert('Registration Successful! Keep your recovery key safe.'); window.location.href='../login.php';</script>";
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    }
    $check_stmt->close();
    $conn->close();
}
?>