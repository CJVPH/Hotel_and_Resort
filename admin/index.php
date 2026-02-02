<?php
require_once '../config/database.php';
require_once '../config/auth.php';

// Require admin login
requireAdminLogin();

// Get dashboard statistics
try {
    $conn = getDBConnection();
    
    // Total reservations
    $totalReservations = 0;
    $result = $conn->query("SELECT COUNT(*) as count FROM reservations");
    if ($result) {
        $row = $result->fetch_assoc();
        $totalReservations = $row['count'];
    }
    
    // Pending reservations
    $pendingReservations = 0;
    $result = $conn->query("SELECT COUNT(*) as count FROM reservations WHERE status = 'pending'");
    if ($result) {
        $row = $result->fetch_assoc();
        $pendingReservations = $row['count'];
    }
    
    // Confirmed reservations
    $confirmedReservations = 0;
    $result = $conn->query("SELECT COUNT(*) as count FROM reservations WHERE status = 'confirmed'");
    if ($result) {
        $row = $result->fetch_assoc();
        $confirmedReservations = $row['count'];
    }
    
    // Total revenue
    $totalRevenue = 0;
    $result = $conn->query("SELECT SUM(payment_amount) as total FROM reservations WHERE payment_status IN ('completed', 'pending')");
    if ($result) {
        $row = $result->fetch_assoc();
        $totalRevenue = $row['total'] ?? 0;
    }
    
    // Monthly revenue (current month)
    $monthlyRevenue = 0;
    $currentMonth = date('Y-m');
    $result = $conn->query("SELECT SUM(payment_amount) as total FROM reservations WHERE payment_status IN ('completed', 'pending') AND DATE_FORMAT(created_at, '%Y-%m') = '$currentMonth'");
    if ($result) {
        $row = $result->fetch_assoc();
        $monthlyRevenue = $row['total'] ?? 0;
    }
    
    // Today's reservations
    $todayReservations = 0;
    $today = date('Y-m-d');
    $result = $conn->query("SELECT COUNT(*) as count FROM reservations WHERE DATE(created_at) = '$today'");
    if ($result) {
        $row = $result->fetch_assoc();
        $todayReservations = $row['count'];
    }
    
    // Cancelled reservations
    $cancelledReservations = 0;
    $result = $conn->query("SELECT COUNT(*) as count FROM reservations WHERE status = 'cancelled'");
    if ($result) {
        $row = $result->fetch_assoc();
        $cancelledReservations = $row['count'];
    }
    
    // Average booking value
    $averageBookingValue = 0;
    $result = $conn->query("SELECT AVG(payment_amount) as average FROM reservations WHERE payment_status IN ('completed', 'pending')");
    if ($result) {
        $row = $result->fetch_assoc();
        $averageBookingValue = $row['average'] ?? 0;
    }
    
    // Most popular room type
    $popularRoomType = 'N/A';
    $result = $conn->query("SELECT room_type, COUNT(*) as count FROM reservations GROUP BY room_type ORDER BY count DESC LIMIT 1");
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $popularRoomType = $row['room_type'];
    }
    
    // Total users
    $totalUsers = 0;
    $result = $conn->query("SELECT COUNT(*) as count FROM users WHERE is_admin = 0");
    if ($result) {
        $row = $result->fetch_assoc();
        $totalUsers = $row['count'];
    }
    
    // Recent reservations
    $recentReservations = [];
    $result = $conn->query("SELECT r.*, u.username FROM reservations r LEFT JOIN users u ON r.user_id = u.id ORDER BY r.created_at DESC LIMIT 5");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $recentReservations[] = $row;
        }
    }
    
    $conn->close();
    
} catch (Exception $e) {
    error_log("Admin dashboard error: " . $e->getMessage());
    $totalReservations = $pendingReservations = $confirmedReservations = $totalRevenue = $totalUsers = 0;
    $monthlyRevenue = $todayReservations = $cancelledReservations = $averageBookingValue = 0;
    $popularRoomType = 'N/A';
    $recentReservations = [];
}

// Set page variables for template
$pageTitle = 'Dashboard';
$currentPage = 'dashboard';
?>
<?php include 'template_header.php'; ?>

<!-- Page Header -->
<div class="page-header">
    <h1><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
    <p>Welcome to Paradise Hotel & Resort Administration Panel</p>
</div>

<!-- Statistics Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-calendar-check"></i>
        </div>
        <div class="stat-number"><?php echo number_format($totalReservations); ?></div>
        <div class="stat-label">Total Reservations</div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-number"><?php echo number_format($pendingReservations); ?></div>
        <div class="stat-label">Pending Reservations</div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-number"><?php echo number_format($confirmedReservations); ?></div>
        <div class="stat-label">Confirmed Reservations</div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-number"><?php echo number_format($totalUsers); ?></div>
        <div class="stat-label">Registered Users</div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-money-bill-wave"></i>
        </div>
        <div class="stat-number">₱<?php echo number_format($totalRevenue, 2); ?></div>
        <div class="stat-label">Total Revenue</div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-chart-line"></i>
        </div>
        <div class="stat-number">₱<?php echo number_format($monthlyRevenue, 2); ?></div>
        <div class="stat-label">Monthly Revenue</div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-calendar-day"></i>
        </div>
        <div class="stat-number"><?php echo number_format($todayReservations); ?></div>
        <div class="stat-label">Today's Bookings</div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-calculator"></i>
        </div>
        <div class="stat-number">₱<?php echo number_format($averageBookingValue, 2); ?></div>
        <div class="stat-label">Average Booking Value</div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-times-circle"></i>
        </div>
        <div class="stat-number"><?php echo number_format($cancelledReservations); ?></div>
        <div class="stat-label">Cancelled Reservations</div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-star"></i>
        </div>
        <div class="stat-text"><?php echo htmlspecialchars($popularRoomType); ?></div>
        <div class="stat-label">Most Popular Room</div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-percentage"></i>
        </div>
        <div class="stat-number">
            <?php 
            $successRate = $totalReservations > 0 ? (($confirmedReservations / $totalReservations) * 100) : 0;
            echo number_format($successRate, 1) . '%';
            ?>
        </div>
        <div class="stat-label">Booking Success Rate</div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-server"></i>
        </div>
        <div class="stat-status online">
            <i class="fas fa-circle"></i> Online
        </div>
        <div class="stat-label">System Status</div>
    </div>
</div>

            <!-- Recent Reservations -->
            <div class="admin-section">
                <div class="section-header">
                    <h2><i class="fas fa-history"></i> Recent Reservations</h2>
                    <a href="reservations.php" class="btn btn-primary">View All</a>
                </div>
                
                <?php if (!empty($recentReservations)): ?>
                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Guest Name</th>
                                <th>Room</th>
                                <th>Check-in</th>
                                <th>Check-out</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentReservations as $reservation): ?>
                            <tr>
                                <td>#<?php echo $reservation['id']; ?></td>
                                <td><?php echo htmlspecialchars($reservation['guest_name']); ?></td>
                                <td><?php echo htmlspecialchars($reservation['room_type']); ?></td>
                                <td><?php echo date('M j, Y', strtotime($reservation['checkin_date'])); ?></td>
                                <td><?php echo date('M j, Y', strtotime($reservation['checkout_date'])); ?></td>
                                <td>₱<?php echo number_format($reservation['price'], 2); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $reservation['status']; ?>">
                                        <?php echo ucfirst($reservation['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($reservation['created_at'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-calendar-times"></i>
                    <h3>No Reservations Yet</h3>
                    <p>Reservations will appear here once customers start booking.</p>
                </div>
                <?php endif; ?>
            </div>

<?php include 'template_footer.php'; ?>