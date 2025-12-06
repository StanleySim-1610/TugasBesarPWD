<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';
require_once '../config/functions.php';

requireAdmin();

// Get admin info
$admin_email = $_SESSION['email'];
$admin = $conn->query("SELECT id_user, nama, email FROM user WHERE email = '$admin_email'")->fetch_assoc();

echo json_encode([
    'success' => true,
    'data' => $admin
]);
?>
