<?php
require_once 'config/database.php';
require_once 'config/auth.php';

// Require login to make reservation
requireLogin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $checkin = $_POST['checkin'];
    $checkout = $_POST['checkout'];
    $room = $_POST['room'];
    $guests = $_POST['guests'];
    $price = $_POST['price'];
    // Optional configuration options (JSON string)
    $options = isset($_POST['options']) ? $_POST['options'] : null;
    $user_id = getUserId();

    // Validate dates
    if (strtotime($checkout) <= strtotime($checkin)) {
        header('Location: index.php?error=Check-out date must be after check-in date');
        exit();
    }

    // Save to database
    $conn = getDBConnection();
    $stmt = $conn->prepare("INSERT INTO reservations (user_id, guest_name, checkin_date, checkout_date, room_type, guests, price, options) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssids", $user_id, $name, $checkin, $checkout, $room, $guests, $price, $options);
    
    if ($stmt->execute()) {
        $reservation_id = $conn->insert_id;
        $stmt->close();
        $conn->close();
        header('Location: confirmation.php?id=' . $reservation_id);
        exit();
    } else {
        $error = 'Reservation failed. Please try again.';
    }
    
    $stmt->close();
    $conn->close();
} else {
    header('Location: index.php');
    exit();
}
?>
