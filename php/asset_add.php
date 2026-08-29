<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$title   = trim($_POST['title'] ?? '');
$type    = ($_POST['type'] ?? 'free') === 'paid' ? 'paid' : 'free';
$price   = ($type === 'paid') ? trim($_POST['price'] ?? '') : null;

if ($title === '' || !isset($_FILES['asset_file']) || $_FILES['asset_file']['error'] !== UPLOAD_ERR_OK) {
    die("Please provide a title and select a valid file. <a href='../profile.php'>Go back</a>");
}

$uploadDir = '../uploads/assets/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$allowedExt = ['zip', 'rar', 'mp4', 'mov', 'png', 'jpg', 'jpeg', 'wav', 'mp3', 'cube', 'mogrt'];
$ext = strtolower(pathinfo($_FILES['asset_file']['name'], PATHINFO_EXTENSION));

if (!in_array($ext, $allowedExt)) {
    die("File type not allowed. <a href='../profile.php'>Go back</a>");
}

$safeName    = uniqid('asset_') . '.' . $ext;
$destination = $uploadDir . $safeName;

if (move_uploaded_file($_FILES['asset_file']['tmp_name'], $destination)) {
    $filePathForDb = 'uploads/assets/' . $safeName;
    $stmt = $conn->prepare("INSERT INTO user_assets (user_id, title, type, price, file_path) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $user_id, $title, $type, $price, $filePathForDb);
    $stmt->execute();
    $stmt->close();
}

$conn->close();
header("Location: ../edit_profile.php");
exit();