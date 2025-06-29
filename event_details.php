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

$id = $_GET['id'] ?? null;
if (!$id) {
    die("Event ID is required.");
}

$stmt = $pdo->prepare("SELECT * FROM events WHERE id = :id");
$stmt->execute([':id' => $id]);
$event = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$event) {
    die("Event not found.");
}

// --- PARTICIPATION LOGIC ---
$participationMsg = '';
$userParticipating = false;
$userRole = null;

if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    // Get user id and role
    $userStmt = $pdo->prepare("SELECT id, role FROM users WHERE username = :username");
    $userStmt->execute([':username' => $username]);
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $user_id = $user['id'];
        $userRole = $user['role']; // Get the user's role (e.g., 'user', 'admin', 'event_organizer')

        // Check if already participating
        $checkStmt = $pdo->prepare("SELECT * FROM event_participants WHERE user_id = :user_id AND event_id = :event_id");
        $checkStmt->execute([':user_id' => $user_id, ':event_id' => $id]);
        $userParticipating = $checkStmt->fetch() ? true : false;

        // Handle participate POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['participate'])) {
            if ($event['price'] > 0) {
                // Redirect to payment page for paid events
                header("Location: payment.php?event_id=" . $id);
                exit;
            } else {
                // Handle free event participation
                $insertStmt = $pdo->prepare("INSERT INTO event_participants (user_id, event_id) VALUES (:user_id, :event_id)");
                if ($insertStmt->execute([':user_id' => $user_id, ':event_id' => $id])) {
                    $userParticipating = true;
                } else {
                    $participationMsg = "Failed to join the event.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($event['title']); ?> - Event Details</title>
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
            max-width: 900px;
            margin: 0 auto;
        }

        .nav-btn {
            display: inline-block;
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 12px 24px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            margin-bottom: 20px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.3);
            transition: all 0.3s ease;
        }

        .nav-btn:hover {
            background: rgba(255,255,255,0.3);
            transform: translateY(-2px);
        }

        .event-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-bottom: 20px;
        }

        .event-header {
            position: relative;
            height: 300px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            overflow: hidden;
        }

        .event-header img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0.8;
        }

        .event-header-content {
            position: relative;
            z-index: 2;
            text-align: center;
            padding: 20px;
        }

        .event-title {
            font-size: 2.5rem;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }

        .event-category {
            background: rgba(255,255,255,0.2);
            padding: 8px 16px;
            border-radius: 20px;
            display: inline-block;
            backdrop-filter: blur(10px);
        }

        .event-content {
            padding: 30px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .info-item {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 15px;
            border-left: 4px solid #667eea;
        }

        .info-label {
            font-weight: 700;
            color: #667eea;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .info-value {
            font-size: 1.1rem;
            color: #333;
        }

        .description-card {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 25px;
        }

        .description-card h3 {
            color: #667eea;
            margin-bottom: 15px;
            font-size: 1.3rem;
        }

        .description-card p {
            line-height: 1.6;
            color: #555;
        }

        .details-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .detail-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border-top: 4px solid #667eea;
        }

        .detail-card h4 {
            color: #667eea;
            margin-bottom: 15px;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .detail-card ul {
            list-style: none;
            padding: 0;
        }

        .detail-card li {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .detail-card li:last-child {
            border-bottom: none;
        }

        .actions-section {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            text-align: center;
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
            margin: 5px;
            font-size: 1rem;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        .btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .btn-secondary {
            background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
        }

        .btn-success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        }

        .message {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .message.success {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .price-badge {
            position: absolute;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: 700;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        @media (max-width: 768px) {
            .event-title {
                font-size: 2rem;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .details-section {
                grid-template-columns: 1fr;
            }
            
            .actions-section {
                padding: 20px 15px;
            }
            
            .btn {
                width: 100%;
                margin: 5px 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="events.php" class="nav-btn">← Back to Events</a>
        
        <div class="event-card">
            <div class="event-header">
                <?php if ($event['image']): ?>
                    <img src="<?php echo htmlspecialchars($event['image']); ?>" alt="Event Image">
                <?php endif; ?>
                <div class="event-header-content">
                    <h1 class="event-title"><?php echo htmlspecialchars($event['title']); ?></h1>
                    <div class="event-category"><?php echo htmlspecialchars($event['category']); ?></div>
                </div>
                <div class="price-badge">
                    <?php echo $event['price'] > 0 ? '$' . number_format($event['price'], 2) : 'FREE'; ?>
                </div>
            </div>
            
            <div class="event-content">
                <?php if ($participationMsg): ?>
                    <div class="message success"><?php echo htmlspecialchars($participationMsg); ?></div>
                <?php endif; ?>
                
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">📅 Date & Time</div>
                        <div class="info-value"><?php echo htmlspecialchars($event['date']); ?> at <?php echo htmlspecialchars($event['time']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">📍 Location</div>
                        <div class="info-value"><?php echo htmlspecialchars($event['location']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">👤 Organizer</div>
                        <div class="info-value"><?php echo htmlspecialchars($event['organizer_username'] ?? 'Unknown'); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">👁️ Visibility</div>
                        <div class="info-value"><?php echo ucfirst(htmlspecialchars($event['visibility'])); ?></div>
                    </div>
                </div>
                
                <div class="description-card">
                    <h3>📝 Event Description</h3>
                    <p><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
                </div>
                
                <div class="details-section">
                    <div class="detail-card">
                        <h4>📋 Agenda</h4>
                        <ul>
                            <?php
                            $agendaItems = json_decode($event['agenda'], true);
                            if (is_array($agendaItems) && !empty($agendaItems)) {
                                foreach ($agendaItems as $item) {
                                    echo '<li>• ' . htmlspecialchars($item) . '</li>';
                                }
                            } else {
                                echo '<li>No agenda items available</li>';
                            }
                            ?>
                        </ul>
                    </div>
                    
                    <div class="detail-card">
                        <h4>🎤 Speakers</h4>
                        <ul>
                            <?php
                            $speakerItems = json_decode($event['speakers'], true);
                            if (is_array($speakerItems) && !empty($speakerItems)) {
                                foreach ($speakerItems as $speaker) {
                                    echo '<li>🗣️ ' . htmlspecialchars($speaker) . '</li>';
                                }
                            } else {
                                echo '<li>No speakers announced yet</li>';
                            }
                            ?>
                        </ul>
                    </div>
                    
                    <div class="detail-card">
                        <h4>🏢 Sponsors</h4>
                        <ul>
                            <?php
                            $sponsorItems = json_decode($event['sponsors'], true);
                            if (is_array($sponsorItems) && !empty($sponsorItems)) {
                                foreach ($sponsorItems as $sponsor) {
                                    echo '<li>🤝 ' . htmlspecialchars($sponsor) . '</li>';
                                }
                            } else {
                                echo '<li>No sponsors listed</li>';
                            }
                            ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="actions-section">
            <?php if (isset($_SESSION['username']) && $_SESSION['username'] === $event['organizer_username']): ?>
                <a href="event_form.php?id=<?php echo $event['id']; ?>" class="btn btn-secondary">✏️ Edit Event</a>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['username']) && $userRole === 'user'): ?>
                <form method="post" style="display:inline;">
                    <button type="submit" name="participate" class="btn" <?php if ($userParticipating) echo 'disabled'; ?>>
                        <?php echo $userParticipating ? '✅ Participating' : '🎟️ Join Event'; ?>
                    </button>
                </form>
                <?php if ($userParticipating): ?>
                    <a href="chat.php?event=<?php echo urlencode($event['title']); ?>" class="btn btn-success">💬 Join Chat</a>
                <?php endif; ?>
            <?php elseif (!isset($_SESSION['username'])): ?>
                <div class="message error">
                    🔒 You need to log in to participate in this event.
                </div>
                <a href="SignUp_LogIn_Form.php" class="btn">🔑 Login to Join</a>
            <?php elseif ($userRole !== 'user'): ?>
                <div class="message error">
                    ℹ️ Only regular users can participate in events.
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
