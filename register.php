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

    // Handle CV file upload for event organizers
    $cvPath = null;
    if ($role === 'event_organizer' && isset($_FILES['cv_file']) && $_FILES['cv_file']['error'] == 0) {
        $targetDir = "uploads/cv/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }
        
        $fileInfo = pathinfo($_FILES["cv_file"]["name"]);
        $fileExtension = strtolower($fileInfo['extension']);
        
        // Validate file type
        if ($fileExtension !== 'pdf') {
            $_SESSION['error'] = "Only PDF files are allowed for CV upload.";
            header("Location: SignUp_LogIn_Form.php");
            exit();
        }
        
        // Validate file size (5MB max)
        if ($_FILES["cv_file"]["size"] > 5242880) {
            $_SESSION['error'] = "CV file size must be less than 5MB.";
            header("Location: SignUp_LogIn_Form.php");
            exit();
        }
        
        $fileName = uniqid() . '_cv_' . $user . '.pdf';
        $targetFilePath = $targetDir . $fileName;
        
        if (move_uploaded_file($_FILES["cv_file"]["tmp_name"], $targetFilePath)) {
            $cvPath = $targetFilePath;
        } else {
            $_SESSION['error'] = "Failed to upload CV file.";
            header("Location: SignUp_LogIn_Form.php");
            exit();
        }
    }

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
    $stmt = $pdo->prepare("INSERT INTO users (username, password, role, verified, full_name, organization, past_experience, cv_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$user, $hashedPass, $role, $verified, $fullName, $organization, $pastExperience, $cvPath])) {
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