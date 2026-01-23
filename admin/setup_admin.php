<?php
require_once '../config/database.php';

// Default admin credentials
$default_admin = [
    'username' => 'admin',
    'email' => 'admin@paradisehotel.com',
    'password' => 'admin123', // Change this after first login!
    'full_name' => 'Administrator'
];

$message = '';
$messageType = '';

// Check if admin already exists
$conn = getDBConnection();
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
$stmt->bind_param("ss", $default_admin['username'], $default_admin['email']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $message = 'Admin account already exists! Use the login page to access admin panel.';
    $messageType = 'info';
} else {
    // Create admin account
    $hashed_password = password_hash($default_admin['password'], PASSWORD_DEFAULT);
    $is_admin = 1;
    
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, is_admin) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $default_admin['username'], $default_admin['email'], $hashed_password, $default_admin['full_name'], $is_admin);
    
    if ($stmt->execute()) {
        $message = 'Admin account created successfully!';
        $messageType = 'success';
    } else {
        $message = 'Error creating admin account: ' . $conn->error;
        $messageType = 'error';
    }
}

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Admin Account</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .setup-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .setup-card {
            background: white;
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 600px;
        }
        .credentials-box {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            margin: 1.5rem 0;
            border-left: 4px solid #667eea;
        }
        .credentials-box h3 {
            margin-top: 0;
            color: #667eea;
        }
        .credential-item {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid #e0e0e0;
        }
        .credential-item:last-child {
            border-bottom: none;
        }
        .credential-label {
            font-weight: 600;
            color: #555;
        }
        .credential-value {
            color: #333;
            font-family: monospace;
        }
        .warning-box {
            background: #fff3cd;
            border: 2px solid #ffc107;
            padding: 1rem;
            border-radius: 8px;
            margin: 1rem 0;
        }
        .warning-box i {
            color: #856404;
            margin-right: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="setup-container">
        <div class="setup-card">
            <div style="text-align: center; margin-bottom: 2rem;">
                <h1 style="color: #667eea; margin-bottom: 0.5rem;">
                    <i class="fas fa-user-shield"></i> Admin Setup
                </h1>
                <p style="color: #666;">Creating default admin account</p>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType === 'success' ? 'success' : ($messageType === 'error' ? 'error' : 'info'); ?>" style="margin-bottom: 2rem;">
                    <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : ($messageType === 'error' ? 'exclamation-circle' : 'info-circle'); ?>"></i> 
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <?php if ($messageType === 'success'): ?>
                <div class="credentials-box">
                    <h3><i class="fas fa-key"></i> Admin Login Credentials</h3>
                    <div class="credential-item">
                        <span class="credential-label">Username:</span>
                        <span class="credential-value"><?php echo htmlspecialchars($default_admin['username']); ?></span>
                    </div>
                    <div class="credential-item">
                        <span class="credential-label">Email:</span>
                        <span class="credential-value"><?php echo htmlspecialchars($default_admin['email']); ?></span>
                    </div>
                    <div class="credential-item">
                        <span class="credential-label">Password:</span>
                        <span class="credential-value"><?php echo htmlspecialchars($default_admin['password']); ?></span>
                    </div>
                </div>

                <div class="warning-box">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Important:</strong> Please change this password immediately after logging in for security!
                </div>

                <div style="text-align: center; margin-top: 2rem;">
                    <a href="login.php" class="btn-primary" style="display: inline-block; text-decoration: none;">
                        <i class="fas fa-sign-in-alt"></i> Go to Admin Login
                    </a>
                </div>
            <?php elseif ($messageType === 'info'): ?>
                <div style="text-align: center; margin-top: 2rem;">
                    <a href="login.php" class="btn-primary" style="display: inline-block; text-decoration: none;">
                        <i class="fas fa-sign-in-alt"></i> Go to Admin Login
                    </a>
                </div>
            <?php else: ?>
                <form method="POST" action="">
                    <button type="submit" class="btn-primary" style="width: 100%;">
                        <i class="fas fa-user-shield"></i> Create Default Admin Account
                    </button>
                </form>
            <?php endif; ?>

            <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid #e0e0e0; text-align: center;">
                <p style="color: #999; font-size: 0.9rem;">
                    <i class="fas fa-shield-alt"></i> Secure Admin Panel
                </p>
            </div>
        </div>
    </div>
</body>
</html>

