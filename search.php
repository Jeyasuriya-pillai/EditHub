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

    $stmt = $conn->prepare("SELECT title, category FROM user_materials WHERE title LIKE ? OR category LIKE ? ORDER BY created_at DESC");
    $stmt->bind_param("ss", $like, $like);
    $stmt->execute();
    $materials = $stmt->get_result();
    $stmt->close();

    $stmt = $conn->prepare("
        SELECT ua.title, ua.type, ua.price, ua.file_path, u.username, u.full_name
        FROM user_assets ua JOIN users u ON ua.user_id = u.id
        WHERE ua.title LIKE ?
        ORDER BY ua.created_at DESC
    ");
    $stmt->bind_param("s", $like);
    $stmt->execute();
    $assets = $stmt->get_result();
    $stmt->close();

    $stmt = $conn->prepare("
        SELECT us.title, us.description, us.price, u.username, u.full_name
        FROM user_services us JOIN users u ON us.user_id = u.id
        WHERE us.title LIKE ? OR us.description LIKE ? OR u.username LIKE ? OR u.full_name LIKE ?
        ORDER BY us.created_at DESC
    ");
    $stmt->bind_param("ssss", $like, $like, $like, $like);
    $stmt->execute();
    $editors = $stmt->get_result();
    $stmt->close();
}

$categoryIcons = ['PNG' => '🖼️', 'SFX' => '🔊', 'CC' => '🎨', 'VFX' => '✨'];
$totalResults = ($materials ? $materials->num_rows : 0) + ($assets ? $assets->num_rows : 0) + ($editors ? $editors->num_rows : 0);
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
        <div class="materials-grid">
            <?php while ($m = $materials->fetch_assoc()): ?>
                <div class="material-card">
                    <div class="material-icon"><?php echo $categoryIcons[$m['category']] ?? '📁'; ?></div>
                    <div class="material-title"><?php echo htmlspecialchars($m['title']); ?></div>
                    <div class="material-desc"><?php echo htmlspecialchars($m['category']); ?></div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>

    <?php if ($assets->num_rows > 0): ?>
        <div class="result-section-title">📦 Assets</div>
        <div class="card">
        <?php while ($a = $assets->fetch_assoc()): ?>
            <div class="asset-item">
                <div>
                    <h4><?php echo htmlspecialchars($a['title']); ?> <span class="eh-badge"><?php echo ucfirst($a['type']); ?></span></h4>
                    <p>By <?php echo htmlspecialchars($a['full_name'] ?: $a['username']); ?><?php echo $a['type'] === 'paid' ? ' • ₹' . htmlspecialchars($a['price']) : ' • Free'; ?></p>
                </div>
                <a href="<?php echo htmlspecialchars($a['file_path']); ?>" class="btn btn-primary btn-sm" download>Get asset</a>
            </div>
        <?php endwhile; ?>
        </div>
    <?php endif; ?>

    <?php if ($editors->num_rows > 0): ?>
        <div class="result-section-title">🎬 Editors & services</div>
        <div class="card">
        <?php while ($s = $editors->fetch_assoc()): ?>
            <div class="editor-item">
                <div>
                    <h4><?php echo htmlspecialchars($s['title']); ?></h4>
                    <p>By <?php echo htmlspecialchars($s['full_name'] ?: $s['username']); ?><?php echo $s['price'] ? ' • ₹' . htmlspecialchars($s['price']) : ''; ?></p>
                </div>
                <a href="home.php#editors" class="btn btn-ghost btn-sm">View</a>
            </div>
        <?php endwhile; ?>
        </div>
    <?php endif; ?>

<?php endif; ?>

</div>

</body>
</html>