<?php
require_once 'auth.php';
require_once '../config/database.php';

$conn = getDBConnection();
$message = '';
$messageType = '';
// Handle create admin submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_admin'])) {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    if ($username === '' || $email === '' || $full_name === '' || $password === '') {
        $message = 'Please fill out all required fields.';
        $messageType = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please provide a valid email address.';
        $messageType = 'error';
    } elseif (strlen($password) < 6) {
        $message = 'Password must be at least 6 characters.';
        $messageType = 'error';
    } elseif ($password !== $confirm) {
        $message = 'Passwords do not match.';
        $messageType = 'error';
    } else {
        // Check uniqueness
        $chk = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1");
        $chk->bind_param('ss', $username, $email);
        $chk->execute();
        $res = $chk->get_result();
        if ($res && $res->num_rows > 0) {
            $message = 'A user with that username or email already exists.';
            $messageType = 'error';
            $chk->close();
        } else {
            $chk->close();
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $is_admin = 1;
            $ins = $conn->prepare("INSERT INTO users (username, email, password, full_name, is_admin) VALUES (?, ?, ?, ?, ?)");
            $ins->bind_param('ssssi', $username, $email, $hashed, $full_name, $is_admin);
            if ($ins->execute()) {
                $message = 'Admin account created successfully.';
                $messageType = 'success';
            } else {
                $message = 'Error creating admin: ' . $conn->error;
                $messageType = 'error';
            }
            $ins->close();
        }
    }
}

// Note: deletions are handled by admin/delete_user.php via POST

// Get all users with reservation count
$sql = "SELECT u.*, COUNT(r.id) as reservation_count FROM users u LEFT JOIN reservations r ON u.id = r.user_id GROUP BY u.id ORDER BY u.created_at DESC";
$result = $conn->query($sql);
$users = $result->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .admin-container { max-width: 1400px; margin: 2rem auto; padding: 0 2rem; }
        .admin-section { background: white; padding: 2rem; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); margin-bottom: 2rem; }
        .table-responsive { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 1rem; text-align: left; border-bottom: 1px solid #e0e0e0; }
        th { background: #f8f9fa; font-weight: 600; color: #667eea; }
        .btn-sm { padding: 0.5rem 1rem; font-size: 0.85rem; border-radius: 6px; border: none; cursor: pointer; text-decoration: none; display: inline-block; margin: 0.25rem; }
        .btn-delete { background: #dc3545; color: white; }
        .btn-delete:hover { background: #c82333; }
        .badge { padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.85rem; font-weight: 600; background: #667eea; color: white; }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <i class="fas fa-hotel"></i>
                <span>Paradise Hotel & Resort - Admin</span>
            </div>
            <div class="nav-menu">
                <a href="index.php" class="nav-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <span class="nav-user"><i class="fas fa-user-circle"></i> Administrator</span>
                <a href="../index.php" class="nav-link"><i class="fas fa-home"></i> View Site</a>
            </div>
        </div>
    </nav>

    <div class="admin-container">
        <div class="admin-header" style="background: white; padding: 2rem; border-radius: 15px; margin-bottom: 2rem; box-shadow: 0 5px 20px rgba(0,0,0,0.1);">
            <h1><i class="fas fa-users"></i> Manage Users</h1>
        </div>
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType === 'success' ? 'success' : 'error'; ?>" style="margin-bottom: 2rem;">
                <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i> <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="admin-section" style="margin-bottom:1.5rem;">
            <h2><i class="fas fa-user-shield"></i> Create Admin Account</h2>
            <form method="POST" style="max-width:700px;">
                <input type="hidden" name="create_admin" value="1">
                <div style="display:flex; gap:1rem; flex-wrap:wrap;">
                    <div style="flex:1; min-width:200px;">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div style="flex:1; min-width:200px;">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div style="flex:1; min-width:200px;">
                        <label>Full Name</label>
                        <input type="text" name="full_name" class="form-control" required>
                    </div>
                    <div style="flex:1; min-width:200px;">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div style="flex:1; min-width:200px;">
                        <label>Confirm Password</label>
                        <input type="password" name="confirm" class="form-control" required>
                    </div>
                </div>
                <div style="margin-top:0.75rem;">
                    <button type="submit" class="btn-primary"><i class="fas fa-user-plus"></i> Create Admin</button>
                </div>
            </form>
        </div>

        <div class="admin-section">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Reservations</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 2rem; color: #999;">No users found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td>#<?php echo str_pad($user['id'], 4, '0', STR_PAD_LEFT); ?></td>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <span class="badge"><?php echo $user['reservation_count']; ?></span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <a href="view_user.php?id=<?php echo $user['id']; ?>" class="btn-sm" style="background:#6c757d;color:#fff;text-decoration:none;border-radius:6px;padding:0.4rem 0.75rem;">View</a>
                                        <a href="change_user_password.php?id=<?php echo $user['id']; ?>" class="btn-sm" style="background:#28a745;color:#fff;text-decoration:none;border-radius:6px;padding:0.4rem 0.75rem;">Change Password</a>
                                        <?php
                                            // Do not allow deleting admin accounts from UI
                                            $isAdminUser = isset($user['is_admin']) && $user['is_admin'] == 1;
                                            $currentAdminId = $_SESSION['admin_id'] ?? null;
                                        ?>
                                        <?php if (!$isAdminUser && $currentAdminId && $currentAdminId != $user['id']): ?>
                                            <form method="POST" action="delete_user.php" style="display:inline;">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <button type="submit" class="btn-sm btn-delete" onclick="return confirm('Are you sure you want to delete this user? This will also delete all their reservations.');"><i class="fas fa-trash"></i> Delete</button>
                                            </form>
                                        <?php elseif ($isAdminUser): ?>
                                            <span style="color: #999; margin-left:0.5rem;">Admin Account</span>
                                        <?php else: ?>
                                            <span style="color: #999; margin-left:0.5rem;">Current Admin</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>

