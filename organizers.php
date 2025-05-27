<?php
// filepath: c:\xampp\htdocs\eventa\organizers.php
session_start();

// Database connection configuration
$host = 'localhost';
$port = 4307;
$dbname = 'eventa';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Fetch all organizers
$sql = "SELECT username FROM users WHERE role = 'event_organizer'";
$stmt = $pdo->query($sql);
$organizers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Organizers</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .organizer-list {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .organizer-item {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            cursor: pointer;
            transition: background 0.2s;
        }
        .organizer-item:hover {
            background: #f0f0f0;
        }
        .organizer-item:last-child {
            border-bottom: none;
        }
        .btn-back {
            display: inline-block;
            margin: 10px 0;
            padding: 8px 15px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
        .btn-back:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <h2 style="text-align: center;">Organizers</h2>
    <div class="organizer-list">
        <?php if (count($organizers) === 0): ?>
            <p>No organizers found.</p>
        <?php else: ?>
            <?php foreach ($organizers as $organizer): ?>
                <div class="organizer-item" onclick="window.location.href='organizer_events.php?username=<?php echo urlencode($organizer['username']); ?>'">
                    <?php echo htmlspecialchars($organizer['username']); ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <div style="text-align: center;">
        <a href="index.html" class="btn-back">Back to Home</a>
    </div>
</body>
</html>