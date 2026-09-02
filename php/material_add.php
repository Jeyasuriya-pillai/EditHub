<?php
session_start();
require_once 'db.php';

// Check user login session
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id  = $_SESSION['user_id'];
$title    = trim($_POST['title'] ?? '');
$category = trim($_POST['category'] ?? 'PNG');

// Validation checks
if ($title === '' || !isset($_FILES['material_file']) || $_FILES['material_file']['error'] !== UPLOAD_ERR_OK) {
    die("Please provide a title and select a valid file. (Upload error code: " . ($_FILES['material_file']['error'] ?? 'no file') . ") <a href='../edit_profile.php'>Go back</a>");
}

// Absolute paths using DOCUMENT_ROOT fallback
$docRoot       = rtrim($_SERVER['DOCUMENT_ROOT'], '/\\');
$baseUploadDir = $docRoot . '/EditHub/uploads/';
$uploadDir     = $baseUploadDir . 'materials/';

// 1. Check & create base 'uploads' folder
if (!is_dir($baseUploadDir)) {
    @mkdir($baseUploadDir, 0777, true);
}

// 2. Check & create 'materials' folder
if (!is_dir($uploadDir)) {
    @mkdir($uploadDir, 0777, true);
}

// Re-check directory availability
if (!is_dir($uploadDir)) {
    die("Could not create 'uploads/materials/' folder automatically. Please manually create the folder structure: <b>C:\\xampp\\htdocs\\EditHub\\uploads\\materials\\</b> <a href='../edit_profile.php'>Go back</a>");
}

// File extension validation
$allowedExt = ['zip', 'rar', 'mp4', 'mov', 'png', 'jpg', 'jpeg', 'wav', 'mp3', 'cube', 'mogrt'];
$ext = strtolower(pathinfo($_FILES['material_file']['name'], PATHINFO_EXTENSION));

if (!in_array($ext, $allowedExt)) {
    die("File type '.$ext' not allowed. Allowed: " . implode(', ', $allowedExt) . " <a href='../edit_profile.php'>Go back</a>");
}

// Generate unique filename and destination
$safeName    = uniqid('material_') . '.' . $ext;
$destination = $uploadDir . $safeName;

// Move uploaded file
if (!move_uploaded_file($_FILES['material_file']['tmp_name'], $destination)) {
    die("File upload failed (could not move file to '$destination'). Check folder permissions. <a href='../edit_profile.php'>Go back</a>");
}

// Save relative path to Database
$filePathForDb = 'uploads/materials/' . $safeName;
$stmt = $conn->prepare("INSERT INTO user_materials (user_id, title, category, file_path) VALUES (?, ?, ?, ?)");
$stmt->bind_param("isss", $user_id, $title, $category, $filePathForDb);

if (!$stmt->execute()) {
    die("Database insert failed: " . $stmt->error . " <a href='../edit_profile.php'>Go back</a>");
}

$stmt->close();
$conn->close();

// Redirect back with success status
header("Location: ../edit_profile.php?material_uploaded=1");
exit();