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
    $fullName = $_POST['full_name'] ?? null;
    $organization = $_POST['organization'] ?? null;
    $pastExperience = $_POST['past_experience'] ?? null;

    // Check username and password length
    if (strlen($user) > 15) {
        $_SESSION['error'] = "Username cannot exceed 15 characters.";
        header("Location: SignUp_LogIn_Form.php");
        exit();
    }

    if (strlen($pass) > 20) {
        $_SESSION['error'] = "Password cannot exceed 20 characters.";
        header("Location: SignUp_LogIn_Form.php");
        exit();
    }

    // Check if username already exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$user]);
    if ($stmt->fetch()) {
        $_SESSION['error'] = "Username already taken.";
        header("Location: SignUp_LogIn_Form.php");
        exit();
    }

    // Hash the password
    $hashedPass = hash('sha256', $pass);

    // Set verified status based on role
    $verified = ($role === 'event_organizer') ? 'no' : 'yes';

    // Insert new user into database
    $stmt = $pdo->prepare("INSERT INTO users (username, password, role, verified, full_name, organization, past_experience) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$user, $hashedPass, $role, $verified, $fullName, $organization, $pastExperience])) {
        $_SESSION['success'] = "Registration successful. Please log in.";
        header("Location: SignUp_LogIn_Form.php");
        exit();
    } else {
        $_SESSION['error'] = "Registration failed. Please try again.";
        header("Location: SignUp_LogIn_Form.php");
        exit();
    }
}
?>