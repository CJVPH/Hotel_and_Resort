<?php
require_once '../config/database.php';

$conn = getDBConnection();
$message = '';
$messageType = '';

$adminUsername = 'admin';
$adminEmail = 'admin@paradisehotel.com';
$adminPassword = 'admin123';
$adminFullName = 'Administrator';
$isAdmin = 1;

// Check if admin already exists
$checkAdmin = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
$checkAdmin->bind_param("ss", $adminUsername, $adminEmail);
$checkAdmin->execute();
$result = $checkAdmin->get_result();

if ($result->num_rows > 0) {
    // Update existing admin
    $hashedPassword = password_hash($adminPassword, PASSWORD_DEFAULT);
    $updateStmt = $conn->prepare("UPDATE users SET password = ?, is_admin = 1 WHERE username = ?");
    $updateStmt->bind_param("ss", $hashedPassword, $adminUsername);
    
    if ($updateStmt->execute()) {
        $message = 'Admin account updated successfully! Password changed to ' . $adminPassword;
        $messageType = 'success';
    } else {
        $message = 'Error updating admin account: ' . $conn->error;
        $messageType = 'error';
    }
    $updateStmt->close();
} else {
    // Create new admin account
    $hashedPassword = password_hash($adminPassword, PASSWORD_DEFAULT);
    $insertStmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, is_admin) VALUES (?, ?, ?, ?, ?)");
    $insertStmt->bind_param("ssssi", $adminUsername, $adminEmail, $hashedPassword, $adminFullName, $isAdmin);
    
    if ($insertStmt->execute()) {
        $message = 'Admin account created successfully!';
        $messageType = 'success';
    } else {
        $message = 'Error creating admin account: ' . $conn->error;
        $messageType = 'error';
    }
    $insertStmt->close();
}

$checkAdmin->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Admin Account</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { padding: 2rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .create-card { background: white; padding: 3rem; border-radius: 20px; max-width: 600px; margin: 0 auto; box-shadow: 0 20px 60px rgba(0,0,0,0.3); }
        .credentials-box { background: #f8f9fa; padding: 1.5rem; border-radius: 10px; margin: 1.5rem 0; border-left: 4px solid #667eea; }
        .credential-item { display: flex; justify-content: space-between; padding: 0.75rem 0; border-bottom: 1px solid #e0e0e0; }
        .credential-item:last-child { border-bottom: none; }
        .credential-label { font-weight: 600; color: #555; }
        .credential-value { color: #333; font-family: monospace; font-weight: 600; }
        .btn { display: inline-block; padding: 0.75rem 1.5rem; background: #667eea; color: white; text-decoration: none; border-radius: 8px; margin-top: 1rem; }
        .btn:hover { background: #5568d3; }
    </style>
</head>
<body>
    <div class="create-card">
        <div style="text-align: center; margin-bottom: 2rem;">
            <h1 style="color: #667eea; margin-bottom: 0.5rem;">
                <i class="fas fa-user-shield"></i> Admin Account Setup
            </h1>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType === 'success' ? 'success' : 'error'; ?>" style="margin-bottom: 2rem;">
                <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i> 
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($messageType === 'success'): ?>
            <div class="credentials-box">
                <h3 style="color: #667eea; margin-top: 0;"><i class="fas fa-key"></i> Your Admin Login Credentials</h3>
                <div class="credential-item">
                    <span class="credential-label">Username:</span>
                    <span class="credential-value"><?php echo htmlspecialchars($adminUsername); ?></span>
                </div>
                <div class="credential-item">
                    <span class="credential-label">Email:</span>
                    <span class="credential-value"><?php echo htmlspecialchars($adminEmail); ?></span>
                </div>
                <div class="credential-item">
                    <span class="credential-label">Password:</span>
                    <span class="credential-value"><?php echo htmlspecialchars($adminPassword); ?></span>
                </div>
            </div>

            <div style="text-align: center; margin-top: 2rem;">
                <a href="login.php" class="btn">
                    <i class="fas fa-sign-in-alt"></i> Go to Admin Login
                </a>
            </div>
        <?php else: ?>
            <p style="text-align: center; color: #666;">
                Click the button below to create your admin account.
            </p>
            <form method="POST" action="" style="text-align: center;">
                <button type="submit" class="btn-primary" style="width: 100%;">
                    <i class="fas fa-user-shield"></i> Create Admin Account
                </button>
            </form>
        <?php endif; ?>

        <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid #e0e0e0; text-align: center;">
            <a href="login.php" style="color: #667eea; text-decoration: none;">
                <i class="fas fa-arrow-left"></i> Back to Login
            </a>
        </div>
    </div>
</body>
</html>

