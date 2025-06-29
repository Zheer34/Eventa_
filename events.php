<?php
session_start();

// Database connection configuration
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #66a1ff 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            color: white;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .controls {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 25px;
            margin-bottom: 30px;
            display: flex;
            gap: 20px;
            align-items: center;
            flex-wrap: wrap;
        }

        .search-form {
            flex: 1;
            display: flex;
            gap: 15px;
            min-width: 300px;
        }

        .search-input {
            flex: 1;
            padding: 12px 20px;
            border: 2px solid #e1e5e9;
            border-radius: 25px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .search-input:focus {
            outline: none;
            border-color: #667eea;
        }

        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-create {
            background: linear-gradient(135deg, #00b894 0%, #00a085 100%);
            font-size: 1.1rem;
            padding: 15px 30px;
        }

        .btn-create:hover {
            box-shadow: 0 6px 20px rgba(0, 184, 148, 0.4);
        }

        .btn-danger {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
            padding: 8px 16px;
            font-size: 0.9rem;
        }

        .btn-danger:hover {
            box-shadow: 0 4px 15px rgba(255, 107, 107, 0.4);
        }

        .message {
            background: linear-gradient(135deg, #00b894 0%, #00a085 100%);
            color: white;
            padding: 15px 25px;
            border-radius: 12px;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(0, 184, 148, 0.3);
            text-align: center;
            font-weight: 600;
        }

        .events-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }

        .event-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
        }

        .event-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }

        .event-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .event-card:hover .event-image {
            transform: scale(1.05);
        }

        .event-content {
            padding: 25px;
        }

        .event-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 10px;
            line-height: 1.3;
        }

        .event-location {
            color: #666;
            font-size: 1rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .event-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }

        .no-events {
            text-align: center;
            color: white;
            font-size: 1.2rem;
            padding: 60px;
            background: rgba(255,255,255,0.1);
            border-radius: 15px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
        }

        .no-events-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.7;
        }

        @media (max-width: 768px) {
            .controls {
                flex-direction: column;
                align-items: stretch;
            }
            
            .search-form {
                min-width: auto;
            }
            
            .events-grid {
                grid-template-columns: 1fr;
            }
            
            .event-actions {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 Events</h1>
        </div>

        <div class="controls">
            <form method="get" action="events.php" class="search-form">
                <input type="text" name="search" placeholder="Search events by name..." value="<?php echo htmlspecialchars($searchTerm); ?>" class="search-input">
                <button type="submit" class="btn">🔍 Search</button>
            </form>
            <a href="Index.php" class="btn">🏠 Home</a>
            <a href="event_form.php" class="btn btn-create">➕ Create New Event</a>
        </div>

        <?php if ($message): ?>
            <div class="message">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if (count($events) === 0): ?>
            <div class="no-events">
                <div class="no-events-icon">📅</div>
                <h3>No events found</h3>
                <p><?php echo $searchTerm ? 'Try adjusting your search terms or' : 'Be the first to'; ?> create an amazing event!</p>
            </div>
        <?php else: ?>
            <div class="events-grid">
                <?php foreach ($events as $event): ?>
                    <div class="event-card" onclick="window.location.href='event_details.php?id=<?php echo $event['id']; ?>'">
                        <?php if (!empty($event['image'])): ?>
                            <img src="<?php echo htmlspecialchars('/' . $event['image']); ?>" alt="Event Image" class="event-image">
                        <?php else: ?>
                            <div class="event-image" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 3rem;">
                                🎪
                            </div>
                        <?php endif; ?>
                        <div class="event-content">
                            <div class="event-title"><?php echo htmlspecialchars($event['title']); ?></div>
                            <div class="event-location">
                                📍 <?php echo htmlspecialchars($event['location']); ?>
                            </div>
                            <div class="event-actions">
                                <span style="color: #667eea; font-weight: 600;">Click to view details</span>
                                <form method="post" onsubmit="return confirm('Are you sure you want to delete this event?');" onclick="event.stopPropagation();">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $event['id']; ?>">
                                    <button type="submit" class="btn-danger">🗑️ Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
