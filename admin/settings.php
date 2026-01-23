<?php
require_once '../config/database.php';

$message = '';
$messageType = '';

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $message = 'Settings updated successfully! (Note: This is a demo. In production, settings would be stored in database)';
    $messageType = 'success';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .admin-container { max-width: 1000px; margin: 2rem auto; padding: 0 2rem; }
        .admin-section { background: white; padding: 2rem; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); margin-bottom: 2rem; }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; color: #555; }
        input[type="text"], input[type="email"], textarea { width: 100%; padding: 0.75rem; border: 1px solid #ccc; border-radius: 6px; }
        textarea { min-height: 100px; resize: vertical; }
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
            <h1><i class="fas fa-cog"></i> Settings</h1>
            <p>Configure website settings</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType === 'success' ? 'success' : 'error'; ?>" style="margin-bottom: 2rem;">
                <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i> <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="admin-section">
                <h2><i class="fas fa-building"></i> Hotel Information</h2>
                <div class="form-group">
                    <label>Hotel Name</label>
                    <input type="text" name="hotel_name" value="Paradise Hotel & Resort">
                </div>
                <div class="form-group">
                    <label>Contact Email</label>
                    <input type="email" name="contact_email" value="info@paradisehotel.com">
                </div>
                <div class="form-group">
                    <label>Contact Phone</label>
                    <input type="text" name="contact_phone" value="+63 123 456 7890">
                </div>
                <div class="form-group">
                    <label>Address</label>
                    <textarea name="address">123 Paradise Street, Resort City, Philippines</textarea>
                </div>
            </div>

            <div class="admin-section">
                <h2><i class="fas fa-globe"></i> Website Settings</h2>
                <div class="form-group">
                    <label>Site Title</label>
                    <input type="text" name="site_title" value="Paradise Hotel & Resort - Book Your Stay">
                </div>
                <div class="form-group">
                    <label>Meta Description</label>
                    <textarea name="meta_description">Experience luxury and comfort at Paradise Hotel & Resort. Book your stay today!</textarea>
                </div>
            </div>

            <div class="admin-section">
                <h2><i class="fas fa-bell"></i> Notification Settings</h2>
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="email_notifications" checked> Enable Email Notifications
                    </label>
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="auto_confirm" checked> Auto-confirm Reservations
                    </label>
                </div>
            </div>

            <button type="submit" class="btn-primary">
                <i class="fas fa-save"></i> Save Settings
            </button>
        </form>
    </div>
</body>
</html>

