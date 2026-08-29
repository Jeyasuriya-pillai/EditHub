<?php
session_start();

// Agar user pehle se logged in hai to seedha home.php (post-login homepage) pe bhej do
if (isset($_SESSION['user_id'])) {
    header("Location: home.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EditHub - Best Video Editing Assets & Editors</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Roboto, -apple-system, sans-serif; scroll-behavior: smooth; }
        body { background-color: #090a10; color: #ffffff; }

        /* Navbar */
        .navbar { display: flex; justify-content: space-between; align-items: center; padding: 16px 45px; background: rgba(9, 10, 16, 0.95); border-bottom: 1px solid #1a1c28; position: sticky; top: 0; z-index: 100; backdrop-filter: blur(8px); }
        .logo { font-size: 24px; font-weight: 800; color: #ffffff; text-decoration: none; letter-spacing: -0.5px; }
        .logo span { color: #8b5cf6; }

        .nav-links { display: flex; gap: 28px; list-style: none; align-items: center; }
        .nav-links a { color: #9ca3af; text-decoration: none; font-size: 14px; font-weight: 600; transition: 0.2s; }
        .nav-links a:hover { color: #8b5cf6; }

        .nav-right { display: flex; align-items: center; gap: 15px; }
        .btn-login { background: #181a26; color: #fff; padding: 8px 18px; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 13px; border: 1px solid #2b2e42; transition: 0.2s; }
        .btn-login:hover { border-color: #8b5cf6; }
        .btn-signup { background: #8b5cf6; color: #fff; padding: 8px 18px; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 13px; transition: 0.2s; }
        .btn-signup:hover { background: #7c3aed; }

        /* Hero */
        .hero { text-align: center; padding: 90px 20px 60px; max-width: 900px; margin: 0 auto; }
        .hero-title { font-size: 46px; font-weight: 800; line-height: 1.2; margin-bottom: 16px; color: #ffffff; }
        .hero-subtitle { font-size: 15px; color: #9ca3af; margin-bottom: 30px; line-height: 1.6; max-width: 700px; margin-left: auto; margin-right: auto; }
        .hero-btns { display: flex; gap: 15px; justify-content: center; margin-bottom: 20px; }
        .btn-primary { background: #8b5cf6; color: #fff; padding: 12px 26px; border-radius: 8px; font-weight: 700; font-size: 14px; text-decoration: none; border: none; cursor: pointer; transition: 0.2s; }
        .btn-primary:hover { background: #7c3aed; }
        .btn-secondary { background: #161824; color: #fff; padding: 12px 26px; border-radius: 8px; font-weight: 700; font-size: 14px; text-decoration: none; border: 1px solid #282c40; transition: 0.2s; }
        .btn-secondary:hover { border-color: #8b5cf6; }

        /* Feature strip */
        .main-container { max-width: 1100px; margin: 0 auto; padding: 0 20px 80px 20px; }
        .features-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 18px; }
        .feature-card { background: #11131c; border-radius: 12px; padding: 26px; border: 1px solid #1f2333; text-align: center; transition: 0.3s; }
        .feature-card:hover { transform: translateY(-3px); border-color: #8b5cf6; }
        .feature-icon { font-size: 30px; margin-bottom: 14px; }
        .feature-title { font-weight: 700; font-size: 16px; margin-bottom: 8px; color: #fff; }
        .feature-desc { font-size: 12px; color: #9ca3af; line-height: 1.5; }

        footer { text-align: center; padding: 24px; color: #6b7280; font-size: 13px; border-top: 1px solid #1a1c28; }
    </style>
</head>
<body>

    <!-- Navbar (Before Login) -->
    <header class="navbar">
        <a href="index.php" class="logo">Edit<span>Hub</span></a>

        <ul class="nav-links">
            <li><a href="#features">Why EditHub</a></li>
            <li><a href="services.php">Services</a></li>
            <li><a href="assets.php">Assets</a></li>
        </ul>

        <div class="nav-right">
            <a href="login.php" class="btn-login">Login</a>
            <a href="register.php" class="btn-signup">Sign Up</a>
        </div>
    </header>

    <!-- Hero -->
    <section class="hero">
        <h1 class="hero-title">Best Edit Materials & Top Video<br>Editors in 2026</h1>
        <p class="hero-subtitle">Get high-quality PNGs, Sound Effects (SFX), Motion Presets, Color Grading CC packs, or hire expert freelance video editors for your next viral project.</p>
        <div class="hero-btns">
            <a href="register.php" class="btn-primary">Get Started Free</a>
            <a href="login.php" class="btn-secondary">Login</a>
        </div>
    </section>

    <!-- Why EditHub -->
    <div class="main-container" id="features">
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">🖼️</div>
                <div class="feature-title">Editing Materials</div>
                <div class="feature-desc">HD PNGs, SFX, CC presets, VFX overlays — everything for your edits.</div>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📦</div>
                <div class="feature-title">Free & Paid Assets</div>
                <div class="feature-desc">Download free resources or unlock premium packs after login.</div>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🎬</div>
                <div class="feature-title">Hire Editors</div>
                <div class="feature-desc">Connect with skilled freelance video editors for your projects.</div>
            </div>
        </div>
    </div>

    <footer>&copy; <?php echo date("Y"); ?> EditHub. All rights reserved.</footer>

</body>
</html>