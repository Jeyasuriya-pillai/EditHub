<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("UPDATE users SET contact_email = NULL WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->close();
$conn->close();

header("Location: ../edit_profile.php?email_removed=1");
exit();