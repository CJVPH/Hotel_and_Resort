<?php
require_once 'config/database.php';
require_once 'config/auth.php';
requireLogin();

$reservation_id = $_GET['id'] ?? null;

if (!$reservation_id) {
    header('Location: index.php');
    exit();
}

$conn = getDBConnection();
$stmt = $conn->prepare("SELECT * FROM reservations WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $reservation_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header('Location: index.php');
    exit();
}

$reservation = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation Confirmed - Paradise Hotel & Resort</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <i class="fas fa-hotel"></i>
                <span>Paradise Hotel & Resort</span>
            </div>
            <div class="nav-menu">
                <span class="nav-user"><i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username']); ?></span>
                <a href="index.php" class="nav-link"><i class="fas fa-home"></i> Home</a>
                <a href="logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </nav>

    <div class="confirmation-container">
        <div class="confirmation-card">
            <div class="confirmation-header success">
                <i class="fas fa-check-circle"></i>
                <h1>Reservation Confirmed!</h1>
                <p>Your booking has been successfully processed</p>
            </div>

            <div class="confirmation-details">
                <div class="detail-section">
                    <h3><i class="fas fa-info-circle"></i> Reservation Details</h3>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <span class="detail-label">Reservation ID:</span>
                            <span class="detail-value">#<?php echo str_pad($reservation['id'], 6, '0', STR_PAD_LEFT); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Guest Name:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($reservation['guest_name']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Check-in Date:</span>
                            <span class="detail-value"><?php echo date('F d, Y', strtotime($reservation['checkin_date'])); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Check-out Date:</span>
                            <span class="detail-value"><?php echo date('F d, Y', strtotime($reservation['checkout_date'])); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Room Type:</span>
                            <span class="detail-value room-badge"><?php echo htmlspecialchars($reservation['room_type']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Number of Guests:</span>
                            <span class="detail-value"><?php echo $reservation['guests']; ?> pax</span>
                        </div>
                        <div class="detail-item full-width">
                            <span class="detail-label">Total Price:</span>
                            <span class="detail-value price-highlight">₱<?php echo number_format($reservation['price'], 2); ?></span>
                        </div>
                        <div class="detail-item full-width">
                            <span class="detail-label">Status:</span>
                            <span class="detail-value status-badge status-<?php echo $reservation['status']; ?>"><?php echo ucfirst($reservation['status']); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="confirmation-actions">
                <a href="index.php" class="btn-primary">
                    <i class="fas fa-calendar-plus"></i> Book Another Room
                </a>
                <button onclick="window.print()" class="btn-secondary">
                    <i class="fas fa-print"></i> Print Confirmation
                </button>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Paradise Hotel & Resort. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>

