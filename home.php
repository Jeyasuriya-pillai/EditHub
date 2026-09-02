<?php
session_start();
require_once 'php/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'] ?? 'User';

// 1. Dynamic materials (uploaded by all users)
$materials = $conn->query("SELECT title, category, file_path FROM user_materials ORDER BY created_at DESC LIMIT 8");

// 2. Dynamic assets (uploaded by all users) - NEWLY ADDED
$assets = $conn->query("
    SELECT ua.title, ua.type, ua.price, ua.file_path, u.username 
    FROM user_assets ua
    JOIN users u ON ua.user_id = u.id
    ORDER BY ua.created_at DESC LIMIT 8
");

// 3. Dynamic hire-me services (uploaded by all users)
$editors = $conn->query("
    SELECT us.id, us.title, us.description, us.price, u.username, u.full_name, u.tag
    FROM user_services us
    JOIN users u ON us.user_id = u.id
    ORDER BY us.created_at DESC LIMIT 12
");

$categoryIcons = ['PNG' => '🖼️', 'SFX' => '🔊', 'CC' => '🎨', 'VFX' => '✨'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EditHub - Home</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Roboto, -apple-system, sans-serif; scroll-behavior: smooth; }
        body { background-color: #090a10; color: #ffffff; }

        .navbar { display: flex; justify-content: space-between; align-items: center; padding: 16px 45px; background: rgba(9, 10, 16, 0.95); border-bottom: 1px solid #1a1c28; position: sticky; top: 0; z-index: 100; backdrop-filter: blur(8px); }
        .logo { font-size: 24px; font-weight: 800; color: #ffffff; text-decoration: none; letter-spacing: -0.5px; }
        .logo span { color: #8b5cf6; }

        .nav-links { display: flex; gap: 28px; list-style: none; align-items: center; }
        .nav-links > li { position: relative; }
        .nav-links a { color: #9ca3af; text-decoration: none; font-size: 14px; font-weight: 600; transition: 0.2s; cursor: pointer; }
        .nav-links a:hover, .nav-links a.active { color: #8b5cf6; }

        .dropdown-menu {
            display: none; position: absolute; top: 28px; left: 0; background: #11131c;
            border: 1px solid #1f2333; border-radius: 8px; min-width: 170px; padding: 8px 0;
            box-shadow: 0 10px 25px rgba(0,0,0,0.4); z-index: 50;
        }
        .nav-links > li:hover .dropdown-menu { display: block; }
        .dropdown-menu li { list-style: none; }
        .dropdown-menu a { display: block; padding: 8px 16px; font-size: 13px; color: #d1d5db; }
        .dropdown-menu a:hover { background: #181a26; color: #8b5cf6; }
        .caret { font-size: 10px; margin-left: 4px; opacity: 0.7; }

        .nav-right { display: flex; align-items: center; gap: 15px; }
        .user-tag { color: #a78bfa; font-weight: 600; font-size: 14px; }
        .btn-profile { background: #181a26; color: #fff; padding: 8px 18px; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 13px; border: 1px solid #2b2e42; transition: 0.2s; }
        .btn-profile:hover { border-color: #8b5cf6; }
        .btn-logout { background: #8b5cf6; color: #fff; padding: 8px 18px; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 13px; transition: 0.2s; }
        .btn-logout:hover { background: #7c3aed; }

        .hero { text-align: center; padding: 50px 20px 40px; max-width: 900px; margin: 0 auto; }
        .search-box { max-width: 580px; margin: 0 auto 30px auto; position: relative; }
        .search-input { width: 100%; padding: 14px 20px 14px 45px; border-radius: 10px; border: 1px solid #232738; background: #11131c; color: #fff; font-size: 14px; outline: none; }
        .search-input:focus { border-color: #8b5cf6; box-shadow: 0 0 15px rgba(139, 92, 246, 0.25); }
        .search-icon { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); font-size: 15px; opacity: 0.8; }
        .hero-title { font-size: 46px; font-weight: 800; line-height: 1.2; margin-bottom: 16px; }
        .hero-subtitle { font-size: 15px; color: #9ca3af; margin-bottom: 30px; line-height: 1.6; max-width: 700px; margin-left: auto; margin-right: auto; }
        .hero-btns { display: flex; gap: 15px; justify-content: center; margin-bottom: 40px; }
        .btn-primary { background: #8b5cf6; color: #fff; padding: 12px 26px; border-radius: 8px; font-weight: 700; font-size: 14px; text-decoration: none; border: none; cursor: pointer; }
        .btn-primary:hover { background: #7c3aed; }
        .btn-secondary { background: #161824; color: #fff; padding: 12px 26px; border-radius: 8px; font-weight: 700; font-size: 14px; text-decoration: none; border: 1px solid #282c40; }
        .btn-secondary:hover { border-color: #8b5cf6; }

        .main-container { max-width: 1100px; margin: 0 auto; padding: 0 20px 80px 20px; }
        .section-header { margin-bottom: 22px; }
        .section-title { font-size: 20px; font-weight: 700; display: flex; align-items: center; gap: 8px; }

        .materials-grid, .assets-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 18px; margin-bottom: 60px; }
        
        .material-card, .asset-card { background: #11131c; border-radius: 12px; padding: 22px; border: 1px solid #1f2333; transition: 0.3s; position: relative; }
        .material-card:hover, .asset-card:hover { transform: translateY(-3px); border-color: #8b5cf6; }
        .material-icon, .asset-icon { width: 44px; height: 44px; border-radius: 8px; background: rgba(139, 92, 246, 0.12); display: flex; align-items: center; justify-content: center; font-size: 22px; margin-bottom: 14px; }
        .material-title, .asset-title { font-weight: 700; font-size: 16px; margin-bottom: 8px; }
        .material-desc, .asset-desc { font-size: 12px; color: #9ca3af; line-height: 1.5; }
        .badge { position: absolute; top: 16px; right: 16px; padding: 3px 8px; border-radius: 4px; font-size: 10px; font-weight: 700; text-transform: uppercase; }
        .badge-free { background: rgba(16, 185, 129, 0.15); color: #10b981; }
        .badge-paid { background: rgba(245, 158, 11, 0.15); color: #f59e0b; }

        .editors-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 18px; }
        .editor-card { background: #11131c; border-radius: 10px; padding: 18px; border: 1px solid #1f2333; display: flex; justify-content: space-between; align-items: center; transition: 0.3s; }
        .editor-card:hover { border-color: #8b5cf6; transform: translateY(-3px); }
        .editor-info h4 { font-size: 15px; font-weight: 700; }
        .editor-info p { font-size: 12px; color: #9ca3af; margin-top: 2px; }
        .editor-meta { font-size: 12px; color: #a78bfa; margin-top: 4px; font-weight: 600; }
        .btn-hire, .btn-download { background: #8b5cf6; color: #fff; border: none; padding: 8px 16px; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-hire:hover, .btn-download:hover { background: #7c3aed; }
        .empty-msg { color: #6b7280; font-size: 13px; grid-column: 1 / -1; }
    </style>
</head>
<body>

    <header class="navbar">
        <a href="home.php" class="logo">Edit<span>Hub</span></a>

        <ul class="nav-links">
            <li><a href="home.php" class="active">Home</a></li>
            <li>
                <a>Services <span class="caret">▾</span></a>
                <ul class="dropdown-menu">
                    <li><a href="services.php">Book a Service</a></li>
                    <li><a href="#editors">Hire Editors</a></li>
                </ul>
            </li>
            <li>
                <a>Assets <span class="caret">▾</span></a>
                <ul class="dropdown-menu">
                    <li><a href="#assets">Free & Paid Assets</a></li>
                </ul>
            </li>
            <li><a href="#materials">Materials</a></li>
            <li><a href="profile.php">Profile</a></li>
        </ul>

        <div class="nav-right">
            <span class="user-tag">Hi, <?php echo htmlspecialchars($username); ?></span>
            <a href="profile.php" class="btn-profile">Profile</a>
            <a href="php/logout.php" class="btn-logout">Logout</a>
        </div>
    </header>

    <section class="hero">
        <form action="search.php" method="GET">
            <div class="search-box">
                <span class="search-icon">🔍</span>
                <input type="text" name="q" class="search-input" placeholder="Search 5,000+ PNGs, SFX, CC Presets or Editors...">
            </div>
        </form>
        <h1 class="hero-title">Best Edit Materials & Top Video<br>Editors in 2026</h1>
        <p class="hero-subtitle">Get high-quality PNGs, Sound Effects (SFX), Motion Presets, Color Grading CC packs, or hire expert freelance video editors for your next viral project.</p>
        <div class="hero-btns">
            <a href="#editors" class="btn-primary">Hire Video Editors</a>
            <a href="#assets" class="btn-secondary">Explore Assets</a>
        </div>
    </section>

    <div class="main-container">

        <!-- Assets Section (NEWLY ADDED) -->
        <section id="assets">
            <div class="section-header">
                <div class="section-title">📦 Community Assets</div>
            </div>
            <div class="assets-grid">
                <?php if ($assets && $assets->num_rows > 0): ?>
                    <?php while ($a = $assets->fetch_assoc()): ?>
                        <div class="asset-card">
                            <span class="badge <?php echo $a['type'] === 'paid' ? 'badge-paid' : 'badge-free'; ?>">
                                <?php echo $a['type'] === 'paid' ? '₹' . htmlspecialchars($a['price']) : 'FREE'; ?>
                            </span>
                            <div class="asset-icon">📦</div>
                            <div class="asset-title"><?php echo htmlspecialchars($a['title']); ?></div>
                            <div class="asset-desc" style="margin-bottom: 12px;">Uploaded by @<?php echo htmlspecialchars($a['username']); ?></div>
                            <a href="<?php echo htmlspecialchars($a['file_path']); ?>" class="btn-download" download>Download</a>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="empty-msg">No assets uploaded yet. Be the first — upload from your profile!</p>
                <?php endif; ?>
            </div>
        </section>

        <!-- Materials Section -->
        <section id="materials">
            <div class="section-header">
                <div class="section-title">🖼️ Editing Materials & Resources</div>
            </div>
            <div class="materials-grid">
                <?php if ($materials && $materials->num_rows > 0): ?>
                    <?php while ($m = $materials->fetch_assoc()): ?>
                        <div class="material-card">
                            <div class="material-icon"><?php echo $categoryIcons[$m['category']] ?? '📁'; ?></div>
                            <div class="material-title"><?php echo htmlspecialchars($m['title']); ?></div>
                            <div class="material-desc"><?php echo htmlspecialchars($m['category']); ?> material uploaded by a community creator.</div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="empty-msg">No materials uploaded yet. Be the first — upload from your profile!</p>
                <?php endif; ?>
            </div>
        </section>

        <!-- Hire Editors Section -->
        <section id="editors">
            <div class="section-header">
                <div class="section-title">🎬 Hire Freelance Video Editors</div>
            </div>
            <div class="editors-grid">
                <?php if ($editors && $editors->num_rows > 0): ?>
                    <?php while ($s = $editors->fetch_assoc()): ?>
                        <div class="editor-card">
                            <div class="editor-info">
                                <h4><?php echo htmlspecialchars($s['full_name'] ?: $s['username']); ?></h4>
                                <p><?php echo htmlspecialchars($s['title']); ?></p>
                                <div class="editor-meta"><?php echo $s['price'] ? '₹' . htmlspecialchars($s['price']) : 'Contact for price'; ?></div>
                            </div>
                            <button class="btn-hire">Hire</button>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="empty-msg">No editors listed yet. Add "My Personal Services" from your profile!</p>
                <?php endif; ?>
            </div>
        </section>

    </div>

</body>
</html>