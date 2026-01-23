<?php
require_once '../config/database.php';

header('Content-Type: application/json');

$roomType = $_GET['room_type'] ?? '';

if (empty($roomType)) {
    echo json_encode(['error' => 'Room type is required']);
    exit();
}

$conn = getDBConnection();
$stmt = $conn->prepare("SELECT image_path FROM room_images WHERE room_type = ? ORDER BY display_order ASC");
$stmt->bind_param("s", $roomType);
$stmt->execute();
$result = $stmt->get_result();

$images = [];
while ($row = $result->fetch_assoc()) {
    $images[] = $row['image_path'];
}

$stmt->close();
$conn->close();

echo json_encode(['images' => $images]);
?>

