<?php
// cv_viewer.php - Simple CV file viewer for admins
session_start();

// Check if user is admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Access denied. Admin privileges required.");
}

$filePath = $_GET['file'] ?? null;
if (!$filePath) {
    die("No file specified.");
}

// Security check - ensure file is in uploads/cv directory and is a PDF
$realPath = realpath($filePath);
$uploadsPath = realpath("uploads/cv/");

if (!$realPath || !$uploadsPath || strpos($realPath, $uploadsPath) !== 0) {
    die("Invalid file path.");
}

if (!file_exists($filePath) || pathinfo($filePath, PATHINFO_EXTENSION) !== 'pdf') {
    die("File not found or invalid file type.");
}

// Set appropriate headers for PDF viewing
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . basename($filePath) . '"');
header('Content-Length: ' . filesize($filePath));

// Output the file
readfile($filePath);
?>
