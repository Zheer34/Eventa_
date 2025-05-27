<?php

header('Content-Type: application/json');

$host = 'localhost';
$port = 4307;
$dbname = 'eventa';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

try {
    $stmt = $pdo->query("SELECT message, created_at FROM notifications ORDER BY created_at DESC LIMIT 10");
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($notifications);
} catch (Exception $e) {
    echo json_encode(['error' => 'Failed to fetch notifications']);
}
?>