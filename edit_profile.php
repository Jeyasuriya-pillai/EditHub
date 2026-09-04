<?php
session_start();
require_once 'php/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT username, full_name, gender, bio, tag FROM users WHERE id = ?");
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
<style>
    * { margin:0; padding:0; box-sizing:border-box; font-family:'Segoe UI', Roboto, sans-serif; }
    body { background:#090a10; color:#fff; }

    .navbar { display:flex; justify-content:space-between; align-items:center; padding:16px 45px; background:rgba(9,10,16,0.95); border-bottom:1px solid #1a1c28; position:sticky; top:0; }
    .logo { font-size:22px; font-weight:800; color:#fff; text-decoration:none; }
    .logo span { color:#8b5cf6; }
    .navbar nav a { color:#9ca3af; text-decoration:none; margin-left:22px; font-size:14px; font-weight:600; }
    .navbar nav a:hover { color:#8b5cf6; }

    .wrap { max-width: 850px; margin: 40px auto; padding: 0 20px 80px; }
    .top-bar { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; }
    .back-link { color:#9ca3af; text-decoration:none; font-size:14px; }
    .back-link:hover { color:#8b5cf6; }

    .card { background:#11131c; border:1px solid #1f2333; border-radius:12px; padding:26px; margin-bottom:28px; }
    .card h2 { font-size:18px; margin-bottom:18px; }
    label { display:block; font-size:13px; color:#9ca3af; margin-bottom:6px; margin-top:14px; }
    input[type=text], input[type=url], input[type=number], textarea, select {
        width:100%; padding:10px 14px; border-radius:8px; border:1px solid #232738; background:#090a10; color:#fff; font-size:14px; outline:none;
    }
    input:focus, textarea:focus, select:focus { border-color:#8b5cf6; }
    textarea { resize:vertical; min-height:80px; }
    .radio-group { display:flex; gap:20px; margin-top:6px; }
    .radio-group label { display:flex; align-items:center; gap:6px; margin:0; color:#e5e7eb; font-size:14px; }
    .btn { background:#8b5cf6; color:#fff; border:none; padding:10px 20px; border-radius:8px; font-weight:700; font-size:13px; cursor:pointer; margin-top:16px; }
    .btn:hover { background:#7c3aed; }
    .btn-danger { background:#ef4444; color:#fff; border:none; padding:6px 12px; border-radius:6px; font-size:12px; cursor:pointer; text-decoration:none; }
    .btn-danger:hover { background:#dc2626; }

    .list-item { display:flex; justify-content:space-between; align-items:center; background:#090a10; border:1px solid #1f2333; border-radius:8px; padding:12px 16px; margin-bottom:10px; }
    .list-item h4 { font-size:14px; }
    .list-item p { font-size:12px; color:#9ca3af; margin-top:2px; }
    .tag-pill { display:inline-block; padding:4px 12px; border-radius:20px; background:rgba(139,92,246,0.15); color:#a78bfa; font-size:12px; font-weight:600; margin-top:6px; }
    .success-msg { background:rgba(16,185,129,0.15); color:#10b981; padding:10px 16px; border-radius:8px; margin-bottom:20px; font-size:14px; }
    .divider { border-top:1px solid #1f2333; margin:20px 0; }
    .type-toggle { display:flex; gap:20px; margin-top:6px; }
</style>
</head>
<body>

<header class="navbar">
    <a href="home.php" class="logo">Edit<span>Hub</span></a>
    <nav>
        <a href="home.php">Home</a>
        <a href="services.php">Services</a>
        <a href="assets.php">Assets</a>
        <a href="profile.php">Profile</a>
        <a href="php/logout.php">Logout</a>
    </nav>
</header>

<div class="wrap">

    <div class="top-bar">
        <a href="profile.php" class="back-link">← Back to Profile</a>
    </div>

    <?php if (isset($_GET['updated'])): ?>
        <div class="success-msg">✅ Profile updated successfully.</div>
    <?php endif; ?>

    <!-- Basic Info -->
    <div class="card">
        <h2>👤 Profile Info</h2>
        <p style="color:#9ca3af; font-size:13px;">Username (cannot be changed): <strong style="color:#fff;"><?php echo htmlspecialchars($user['username']); ?></strong></p>
        <span class="tag-pill"><?php echo htmlspecialchars(ucfirst($user['tag'] ?? 'normal')); ?></span>

        <form action="php/update_profile.php" method="POST">
            <label>Display Name</label>
            <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" placeholder="Your name">

            <label>Gender</label>
            <div class="radio-group">
                <label><input type="radio" name="gender" value="male" <?php echo ($user['gender'] ?? '') === 'male' ? 'checked' : ''; ?>> Male</label>
                <label><input type="radio" name="gender" value="female" <?php echo ($user['gender'] ?? '') === 'female' ? 'checked' : ''; ?>> Female</label>
            </div>

            <label>Bio</label>
            <textarea name="bio" placeholder="Tell people about yourself..."><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>

            <label>Profile Tag</label>
            <select name="tag">
                <option value="normal" <?php echo ($user['tag'] ?? '') === 'normal' ? 'selected' : ''; ?>>Normal</option>
                <option value="creator" <?php echo ($user['tag'] ?? '') === 'creator' ? 'selected' : ''; ?>>Creator</option>
                <option value="editor" <?php echo ($user['tag'] ?? '') === 'editor' ? 'selected' : ''; ?>>Editor</option>
            </select>

            <button type="submit" class="btn">Save Changes</button>
        </form>
    </div>

    <!-- Social Links -->
    <div class="card">
        <h2>🔗 Social Media Links</h2>
        <?php while ($link = $socialLinks->fetch_assoc()): ?>
            <div class="list-item">
                <div>
                    <h4><?php echo htmlspecialchars($link['platform']); ?></h4>
                    <p><?php echo htmlspecialchars($link['url']); ?></p>
                </div>
                <a href="php/social_link_delete.php?id=<?php echo $link['id']; ?>" class="btn-danger" onclick="return confirm('Remove this link?');">Remove</a>
            </div>
        <?php endwhile; ?>

        <div class="divider"></div>
        <form action="php/social_link_add.php" method="POST">
            <label>Platform (e.g. Instagram, YouTube)</label>
            <input type="text" name="platform" placeholder="Instagram" required>
            <label>Profile URL</label>
            <input type="url" name="url" placeholder="https://instagram.com/yourhandle" required>
            <button type="submit" class="btn">Add Link</button>
        </form>
    </div>

    <!-- My Personal Services (Hire Me) -->
    <div class="card">
        <h2>🎬 My Personal Services (Hire Me)</h2>
        <p style="color:#9ca3af; font-size:13px; margin-bottom:10px;">These will appear on the homepage Services / Hire Editors section.</p>

        <?php while ($service = $myServices->fetch_assoc()): ?>
            <div class="list-item">
                <div>
                    <h4><?php echo htmlspecialchars($service['title']); ?></h4>
                    <p><?php echo htmlspecialchars($service['description']); ?> <?php echo $service['price'] ? '• ₹' . htmlspecialchars($service['price']) : ''; ?></p>
                </div>
                <a href="php/service_delete.php?id=<?php echo $service['id']; ?>" class="btn-danger" onclick="return confirm('Delete this service?');">Delete</a>
            </div>
        <?php endwhile; ?>

        <div class="divider"></div>
        <form action="php/service_add.php" method="POST">
            <label>Service Title</label>
            <input type="text" name="title" placeholder="e.g. Reels Editing" required>
            <label>Description</label>
            <textarea name="description" placeholder="What do you offer?"></textarea>
            <label>Price (₹ per hour / per video)</label>
            <input type="text" name="price" placeholder="e.g. 1500">
            <button type="submit" class="btn">Add Service</button>
        </form>
    </div>

    <!-- My Uploaded Assets -->
    <div class="card">
        <h2>📦 My Uploaded Assets</h2>
        <p style="color:#9ca3af; font-size:13px; margin-bottom:10px;">These will appear on the homepage Assets (Free/Paid) section.</p>

        <?php while ($asset = $myAssets->fetch_assoc()): ?>
            <div class="list-item">
                <div>
                    <h4><?php echo htmlspecialchars($asset['title']); ?> <span class="tag-pill" style="margin-top:0;"><?php echo htmlspecialchars(ucfirst($asset['type'])); ?></span></h4>
                    <p><?php echo $asset['type'] === 'paid' ? '₹' . htmlspecialchars($asset['price']) : 'Free'; ?></p>
                </div>
                <a href="php/asset_delete.php?id=<?php echo $asset['id']; ?>" class="btn-danger" onclick="return confirm('Delete this asset?');">Delete</a>
            </div>
        <?php endwhile; ?>

        <div class="divider"></div>
        <form action="php/asset_add.php" method="POST" enctype="multipart/form-data">
            <label>Asset Title</label>
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

            <label>Upload File</label>
            <input type="file" name="asset_file" required>

            <label>Preview Image (optional, shown as thumbnail)</label>
            <input type="file" name="thumbnail" accept="image/*">

            <button type="submit" class="btn">Upload Asset</button>
        </form>
    </div>

    <!-- My Uploaded Materials -->
    <div class="card">
        <h2>🖼️ My Uploaded Materials</h2>
        <p style="color:#9ca3af; font-size:13px; margin-bottom:10px;">These will appear on the homepage Materials section.</p>

        <?php while ($material = $myMaterials->fetch_assoc()): ?>
            <div class="list-item">
                <div>
                    <h4><?php echo htmlspecialchars($material['title']); ?></h4>
                    <p><?php echo htmlspecialchars($material['category']); ?></p>
                </div>
                <a href="php/material_delete.php?id=<?php echo $material['id']; ?>" class="btn-danger" onclick="return confirm('Delete this material?');">Delete</a>
            </div>
        <?php endwhile; ?>

        <div class="divider"></div>
        <form action="php/material_add.php" method="POST" enctype="multipart/form-data">
            <label>Material Title</label>
            <input type="text" name="title" placeholder="e.g. Glitch Overlay Pack" required>

            <label>Category</label>
            <select name="category">
                <option value="PNG">HD PNG Cutout</option>
                <option value="SFX">SFX / Sound Pack</option>
                <option value="CC">CC / Color Grading</option>
                <option value="VFX">VFX / Video Effect</option>
            </select>

            <label>Upload File</label>
            <input type="file" name="material_file" required>

            <label>Preview Image (optional, shown as thumbnail)</label>
            <input type="file" name="thumbnail" accept="image/*">

            <button type="submit" class="btn">Upload Material</button>
        </form>
    </div>

</div>

</body>
</html>