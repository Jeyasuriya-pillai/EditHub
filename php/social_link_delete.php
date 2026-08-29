<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$link_id = intval($_GET['id'] ?? 0);

// Ownership check - user apni hi link delete kar sakta hai
$stmt = $conn->prepare("DELETE FROM social_links WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $link_id, $user_id);
$stmt->execute();
$stmt->close();
$conn->close();

header("Location: ../edit_profile.php");
exit();