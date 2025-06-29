<?php
// filepath: c:\xampp\htdocs\eventa\organizer_events.php
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events by <?php echo htmlspecialchars($organizerUsername); ?></title>
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

        .organizer-info {
            background: rgba(255,255,255,0.1);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
        }

        .organizer-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #667eea;
            font-size: 2rem;
            font-weight: 700;
            margin: 0 auto 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }

        .organizer-name {
            font-size: 2rem;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .organizer-stats {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-top: 20px;
        }

        .stat {
            text-align: center;
        }

        .stat-number {
            font-size: 1.5rem;
            font-weight: 700;
            display: block;
        }

        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
            margin-top: 5px;
        }

        .nav-btn {
            display: inline-block;
            background: white;
            color: #667eea;
            padding: 12px 24px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            margin: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
        }

        .nav-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.3);
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

        @media (max-width: 768px) {
            .events-grid {
                grid-template-columns: 1fr;
            }
            
            .organizer-stats {
                flex-direction: column;
                gap: 15px;
            }
            
            .organizer-name {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="organizer-info">
                <?php
                $firstLetter = strtoupper(substr($organizerUsername, 0, 1));
                $eventCount = count($events);
                ?>
                <div class="organizer-avatar">
                    <?php echo $firstLetter; ?>
                </div>
                <h1 class="organizer-name"><?php echo htmlspecialchars($organizerUsername); ?></h1>
                <div style="opacity: 0.9;">🎪 Event Organizer</div>
                <div class="organizer-stats">
                    <div class="stat">
                        <span class="stat-number"><?php echo $eventCount; ?></span>
                        <div class="stat-label">Total Events</div>
                    </div>
                    <div class="stat">
                        <span class="stat-number">⭐</span>
                        <div class="stat-label">Verified</div>
                    </div>
                </div>
            </div>
            <a href="organizers.php" class="nav-btn">👥 Back to Organizers</a>
        </div>

        <?php if (count($events) === 0): ?>
            <div class="no-events">
                <div class="no-events-icon">📅</div>
                <h3>No events yet</h3>
                <p>This organizer hasn't created any events yet. Check back later for exciting events!</p>
            </div>
        <?php else: ?>
            <div class="events-grid">
                <?php foreach ($events as $event): ?>
                    <div class="event-card" onclick="window.location.href='event_details.php?id=<?php echo $event['id']; ?>'">
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