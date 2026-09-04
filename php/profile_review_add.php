<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reviewer_id = $_SESSION['user_id'];
    $profile_id  = intval($_POST['profile_id'] ?? 0);
    $rating      = intval($_POST['rating'] ?? 0);
    $comment     = trim($_POST['comment'] ?? '');

    // Validations
    if ($profile_id <= 0 || $rating < 1 || $rating > 5) {
        die("Invalid review data.");
    }

    // Khud ko review nahi dene dena
    if ($reviewer_id === $profile_id) {
        header("Location: ../view_profile.php?user_id=" . $profile_id . "&error=self_review");
        exit();
    }

    // Pehle se review diya h kya check karo (Update or Insert)
    $stmt = $conn->prepare("SELECT id FROM profile_reviews WHERE profile_id = ? AND reviewer_id = ?");
    $stmt->bind_param("ii", $profile_id, $reviewer_id);
    $stmt->execute();
    $existing = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($existing) {
        // Update existing review
        $stmt = $conn->prepare("UPDATE profile_reviews SET rating = ?, comment = ?, created_at = NOW() WHERE id = ?");
        $stmt->bind_param("isi", $rating, $comment, $existing['id']);
    } else {
        // Insert new review
        $stmt = $conn->prepare("INSERT INTO profile_reviews (profile_id, reviewer_id, rating, comment) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiis", $profile_id, $reviewer_id, $rating, $comment);
    }

    if ($stmt->execute()) {
        $stmt->close();
        header("Location: ../view_profile.php?user_id=" . $profile_id . "&success=reviewed");
        exit();
    } else {
        die("Error saving review.");
    }
} else {
    header("Location: ../home.php");
    exit();
}