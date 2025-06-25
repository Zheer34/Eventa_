<?php
session_start();

if ($_SESSION['role'] !== 'admin') {
    die("Access denied.");
}

// Database connection
$host = 'localhost';
$dbname = 'eventa';
$username = 'root';
$password = '12345';

try {
    $pdo = new PDO("mysql:host=$host;port=3306;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Handle verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify'])) {
    $userId = $_POST['user_id'];
    $stmt = $pdo->prepare("UPDATE users SET verified = 'yes' WHERE id = ?");
    $stmt->execute([$userId]);
}

// Fetch unverified event organizers
$stmt = $pdo->query("SELECT id, username FROM users WHERE role = 'event_organizer' AND verified = 'no'");
$organizers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify Event Organizers</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header class="hero">
        <h1>Verify Event Organizers</h1>
        <a href="Index.php" class="btn" style="margin-top: 10px;">Home</a>
    </header>
    <section class="intro">
        <h2>Pending Event Organizer Accounts</h2>
        <table style="margin: 0 auto; border-collapse: collapse; width: 80%;">
            <thead>
                <tr style="background-color: #9fbdee; color: white;">
                    <th style="padding: 10px; border: 1px solid #ccc;">Username</th>
                    <th style="padding: 10px; border: 1px solid #ccc;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($organizers as $organizer): ?>
                    <tr>
                        <td style="padding: 10px; border: 1px solid #ccc;"><?php echo htmlspecialchars($organizer['username']); ?></td>
                        
                        <td style="padding: 10px; border: 1px solid #ccc;">
                            <form method="get" action="view_organizer.php">
                                <input type="hidden" name="user_id" value="<?php echo $organizer['id']; ?>">
                                <button type="submit" class="btn">View</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</body>
</html>