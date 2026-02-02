<?php
require_once 'config/database.php';
require_once 'config/auth.php';

// Get reservation ID first
$reservationId = intval($_GET['reservation_id'] ?? 0);

if ($reservationId <= 0) {
    header('Location: booking.php?error=Invalid reservation');
    exit();
}

// If not logged in, redirect to login with reservation ID
if (!isLoggedIn()) {
    header('Location: login.php?redirect=payment.php&reservation_id=' . $reservationId . '&message=' . urlencode('Please log in to complete your payment'));
    exit();
}

// Get reservation details
try {
    $conn = getDBConnection();
    
    // For logged-in users, check if reservation belongs to them OR if it's a guest reservation (user_id is NULL)
    $stmt = $conn->prepare("SELECT * FROM reservations WHERE id = ? AND (user_id = ? OR user_id IS NULL)");
    $userId = getUserId();
    $stmt->bind_param("ii", $reservationId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        header('Location: booking.php?error=Reservation not found');
        exit();
    }
    
    $reservation = $result->fetch_assoc();
    
    // If this was a guest reservation, update it to associate with the logged-in user
    if ($reservation['user_id'] === null) {
        $updateStmt = $conn->prepare("UPDATE reservations SET user_id = ? WHERE id = ?");
        $updateStmt->bind_param("ii", $userId, $reservationId);
        $updateStmt->execute();
        $updateStmt->close();
        $reservation['user_id'] = $userId;
    }
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    error_log("Payment page error: " . $e->getMessage());
    header('Location: booking.php?error=Database error');
    exit();
}

$totalAmount = $reservation['price'];
$halfAmount = $totalAmount / 2;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Options - Paradise Hotel & Resort</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/booking.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="booking-page">
    <!-- Header -->
    <header class="booking-header">
        <div class="header-container">
            <div class="header-left">
                <a href="booking.php" class="back-link">
                    <i class="fas fa-arrow-left"></i>
                    <span>Back to Booking</span>
                </a>
            </div>
            <div class="header-center">
                <div class="hotel-logo">
                    <i class="fas fa-hotel"></i>
                    <span>Paradise Hotel & Resort</span>
                </div>
            </div>
            <div class="header-right">
                <div class="user-info">
                    <i class="fas fa-user-circle"></i>
                    <span><?php echo htmlspecialchars(getFullName() ?? getUsername()); ?></span>
                </div>
            </div>
        </div>
    </header>

    <div class="payment-container">
        <div class="payment-form-section">
            <div class="booking-card">
                <div class="booking-header">
                    <h1><i class="fas fa-credit-card"></i> Payment Options</h1>
                    <p>Choose your preferred payment amount</p>
                </div>

                <!-- Reservation Summary -->
                <div class="form-section">
                    <h3><i class="fas fa-receipt"></i> Reservation Summary</h3>
                    <div class="reservation-summary">
                        <div class="summary-row">
                            <span>Guest Name:</span>
                            <span><?php echo htmlspecialchars($reservation['guest_name']); ?></span>
                        </div>
                        <div class="summary-row">
                            <span>Room:</span>
                            <span><?php echo htmlspecialchars($reservation['room_type']); ?></span>
                        </div>
                        <div class="summary-row">
                            <span>Check-in:</span>
                            <span><?php echo date('M d, Y', strtotime($reservation['checkin_date'])); ?></span>
                        </div>
                        <div class="summary-row">
                            <span>Check-out:</span>
                            <span><?php echo date('M d, Y', strtotime($reservation['checkout_date'])); ?></span>
                        </div>
                        <div class="summary-row">
                            <span>Guests:</span>
                            <span><?php echo $reservation['guests']; ?> guests</span>
                        </div>
                        <div class="summary-row total">
                            <span>Total Amount:</span>
                            <span>₱<?php echo number_format($totalAmount, 2); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Payment Options -->
                <div class="form-section">
                    <h3><i class="fas fa-money-bill-wave"></i> Select Payment Amount</h3>
                    <div class="payment-options">
                        <div class="payment-option">
                            <div class="payment-card">
                                <div class="payment-header">
                                    <h4><i class="fas fa-percentage"></i> 50% Deposit</h4>
                                    <div class="payment-amount">₱<?php echo number_format($halfAmount, 2); ?></div>
                                </div>
                                <div class="payment-details">
                                    <p>Pay 50% now and the remaining 50% upon check-in</p>
                                    <ul>
                                        <li>Secure your reservation</li>
                                        <li>Flexible payment schedule</li>
                                        <li>Pay balance at hotel</li>
                                    </ul>
                                </div>
                                <form action="process_payment.php" method="POST">
                                    <input type="hidden" name="reservation_id" value="<?php echo $reservationId; ?>">
                                    <input type="hidden" name="payment_percentage" value="50">
                                    <input type="hidden" name="payment_amount" value="<?php echo $halfAmount; ?>">
                                    <button type="submit" class="btn-payment">
                                        <i class="fas fa-credit-card"></i> Pay 50% Now
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="payment-option">
                            <div class="payment-card">
                                <div class="payment-header">
                                    <h4><i class="fas fa-check-circle"></i> Full Payment</h4>
                                    <div class="payment-amount">₱<?php echo number_format($totalAmount, 2); ?></div>
                                </div>
                                <div class="payment-details">
                                    <p>Pay the full amount now and enjoy your stay worry-free</p>
                                    <ul>
                                        <li>Complete payment</li>
                                        <li>No additional charges</li>
                                        <li>Express check-in</li>
                                    </ul>
                                </div>
                                <form action="process_payment.php" method="POST">
                                    <input type="hidden" name="reservation_id" value="<?php echo $reservationId; ?>">
                                    <input type="hidden" name="payment_percentage" value="100">
                                    <input type="hidden" name="payment_amount" value="<?php echo $totalAmount; ?>">
                                    <button type="submit" class="btn-payment btn-full">
                                        <i class="fas fa-credit-card"></i> Pay Full Amount
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
    /* Payment Container Centering */
    .payment-container {
        display: flex;
        justify-content: center;
        align-items: flex-start;
        min-height: calc(100vh - 120px);
        padding: 2rem;
    }

    .payment-form-section {
        width: 100%;
        max-width: 900px;
        margin: 0 auto;
    }

    .reservation-summary {
        background: white;
        padding: 1.5rem;
        border-radius: 10px;
        border: 1px solid #e0e0e0;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 0;
        border-bottom: 1px solid #f0f0f0;
    }

    .summary-row:last-child {
        border-bottom: none;
    }

    .summary-row.total {
        font-size: 1.2rem;
        font-weight: 700;
        color: #2C3E50;
        border-top: 2px solid #C9A961;
        margin-top: 0.5rem;
        padding-top: 1rem;
    }

    .payment-options {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
    }

    .payment-card {
        background: white;
        border: 2px solid #e0e0e0;
        border-radius: 15px;
        padding: 2rem;
        text-align: center;
        transition: all 0.3s ease;
    }

    .payment-card:hover {
        border-color: #C9A961;
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }

    .payment-header h4 {
        color: #2C3E50;
        font-size: 1.3rem;
        margin-bottom: 1rem;
    }

    .payment-amount {
        font-size: 2rem;
        font-weight: 700;
        color: #C9A961;
        margin-bottom: 1rem;
    }

    .payment-details {
        text-align: left;
        margin-bottom: 2rem;
    }

    .payment-details p {
        color: #666;
        margin-bottom: 1rem;
    }

    .payment-details ul {
        list-style: none;
        padding: 0;
    }

    .payment-details li {
        color: #555;
        padding: 0.25rem 0;
        position: relative;
        padding-left: 1.5rem;
    }

    .payment-details li:before {
        content: '✓';
        color: #28a745;
        font-weight: bold;
        position: absolute;
        left: 0;
    }

    .btn-payment {
        background: linear-gradient(135deg, #C9A961 0%, #8B7355 100%);
        color: white;
        border: none;
        padding: 1rem 2rem;
        border-radius: 50px;
        font-size: 1.1rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        justify-content: center;
        width: 100%;
        box-shadow: 0 5px 15px rgba(201, 169, 97, 0.3);
    }

    .btn-payment:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(201, 169, 97, 0.4);
    }

    .btn-full {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
    }

    .btn-full:hover {
        box-shadow: 0 8px 25px rgba(40, 167, 69, 0.4);
    }

    @media (max-width: 768px) {
        .payment-container {
            padding: 1rem;
            min-height: calc(100vh - 100px);
        }

        .payment-form-section {
            max-width: 100%;
        }

        .payment-options {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        
        .payment-card {
            padding: 1.5rem;
        }
    }
    </style>
</body>
</html>