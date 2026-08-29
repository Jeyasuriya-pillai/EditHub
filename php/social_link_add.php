<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id  = $_SESSION['user_id'];
$platform = trim($_POST['platform'] ?? '');
$url      = trim($_POST['url'] ?? '');

if ($platform !== '' && $url !== '') {
    $stmt = $conn->prepare("INSERT INTO social_links (user_id, platform, url) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $platform, $url);
    $stmt->execute();
    $stmt->close();
}

$conn->close();
header("Location: ../edit_profile.php");
exit();