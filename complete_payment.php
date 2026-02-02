<?php
require_once 'config/database.php';
require_once 'config/auth.php';

// Require login
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: booking.php?error=Invalid request method');
    exit();
}

// Get form data
$reservationId = intval($_POST['reservation_id'] ?? 0);
$paymentMethod = $_POST['payment_method'] ?? '';

// Validation
if ($reservationId <= 0) {
    header('Location: booking.php?error=Invalid reservation');
    exit();
}

$validMethods = ['credit_card', 'paypal', 'gcash', 'bank_transfer', 'cash', 'otc'];
if (!in_array($paymentMethod, $validMethods)) {
    header('Location: payment_method.php?reservation_id=' . $reservationId . '&error=Invalid payment method');
    exit();
}

try {
    $conn = getDBConnection();
    
    // Verify reservation belongs to current user
    $stmt = $conn->prepare("SELECT * FROM reservations WHERE id = ? AND user_id = ?");
    $userId = getUserId();
    $stmt->bind_param("ii", $reservationId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        header('Location: booking.php?error=Reservation not found');
        exit();
    }
    
    $reservation = $result->fetch_assoc();
    $stmt->close();
    
    // Generate payment reference
    $paymentReference = 'PAY-' . strtoupper(substr($paymentMethod, 0, 3)) . '-' . date('Ymd') . '-' . str_pad($reservationId, 6, '0', STR_PAD_LEFT);
    
    // Update reservation with payment method and reference
    $paymentStatus = ($paymentMethod === 'cash' || $paymentMethod === 'otc') ? 'pending' : 'completed';
    
    $stmt = $conn->prepare("UPDATE reservations SET payment_method = ?, payment_reference = ?, payment_status = ?, status = 'confirmed' WHERE id = ?");
    $stmt->bind_param("sssi", $paymentMethod, $paymentReference, $paymentStatus, $reservationId);
    
    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        
        // Redirect to confirmation page
        header('Location: confirmation.php?reservation_id=' . $reservationId);
        exit();
    } else {
        throw new Exception("Failed to complete payment: " . $stmt->error);
    }
    
} catch (Exception $e) {
    error_log("Complete payment error: " . $e->getMessage());
    header('Location: payment_method.php?reservation_id=' . $reservationId . '&error=Payment processing failed, please try again');
    exit();
}
?>