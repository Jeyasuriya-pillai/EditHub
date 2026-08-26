<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editing Assets - EditHub</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .container { max-width: 900px; margin: 40px auto; padding: 0 20px; }
        .asset-list { display: flex; flex-direction: column; gap: 15px; }
        .asset-item { background: #0b1428; border: 1px solid #1b2a48; padding: 20px; border-radius: 8px; display: flex; justify-content: space-between; align-items: center; }
        .download-btn { background: #10b981; color: white; padding: 8px 16px; border-radius: 5px; text-decoration: none; font-size: 14px; }
        .disabled-btn { background: #475569; color: #94a3b8; padding: 8px 16px; border-radius: 5px; text-decoration: none; font-size: 14px; }
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
        <h2>Free Editing Resources</h2>
        <p style="color: #94a3b8; margin-bottom: 25px;">Download free LUTs, sound packs, and overlay effects.</p>

        <div class="asset-list">
            <div class="asset-item">
                <div>
                    <h4 style="color: #fff;">Cinematic LUTs Pack (Premiere / CapCut)</h4>
                    <p style="color: #94a3b8; font-size: 13px;">Size: 45 MB • Format: .cube</p>
                </div>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="#" class="download-btn">Download Asset</a>
                <?php else: ?>
                    <a href="login.html" class="disabled-btn">Login to Download</a>
                <?php endif; ?>
            </div>

            <div class="asset-item">
                <div>
                    <h4 style="color: #fff;">100+ Whoosh & Transition Sound Effects</h4>
                    <p style="color: #94a3b8; font-size: 13px;">Size: 120 MB • Format: .wav</p>
                </div>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="#" class="download-btn">Download Asset</a>
                <?php else: ?>
                    <a href="login.html" class="disabled-btn">Login to Download</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

</body>
</html>