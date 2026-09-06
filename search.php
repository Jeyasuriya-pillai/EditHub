<?php
session_start();
require_once 'php/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'] ?? 'User';
$query = trim($_GET['q'] ?? '');

$materials = null;
$assets = null;
$editors = null;

if ($query !== '') {
    $like = '%' . $query . '%';

    $stmt = $conn->prepare("SELECT id, title, category, thumbnail FROM user_materials WHERE title LIKE ? OR category LIKE ? ORDER BY created_at DESC");
    $stmt->bind_param("ss", $like, $like);
    $stmt->execute();
    $materials = $stmt->get_result();
    $stmt->close();

    $stmt = $conn->prepare("
        SELECT ua.id, ua.title, ua.type, ua.price, ua.thumbnail, ua.file_path, u.id AS owner_id, u.username, u.full_name
        FROM user_assets ua JOIN users u ON ua.user_id = u.id
        WHERE ua.title LIKE ?
        ORDER BY ua.created_at DESC
    ");
    $stmt->bind_param("s", $like);
    $stmt->execute();
    $assets = $stmt->get_result();
    $stmt->close();

    $stmt = $conn->prepare("
        SELECT us.id, us.title, us.description, us.price, u.id AS owner_id, u.username, u.full_name,
               COALESCE(AVG(r.rating),0) AS avg_rating, COUNT(r.id) AS review_count
        FROM user_services us JOIN users u ON us.user_id = u.id
        LEFT JOIN reviews r ON r.target_type = 'service' AND r.target_id = us.id
        WHERE us.title LIKE ? OR us.description LIKE ? OR u.username LIKE ? OR u.full_name LIKE ?
        GROUP BY us.id
        ORDER BY us.created_at DESC
    ");
    $stmt->bind_param("ssss", $like, $like, $like, $like);
    $stmt->execute();
    $editors = $stmt->get_result();
    $stmt->close();
}

$categoryIcons = ['PNG' => '🖼️', 'SFX' => '🔊', 'CC' => '🎨', 'VFX' => '✨'];
$totalResults = ($materials ? $materials->num_rows : 0) + ($assets ? $assets->num_rows : 0) + ($editors ? $editors->num_rows : 0);

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
<title>Search results for "<?php echo htmlspecialchars($query); ?>" - EditHub</title>
<link rel="stylesheet" href="css/style.css">
<style>
    .search-header { padding: 40px 40px 20px; max-width: 900px; margin: 0 auto; }
    .search-box { position:relative; }
    .search-input { width:100%; padding:14px 20px 14px 45px; }
    .search-icon { position:absolute; left:16px; top:50%; transform:translateY(-50%); font-size:16px; opacity:0.7; }
    .results-count { color:var(--muted); font-size:13px; margin-top:14px; }

    .wrap { max-width: 900px; margin: 0 auto; padding: 10px 40px 80px; }
    .result-section-title { font-family: var(--font-display); font-size:18px; margin: 34px 0 16px; }

    .materials-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 1px; background: var(--line); border: 1px solid var(--line); }
    .material-card { background: var(--surface); padding: 20px; }
    .material-icon { width: 40px; height: 40px; border-radius: var(--radius); background: rgba(255,90,54,0.1); display: flex; align-items: center; justify-content: center; font-size: 20px; margin-bottom: 12px; }
    .material-title { font-family: var(--font-display); font-weight: 700; font-size: 15px; margin-bottom: 4px; }
    .material-desc { font-size: 12px; color: var(--muted); }

    .asset-item, .editor-item { padding:16px 20px; margin-bottom:1px; display:flex; justify-content:space-between; align-items:center; }
    .asset-item h4, .editor-item h4 { font-size:14px; margin-bottom:4px; font-family: var(--font-display); }
    .asset-item p, .editor-item p { font-size:12px; color:var(--muted); }

    .empty-state { text-align:center; padding:60px 20px; color:var(--muted-2); }
    .empty-state h3 { color:var(--muted); margin-bottom:8px; font-family: var(--font-display); }

    .asset-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 1px; background: var(--line); border: 1px solid var(--line); }
    .asset-card { background: var(--surface); overflow: hidden; }
    .asset-thumb { width: 100%; height: 130px; object-fit: cover; display: block; background: var(--surface-2); }
    .asset-thumb-fallback { width: 100%; height: 130px; display: flex; align-items: center; justify-content: center; font-size: 30px; background: var(--surface-2); }
    .asset-body { padding: 16px; }
    .asset-body h4 { font-family: var(--font-display); font-size: 15px; margin-bottom: 4px; }
    .by-line { color: var(--muted); font-size: 12px; margin-bottom: 10px; }
    .by-line a { color: var(--teal); text-decoration: none; }
    .btn-row { display: flex; gap: 8px; }
    .btn-row .btn { flex: 1; }

    .editor-card { padding:18px; display:flex; align-items:center; gap:14px; border-bottom:1px solid var(--line); }
    .editor-card:last-child { border-bottom:none; }
    .editor-avatar { width:44px; height:44px; border-radius:50%; background:var(--accent); color:#0c0c0e; display:flex; align-items:center; justify-content:center; font-size:16px; font-weight:900; flex-shrink:0; font-family:var(--font-display); }
    .editor-info { flex:1; min-width:0; }
    .editor-info h4 { font-family:var(--font-display); font-size:15px; margin-bottom:2px; }
    .editor-info p { font-size:12px; color:var(--muted); }
    .editor-btns { display:flex; gap:8px; flex-shrink:0; }
</style>
</head>
<body>

<?php require_once 'php/navbar.php'; ?>

<div class="search-header">
    <form action="search.php" method="GET">
        <div class="search-box">
            <span class="search-icon">🔍</span>
            <input type="text" name="q" class="search-input" placeholder="Search PNGs, SFX, CC Presets or Editors..." value="<?php echo htmlspecialchars($query); ?>" autofocus>
        </div>
    </form>
    <?php if ($query !== ''): ?>
        <p class="results-count"><?php echo $totalResults; ?> result<?php echo $totalResults !== 1 ? 's' : ''; ?> for "<?php echo htmlspecialchars($query); ?>"</p>
    <?php endif; ?>
</div>

<div class="wrap">

<?php if ($query === ''): ?>

    <div class="empty-state">
        <h3>Start typing above to search</h3>
        <p>Search across editing materials, assets, and freelance editors.</p>
    </div>

<?php elseif ($totalResults === 0): ?>

    <div class="empty-state">
        <h3>No results found</h3>
        <p>Try a different keyword.</p>
    </div>

<?php else: ?>

    <?php if ($materials->num_rows > 0): ?>
        <div class="result-section-title">🖼️ Materials</div>
        <div class="asset-grid">
            <?php while ($m = $materials->fetch_assoc()): ?>
                <div class="asset-card">
                    <?php if ($m['thumbnail']): ?>
                        <img src="<?php echo htmlspecialchars($m['thumbnail']); ?>" class="asset-thumb" alt="">
                    <?php else: ?>
                        <div class="asset-thumb-fallback"><?php echo $categoryIcons[$m['category']] ?? '📁'; ?></div>
                    <?php endif; ?>
                    <div class="asset-body">
                        <h4><?php echo htmlspecialchars($m['title']); ?></h4>
                        <div class="by-line"><?php echo htmlspecialchars($m['category']); ?></div>
                        <a href="review.php?type=material&id=<?php echo $m['id']; ?>" class="btn btn-primary btn-sm btn-block">View & review</a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>

    <?php if ($assets->num_rows > 0): ?>
        <div class="result-section-title">📦 Assets</div>
        <div class="asset-grid">
            <?php while ($a = $assets->fetch_assoc()): ?>
                <div class="asset-card">
                    <?php if ($a['thumbnail']): ?>
                        <img src="<?php echo htmlspecialchars($a['thumbnail']); ?>" class="asset-thumb" alt="">
                    <?php else: ?>
                        <div class="asset-thumb-fallback">📦</div>
                    <?php endif; ?>
                    <div class="asset-body">
                        <h4><?php echo htmlspecialchars($a['title']); ?> <span class="eh-badge"><?php echo ucfirst($a['type']); ?></span></h4>
                        <div class="by-line">By <?php echo htmlspecialchars($a['full_name'] ?: $a['username']); ?><?php echo $a['type'] === 'paid' ? ' • ₹' . htmlspecialchars($a['price']) : ' • Free'; ?></div>
                        <div class="btn-row">
                            <a href="<?php echo htmlspecialchars($a['file_path']); ?>" class="btn btn-primary btn-sm" download>Get asset</a>
                            <a href="review.php?type=asset&id=<?php echo $a['id']; ?>" class="btn btn-ghost btn-sm">View & review</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>

    <?php if ($editors->num_rows > 0): ?>
        <div class="result-section-title">🎬 Editors & services</div>
        <div class="card">
        <?php while ($s = $editors->fetch_assoc()): ?>
            <div class="editor-card">
                <div class="editor-avatar"><?php echo strtoupper(substr($s['full_name'] ?: $s['username'], 0, 1)); ?></div>
                <div class="editor-info">
                    <h4><?php echo htmlspecialchars($s['full_name'] ?: $s['username']); ?> — <?php echo htmlspecialchars($s['title']); ?></h4>
                    <p><?php echo $s['price'] ? '₹' . htmlspecialchars($s['price']) : 'Contact for price'; ?> <span class="stars"><?php echo starDisplay($s['avg_rating']); ?></span> <span class="rating-count">(<?php echo $s['review_count']; ?>)</span></p>
                </div>
                <div class="editor-btns">
                    <a href="view_profile.php?user_id=<?php echo $s['owner_id']; ?>" class="btn btn-primary btn-sm">View profile</a>
                    <a href="review.php?type=service&id=<?php echo $s['id']; ?>" class="btn btn-ghost btn-sm">Reviews</a>
                </div>
            </div>
        <?php endwhile; ?>
        </div>
    <?php endif; ?>

<?php endif; ?>

</div>

</body>
</html>