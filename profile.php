<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EditHub - My Profile</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Roboto, sans-serif; }
        body { background-color: #0b0d14; color: #ffffff; }

        /* Navbar with matching theme */
        .navbar { display: flex; justify-content: space-between; align-items: center; padding: 18px 40px; background: rgba(15, 17, 26, 0.9); border-bottom: 1px solid #1f2430; position: sticky; top: 0; z-index: 100; }
        .logo { font-size: 24px; font-weight: 800; color: #ffffff; text-decoration: none; }
        .logo span { color: #8b5cf6; }
        .nav-links { display: flex; gap: 25px; list-style: none; align-items: center; }
        .nav-links a { color: #9ca3af; text-decoration: none; font-size: 14px; font-weight: 600; transition: 0.3s; }
        .nav-links a:hover, .nav-links a.active { color: #8b5cf6; }
        .btn-logout { background: #8b5cf6; color: #fff; padding: 8px 18px; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 14px; transition: 0.3s; }
        .btn-logout:hover { background: #7c3aed; }

        /* Main Profile Layout */
        .container { max-width: 1100px; margin: 40px auto; padding: 0 20px; }
        .profile-card { background: #161922; border: 1px solid #232836; border-radius: 14px; padding: 30px; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; }
        .user-info h2 { font-size: 24px; color: #fff; }
        .user-info p { color: #a78bfa; margin-top: 5px; font-weight: 500; }
        .badge { background: rgba(139, 92, 246, 0.2); color: #a78bfa; border: 1px solid #8b5cf6; padding: 6px 14px; border-radius: 20px; font-size: 13px; font-weight: 600; }

        /* Sections Grid */
        .section-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; }
        .panel { background: #161922; border: 1px solid #232836; border-radius: 14px; padding: 25px; }
        .panel-title { font-size: 18px; font-weight: 700; margin-bottom: 20px; color: #f3f4f6; display: flex; justify-content: space-between; align-items: center; }
        .panel-title span { color: #8b5cf6; }

        /* Forms & Inputs */
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-size: 13px; color: #9ca3af; margin-bottom: 6px; }
        .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 10px 14px; border-radius: 8px; border: 1px solid #232836; background: #0f111a; color: #fff; font-size: 14px; outline: none; }
        .form-group input:focus, .form-group textarea:focus { border-color: #8b5cf6; }
        .btn-add { background: #8b5cf6; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; width: 100%; transition: 0.3s; }
        .btn-add:hover { background: #7c3aed; }

        /* Existing Items List */
        .item-list { margin-top: 20px; display: flex; flex-direction: column; gap: 12px; }
        .item-card { background: #0f111a; border: 1px solid #232836; padding: 12px 16px; border-radius: 8px; display: flex; justify-content: space-between; align-items: center; }
        .item-card h4 { font-size: 14px; color: #fff; }
        .item-card p { font-size: 12px; color: #9ca3af; }
        .price-tag { color: #8b5cf6; font-weight: 700; font-size: 14px; }
    </style>
</head>
<body>

    <!-- Matching Navbar -->
    <header class="navbar">
        <a href="index.php" class="logo">EDIT<span>HUB</span></a>
        <ul class="nav-links">
            <li><a href="index.php">Home</a></li>
            <li><a href="index.php#services">Services</a></li>
            <li><a href="index.php#assets">Assets</a></li>
            <li><a href="profile.php" class="active">Profile</a></li>
        </ul>
        <a href="php/logout.php" class="btn-logout">Logout</a>
    </header>

    <div class="container">
        <!-- User Info Banner -->
        <div class="profile-card">
            <div class="user-info">
                <h2>Username: <?php echo htmlspecialchars($username); ?></h2>
                <p>Creator / Editor Account</p>
            </div>
            <span class="badge">Active Member</span>
        </div>

        <div class="section-grid">
            <!-- Manage Services (Hire Me Rates) -->
            <div class="panel">
                <div class="panel-title"><span>💼</span> My Personal Services (Hire Me)</div>
                <form action="#" method="POST">
                    <div class="form-group">
                        <label>Service Title (e.g., Reels Editing, VFX Composition)</label>
                        <input type="text" placeholder="Enter service name..." required>
                    </div>
                    <div class="form-group">
                        <label>Hourly / Project Rate (₹)</label>
                        <input type="text" placeholder="e.g., ₹1,500 / hr" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea rows="2" placeholder="Brief details about your editing service..."></textarea>
                    </div>
                    <button type="submit" class="btn-add">+ Add Service Option</button>
                </form>

                <div class="item-list">
                    <div class="item-card">
                        <div>
                            <h4>Reels & Shorts Editing</h4>
                            <p>Fast pacing & dynamic captions</p>
                        </div>
                        <span class="price-tag">₹1,500/hr</span>
                    </div>
                </div>
            </div>

            <!-- Manage Assets -->
            <div class="panel">
                <div class="panel-title"><span>📦</span> My Uploaded Assets</div>
                <form action="#" method="POST">
                    <div class="form-group">
                        <label>Asset Name</label>
                        <input type="text" placeholder="e.g., Cinematic LUT Pack" required>
                    </div>
                    <div class="form-group">
                        <label>Category</label>
                        <select>
                            <option>PNG Cutouts</option>
                            <option>SFX Sound Effects</option>
                            <option>CC & Presets</option>
                            <option>VFX Overlays</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Download Link / Drive URL</label>
                        <input type="url" placeholder="https://..." required>
                    </div>
                    <button type="submit" class="btn-add">+ Upload Asset</button>
                </form>

                <div class="item-list">
                    <div class="item-card">
                        <div>
                            <h4>4K Dark Cinematic CC</h4>
                            <p>Category: CC & Presets</p>
                        </div>
                        <span class="price-tag">Free</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>