<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';
require_once '../config/functions.php';

requireAdmin();

// Get all payments
$payments = $conn->query("
    SELECT p.*, r.id_user
    FROM payment_reservation p
    JOIN reservation r ON p.id_reservation = r.id_reservation
    ORDER BY p.created_at DESC
");
$paymentsData = [];

while ($payment = $payments->fetch_assoc()) {
    $paymentsData[] = $payment;
}

echo json_encode([
    'success' => true,
    'data' => $paymentsData
]);
?>
