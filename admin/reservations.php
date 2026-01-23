<?php
require_once '../config/database.php';

$conn = getDBConnection();
$message = '';
$messageType = '';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $reservationId = $_POST['reservation_id'];
    $newStatus = $_POST['status'];
    
    $stmt = $conn->prepare("UPDATE reservations SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $newStatus, $reservationId);
    
    if ($stmt->execute()) {
        $message = 'Reservation status updated successfully!';
        $messageType = 'success';
    } else {
        $message = 'Error updating reservation status.';
        $messageType = 'error';
    }
    $stmt->close();
}

// Handle deletion
if (isset($_GET['delete'])) {
    $reservationId = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM reservations WHERE id = ?");
    $stmt->bind_param("i", $reservationId);
    
    if ($stmt->execute()) {
        $message = 'Reservation deleted successfully!';
        $messageType = 'success';
    } else {
        $message = 'Error deleting reservation.';
        $messageType = 'error';
    }
    $stmt->close();
}

// Get filter
$filter = $_GET['filter'] ?? 'all';
$statusFilter = '';

if ($filter == 'pending') {
    $statusFilter = "WHERE r.status = 'pending'";
} elseif ($filter == 'confirmed') {
    $statusFilter = "WHERE r.status = 'confirmed'";
} elseif ($filter == 'cancelled') {
    $statusFilter = "WHERE r.status = 'cancelled'";
}

// Get all reservations
$sql = "SELECT r.*, u.username, u.full_name, u.email FROM reservations r LEFT JOIN users u ON r.user_id = u.id $statusFilter ORDER BY r.created_at DESC";
$result = $conn->query($sql);
$reservations = $result->fetch_all(MYSQLI_ASSOC);

// Get single reservation details if ID provided
$reservationDetails = null;
if (isset($_GET['id'])) {
    $reservationId = $_GET['id'];
    $stmt = $conn->prepare("SELECT r.*, u.username, u.full_name, u.email FROM reservations r LEFT JOIN users u ON r.user_id = u.id WHERE r.id = ?");
    $stmt->bind_param("i", $reservationId);
    $stmt->execute();
    $result = $stmt->get_result();
    $reservationDetails = $result->fetch_assoc();
    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Reservations - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .admin-container { max-width: 1400px; margin: 2rem auto; padding: 0 2rem; }
        .admin-section { background: white; padding: 2rem; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); margin-bottom: 2rem; }
        .filter-tabs { display: flex; gap: 1rem; margin-bottom: 1.5rem; flex-wrap: wrap; }
        .filter-tab { padding: 0.75rem 1.5rem; background: #f8f9fa; border: none; border-radius: 8px; cursor: pointer; text-decoration: none; color: #333; font-weight: 500; transition: all 0.3s; }
        .filter-tab:hover { background: #e9ecef; }
        .filter-tab.active { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .table-responsive { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 1rem; text-align: left; border-bottom: 1px solid #e0e0e0; }
        th { background: #f8f9fa; font-weight: 600; color: #667eea; }
        .badge { padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.85rem; font-weight: 600; }
        .badge-pending { background: #fff3cd; color: #856404; }
        .badge-confirmed { background: #d4edda; color: #155724; }
        .badge-cancelled { background: #f8d7da; color: #721c24; }
        .btn-sm { padding: 0.5rem 1rem; font-size: 0.85rem; border-radius: 6px; border: none; cursor: pointer; text-decoration: none; display: inline-block; margin: 0.25rem; }
        .btn-view { background: #667eea; color: white; }
        .btn-edit { background: #28a745; color: white; }
        .btn-delete { background: #dc3545; color: white; }
        .detail-card { background: #f8f9fa; padding: 1.5rem; border-radius: 10px; margin-bottom: 1rem; }
        .detail-row { display: grid; grid-template-columns: 150px 1fr; gap: 1rem; padding: 0.75rem 0; border-bottom: 1px solid #e0e0e0; }
        .detail-row:last-child { border-bottom: none; }
        .detail-label { font-weight: 600; color: #667eea; }
        select { padding: 0.5rem; border-radius: 6px; border: 1px solid #ccc; }
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
            <h1><i class="fas fa-calendar-alt"></i> Manage Reservations</h1>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType === 'success' ? 'success' : 'error'; ?>" style="margin-bottom: 2rem;">
                <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i> <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($reservationDetails): ?>
            <!-- Reservation Details -->
            <div class="admin-section">
                <h2><i class="fas fa-info-circle"></i> Reservation Details</h2>
                <div class="detail-card">
                    <div class="detail-row">
                        <div class="detail-label">Reservation ID:</div>
                        <div>#<?php echo str_pad($reservationDetails['id'], 6, '0', STR_PAD_LEFT); ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Guest Name:</div>
                        <div><?php echo htmlspecialchars($reservationDetails['guest_name']); ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">User:</div>
                        <div><?php echo htmlspecialchars($reservationDetails['full_name'] ?? $reservationDetails['username']); ?> (<?php echo htmlspecialchars($reservationDetails['email']); ?>)</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Room Type:</div>
                        <div><?php echo htmlspecialchars($reservationDetails['room_type']); ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Check-in:</div>
                        <div><?php echo date('F d, Y', strtotime($reservationDetails['checkin_date'])); ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Check-out:</div>
                        <div><?php echo date('F d, Y', strtotime($reservationDetails['checkout_date'])); ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Guests:</div>
                        <div><?php echo $reservationDetails['guests']; ?> pax</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Price:</div>
                        <div>₱<?php echo number_format($reservationDetails['price'], 2); ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Status:</div>
                        <div>
                            <span class="badge badge-<?php echo strtolower($reservationDetails['status']); ?>">
                                <?php echo ucfirst($reservationDetails['status']); ?>
                            </span>
                        </div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Created:</div>
                        <div><?php echo date('F d, Y H:i', strtotime($reservationDetails['created_at'])); ?></div>
                    </div>
                </div>

                <form method="POST" style="margin-top: 1.5rem;">
                    <input type="hidden" name="reservation_id" value="<?php echo $reservationDetails['id']; ?>">
                    <div class="form-group">
                        <label>Update Status:</label>
                        <select name="status" required>
                            <option value="pending" <?php echo $reservationDetails['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="confirmed" <?php echo $reservationDetails['status'] == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                            <option value="cancelled" <?php echo $reservationDetails['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
                    <button type="submit" name="update_status" class="btn-primary">
                        <i class="fas fa-save"></i> Update Status
                    </button>
                    <a href="reservations.php" class="btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </form>
            </div>
        <?php else: ?>
            <!-- Reservations List -->
            <div class="admin-section">
                <div class="filter-tabs">
                    <a href="?filter=all" class="filter-tab <?php echo $filter == 'all' ? 'active' : ''; ?>">All</a>
                    <a href="?filter=pending" class="filter-tab <?php echo $filter == 'pending' ? 'active' : ''; ?>">Pending</a>
                    <a href="?filter=confirmed" class="filter-tab <?php echo $filter == 'confirmed' ? 'active' : ''; ?>">Confirmed</a>
                    <a href="?filter=cancelled" class="filter-tab <?php echo $filter == 'cancelled' ? 'active' : ''; ?>">Cancelled</a>
                </div>

                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Guest Name</th>
                                <th>User</th>
                                <th>Room Type</th>
                                <th>Check-in</th>
                                <th>Check-out</th>
                                <th>Guests</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($reservations)): ?>
                                <tr>
                                    <td colspan="10" style="text-align: center; padding: 2rem; color: #999;">No reservations found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($reservations as $reservation): ?>
                                    <tr>
                                        <td>#<?php echo str_pad($reservation['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                        <td><?php echo htmlspecialchars($reservation['guest_name']); ?></td>
                                        <td><?php echo htmlspecialchars($reservation['full_name'] ?? $reservation['username'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($reservation['room_type']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($reservation['checkin_date'])); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($reservation['checkout_date'])); ?></td>
                                        <td><?php echo $reservation['guests']; ?> pax</td>
                                        <td>₱<?php echo number_format($reservation['price'], 2); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo strtolower($reservation['status']); ?>">
                                                <?php echo ucfirst($reservation['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="?id=<?php echo $reservation['id']; ?>" class="btn-sm btn-view">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                            <a href="?delete=<?php echo $reservation['id']; ?>" class="btn-sm btn-delete" onclick="return confirm('Are you sure you want to delete this reservation?');">
                                                <i class="fas fa-trash"></i> Delete
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

