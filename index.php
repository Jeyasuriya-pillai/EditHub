<?php
session_start();
$is_logged_in = isset($_SESSION['user_id']);
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'jirox_fx';
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

        /* Navbar */
        .navbar { display: flex; justify-content: space-between; align-items: center; padding: 16px 45px; background: rgba(9, 10, 16, 0.95); border-bottom: 1px solid #1a1c28; position: sticky; top: 0; z-index: 100; backdrop-filter: blur(8px); }
        .logo { font-size: 24px; font-weight: 800; color: #ffffff; text-decoration: none; letter-spacing: -0.5px; }
        .logo span { color: #8b5cf6; }
        
        .nav-links { display: flex; gap: 28px; list-style: none; align-items: center; }
        .nav-links a { color: #9ca3af; text-decoration: none; font-size: 14px; font-weight: 600; transition: 0.2s; }
        .nav-links a:hover, .nav-links a.active { color: #8b5cf6; }

        .nav-right { display: flex; align-items: center; gap: 15px; }
        .user-tag { color: #a78bfa; font-weight: 600; font-size: 14px; }
        .btn-profile { background: #181a26; color: #fff; padding: 8px 18px; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 13px; border: 1px solid #2b2e42; transition: 0.2s; }
        .btn-profile:hover { border-color: #8b5cf6; }
        .btn-logout { background: #8b5cf6; color: #fff; padding: 8px 18px; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 13px; transition: 0.2s; }
        .btn-logout:hover { background: #7c3aed; }

        /* Hero Section */
        .hero { text-align: center; padding: 50px 20px 40px; max-width: 900px; margin: 0 auto; }

        /* SEARCH BAR TOP POSITIONED */
        .search-box { max-width: 580px; margin: 0 auto 30px auto; position: relative; }
        .search-input { width: 100%; padding: 14px 20px 14px 45px; border-radius: 10px; border: 1px solid #232738; background: #11131c; color: #fff; font-size: 14px; outline: none; transition: 0.3s; }
        .search-input:focus { border-color: #8b5cf6; box-shadow: 0 0 15px rgba(139, 92, 246, 0.25); }
        .search-icon { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); font-size: 15px; opacity: 0.8; }

        .hero-title { font-size: 46px; font-weight: 800; line-height: 1.2; margin-bottom: 16px; color: #ffffff; }
        .hero-subtitle { font-size: 15px; color: #9ca3af; margin-bottom: 30px; line-height: 1.6; max-width: 700px; margin-left: auto; margin-right: auto; }

        /* Hero Action Buttons */
        .hero-btns { display: flex; gap: 15px; justify-content: center; margin-bottom: 40px; }
        .btn-primary { background: #8b5cf6; color: #fff; padding: 12px 26px; border-radius: 8px; font-weight: 700; font-size: 14px; text-decoration: none; border: none; cursor: pointer; transition: 0.2s; }
        .btn-primary:hover { background: #7c3aed; }
        .btn-secondary { background: #161824; color: #fff; padding: 12px 26px; border-radius: 8px; font-weight: 700; font-size: 14px; text-decoration: none; border: 1px solid #282c40; transition: 0.2s; }
        .btn-secondary:hover { border-color: #8b5cf6; }

        /* Main Container */
        .main-container { max-width: 1100px; margin: 0 auto; padding: 0 20px 80px 20px; }
        .section-header { margin-bottom: 22px; }
        .section-title { font-size: 20px; font-weight: 700; color: #ffffff; display: flex; align-items: center; gap: 8px; }
        .section-subtitle { font-size: 13px; color: #9ca3af; margin-top: 4px; }

        /* Editing Materials Grid (From First Image) */
        .materials-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 18px; margin-bottom: 60px; }
        .material-card { background: #11131c; border-radius: 12px; padding: 22px; border: 1px solid #1f2333; transition: 0.3s; }
        .material-card:hover { transform: translateY(-3px); border-color: #8b5cf6; }
        .material-icon { width: 44px; height: 44px; border-radius: 8px; background: rgba(139, 92, 246, 0.12); display: flex; align-items: center; justify-content: center; font-size: 22px; margin-bottom: 14px; }
        .material-title { font-weight: 700; font-size: 16px; margin-bottom: 8px; color: #fff; }
        .material-desc { font-size: 12px; color: #9ca3af; line-height: 1.5; }

        /* Free Editing Resources Downloads (Merged From Second Image) */
        .resources-section { margin-bottom: 60px; }
        .downloads-list { display: flex; flex-direction: column; gap: 14px; }
        .download-card { background: #11131c; border: 1px solid #1f2333; border-radius: 10px; padding: 18px 24px; display: flex; justify-content: space-between; align-items: center; transition: 0.3s; }
        .download-card:hover { border-color: #8b5cf6; }
        .download-info h4 { font-size: 15px; font-weight: 700; color: #ffffff; margin-bottom: 4px; }
        .download-info p { font-size: 12px; color: #9ca3af; }
        .btn-download { background: #10b981; color: #ffffff; border: none; padding: 10px 20px; border-radius: 6px; font-weight: 700; font-size: 13px; cursor: pointer; text-decoration: none; transition: 0.2s; }
        .btn-download:hover { background: #059669; }

        /* Editors Section */
        .editors-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 18px; }
        .editor-card { background: #11131c; border-radius: 10px; padding: 18px; border: 1px solid #1f2333; display: flex; justify-content: space-between; align-items: center; transition: 0.3s; }
        .editor-card:hover { border-color: #8b5cf6; transform: translateY(-3px); }
        .editor-profile { display: flex; gap: 12px; align-items: center; }
        .editor-avatar { width: 46px; height: 46px; border-radius: 50%; border: 2px solid #8b5cf6; object-fit: cover; }
        .editor-info h4 { font-size: 15px; font-weight: 700; color: #fff; }
        .editor-info p { font-size: 12px; color: #9ca3af; margin-top: 2px; }
        .editor-meta { font-size: 12px; color: #a78bfa; margin-top: 4px; font-weight: 600; }
        .btn-hire { background: #8b5cf6; color: #fff; border: none; padding: 8px 16px; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; transition: 0.2s; }
        .btn-hire:hover { background: #7c3aed; }
    </style>
</head>
<body>

    <!-- Header Navbar -->
    <header class="navbar">
        <a href="index.php" class="logo">Edit<span>Hub</span></a>
        
        <ul class="nav-links">
            <li><a href="index.php" class="active">Home</a></li>
            <li><a href="#materials">Materials</a></li>
            <li><a href="#resources">Free Assets</a></li>
            <li><a href="#editors">Hire Editors</a></li>
            <?php if ($is_logged_in): ?>
                <li><a href="profile.php">Profile</a></li>
            <?php endif; ?>
        </ul>

        <div class="nav-right">
            <?php if ($is_logged_in): ?>
                <span class="user-tag">Hi, <?php echo htmlspecialchars($username); ?></span>
                <a href="profile.php" class="btn-profile">Profile</a>
                <a href="php/logout.php" class="btn-logout">Logout</a>
            <?php else: ?>
                <a href="login.php" class="btn-logout">Login</a>
            <?php endif; ?>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <!-- Search bar strictly placed at top -->
        <div class="search-box">
            <span class="search-icon">🔍</span>
            <input type="text" class="search-input" placeholder="Search 5,000+ PNGs, SFX, CC Presets or Editors...">
        </div>

        <h1 class="hero-title">Best Edit Materials & Top Video<br>Editors in 2026</h1>
        <p class="hero-subtitle">Get high-quality PNGs, Sound Effects (SFX), Motion Presets, Color Grading CC packs, or hire expert freelance video editors for your next viral project.</p>

        <div class="hero-btns">
            <a href="#editors" class="btn-primary">Hire Video Editors</a>
            <a href="#materials" class="btn-secondary">Explore Asset Packs</a>
        </div>
    </section>

    <!-- Main Content Area -->
    <div class="main-container">

        <!-- 1. Editing Materials & Resources Section -->
        <section id="materials">
            <div class="section-header">
                <div class="section-title">🖼️ Editing Materials & Resources</div>
            </div>
            <div class="materials-grid">
                <div class="material-card">
                    <div class="material-icon">🖼️</div>
                    <div class="material-title">HD PNG Cutouts</div>
                    <div class="material-desc">Transparent 4K elements, text callouts & overlays for video thumbnails & edits.</div>
                </div>
                <div class="material-card">
                    <div class="material-icon">🔊</div>
                    <div class="material-title">SFX & Sound Packs</div>
                    <div class="material-desc">Swooshes, cinematic whooshes, risers, pop sounds & glitch sound effects.</div>
                </div>
                <div class="material-card">
                    <div class="material-icon">🎨</div>
                    <div class="material-title">CC & Color Grading</div>
                    <div class="material-desc">Lightroom presets, LUTs, After Effects MOGRTs & Cinematic CC presets.</div>
                </div>
                <div class="material-card">
                    <div class="material-icon">✨</div>
                    <div class="material-title">VFX & Video Effects</div>
                    <div class="material-desc">Light leaks, film grain, CRT overlays, particle effects & transition templates.</div>
                </div>
            </div>
        </section>

        <!-- 2. Free Editing Resources Section (Merged from Second Image) -->
        <section id="resources" class="resources-section">
            <div class="section-header">
                <div class="section-title">📦 Free Editing Resources</div>
                <div class="section-subtitle">Download free LUTs, sound packs, and overlay effects directly.</div>
            </div>
            <div class="downloads-list">
                <div class="download-card">
                    <div class="download-info">
                        <h4>Cinematic LUTs Pack (Premiere / CapCut)</h4>
                        <p>Size: 45 MB • Format: .cube</p>
                    </div>
                    <a href="#" class="btn-download">Download Asset</a>
                </div>
                <div class="download-card">
                    <div class="download-info">
                        <h4>100+ Whoosh & Transition Sound Effects</h4>
                        <p>Size: 120 MB • Format: .wav</p>
                    </div>
                    <a href="#" class="btn-download">Download Asset</a>
                </div>
            </div>
        </section>

        <!-- 3. Hire Freelance Video Editors Section -->
        <section id="editors">
            <div class="section-header">
                <div class="section-title">🎬 Hire Freelance Video Editors</div>
            </div>
            <div class="editors-grid">
                <div class="editor-card">
                    <div class="editor-profile">
                        <img src="https://i.pravatar.cc/100?img=12" class="editor-avatar" alt="Editor">
                        <div class="editor-info">
                            <h4>Gourav Sharma</h4>
                            <p>Reels & YouTube Specialist</p>
                            <div class="editor-meta">⭐ 5.0 • ₹1,500/hr</div>
                        </div>
                    </div>
                    <button class="btn-hire">Hire</button>
                </div>
                <div class="editor-card">
                    <div class="editor-profile">
                        <img src="https://i.pravatar.cc/100?img=33" class="editor-avatar" alt="Editor">
                        <div class="editor-info">
                            <h4>Aarav Patel</h4>
                            <p>3D Motion & VFX Designer</p>
                            <div class="editor-meta">⭐ 4.9 • ₹2,500/hr</div>
                        </div>
                    </div>
                    <button class="btn-hire">Hire</button>
                </div>
            </div>
        </section>

    </div>

</body>
</html>