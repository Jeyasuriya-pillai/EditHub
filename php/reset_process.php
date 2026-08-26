<?php
session_start();
require_once 'db.php';

/** @var mysqli $conn */
global $conn;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username     = trim($_POST['username']);
    $recovery_key = trim($_POST['recovery_key']);
    $new_password = $_POST['new_password'];

    if (empty($username) || empty($recovery_key) || empty($new_password)) {
        die("Please fill all fields.");
    }

    $stmt = $conn->prepare("SELECT id, recovery_key FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        if (password_verify($recovery_key, $user['recovery_key'])) {
            $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update_stmt->bind_param("si", $hashed_new_password, $user['id']);
            $update_stmt->execute();
            
            echo "<script>alert('Password Reset Successful! Now login with your new password.'); window.location.href='../login.html';</script>";
            $update_stmt->close();
        } else {
            echo "<script>alert('Incorrect Recovery Phrase!'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('Username not found!'); window.history.back();</script>";
    }

    $stmt->close();
    $conn->close();
}
?>