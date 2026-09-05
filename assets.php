<?php
session_start();
require_once 'php/db.php';

$freeAssets = $conn->query("
    SELECT ua.id, ua.title, ua.thumbnail, ua.file_path, u.id AS owner_id, u.username, u.full_name,
           COALESCE(AVG(r.rating),0) AS avg_rating, COUNT(r.id) AS review_count
    FROM user_assets ua JOIN users u ON ua.user_id = u.id
    LEFT JOIN reviews r ON r.target_type = 'asset' AND r.target_id = ua.id
    WHERE ua.type = 'free' GROUP BY ua.id ORDER BY ua.created_at DESC
");

$paidAssets = $conn->query("
    SELECT ua.id, ua.title, ua.price, ua.thumbnail, ua.file_path, u.id AS owner_id, u.username, u.full_name,
           COALESCE(AVG(r.rating),0) AS avg_rating, COUNT(r.id) AS review_count
    FROM user_assets ua JOIN users u ON ua.user_id = u.id
    LEFT JOIN reviews r ON r.target_type = 'asset' AND r.target_id = ua.id
    WHERE ua.type = 'paid' GROUP BY ua.id ORDER BY ua.created_at DESC
");

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
    <title>Editing Assets - EditHub</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .wrap { max-width: 1080px; margin: 0 auto; padding: 50px 40px 80px; }
        .section-block { margin-bottom: 56px; }
        .page-head .eyebrow { color: var(--accent); font-family: var(--font-display); font-weight: 800; font-size: 12px; letter-spacing: 0.08em; margin-bottom: 8px; }
        .section-block p.sub { color: var(--muted); font-size: 13px; margin-bottom: 22px; margin-top: 4px; }
        .asset-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 1px; background: var(--line); border: 1px solid var(--line); }
        .asset-card { background: var(--surface); overflow: hidden; }
        .asset-thumb { width: 100%; height: 130px; object-fit: cover; display: block; background: var(--surface-2); }
        .asset-thumb-fallback { width: 100%; height: 130px; display: flex; align-items: center; justify-content: center; font-size: 30px; background: var(--surface-2); }
        .asset-body { padding: 16px; }
        .asset-body h4 { font-family: var(--font-display); font-size: 15px; margin-bottom: 4px; }
        .by-line { color: var(--muted); font-size: 12px; margin-bottom: 6px; }
        .by-line a { color: var(--teal); text-decoration: none; }
        .stars-row { margin-bottom: 10px; }
        .asset-price { font-family: var(--font-display); font-weight: 800; color: var(--gold); display: block; margin-bottom: 8px; font-size: 15px; }
        .btn-row { display: flex; gap: 8px; }
        .btn-row .btn { flex: 1; }
        .empty-msg { color: var(--muted-2); font-size: 14px; }
    </style>
</head>
<body>

    <?php require_once 'php/navbar.php'; ?>

    <div class="wrap">
        <div class="page-head" style="margin-bottom:36px;">
            <div class="eyebrow">ASSET LIBRARY</div>
            <h1 style="font-size:32px;">Editing assets</h1>
        </div>

        <div class="section-block" id="free">
            <div class="eh-section-head"><h2>Free editing resources</h2></div>
            <p class="sub">Free LUTs, sound packs, and overlay effects uploaded by the community.</p>

            <div class="asset-grid">
                <?php if ($freeAssets && $freeAssets->num_rows > 0): ?>
                    <?php while ($a = $freeAssets->fetch_assoc()): ?>
                        <div class="asset-card">
                            <?php if ($a['thumbnail']): ?>
                                <img src="<?php echo htmlspecialchars($a['thumbnail']); ?>" class="asset-thumb" alt="">
                            <?php else: ?>
                                <div class="asset-thumb-fallback">📦</div>
                            <?php endif; ?>
                            <div class="asset-body">
                                <h4><?php echo htmlspecialchars($a['title']); ?></h4>
                                <div class="by-line">By <a href="view_profile.php?user_id=<?php echo $a['owner_id']; ?>"><?php echo htmlspecialchars($a['full_name'] ?: $a['username']); ?></a></div>
                                <div class="stars-row"><span class="stars"><?php echo starDisplay($a['avg_rating']); ?></span> <span class="rating-count">(<?php echo $a['review_count']; ?>)</span></div>
                                <div class="btn-row">
                                    <?php if (isset($_SESSION['user_id'])): ?>
                                        <a href="<?php echo htmlspecialchars($a['file_path']); ?>" class="btn btn-primary btn-sm" download>Download</a>
                                        <a href="review.php?type=asset&id=<?php echo $a['id']; ?>" class="btn btn-ghost btn-sm">Reviews</a>
                                    <?php else: ?>
                                        <a href="login.php" class="btn btn-disabled btn-sm btn-block">Login to download</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="empty-msg">No free assets uploaded yet.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="section-block" id="paid">
            <div class="eh-section-head"><h2>Premium asset packs</h2></div>
            <p class="sub">High-end packs uploaded by creators for professional-quality edits.</p>

            <div class="asset-grid">
                <?php if ($paidAssets && $paidAssets->num_rows > 0): ?>
                    <?php while ($a = $paidAssets->fetch_assoc()): ?>
                        <div class="asset-card">
                            <?php if ($a['thumbnail']): ?>
                                <img src="<?php echo htmlspecialchars($a['thumbnail']); ?>" class="asset-thumb" alt="">
                            <?php else: ?>
                                <div class="asset-thumb-fallback">📦</div>
                            <?php endif; ?>
                            <div class="asset-body">
                                <h4><?php echo htmlspecialchars($a['title']); ?></h4>
                                <div class="by-line">By <a href="view_profile.php?user_id=<?php echo $a['owner_id']; ?>"><?php echo htmlspecialchars($a['full_name'] ?: $a['username']); ?></a></div>
                                <div class="stars-row"><span class="stars"><?php echo starDisplay($a['avg_rating']); ?></span> <span class="rating-count">(<?php echo $a['review_count']; ?>)</span></div>
                                <span class="asset-price">₹<?php echo htmlspecialchars($a['price']); ?></span>
                                <div class="btn-row">
                                    <?php if (isset($_SESSION['user_id'])): ?>
                                        <a href="<?php echo htmlspecialchars($a['file_path']); ?>" class="btn btn-primary btn-sm">Buy now</a>
                                        <a href="review.php?type=asset&id=<?php echo $a['id']; ?>" class="btn btn-ghost btn-sm">Reviews</a>
                                    <?php else: ?>
                                        <a href="login.php" class="btn btn-disabled btn-sm btn-block">Login to buy</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="empty-msg">No paid assets uploaded yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <footer class="eh-footer">&copy; <?php echo date("Y"); ?> EditHub. All rights reserved.</footer>

</body>
</html>