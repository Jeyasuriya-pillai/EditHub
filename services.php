<?php
session_start();
require_once 'php/db.php';

// Dynamic freelancer services with owner + rating info
$services = $conn->query("
    SELECT us.id, us.title, us.description, us.price, u.id AS owner_id, u.username, u.full_name,
           COALESCE(AVG(r.rating),0) AS avg_rating, COUNT(r.id) AS review_count
    FROM user_services us
    JOIN users u ON us.user_id = u.id
    LEFT JOIN reviews r ON r.target_type = 'service' AND r.target_id = us.id
    GROUP BY us.id
    ORDER BY us.created_at DESC
");

function starDisplay($rating) {
    $rating = round($rating);
    return str_repeat('★', $rating) . str_repeat('☆', 5 - $rating);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Services - EditHub</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .wrap { max-width: 1080px; margin: 0 auto; padding: 50px 40px 80px; }
        .page-head { margin-bottom: 34px; }
        .page-head .eyebrow { color: var(--accent); font-family: var(--font-display); font-weight: 800; font-size: 12px; letter-spacing: 0.08em; margin-bottom: 10px; }
        .page-head h1 { font-size: 32px; }

        .svc-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0; }
        .svc-grid > .card { border-radius: 0; border-left: none; }
        .svc-grid > .card:first-child { border-left: 1px solid var(--line); }
        .svc-card-pad { padding: 30px; }
        .svc-card-pad h3 { font-size: 19px; margin-bottom: 6px; font-family: var(--font-display); }
        .svc-card-pad h3 a { color: inherit; text-decoration: none; }
        .svc-card-pad h3 a:hover { color: var(--accent); }
        .svc-author { font-size: 12px; color: var(--muted); margin-bottom: 10px; }
        .svc-author a { color: var(--teal); text-decoration: none; }
        .svc-card-pad p { color: var(--muted); font-size: 13px; line-height: 1.6; margin-bottom: 14px; min-height: 60px; }
        .svc-price { font-family: var(--font-display); font-size: 24px; font-weight: 900; color: var(--accent); margin-bottom: 10px; }
        .svc-stars-row { margin-bottom: 16px; }
        .btn-group { display: flex; gap: 8px; }
        .empty-msg { padding: 30px; color: var(--muted); }

        @media (max-width: 860px) {
            .svc-grid { grid-template-columns: 1fr; }
            .svc-grid > .card:first-child { border-left: none; }
        }
    </style>
</head>
<body>

    <?php require_once 'php/navbar.php'; ?>

    <div class="wrap">
        <div class="page-head">
            <div class="eyebrow">HIRE A FREELANCER</div>
            <h1>Services offered by our editors</h1>
        </div>

        <div class="svc-grid">
            <?php if ($services && $services->num_rows > 0): ?>
                <?php while ($s = $services->fetch_assoc()): ?>
                    <div class="card">
                        <div class="svc-card-pad">
                            <h3><a href="view_profile.php?user_id=<?php echo $s['owner_id']; ?>"><?php echo htmlspecialchars($s['title']); ?></a></h3>
                            <div class="svc-author">By <a href="view_profile.php?user_id=<?php echo $s['owner_id']; ?>"><?php echo htmlspecialchars($s['full_name'] ?: $s['username']); ?></a></div>
                            <p><?php echo htmlspecialchars($s['description']); ?></p>
                            <div class="svc-price">
                                <?php echo $s['price'] ? '₹' . htmlspecialchars($s['price']) : 'Contact for price'; ?>
                            </div>
                            <div class="svc-stars-row">
                                <span class="stars"><?php echo starDisplay($s['avg_rating']); ?></span>
                                <span class="rating-count">(<?php echo $s['review_count']; ?> review<?php echo $s['review_count'] != 1 ? 's' : ''; ?>)</span>
                            </div>
                            <div class="btn-group">
                                <a href="view_profile.php?user_id=<?php echo $s['owner_id']; ?>" class="btn btn-outline btn-sm">Profile</a>
                                <a href="review.php?type=service&id=<?php echo $s['id']; ?>" class="btn btn-primary btn-sm">Review</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="empty-msg">No editors have listed their services yet. Add "My Services (Hire Me)" from your profile to be the first!</p>
            <?php endif; ?>
        </div>
    </div>

    <footer class="eh-footer">&copy; <?php echo date("Y"); ?> EditHub. All rights reserved.</footer>

</body>
</html>