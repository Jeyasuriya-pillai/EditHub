<?php
session_start();
require_once 'db.php';

/** @var mysqli $conn */
global $conn;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        die("Please fill all fields.");
    }

    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            // Security fix: Regenerate session ID upon login
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            
            echo "<script>alert('Login Successful!'); window.location.href='../index.php';</script>";
            exit();
        } else {
            echo "<script>alert('Incorrect Password!'); window.history.back();</script>";
            exit();
        }
    } else {
        echo "<script>alert('Username not registered!'); window.history.back();</script>";
        exit();
    }

    $stmt->close();
    $conn->close();
}
?>