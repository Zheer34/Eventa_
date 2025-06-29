<?php
session_start();

// Database connection configuration
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

$message = '';
$action = $_POST['action'] ?? '';

function sanitizeInput($data) {
    return htmlspecialchars(trim($data));
}

if ($action === 'create' || $action === 'edit') {
    $id = $_POST['id'] ?? null;
    $title = sanitizeInput($_POST['title']);
    $description = sanitizeInput($_POST['description']);
    $date = $_POST['date'];
    $time = $_POST['time'];
    $location = sanitizeInput($_POST['location']);
    $category = sanitizeInput($_POST['category']);
    $visibility = $_POST['visibility'];
    $recurring = $_POST['recurring'] ?? 'no';

    // Handle image upload
// Handle image upload
$imagePath = '';
if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
    $targetDir = "uploads/";
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }
    $fileName = uniqid() . '_' . basename($_FILES["image"]["name"]); // Add unique ID to prevent conflicts
    $targetPath = $targetDir . $fileName;
    
    if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetPath)) {
        $imagePath = $targetPath; // Store relative path
    } else {
        // Handle upload error
        $message = "Error uploading image.";
    }
}
    // Agenda, speakers, sponsors as JSON strings
    $agenda = json_encode(array_map('sanitizeInput', $_POST['agenda'] ?? []));
    $speakers = json_encode(array_map('sanitizeInput', $_POST['speakers'] ?? []));
    $sponsors = json_encode(array_map('sanitizeInput', $_POST['sponsors'] ?? []));
    $price = ($_POST['price_type'] === 'paid') ? ($_POST['price'] ?? 0) : 0;

    if ($action === 'create') {
        $organizer_username = $_SESSION['username'] ?? 'unknown';
        $sql = "INSERT INTO events (title, description, date, time, location, image, category, visibility, recurring, agenda, speakers, sponsors, organizer_username, price)
                VALUES (:title, :description, :date, :time, :location, :image, :category, :visibility, :recurring, :agenda, :speakers, :sponsors, :organizer_username, :price)";
        $stmt = $pdo->prepare($sql);
        $params = [
            ':title' => $title,
            ':description' => $description,
            ':date' => $date,
            ':time' => $time,
            ':location' => $location,
            ':image' => $imagePath,
            ':category' => $category,
            ':visibility' => $visibility,
            ':recurring' => $recurring,
            ':agenda' => $agenda,
            ':speakers' => $speakers,
            ':sponsors' => $sponsors,
            ':organizer_username' => $organizer_username,
            ':price' => $price
        ];
        if ($stmt->execute($params)) {
            $message = "Event created successfully.";
            addNotification($pdo, "A new event '{$title}' has been created by {$organizer_username}.");
        } else {
            $message = "Error creating event.";
        }
    } elseif ($action === 'edit' && $id) {
        $sql = "UPDATE events SET title=:title, description=:description, date=:date, time=:time, location=:location, category=:category, visibility=:visibility, recurring=:recurring, agenda=:agenda, speakers=:speakers, sponsors=:sponsors, price=:price";
        if ($imagePath) {
            $sql .= ", image=:image";
        }
        $sql .= " WHERE id=:id";
        $stmt = $pdo->prepare($sql);
        $params = [
            ':title' => $title,
            ':description' => $description,
            ':date' => $date,
            ':time' => $time,
            ':location' => $location,
            ':category' => $category,
            ':visibility' => $visibility,
            ':recurring' => $recurring,
            ':agenda' => $agenda,
            ':speakers' => $speakers,
            ':sponsors' => $sponsors,
            ':price' => $price,
            ':id' => $id
        ];
        if ($imagePath) {
            $params[':image'] = $imagePath;
        }
        if ($stmt->execute($params)) {
            $message = "Event updated successfully.";
            addNotification($pdo, "The event '{$title}' has been updated.");
        } else {
            $message = "Error updating event.";
        }
    }
}

// If editing, fetch event data to prefill form
$editEvent = null;
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $editEvent = $stmt->fetch(PDO::FETCH_ASSOC);
}

function addNotification($pdo, $message) {
    $stmt = $pdo->prepare("INSERT INTO notifications (message) VALUES (:message)");
    $stmt->execute([':message' => $message]);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $editEvent ? 'Edit Event' : 'Create Event'; ?></title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="Login_Form.css">
    <style>
        body {
            background: #f4f8fb;
        }
        .event-form-card {
            max-width: 520px;
            margin: 40px auto;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(102,161,255,0.10);
            padding: 32px 36px 28px 36px;
            border: 1px solid #e3eaf5;
        }
        .event-form-card h2 {
            text-align: center;
            margin-bottom: 24px;
            color: #007bff;
            font-size: 2rem;
            font-weight: 600;
        }
        .event-form label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: #333;
            margin-top: 18px;
        }
        .event-form input[type="text"],
        .event-form input[type="date"],
        .event-form input[type="time"],
        .event-form input[type="file"],
        .event-form select,
        .event-form textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #c7d6ee;
            border-radius: 6px;
            font-size: 1rem;
            margin-bottom: 8px;
            background: #f7faff;
            transition: border-color 0.2s;
        }
        .event-form input[type="file"] {
            background: none;
            padding: 5px 0;
        }
        .event-form textarea {
            min-height: 60px;
            resize: vertical;
        }
        .event-form .form-row {
            display: flex;
            gap: 16px;
        }
        .event-form .form-row > div {
            flex: 1;
        }
        .event-form .btn {
            width: 48%;
            margin-top: 18px;
            margin-right: 2%;
            font-size: 1rem;
            padding: 10px 0;
            border-radius: 6px;
            border: none;
            background: #66a1ff;
            color: #fff;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        .event-form .btn:last-child {
            background: #e3eaf5;
            color: #007bff;
            margin-right: 0;
        }
        .event-form .btn:hover {
            background: #007bff;
            color: #fff;
        }
        .event-form .btn:last-child:hover {
            background: #dbe7fa;
            color: #0056b3;
        }
        .message {
            max-width: 520px;
            margin: 20px auto 0 auto;
            padding: 12px 18px;
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            border-radius: 8px;
            font-size: 1rem;
            text-align: center;
        }
        @media (max-width: 600px) {
            .event-form-card {
                padding: 18px 8px;
            }
            .event-form .form-row {
                flex-direction: column;
                gap: 0;
            }
            .event-form .btn {
                width: 100%;
                margin-right: 0;
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>
    <h2 style="text-align:center;"><?php echo $editEvent ? 'Edit Event' : 'Create Event'; ?></h2>
    <?php if ($message): ?>
        <div class="message"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <div class="event-form-card">
        <form class="event-form" method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="<?php echo $editEvent ? 'edit' : 'create'; ?>">
            <input type="hidden" name="id" value="<?php echo $editEvent['id'] ?? ''; ?>">

            <!-- Hidden fields for visibility and recurring -->
            <input type="hidden" name="visibility" value="public">
            <input type="hidden" name="recurring" value="no">

            <label for="title">Title</label>
            <input type="text" name="title" id="title" required value="<?php echo htmlspecialchars($editEvent['title'] ?? ''); ?>">

            <label for="description">Description</label>
            <textarea name="description" id="description" rows="3" required><?php echo htmlspecialchars($editEvent['description'] ?? ''); ?></textarea>

            <div class="form-row">
                <div>
                    <label for="date">Date</label>
                    <input type="date" name="date" id="date" required value="<?php echo htmlspecialchars($editEvent['date'] ?? ''); ?>">
                </div>
                <div>
                    <label for="time">Time</label>
                    <input type="time" name="time" id="time" required value="<?php echo htmlspecialchars($editEvent['time'] ?? ''); ?>">
                </div>
            </div>

            <label for="location">Location</label>
            <input type="text" name="location" id="location" required value="<?php echo htmlspecialchars($editEvent['location'] ?? ''); ?>">

            <label for="image">Image/Banner</label>
            <input type="file" name="image" id="image" accept="image/*">

            <div class="form-row">
                <div>
                    <label for="category">Category</label>
                    <input type="text" name="category" id="category" required value="<?php echo htmlspecialchars($editEvent['category'] ?? ''); ?>">
                </div>
            </div>

            <label>Agenda (one item per line)</label>
            <textarea name="agenda[]" id="agenda" rows="3" placeholder="Enter agenda items separated by new lines"><?php
                if (!empty($editEvent['agenda'])) {
                    $agendaItems = json_decode($editEvent['agenda'], true);
                    if (is_array($agendaItems)) {
                        echo htmlspecialchars(implode("\n", $agendaItems));
                    }
                }
            ?></textarea>

            <label>Speakers (one per line)</label>
            <textarea name="speakers[]" id="speakers" rows="3" placeholder="Enter speaker names separated by new lines"><?php
                if (!empty($editEvent['speakers'])) {
                    $speakerItems = json_decode($editEvent['speakers'], true);
                    if (is_array($speakerItems)) {
                        echo htmlspecialchars(implode("\n", $speakerItems));
                    }
                }
            ?></textarea>

            <label>Sponsors (one per line)</label>
            <textarea name="sponsors[]" id="sponsors" rows="3" placeholder="Enter sponsor names separated by new lines"><?php
                if (!empty($editEvent['sponsors'])) {
                    $sponsorItems = json_decode($editEvent['sponsors'], true);
                    if (is_array($sponsorItems)) {
                        echo htmlspecialchars(implode("\n", $sponsorItems));
                    }
                }
            ?></textarea>

            <label for="price">Is this event free or paid?</label>
            <select name="price_type" id="price_type" onchange="togglePriceInput()">
                <option value="free" <?php if (($editEvent['price'] ?? 0) == 0) echo 'selected'; ?>>Free</option>
                <option value="paid" <?php if (($editEvent['price'] ?? 0) > 0) echo 'selected'; ?>>Paid</option>
            </select>

            <div id="price_input" style="display: <?php echo (($editEvent['price'] ?? 0) > 0) ? 'block' : 'none'; ?>;">
                <label for="price">Ticket Price</label>
                <input type="number" name="price" id="price" step="0.01" min="0" value="<?php echo htmlspecialchars($editEvent['price'] ?? ''); ?>">
            </div>

            <script>
            function togglePriceInput() {
                const priceType = document.getElementById('price_type').value;
                const priceInput = document.getElementById('price_input');
                priceInput.style.display = priceType === 'paid' ? 'block' : 'none';
            }
            </script>

            <div class="form-row" style="margin-top: 10px;">
                <button type="submit" class="btn"><?php echo $editEvent ? 'Update Event' : 'Create Event'; ?></button>
                <button type="button" class="btn" onclick="window.location.href='events.php';">Back to Events</button>
            </div>
        </form>
    </div>
</body>
</html>
