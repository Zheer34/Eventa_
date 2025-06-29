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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Participating Events</title>
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
            text-align: center;
            margin-bottom: 30px;
        }

        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            font-size: 1.1rem;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-browse {
            background: linear-gradient(135deg, #00b894 0%, #00a085 100%);
        }

        .btn-browse:hover {
            box-shadow: 0 6px 20px rgba(0, 184, 148, 0.4);
        }

        .success-message {
            background: linear-gradient(135deg, #00b894 0%, #00a085 100%);
            color: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 30px;
            text-align: center;
            font-weight: 600;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            animation: slideIn 0.5s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
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
            margin-bottom: 15px;
            line-height: 1.3;
        }

        .event-details {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-bottom: 20px;
        }

        .event-location, .event-datetime {
            color: #666;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .event-footer {
            padding-top: 15px;
            border-top: 1px solid #eee;
            text-align: center;
        }

        .view-details {
            color: #667eea;
            font-weight: 600;
            font-size: 0.95rem;
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

        .participant-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: linear-gradient(135deg, #00b894 0%, #00a085 100%);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        @media (max-width: 768px) {
            .events-grid {
                grid-template-columns: 1fr;
            }
            
            .event-details {
                gap: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎟️ My Participating Events</h1>
        </div>

        <div class="controls">
            <a href="events.php" class="btn btn-browse">🔍 Browse All Events</a>
        </div>

        <?php if (isset($_GET['payment_success']) && $_GET['payment_success'] == 1): ?>
            <div class="success-message">
                🎉 Payment successful! The event has been added to your list.
            </div>
        <?php endif; ?>

        <?php if (count($events) === 0): ?>
            <div class="no-events">
                <div class="no-events-icon">🎪</div>
                <h3>No events yet!</h3>
                <p>You are not participating in any events yet. Browse available events and join something exciting!</p>
            </div>
        <?php else: ?>
            <div class="events-grid">
                <?php foreach ($events as $event): ?>
                    <div class="event-card" onclick="window.location.href='event_details.php?id=<?php echo $event['id']; ?>'">
                        <div class="participant-badge">✓ Participating</div>
                        <?php if (!empty($event['image'])): ?>
                            <img src="<?php echo htmlspecialchars($event['image']); ?>" alt="Event Image" class="event-image">
                        <?php else: ?>
                            <div class="event-image" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 3rem;">
                                🎪
                            </div>
                        <?php endif; ?>
                        <div class="event-content">
                            <div class="event-title"><?php echo htmlspecialchars($event['title']); ?></div>
                            <div class="event-details">
                                <div class="event-location">
                                    📍 <?php echo htmlspecialchars($event['location']); ?>
                                </div>
                                <div class="event-datetime">
                                    📅 <?php echo htmlspecialchars($event['date']); ?> 
                                    🕐 <?php echo htmlspecialchars($event['time']); ?>
                                </div>
                            </div>
                            <div class="event-footer">
                                <span class="view-details">Click to view full details →</span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>