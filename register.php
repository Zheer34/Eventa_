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
    $role = $_POST['role'];

    // Check if username already exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$user]);
    if ($stmt->fetch()) {
        $_SESSION['error'] = "Username already taken.";
        header("Location: SignUp_LogIn_Form.html");
        exit();
    }

    // Hash the password
$hashedPass = hash('sha256', $pass);

// Set verified status based on role
if ($role === 'admin') {
    $verified = 'yes';
} elseif ($role === 'event_organizer') {
    $verified = 'no';
} else {
    $verified = 'yes';
}

    // Insert new user into database
    $stmt = $pdo->prepare("INSERT INTO users (username, password, role, verified) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$user, $hashedPass, $role, $verified])) {
        $_SESSION['success'] = "Registration successful. Please log in.";
        header("Location: SignUp_LogIn_Form.html");
        exit();
    } else {
        $_SESSION['error'] = "Registration failed. Please try again.";
        header("Location: SignUp_LogIn_Form.html");
        exit();
    }
}
?>