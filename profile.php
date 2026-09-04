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

// ================= Reviews on my Services =================
$stmt = $conn->prepare("
    SELECT r.rating, r.comment, r.created_at, us.title AS item_title,
           u.id AS reviewer_id, u.username, u.full_name
    FROM reviews r
    JOIN user_services us ON r.target_id = us.id AND r.target_type = 'service'
    JOIN users u ON r.user_id = u.id
    WHERE us.user_id = ?
    ORDER BY r.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$serviceReviews = $stmt->get_result();
$stmt->close();

// ================= Reviews on my Assets =================
$stmt = $conn->prepare("
    SELECT r.rating, r.comment, r.created_at, ua.title AS item_title,
           u.id AS reviewer_id, u.username, u.full_name
    FROM reviews r
    JOIN user_assets ua ON r.target_id = ua.id AND r.target_type = 'asset'
    JOIN users u ON r.user_id = u.id
    WHERE ua.user_id = ?
    ORDER BY r.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$assetReviews = $stmt->get_result();
$stmt->close();

// ================= Reviews on my Materials =================
$stmt = $conn->prepare("
    SELECT r.rating, r.comment, r.created_at, um.title AS item_title,
           u.id AS reviewer_id, u.username, u.full_name
    FROM reviews r
    JOIN user_materials um ON r.target_id = um.id AND r.target_type = 'material'
    JOIN users u ON r.user_id = u.id
    WHERE um.user_id = ?
    ORDER BY r.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$materialReviews = $stmt->get_result();
$stmt->close();

// ================= NEW: Overall combined rating (Services + Assets + Materials) =================
$stmt = $conn->prepare("
    SELECT r.rating FROM reviews r
    JOIN user_services us ON r.target_id = us.id AND r.target_type = 'service'
    WHERE us.user_id = ?
    UNION ALL
    SELECT r.rating FROM reviews r
    JOIN user_assets ua ON r.target_id = ua.id AND r.target_type = 'asset'
    WHERE ua.user_id = ?
    UNION ALL
    SELECT r.rating FROM reviews r
    JOIN user_materials um ON r.target_id = um.id AND r.target_type = 'material'
    WHERE um.user_id = ?
");
$stmt->bind_param("iii", $user_id, $user_id, $user_id);
$stmt->execute();
$allRatingsResult = $stmt->get_result();

$totalRating = 0;
$totalCount = 0;
while ($row = $allRatingsResult->fetch_assoc()) {
    $totalRating += $row['rating'];
    $totalCount++;
}
$overallAvg = $totalCount > 0 ? ($totalRating / $totalCount) : 0;
$stmt->close();

function getRankLabel($avg, $count) {
    if ($count === 0) return "Not Rated Yet";
    if ($avg >= 4.5) return "🏆 Top Rated";
    if ($avg >= 4.0) return "🌟 Excellent";
    if ($avg >= 3.0) return "👍 Good";
    if ($avg >= 2.0) return "🙂 Average";
    return "⚠️ Needs Improvement";
}

$displayName = $user['full_name'] ?: $user['username'];
$tag = ucfirst($user['tag'] ?? 'normal');

function starDisplay($rating) {
    $rating = round($rating);
    return str_repeat('★', $rating) . str_repeat('☆', 5 - $rating);
}
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

    /* ===== NEW: Overall rating card ===== */
    .overall-rating-card { background:linear-gradient(135deg, rgba(139,92,246,0.15), rgba(99,102,241,0.08)); border:1px solid rgba(139,92,246,0.3); border-radius:14px; padding:24px 30px; margin-bottom:28px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:14px; }
    .overall-rating-left h3 { font-size:15px; color:#c9ccd6; margin-bottom:8px; }
    .overall-rating-value { font-size:32px; font-weight:800; color:#fff; }
    .overall-rating-stars { color:#f59e0b; font-size:20px; letter-spacing:2px; margin-left:10px; }
    .overall-rating-count { color:#9ca3af; font-size:13px; margin-top:4px; }
    .rank-badge { background:rgba(139,92,246,0.2); color:#a78bfa; padding:10px 20px; border-radius:10px; font-weight:800; font-size:15px; text-align:center; }

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

    .thumb-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(140px,1fr)); gap:12px; }
    .thumb-card { background:#090a10; border:1px solid #1f2333; border-radius:8px; overflow:hidden; color:#fff; display:block; }
    .thumb-card img { width:100%; height:100px; object-fit:cover; display:block; }
    .thumb-fallback { width:100%; height:100px; display:flex; align-items:center; justify-content:center; font-size:28px; background:#161824; }
    .thumb-title { font-size:12px; padding:8px; }

    /* ===== Review styles ===== */
    .review-item { border-bottom:1px solid #1f2333; padding:14px 0; }
    .review-item:last-child { border-bottom:none; }
    .review-top { display:flex; justify-content:space-between; align-items:center; margin-bottom:6px; }
    .review-author-link { text-decoration:none; display:flex; align-items:center; gap:8px; }
    .review-avatar { width:26px; height:26px; border-radius:50%; background:linear-gradient(135deg,#8b5cf6,#6366f1); display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:800; color:#fff; flex-shrink:0; }
    .review-author { font-weight:700; font-size:14px; color:#fff; }
    .review-stars { color:#f59e0b; font-size:13px; }
    .review-comment { color:#c9ccd6; font-size:13px; line-height:1.5; margin-top:4px; }
    .review-item-tag { color:#a78bfa; font-size:11px; margin-top:4px; }
    .review-date { color:#6b7280; font-size:11px; margin-top:4px; }
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

    <!-- ================= NEW: Overall Rating / Rank ================= -->
    <div class="overall-rating-card">
        <div class="overall-rating-left">
            <h3>⭐ Overall Rating (Services + Assets + Materials)</h3>
            <span class="overall-rating-value"><?php echo number_format($overallAvg, 1); ?></span>
            <span class="overall-rating-stars"><?php echo starDisplay($overallAvg); ?></span>
            <div class="overall-rating-count"><?php echo $totalCount; ?> total review<?php echo $totalCount != 1 ? 's' : ''; ?></div>
        </div>
        <div class="rank-badge"><?php echo getRankLabel($overallAvg, $totalCount); ?></div>
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

    <!-- NOTE: Thumbnails are now plain (not clickable to review.php) -->
    <div class="card">
        <h3>📦 My Uploaded Assets</h3>
        <?php if ($myAssets->num_rows > 0): ?>
            <div class="thumb-grid">
                <?php while ($a = $myAssets->fetch_assoc()): ?>
                    <div class="thumb-card">
                        <?php if ($a['thumbnail']): ?>
                            <img src="<?php echo htmlspecialchars($a['thumbnail']); ?>" alt="">
                        <?php else: ?>
                            <div class="thumb-fallback">📦</div>
                        <?php endif; ?>
                        <div class="thumb-title"><?php echo htmlspecialchars($a['title']); ?> <span class="badge"><?php echo ucfirst($a['type']); ?></span></div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p class="empty-msg">No assets uploaded yet.</p>
        <?php endif; ?>
    </div>

    <div class="card">
        <h3>🖼️ My Uploaded Materials</h3>
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

    <!-- ================= Reviews on My Services ================= -->
    <div class="card">
        <h3>🎬 Reviews on My Services</h3>
        <?php if ($serviceReviews->num_rows > 0): ?>
            <?php while ($r = $serviceReviews->fetch_assoc()): ?>
                <div class="review-item">
                    <div class="review-top">
                        <a href="view_profile.php?user_id=<?php echo $r['reviewer_id']; ?>" class="review-author-link">
                            <span class="review-avatar"><?php echo strtoupper(substr($r['full_name'] ?: $r['username'], 0, 1)); ?></span>
                            <span class="review-author"><?php echo htmlspecialchars($r['full_name'] ?: $r['username']); ?></span>
                        </a>
                        <span class="review-stars"><?php echo starDisplay($r['rating']); ?></span>
                    </div>
                    <?php if (!empty($r['comment'])): ?>
                        <p class="review-comment"><?php echo nl2br(htmlspecialchars($r['comment'])); ?></p>
                    <?php endif; ?>
                    <div class="review-item-tag">on: <?php echo htmlspecialchars($r['item_title']); ?></div>
                    <div class="review-date"><?php echo date('d M Y', strtotime($r['created_at'])); ?></div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="empty-msg">No reviews yet on your services.</p>
        <?php endif; ?>
    </div>

    <!-- ================= Reviews on My Assets ================= -->
    <div class="card">
        <h3>📦 Reviews on My Assets</h3>
        <?php if ($assetReviews->num_rows > 0): ?>
            <?php while ($r = $assetReviews->fetch_assoc()): ?>
                <div class="review-item">
                    <div class="review-top">
                        <a href="view_profile.php?user_id=<?php echo $r['reviewer_id']; ?>" class="review-author-link">
                            <span class="review-avatar"><?php echo strtoupper(substr($r['full_name'] ?: $r['username'], 0, 1)); ?></span>
                            <span class="review-author"><?php echo htmlspecialchars($r['full_name'] ?: $r['username']); ?></span>
                        </a>
                        <span class="review-stars"><?php echo starDisplay($r['rating']); ?></span>
                    </div>
                    <?php if (!empty($r['comment'])): ?>
                        <p class="review-comment"><?php echo nl2br(htmlspecialchars($r['comment'])); ?></p>
                    <?php endif; ?>
                    <div class="review-item-tag">on: <?php echo htmlspecialchars($r['item_title']); ?></div>
                    <div class="review-date"><?php echo date('d M Y', strtotime($r['created_at'])); ?></div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="empty-msg">No reviews yet on your assets.</p>
        <?php endif; ?>
    </div>

    <!-- ================= Reviews on My Materials ================= -->
    <div class="card">
        <h3>🖼️ Reviews on My Materials</h3>
        <?php if ($materialReviews->num_rows > 0): ?>
            <?php while ($r = $materialReviews->fetch_assoc()): ?>
                <div class="review-item">
                    <div class="review-top">
                        <a href="view_profile.php?user_id=<?php echo $r['reviewer_id']; ?>" class="review-author-link">
                            <span class="review-avatar"><?php echo strtoupper(substr($r['full_name'] ?: $r['username'], 0, 1)); ?></span>
                            <span class="review-author"><?php echo htmlspecialchars($r['full_name'] ?: $r['username']); ?></span>
                        </a>
                        <span class="review-stars"><?php echo starDisplay($r['rating']); ?></span>
                    </div>
                    <?php if (!empty($r['comment'])): ?>
                        <p class="review-comment"><?php echo nl2br(htmlspecialchars($r['comment'])); ?></p>
                    <?php endif; ?>
                    <div class="review-item-tag">on: <?php echo htmlspecialchars($r['item_title']); ?></div>
                    <div class="review-date"><?php echo date('d M Y', strtotime($r['created_at'])); ?></div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="empty-msg">No reviews yet on your materials.</p>
        <?php endif; ?>
    </div>

</div>

</body>
</html>