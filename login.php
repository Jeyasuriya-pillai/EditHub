<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>EditHub - Authentication</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .auth-container { max-width: 400px; margin: 60px auto; background: #0b1428; border: 1px solid #1b2a48; border-radius: 12px; padding: 30px; color: white; }
        .auth-container h2 { text-align: center; color: #8b5cf6; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-size: 13px; color: #94a3b8; margin-bottom: 5px; }
        .form-group input { width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #1b2a48; background: #1e293b; color: white; box-sizing: border-box; }
        .notice { background: rgba(239, 68, 68, 0.1); border: 1px solid #ef4444; color: #f87171; padding: 10px; border-radius: 6px; font-size: 12px; margin-bottom: 15px; }
        .btn-submit { width: 100%; background: #8b5cf6; color: white; padding: 10px; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; }
        .toggle-link { text-align: center; margin-top: 15px; font-size: 13px; }
        .toggle-link a { color: #8b5cf6; text-decoration: none; }
    </style>
</head>
<body>

    <div class="auth-container">
        <!-- Login Form (Default Visible) -->
        <div id="loginForm">
            <h2>Login to EditHub</h2>
            <form action="php/login_process.php" method="POST">
                <div class="form-group">
                    <label>Username (ID)</label>
                    <input type="text" name="username" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" class="btn-submit">Login</button>
            </form>
            <div class="toggle-link">
                <a href="#" onclick="showForm('forgotForm')">Forgot Password?</a> | 
                <a href="#" onclick="showForm('registerForm')">Create Account</a>
            </div>
        </div>

        <!-- Registration Form -->
        <div id="registerForm" style="display: none;">
            <h2>Create EditHub Account</h2>
            <div class="notice">
                ⚠️ <b>Important Notice:</b> Save your recovery pass carefully! If you forget it, you will lose your account permanently.
            </div>
            <form action="php/register_process.php" method="POST">
                <div class="form-group">
                    <label>Username (ID)</label>
                    <input type="text" name="username" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                <div class="form-group">
                    <label>Recovery Pass Phrase</label>
                    <input type="text" name="recovery_key" placeholder="e.g., EditHub@2026#JX" required>
                </div>
                <button type="submit" class="btn-submit">Sign Up</button>
            </form>
            <div class="toggle-link">
                Already have an account? <a href="#" onclick="showForm('loginForm')">Login</a>
            </div>
        </div>

        <!-- Forgot Password Form -->
        <div id="forgotForm" style="display: none;">
            <h2>Reset Password</h2>
            <form action="php/reset_process.php" method="POST">
                <div class="form-group">
                    <label>Username (ID)</label>
                    <input type="text" name="username" required>
                </div>
                <div class="form-group">
                    <label>Recovery Question: Enter your EditHub recovery phrase</label>
                    <input type="text" name="recovery_key" placeholder="Enter recovery pass..." required>
                </div>
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" required>
                </div>
                <button type="submit" class="btn-submit">Reset Password</button>
            </form>
            <div class="toggle-link">
                <a href="#" onclick="showForm('loginForm')">Back to Login</a>
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

        // URL parameter checking for direct access to register
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('action') === 'signup') {
            showForm('registerForm');
        }
    </script>
</body>
</html>