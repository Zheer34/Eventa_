<?php
session_start();

$host = 'localhost';
$port = 3306;
$dbname = 'eventa';
$username = 'root';
$password = '12345';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$event_id = $_GET['event_id'] ?? null;
if (!$event_id) {
    die("Event ID is required.");
}

$stmt = $pdo->prepare("SELECT * FROM events WHERE id = :id");
$stmt->execute([':id' => $event_id]);
$event = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$event) {
    die("Event not found.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'] ?? null;
    if (!$user_id) {
        die("You must be logged in to make a payment.");
    }

    $cardholderName = $_POST['cardholder_name'] ?? '';
    $cardNumber = $_POST['card_number'] ?? '';
    $expiryDate = $_POST['expiry_date'] ?? '';
    $cvv = $_POST['cvv'] ?? '';

    // Validate credit card details
    if (strlen($cardNumber) !== 16 || !ctype_digit($cardNumber)) {
        $error = "Invalid credit card number.";
    } elseif (!preg_match('/^\d{2}\/\d{2}$/', $expiryDate)) {
        $error = "Invalid expiration date format. Use MM/YY.";
    } elseif (strlen($cvv) !== 3 || !ctype_digit($cvv)) {
        $error = "Invalid CVV.";
    } else {
        $amount = $event['price'];

        // Insert payment record
        $stmt = $pdo->prepare("INSERT INTO payments (user_id, event_id, amount) VALUES (:user_id, :event_id, :amount)");
        if ($stmt->execute([':user_id' => $user_id, ':event_id' => $event_id, ':amount' => $amount])) {
            // Add user to event participants
            $stmt = $pdo->prepare("INSERT INTO event_participants (user_id, event_id) VALUES (:user_id, :event_id)");
            $stmt->execute([':user_id' => $user_id, ':event_id' => $event_id]);

            // Redirect to my_events.php with a confirmation message
            header("Location: my_events.php?payment_success=1");
            exit;
        } else {
            $error = "Payment failed. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment for <?php echo htmlspecialchars($event['title']); ?></title>
    <link rel="stylesheet" href="styles.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #66a1ff 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            color: white;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .payment-wrapper {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        .event-summary {
            background: rgba(255,255,255,0.95);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
        }

        .event-summary h3 {
            color: #333;
            margin-bottom: 20px;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .event-info {
            margin-bottom: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
            border-left: 4px solid #66a1ff;
        }

        .event-info strong {
            color: #333;
            display: block;
            margin-bottom: 5px;
        }

        .price-highlight {
            background: linear-gradient(135deg, #66a1ff 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            margin-top: 20px;
        }

        .price-highlight .amount {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .payment-form {
            background: rgba(255,255,255,0.95);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
        }

        .payment-form h3 {
            color: #333;
            margin-bottom: 25px;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-group input {
            width: 100%;
            padding: 15px;
            border: 2px solid #e1e5e9;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }

        .form-group input:focus {
            outline: none;
            border-color: #66a1ff;
            box-shadow: 0 0 0 3px rgba(102, 161, 255, 0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .pay-button {
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, #00b894 0%, #00a085 100%);
            color: white;
            border: none;
            border-radius: 15px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .pay-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 184, 148, 0.3);
        }

        .error-msg {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
            color: white;
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nav-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
        }

        .nav-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            border: 2px solid rgba(255,255,255,0.3);
            padding: 12px 24px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .nav-btn:hover {
            background: rgba(255,255,255,0.3);
            border-color: rgba(255,255,255,0.5);
            transform: translateY(-2px);
        }

        .security-info {
            background: rgba(255,255,255,0.1);
            border-radius: 15px;
            padding: 20px;
            margin-top: 20px;
            text-align: center;
            color: white;
        }

        .security-info i {
            font-size: 1.5rem;
            margin-bottom: 10px;
            color: #00b894;
        }

        @media (max-width: 768px) {
            .payment-wrapper {
                grid-template-columns: 1fr;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .header h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class='bx bxs-credit-card'></i> Payment</h1>
            <p>Secure payment for your event registration</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="error-msg">
                <i class='bx bx-error-circle'></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <div class="payment-wrapper">
            <div class="event-summary">
                <h3><i class='bx bxs-calendar-event'></i> Event Summary</h3>
                
                <div class="event-info">
                    <strong>Event Title</strong>
                    <?php echo htmlspecialchars($event['title']); ?>
                </div>
                
                <div class="event-info">
                    <strong>Date & Time</strong>
                    <?php echo date('F j, Y', strtotime($event['date'])); ?> at <?php echo date('g:i A', strtotime($event['time'])); ?>
                </div>
                
                <div class="event-info">
                    <strong>Location</strong>
                    <?php echo htmlspecialchars($event['location']); ?>
                </div>
                
                <?php if (!empty($event['description'])): ?>
                <div class="event-info">
                    <strong>Description</strong>
                    <?php echo htmlspecialchars(substr($event['description'], 0, 150)) . (strlen($event['description']) > 150 ? '...' : ''); ?>
                </div>
                <?php endif; ?>

                <div class="price-highlight">
                    <div class="amount">$<?php echo number_format($event['price'], 2); ?></div>
                    <div>Total Amount</div>
                </div>
            </div>

            <div class="payment-form">
                <h3><i class='bx bxs-lock-alt'></i> Payment Details</h3>
                
                <form method="post">
                    <div class="form-group">
                        <label for="cardholder_name">
                            <i class='bx bx-user'></i>
                            Cardholder Name
                        </label>
                        <input type="text" id="cardholder_name" name="cardholder_name" placeholder="Enter full name as on card" required>
                    </div>

                    <div class="form-group">
                        <label for="card_number">
                            <i class='bx bxs-credit-card'></i>
                            Card Number
                        </label>
                        <input type="text" id="card_number" name="card_number" placeholder="1234 5678 9012 3456" maxlength="19" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="expiry_date">
                                <i class='bx bx-calendar'></i>
                                Expiry Date
                            </label>
                            <input type="text" id="expiry_date" name="expiry_date" placeholder="MM/YY" maxlength="5" required>
                        </div>
                        <div class="form-group">
                            <label for="cvv">
                                <i class='bx bx-shield'></i>
                                CVV
                            </label>
                            <input type="text" id="cvv" name="cvv" placeholder="123" maxlength="3" required>
                        </div>
                    </div>

                    <button type="submit" class="pay-button">
                        <i class='bx bx-credit-card'></i>
                        Pay $<?php echo number_format($event['price'], 2); ?>
                    </button>
                </form>
            </div>
        </div>

        <div class="security-info">
            <i class='bx bxs-shield-check'></i>
            <p><strong>Secure Payment</strong></p>
            <p>Your payment information is encrypted and secure. We never store your credit card details.</p>
        </div>

        <div class="nav-buttons">
            <a href="event_details.php?id=<?php echo $event_id; ?>" class="nav-btn">
                <i class='bx bx-arrow-back'></i>
                Back to Event
            </a>
            <a href="events.php" class="nav-btn">
                <i class='bx bx-home'></i>
                All Events
            </a>
        </div>
    </div>

    <script>
        // Format card number input
        document.getElementById('card_number').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s/g, '').replace(/[^0-9]/gi, '');
            let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
            if (formattedValue.length <= 19) {
                e.target.value = formattedValue;
            }
        });

        // Format expiry date input
        document.getElementById('expiry_date').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
            e.target.value = value;
        });

        // Only allow numbers for CVV
        document.getElementById('cvv').addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/\D/g, '');
        });
    </script>
</body>
</html>