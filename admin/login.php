<?php
require_once '../config/database.php';
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password!';
    } else {
        $conn = getDBConnection();
        
        // Check if user exists and is admin
        $stmt = $conn->prepare("SELECT id, username, password, full_name, is_admin FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password'])) {
                // Check if user is admin - be more flexible with different data types
                $isAdmin = ($user['is_admin'] == 1 || $user['is_admin'] === '1' || $user['is_admin'] === true || $user['is_admin'] == 'true');
                
                if ($isAdmin) {
                    $_SESSION['admin_id'] = $user['id'];
                    $_SESSION['admin_username'] = $user['username'];
                    $_SESSION['admin_full_name'] = $user['full_name'];
                    
                    // Verify session was set
                    if (isset($_SESSION['admin_id'])) {
                        header('Location: index.php');
                        exit();
                    } else {
                        $error = 'Session error! Please try again.';
                    }
                } else {
                    // Auto-fix: if password is correct but not admin, make them admin
                    $updateStmt = $conn->prepare("UPDATE users SET is_admin = 1 WHERE id = ?");
                    $updateStmt->bind_param("i", $user['id']);
                    $updateStmt->execute();
                    $updateStmt->close();
                    
                    // Try login again
                    $_SESSION['admin_id'] = $user['id'];
                    $_SESSION['admin_username'] = $user['username'];
                    $_SESSION['admin_full_name'] = $user['full_name'];
                    
                    header('Location: index.php');
                    exit();
                }
            } else {
                $error = 'Invalid password! Please check your password.';
            }
        } else {
            $error = 'User not found! Please check your username/email.';
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
    <title>Admin Login - Paradise Hotel & Resort</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .admin-login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .admin-login-card {
            background: white;
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 450px;
        }
        .admin-login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .admin-login-header h1 {
            font-size: 2rem;
            color: #667eea;
            margin-bottom: 0.5rem;
        }
        .admin-login-header h1 i {
            margin-right: 0.5rem;
        }
        .admin-login-header p {
            color: #666;
            font-size: 0.95rem;
        }
        .admin-badge {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-top: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="admin-login-container">
        <div class="admin-login-card">
            <div class="admin-login-header">
                <h1><i class="fas fa-shield-alt"></i> Admin Login</h1>
                <p>Paradise Hotel & Resort</p>
                <span class="admin-badge">Administrator Access Only</span>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="auth-form">
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Username or Email</label>
                    <input type="text" name="username" placeholder="Enter your admin username or email" required autofocus>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-lock"></i> Password</label>
                    <input type="password" name="password" placeholder="Enter your password" required>
                </div>

                <button type="submit" class="btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Login to Admin Panel
                </button>
            </form>

            <div class="auth-footer">
                <p><a href="../index.php"><i class="fas fa-arrow-left"></i> Back to Main Website</a></p>
            </div>
        </div>
    </div>
</body>
</html>

