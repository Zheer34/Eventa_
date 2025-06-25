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
    <title><?php echo htmlspecialchars($event['title']); ?> - Event Details</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="Login_Form.css">
    <style>
        .event-details {
            max-width: 800px;
            margin: 30px auto;
            padding: 25px 30px;
            border-radius: 12px;
            border: 1px solid #ccc;
            background: #f9f9f9;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
        }
        .event-header {
            display: flex;
            align-items: center;
            gap: 25px;
            margin-bottom: 25px;
        }
        .event-header img {
            width: 160px;
            height: 120px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #ddd;
        }
        .event-title {
            font-size: 2rem;
            margin: 0;
        }
        .event-info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px 32px;
            margin-bottom: 25px;
        }
        .event-info-label {
            font-weight: bold;
            color: #333;
        }
        .event-section-card {
            background: #fff;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            padding: 18px 20px;
            margin-bottom: 18px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.03);
        }
        .event-section-card h3 {
            margin-top: 0;
            margin-bottom: 10px;
            font-size: 1.1rem;
            color: #007bff;
        }
        .event-section-card ul {
            margin: 0;
            padding-left: 20px;
        }
        .btn {
            padding: 8px 18px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-top: 10px;
            margin-right: 10px;
            font-size: 1rem;
        }
        .participation-msg {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 6px;
        }
        .error-msg {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 6px;
        }
        @media (max-width: 600px) {
            .event-info-grid {
                grid-template-columns: 1fr;
            }
            .event-header {
                flex-direction: column;
                align-items: flex-start;
            }
            .event-header img {
                width: 100%;
                height: auto;
            }
        }
    </style>
</head>
<body>
    <div class="event-details">
        <div class="event-header">
            <?php if ($event['image']): ?>
                <img src="<?php echo htmlspecialchars($event['image']); ?>" alt="Event Image">
            <?php endif; ?>
            <div>
                <h2 class="event-title"><?php echo htmlspecialchars($event['title']); ?></h2>
                <div style="color:#666;"><?php echo htmlspecialchars($event['category']); ?></div>
            </div>
        </div>
        <?php if ($participationMsg): ?>
            <div class="participation-msg"><?php echo htmlspecialchars($participationMsg); ?></div>
        <?php endif; ?>
        <div class="event-info-grid">
            <div>
                <div class="event-info-label">Date & Time:</div>
                <div><?php echo htmlspecialchars($event['date']); ?> at <?php echo htmlspecialchars($event['time']); ?></div>
            </div>
            <div>
                <div class="event-info-label">Location:</div>
                <div><?php echo htmlspecialchars($event['location']); ?></div>
            </div>
            <div>
                
            </div>
            <div>
                
            </div>
            <div>
                <div class="event-info-label">Price:</div>
                <div><?php echo $event['price'] > 0 ? '$' . number_format($event['price'], 2) : 'Free'; ?></div>
            </div>
        </div>
        <div class="event-section-card">
            <h3>Description</h3>
            <p><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
        </div>
        <div class="event-section-card">
            <h3>Agenda</h3>
            <ul>
                <?php
                $agendaItems = json_decode($event['agenda'], true);
                if (is_array($agendaItems)) {
                    foreach ($agendaItems as $item) {
                        echo '<li>' . htmlspecialchars($item) . '</li>';
                    }
                }
                ?>
            </ul>
        </div>
        <div class="event-section-card">
            <h3>Speakers</h3>
            <ul>
                <?php
                $speakerItems = json_decode($event['speakers'], true);
                if (is_array($speakerItems)) {
                    foreach ($speakerItems as $speaker) {
                        echo '<li>' . htmlspecialchars($speaker) . '</li>';
                    }
                }
                ?>
            </ul>
        </div>
        <div class="event-section-card">
            <h3>Sponsors</h3>
            <ul>
                <?php
                $sponsorItems = json_decode($event['sponsors'], true);
                if (is_array($sponsorItems)) {
                    foreach ($sponsorItems as $sponsor) {
                        echo '<li>' . htmlspecialchars($sponsor) . '</li>';
                    }
                }
                ?>
            </ul>
        </div>
        <a href="events.html" class="btn">Back to Events</a>
        <?php if (isset($_SESSION['username']) && $_SESSION['username'] === $event['organizer_username']): ?>
            <a href="event_form.php?id=<?php echo $event['id']; ?>" class="btn">Edit Event</a>
        <?php endif; ?>
        <?php if (isset($_SESSION['username']) && $userRole === 'user'): ?>
            <form method="post" style="display:inline;">
                <button type="submit" name="participate" class="btn" <?php if ($userParticipating) echo 'disabled'; ?>>
                    <?php echo $userParticipating ? 'Participating' : 'Participate'; ?>
                </button>
            </form>
            <?php if ($userParticipating): ?>
                <a href="chat.php?event=<?php echo urlencode($event['title']); ?>" class="btn">Join Chat</a>
            <?php endif; ?>
        <?php elseif (!isset($_SESSION['username'])): ?>
            <div class="error-msg">
                You need to log in to participate in this event.
            </div>
        <?php elseif ($userRole !== 'user'): ?>
            <div class="error-msg">
                Only normal users can participate in events.
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
