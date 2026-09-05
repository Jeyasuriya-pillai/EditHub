<?php
session_start();
require_once 'php/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'] ?? 'User';

// Dynamic materials with thumbnail + ratings
$materials = $conn->query("
    SELECT um.id, um.title, um.category, um.thumbnail,
           COALESCE(AVG(r.rating),0) AS avg_rating, COUNT(r.id) AS review_count
    FROM user_materials um
    LEFT JOIN reviews r ON r.target_type = 'material' AND r.target_id = um.id
    GROUP BY um.id
    ORDER BY um.created_at DESC LIMIT 8
");

// Dynamic hire-me services with rating info
$editors = $conn->query("
    SELECT us.id, us.title, us.description, us.price, u.id AS owner_id, u.username, u.full_name,
           COALESCE(AVG(r.rating),0) AS avg_rating, COUNT(r.id) AS review_count
    FROM user_services us
    JOIN users u ON us.user_id = u.id
    LEFT JOIN reviews r ON r.target_type = 'service' AND r.target_id = us.id
    GROUP BY us.id
    ORDER BY us.created_at DESC LIMIT 12
");

// Dynamic assets with thumbnail and rating
$assets = $conn->query("
    SELECT ua.id, ua.title, ua.type, ua.price, ua.thumbnail,
           COALESCE(AVG(r.rating),0) AS avg_rating, COUNT(r.id) AS review_count
    FROM user_assets ua
    LEFT JOIN reviews r ON r.target_type = 'asset' AND r.target_id = ua.id
    GROUP BY ua.id
    ORDER BY ua.created_at DESC LIMIT 8
");

$categoryIcons = ['PNG' => '🖼️', 'SFX' => '🔊', 'CC' => '🎨', 'VFX' => '✨'];

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
    <title>EditHub - Home</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .hero { padding: 46px 40px 36px; max-width: 900px; }
        .search-box { max-width: 560px; position: relative; margin-bottom: 26px; }
        .search-input { width: 100%; padding: 13px 18px 13px 42px; }
        .search-icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); font-size: 15px; opacity: 0.7; }
        .hero h1 { font-size: 42px; margin-bottom: 14px; max-width: 700px; }
        .hero p { color: var(--muted); font-size: 15px; line-height: 1.7; max-width: 640px; margin-bottom: 26px; }
        .hero .btn-row { display: flex; gap: 12px; }

        .main-wrap { max-width: 1180px; margin: 0 auto; padding: 10px 40px 80px; }

        .grid-4 { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 1px; background: var(--line); border: 1px solid var(--line); margin-bottom: 56px; }
        .thumb-card { background: var(--surface); overflow: hidden; position: relative; }
        .thumb-img { width: 100%; height: 130px; object-fit: cover; display: block; background: var(--surface-2); }
        .thumb-fallback { width: 100%; height: 130px; display: flex; align-items: center; justify-content: center; font-size: 32px; background: var(--surface-2); }
        .thumb-body { padding: 16px; }
        .thumb-title { font-family: var(--font-display); font-weight: 700; font-size: 15px; margin-bottom: 6px; }
        .thumb-desc { font-size: 12px; color: var(--muted); line-height: 1.4; margin-bottom: 8px; }
        .price-badge { position: absolute; top: 10px; right: 10px; background: rgba(0,0,0,0.75); padding: 3px 10px; font-size: 11px; font-weight: 700; border-radius: 3px; }
        .price-free { color: var(--success); }
        .price-paid { color: var(--gold); }
        .stars-row { margin-top: 6px; margin-bottom: 10px; }

        .editors-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1px; background: var(--line); border: 1px solid var(--line); }
        .editor-card { background: var(--surface); padding: 18px; display: flex; justify-content: space-between; align-items: center; gap: 12px; }
        .editor-profile-link { text-decoration: none; color: inherit; flex: 1; min-width: 0; }
        .editor-info h4 { font-family: var(--font-display); font-size: 15px; font-weight: 700; }
        .editor-info h4:hover { color: var(--accent); }
        .editor-info p { font-size: 12px; color: var(--muted); margin-top: 2px; }
        .editor-meta { font-size: 12px; color: var(--teal); margin-top: 4px; font-weight: 700; }
        .btn-group { display: flex; gap: 6px; }
    </style>
</head>
<body>

    <?php require_once 'php/navbar.php'; ?>

    <section class="hero">
        <form action="search.php" method="GET">
            <div class="search-box">
                <span class="search-icon">🔍</span>
                <input type="text" name="q" class="search-input" placeholder="Search 5,000+ PNGs, SFX, CC Presets or Editors...">
            </div>
        </form>
        <h1>Best edit materials & top video editors in 2026</h1>
        <p>Get high-quality PNGs, sound effects, motion presets, and color grading packs — or hire an expert freelance video editor for your next project.</p>
        <div class="btn-row">
            <a href="#editors" class="btn btn-primary">Hire video editors</a>
            <a href="#materials" class="btn btn-outline">Explore asset packs</a>
        </div>
    </section>

    <div class="ruler"></div>

    <div class="main-wrap">

        <!-- Community Assets -->
        <section id="assets-preview" style="margin-top:48px;">
            <div class="eh-section-head"><h2>Community Assets</h2></div>
            <div class="grid-4">
                <?php if ($assets && $assets->num_rows > 0): ?>
                    <?php while ($a = $assets->fetch_assoc()): ?>
                        <div class="thumb-card">
                            <span class="price-badge <?php echo $a['type'] === 'free' ? 'price-free' : 'price-paid'; ?>">
                                <?php echo $a['type'] === 'free' ? 'FREE' : '₹' . htmlspecialchars($a['price']); ?>
                            </span>
                            <?php if ($a['thumbnail']): ?>
                                <img src="<?php echo htmlspecialchars($a['thumbnail']); ?>" class="thumb-img" alt="">
                            <?php else: ?>
                                <div class="thumb-fallback">📦</div>
                            <?php endif; ?>
                            <div class="thumb-body">
                                <div class="thumb-title"><?php echo htmlspecialchars($a['title']); ?></div>
                                <div class="stars-row"><span class="stars"><?php echo starDisplay($a['avg_rating']); ?></span><span class="rating-count">(<?php echo $a['review_count']; ?>)</span></div>
                                <!-- Updated Link with from=home -->
                                <a href="review.php?type=asset&id=<?php echo $a['id']; ?>&from=home" class="btn btn-primary btn-sm btn-block">View & review</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="empty-msg">No assets uploaded yet.</p>
                <?php endif; ?>
            </div>
        </section>

        <!-- Materials -->
        <section id="materials">
            <div class="eh-section-head"><h2>Editing Materials & Resources</h2></div>
            <div class="grid-4">
                <?php if ($materials && $materials->num_rows > 0): ?>
                    <?php while ($m = $materials->fetch_assoc()): ?>
                        <div class="thumb-card">
                            <?php if ($m['thumbnail']): ?>
                                <img src="<?php echo htmlspecialchars($m['thumbnail']); ?>" class="thumb-img" alt="">
                            <?php else: ?>
                                <div class="thumb-fallback"><?php echo $categoryIcons[$m['category']] ?? '📁'; ?></div>
                            <?php endif; ?>
                            <div class="thumb-body">
                                <div class="thumb-title"><?php echo htmlspecialchars($m['title']); ?></div>
                                <div class="thumb-desc"><?php echo htmlspecialchars($m['category']); ?> material by a community creator</div>
                                <div class="stars-row"><span class="stars"><?php echo starDisplay($m['avg_rating']); ?></span><span class="rating-count">(<?php echo $m['review_count']; ?>)</span></div>
                                <!-- Updated Link with from=home -->
                                <a href="review.php?type=material&id=<?php echo $m['id']; ?>&from=home" class="btn btn-primary btn-sm btn-block">View & review</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="empty-msg">No materials uploaded yet. Be the first — upload from your profile!</p>
                <?php endif; ?>
            </div>
        </section>

        <!-- Editors -->
        <section id="editors">
            <div class="eh-section-head"><h2>Hire freelance video editors</h2></div>
            <div class="editors-grid">
                <?php if ($editors && $editors->num_rows > 0): ?>
                    <?php while ($s = $editors->fetch_assoc()): ?>
                        <div class="editor-card">
                            <a href="view_profile.php?user_id=<?php echo $s['owner_id']; ?>" class="editor-profile-link">
                                <div class="editor-info">
                                    <h4><?php echo htmlspecialchars($s['full_name'] ?: $s['username']); ?></h4>
                                    <p><?php echo htmlspecialchars($s['title']); ?></p>
                                    <div class="editor-meta"><?php echo $s['price'] ? '₹' . htmlspecialchars($s['price']) : 'Contact for price'; ?></div>
                                    <div class="stars-row"><span class="stars"><?php echo starDisplay($s['avg_rating']); ?></span><span class="rating-count">(<?php echo $s['review_count']; ?>)</span></div>
                                </div>
                            </a>
                            <div class="btn-group">
                                <a href="view_profile.php?user_id=<?php echo $s['owner_id']; ?>" class="btn btn-outline btn-sm">Profile</a>
                                <!-- Write Review Button added for Services -->
                                <a href="review.php?type=service&id=<?php echo $s['id']; ?>&from=home" class="btn btn-primary btn-sm">Review</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="empty-msg">No editors listed yet. Add "My Personal Services" from your profile!</p>
                <?php endif; ?>
            </div>
        </section>

    </div>

    <footer class="eh-footer">&copy; <?php echo date("Y"); ?> EditHub. All rights reserved.</footer>

</body>
</html>