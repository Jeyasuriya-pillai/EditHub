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

$stmt = $conn->prepare("SELECT COALESCE(AVG(rating),0) AS avg_rating, COUNT(*) AS review_count FROM reviews WHERE target_type = ? AND target_id = ?");
$stmt->bind_param("si", $type, $id);
$stmt->execute();
$ratingInfo = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch reviews including review ID to enable deletion feature
$stmt = $conn->prepare("SELECT r.id AS review_id, r.rating, r.comment, r.created_at, u.id AS reviewer_id, u.username, u.full_name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.target_type = ? AND r.target_id = ? ORDER BY r.created_at DESC");
$stmt->bind_param("si", $type, $id);
$stmt->execute();
$reviews = $stmt->get_result();
$stmt->close();

$isOwner = ($_SESSION['user_id'] == $item['owner_id']);

function starDisplay($rating) {
    $rating = round($rating);
    return str_repeat('★', $rating) . str_repeat('☆', 5 - $rating);
}

$videoExts = ['mp4', 'webm', 'mov', 'mkv'];
$audioExts = ['mp3', 'wav', 'ogg', 'm4a', 'aac', 'flac', 'opus', 'wma', 'aif', 'aiff'];
$fileExt = !empty($item['file_path']) ? strtolower(pathinfo($item['file_path'], PATHINFO_EXTENSION)) : '';
$isVideo = in_array($fileExt, $videoExts);
$isAudio = in_array($fileExt, $audioExts);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo htmlspecialchars($item['title']); ?> - Reviews - EditHub</title>
<link rel="stylesheet" href="css/style.css">
<style>
    .wrap { max-width: 760px; margin: 0 auto; padding: 40px 20px 80px; }
    .item-card { display:flex; gap:20px; align-items:flex-start; flex-wrap:wrap; padding: 26px; margin-bottom: 26px; }
    .item-thumb { width:130px; height:130px; object-fit:cover; flex-shrink:0; background:var(--surface-2); border-radius: var(--radius); }
    .item-thumb-fallback { width:130px; height:130px; background:var(--surface-2); display:flex; align-items:center; justify-content:center; font-size:40px; flex-shrink:0; border-radius: var(--radius); }
    .item-title { font-size:24px; margin-bottom:6px; }
    .item-meta { color:var(--muted); font-size:13px; margin-bottom:10px; }
    .item-meta a { color:var(--teal); text-decoration:none; }
    .stars-big { font-size:18px; letter-spacing:2px; }

    label { display:block; font-size:13px; color:var(--muted); margin-bottom:6px; margin-top:14px; }
    select, textarea { width:100%; padding:10px 14px; border-radius:var(--radius); border:1px solid var(--line); background:var(--canvas); color:var(--ink); font-size:14px; outline:none; }
    textarea { resize:vertical; min-height:70px; }

    .review-item { border-bottom:1px solid var(--line); padding:14px 0; }
    .review-item:last-child { border-bottom:none; }
    .review-top { display:flex; justify-content:space-between; align-items:center; margin-bottom:6px; }
    .review-author { font-weight:700; font-size:14px; font-family: var(--font-display); }
    .review-comment { color:#c9ccd6; font-size:13px; line-height:1.5; }
    .review-bottom { display:flex; justify-content:space-between; align-items:center; margin-top:6px; }
    .review-date { color:var(--muted-2); font-size:11px; }
    .review-author-link { text-decoration:none; display:flex; align-items:center; gap:8px; color: var(--ink); }
    .review-avatar { width:26px; height:26px; border-radius:50%; background:var(--accent); display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:800; color:#0c0c0e; flex-shrink:0; }
    .btn-delete-review { color:#ef4444; font-size:12px; text-decoration:none; font-weight:600; cursor:pointer; }
    .btn-delete-review:hover { text-decoration:underline; }
</style>
</head>
<body>

<?php require_once 'php/navbar.php'; ?>

<div class="wrap">

    <div class="card item-card">
        <?php if (!empty($item['thumbnail'])): ?>
            <img src="<?php echo htmlspecialchars($item['thumbnail']); ?>" alt="" class="item-thumb">
        <?php else: ?>
            <div class="item-thumb-fallback"><?php echo $type === 'material' ? '🖼️' : ($type === 'asset' ? '📦' : '🎬'); ?></div>
        <?php endif; ?>
        <div style="flex:1; min-width:200px;">
        <div class="item-title"><?php echo htmlspecialchars($item['title']); ?></div>
        <div class="item-meta">
            By <a href="view_profile.php?id=<?php echo $item['owner_id']; ?>"><?php echo htmlspecialchars($item['full_name'] ?: $item['username']); ?></a>
        </div>
        <span class="stars stars-big"><?php echo starDisplay($ratingInfo['avg_rating']); ?></span>
        <span class="rating-count"><?php echo number_format($ratingInfo['avg_rating'], 1); ?> (<?php echo $ratingInfo['review_count']; ?> review<?php echo $ratingInfo['review_count'] != 1 ? 's' : ''; ?>)</span>

        <?php if ($isVideo): ?>
            <div style="margin-top:14px;">
                <video controls style="width:100%; max-width:420px; border-radius:var(--radius); background:#000;">
                    <source src="<?php echo htmlspecialchars($item['file_path']); ?>">
                    Your browser doesn't support video playback.
                </video>
            </div>
        <?php elseif ($isAudio): ?>
            <div style="margin-top:14px;">
                <audio controls style="width:100%; max-width:420px;">
                    <source src="<?php echo htmlspecialchars($item['file_path']); ?>">
                    Your browser doesn't support audio playback.
                </audio>
            </div>
        <?php endif; ?>

        <?php if (!empty($item['file_path'])): ?>
            <div style="margin-top:14px;">
                <a href="<?php echo htmlspecialchars($item['file_path']); ?>" download class="btn btn-primary btn-sm">
                    <?php echo ($type === 'asset' && ($item['asset_type'] ?? '') === 'paid') ? 'Buy / Download' : 'Download'; ?>
                </a>
            </div>
        <?php endif; ?>
        </div>
    </div>

    <!-- Review Form: Available to all users -->
    <?php if ($isOwner): ?>
        <div class="notice-msg" style="margin-bottom:20px;">👤 This is your own upload. You can also write a review on it below.</div>
    <?php endif; ?>

    <div class="card card-pad" style="margin-bottom:20px;">
        <h3 style="margin-bottom:16px;">Write a review</h3>
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

            <button type="submit" class="btn btn-primary" style="margin-top:18px;">Submit review</button>
        </form>
    </div>

    <div class="card card-pad">
        <h3 style="margin-bottom:16px;"><?php echo $isOwner ? '👥 Reviews from your clients' : 'All reviews'; ?></h3>
        <?php if ($reviews->num_rows > 0): ?>
            <?php while ($r = $reviews->fetch_assoc()): ?>
                <div class="review-item">
                    <div class="review-top">
                        <a href="view_profile.php?id=<?php echo $r['reviewer_id']; ?>" class="review-author-link">
                            <span class="review-avatar"><?php echo strtoupper(substr($r['full_name'] ?: $r['username'], 0, 1)); ?></span>
                            <span class="review-author"><?php echo htmlspecialchars($r['full_name'] ?: $r['username']); ?></span>
                        </a>
                        <span class="stars"><?php echo starDisplay($r['rating']); ?></span>
                    </div>
                    <?php if (!empty($r['comment'])): ?>
                        <p class="review-comment"><?php echo nl2br(htmlspecialchars($r['comment'])); ?></p>
                    <?php endif; ?>
                    <div class="review-bottom">
                        <div class="review-date"><?php echo date('d M Y', strtotime($r['created_at'])); ?></div>
                        <?php if ($r['reviewer_id'] == $_SESSION['user_id']): ?>
                            <a href="php/review_delete.php?id=<?php echo $r['review_id']; ?>" 
                               class="btn-delete-review" 
                               onclick="return confirm('Are you sure you want to delete this review?');">
                               🗑️ Delete
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="empty-msg">No reviews yet<?php echo $isOwner ? ' from clients.' : '.'; ?></p>
        <?php endif; ?>
    </div>

</div>

</body>
</html>