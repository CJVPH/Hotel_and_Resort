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
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = isset($_POST['user_id']) ? intval($_POST['user_id']) : $userId;
    $newPass = trim($_POST['password'] ?? '');
    $confirm = trim($_POST['confirm'] ?? '');

    if ($userId <= 0) {
        $error = 'Invalid user id';
    } elseif (strlen($newPass) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($newPass !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $conn = getDBConnection();
        $hashed = password_hash($newPass, PASSWORD_DEFAULT);
        $stmt = $conn->prepare('UPDATE users SET password = ? WHERE id = ?');
        $stmt->bind_param('si', $hashed, $userId);
        if ($stmt->execute()) {
            $stmt->close();
            $conn->close();
            header('Location: view_user.php?id=' . $userId . '&message=' . urlencode('Password changed successfully'));
            exit();
        } else {
            $error = 'Error updating password: ' . $conn->error;
        }
        $stmt->close();
        $conn->close();
    }
}

// Load user for GET
if ($userId > 0 && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $conn = getDBConnection();
    $stmt = $conn->prepare('SELECT id, username, email, full_name FROM users WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 1) {
        $user = $res->fetch_assoc();
    } else {
        header('Location: users.php?message=' . urlencode('User not found'));
        exit();
    }
    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Change User Password</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="admin-container" style="max-width:600px;margin:2rem auto;">
        <a href="view_user.php?id=<?php echo htmlspecialchars($userId); ?>">&larr; Back</a>
        <div class="admin-section" style="margin-top:1rem;">
            <h2>Change Password for <?php echo htmlspecialchars($user['username'] ?? ''); ?></h2>
            <?php if (!empty($error)): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form method="POST">
                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($userId); ?>">
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="password" required>
                </div>
                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm" required>
                </div>
                <button type="submit" class="btn-primary">Change Password</button>
            </form>
        </div>
    </div>
</body>
</html>