<?php
require_once 'config/database.php';

echo "<h2>Admin Account Reset Tool</h2>";

try {
    $conn = getDBConnection();
    
    // Check if admin user exists
    $stmt = $conn->prepare("SELECT id, username, email, is_admin FROM users WHERE username = 'admin'");
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        echo "<p>✅ Admin user found:</p>";
        echo "<ul>";
        echo "<li>ID: " . $admin['id'] . "</li>";
        echo "<li>Username: " . $admin['username'] . "</li>";
        echo "<li>Email: " . $admin['email'] . "</li>";
        echo "<li>Is Admin: " . ($admin['is_admin'] ? 'Yes' : 'No') . "</li>";
        echo "</ul>";
        
        // Reset password to admin123
        $newPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $updateStmt = $conn->prepare("UPDATE users SET password = ?, is_admin = 1 WHERE username = 'admin'");
        $updateStmt->bind_param("s", $newPassword);
        
        if ($updateStmt->execute()) {
            echo "<p>✅ Admin password reset to: <strong>admin123</strong></p>";
            echo "<p>✅ Admin privileges confirmed</p>";
        } else {
            echo "<p>❌ Failed to reset password: " . $updateStmt->error . "</p>";
        }
        $updateStmt->close();
        
    } else {
        echo "<p>❌ Admin user not found. Creating new admin user...</p>";
        
        // Create new admin user
        $password = password_hash('admin123', PASSWORD_DEFAULT);
        $insertStmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, is_admin) VALUES ('admin', 'admin@paradisehotel.com', ?, 'Administrator', 1)");
        $insertStmt->bind_param("s", $password);
        
        if ($insertStmt->execute()) {
            echo "<p>✅ New admin user created:</p>";
            echo "<ul>";
            echo "<li>Username: <strong>admin</strong></li>";
            echo "<li>Password: <strong>admin123</strong></li>";
            echo "<li>Email: admin@paradisehotel.com</li>";
            echo "</ul>";
        } else {
            echo "<p>❌ Failed to create admin user: " . $insertStmt->error . "</p>";
        }
        $insertStmt->close();
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    echo "<p>❌ Database error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>Admin Login Links:</h3>";
echo "<p><a href='admin_access.php'>Admin Access Page</a></p>";
echo "<p><a href='admin/login.php'>Direct Admin Login</a></p>";
echo "<p><a href='admin/index.php'>Admin Dashboard</a></p>";

echo "<hr>";
echo "<p><strong>Credentials to use:</strong></p>";
echo "<ul>";
echo "<li>Username: <code>admin</code></li>";
echo "<li>Password: <code>admin123</code></li>";
echo "</ul>";

echo "<hr>";
echo "<p><em>You can delete this file (admin_reset.php) after confirming admin access works.</em></p>";
?>

<style>
body { font-family: Arial, sans-serif; max-width: 800px; margin: 2rem auto; padding: 2rem; background: #f5f5f5; }
h2, h3 { color: #2C3E50; }
p { margin: 1rem 0; }
ul { background: white; padding: 1rem; border-radius: 5px; }
code { background: #e9ecef; padding: 0.2rem 0.4rem; border-radius: 3px; }
a { color: #C9A961; text-decoration: none; font-weight: bold; }
a:hover { text-decoration: underline; }
</style>