<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id   = $_SESSION['user_id'];
$review_id = intval($_GET['id'] ?? $_POST['id'] ?? 0);

if ($review_id <= 0) {
    die("Invalid request. <a href='../home.php'>Go home</a>");
}

// First fetch the review to check ownership and get target info for redirection
$stmt = $conn->prepare("SELECT target_type, target_id, user_id FROM reviews WHERE id = ?");
$stmt->bind_param("i", $review_id);
$stmt->execute();
$result = $stmt->get_result();
$review = $result->fetch_assoc();
$stmt->close();

if (!$review) {
    die("Review not found. <a href='../home.php'>Go home</a>");
}

// Security Check: Only allow the author of the review to delete it
if ($review['user_id'] != $user_id) {
    die("Unauthorized action. You can only delete your own reviews.");
}

// Delete review from database
$delStmt = $conn->prepare("DELETE FROM reviews WHERE id = ? AND user_id = ?");
$delStmt->bind_param("ii", $review_id, $user_id);
$delStmt->execute();
$delStmt->close();

$conn->close();

// Redirect back to the review page with 'from=home' parameter
header("Location: ../review.php?type=" . urlencode($review['target_type']) . "&id=" . $review['target_id'] . "&from=home");
exit();