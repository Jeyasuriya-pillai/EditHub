<?php session_start(); if (isset($_SESSION['user_id'])) { header("Location: home.php"); exit(); } ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>EditHub - Sign In</title>
<link rel="stylesheet" href="css/style.css">
<style>
    .auth-wrap { min-height: calc(100vh - 65px); display: flex; align-items: center; justify-content: center; padding: 40px 20px; }
    .auth-box { width: 100%; max-width: 400px; }
    .auth-box h2 { font-size: 26px; margin-bottom: 6px; }
    .auth-sub { color: var(--muted); font-size: 13px; margin-bottom: 24px; }
    .toggle-row { text-align: center; margin-top: 20px; font-size: 13px; color: var(--muted); }
    .toggle-row a { color: var(--accent); text-decoration: none; font-weight: 600; cursor: pointer; }
    .toggle-row a:hover { text-decoration: underline; }
    .divider-links { text-align:center; font-size:13px; color:var(--muted-2); margin-top:14px; }
</style>
</head>
<body>

<a href="index.php" class="eh-logo" style="padding:20px 40px; display:inline-flex;">Edit<span>Hub</span></a>

<div class="auth-wrap">
<div class="auth-box">

    <!-- Login -->
    <div id="loginForm" class="card card-pad card-top-accent">
        <h2>Sign in</h2>
        <p class="auth-sub">Access your editor toolkit and dashboard.</p>
        <form action="php/login_process.php" method="POST">
            <label>Username (ID)</label>
            <input type="text" name="username" required>
            <label>Password</label>
            <input type="password" name="password" required>
            <button type="submit" class="btn btn-primary btn-block" style="margin-top:22px;">Sign In</button>
        </form>
        <div class="divider-links">
            <a href="#" onclick="showForm('forgotForm'); return false;" style="color:var(--muted);">Forgot password?</a>
        </div>
        <div class="toggle-row">
            New to EditHub? <a onclick="showForm('registerForm')">Create an account</a>
        </div>
    </div>

    <!-- Register -->
    <div id="registerForm" class="card card-pad card-top-accent" style="display:none;">
        <h2>Create account</h2>
        <p class="auth-sub">Join the editor community.</p>
        <div class="notice-msg">
            ⚠️ Save your recovery pass phrase somewhere safe. If you lose it, your account cannot be recovered.
        </div>
        <form action="php/register_process.php" method="POST">
            <label>Username (ID)</label>
            <input type="text" name="username" required>
            <label>Password</label>
            <input type="password" name="password" required>
            <label>Recovery Pass Phrase</label>
            <input type="text" name="recovery_key" placeholder="e.g. EditHub@2026#JX" required>
            <button type="submit" class="btn btn-primary btn-block" style="margin-top:22px;">Sign Up</button>
        </form>
        <div class="toggle-row">
            Already have an account? <a onclick="showForm('loginForm')">Sign in</a>
        </div>
    </div>

    <!-- Forgot Password -->
    <div id="forgotForm" class="card card-pad card-top-accent" style="display:none;">
        <h2>Reset password</h2>
        <p class="auth-sub">Use your recovery pass phrase to set a new password.</p>
        <form action="php/reset_process.php" method="POST">
            <label>Username (ID)</label>
            <input type="text" name="username" required>
            <label>Recovery Pass Phrase</label>
            <input type="text" name="recovery_key" placeholder="Enter recovery pass..." required>
            <label>New Password</label>
            <input type="password" name="new_password" required>
            <button type="submit" class="btn btn-primary btn-block" style="margin-top:22px;">Reset Password</button>
        </form>
        <div class="toggle-row">
            <a onclick="showForm('loginForm')">Back to sign in</a>
        </div>
    </div>

</div>
</div>

<script>
    function showForm(formId) {
        document.getElementById('registerForm').style.display = 'none';
        document.getElementById('loginForm').style.display = 'none';
        document.getElementById('forgotForm').style.display = 'none';
        document.getElementById(formId).style.display = 'block';
    }
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('action') === 'signup') { showForm('registerForm'); }
</script>
</body>
</html>