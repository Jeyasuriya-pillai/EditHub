<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id   = $_SESSION['user_id'];
$full_name = trim($_POST['full_name'] ?? '');
$gender    = $_POST['gender'] ?? '';
$bio       = trim($_POST['bio'] ?? '');
$tag       = $_POST['tag'] ?? 'normal';

$allowed_gender = ['male', 'female'];
$gender = in_array($gender, $allowed_gender) ? $gender : null;

$allowed_tags = ['normal', 'creator', 'editor'];
$tag = in_array($tag, $allowed_tags) ? $tag : 'normal';

$stmt = $conn->prepare("UPDATE users SET full_name = ?, gender = ?, bio = ?, tag = ? WHERE id = ?");
$stmt->bind_param("ssssi", $full_name, $gender, $bio, $tag, $user_id);
$stmt->execute();
$stmt->close();
$conn->close();

header("Location: ../edit_profile.php?updated=1");
exit();