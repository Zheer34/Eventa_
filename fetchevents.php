<?php
// fetchevents.php - Returns event data as JSON

$host = 'localhost';
$port = 3306;
$dbname = 'eventa';
$username = 'root';
$password = '12345';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Get search term from GET parameter
$searchTerm = $_GET['search'] ?? '';

try {
    if ($searchTerm) {
        $sql = "SELECT id, title, location, image, date, time, category, visibility, recurring, agenda, speakers, sponsors, organizer_username FROM events WHERE title LIKE :search ORDER BY date, time";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':search' => '%' . $searchTerm . '%']);
    } else {
        $sql = "SELECT id, title, location, image, date, time, category, visibility, recurring, agenda, speakers, sponsors, organizer_username FROM events ORDER BY date, time";
        $stmt = $pdo->query($sql);
    }
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
    header('Content-Type: application/json');
    echo json_encode($events);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch events']);
}
?>
