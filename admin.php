<?php
session_start();
require_once 'php/db.php';

/** @var mysqli $conn */
global $conn;

// Total Users Count
$count_query = "SELECT COUNT(*) as total_users FROM users";
$count_result = mysqli_query($conn, $count_query);
$total_data = mysqli_fetch_assoc($count_result);

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
        .admin-box { max-width: 800px; margin: 30px auto; background: #0b1428; border: 1px solid #1b2a48; border-radius: 12px; padding: 25px; }
        .stats-card { background: #1e293b; padding: 15px; border-radius: 8px; width: 220px; margin-bottom: 20px; text-align: center; border: 1px solid #334155; }
        .stats-card h3 { font-size: 32px; color: #8b5cf6; margin: 5px 0 0 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px; border: 1px solid #1b2a48; text-align: left; }
        th { background-color: #1e1b4b; color: #a78bfa; }
        tr:nth-child(even) { background-color: #0f172a; }
    </style>
</head>
<body>

    <div class="admin-box">
        <h2>EditHub Admin Panel</h2>
        
        <div class="stats-card">
            <span>Total Registered Users</span>
            <h3><?php echo $total_data['total_users']; ?></h3>
        </div>

        <h3>User Records</h3>
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