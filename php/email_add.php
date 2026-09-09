<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id       = $_SESSION['user_id'];
$contact_email = trim($_POST['contact_email'] ?? '');

if ($contact_email === '' || !filter_var($contact_email, FILTER_VALIDATE_EMAIL)) {
    die("Please enter a valid email address. <a href='../edit_profile.php'>Go back</a>");
}

$stmt = $conn->prepare("UPDATE users SET contact_email = ? WHERE id = ?");
$stmt->bind_param("si", $contact_email, $user_id);
$stmt->execute();
$stmt->close();
$conn->close();

header("Location: ../edit_profile.php?email_updated=1");
exit();