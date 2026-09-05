<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id  = $_SESSION['user_id'];
$title    = trim($_POST['title'] ?? '');
$category = trim($_POST['category'] ?? 'PNG');

if ($title === '' || !isset($_FILES['material_file']) || $_FILES['material_file']['error'] !== UPLOAD_ERR_OK) {
    die("Please provide a title and select a valid file. <a href='../edit_profile.php'>Go back</a>");
}

$uploadDir = dirname(__DIR__) . '/uploads/materials/';
if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
    die("Could not create 'uploads/materials/' folder. Create it manually. <a href='../edit_profile.php'>Go back</a>");
}
if (!is_writable($uploadDir)) {
    die("Upload folder 'uploads/materials/' is not writable. <a href='../edit_profile.php'>Go back</a>");
}

$allowedExt = ['zip', 'rar', '7z', 'mp4', 'mov', 'avi', 'mkv', 'webm', 'png', 'jpg', 'jpeg', 'gif', 'webp', 'svg', 'psd', 'ai', 'pdf', 'wav', 'mp3', 'aac', 'flac', 'ogg', 'm4a', 'aif', 'aiff', 'wma', 'opus', 'oga', 'cube', 'mogrt', '3dl', 'lut', 'ttf', 'otf', 'aep', 'prproj', 'drp', 'json', 'xml', 'srt', 'ass', 'csv'];
$ext = strtolower(pathinfo($_FILES['material_file']['name'], PATHINFO_EXTENSION));
if (!in_array($ext, $allowedExt)) {
    die("File type '.$ext' not allowed. Allowed: " . implode(', ', $allowedExt) . " <a href='../edit_profile.php'>Go back</a>");
}

$safeName    = uniqid('material_') . '.' . $ext;
$destination = $uploadDir . $safeName;
if (!move_uploaded_file($_FILES['material_file']['tmp_name'], $destination)) {
    die("File upload failed. Check folder permissions. <a href='../edit_profile.php'>Go back</a>");
}
$filePathForDb = 'uploads/materials/' . $safeName;

// Optional preview image
$thumbPathForDb = null;
if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
    $imgExt = strtolower(pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION));
    $allowedImg = ['png', 'jpg', 'jpeg', 'webp'];
    if (in_array($imgExt, $allowedImg)) {
        $imgName = uniqid('preview_') . '.' . $imgExt;
        $imgDest = $uploadDir . $imgName;
        if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $imgDest)) {
            $thumbPathForDb = 'uploads/materials/' . $imgName;
        }
    }
}

$stmt = $conn->prepare("INSERT INTO user_materials (user_id, title, category, file_path, thumbnail) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("issss", $user_id, $title, $category, $filePathForDb, $thumbPathForDb);
if (!$stmt->execute()) {
    die("Database insert failed: " . $stmt->error . " <a href='../edit_profile.php'>Go back</a>");
}
$stmt->close();
$conn->close();

header("Location: ../edit_profile.php?material_uploaded=1");
exit();