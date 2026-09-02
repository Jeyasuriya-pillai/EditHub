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
    die("Please provide a title and select a valid file. (Upload error code: " . ($_FILES['asset_file']['error'] ?? 'no file') . ") <a href='../edit_profile.php'>Go back</a>");
}

// Absolute Path Setup
$docRoot       = rtrim($_SERVER['DOCUMENT_ROOT'], '/\\');
$baseUploadDir = $docRoot . '/EditHub/uploads/';
$uploadDir     = $baseUploadDir . 'assets/';

if (!is_dir($baseUploadDir)) {
    @mkdir($baseUploadDir, 0777, true);
}

if (!is_dir($uploadDir)) {
    @mkdir($uploadDir, 0777, true);
}

if (!is_dir($uploadDir)) {
    die("Upload directory does not exist. <a href='../edit_profile.php'>Go back</a>");
}

if (!is_writable($uploadDir)) {
    die("Upload folder 'uploads/assets/' is not writable. Give it write permission. <a href='../edit_profile.php'>Go back</a>");
}

$allowedExt = ['zip', 'rar', 'mp4', 'mov', 'png', 'jpg', 'jpeg', 'wav', 'mp3', 'cube', 'mogrt'];
$ext = strtolower(pathinfo($_FILES['asset_file']['name'], PATHINFO_EXTENSION));

if (!in_array($ext, $allowedExt)) {
    die("File type '.$ext' not allowed. Allowed: " . implode(', ', $allowedExt) . " <a href='../edit_profile.php'>Go back</a>");
}

$safeName    = uniqid('asset_') . '.' . $ext;
$destination = $uploadDir . $safeName;

if (!move_uploaded_file($_FILES['asset_file']['tmp_name'], $destination)) {
    die("File upload failed (could not move file to '$destination'). Check folder permissions. <a href='../edit_profile.php'>Go back</a>");
}

$filePathForDb = 'uploads/assets/' . $safeName;
$stmt = $conn->prepare("INSERT INTO user_assets (user_id, title, type, price, file_path) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("issss", $user_id, $title, $type, $price, $filePathForDb);

if (!$stmt->execute()) {
    die("Database insert failed: " . $stmt->error . " <a href='../edit_profile.php'>Go back</a>");
}
$stmt->close();
$conn->close();

// Changed redirection to Home page (index.php)
header("Location: ../index.php?asset_uploaded=1");
exit();