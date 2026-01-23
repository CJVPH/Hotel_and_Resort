<?php
require_once '../config/database.php';

// This file is for creating the first admin account
// DELETE THIS FILE after creating your admin account for security!

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = trim($_POST['full_name']);
    
    if (empty($username) || empty($email) || empty($password) || empty($full_name)) {
        $message = 'All fields are required!';
        $messageType = 'error';
    } elseif ($password !== $confirm_password) {
        $message = 'Passwords do not match!';
        $messageType = 'error';
    } elseif (strlen($password) < 6) {
        $message = 'Password must be at least 6 characters long!';
        $messageType = 'error';
    } else {
        $conn = getDBConnection();
        
        // Check if username or email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $message = 'Username or email already exists!';
            $messageType = 'error';
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $is_admin = 1; // Set as admin
            
            // Insert admin user
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, is_admin) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssi", $username, $email, $hashed_password, $full_name, $is_admin);
            
            if ($stmt->execute()) {
                $message = 'Admin account created successfully! You can now login at admin/login.php';
                $messageType = 'success';
            } else {
                $message = 'Error creating admin account. Please try again.';
                $messageType = 'error';
            }
        }
        
        $stmt->close();
        $conn->close();
    }
}
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
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1><i class="fas fa-user-shield"></i> Create Admin Account</h1>
                <p style="color: #dc3545; font-weight: 600;">⚠️ DELETE THIS FILE AFTER CREATING ADMIN ACCOUNT!</p>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType === 'success' ? 'success' : 'error'; ?>">
                    <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i> <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="auth-form">
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Full Name</label>
                    <input type="text" name="full_name" placeholder="Enter your full name" required>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-user-circle"></i> Username</label>
                    <input type="text" name="username" placeholder="Choose a username" required>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-envelope"></i> Email</label>
                    <input type="email" name="email" placeholder="Enter your email" required>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-lock"></i> Password</label>
                    <input type="password" name="password" placeholder="Create a password (min. 6 characters)" required>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-lock"></i> Confirm Password</label>
                    <input type="password" name="confirm_password" placeholder="Confirm your password" required>
                </div>

                <button type="submit" class="btn-primary">
                    <i class="fas fa-user-shield"></i> Create Admin Account
                </button>
            </form>

            <div class="auth-footer">
                <p><a href="login.php">Already have an admin account? Login here</a></p>
            </div>
        </div>
    </div>
</body>
</html>

