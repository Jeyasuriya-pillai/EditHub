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
    die("Please provide a title and select a valid file. <a href='../profile.php'>Go back</a>");
}

$uploadDir = '../uploads/materials/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$allowedExt = ['zip', 'rar', 'mp4', 'mov', 'png', 'jpg', 'jpeg', 'wav', 'mp3', 'cube', 'mogrt'];
$ext = strtolower(pathinfo($_FILES['material_file']['name'], PATHINFO_EXTENSION));

if (!in_array($ext, $allowedExt)) {
    die("File type not allowed. <a href='../profile.php'>Go back</a>");
}

$safeName    = uniqid('material_') . '.' . $ext;
$destination = $uploadDir . $safeName;

if (move_uploaded_file($_FILES['material_file']['tmp_name'], $destination)) {
    $filePathForDb = 'uploads/materials/' . $safeName;
    $stmt = $conn->prepare("INSERT INTO user_materials (user_id, title, category, file_path) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $title, $category, $filePathForDb);
    $stmt->execute();
    $stmt->close();
}

$conn->close();
header("Location: ../edit_profile.php");
exit();