<?php
session_start();
$is_logged_in = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EditHub - Premium Assets & Video Editors</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Roboto, Helvetica, sans-serif; scroll-behavior: smooth; }
        body { background-color: #0b0d14; color: #ffffff; overflow-x: hidden; }

        /* Keyframe Animations */
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes pulseGlow {
            0%, 100% { box-shadow: 0 0 15px rgba(139, 92, 246, 0.3); }
            50% { box-shadow: 0 0 30px rgba(139, 92, 246, 0.7); }
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-8px); }
        }

        /* Navbar */
        .navbar { display: flex; justify-content: space-between; align-items: center; padding: 18px 40px; background: rgba(15, 17, 26, 0.85); backdrop-filter: blur(12px); border-bottom: 1px solid #1f2430; position: sticky; top: 0; z-index: 100; animation: fadeInDown 0.8s ease-out; }
        .logo { font-size: 24px; font-weight: 800; color: #ffffff; letter-spacing: 1px; text-decoration: none; }
        .logo span { color: #8b5cf6; }
        .nav-links { display: flex; gap: 25px; list-style: none; }
        .nav-links a { color: #9ca3af; text-decoration: none; font-size: 14px; font-weight: 600; transition: color 0.3s; }
        .nav-links a:hover { color: #8b5cf6; }
        .nav-btns { display: flex; gap: 12px; align-items: center; }
        .btn { padding: 9px 22px; border-radius: 8px; font-weight: 600; text-decoration: none; cursor: pointer; font-size: 14px; border: none; transition: all 0.3s ease; }
        .btn-login { background: #8b5cf6; color: #fff; box-shadow: 0 4px 14px rgba(139, 92, 246, 0.4); }
        .btn-login:hover { background: #7c3aed; transform: translateY(-2px); box-shadow: 0 6px 20px rgba(139, 92, 246, 0.6); }
        .btn-signup { background: #1f2937; color: #fff; border: 1px solid #374151; }
        .btn-signup:hover { background: #374151; transform: translateY(-2px); }
        .user-tag { color: #a78bfa; font-weight: 600; font-size: 15px; margin-right: 10px; }

        /* Hero Section */
        .hero { text-align: center; padding: 90px 20px 60px; max-width: 900px; margin: 0 auto; animation: fadeInUp 0.9s ease-out; }
        .hero h1 { font-size: 48px; font-weight: 800; letter-spacing: -0.5px; line-height: 1.2; margin-bottom: 20px; background: linear-gradient(135deg, #ffffff 30%, #a78bfa); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .hero p { font-size: 17px; color: #9ca3af; margin-bottom: 35px; line-height: 1.6; }
        .hero-btns { display: flex; gap: 15px; justify-content: center; margin-bottom: 40px; }
        .btn-hero-primary { background: #8b5cf6; color: #fff; padding: 14px 30px; border-radius: 8px; font-weight: 700; text-decoration: none; transition: 0.3s; animation: pulseGlow 3s infinite; }
        .btn-hero-primary:hover { transform: translateY(-3px); }
        .btn-hero-secondary { background: #161922; color: #fff; padding: 14px 30px; border-radius: 8px; font-weight: 600; text-decoration: none; border: 1px solid #232836; transition: 0.3s; }
        .btn-hero-secondary:hover { border-color: #8b5cf6; background: #1f2430; transform: translateY(-3px); }

        /* Search Bar */
        .search-container { max-width: 600px; margin: 0 auto 50px auto; position: relative; }
        .search-input { width: 100%; padding: 16px 20px 16px 48px; border-radius: 12px; border: 1px solid #232836; background: #161922; color: #fff; font-size: 15px; outline: none; transition: 0.3s; }
        .search-input:focus { border-color: #8b5cf6; box-shadow: 0 0 20px rgba(139, 92, 246, 0.3); }
        .search-icon { position: absolute; left: 18px; top: 50%; transform: translateY(-50%); color: #6b7280; }

        /* Main Layout */
        .main-container { max-width: 1200px; margin: 0 auto; padding: 0 30px 80px 30px; }
        .section-title { font-size: 24px; font-weight: 700; margin-bottom: 25px; color: #f3f4f6; display: flex; align-items: center; gap: 10px; }
        .section-title span { color: #8b5cf6; }

        /* Asset Cards Grid */
        .assets-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 22px; margin-bottom: 70px; }
        .asset-card { background: #161922; border-radius: 14px; padding: 25px; border: 1px solid #232836; transition: all 0.3s ease; cursor: pointer; position: relative; overflow: hidden; }
        .asset-card:hover { transform: translateY(-8px); border-color: #8b5cf6; box-shadow: 0 10px 30px rgba(139, 92, 246, 0.25); }
        .asset-icon { width: 50px; height: 50px; border-radius: 10px; background: rgba(139, 92, 246, 0.15); display: flex; align-items: center; justify-content: center; font-size: 24px; margin-bottom: 18px; color: #a78bfa; }
        .asset-title { font-weight: 700; font-size: 18px; margin-bottom: 8px; color: #ffffff; }
        .asset-desc { font-size: 13px; color: #9ca3af; line-height: 1.5; }

        /* Editors Grid */
        .editors-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 22px; margin-bottom: 70px; }
        .editor-card { background: #161922; border-radius: 14px; padding: 22px; border: 1px solid #232836; display: flex; justify-content: space-between; align-items: center; transition: all 0.3s ease; }
        .editor-card:hover { border-color: #8b5cf6; transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0,0,0,0.5); }
        .editor-profile { display: flex; gap: 15px; align-items: center; }
        .editor-avatar { width: 55px; height: 55px; border-radius: 50%; border: 2px solid #8b5cf6; object-fit: cover; }
        .editor-info h4 { font-size: 16px; font-weight: 700; color: #fff; }
        .editor-info p { font-size: 13px; color: #9ca3af; margin-top: 3px; }
        .editor-meta { display: flex; gap: 8px; font-size: 12px; color: #a78bfa; margin-top: 6px; font-weight: 600; }
        .btn-hire { background: #8b5cf6; color: #fff; border: none; padding: 8px 18px; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; transition: 0.3s; }
        .btn-hire:hover { background: #7c3aed; transform: scale(1.05); }

        /* About Us Section */
        .about-section { background: linear-gradient(135deg, #161922, #0f111a); border: 1px solid #232836; border-radius: 16px; padding: 40px; margin-bottom: 60px; position: relative; overflow: hidden; }
        .about-section::before { content: ''; position: absolute; top: -50%; left: -50%; width: 200%; height: 200%; background: radial-gradient(circle, rgba(139,92,246,0.08) 0%, transparent 60%); pointer-events: none; }
        .about-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; align-items: center; }
        .about-text h3 { font-size: 28px; margin-bottom: 15px; color: #fff; }
        .about-text p { color: #9ca3af; font-size: 14px; line-height: 1.7; margin-bottom: 15px; }
        .about-stats { display: flex; gap: 20px; margin-top: 20px; }
        .stat-box { background: rgba(31, 36, 48, 0.6); padding: 15px 20px; border-radius: 10px; border: 1px solid #232836; text-align: center; }
        .stat-box h4 { font-size: 22px; color: #8b5cf6; }
        .stat-box p { font-size: 12px; color: #9ca3af; margin-top: 4px; }

        /* Lock Notice Banner */
        .guest-banner { background: rgba(139, 92, 246, 0.1); border: 1px solid #8b5cf6; padding: 15px 20px; border-radius: 10px; margin-bottom: 35px; display: flex; justify-content: space-between; align-items: center; animation: float 4s ease-in-out infinite; }
        .guest-banner p { color: #ddd6fe; font-size: 14px; }
    </style>
</head>
<body>

    <!-- Header Navbar -->
    <header class="navbar">
        <a href="index.php" class="logo">EDIT<span>HUB</span></a>

        <ul class="nav-links">
            <li><a href="#assets">Materials</a></li>
            <li><a href="#editors">Hire Editors</a></li>
            <li><a href="#about">About Us</a></li>
        </ul>

        <div class="nav-btns">
            <?php if ($is_logged_in): ?>
                <span class="user-tag">Hi, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="profile.php" class="btn btn-signup">Profile</a>
                <a href="php/logout.php" class="btn btn-login">Logout</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-login">Login</a>
                <a href="login.php?action=signup" class="btn btn-signup">Sign Up</a>
            <?php endif; ?>
        </div>
    </header>

    <!-- Hero Banner -->
    <section class="hero">
        <h1>Best Edit Materials & Top Video Editors in 2026</h1>
        <p>Get high-quality PNGs, Sound Effects (SFX), Motion Presets, Color Grading CC packs, or hire expert freelance video editors for your next viral project.</p>

        <div class="hero-btns">
            <a href="#editors" class="btn-hero-primary">Hire Video Editors</a>
            <a href="#assets" class="btn-hero-secondary">Explore Asset Packs</a>
        </div>

        <div class="search-container">
            <span class="search-icon">🔍</span>
            <input type="text" class="search-input" placeholder="Search 5,000+ PNGs, SFX, CC Presets or Editors..." onfocus="checkAuth()">
        </div>
    </section>

    <!-- Main Content Body -->
    <div class="main-container">

        <?php if (!$is_logged_in): ?>
        <div class="guest-banner">
            <p>🔒 <b>Preview Mode:</b> Log in to download full 4K assets & hire video editors directly.</p>
            <a href="login.php" class="btn btn-login" style="padding: 6px 14px; font-size: 12px;">Login Now</a>
        </div>
        <?php endif; ?>

        <!-- Category 1: Edit Materials -->
        <section id="assets">
            <div class="section-title"><span>🖼️</span> Editing Materials & Resources</div>

            <div class="assets-grid">
                <div class="asset-card" onclick="checkAuth()">
                    <div class="asset-icon">🖼️</div>
                    <div class="asset-title">HD PNG Cutouts</div>
                    <div class="asset-desc">Transparent 4K elements, text callouts & overlays for video thumbnails & edits.</div>
                </div>

                <div class="asset-card" onclick="checkAuth()">
                    <div class="asset-icon">🔊</div>
                    <div class="asset-title">SFX & Sound Packs</div>
                    <div class="asset-desc">Swooshes, cinematic whooshes, risers, pop sounds & glitch sound effects.</div>
                </div>

                <div class="asset-card" onclick="checkAuth()">
                    <div class="asset-icon">🎨</div>
                    <div class="asset-title">CC & Color Grading</div>
                    <div class="asset-desc">Lightroom presets, LUTs, After Effects MOGRTs & Cinematic CC presets.</div>
                </div>

                <div class="asset-card" onclick="checkAuth()">
                    <div class="asset-icon">✨</div>
                    <div class="asset-title">VFX & Video Effects</div>
                    <div class="asset-desc">Light leaks, film grain, CRT overlays, particle effects & transition templates.</div>
                </div>
            </div>
        </section>

        <!-- Category 2: Freelance Editors -->
        <section id="editors">
            <div class="section-title"><span>🎬</span> Top Freelance Video Editors</div>

            <div class="editors-grid">
                <div class="editor-card">
                    <div class="editor-profile">
                        <img src="https://i.pravatar.cc/100?img=12" class="editor-avatar" alt="Editor">
                        <div class="editor-info">
                            <h4>Gourav Sharma</h4>
                            <p>Reels & YouTube Specialist</p>
                            <div class="editor-meta">
                                <span>⭐ 5.0 (42)</span> • <span>₹1.5k+/hr</span>
                            </div>
                        </div>
                    </div>
                    <button class="btn-hire" onclick="checkAuth()">Hire</button>
                </div>

                <div class="editor-card">
                    <div class="editor-profile">
                        <img src="https://i.pravatar.cc/100?img=33" class="editor-avatar" alt="Editor">
                        <div class="editor-info">
                            <h4>Aarav Patel</h4>
                            <p>3D & VFX Motion Designer</p>
                            <div class="editor-meta">
                                <span>⭐ 4.9 (58)</span> • <span>₹2.5k+/hr</span>
                            </div>
                        </div>
                    </div>
                    <button class="btn-hire" onclick="checkAuth()">Hire</button>
                </div>

                <div class="editor-card">
                    <div class="editor-profile">
                        <img src="https://i.pravatar.cc/100?img=47" class="editor-avatar" alt="Editor">
                        <div class="editor-info">
                            <h4>Sneha Rao</h4>
                            <p>Cinematic & Commercial Ads</p>
                            <div class="editor-meta">
                                <span>⭐ 5.0 (31)</span> • <span>₹2k+/hr</span>
                            </div>
                        </div>
                    </div>
                    <button class="btn-hire" onclick="checkAuth()">Hire</button>
                </div>
            </div>
        </section>

        <!-- Category 3: About Us -->
        <section id="about" class="about-section">
            <div class="about-grid">
                <div class="about-text">
                    <h3>About EditHub</h3>
                    <p>EditHub is a one-stop ecosystem designed specifically for video creators, editors, and motion graphic artists. We provide high-quality editing assets including PNGs, Sound Effects, Color Grading LUTs, and VFX elements.</p>
                    <p>Whether you need professional materials to enhance your timeline or want to hire talented freelance editors to bring your creative vision to life, EditHub connects you with everything you need in one place.</p>
                    <div class="about-stats">
                        <div class="stat-box">
                            <h4>50K+</h4>
                            <p>Active Creators</p>
                        </div>
                        <div class="stat-box">
                            <h4>10K+</h4>
                            <p>Assets Available</p>
                        </div>
                        <div class="stat-box">
                            <h4>500+</h4>
                            <p>Verified Editors</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </div>

    <script>
        function checkAuth() {
            var isLoggedIn = <?php echo json_encode($is_logged_in); ?>;
            if (!isLoggedIn) {
                alert('Please login or sign up to access EditHub assets and hire video editors!');
                window.location.href = 'login.php';
            } else {
                window.location.href = 'assets.php';
            }
        }
    </script>
</body>
</html>