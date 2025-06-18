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

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $userId = $_POST['user_id'];
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$userId]);
}

// Handle admin creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_admin'])) {
    $username = $_POST['username'];
    $password = hash('sha256', $_POST['password']);
    $stmt = $pdo->prepare("INSERT INTO users (username, password, role, verified) VALUES (?, ?, 'admin', 'yes')");
    $stmt->execute([$username, $password]);
}

// Fetch users
$searchTerm = $_GET['search'] ?? '';
$sql = "SELECT id, username, role, verified FROM users WHERE username LIKE :search";
$stmt = $pdo->prepare($sql);
$stmt->execute([':search' => '%' . $searchTerm . '%']);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header class="hero">
        <h1>Manage Users</h1>
        <a href="Index.php" class="btn" style="margin-top: 10px;">Home</a>
    </header>
    <section class="intro">
        <h2>Search and Manage Users</h2>
        <form method="get" style="margin-bottom: 20px;">
            <input type="text" name="search" placeholder="Search by username" value="<?php echo htmlspecialchars($searchTerm); ?>" style="padding: 10px; width: 300px; border-radius: 5px; border: 1px solid #ccc;">
            <button type="submit" class="btn">Search</button>
        </form>
        <table style="margin: 0 auto; border-collapse: collapse; width: 80%;">
            <thead>
                <tr style="background-color: #9fbdee; color: white;">
                    <th style="padding: 10px; border: 1px solid #ccc;">Username</th>
                    <th style="padding: 10px; border: 1px solid #ccc;">Role</th>
                    <th style="padding: 10px; border: 1px solid #ccc;">Verified</th>
                    <th style="padding: 10px; border: 1px solid #ccc;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td style="padding: 10px; border: 1px solid #ccc;"><?php echo htmlspecialchars($user['username']); ?></td>
                        <td style="padding: 10px; border: 1px solid #ccc;"><?php echo htmlspecialchars($user['role']); ?></td>
                        <td style="padding: 10px; border: 1px solid #ccc;"><?php echo htmlspecialchars($user['verified']); ?></td>
                        <td style="padding: 10px; border: 1px solid #ccc;">
                            <form method="post" style="display: inline;">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <button type="submit" name="delete" class="btn">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>
    <section class="intro">
        <h2>Create Admin Account</h2>
        <form method="post" style="margin-top: 20px;">
            <input type="text" name="username" placeholder="Username" required style="padding: 10px; width: 300px; border-radius: 5px; border: 1px solid #ccc;">
            <input type="password" name="password" placeholder="Password" required style="padding: 10px; width: 300px; border-radius: 5px; border: 1px solid #ccc; margin-top: 10px;">
            <button type="submit" name="create_admin" class="btn" style="margin-top: 10px;">Create Admin</button>
        </form>
    </section>
</body>
</html>