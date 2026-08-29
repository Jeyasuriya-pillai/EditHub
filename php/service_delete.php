<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id    = $_SESSION['user_id'];
$service_id = intval($_GET['id'] ?? 0);

$stmt = $conn->prepare("DELETE FROM user_services WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $service_id, $user_id);
$stmt->execute();
$stmt->close();
$conn->close();

header("Location: ../edit_profile.php");
exit();