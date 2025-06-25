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

$userId = $_GET['user_id'] ?? null;
if (!$userId) {
    die("User ID is required.");
}   

// Fetch user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User not found.");
}

// Handle verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify'])) {
    $stmt = $pdo->prepare("UPDATE users SET verified = 'yes' WHERE id = ?");
    $stmt->execute([$userId]);
    $user['verified'] = 'yes'; // Update the local variable to reflect the change
    $successMessage = "The organizer has been successfully verified.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Organizer Details</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .button-container {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <h1>Organizer Details</h1>

    <?php if (isset($successMessage)): ?>
        <p style="color: green;"><?php echo htmlspecialchars($successMessage); ?></p>
    <?php endif; ?>

    <p><strong>Full Name:</strong> <?php echo htmlspecialchars($user['full_name']); ?></p>
    <p><strong>Organization:</strong> <?php echo htmlspecialchars($user['organization']); ?></p>
    <p><strong>Past Experience:</strong> <?php echo nl2br(htmlspecialchars($user['past_experience'])); ?></p>
    <p><strong>Verified:</strong> <?php echo $user['verified'] === 'yes' ? 'Yes' : 'No'; ?></p>

    <div class="button-container">
        <?php if ($user['verified'] !== 'yes'): ?>
            <form method="post" style="margin: 0;">
                <button type="submit" name="verify" class="btn">Verify</button>
            </form>
        <?php endif; ?>
        <a href="VerifyOrg.php" class="btn">Back</a>
    </div>
</body>
</html>