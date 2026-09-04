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

// Dynamic hire-me services with thumbnail-less rating info
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
        .section-subtitle { font-size: 13px; color: #9ca3af; margin-top: 4px; }

        .grid-4 { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 18px; margin-bottom: 60px; }
        .thumb-card { background: #11131c; border-radius: 12px; border: 1px solid #1f2333; overflow: hidden; transition: 0.3s; position: relative; }
        .thumb-card:hover { transform: translateY(-3px); border-color: #8b5cf6; }
        .thumb-img { width: 100%; height: 130px; object-fit: cover; display: block; background: #181a26; }
        .thumb-fallback { width: 100%; height: 130px; display: flex; align-items: center; justify-content: center; font-size: 34px; background: rgba(139, 92, 246, 0.08); }
        .thumb-body { padding: 16px; }
        .thumb-title { font-weight: 700; font-size: 15px; margin-bottom: 6px; }
        .thumb-desc { font-size: 12px; color: #9ca3af; line-height: 1.4; }
        .price-badge { position: absolute; top: 10px; right: 10px; background: rgba(0,0,0,0.7); padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: 700; }
        .price-free { color: #10b981; }
        .price-paid { color: #f59e0b; }
        .stars-row { color: #f59e0b; font-size: 12px; margin-top: 6px; }
        .stars-row span { color: #6b7280; margin-left: 4px; }
        .btn-get { display: block; text-align: center; background: #8b5cf6; color: #fff; padding: 8px; border-radius: 6px; font-size: 12px; font-weight: 700; text-decoration: none; margin-top: 10px; }
        .btn-get:hover { background: #7c3aed; }

        .editors-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 18px; }
        .editor-card { background: #11131c; border-radius: 10px; padding: 18px; border: 1px solid #1f2333; display: flex; justify-content: space-between; align-items: center; transition: 0.3s; }
        .editor-card:hover { border-color: #8b5cf6; transform: translateY(-3px); }
        .editor-profile-link { text-decoration:none; color:inherit; }
        .editor-info h4 { font-size: 15px; font-weight: 700; }
        .editor-info h4:hover { color: #a78bfa; }
        .editor-info p { font-size: 12px; color: #9ca3af; margin-top: 2px; }
        .editor-meta { font-size: 12px; color: #a78bfa; margin-top: 4px; font-weight: 600; }
        .btn-hire { background: #8b5cf6; color: #fff; border: none; padding: 8px 16px; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; text-decoration:none; display:inline-block; }
        .btn-hire:hover { background: #7c3aed; }
        .empty-msg { color: #6b7280; font-size: 13px; }
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
                    <li><a href="assets.php#free">Free Assets</a></li>
                    <li><a href="assets.php#paid">Paid Assets</a></li>
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
            <a href="#materials" class="btn-secondary">Explore Asset Packs</a>
        </div>
    </section>

    <div class="main-container">

        <!-- Community Assets (dynamic, with thumbnails + ratings) -->
        <section id="assets-preview">
            <div class="section-header">
                <div class="section-title">📦 Community Assets</div>
            </div>
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
                                <div class="stars-row"><?php echo starDisplay($a['avg_rating']); ?> <span>(<?php echo $a['review_count']; ?>)</span></div>
                                <a href="review.php?type=asset&id=<?php echo $a['id']; ?>" class="btn-get">View & Review</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="empty-msg">No assets uploaded yet.</p>
                <?php endif; ?>
            </div>
        </section>

        <!-- Materials Section (dynamic, with thumbnails) -->
        <section id="materials">
            <div class="section-header">
                <div class="section-title">🖼️ Editing Materials & Resources</div>
            </div>
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
                                <div class="thumb-desc"><?php echo htmlspecialchars($m['category']); ?> material uploaded by a community creator.</div>
                                <div class="stars-row"><?php echo starDisplay($m['avg_rating']); ?> <span>(<?php echo $m['review_count']; ?>)</span></div>
                                <a href="review.php?type=material&id=<?php echo $m['id']; ?>" class="btn-get">View & Review</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="empty-msg">No materials uploaded yet. Be the first — upload from your profile!</p>
                <?php endif; ?>
            </div>
        </section>

        <!-- Hire Editors Section (dynamic, links to their profile) -->
        <section id="editors">
            <div class="section-header">
                <div class="section-title">🎬 Hire Freelance Video Editors</div>
            </div>
            <div class="editors-grid">
                <?php if ($editors && $editors->num_rows > 0): ?>
                    <?php while ($s = $editors->fetch_assoc()): ?>
                        <div class="editor-card">
                            <a href="view_profile.php?user_id=<?php echo $s['owner_id']; ?>" class="editor-profile-link">
                                <div class="editor-info">
                                    <h4><?php echo htmlspecialchars($s['full_name'] ?: $s['username']); ?></h4>
                                    <p><?php echo htmlspecialchars($s['title']); ?></p>
                                    <div class="editor-meta"><?php echo $s['price'] ? '₹' . htmlspecialchars($s['price']) : 'Contact for price'; ?></div>
                                    <div class="stars-row"><?php echo starDisplay($s['avg_rating']); ?> <span>(<?php echo $s['review_count']; ?>)</span></div>
                                </div>
                            </a>
                            <a href="view_profile.php?user_id=<?php echo $s['owner_id']; ?>" class="btn-hire">View Profile</a>
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