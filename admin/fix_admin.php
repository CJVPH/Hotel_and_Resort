<?php
require_once '../config/database.php';

$conn = getDBConnection();
$messages = [];

// Step 1: Ensure is_admin column exists
$columnCheck = $conn->query("SHOW COLUMNS FROM users LIKE 'is_admin'");
if ($columnCheck->num_rows == 0) {
    $conn->query("ALTER TABLE users ADD COLUMN is_admin TINYINT(1) DEFAULT 0");
    $messages[] = ['type' => 'success', 'msg' => 'Added is_admin column to users table'];
} else {
    $messages[] = ['type' => 'info', 'msg' => 'is_admin column already exists'];
}

// Step 2: Check if admin account exists
$checkAdmin = $conn->query("SELECT id, username, email, password, full_name, is_admin FROM users WHERE username = 'admin'");
$adminExists = $checkAdmin->num_rows > 0;
$admin = $adminExists ? $checkAdmin->fetch_assoc() : null;

if (!$adminExists) {
    // Create admin account
    $adminUsername = 'admin';
    $adminEmail = 'admin@paradisehotel.com';
    $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $adminName = 'Administrator';
    $isAdmin = 1;
    
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, is_admin) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $adminUsername, $adminEmail, $adminPassword, $adminName, $isAdmin);
    
    if ($stmt->execute()) {
        $messages[] = ['type' => 'success', 'msg' => 'Admin account created successfully!'];
        $adminExists = true;
    } else {
        $messages[] = ['type' => 'error', 'msg' => 'Error creating admin: ' . $conn->error];
    }
    $stmt->close();
} else {
    // Ensure admin is set to admin
    if ($admin['is_admin'] != 1) {
        $conn->query("UPDATE users SET is_admin = 1 WHERE username = 'admin'");
        $messages[] = ['type' => 'success', 'msg' => 'Updated admin account: Set is_admin = 1'];
    } else {
        $messages[] = ['type' => 'info', 'msg' => 'Admin account already exists and is_admin is set correctly'];
    }
}

// Step 3: Verify admin account
$verifyAdmin = $conn->query("SELECT id, username, email, full_name, is_admin FROM users WHERE username = 'admin' AND is_admin = 1");
$verified = $verifyAdmin->num_rows > 0;

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fix Admin Access</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { padding: 2rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .fix-card { background: white; padding: 2rem; border-radius: 15px; max-width: 700px; margin: 0 auto; box-shadow: 0 20px 60px rgba(0,0,0,0.3); }
        .message { padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
        .message.success { background: #d4edda; color: #155724; border-left: 4px solid #28a745; }
        .message.error { background: #f8d7da; color: #721c24; border-left: 4px solid #dc3545; }
        .message.info { background: #d1ecf1; color: #0c5460; border-left: 4px solid #17a2b8; }
        .credentials-box { background: #f8f9fa; padding: 1.5rem; border-radius: 10px; margin: 1.5rem 0; border-left: 4px solid #667eea; }
        .credential-item { display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #e0e0e0; }
        .credential-item:last-child { border-bottom: none; }
        .credential-label { font-weight: 600; color: #555; }
        .credential-value { color: #333; font-family: monospace; }
        .btn { display: inline-block; padding: 0.75rem 1.5rem; background: #667eea; color: white; text-decoration: none; border-radius: 8px; margin-top: 1rem; }
        .btn:hover { background: #5568d3; }
    </style>
</head>
<body>
    <div class="fix-card">
        <h1 style="color: #667eea; margin-bottom: 1rem;">
            <i class="fas fa-tools"></i> Admin Access Fix
        </h1>
        
        <?php foreach ($messages as $msg): ?>
            <div class="message <?php echo $msg['type']; ?>">
                <i class="fas fa-<?php echo $msg['type'] === 'success' ? 'check-circle' : ($msg['type'] === 'error' ? 'exclamation-circle' : 'info-circle'); ?>"></i>
                <?php echo htmlspecialchars($msg['msg']); ?>
            </div>
        <?php endforeach; ?>
        
        <?php if ($verified): ?>
            <div class="message success">
                <strong>✓ Admin account is ready!</strong>
            </div>
            
            <div class="credentials-box">
                <h3><i class="fas fa-key"></i> Login Credentials</h3>
                <div class="credential-item">
                    <span class="credential-label">Username:</span>
                    <span class="credential-value">admin</span>
                </div>
                <div class="credential-item">
                    <span class="credential-label">Email:</span>
                    <span class="credential-value">admin@paradisehotel.com</span>
                </div>
                <div class="credential-item">
                    <span class="credential-label">Password:</span>
                    <span class="credential-value">admin123</span>
                </div>
            </div>
            
            <div style="text-align: center;">
                <a href="login.php" class="btn">
                    <i class="fas fa-sign-in-alt"></i> Go to Admin Login
                </a>
            </div>
        <?php else: ?>
            <div class="message error">
                <strong>✗ There was an issue setting up the admin account.</strong>
                <p style="margin-top: 0.5rem;">Please try running this page again or check your database connection.</p>
            </div>
        <?php endif; ?>
        
        <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid #e0e0e0; text-align: center;">
            <a href="check_admin.php" style="color: #667eea; text-decoration: none;">
                <i class="fas fa-search"></i> Check Admin Status
            </a>
        </div>
    </div>
</body>
</html>

