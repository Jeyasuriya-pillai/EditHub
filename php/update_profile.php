<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id       = $_SESSION['user_id'];
$full_name     = trim($_POST['full_name'] ?? '');
$contact_email = trim($_POST['contact_email'] ?? '');
$gender        = $_POST['gender'] ?? '';
$bio           = trim($_POST['bio'] ?? '');
$tag           = $_POST['tag'] ?? 'normal';

$allowed_gender = ['male', 'female'];
$gender = in_array($gender, $allowed_gender) ? $gender : null;

$allowed_tags = ['normal', 'creator', 'editor'];
$tag = in_array($tag, $allowed_tags) ? $tag : 'normal';

/*
|--------------------------------------------------------------------------
| Profile Photo
|--------------------------------------------------------------------------
*/

$profilePhotoPath = null;

if (
    isset($_FILES['profile_photo']) &&
    $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK
) {

    $uploadDir = dirname(__DIR__) . '/uploads/profile/';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];

    $ext = strtolower(
        pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION)
    );

    if (!in_array($ext, $allowedExt)) {
        die("Invalid profile photo format. Use JPG, JPEG, PNG or WEBP.");
    }

    if ($_FILES['profile_photo']['size'] > 5 * 1024 * 1024) {
        die("Profile photo must be less than 5MB.");
    }

    $safeName = 'profile_' . $user_id . '_' . uniqid() . '.' . $ext;

    $destination = $uploadDir . $safeName;

    if (!move_uploaded_file($_FILES['profile_photo']['tmp_name'], $destination)) {
        die("Profile photo upload failed.");
    }

    $profilePhotoPath = 'uploads/profile/' . $safeName;
}

/*
|--------------------------------------------------------------------------
| Update User
|--------------------------------------------------------------------------
*/

if ($profilePhotoPath !== null) {

    $stmt = $conn->prepare("
        UPDATE users
        SET full_name = ?,
            contact_email = ?,
            gender = ?,
            bio = ?,
            tag = ?,
            profile_photo = ?
        WHERE id = ?
    ");

    $stmt->bind_param(
        "ssssssi",
        $full_name,
        $contact_email,
        $gender,
        $bio,
        $tag,
        $profilePhotoPath,
        $user_id
    );

} else {

    $stmt = $conn->prepare("
        UPDATE users
        SET full_name = ?,
            contact_email = ?,
            gender = ?,
            bio = ?,
            tag = ?
        WHERE id = ?
    ");

    $stmt->bind_param(
        "sssssi",
        $full_name,
        $contact_email,
        $gender,
        $bio,
        $tag,
        $user_id
    );
}

$stmt->execute();
$stmt->close();

$conn->close();

header("Location: ../edit_profile.php?updated=1");
exit();
?>