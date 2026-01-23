<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$room = $_GET['room'] ?? '';
$guests = isset($_GET['guests']) ? intval($_GET['guests']) : null;

if (!$room || !$guests) {
    echo json_encode(['success' => false, 'message' => 'room and guests are required']);
    exit;
}

$conn = getDBConnection();
$stmt = $conn->prepare("SELECT checkin_date, checkout_date FROM reservations WHERE room_type = ? AND guests = ?");
$stmt->bind_param('si', $room, $guests);
$stmt->execute();
$res = $stmt->get_result();

$blocked = [];
while ($row = $res->fetch_assoc()) {
    $start = $row['checkin_date'];
    $end = $row['checkout_date'];
    $cur = strtotime($start);
    $endTs = strtotime($end);
    // mark nights from checkin up to (but not including) checkout
    while ($cur < $endTs) {
        $blocked[] = date('Y-m-d', $cur);
        $cur = strtotime('+1 day', $cur);
    }
}

$stmt->close();
$conn->close();

echo json_encode(['success' => true, 'room' => $room, 'guests' => $guests, 'blocked' => array_values(array_unique($blocked))]);
exit;
?>
