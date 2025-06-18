<?php

session_start();

$host = 'localhost';
$port = 3306;
$dbname = 'eventa';
$username = 'root';
$password = '12345';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

if (!isset($_SESSION['username'])) {
    die("You must be logged in to view your events.");
}

// Get user id
$userStmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
$userStmt->execute([':username' => $_SESSION['username']]);
$user = $userStmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User not found.");
}

$user_id = $user['id'];

// Fetch events the user is participating in
$sql = "SELECT e.* FROM events e
        INNER JOIN event_participants ep ON e.id = ep.event_id
        WHERE ep.user_id = :user_id
        ORDER BY e.date, e.time";
$stmt = $pdo->prepare($sql);
$stmt->execute([':user_id' => $user_id]);
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Participating Events</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="Login_Form.css">
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
            box-shadow: 2px 2px 5px rgba(0,0,0,0.1);
            transition: box-shadow 0.3s ease;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .event-box:hover {
            box-shadow: 4px 4px 10px rgba(0,0,0,0.2);
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
        .success-msg {
            background-color: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
        }
    </style>
</head>
<body>
    <h2 style="text-align:center;">My Participating Events</h2>
    <div style="max-width:900px; margin:10px auto;">
        <a href="events.html" class="btn" style="margin-bottom: 15px; display: inline-block;">Browse All Events</a>
    </div>
    <?php if (isset($_GET['payment_success']) && $_GET['payment_success'] == 1): ?>
        <div class="success-msg">Payment successful! The event has been added to your list.</div>
    <?php endif; ?>
    <div class="event-list">
        <?php if (count($events) === 0): ?>
            <p>You are not participating in any events yet.</p>
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
</body>
</html>