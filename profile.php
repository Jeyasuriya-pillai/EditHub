<?php
session_start();
require_once 'php/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Added contact_email in SELECT query
$stmt = $conn->prepare("SELECT username, full_name, gender, bio, tag, contact_email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$stmt = $conn->prepare("SELECT platform, url FROM social_links WHERE user_id = ? ORDER BY id DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$socialLinks = $stmt->get_result();
$stmt->close();

$stmt = $conn->prepare("SELECT id, title, description, price FROM user_services WHERE user_id = ? ORDER BY id DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$myServices = $stmt->get_result();
$stmt->close();

$stmt = $conn->prepare("SELECT id, title, type, price, thumbnail FROM user_assets WHERE user_id = ? ORDER BY id DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$myAssets = $stmt->get_result();
$stmt->close();

$stmt = $conn->prepare("SELECT id, title, category, thumbnail FROM user_materials WHERE user_id = ? ORDER BY id DESC");
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
<link rel="stylesheet" href="css/style.css">
<style>
    .wrap { max-width: 800px; margin: 0 auto; padding: 40px 20px 80px; }
    .profile-header { padding:30px; display:flex; justify-content:space-between; align-items:center; gap:16px; margin-bottom:28px; flex-wrap:wrap; }
    .profile-main { display:flex; gap:18px; align-items:center; }
    .avatar-circle { width:70px; height:70px; border-radius:50%; background: var(--accent); color:#0c0c0e; display:flex; align-items:center; justify-content:center; font-size:26px; font-weight:900; flex-shrink:0; font-family: var(--font-display); }
    .profile-name { font-size:24px; }
    .profile-meta { color:var(--muted); font-size:13px; margin-top:4px; }
    .bio-text { color:#c9ccd6; font-size:14px; line-height:1.6; }
    .social-links { display:flex; flex-wrap:wrap; gap:10px; }
    .social-chip { background:var(--canvas); border:1px solid var(--line); padding:8px 14px; border-radius:20px; font-size:13px; text-decoration:none; color:#d1d5db; }
    .social-chip:hover { border-color:var(--accent); color:var(--accent); }
    .list-item { background:var(--canvas); border:1px solid var(--line); border-radius:var(--radius); padding:12px 16px; margin-bottom:10px; display:flex; justify-content:space-between; align-items:center; }
    .list-item h4 { font-size:14px; font-family: var(--font-display); }
    .list-item p { font-size:12px; color:var(--muted); margin-top:2px; }
    .thumb-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(140px,1fr)); gap:1px; background: var(--line); border: 1px solid var(--line); }
    .thumb-card { background:var(--canvas); overflow:hidden; text-decoration:none; color:#fff; display:block; }
    .thumb-card img { width:100%; height:100px; object-fit:cover; display:block; }
    .thumb-fallback { width:100%; height:100px; display:flex; align-items:center; justify-content:center; font-size:28px; background:var(--surface-2); }
    .thumb-title { font-size:12px; padding:8px; }
    .contact-badge { background: #1e2029; border: 1px solid #2e3243; color: #ff5232; padding: 6px 12px; border-radius: 6px; font-size: 13px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; }
</style>
</head>
<body>

<?php require_once 'php/navbar.php'; ?>

<div class="wrap">

    <div class="card profile-header">
        <div class="profile-main">
            <div class="avatar-circle"><?php echo strtoupper(substr($displayName, 0, 1)); ?></div>
            <div>
                <div class="profile-name"><?php echo htmlspecialchars($displayName); ?></div>
                <div class="profile-meta">@<?php echo htmlspecialchars($user['username']); ?> <?php echo $user['gender'] ? '• ' . ucfirst($user['gender']) : ''; ?></div>
                <span class="eh-badge" style="margin-top:8px; display:inline-block;"><?php echo htmlspecialchars($tag); ?></span>
            </div>
        </div>
        <div style="display:flex; flex-direction:column; align-items:flex-end; gap:10px;">
            <a href="edit_profile.php" class="btn btn-primary btn-sm">Edit profile</a>
            <?php if (!empty($user['contact_email'])): ?>
                <span class="contact-badge">✉️ <?php echo htmlspecialchars($user['contact_email']); ?></span>
            <?php endif; ?>
        </div>
    </div>

    <div class="card card-pad" style="margin-bottom:24px;">
        <h3 style="margin-bottom:14px;">About</h3>
        <?php if (!empty($user['bio'])): ?>
            <p class="bio-text"><?php echo nl2br(htmlspecialchars($user['bio'])); ?></p>
        <?php else: ?>
            <p class="empty-msg">No bio added yet.</p>
        <?php endif; ?>
    </div>

    <div class="card card-pad" style="margin-bottom:24px;">
        <h3 style="margin-bottom:14px;">🔗 Social links</h3>
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

    <div class="card card-pad" style="margin-bottom:24px;">
        <h3 style="margin-bottom:14px;">🎬 My services (Hire me)</h3>
        <?php if ($myServices->num_rows > 0): ?>
            <?php while ($s = $myServices->fetch_assoc()): ?>
                <div class="list-item">
                    <div>
                        <h4><?php echo htmlspecialchars($s['title']); ?></h4>
                        <p><?php echo htmlspecialchars($s['description']); ?> <?php echo $s['price'] ? '• ₹' . htmlspecialchars($s['price']) : ''; ?></p>
                    </div>
                    <?php if (!empty($user['contact_email'])): ?>
                        <a href="mailto:<?php echo htmlspecialchars($user['contact_email']); ?>?subject=Inquiry%20for%20<?php echo urlencode($s['title']); ?>" class="btn btn-primary btn-sm" style="background:#ff5232;">Test Hire Link</a>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="empty-msg">No services added yet.</p>
        <?php endif; ?>
    </div>

    <div class="card card-pad" style="margin-bottom:24px;">
        <h3 style="margin-bottom:14px;">📦 My uploaded assets</h3>
        <?php if ($myAssets->num_rows > 0): ?>
            <div class="thumb-grid">
                <?php while ($a = $myAssets->fetch_assoc()): ?>
                    <div class="thumb-card">
                        <?php if ($a['thumbnail']): ?>
                            <img src="<?php echo htmlspecialchars($a['thumbnail']); ?>" alt="">
                        <?php else: ?>
                            <div class="thumb-fallback">📦</div>
                        <?php endif; ?>
                        <div class="thumb-title"><?php echo htmlspecialchars($a['title']); ?> <span class="eh-badge" style="margin-left:4px;"><?php echo ucfirst($a['type']); ?></span></div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p class="empty-msg">No assets uploaded yet.</p>
        <?php endif; ?>
    </div>

    <div class="card card-pad">
        <h3 style="margin-bottom:14px;">🖼️ My uploaded materials</h3>
        <?php if ($myMaterials->num_rows > 0): ?>
            <div class="thumb-grid">
                <?php while ($m = $myMaterials->fetch_assoc()): ?>
                    <div class="thumb-card">
                        <?php if ($m['thumbnail']): ?>
                            <img src="<?php echo htmlspecialchars($m['thumbnail']); ?>" alt="">
                        <?php else: ?>
                            <div class="thumb-fallback">🖼️</div>
                        <?php endif; ?>
                        <div class="thumb-title"><?php echo htmlspecialchars($m['title']); ?></div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p class="empty-msg">No materials uploaded yet.</p>
        <?php endif; ?>
    </div>

</div>

</body>
</html>