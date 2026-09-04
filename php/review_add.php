<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id     = $_SESSION['user_id'];
$target_type = $_POST['target_type'] ?? '';
$target_id   = intval($_POST['target_id'] ?? 0);
$rating      = max(1, min(5, intval($_POST['rating'] ?? 5)));
$comment     = trim($_POST['comment'] ?? '');

$allowedTypes = ['asset', 'material', 'service'];
if (!in_array($target_type, $allowedTypes) || $target_id <= 0) {
    die("Invalid review submission. <a href='../home.php'>Go home</a>");
}

$stmt = $conn->prepare("INSERT INTO reviews (target_type, target_id, user_id, rating, comment) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("siiis", $target_type, $target_id, $user_id, $rating, $comment);
$stmt->execute();
$stmt->close();
$conn->close();

header("Location: ../review.php?type=" . urlencode($target_type) . "&id=" . $target_id);
exit();