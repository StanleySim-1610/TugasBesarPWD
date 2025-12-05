<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';
require_once '../config/functions.php';

requireAdmin();

// Get all users
$users = $conn->query("SELECT id_user, nama, email, no_telp, no_identitas, created_at FROM user ORDER BY created_at DESC");
$usersData = [];

while ($user = $users->fetch_assoc()) {
    $usersData[] = $user;
}

echo json_encode([
    'success' => true,
    'data' => $usersData
]);
?>
