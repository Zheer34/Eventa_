<?php
session_start();

// Database connection
$host = 'localhost';
$dbname = 'eventa';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;port=4307;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$user]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($userData && hash('sha256', $pass) === $userData['password']) {
        // Store user information in the session
        $_SESSION['user_id'] = $userData['id']; // Add this line to store the user ID
        $_SESSION['username'] = $userData['username'];
        $_SESSION['role'] = $userData['role'];

        if ($userData['role'] === 'admin') {
            header("Location: index.html");
        } else {
            header("Location: index.html");
        }
        exit();
    } else {
        $_SESSION['error'] = "Invalid username or password.";
        header("Location: SignUp_LogIn_Form.html");  // Redirect to login_form.php for invalid logins
        exit();
    }
}
?>