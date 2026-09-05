<?php
// Include this after session_start(). Expects $conn to be optional.
$__loggedIn = isset($_SESSION['user_id']);
$__username = $_SESSION['username'] ?? '';
?>
<header class="eh-nav">
    <a href="<?php echo $__loggedIn ? 'home.php' : 'index.php'; ?>" class="eh-logo">Edit<span>Hub</span></a>

    <ul class="eh-links">
        <li><a href="<?php echo $__loggedIn ? 'home.php' : 'index.php'; ?>">Home</a></li>
        <li>
            <a>Services</a>
            <ul class="eh-dropdown">
                <li><a href="services.php">Book a Service</a></li>
                <?php if ($__loggedIn): ?><li><a href="home.php#editors">Hire Editors</a></li><?php endif; ?>
            </ul>
        </li>
        <li>
            <a>Assets</a>
            <ul class="eh-dropdown">
                <li><a href="assets.php#free">Free Assets</a></li>
                <li><a href="assets.php#paid">Paid Assets</a></li>
            </ul>
        </li>
        <?php if ($__loggedIn): ?>
            <li><a href="home.php#materials">Materials</a></li>
            <li><a href="profile.php">Profile</a></li>
        <?php endif; ?>
    </ul>

    <div class="eh-right">
        <?php if ($__loggedIn): ?>
            <span class="eh-user-tag">Hi, <?php echo htmlspecialchars($__username); ?></span>
            <a href="profile.php" class="btn btn-ghost btn-sm">Profile</a>
            <a href="php/logout.php" class="btn btn-primary btn-sm">Logout</a>
        <?php else: ?>
            <a href="login.php" class="btn btn-ghost btn-sm">Login</a>
            <a href="login.php?action=signup" class="btn btn-primary btn-sm">Sign Up</a>
        <?php endif; ?>
    </div>
</header>