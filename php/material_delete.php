<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id     = $_SESSION['user_id'];
$material_id = intval($_GET['id'] ?? 0);

$stmt = $conn->prepare("SELECT file_path FROM user_materials WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $material_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $filePath = '../' . $row['file_path'];
    if (file_exists($filePath)) {
        unlink($filePath);
    }
}
$stmt->close();

$stmt2 = $conn->prepare("DELETE FROM user_materials WHERE id = ? AND user_id = ?");
$stmt2->bind_param("ii", $material_id, $user_id);
$stmt2->execute();
$stmt2->close();
$conn->close();

header("Location: ../edit_profile.php");
exit();