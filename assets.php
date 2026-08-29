<?php
session_start();
require_once 'php/db.php';

$freeAssets = $conn->query("
    SELECT ua.id, ua.title, ua.file_path, u.username, u.full_name
    FROM user_assets ua JOIN users u ON ua.user_id = u.id
    WHERE ua.type = 'free' ORDER BY ua.created_at DESC
");

$paidAssets = $conn->query("
    SELECT ua.id, ua.title, ua.price, ua.file_path, u.username, u.full_name
    FROM user_assets ua JOIN users u ON ua.user_id = u.id
    WHERE ua.type = 'paid' ORDER BY ua.created_at DESC
");
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
        .section-block { margin-bottom: 50px; }
        .section-block h2 { margin-bottom: 6px; }
        .section-block > p { color: #94a3b8; margin-bottom: 25px; }
        .asset-list { display: flex; flex-direction: column; gap: 15px; }
        .asset-item { background: #0b1428; border: 1px solid #1b2a48; padding: 20px; border-radius: 8px; display: flex; justify-content: space-between; align-items: center; }
        .download-btn { background: #10b981; color: white; padding: 8px 16px; border-radius: 5px; text-decoration: none; font-size: 14px; }
        .buy-btn { background: #3b82f6; color: white; padding: 8px 16px; border-radius: 5px; text-decoration: none; font-size: 14px; }
        .disabled-btn { background: #475569; color: #94a3b8; padding: 8px 16px; border-radius: 5px; text-decoration: none; font-size: 14px; }
        .asset-price { font-weight: bold; color: #fff; margin-right: 15px; }
        .empty-msg { color: #64748b; font-size: 14px; }
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
                <a href="login.php">Login</a>
            <?php endif; ?>
        </nav>
    </header>

    <div class="container">

        <!-- Free Assets -->
        <div class="section-block" id="free">
            <h2>Free Editing Resources</h2>
            <p>Free LUTs, sound packs, and overlay effects uploaded by the community.</p>

            <div class="asset-list">
                <?php if ($freeAssets && $freeAssets->num_rows > 0): ?>
                    <?php while ($a = $freeAssets->fetch_assoc()): ?>
                        <div class="asset-item">
                            <div>
                                <h4 style="color: #fff;"><?php echo htmlspecialchars($a['title']); ?></h4>
                                <p style="color: #94a3b8; font-size: 13px;">By <?php echo htmlspecialchars($a['full_name'] ?: $a['username']); ?></p>
                            </div>
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <a href="<?php echo htmlspecialchars($a['file_path']); ?>" class="download-btn" download>Download Asset</a>
                            <?php else: ?>
                                <a href="login.php" class="disabled-btn">Login to Download</a>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="empty-msg">No free assets uploaded yet.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Paid Assets -->
        <div class="section-block" id="paid">
            <h2>Premium Asset Packs</h2>
            <p>High-end packs uploaded by creators for professional-quality edits.</p>

            <div class="asset-list">
                <?php if ($paidAssets && $paidAssets->num_rows > 0): ?>
                    <?php while ($a = $paidAssets->fetch_assoc()): ?>
                        <div class="asset-item">
                            <div>
                                <h4 style="color: #fff;"><?php echo htmlspecialchars($a['title']); ?></h4>
                                <p style="color: #94a3b8; font-size: 13px;">By <?php echo htmlspecialchars($a['full_name'] ?: $a['username']); ?></p>
                            </div>
                            <div style="display:flex; align-items:center;">
                                <span class="asset-price">₹<?php echo htmlspecialchars($a['price']); ?></span>
                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <a href="<?php echo htmlspecialchars($a['file_path']); ?>" class="buy-btn">Buy Now</a>
                                <?php else: ?>
                                    <a href="login.php" class="disabled-btn">Login to Buy</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="empty-msg">No paid assets uploaded yet.</p>
                <?php endif; ?>
            </div>
        </div>

    </div>

</body>
</html>