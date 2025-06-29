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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Event Organizers</title>
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
            max-width: 1000px;
            margin: 0 auto;
        }
        
        .header-card {
            background: linear-gradient(135deg, #66a1ff, #4d8ae6);
            color: white;
            padding: 40px;
            border-radius: 15px;
            text-align: center;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .header-card h1 {
            margin: 0;
            font-size: 2.5rem;
            font-weight: 600;
            margin-bottom: 15px;
        }
        
        .home-btn {
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 12px 25px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        
        .home-btn:hover {
            background-color: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }
        
        .content-card {
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .content-header {
            background-color: #f8f9fa;
            padding: 25px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .content-header h2 {
            margin: 0;
            color: #495057;
            font-size: 1.8rem;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .organizers-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .organizers-table thead {
            background: linear-gradient(135deg, #66a1ff, #4d8ae6);
            color: white;
        }
        
        .organizers-table th {
            padding: 20px;
            text-align: left;
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        .organizers-table td {
            padding: 20px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .organizers-table tbody tr:hover {
            background-color: #f8f9fa;
            transition: background-color 0.3s ease;
        }
        
        .username-cell {
            font-weight: 600;
            color: #495057;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .view-btn {
            background-color: #66a1ff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .view-btn:hover {
            background-color: #4d8ae6;
            transform: translateY(-2px);
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 40px;
            color: #6c757d;
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            color: #dee2e6;
        }
        
        .empty-state h3 {
            margin: 0 0 10px 0;
            color: #495057;
        }
        
        @media (max-width: 768px) {
            .container {
                margin: 10px;
            }
            
            .header-card {
                padding: 25px;
                border-radius: 10px;
            }
            
            .header-card h1 {
                font-size: 2rem;
            }
            
            .content-header {
                padding: 20px;
            }
            
            .organizers-table th,
            .organizers-table td {
                padding: 15px 10px;
            }
            
            .content-header h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-card">
            <h1><i class='bx bxs-shield-check'></i> Verify Event Organizers</h1>
            <a href="Index.php" class="home-btn">
                <i class='bx bx-home'></i>
                Back to Home
            </a>
        </div>
        
        <div class="content-card">
            <div class="content-header">
                <h2>
                    <i class='bx bxs-user-check'></i>
                    Pending Organizer Accounts
                </h2>
            </div>
            
            <?php if (count($organizers) === 0): ?>
                <div class="empty-state">
                    <i class='bx bx-check-circle'></i>
                    <h3>All Caught Up!</h3>
                    <p>There are no pending organizer accounts to verify at the moment.</p>
                </div>
            <?php else: ?>
                <table class="organizers-table">
                    <thead>
                        <tr>
                            <th><i class='bx bxs-user' style="margin-right: 8px;"></i>Username</th>
                            <th><i class='bx bxs-cog' style="margin-right: 8px;"></i>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($organizers as $organizer): ?>
                            <tr>
                                <td>
                                    <div class="username-cell">
                                        <i class='bx bxs-user-circle' style="color: #66a1ff; font-size: 1.2rem;"></i>
                                        <?php echo htmlspecialchars($organizer['username']); ?>
                                    </div>
                                </td>
                                <td>
                                    <form method="get" action="view_organizer.php" style="margin: 0;">
                                        <input type="hidden" name="user_id" value="<?php echo $organizer['id']; ?>">
                                        <button type="submit" class="view-btn">
                                            <i class='bx bx-show'></i>
                                            View Details
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>