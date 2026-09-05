<?php
session_start();
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
<title>EditHub - The Editor's Toolkit</title>
<link rel="stylesheet" href="css/style.css">
<style>
    .hero { padding: 90px 40px 60px; max-width: 980px; }
    .hero .eyebrow { color: var(--accent); font-family: var(--font-display); font-weight: 800; font-size: 13px; letter-spacing: 0.08em; margin-bottom: 18px; }
    .hero h1 { font-size: 58px; max-width: 780px; margin-bottom: 22px; }
    .hero p { color: var(--muted); font-size: 16px; max-width: 560px; line-height: 1.7; margin-bottom: 32px; }
    .hero .btn-row { display: flex; gap: 12px; }

    .stat-strip { display: flex; border-top: 1px solid var(--line); border-bottom: 1px solid var(--line); }
    .stat-strip > div { flex: 1; padding: 26px 40px; border-right: 1px solid var(--line); }
    .stat-strip > div:last-child { border-right: none; }
    .stat-strip .num { font-family: var(--font-display); font-size: 30px; font-weight: 900; color: var(--accent); }
    .stat-strip .label { color: var(--muted); font-size: 12px; text-transform: uppercase; letter-spacing: 0.04em; margin-top: 4px; }

    .feature-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0; }
    .feature-grid > .card { border-radius: 0; border-top: none; border-left: none; }
    .feature-grid > .card:last-child { border-right: none; }
    .feature-grid .card-pad { padding: 34px 30px; }
    .feature-grid h3 { font-size: 18px; margin-bottom: 10px; }
    .feature-grid p { color: var(--muted); font-size: 13px; line-height: 1.6; }
    .feature-num { font-family: var(--font-display); color: var(--muted-2); font-size: 13px; margin-bottom: 16px; }

    @media (max-width: 860px) {
        .hero h1 { font-size: 38px; }
        .stat-strip { flex-direction: column; }
        .stat-strip > div { border-right: none; border-bottom: 1px solid var(--line); }
        .feature-grid { grid-template-columns: 1fr; }
    }
</style>
</head>
<body>

<?php require_once 'php/navbar.php'; ?>

<section class="hero">
    <div class="eyebrow">FOR EDITORS, BY EDITORS</div>
    <h1>Everything a video editor needs, in one bin.</h1>
    <p>Hire freelance editors, pull free & paid assets, and grab editing materials — LUTs, SFX, VFX, presets — uploaded by a working community of creators.</p>
    <div class="btn-row">
        <a href="login.php?action=signup" class="btn btn-primary">Get started free</a>
        <a href="login.php" class="btn btn-outline">Sign in</a>
    </div>
</section>

<div class="ruler"></div>

<div class="stat-strip">
    <div><div class="num">5K+</div><div class="label">Assets & Materials</div></div>
    <div><div class="num">Free</div><div class="label">& Paid Packs</div></div>
    <div><div class="num">24/7</div><div class="label">Editor Marketplace</div></div>
</div>

<div class="feature-grid">
    <div class="card">
        <div class="card-pad">
            <div class="feature-num">01</div>
            <h3>Editing Materials</h3>
            <p>HD PNGs, SFX, CC & color grading presets, VFX overlays — uploaded and shared by the community.</p>
        </div>
    </div>
    <div class="card">
        <div class="card-pad">
            <div class="feature-num">02</div>
            <h3>Free & Paid Assets</h3>
            <p>Download free resources or unlock premium packs once you're signed in.</p>
        </div>
    </div>
    <div class="card">
        <div class="card-pad">
            <div class="feature-num">03</div>
            <h3>Hire Editors</h3>
            <p>Browse freelance video editors, check ratings from real clients, and book directly.</p>
        </div>
    </div>
</div>

<footer class="eh-footer">&copy; <?php echo date("Y"); ?> EditHub. All rights reserved.</footer>

</body>
</html>