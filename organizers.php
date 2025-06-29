<?php
// filepath: c:\xampp\htdocs\eventa\organizers.php
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

// Fetch all organizers
$sql = "SELECT username FROM users WHERE role = 'event_organizer'";
$stmt = $pdo->query($sql);
$organizers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Organizers</title>
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
            max-width: 1000px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            color: white;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
            margin-bottom: 20px;
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

        .organizers-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }

        .organizer-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 25px;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .organizer-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }

        .organizer-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .organizer-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 15px;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .organizer-name {
            font-size: 1.3rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 10px;
        }

        .organizer-role {
            color: #666;
            font-size: 0.95rem;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .organizer-stats {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }

        .stat {
            text-align: center;
        }

        .stat-number {
            font-size: 1.2rem;
            font-weight: 700;
            color: #667eea;
            display: block;
        }

        .stat-label {
            font-size: 0.85rem;
            color: #666;
            margin-top: 2px;
        }

        .view-events-btn {
            position: absolute;
            bottom: 20px;
            right: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .organizer-card:hover .view-events-btn {
            opacity: 1;
        }

        .no-organizers {
            text-align: center;
            color: white;
            font-size: 1.2rem;
            padding: 60px;
            background: rgba(255,255,255,0.1);
            border-radius: 15px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
        }

        .no-organizers-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.7;
        }

        @media (max-width: 768px) {
            .organizers-grid {
                grid-template-columns: 1fr;
            }
            
            .header h1 {
                font-size: 2rem;
            }
            
            .organizer-stats {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>👥 Event Organizers</h1>
            <p>Discover amazing events from talented organizers</p>
            <a href="Index.php" class="nav-btn">🏠 Back to Home</a>
        </div>

        <?php if (count($organizers) === 0): ?>
            <div class="no-organizers">
                <div class="no-organizers-icon">🎭</div>
                <h3>No organizers found</h3>
                <p>There are currently no event organizers in the system.</p>
            </div>
        <?php else: ?>
            <div class="organizers-grid">
                <?php foreach ($organizers as $organizer): ?>
                    <?php
                    // Get organizer's event count
                    $eventCountStmt = $pdo->prepare("SELECT COUNT(*) as count FROM events WHERE organizer_username = ?");
                    $eventCountStmt->execute([$organizer['username']]);
                    $eventCount = $eventCountStmt->fetch(PDO::FETCH_ASSOC)['count'];
                    
                    // Get first letter for avatar
                    $firstLetter = strtoupper(substr($organizer['username'], 0, 1));
                    ?>
                    <div class="organizer-card" onclick="window.location.href='organizer_events.php?username=<?php echo urlencode($organizer['username']); ?>'">
                        <div class="organizer-avatar">
                            <?php echo $firstLetter; ?>
                        </div>
                        <div class="organizer-name"><?php echo htmlspecialchars($organizer['username']); ?></div>
                        <div class="organizer-role">
                            🎪 Event Organizer
                        </div>
                        <div class="organizer-stats">
                            <div class="stat">
                                <span class="stat-number"><?php echo $eventCount; ?></span>
                                <div class="stat-label">Events</div>
                            </div>
                            <div class="stat">
                                <span class="stat-number">⭐</span>
                                <div class="stat-label">Verified</div>
                            </div>
                        </div>
                        <button class="view-events-btn">View Events →</button>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>