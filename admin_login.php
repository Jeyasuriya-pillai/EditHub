<?php
session_start();

// ===== Hardcoded Admin Credentials (change these as needed) =====
$ADMIN_USERNAME = "admin";
$ADMIN_PASSWORD = "admin@123";
// ==================================================================

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inputUser = trim($_POST['admin_username'] ?? '');
    $inputPass = trim($_POST['admin_password'] ?? '');

    if ($inputUser === $ADMIN_USERNAME && $inputPass === $ADMIN_PASSWORD) {
        $_SESSION['is_admin'] = true;
        header("Location: admin.php");
        exit();
    } else {
        $error = "Invalid admin username or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login - EditHub</title>
    <style>
        * { box-sizing: border-box; }
        body { background: #070d19; color: white; font-family: sans-serif; display:flex; align-items:center; justify-content:center; height:100vh; margin:0; }
        .login-box { background: #0b1428; border: 1px solid #1b2a48; border-radius: 12px; padding: 35px; width: 100%; max-width: 360px; }
        .login-box h2 { margin-top:0; margin-bottom:6px; color:#a78bfa; }
        .login-box p { color:#94a3b8; font-size:13px; margin-bottom:22px; }
        label { display:block; font-size:13px; color:#94a3b8; margin-bottom:6px; margin-top:14px; }
        input { width:100%; padding:10px 12px; border-radius:8px; border:1px solid #1b2a48; background:#070d19; color:#fff; font-size:14px; outline:none; }
        input:focus { border-color:#8b5cf6; }
        button { width:100%; margin-top:22px; padding:11px; border:none; border-radius:8px; background:#8b5cf6; color:#fff; font-weight:700; font-size:14px; cursor:pointer; }
        button:hover { background:#7c3aed; }
        .error-msg { background:rgba(239,68,68,0.15); border:1px solid rgba(239,68,68,0.4); color:#fca5a5; padding:10px 14px; border-radius:8px; font-size:13px; margin-bottom:10px; }
    </style>
</head>
<body>

    <div class="login-box">
        <h2>Admin Login</h2>
        <p>EditHub admin panel access</p>

        <?php if ($error): ?>
            <div class="error-msg">⚠️ <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST">
            <label>Admin Username</label>
            <input type="text" name="admin_username" required autofocus>

            <label>Admin Password</label>
            <input type="password" name="admin_password" required>

            <button type="submit">Login</button>
        </form>
    </div>

</body>
</html>