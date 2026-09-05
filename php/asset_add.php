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
    die("Please provide a title and select a valid file. <a href='../edit_profile.php'>Go back</a>");
}

$uploadDir = dirname(__DIR__) . '/uploads/assets/';
if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
    die("Could not create 'uploads/assets/' folder. Create it manually. <a href='../edit_profile.php'>Go back</a>");
}
if (!is_writable($uploadDir)) {
    die("Upload folder 'uploads/assets/' is not writable. <a href='../edit_profile.php'>Go back</a>");
}

$allowedExt = ['zip', 'rar', '7z', 'mp4', 'mov', 'avi', 'mkv', 'webm', 'png', 'jpg', 'jpeg', 'gif', 'webp', 'svg', 'psd', 'ai', 'pdf', 'wav', 'mp3', 'aac', 'flac', 'ogg', 'm4a', 'aif', 'aiff', 'wma', 'opus', 'oga', 'cube', 'mogrt', '3dl', 'lut', 'ttf', 'otf', 'aep', 'prproj', 'drp', 'json', 'xml', 'srt', 'ass', 'csv'];
$ext = strtolower(pathinfo($_FILES['asset_file']['name'], PATHINFO_EXTENSION));
if (!in_array($ext, $allowedExt)) {
    die("File type '.$ext' not allowed. Allowed: " . implode(', ', $allowedExt) . " <a href='../edit_profile.php'>Go back</a>");
}

$safeName    = uniqid('asset_') . '.' . $ext;
$destination = $uploadDir . $safeName;
if (!move_uploaded_file($_FILES['asset_file']['tmp_name'], $destination)) {
    die("File upload failed. Check folder permissions. <a href='../edit_profile.php'>Go back</a>");
}
$filePathForDb = 'uploads/assets/' . $safeName;

// Optional preview image
$thumbPathForDb = null;
if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
    $imgExt = strtolower(pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION));
    $allowedImg = ['png', 'jpg', 'jpeg', 'webp'];
    if (in_array($imgExt, $allowedImg)) {
        $imgName = uniqid('preview_') . '.' . $imgExt;
        $imgDest = $uploadDir . $imgName;
        if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $imgDest)) {
            $thumbPathForDb = 'uploads/assets/' . $imgName;
        }
    }
}

$stmt = $conn->prepare("INSERT INTO user_assets (user_id, title, type, price, file_path, thumbnail) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("isssss", $user_id, $title, $type, $price, $filePathForDb, $thumbPathForDb);
if (!$stmt->execute()) {
    die("Database insert failed: " . $stmt->error . " <a href='../edit_profile.php'>Go back</a>");
}
$stmt->close();
$conn->close();

header("Location: ../edit_profile.php?asset_uploaded=1");
exit();