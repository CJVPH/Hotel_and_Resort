<?php
require_once '../config/database.php';

$conn = getDBConnection();
$message = '';
$messageType = '';

// Handle room pricing update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_pricing'])) {
    // Expected fields: regular_2pax, regular_8pax, regular_20pax, deluxe_2pax, etc.
    $updates = [
        ['Regular', 2, $_POST['regular_2pax'] ?? null],
        ['Regular', 8, $_POST['regular_8pax'] ?? null],
        ['Regular', 20, $_POST['regular_20pax'] ?? null],
        ['Deluxe', 2, $_POST['deluxe_2pax'] ?? null],
        ['Deluxe', 8, $_POST['deluxe_8pax'] ?? null],
        ['Deluxe', 20, $_POST['deluxe_20pax'] ?? null],
        ['VIP', 2, $_POST['vip_2pax'] ?? null],
        ['VIP', 8, $_POST['vip_8pax'] ?? null],
        ['VIP', 20, $_POST['vip_20pax'] ?? null],
    ];

    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("INSERT INTO room_prices (room_type, pax_group, price) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE price=VALUES(price)");
        foreach ($updates as $u) {
            $room = $u[0];
            $pax = intval($u[1]);
            $price = is_numeric($u[2]) ? floatval($u[2]) : 0;
            $stmt->bind_param('sid', $room, $pax, $price);
            $stmt->execute();
        }
        $stmt->close();
        $conn->commit();
        $message = 'Room pricing updated successfully!';
        $messageType = 'success';
    } catch (Exception $e) {
        $conn->rollback();
        $message = 'Failed to update room pricing: ' . $e->getMessage();
        $messageType = 'error';
    }
}

// Fetch current prices to populate the form
$prices = [];
$res = $conn->query("SELECT room_type, pax_group, price FROM room_prices");
if ($res) {
    while ($r = $res->fetch_assoc()) {
        $prices[$r['room_type']][intval($r['pax_group'])] = $r['price'];
    }
    $res->free();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Management - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .admin-container { max-width: 1400px; margin: 2rem auto; padding: 0 2rem; }
        .admin-section { background: white; padding: 2rem; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); margin-bottom: 2rem; }
        .room-pricing-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; }
        .pricing-card { border: 2px solid #e0e0e0; border-radius: 10px; padding: 1.5rem; }
        .pricing-card h3 { color: #667eea; margin-bottom: 1rem; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; color: #555; }
        input[type="number"] { width: 100%; padding: 0.75rem; border: 1px solid #ccc; border-radius: 6px; }
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
            <h1><i class="fas fa-bed"></i> Room Management</h1>
            <p>Manage room types, pricing, and details</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType === 'success' ? 'success' : 'error'; ?>" style="margin-bottom: 2rem;">
                <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i> <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="admin-section">
            <h2><i class="fas fa-money-bill-wave"></i> Room Pricing</h2>
            <form method="POST">
                <div class="room-pricing-grid">
                    <!-- Regular Room -->
                    <div class="pricing-card">
                        <h3><i class="fas fa-home"></i> Regular Room</h3>
                        <div class="form-group">
                            <label>2 Pax Price (₱)</label>
                            <input type="number" name="regular_2pax" value="<?php echo isset($prices['Regular'][2]) ? htmlspecialchars($prices['Regular'][2]) : 1500; ?>" min="0" step="100">
                        </div>
                        <div class="form-group">
                            <label>4-8 Pax Price (₱)</label>
                            <input type="number" name="regular_8pax" value="<?php echo isset($prices['Regular'][8]) ? htmlspecialchars($prices['Regular'][8]) : 3000; ?>" min="0" step="100">
                        </div>
                        <div class="form-group">
                            <label>10-20 Pax Price (₱)</label>
                            <input type="number" name="regular_20pax" value="<?php echo isset($prices['Regular'][20]) ? htmlspecialchars($prices['Regular'][20]) : 6000; ?>" min="0" step="100">
                        </div>
                    </div>

                    <!-- Deluxe Room -->
                    <div class="pricing-card">
                        <h3><i class="fas fa-star"></i> Deluxe Room</h3>
                        <div class="form-group">
                            <label>2 Pax Price (₱)</label>
                            <input type="number" name="deluxe_2pax" value="<?php echo isset($prices['Deluxe'][2]) ? htmlspecialchars($prices['Deluxe'][2]) : 2500; ?>" min="0" step="100">
                        </div>
                        <div class="form-group">
                            <label>4-8 Pax Price (₱)</label>
                            <input type="number" name="deluxe_8pax" value="<?php echo isset($prices['Deluxe'][8]) ? htmlspecialchars($prices['Deluxe'][8]) : 4500; ?>" min="0" step="100">
                        </div>
                        <div class="form-group">
                            <label>10-20 Pax Price (₱)</label>
                            <input type="number" name="deluxe_20pax" value="<?php echo isset($prices['Deluxe'][20]) ? htmlspecialchars($prices['Deluxe'][20]) : 8500; ?>" min="0" step="100">
                        </div>
                    </div>

                    <!-- VIP Room -->
                    <div class="pricing-card">
                        <h3><i class="fas fa-crown"></i> VIP Suite</h3>
                        <div class="form-group">
                            <label>2 Pax Price (₱)</label>
                            <input type="number" name="vip_2pax" value="<?php echo isset($prices['VIP'][2]) ? htmlspecialchars($prices['VIP'][2]) : 4000; ?>" min="0" step="100">
                        </div>
                        <div class="form-group">
                            <label>4-8 Pax Price (₱)</label>
                            <input type="number" name="vip_8pax" value="<?php echo isset($prices['VIP'][8]) ? htmlspecialchars($prices['VIP'][8]) : 7000; ?>" min="0" step="100">
                        </div>
                        <div class="form-group">
                            <label>10-20 Pax Price (₱)</label>
                            <input type="number" name="vip_20pax" value="<?php echo isset($prices['VIP'][20]) ? htmlspecialchars($prices['VIP'][20]) : 12000; ?>" min="0" step="100">
                        </div>
                    </div>
                </div>
                <button type="submit" name="update_pricing" class="btn-primary" style="margin-top: 1.5rem;">
                    <i class="fas fa-save"></i> Update Pricing
                </button>
            </form>
        </div>

        <div class="admin-section">
            <h2><i class="fas fa-info-circle"></i> Room Information</h2>
            <p>Room descriptions, amenities, and inclusions are currently managed in the JavaScript file (<code>assets/js/script.js</code>).</p>
            <p>To modify room details:</p>
            <ul>
                <li>Edit the <code>roomDetails</code> object in <code>assets/js/script.js</code></li>
                <li>Update descriptions, amenities, and inclusions as needed</li>
                <li>Upload room images using the <a href="upload_images.php">Room Images</a> page</li>
            </ul>
        </div>
    </div>
</body>
</html>

