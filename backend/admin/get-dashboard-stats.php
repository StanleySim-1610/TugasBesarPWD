<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';
require_once '../config/functions.php';

requireAdmin();

// Get dashboard stats
$rooms = $conn->query("SELECT COUNT(*) as count FROM kamar")->fetch_assoc();
$reservations = $conn->query("SELECT COUNT(*) as count FROM reservation")->fetch_assoc();
$users = $conn->query("SELECT COUNT(*) as count FROM user")->fetch_assoc();
$revenue = $conn->query("SELECT SUM(total_bayar) as total FROM payment_reservation WHERE status = 'paid'")->fetch_assoc();

echo json_encode([
    'success' => true,
    'data' => [
        'total_rooms' => $rooms['count'],
        'total_reservations' => $reservations['count'],
        'total_users' => $users['count'],
        'total_revenue' => $revenue['total'] ?? 0
    ]
]);
?>
