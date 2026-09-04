<?php
session_start();
require_once 'php/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$type = $_GET['type'] ?? '';
$id   = intval($_GET['id'] ?? 0);

$allowedTypes = ['asset', 'material', 'service'];
if (!in_array($type, $allowedTypes) || $id <= 0) {
    die("Invalid item.");
}

// Fetch the item + owner info
if ($type === 'asset') {
    $stmt = $conn->prepare("SELECT ua.title, ua.type AS asset_type, ua.price, ua.thumbnail, ua.file_path, u.id AS owner_id, u.username, u.full_name FROM user_assets ua JOIN users u ON ua.user_id = u.id WHERE ua.id = ?");
} elseif ($type === 'material') {
    $stmt = $conn->prepare("SELECT um.title, um.category, um.thumbnail, um.file_path, u.id AS owner_id, u.username, u.full_name FROM user_materials um JOIN users u ON um.user_id = u.id WHERE um.id = ?");
} else {
    $stmt = $conn->prepare("SELECT us.title, us.description, us.price, u.id AS owner_id, u.username, u.full_name FROM user_services us JOIN users u ON us.user_id = u.id WHERE us.id = ?");
}
$stmt->bind_param("i", $id);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$item) {
    die("Item not found.");
}

// Average rating
$stmt = $conn->prepare("SELECT COALESCE(AVG(rating),0) AS avg_rating, COUNT(*) AS review_count FROM reviews WHERE target_type = ? AND target_id = ?");
$stmt->bind_param("si", $type, $id);
$stmt->execute();
$ratingInfo = $stmt->get_result()->fetch_assoc();
$stmt->close();

// All reviews
$stmt = $conn->prepare("SELECT r.rating, r.comment, r.created_at, u.username, u.full_name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.target_type = ? AND r.target_id = ? ORDER BY r.created_at DESC");
$stmt->bind_param("si", $type, $id);
$stmt->execute();
$reviews = $stmt->get_result();
$stmt->close();

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
<title><?php echo htmlspecialchars($item['title']); ?> - Reviews - EditHub</title>
<style>
    * { margin:0; padding:0; box-sizing:border-box; font-family:'Segoe UI', Roboto, sans-serif; }
    body { background:#090a10; color:#fff; }
    .navbar { display:flex; justify-content:space-between; align-items:center; padding:16px 45px; background:rgba(9,10,16,0.95); border-bottom:1px solid #1a1c28; position:sticky; top:0; }
    .logo { font-size:22px; font-weight:800; color:#fff; text-decoration:none; }
    .logo span { color:#8b5cf6; }
    .navbar nav a { color:#9ca3af; text-decoration:none; margin-left:22px; font-size:14px; font-weight:600; }
    .navbar nav a:hover { color:#8b5cf6; }

    .wrap { max-width: 750px; margin: 40px auto; padding: 0 20px 80px; }
    .item-card { background:#11131c; border:1px solid #1f2333; border-radius:14px; padding:26px; margin-bottom:26px; }
    .item-title { font-size:22px; font-weight:800; margin-bottom:6px; }
    .item-meta { color:#9ca3af; font-size:13px; margin-bottom:10px; }
    .item-meta a { color:#a78bfa; text-decoration:none; }
    .stars { color:#f59e0b; font-size:20px; letter-spacing:2px; }
    .rating-count { color:#9ca3af; font-size:13px; margin-left:8px; }

    .card { background:#11131c; border:1px solid #1f2333; border-radius:12px; padding:24px; margin-bottom:20px; }
    .card h3 { font-size:16px; margin-bottom:16px; }

    label { display:block; font-size:13px; color:#9ca3af; margin-bottom:6px; margin-top:14px; }
    select, textarea { width:100%; padding:10px 14px; border-radius:8px; border:1px solid #232738; background:#090a10; color:#fff; font-size:14px; outline:none; }
    textarea { resize:vertical; min-height:70px; }
    .btn { background:#8b5cf6; color:#fff; border:none; padding:10px 20px; border-radius:8px; font-weight:700; font-size:13px; cursor:pointer; margin-top:16px; }
    .btn:hover { background:#7c3aed; }

    .review-item { border-bottom:1px solid #1f2333; padding:14px 0; }
    .review-item:last-child { border-bottom:none; }
    .review-top { display:flex; justify-content:space-between; align-items:center; margin-bottom:6px; }
    .review-author { font-weight:700; font-size:14px; }
    .review-stars { color:#f59e0b; font-size:14px; }
    .review-comment { color:#c9ccd6; font-size:13px; line-height:1.5; }
    .review-date { color:#6b7280; font-size:11px; margin-top:4px; }
    .empty-msg { color:#6b7280; font-size:13px; }
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

    <div class="item-card">
        <div class="item-title"><?php echo htmlspecialchars($item['title']); ?></div>
        <div class="item-meta">
            By <a href="view_profile.php?user_id=<?php echo $item['owner_id']; ?>"><?php echo htmlspecialchars($item['full_name'] ?: $item['username']); ?></a>
        </div>
        <span class="stars"><?php echo starDisplay($ratingInfo['avg_rating']); ?></span>
        <span class="rating-count"><?php echo number_format($ratingInfo['avg_rating'], 1); ?> (<?php echo $ratingInfo['review_count']; ?> review<?php echo $ratingInfo['review_count'] != 1 ? 's' : ''; ?>)</span>
        <?php if (!empty($item['file_path'])): ?>
            <div style="margin-top:14px;">
                <a href="<?php echo htmlspecialchars($item['file_path']); ?>" download style="background:#8b5cf6; color:#fff; padding:8px 18px; border-radius:6px; text-decoration:none; font-size:13px; font-weight:700;">
                    <?php echo ($type === 'asset' && ($item['asset_type'] ?? '') === 'paid') ? 'Buy / Download' : 'Download'; ?>
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Add Review -->
    <div class="card">
        <h3>Write a Review</h3>
        <form action="php/review_add.php" method="POST">
            <input type="hidden" name="target_type" value="<?php echo htmlspecialchars($type); ?>">
            <input type="hidden" name="target_id" value="<?php echo $id; ?>">

            <label>Rating</label>
            <select name="rating" required>
                <option value="">Select rating</option>
                <option value="5">★★★★★ Excellent</option>
                <option value="4">★★★★☆ Good</option>
                <option value="3">★★★☆☆ Average</option>
                <option value="2">★★☆☆☆ Below Average</option>
                <option value="1">★☆☆☆☆ Poor</option>
            </select>

            <label>Comment</label>
            <textarea name="comment" placeholder="Share your experience..."></textarea>

            <button type="submit" class="btn">Submit Review</button>
        </form>
    </div>

    <!-- All Reviews -->
    <div class="card">
        <h3>All Reviews</h3>
        <?php if ($reviews->num_rows > 0): ?>
            <?php while ($r = $reviews->fetch_assoc()): ?>
                <div class="review-item">
                    <div class="review-top">
                        <span class="review-author"><?php echo htmlspecialchars($r['full_name'] ?: $r['username']); ?></span>
                        <span class="review-stars"><?php echo starDisplay($r['rating']); ?></span>
                    </div>
                    <?php if (!empty($r['comment'])): ?>
                        <p class="review-comment"><?php echo nl2br(htmlspecialchars($r['comment'])); ?></p>
                    <?php endif; ?>
                    <div class="review-date"><?php echo date('d M Y', strtotime($r['created_at'])); ?></div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="empty-msg">No reviews yet. Be the first to review!</p>
        <?php endif; ?>
    </div>

</div>

</body>
</html>