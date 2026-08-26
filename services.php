<?php
session_start();
require_once 'php/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Services - EditHub</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .container { max-width: 1000px; margin: 40px auto; padding: 0 20px; }
        .services-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; }
        .card { background: #0b1428; border: 1px solid #1b2a48; border-radius: 10px; padding: 20px; text-align: center; }
        .card h3 { color: #3b82f6; margin-bottom: 10px; }
        .card p { color: #94a3b8; font-size: 14px; margin-bottom: 15px; }
        .price { font-size: 20px; font-weight: bold; color: #fff; margin-bottom: 15px; }
        .btn-order { background: #3b82f6; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; text-decoration: none; display: inline-block; }
    </style>
</head>
<body>

    <header class="navbar">
        <div class="logo">Edit<span>Hub</span></div>
<nav>
    <a href="index.php">Home</a>
    <a href="services.php">Services</a>
    <a href="assets.php">Assets</a>
    <?php if (isset($_SESSION['username'])): ?>
        <a href="profile.php">Hi, <?php echo htmlspecialchars($_SESSION['username']); ?></a>
        <a href="php/logout.php">Logout</a>
    <?php else: ?>
        <a href="login.html">Login</a>
    <?php endif; ?>
</nav>
    </header>

    <div class="container">
        <h2 style="text-align: center; margin-bottom: 30px;">Our Editing Services</h2>
        <div class="services-grid">
            <div class="card">
                <h3>Shorts & Reels Editing</h3>
                <p>Engaging captions, fast cuts, trending audio, and color grading for Instagram & YouTube Shorts.</p>
                <div class="price">₹499 / Video</div>
                <a href="<?php echo isset($_SESSION['user_id']) ? 'book_service.php?service=reels' : 'login.html'; ?>" class="btn-order">Book Now</a>
            </div>

            <div class="card">
                <h3>YouTube Longform</h3>
                <p>Complete video editing with motion graphics, sound design, and storytelling elements.</p>
                <div class="price">₹1,999 / Video</div>
                <a href="<?php echo isset($_SESSION['user_id']) ? 'book_service.php?service=youtube' : 'login.html'; ?>" class="btn-order">Book Now</a>
            </div>

            <div class="card">
                <h3>Thumbnail Design</h3>
                <p>High CTR YouTube thumbnail design to increase your video views drastically.</p>
                <div class="price">₹299 / Graphic</div>
                <a href="<?php echo isset($_SESSION['user_id']) ? 'book_service.php?service=thumbnail' : 'login.html'; ?>" class="btn-order">Book Now</a>
            </div>
        </div>
    </div>

</body>
</html>