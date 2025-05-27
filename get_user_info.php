<?php
session_start();

header('Content-Type: application/json');

// Debug: Log session data
error_log("Session Data: " . print_r($_SESSION, true));

if (isset($_SESSION['username']) && isset($_SESSION['role'])) {
    echo json_encode([
        'username' => $_SESSION['username'],
        'role' => $_SESSION['role']
    ]);
} else {
    echo json_encode([
        'username' => null,
        'role' => null
    ]);
}
?>
