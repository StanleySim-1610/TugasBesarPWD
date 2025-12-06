<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';
require_once '../config/functions.php';

requireAdmin();

// Get all F&B orders
$orders = $conn->query("
    SELECT f.*, r.id_user
    FROM fnb_order f
    JOIN reservation r ON f.id_reservation = r.id_reservation
    ORDER BY f.created_at DESC
");
$ordersData = [];

while ($order = $orders->fetch_assoc()) {
    $ordersData[] = $order;
}

echo json_encode([
    'success' => true,
    'data' => $ordersData
]);
?>
