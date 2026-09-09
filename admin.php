<?php
session_start();
require_once 'php/db.php';

/** @var mysqli $conn */
global $conn;

// ===== Protect this page: must be logged in as admin =====
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: admin_login.php");
    exit();
}

// Total Users Count
$count_query = "SELECT COUNT(*) as total_users FROM users";
$count_result = mysqli_query($conn, $count_query);
$total_data = mysqli_fetch_assoc($count_result);

// Total Assets Count
$assets_count_result = mysqli_query($conn, "SELECT COUNT(*) as total FROM user_assets");
$total_assets = mysqli_fetch_assoc($assets_count_result)['total'];

// Total Materials Count
$materials_count_result = mysqli_query($conn, "SELECT COUNT(*) as total FROM user_materials");
$total_materials = mysqli_fetch_assoc($materials_count_result)['total'];

// Total Services Count
$services_count_result = mysqli_query($conn, "SELECT COUNT(*) as total FROM user_services");
$total_services = mysqli_fetch_assoc($services_count_result)['total'];

// Total Reviews Count
$reviews_count_result = mysqli_query($conn, "SELECT COUNT(*) as total FROM reviews");
$total_reviews = mysqli_fetch_assoc($reviews_count_result)['total'];

// Users List Query
$users_query = "SELECT id, username, created_at FROM users ORDER BY id DESC";
$users_result = mysqli_query($conn, $users_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>EditHub - Admin Dashboard</title>
    <style>
        body { background: #070d19; color: white; font-family: sans-serif; padding: 20px; }
        .admin-box { max-width: 900px; margin: 30px auto; background: #0b1428; border: 1px solid #1b2a48; border-radius: 12px; padding: 25px; }
        .top-bar { display:flex; justify-content:space-between; align-items:center; margin-bottom: 20px; }
        .logout-link { color:#fca5a5; text-decoration:none; font-size:13px; border:1px solid #3f2020; padding:8px 14px; border-radius:8px; }
        .logout-link:hover { background: rgba(239,68,68,0.1); }
        .stats-row { display:flex; flex-wrap:wrap; gap:16px; }
        .stats-card { background: #1e293b; padding: 15px; border-radius: 8px; width: 190px; text-align: center; border: 1px solid #334155; }
        .stats-card span { font-size: 12px; color: #94a3b8; }
        .stats-card h3 { font-size: 30px; color: #8b5cf6; margin: 5px 0 0 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px; border: 1px solid #1b2a48; text-align: left; }
        th { background-color: #1e1b4b; color: #a78bfa; }
        tr:nth-child(even) { background-color: #0f172a; }
        h2 { margin: 0; }
    </style>
</head>
<body>

    <div class="admin-box">
        <div class="top-bar">
            <h2>EditHub Admin Panel</h2>
            <a href="admin_logout.php" class="logout-link">Logout</a>
        </div>

        <div class="stats-row">
            <div class="stats-card">
                <span>Total Registered Users</span>
                <h3><?php echo $total_data['total_users']; ?></h3>
            </div>
            <div class="stats-card">
                <span>Total Assets</span>
                <h3><?php echo $total_assets; ?></h3>
            </div>
            <div class="stats-card">
                <span>Total Materials</span>
                <h3><?php echo $total_materials; ?></h3>
            </div>
            <div class="stats-card">
                <span>Total Services</span>
                <h3><?php echo $total_services; ?></h3>
            </div>
            <div class="stats-card">
                <span>Total Reviews</span>
                <h3><?php echo $total_reviews; ?></h3>
            </div>
        </div>

        <h3 style="margin-top:30px;">User Records</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Joined Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($users_result)): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                    <td><?php echo $row['created_at']; ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

</body>
</html>