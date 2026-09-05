<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id       = $_SESSION['user_id'];
$title         = trim($_POST['title'] ?? '');
$description   = trim($_POST['description'] ?? '');
$price         = trim($_POST['price'] ?? '');
$contact_email = trim($_POST['contact_email'] ?? '');

// Update user's contact email if provided
if ($contact_email !== '') {
    $stmtEmail = $conn->prepare("UPDATE users SET contact_email = ? WHERE id = ?");
    $stmtEmail->bind_param("si", $contact_email, $user_id);
    $stmtEmail->execute();
    $stmtEmail->close();
}

// Add service entry
if ($title !== '') {
    $stmt = $conn->prepare("INSERT INTO user_services (user_id, title, description, price) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $title, $description, $price);
    $stmt->execute();
    $stmt->close();
}

$conn->close();
header("Location: ../edit_profile.php");
exit();
?>