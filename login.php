<?php

session_start();

// Database connection
$host = 'localhost';
$dbname = 'eventa';
$username = 'root';
$password = '12345';

try {
    $pdo = new PDO("mysql:host=$host;port=3306;dbname=$dbname", $username, $password);
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
        if ($userData['verified'] === 'no') {
            $_SESSION['error'] = "Your account is not verified yet. Please wait for admin approval.";
            header("Location: SignUp_LogIn_Form.html");
            exit();
        }

        // Store user information in the session
        $_SESSION['user_id'] = $userData['id'];
        $_SESSION['username'] = $userData['username'];
        $_SESSION['role'] = $userData['role'];

        header("Location: index.php");
        exit();
    } else {
        $_SESSION['error'] = "Invalid username or password.";
        header("Location: SignUp_LogIn_Form.html");
        exit();
    }
}
?>