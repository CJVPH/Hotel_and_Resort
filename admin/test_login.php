<?php
// Test login page - bypasses normal checks
require_once '../config/database.php';
session_start();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password!';
    } else {
        $conn = getDBConnection();
        
        // Check if user exists
        $stmt = $conn->prepare("SELECT id, username, email, password, full_name, is_admin FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            // Debug info
            $debug = [
                'user_found' => true,
                'username' => $user['username'],
                'is_admin_value' => $user['is_admin'],
                'is_admin_type' => gettype($user['is_admin']),
                'password_match' => password_verify($password, $user['password'])
            ];
            
            if (password_verify($password, $user['password'])) {
                // Check admin status - be more flexible
                $isAdmin = ($user['is_admin'] == 1 || $user['is_admin'] === '1' || $user['is_admin'] === true);
                
                if ($isAdmin) {
                    $_SESSION['admin_id'] = $user['id'];
                    $_SESSION['admin_username'] = $user['username'];
                    $_SESSION['admin_full_name'] = $user['full_name'];
                    
                    $success = 'Login successful! Redirecting...';
                    header('Refresh: 2; url=index.php');
                } else {
                    $error = 'User found but is_admin = ' . var_export($user['is_admin'], true) . '. Setting to admin...';
                    // Auto-fix: set user as admin
                    $updateStmt = $conn->prepare("UPDATE users SET is_admin = 1 WHERE id = ?");
                    $updateStmt->bind_param("i", $user['id']);
                    $updateStmt->execute();
                    $updateStmt->close();
                    
                    // Try login again
                    $_SESSION['admin_id'] = $user['id'];
                    $_SESSION['admin_username'] = $user['username'];
                    $_SESSION['admin_full_name'] = $user['full_name'];
                    $success = 'User set as admin! Redirecting...';
                    header('Refresh: 2; url=index.php');
                }
            } else {
                $error = 'Password incorrect! Debug: ' . json_encode($debug);
            }
        } else {
            $error = 'User not found! Please check username/email.';
        }
        
        $stmt->close();
        $conn->close();
    }
}

// Get all users for debugging
$conn = getDBConnection();
$allUsers = $conn->query("SELECT id, username, email, is_admin FROM users ORDER BY id");
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Admin Login</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { padding: 2rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .test-card { background: white; padding: 2rem; border-radius: 15px; max-width: 600px; margin: 0 auto 1rem; box-shadow: 0 20px 60px rgba(0,0,0,0.3); }
        .debug-box { background: #f8f9fa; padding: 1rem; border-radius: 8px; margin: 1rem 0; font-family: monospace; font-size: 0.85rem; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { padding: 0.5rem; text-align: left; border-bottom: 1px solid #ddd; font-size: 0.9rem; }
        th { background: #f8f9fa; }
    </style>
</head>
<body>
    <div class="test-card">
        <h1 style="color: #667eea;"><i class="fas fa-bug"></i> Test Admin Login</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="auth-form">
            <div class="form-group">
                <label><i class="fas fa-user"></i> Username or Email</label>
                <input type="text" name="username" placeholder="Enter username or email" required autofocus>
            </div>
            
            <div class="form-group">
                <label><i class="fas fa-lock"></i> Password</label>
                <input type="password" name="password" placeholder="Enter password" required>
            </div>
            
            <button type="submit" class="btn-primary">
                <i class="fas fa-sign-in-alt"></i> Test Login
            </button>
        </form>
    </div>
    
    <div class="test-card">
        <h2>All Users in Database</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Is Admin</th>
            </tr>
            <?php while ($user = $allUsers->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $user['id']; ?></td>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td>
                        <?php if ($user['is_admin'] == 1): ?>
                            <span style="color: green;">✓ Yes</span>
                        <?php else: ?>
                            <span style="color: red;">✗ No (<?php echo $user['is_admin']; ?>)</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
    
    <div class="test-card">
        <h2>Quick Actions</h2>
        <a href="create_admin_account.php" class="btn-primary" style="display: inline-block; margin-right: 1rem;">
            <i class="fas fa-user-plus"></i> Create Admin Account
        </a>
        <a href="fix_admin.php" class="btn-primary" style="display: inline-block; margin-right: 1rem;">
            <i class="fas fa-tools"></i> Fix Admin Access
        </a>
        <a href="login.php" class="btn-primary" style="display: inline-block;">
            <i class="fas fa-sign-in-alt"></i> Normal Login Page
        </a>
    </div>
</body>
</html>

