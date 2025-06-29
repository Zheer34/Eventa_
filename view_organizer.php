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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organizer Details - <?php echo htmlspecialchars($user['username']); ?></title>
    <link rel="stylesheet" href="styles.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #66a1ff, #4d8ae6);
            color: white;
            padding: 30px;
            text-align: center;
            position: relative;
        }
        
        .header h1 {
            margin: 0;
            font-size: 2.5rem;
            font-weight: 600;
        }
        
        .header .username {
            font-size: 1.2rem;
            margin-top: 10px;
            opacity: 0.9;
        }
        
        .status-badge {
            position: absolute;
            top: 20px;
            right: 20px;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-verified {
            background-color: #28a745;
            color: white;
        }
        
        .status-pending {
            background-color: #ffc107;
            color: #212529;
        }
        
        .content {
            padding: 40px;
        }
        
        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            border-left: 4px solid #28a745;
            font-weight: 500;
        }
        
        .success-message i {
            margin-right: 10px;
        }
        
        .info-grid {
            display: grid;
            gap: 25px;
            margin-bottom: 30px;
        }
        
        .info-item {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid #66a1ff;
        }
        
        .info-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .info-value {
            color: #212529;
            font-size: 1.1rem;
            line-height: 1.5;
        }
        
        .cv-section {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            padding: 25px;
            border-radius: 10px;
            text-align: center;
            border: 2px dashed #dee2e6;
        }
        
        .cv-available {
            border-color: #28a745;
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
        }
        
        .cv-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background-color: #66a1ff;
            color: white;
            padding: 12px 25px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 161, 255, 0.3);
        }
        
        .cv-btn:hover {
            background-color: #4d8ae6;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 161, 255, 0.4);
        }
        
        .no-cv {
            color: #6c757d;
            font-style: italic;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
            flex-wrap: wrap;
        }
        
        .btn-primary {
            background-color: #28a745;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        
        .btn-primary:hover {
            background-color: #218838;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        
        .btn-secondary:hover {
            background-color: #5a6268;
            transform: translateY(-2px);
        }
        
        @media (max-width: 768px) {
            .container {
                margin: 10px;
                border-radius: 10px;
            }
            
            .header {
                padding: 20px;
            }
            
            .header h1 {
                font-size: 2rem;
            }
            
            .content {
                padding: 20px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .status-badge {
                position: static;
                margin-top: 15px;
                display: inline-block;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class='bx bxs-user-detail'></i> Organizer Details</h1>
            <div class="username">@<?php echo htmlspecialchars($user['username']); ?></div>
            <div class="status-badge <?php echo $user['verified'] === 'yes' ? 'status-verified' : 'status-pending'; ?>">
                <?php echo $user['verified'] === 'yes' ? 'Verified' : 'Pending'; ?>
            </div>
        </div>
        
        <div class="content">
            <?php if (isset($successMessage)): ?>
                <div class="success-message">
                    <i class='bx bxs-check-circle'></i>
                    <?php echo htmlspecialchars($successMessage); ?>
                </div>
            <?php endif; ?>
            
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">
                        <i class='bx bxs-user'></i>
                        Full Name
                    </div>
                    <div class="info-value">
                        <?php echo htmlspecialchars($user['full_name'] ?: 'Not provided'); ?>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">
                        <i class='bx bxs-buildings'></i>
                        Organization
                    </div>
                    <div class="info-value">
                        <?php echo htmlspecialchars($user['organization'] ?: 'Not provided'); ?>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">
                        <i class='bx bxs-briefcase'></i>
                        Past Experience
                    </div>
                    <div class="info-value">
                        <?php echo $user['past_experience'] ? nl2br(htmlspecialchars($user['past_experience'])) : '<em style="color: #6c757d;">No experience details provided</em>'; ?>
                    </div>
                </div>
            </div>
            
            <div class="cv-section <?php echo (!empty($user['cv_path']) && file_exists($user['cv_path'])) ? 'cv-available' : ''; ?>">
                <div class="info-label" style="justify-content: center; margin-bottom: 15px;">
                    <i class='bx bxs-file-pdf'></i>
                    CV
                </div>
                <?php if (!empty($user['cv_path']) && file_exists($user['cv_path'])): ?>
                    <a href="cv_viewer.php?file=<?php echo urlencode($user['cv_path']); ?>" target="_blank" class="cv-btn">
                        <i class='bx bxs-download'></i>
                        View CV Document
                    </a>
                <?php else: ?>
                    <div class="no-cv">
                        <i class='bx bx-file' style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
                        No CV uploaded by this organizer
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="action-buttons">
                <?php if ($user['verified'] !== 'yes'): ?>
                    <form method="post" style="margin: 0;">
                        <button type="submit" name="verify" class="btn-primary">
                            <i class='bx bxs-check-shield'></i>
                            Verify Organizer
                        </button>
                    </form>
                <?php endif; ?>
                <a href="VerifyOrg.php" class="btn-secondary">
                    <i class='bx bx-arrow-back'></i>
                    Back to List
                </a>
            </div>
        </div>
    </div>
</body>
</html>