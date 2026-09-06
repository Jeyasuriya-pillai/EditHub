<?php
session_start();
require_once 'php/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$categoryIcons = ['PNG' => '🖼️', 'SFX' => '🔊', 'CC' => '🎨', 'VFX' => '✨'];
$categoryLabels = ['PNG' => 'HD PNG Cutouts', 'SFX' => 'SFX & Sound Packs', 'CC' => 'CC & Color Grading', 'VFX' => 'VFX & Video Effects'];

function starDisplay($rating) {
    $rating = round($rating);
    return str_repeat('★', $rating) . str_repeat('☆', 5 - $rating);
}

// Fetch all materials grouped by category
$materialsByCategory = [];
$result = $conn->query("
    SELECT um.id, um.title, um.category, um.thumbnail, u.id AS owner_id, u.username, u.full_name,
           COALESCE(AVG(r.rating),0) AS avg_rating, COUNT(r.id) AS review_count
    FROM user_materials um
    JOIN users u ON um.user_id = u.id
    LEFT JOIN reviews r ON r.target_type = 'material' AND r.target_id = um.id
    GROUP BY um.id
    ORDER BY um.created_at DESC
");
while ($row = $result->fetch_assoc()) {
    $cat = $row['category'];
    if (!isset($materialsByCategory[$cat])) $materialsByCategory[$cat] = [];
    $materialsByCategory[$cat][] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editing Materials - EditHub</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .wrap { max-width: 1080px; margin: 0 auto; padding: 50px 40px 80px; }
        .page-head { margin-bottom: 40px; }
        .page-head .eyebrow { color: var(--accent); font-family: var(--font-display); font-weight: 800; font-size: 12px; letter-spacing: 0.08em; margin-bottom: 8px; }
        .cat-section { margin-bottom: 50px; }
        .cat-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 1px; background: var(--line); border: 1px solid var(--line); }
        .mat-card { background: var(--surface); overflow: hidden; }
        .mat-thumb { width: 100%; height: 130px; object-fit: cover; display: block; background: var(--surface-2); }
        .mat-thumb-fallback { width: 100%; height: 130px; display: flex; align-items: center; justify-content: center; font-size: 32px; background: var(--surface-2); }
        .mat-body { padding: 16px; }
        .mat-body h4 { font-family: var(--font-display); font-size: 15px; margin-bottom: 4px; }
        .by-line { color: var(--muted); font-size: 12px; margin-bottom: 6px; }
        .by-line a { color: var(--teal); text-decoration: none; }
        .stars-row { margin-bottom: 10px; }
        .empty-msg { color: var(--muted-2); font-size: 14px; }
    </style>
</head>
<body>

    <?php require_once 'php/navbar.php'; ?>

    <div class="wrap">
        <div class="page-head">
            <div class="eyebrow">MATERIAL LIBRARY</div>
            <h1 style="font-size:32px;">Editing materials & resources</h1>
        </div>

        <?php if (empty($materialsByCategory)): ?>
            <p class="empty-msg">No materials uploaded yet. Be the first — upload from your profile!</p>
        <?php else: ?>
            <?php foreach (['PNG', 'SFX', 'CC', 'VFX'] as $cat): ?>
                <?php if (!empty($materialsByCategory[$cat])): ?>
                    <div class="cat-section">
                        <div class="eh-section-head">
                            <h2><?php echo $categoryIcons[$cat]; ?> <?php echo $categoryLabels[$cat]; ?></h2>
                            <span class="count"><?php echo count($materialsByCategory[$cat]); ?> item<?php echo count($materialsByCategory[$cat]) != 1 ? 's' : ''; ?></span>
                        </div>
                        <div class="cat-grid">
                            <?php foreach ($materialsByCategory[$cat] as $m): ?>
                                <div class="mat-card">
                                    <?php if ($m['thumbnail']): ?>
                                        <img src="<?php echo htmlspecialchars($m['thumbnail']); ?>" class="mat-thumb" alt="">
                                    <?php else: ?>
                                        <div class="mat-thumb-fallback"><?php echo $categoryIcons[$cat]; ?></div>
                                    <?php endif; ?>
                                    <div class="mat-body">
                                        <h4><?php echo htmlspecialchars($m['title']); ?></h4>
                                        <div class="by-line">By <a href="view_profile.php?user_id=<?php echo $m['owner_id']; ?>"><?php echo htmlspecialchars($m['full_name'] ?: $m['username']); ?></a></div>
                                        <div class="stars-row"><span class="stars"><?php echo starDisplay($m['avg_rating']); ?></span> <span class="rating-count">(<?php echo $m['review_count']; ?>)</span></div>
                                        <a href="review.php?type=material&id=<?php echo $m['id']; ?>" class="btn btn-primary btn-sm btn-block">View & review</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <footer class="eh-footer">&copy; <?php echo date("Y"); ?> EditHub. All rights reserved.</footer>

</body>
</html>