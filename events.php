<?php
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

$message = '';
$action = $_POST['action'] ?? '';

// Function to add notifications
function addNotification($pdo, $message) {
    $stmt = $pdo->prepare("INSERT INTO notifications (message) VALUES (:message)");
    $stmt->execute([':message' => $message]);
}

// Handle delete action
if ($action === 'delete') {
    $id = $_POST['id'] ?? null;
    if ($id) {
        // Fetch the event title before deleting
        $stmt = $pdo->prepare("SELECT title FROM events WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $event = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($event) {
            $sql = "DELETE FROM events WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute([':id' => $id])) {
                $message = "Event deleted successfully.";
                // Add a notification for the deleted event
                addNotification($pdo, "The event '{$event['title']}' has been canceled.");
            } else {
                $message = "Error deleting event.";
            }
        } else {
            $message = "Event not found.";
        }
    }
}

// Fetch events with optional search filter
$searchTerm = $_GET['search'] ?? '';
if ($searchTerm) {
    $sql = "SELECT id, title, location, image FROM events WHERE title LIKE :search ORDER BY date, time";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':search' => '%' . $searchTerm . '%']);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $sql = "SELECT id, title, location, image FROM events ORDER BY date, time";
    $stmt = $pdo->query($sql);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Events</title>
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
            cursor: pointer;
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
        .btn-danger {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 6px 10px;
            cursor: pointer;
            margin-top: 10px;
        }
        .event-image {
            width: 100%;
            height: 180px;
            object-fit: cover;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <h2 style="text-align:center;">Events</h2>

    <!-- Search form -->
    <div style="max-width: 900px; margin: 10px auto 20px auto; text-align: center;">
        <form method="get" action="events.php" style="display: inline-block; width: 100%; max-width: 400px;">
            <input type="text" name="search" placeholder="Search events by name" value="<?php echo htmlspecialchars($searchTerm); ?>" style="width: 70%; padding: 8px; font-size: 1em; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
            <button type="submit" style="width: 28%; padding: 8px; font-size: 1em; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">Search</button>
        </form>
    </div>

    <?php if ($message): ?>
        <div class="message" style="max-width: 900px; margin: 10px auto; padding: 10px; background: #d4edda; color: #155724; border: 1px solid #c3e6cb;">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div style="max-width: 900px; margin: 10px auto;">
        <a href="event_form.php" class="btn" style="margin-bottom: 15px; display: inline-block;">Create New Event</a>
    </div>

    <div class="event-list">
        <?php if (count($events) === 0): ?>
            <p>No events found.</p>
        <?php else: ?>
            <?php foreach ($events as $event): ?>
                <div class="event-box" onclick="window.location.href='event_details.php?id=<?php echo $event['id']; ?>'">
                    <?php if (!empty($event['image'])): ?>
                        <img src="<?php echo htmlspecialchars('/' . $event['image']); ?>" alt="Event Image" class="event-image">
                    <?php endif; ?>
                    <div>
                        <div class="event-title"><?php echo htmlspecialchars($event['title']); ?></div>
                        <div class="event-location"><?php echo htmlspecialchars($event['location']); ?></div>
                    </div>
                    <form method="post" onsubmit="return confirm('Are you sure you want to delete this event?');" style="margin-top: 10px;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?php echo $event['id']; ?>">
                        <button type="submit" class="btn-danger">Delete</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>
