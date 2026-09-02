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
<style>
    * { margin:0; padding:0; box-sizing:border-box; font-family:'Segoe UI', Roboto, sans-serif; }
    body { background:#090a10; color:#fff; }

    .navbar { display:flex; justify-content:space-between; align-items:center; padding:16px 45px; background:rgba(9,10,16,0.95); border-bottom:1px solid #1a1c28; position:sticky; top:0; z-index:100; }
    .logo { font-size:22px; font-weight:800; color:#fff; text-decoration:none; }
    .logo span { color:#8b5cf6; }
    .navbar nav a { color:#9ca3af; text-decoration:none; margin-left:22px; font-size:14px; font-weight:600; }
    .navbar nav a:hover { color:#8b5cf6; }

    .search-header { padding: 40px 20px 20px; max-width: 900px; margin: 0 auto; }
    .search-box { position:relative; }
    .search-input { width:100%; padding:14px 20px 14px 45px; border-radius:10px; border:1px solid #232738; background:#11131c; color:#fff; font-size:15px; outline:none; }
    .search-input:focus { border-color:#8b5cf6; box-shadow:0 0 15px rgba(139,92,246,0.25); }
    .search-icon { position:absolute; left:16px; top:50%; transform:translateY(-50%); font-size:16px; opacity:0.8; }
    .results-count { color:#9ca3af; font-size:13px; margin-top:14px; }

    .wrap { max-width: 900px; margin: 0 auto; padding: 10px 20px 80px; }
    .section-title { font-size:18px; font-weight:700; margin: 30px 0 16px; display:flex; align-items:center; gap:8px; }

    .materials-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 16px; }
    .material-card { background: #11131c; border-radius: 12px; padding: 20px; border: 1px solid #1f2333; }
    .material-icon { width: 40px; height: 40px; border-radius: 8px; background: rgba(139, 92, 246, 0.12); display: flex; align-items: center; justify-content: center; font-size: 20px; margin-bottom: 12px; }
    .material-title { font-weight: 700; font-size: 15px; margin-bottom: 4px; }
    .material-desc { font-size: 12px; color: #9ca3af; }

    .asset-item, .editor-item { background:#11131c; border:1px solid #1f2333; border-radius:10px; padding:16px 20px; margin-bottom:12px; display:flex; justify-content:space-between; align-items:center; }
    .asset-item h4, .editor-item h4 { font-size:14px; margin-bottom:4px; }
    .asset-item p, .editor-item p { font-size:12px; color:#9ca3af; }
    .badge { display:inline-block; font-size:11px; padding:2px 8px; border-radius:12px; background:rgba(139,92,246,0.15); color:#a78bfa; margin-left:6px; }
    .btn-small { background:#8b5cf6; color:#fff; padding:8px 16px; border-radius:6px; text-decoration:none; font-size:12px; font-weight:600; white-space:nowrap; }
    .btn-small:hover { background:#7c3aed; }

    .empty-state { text-align:center; padding:60px 20px; color:#6b7280; }
    .empty-state h3 { color:#9ca3af; margin-bottom:8px; }
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
        <div class="section-title">🖼️ Materials</div>
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
        <div class="section-title">📦 Assets</div>
        <?php while ($a = $assets->fetch_assoc()): ?>
            <div class="asset-item">
                <div>
                    <h4><?php echo htmlspecialchars($a['title']); ?> <span class="badge"><?php echo ucfirst($a['type']); ?></span></h4>
                    <p>By <?php echo htmlspecialchars($a['full_name'] ?: $a['username']); ?><?php echo $a['type'] === 'paid' ? ' • ₹' . htmlspecialchars($a['price']) : ' • Free'; ?></p>
                </div>
                <a href="<?php echo htmlspecialchars($a['file_path']); ?>" class="btn-small" download>Get Asset</a>
            </div>
        <?php endwhile; ?>
    <?php endif; ?>

    <?php if ($editors->num_rows > 0): ?>
        <div class="section-title">🎬 Editors & Services</div>
        <?php while ($s = $editors->fetch_assoc()): ?>
            <div class="editor-item">
                <div>
                    <h4><?php echo htmlspecialchars($s['title']); ?></h4>
                    <p>By <?php echo htmlspecialchars($s['full_name'] ?: $s['username']); ?><?php echo $s['price'] ? ' • ₹' . htmlspecialchars($s['price']) : ''; ?></p>
                </div>
                <a href="home.php#editors" class="btn-small">View</a>
            </div>
        <?php endwhile; ?>
    <?php endif; ?>

<?php endif; ?>

</div>

</body>
</html>