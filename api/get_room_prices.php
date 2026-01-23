<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

$conn = getDBConnection();

$sql = "SELECT room_type, pax_group, price FROM room_prices";
$res = $conn->query($sql);

$prices = [];
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $rt = $row['room_type'];
        $pg = intval($row['pax_group']);
        $pr = floatval($row['price']);
        if (!isset($prices[$rt])) $prices[$rt] = [];
        $prices[$rt][$pg] = $pr;
    }
}

$conn->close();

echo json_encode(['success' => true, 'prices' => $prices]);
