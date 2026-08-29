<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id  = $_SESSION['user_id'];
$asset_id = intval($_GET['id'] ?? 0);

// File path nikal ke pehle disk se delete karo, phir DB record
$stmt = $conn->prepare("SELECT file_path FROM user_assets WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $asset_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $filePath = '../' . $row['file_path'];
    if (file_exists($filePath)) {
        unlink($filePath);
    }
}
$stmt->close();

$stmt2 = $conn->prepare("DELETE FROM user_assets WHERE id = ? AND user_id = ?");
$stmt2->bind_param("ii", $asset_id, $user_id);
$stmt2->execute();
$stmt2->close();
$conn->close();

header("Location: ../edit_profile.php");
exit();