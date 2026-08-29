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

$stmt = $conn->prepare("SELECT platform, url FROM social_links WHERE user_id = ? ORDER BY id DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$socialLinks = $stmt->get_result();
$stmt->close();

$stmt = $conn->prepare("SELECT title, description, price FROM user_services WHERE user_id = ? ORDER BY id DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$myServices = $stmt->get_result();
$stmt->close();

$stmt = $conn->prepare("SELECT title, type, price FROM user_assets WHERE user_id = ? ORDER BY id DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$myAssets = $stmt->get_result();
$stmt->close();

$stmt = $conn->prepare("SELECT title, category FROM user_materials WHERE user_id = ? ORDER BY id DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$myMaterials = $stmt->get_result();
$stmt->close();

$displayName = $user['full_name'] ?: $user['username'];
$tag = ucfirst($user['tag'] ?? 'normal');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo htmlspecialchars($displayName); ?> - EditHub Profile</title>
<style>
    * { margin:0; padding:0; box-sizing:border-box; font-family:'Segoe UI', Roboto, sans-serif; }
    body { background:#090a10; color:#fff; }

    .navbar { display:flex; justify-content:space-between; align-items:center; padding:16px 45px; background:rgba(9,10,16,0.95); border-bottom:1px solid #1a1c28; position:sticky; top:0; }
    .logo { font-size:22px; font-weight:800; color:#fff; text-decoration:none; }
    .logo span { color:#8b5cf6; }
    .navbar nav a { color:#9ca3af; text-decoration:none; margin-left:22px; font-size:14px; font-weight:600; }
    .navbar nav a:hover { color:#8b5cf6; }

    .wrap { max-width: 800px; margin: 40px auto; padding: 0 20px 80px; }

    .profile-header { background:#11131c; border:1px solid #1f2333; border-radius:14px; padding:30px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px; margin-bottom:28px; }
    .avatar-circle { width:70px; height:70px; border-radius:50%; background:linear-gradient(135deg,#8b5cf6,#6366f1); display:flex; align-items:center; justify-content:center; font-size:26px; font-weight:800; }
    .profile-main { display:flex; gap:18px; align-items:center; }
    .profile-name { font-size:22px; font-weight:800; }
    .profile-meta { color:#9ca3af; font-size:13px; margin-top:4px; }
    .tag-pill { display:inline-block; padding:4px 12px; border-radius:20px; background:rgba(139,92,246,0.15); color:#a78bfa; font-size:12px; font-weight:700; margin-top:8px; }
    .btn-edit { background:#8b5cf6; color:#fff; padding:10px 20px; border-radius:8px; text-decoration:none; font-weight:700; font-size:13px; white-space:nowrap; }
    .btn-edit:hover { background:#7c3aed; }

    .card { background:#11131c; border:1px solid #1f2333; border-radius:12px; padding:24px; margin-bottom:24px; }
    .card h3 { font-size:16px; margin-bottom:16px; }
    .bio-text { color:#c9ccd6; font-size:14px; line-height:1.6; }
    .empty-msg { color:#6b7280; font-size:13px; }

    .social-links { display:flex; flex-wrap:wrap; gap:10px; }
    .social-chip { background:#090a10; border:1px solid #232738; padding:8px 14px; border-radius:20px; font-size:13px; text-decoration:none; color:#d1d5db; }
    .social-chip:hover { border-color:#8b5cf6; color:#a78bfa; }

    .list-item { background:#090a10; border:1px solid #1f2333; border-radius:8px; padding:12px 16px; margin-bottom:10px; }
    .list-item h4 { font-size:14px; }
    .list-item p { font-size:12px; color:#9ca3af; margin-top:2px; }
    .badge { display:inline-block; font-size:11px; padding:2px 8px; border-radius:12px; background:rgba(139,92,246,0.15); color:#a78bfa; margin-left:6px; }
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

    <div class="profile-header">
        <div class="profile-main">
            <div class="avatar-circle"><?php echo strtoupper(substr($displayName, 0, 1)); ?></div>
            <div>
                <div class="profile-name"><?php echo htmlspecialchars($displayName); ?></div>
                <div class="profile-meta">@<?php echo htmlspecialchars($user['username']); ?> <?php echo $user['gender'] ? '• ' . ucfirst($user['gender']) : ''; ?></div>
                <span class="tag-pill"><?php echo htmlspecialchars($tag); ?></span>
            </div>
        </div>
        <a href="edit_profile.php" class="btn-edit">✏️ Edit Profile</a>
    </div>

    <div class="card">
        <h3>About</h3>
        <?php if (!empty($user['bio'])): ?>
            <p class="bio-text"><?php echo nl2br(htmlspecialchars($user['bio'])); ?></p>
        <?php else: ?>
            <p class="empty-msg">No bio added yet.</p>
        <?php endif; ?>
    </div>

    <div class="card">
        <h3>🔗 Social Links</h3>
        <?php if ($socialLinks->num_rows > 0): ?>
            <div class="social-links">
                <?php while ($link = $socialLinks->fetch_assoc()): ?>
                    <a href="<?php echo htmlspecialchars($link['url']); ?>" target="_blank" class="social-chip"><?php echo htmlspecialchars($link['platform']); ?></a>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p class="empty-msg">No social links added yet.</p>
        <?php endif; ?>
    </div>

    <div class="card">
        <h3>🎬 My Services (Hire Me)</h3>
        <?php if ($myServices->num_rows > 0): ?>
            <?php while ($s = $myServices->fetch_assoc()): ?>
                <div class="list-item">
                    <h4><?php echo htmlspecialchars($s['title']); ?></h4>
                    <p><?php echo htmlspecialchars($s['description']); ?> <?php echo $s['price'] ? '• ₹' . htmlspecialchars($s['price']) : ''; ?></p>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="empty-msg">No services added yet.</p>
        <?php endif; ?>
    </div>

    <div class="card">
        <h3>📦 My Uploaded Assets</h3>
        <?php if ($myAssets->num_rows > 0): ?>
            <?php while ($a = $myAssets->fetch_assoc()): ?>
                <div class="list-item">
                    <h4><?php echo htmlspecialchars($a['title']); ?> <span class="badge"><?php echo ucfirst($a['type']); ?></span></h4>
                    <p><?php echo $a['type'] === 'paid' ? '₹' . htmlspecialchars($a['price']) : 'Free'; ?></p>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="empty-msg">No assets uploaded yet.</p>
        <?php endif; ?>
    </div>

    <div class="card">
        <h3>🖼️ My Uploaded Materials</h3>
        <?php if ($myMaterials->num_rows > 0): ?>
            <?php while ($m = $myMaterials->fetch_assoc()): ?>
                <div class="list-item">
                    <h4><?php echo htmlspecialchars($m['title']); ?></h4>
                    <p><?php echo htmlspecialchars($m['category']); ?></p>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="empty-msg">No materials uploaded yet.</p>
        <?php endif; ?>
    </div>

</div>

</body>
</html>