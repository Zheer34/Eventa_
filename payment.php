<?php
session_start();

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

$event_id = $_GET['event_id'] ?? null;
if (!$event_id) {
    die("Event ID is required.");
}

$stmt = $pdo->prepare("SELECT * FROM events WHERE id = :id");
$stmt->execute([':id' => $event_id]);
$event = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$event) {
    die("Event not found.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'] ?? null;
    if (!$user_id) {
        die("You must be logged in to make a payment.");
    }

    $amount = $event['price'];

    // Insert payment record
    $stmt = $pdo->prepare("INSERT INTO payments (user_id, event_id, amount) VALUES (:user_id, :event_id, :amount)");
    if ($stmt->execute([':user_id' => $user_id, ':event_id' => $event_id, ':amount' => $amount])) {
        // Add user to event participants
        $stmt = $pdo->prepare("INSERT INTO event_participants (user_id, event_id) VALUES (:user_id, :event_id)");
        $stmt->execute([':user_id' => $user_id, ':event_id' => $event_id]);

        // Redirect to my_events.php with a confirmation message
        header("Location: my_events.php?payment_success=1");
        exit;
    } else {
        $error = "Payment failed. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment for <?php echo htmlspecialchars($event['title']); ?></title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h2>Payment for <?php echo htmlspecialchars($event['title']); ?></h2>
    <p>Price: $<?php echo number_format($event['price'], 2); ?></p>

    <?php if (isset($error)): ?>
        <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="post">
        <button type="submit" class="btn">Pay $<?php echo number_format($event['price'], 2); ?></button>
    </form>
</body>
</html>