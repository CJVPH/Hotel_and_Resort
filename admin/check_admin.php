<?php
require_once '../config/database.php';

$conn = getDBConnection();

// Check if admin account exists
$result = $conn->query("SELECT id, username, email, full_name, is_admin, password FROM users WHERE username = 'admin' OR email = 'admin@paradisehotel.com'");
$admin = $result->fetch_assoc();

// Check all admin users
$allAdmins = $conn->query("SELECT id, username, email, full_name, is_admin FROM users WHERE is_admin = 1");

// Test password
$testPassword = 'admin123';
$passwordMatch = false;
if ($admin && isset($admin['password'])) {
    $passwordMatch = password_verify($testPassword, $admin['password']);
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Account Check</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { padding: 2rem; background: #f5f5f5; }
        .check-card { background: white; padding: 2rem; border-radius: 10px; margin-bottom: 1rem; max-width: 800px; margin: 0 auto 1rem; }
        .status { padding: 0.5rem 1rem; border-radius: 5px; display: inline-block; margin: 0.5rem 0; }
        .status.success { background: #d4edda; color: #155724; }
        .status.error { background: #f8d7da; color: #721c24; }
        .status.warning { background: #fff3cd; color: #856404; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { padding: 0.75rem; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; }
        .btn { display: inline-block; padding: 0.75rem 1.5rem; background: #667eea; color: white; text-decoration: none; border-radius: 5px; margin-top: 1rem; }
    </style>
</head>
<body>
    <div class="check-card">
        <h1>Admin Account Diagnostic</h1>
        
        <h2>Admin Account Status</h2>
        <?php if ($admin): ?>
            <div class="status success">✓ Admin account found!</div>
            <table>
                <tr>
                    <th>Field</th>
                    <th>Value</th>
                </tr>
                <tr>
                    <td>ID</td>
                    <td><?php echo $admin['id']; ?></td>
                </tr>
                <tr>
                    <td>Username</td>
                    <td><?php echo htmlspecialchars($admin['username']); ?></td>
                </tr>
                <tr>
                    <td>Email</td>
                    <td><?php echo htmlspecialchars($admin['email']); ?></td>
                </tr>
                <tr>
                    <td>Full Name</td>
                    <td><?php echo htmlspecialchars($admin['full_name']); ?></td>
                </tr>
                <tr>
                    <td>Is Admin</td>
                    <td>
                        <?php if ($admin['is_admin'] == 1): ?>
                            <span class="status success">Yes (1)</span>
                        <?php else: ?>
                            <span class="status error">No (<?php echo $admin['is_admin']; ?>)</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td>Password Test</td>
                    <td>
                        <?php if ($passwordMatch): ?>
                            <span class="status success">✓ Password 'admin123' matches!</span>
                        <?php else: ?>
                            <span class="status error">✗ Password 'admin123' does NOT match!</span>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
        <?php else: ?>
            <div class="status error">✗ Admin account NOT found!</div>
            <p>You need to create an admin account first.</p>
        <?php endif; ?>
        
        <h2>All Admin Users</h2>
        <?php if ($allAdmins->num_rows > 0): ?>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Full Name</th>
                    <th>Is Admin</th>
                </tr>
                <?php while ($row = $allAdmins->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                        <td><?php echo $row['is_admin'] == 1 ? 'Yes' : 'No'; ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <div class="status warning">No admin users found in database.</div>
        <?php endif; ?>
        
        <div style="margin-top: 2rem;">
            <a href="setup_admin.php" class="btn">Create Admin Account</a>
            <a href="login.php" class="btn">Go to Login</a>
        </div>
    </div>
</body>
</html>

