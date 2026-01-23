<?php
require_once 'auth.php';
require_once '../config/database.php';

if (session_status() === PHP_SESSION_NONE) session_start();

$adminId = $_SESSION['admin_id'] ?? null;
if (!$adminId) {
    header('Location: login.php');
    exit();
}

$userId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($userId <= 0) {
    header('Location: users.php?message=' . urlencode('Invalid user id'));
    exit();
}

$conn = getDBConnection();
$stmt = $conn->prepare("SELECT id, username, email, full_name, created_at, is_admin FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $userId);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows !== 1) {
    $stmt->close();
    $conn->close();
    header('Location: users.php?message=' . urlencode('User not found'));
    exit();
}
$user = $res->fetch_assoc();
$stmt->close();

// Get user's reservations
$rstmt = $conn->prepare("SELECT id, checkin_date, checkout_date, room_type, guests, price, status FROM reservations WHERE user_id = ? ORDER BY created_at DESC");
$rstmt->bind_param('i', $userId);
$rstmt->execute();
$res2 = $rstmt->get_result();
$reservations = $res2->fetch_all(MYSQLI_ASSOC);
$rstmt->close();

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>View User - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="admin-container" style="max-width:1000px;margin:2rem auto;">
        <a href="users.php" style="display:inline-block;margin-bottom:1rem;">&larr; Back to Users</a>
        <div class="admin-section">
            <h2>User Profile</h2>
            <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
            <p><strong>Full name:</strong> <?php echo htmlspecialchars($user['full_name']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            <p><strong>Is Admin:</strong> <?php echo $user['is_admin'] == 1 ? 'Yes' : 'No'; ?></p>
            <p><strong>Joined:</strong> <?php echo date('M d, Y', strtotime($user['created_at'])); ?></p>

            <div style="margin-top:1rem;">
                <a href="change_user_password.php?id=<?php echo $user['id']; ?>" class="btn-sm" style="background:#667eea;color:#fff;padding:0.5rem 1rem;border-radius:6px;text-decoration:none;">Change Password</a>
            </div>
        </div>

        <div class="admin-section" style="margin-top:1rem;">
            <h3>Reservations</h3>
            <?php if (empty($reservations)): ?>
                <p>No reservations found for this user.</p>
            <?php else: ?>
                <table style="width:100%;border-collapse:collapse;">
                    <thead>
                        <tr><th>ID</th><th>Room</th><th>Check-in</th><th>Check-out</th><th>Guests</th><th>Price</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reservations as $r): ?>
                            <tr>
                                <td>#<?php echo $r['id']; ?></td>
                                <td><?php echo htmlspecialchars($r['room_type']); ?></td>
                                <td><?php echo htmlspecialchars($r['checkin_date']); ?></td>
                                <td><?php echo htmlspecialchars($r['checkout_date']); ?></td>
                                <td><?php echo htmlspecialchars($r['guests']); ?></td>
                                <td>₱<?php echo number_format($r['price'],2); ?></td>
                                <td><?php echo htmlspecialchars($r['status']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>