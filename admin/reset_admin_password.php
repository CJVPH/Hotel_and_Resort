<?php
require_once '../config/database.php';

// Allow only local requests for safety
$allowed = ['127.0.0.1', '::1'];
$remote = $_SERVER['REMOTE_ADDR'] ?? '';
if (!in_array($remote, $allowed)) {
    http_response_code(403);
    echo "Access denied. This tool can only be run from the server (localhost).";
    exit;
}

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword = trim($_POST['password'] ?? '');

    if (strlen($newPassword) < 6) {
        $message = 'Password must be at least 6 characters.';
        $messageType = 'error';
    } else {
        $conn = getDBConnection();
        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);

        // Try update existing admin user (by username or known email)
        $username = 'admin';
        $email = 'admin@paradisehotel.com';
        $stmt = $conn->prepare("UPDATE users SET password = ?, is_admin = 1 WHERE username = ? OR email = ?");
        $stmt->bind_param('sss', $hashed, $username, $email);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $message = 'Admin password updated successfully.';
            $messageType = 'success';
        } else {
            // Insert a new admin account if none matched
            $full_name = 'Administrator';
            $insert = $conn->prepare("INSERT INTO users (username, email, password, full_name, is_admin) VALUES (?, ?, ?, ?, 1)");
            $insert->bind_param('ssss', $username, $email, $hashed, $full_name);
            if ($insert->execute()) {
                $message = 'Admin account created successfully.';
                $messageType = 'success';
            } else {
                $message = 'Failed to create admin account: ' . $conn->error;
                $messageType = 'error';
            }
            $insert->close();
        }

        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Reset Admin Password</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { display:flex; align-items:center; justify-content:center; min-height:100vh; background:#f5f7ff; }
        .card { background:white; padding:2rem; border-radius:12px; box-shadow:0 10px 30px rgba(0,0,0,0.08); width:420px; }
        .card h1 { margin:0 0 1rem 0; color:#333; font-size:1.25rem; }
        .form-group { margin-bottom:1rem; }
        input[type=password] { width:100%; padding:0.75rem; border:1px solid #ddd; border-radius:6px; }
        .btn { display:inline-block; padding:0.6rem 1rem; background:#667eea; color:white; border-radius:6px; text-decoration:none; border:none; cursor:pointer; }
        .alert { padding:0.75rem 1rem; border-radius:6px; margin-bottom:1rem; }
        .alert.success { background:#d4edda; color:#155724; }
        .alert.error { background:#f8d7da; color:#721c24; }
        .note { font-size:0.9rem; color:#666; margin-top:0.5rem; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Reset or Create Admin Account</h1>

        <?php if ($message): ?>
            <div class="alert <?php echo $messageType === 'success' ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>New Admin Password</label>
                <input type="password" name="password" placeholder="Enter new password (min 6 chars)" required>
            </div>
            <div style="text-align:right;">
                <button type="submit" class="btn">Apply</button>
            </div>
        </form>

        <p class="note">This tool only runs when accessed from localhost. After you log in, change the password and remove this file.</p>
    </div>
</body>
</html>
