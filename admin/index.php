<?php
require_once 'auth.php';
require_once '../config/database.php';

$conn = getDBConnection();

// Get statistics
$stats = [];

// Total reservations
$result = $conn->query("SELECT COUNT(*) as total FROM reservations");
$stats['total_reservations'] = $result->fetch_assoc()['total'];

// Pending reservations
$result = $conn->query("SELECT COUNT(*) as total FROM reservations WHERE status = 'pending'");
$stats['pending_reservations'] = $result->fetch_assoc()['total'];

// Confirmed reservations
$result = $conn->query("SELECT COUNT(*) as total FROM reservations WHERE status = 'confirmed'");
$stats['confirmed_reservations'] = $result->fetch_assoc()['total'];

// Total users
$result = $conn->query("SELECT COUNT(*) as total FROM users");
$stats['total_users'] = $result->fetch_assoc()['total'];

// Total revenue
$result = $conn->query("SELECT SUM(price) as total FROM reservations WHERE status = 'confirmed'");
$revenue = $result->fetch_assoc()['total'];
$stats['total_revenue'] = $revenue ? number_format($revenue, 2) : '0.00';

// Recent reservations
$result = $conn->query("SELECT r.*, u.username, u.full_name FROM reservations r LEFT JOIN users u ON r.user_id = u.id ORDER BY r.created_at DESC LIMIT 5");
$recent_reservations = $result->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Paradise Hotel & Resort</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .admin-dashboard {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }
        .stat-icon.blue { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .stat-icon.green { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
        .stat-icon.orange { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .stat-icon.purple { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
        .stat-info h3 {
            margin: 0;
            font-size: 2rem;
            color: #333;
        }
        .stat-info p {
            margin: 0.25rem 0 0 0;
            color: #666;
            font-size: 0.9rem;
        }
        .admin-section {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        .admin-section h2 {
            color: #667eea;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .admin-menu {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .admin-menu-item {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            text-decoration: none;
            color: #333;
            transition: all 0.3s;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        .admin-menu-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }
        .admin-menu-item i {
            font-size: 2.5rem;
            color: #667eea;
            margin-bottom: 0.5rem;
        }
        .admin-menu-item h3 {
            margin: 0;
            font-size: 1.1rem;
        }
        .table-responsive {
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #667eea;
        }
        .badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .badge-pending { background: #fff3cd; color: #856404; }
        .badge-confirmed { background: #d4edda; color: #155724; }
        .badge-cancelled { background: #f8d7da; color: #721c24; }
        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn-view { background: #667eea; color: white; }
        .btn-view:hover { background: #5568d3; }
        .btn-edit { background: #28a745; color: white; }
        .btn-edit:hover { background: #218838; }
        .btn-delete { background: #dc3545; color: white; }
        .btn-delete:hover { background: #c82333; }
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
                <span class="nav-user"><i class="fas fa-user-circle"></i> Administrator</span>
                <a href="../index.php" class="nav-link"><i class="fas fa-home"></i> View Site</a>
            </div>
        </div>
    </nav>

    <div class="admin-dashboard">
        <div class="admin-header" style="background: white; padding: 2rem; border-radius: 15px; margin-bottom: 2rem; box-shadow: 0 5px 20px rgba(0,0,0,0.1);">
            <h1><i class="fas fa-tachometer-alt"></i> Admin Dashboard</h1>
            <p>Manage your hotel reservation system</p>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $stats['total_reservations']; ?></h3>
                    <p>Total Reservations</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon orange">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $stats['pending_reservations']; ?></h3>
                    <p>Pending Reservations</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $stats['confirmed_reservations']; ?></h3>
                    <p>Confirmed Reservations</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon purple">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $stats['total_users']; ?></h3>
                    <p>Total Users</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="stat-info">
                    <h3>₱<?php echo $stats['total_revenue']; ?></h3>
                    <p>Total Revenue</p>
                </div>
            </div>
        </div>

        <!-- Admin Menu -->
        <div class="admin-menu">
            <a href="reservations.php" class="admin-menu-item">
                <i class="fas fa-calendar-alt"></i>
                <h3>Manage Reservations</h3>
                <p>View and manage all bookings</p>
            </a>
            <a href="users.php" class="admin-menu-item">
                <i class="fas fa-users"></i>
                <h3>Manage Users</h3>
                <p>View and manage user accounts</p>
            </a>
            <a href="upload_images.php" class="admin-menu-item">
                <i class="fas fa-images"></i>
                <h3>Room Images</h3>
                <p>Upload and manage room photos</p>
            </a>
            <a href="rooms.php" class="admin-menu-item">
                <i class="fas fa-bed"></i>
                <h3>Room Management</h3>
                <p>Manage room types and pricing</p>
            </a>
            <a href="settings.php" class="admin-menu-item">
                <i class="fas fa-cog"></i>
                <h3>Settings</h3>
                <p>Website configuration</p>
            </a>
        </div>

        <!-- Recent Reservations -->
        <div class="admin-section">
            <h2><i class="fas fa-clock"></i> Recent Reservations</h2>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Guest Name</th>
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
                        <?php if (empty($recent_reservations)): ?>
                            <tr>
                                <td colspan="9" style="text-align: center; padding: 2rem; color: #999;">No reservations yet</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recent_reservations as $reservation): ?>
                                <tr>
                                    <td>#<?php echo str_pad($reservation['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                    <td><?php echo htmlspecialchars($reservation['guest_name']); ?></td>
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
                                        <a href="reservations.php?id=<?php echo $reservation['id']; ?>" class="btn-sm btn-view">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div style="margin-top: 1rem; text-align: right;">
                <a href="reservations.php" class="btn-primary">
                    <i class="fas fa-list"></i> View All Reservations
                </a>
            </div>
        </div>
    </div>
</body>
</html>

