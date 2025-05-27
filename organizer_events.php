<?php
// filepath: c:\xampp\htdocs\eventa\organizer_events.php
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

$organizerUsername = $_GET['username'] ?? null;
if (!$organizerUsername) {
    die("Organizer username is required.");
}

// Fetch events hosted by the organizer
$sql = "SELECT * FROM events WHERE organizer_username = :username ORDER BY date, time";
$stmt = $pdo->prepare($sql);
$stmt->execute([':username' => $organizerUsername]);
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Events by <?php echo htmlspecialchars($organizerUsername); ?></title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .event-list {
            max-width: 900px;
            margin: 20px auto;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }
        .event-box {
            border: 1px solid #ccc;
            padding: 15px;
            width: 250px;
            background: #f9f9f9;
            box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.1);
            transition: box-shadow 0.3s ease;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .event-box:hover {
            box-shadow: 4px 4px 10px rgba(0, 0, 0, 0.2);
        }
        .event-title {
            font-weight: bold;
            font-size: 1.2em;
            margin-bottom: 8px;
        }
        .event-location {
            color: #555;
            margin-bottom: 10px;
        }
        .event-image {
            width: 100%;
            height: 180px;
            object-fit: cover;
            margin-bottom: 10px;
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
    <h2 style="text-align: center;">Events by <?php echo htmlspecialchars($organizerUsername); ?></h2>
    <div class="event-list">
        <?php if (count($events) === 0): ?>
            <p>No events found for this organizer.</p>
        <?php else: ?>
            <?php foreach ($events as $event): ?>
                <div class="event-box" onclick="window.location.href='event_details.php?id=<?php echo $event['id']; ?>'">
                    <?php if (!empty($event['image'])): ?>
                        <img src="<?php echo htmlspecialchars($event['image']); ?>" alt="Event Image" class="event-image">
                    <?php endif; ?>
                    <div class="event-title"><?php echo htmlspecialchars($event['title']); ?></div>
                    <div class="event-location"><?php echo htmlspecialchars($event['location']); ?></div>
                    <div><?php echo htmlspecialchars($event['date']); ?> at <?php echo htmlspecialchars($event['time']); ?></div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <div style="text-align: center;">
        <a href="organizers.php" class="btn-back">Back to Organizers</a>
    </div>
</body>
</html>