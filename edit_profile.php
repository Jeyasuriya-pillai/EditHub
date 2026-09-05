<?php
session_start();
require_once 'php/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Added contact_email column in query
$stmt = $conn->prepare("SELECT username, full_name, gender, bio, tag, contact_email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$stmt = $conn->prepare("SELECT id, platform, url FROM social_links WHERE user_id = ? ORDER BY id DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$socialLinks = $stmt->get_result();
$stmt->close();

$stmt = $conn->prepare("SELECT id, title, description, price FROM user_services WHERE user_id = ? ORDER BY id DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$myServices = $stmt->get_result();
$stmt->close();

$stmt = $conn->prepare("SELECT id, title, type, price FROM user_assets WHERE user_id = ? ORDER BY id DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$myAssets = $stmt->get_result();
$stmt->close();

$stmt = $conn->prepare("SELECT id, title, category FROM user_materials WHERE user_id = ? ORDER BY id DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$myMaterials = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Profile - EditHub</title>
<link rel="stylesheet" href="css/style.css">
<style>
    .wrap { max-width: 850px; margin: 0 auto; padding: 40px 20px 80px; }
    .top-bar { display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; }
    .back-link { color:var(--muted); text-decoration:none; font-size:14px; }
    .back-link:hover { color:var(--accent); }
    .radio-group { display:flex; gap:20px; margin-top:8px; }
    .radio-group label { display:flex; align-items:center; gap:6px; margin:0; color:#e5e7eb; font-size:14px; text-transform:none; }
    .divider { border-top:1px solid var(--line); margin:22px 0; }
    .type-toggle { display:flex; gap:20px; margin-top:8px; }
    .type-toggle label { display:flex; align-items:center; gap:6px; color:#e5e7eb; font-size:14px; text-transform:none; }
    .list-item { display:flex; justify-content:space-between; align-items:center; background:var(--canvas); border:1px solid var(--line); border-radius:var(--radius); padding:12px 16px; margin-bottom:10px; gap:12px; }
    .list-item h4 { font-size:14px; font-family:var(--font-display); }
    .list-item p { font-size:12px; color:var(--muted); margin-top:2px; }
</style>
</head>
<body>

<?php require_once 'php/navbar.php'; ?>

<div class="wrap">

    <div class="top-bar">
        <a href="profile.php" class="back-link">← Back to profile</a>
    </div>

    <?php if (isset($_GET['updated'])): ?>
        <div class="success-msg">✅ Profile updated successfully.</div>
    <?php endif; ?>

    <div class="card card-pad" style="margin-bottom:28px;">
        <h2 style="font-size:18px; margin-bottom:8px;">👤 Profile info</h2>
        <p style="color:var(--muted); font-size:13px;">Username (cannot be changed): <strong style="color:#fff;"><?php echo htmlspecialchars($user['username']); ?></strong></p>
        <span class="eh-badge" style="margin-top:8px; display:inline-block;"><?php echo htmlspecialchars(ucfirst($user['tag'] ?? 'normal')); ?></span>

        <form action="php/update_profile.php" method="POST">
            <label>Display name</label>
            <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" placeholder="Your name">

            <label>Contact Email (For receiving hire/client inquiries)</label>
            <input type="email" name="contact_email" value="<?php echo htmlspecialchars($user['contact_email'] ?? ''); ?>" placeholder="e.g. yourname@gmail.com" required>

            <label>Gender</label>
            <div class="radio-group">
                <label><input type="radio" name="gender" value="male" <?php echo ($user['gender'] ?? '') === 'male' ? 'checked' : ''; ?>> Male</label>
                <label><input type="radio" name="gender" value="female" <?php echo ($user['gender'] ?? '') === 'female' ? 'checked' : ''; ?>> Female</label>
            </div>

            <label>Bio</label>
            <textarea name="bio" placeholder="Tell people about yourself..."><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>

            <label>Profile tag</label>
            <select name="tag">
                <option value="normal" <?php echo ($user['tag'] ?? '') === 'normal' ? 'selected' : ''; ?>>Normal</option>
                <option value="creator" <?php echo ($user['tag'] ?? '') === 'creator' ? 'selected' : ''; ?>>Creator</option>
                <option value="editor" <?php echo ($user['tag'] ?? '') === 'editor' ? 'selected' : ''; ?>>Editor</option>
            </select>

            <button type="submit" class="btn btn-primary" style="margin-top:20px;">Save changes</button>
        </form>
    </div>

    <div class="card card-pad" style="margin-bottom:28px;">
        <h2 style="font-size:18px; margin-bottom:18px;">🔗 Social media links</h2>
        <?php while ($link = $socialLinks->fetch_assoc()): ?>
            <div class="list-item">
                <div>
                    <h4><?php echo htmlspecialchars($link['platform']); ?></h4>
                    <p><?php echo htmlspecialchars($link['url']); ?></p>
                </div>
                <a href="php/social_link_delete.php?id=<?php echo $link['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Remove this link?');">Remove</a>
            </div>
        <?php endwhile; ?>

        <div class="divider"></div>
        <form action="php/social_link_add.php" method="POST">
            <label>Platform (e.g. Instagram, YouTube)</label>
            <input type="text" name="platform" placeholder="Instagram" required>
            <label>Profile URL</label>
            <input type="url" name="url" placeholder="https://instagram.com/yourhandle" required>
            <button type="submit" class="btn btn-primary" style="margin-top:20px;">Add link</button>
        </form>
    </div>

    <div class="card card-pad" style="margin-bottom:28px;">
        <h2 style="font-size:18px; margin-bottom:4px;">🎬 My personal services (Hire me)</h2>
        <p style="color:var(--muted); font-size:13px; margin-bottom:16px;">These appear on the homepage Services / Hire Editors section.</p>

        <?php while ($service = $myServices->fetch_assoc()): ?>
            <div class="list-item">
                <div>
                    <h4><?php echo htmlspecialchars($service['title']); ?></h4>
                    <p><?php echo htmlspecialchars($service['description']); ?> <?php echo $service['price'] ? '• ₹' . htmlspecialchars($service['price']) : ''; ?></p>
                </div>
                <a href="php/service_delete.php?id=<?php echo $service['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this service?');">Delete</a>
            </div>
        <?php endwhile; ?>

        <div class="divider"></div>
        <form action="php/service_add.php" method="POST">
            <label>Service title</label>
            <input type="text" name="title" placeholder="e.g. Reels Editing" required>
            <label>Description</label>
            <textarea name="description" placeholder="What do you offer?"></textarea>
            <label>Price (₹ per hour / per video)</label>
            <input type="text" name="price" placeholder="e.g. 1500">
            <button type="submit" class="btn btn-primary" style="margin-top:20px;">Add service</button>
        </form>
    </div>

    <div class="card card-pad" style="margin-bottom:28px;">
        <h2 style="font-size:18px; margin-bottom:4px;">📦 My uploaded assets</h2>
        <p style="color:var(--muted); font-size:13px; margin-bottom:16px;">These appear on the homepage Assets (Free/Paid) section.</p>

        <?php while ($asset = $myAssets->fetch_assoc()): ?>
            <div class="list-item">
                <div>
                    <h4><?php echo htmlspecialchars($asset['title']); ?> <span class="eh-badge" style="margin-left:4px;"><?php echo htmlspecialchars(ucfirst($asset['type'])); ?></span></h4>
                    <p><?php echo $asset['type'] === 'paid' ? '₹' . htmlspecialchars($asset['price']) : 'Free'; ?></p>
                </div>
                <a href="php/asset_delete.php?id=<?php echo $asset['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this asset?');">Delete</a>
            </div>
        <?php endwhile; ?>

        <div class="divider"></div>
        <form action="php/asset_add.php" method="POST" enctype="multipart/form-data">
            <label>Asset title</label>
            <input type="text" name="title" placeholder="e.g. Cinematic LUT Pack" required>

            <label>Type</label>
            <div class="type-toggle">
                <label><input type="radio" name="type" value="free" checked onclick="document.getElementById('assetPriceBox').style.display='none'"> Free</label>
                <label><input type="radio" name="type" value="paid" onclick="document.getElementById('assetPriceBox').style.display='block'"> Paid</label>
            </div>

            <div id="assetPriceBox" style="display:none;">
                <label>Price (₹)</label>
                <input type="number" name="price" placeholder="e.g. 299">
            </div>

            <label>Upload file</label>
            <input type="file" name="asset_file" required>

            <label>Preview image (optional, shown as thumbnail)</label>
            <input type="file" name="thumbnail" accept="image/*">

            <button type="submit" class="btn btn-primary" style="margin-top:20px;">Upload asset</button>
        </form>
    </div>

    <div class="card card-pad">
        <h2 style="font-size:18px; margin-bottom:4px;">🖼️ My uploaded materials</h2>
        <p style="color:var(--muted); font-size:13px; margin-bottom:16px;">These appear on the homepage Materials section.</p>

        <?php while ($material = $myMaterials->fetch_assoc()): ?>
            <div class="list-item">
                <div>
                    <h4><?php echo htmlspecialchars($material['title']); ?></h4>
                    <p><?php echo htmlspecialchars($material['category']); ?></p>
                </div>
                <a href="php/material_delete.php?id=<?php echo $material['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this material?');">Delete</a>
            </div>
        <?php endwhile; ?>

        <div class="divider"></div>
        <form action="php/material_add.php" method="POST" enctype="multipart/form-data">
            <label>Material title</label>
            <input type="text" name="title" placeholder="e.g. Glitch Overlay Pack" required>

            <label>Category</label>
            <select name="category">
                <option value="PNG">HD PNG Cutout</option>
                <option value="SFX">SFX / Sound Pack</option>
                <option value="CC">CC / Color Grading</option>
                <option value="VFX">VFX / Video Effect</option>
            </select>

            <label>Upload file</label>
            <input type="file" name="material_file" required>

            <label>Preview image (optional, shown as thumbnail)</label>
            <input type="file" name="thumbnail" accept="image/*">

            <button type="submit" class="btn btn-primary" style="margin-top:20px;">Upload material</button>
        </form>
    </div>

</div>

</body>
</html>