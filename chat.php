<?php
session_start();
if (!isset($_SESSION['username'])) {
  // Redirect to login or block access
  echo "Access denied. Please log in.";
  exit;
}
$username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>EventaChat - Event-Specific Chat</title>
  <link rel="stylesheet" href="styles.css">
  <style>
    body {
      margin: 0;
      font-family: 'Georgia', cursive;
      background-color: #fffaf0;
      color: #2d2d2d;
    }

    .chat-container {
      max-width: 800px;
      margin: 50px auto;
      background-color: #ffffff;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .chat-header {
      text-align: center;
      margin-bottom: 20px;
    }

    .chat-header h2 {
      font-size: 36px;
      color: #66a1ff;
    }

    .chat-header p {
      font-size: 18px;
      color: #333;
    }

    #chat-box {
      height: 300px;
      width: 100%;
      border: 1px solid #ccc;
      padding: 10px;
      overflow-y: scroll;
      margin-bottom: 20px;
      background-color: #f9f9f9;
      border-radius: 5px;
    }

    .message {
      margin: 5px 0;
      padding: 5px;
      border-bottom: 1px solid #ddd;
    }

    .message strong {
      color: #007bff;
    }

    .chat-input {
      display: flex;
      gap: 10px;
    }

    .chat-input input {
      flex: 1;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 5px;
      font-size: 16px;
    }

    .chat-input button {
      padding: 10px 20px;
      background-color: #66a1ff;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      font-size: 16px;
    }

    .chat-input button:hover {
      background-color: #4d8ae6;
    }

    .back-button {
      display: inline-block;
      margin-top: 20px;
      padding: 10px 20px;
      background-color: #66a1ff;
      color: white;
      text-decoration: none;
      border-radius: 5px;
      font-size: 16px;
    }

    .back-button:hover {
      background-color: #4d8ae6;
    }
  </style>
</head>
<body>

  <div class="chat-container">
    <div class="chat-header">
      <h2>Welcome to EventaChat</h2>
      <p>Chat for Event: <span id="event-name"></span></p>
    </div>

    <div id="chat-box"></div>

    <div class="chat-input">
      <input type="text" id="message" placeholder="Type a message..." />
      <button onclick="sendMessage()">Send</button>
    </div>

    <a href="index.php" class="back-button">Back to Home</a>
  </div>

  <!-- Firebase App -->
  <script src="https://www.gstatic.com/firebasejs/10.5.2/firebase-app-compat.js"></script>
  <script src="https://www.gstatic.com/firebasejs/10.5.2/firebase-database-compat.js"></script>

  <script>
    // Your Firebase configuration
    const firebaseConfig = {
      apiKey: "AIzaSyAKRoykFoWGNsEqbQB4c-hqP6FlZlxvj8o",
      authDomain: "eventachat.firebaseapp.com",
      databaseURL: "https://eventachat-default-rtdb.firebaseio.com",
      projectId: "eventachat",
      storageBucket: "eventachat.firebasestorage.app",
      messagingSenderId: "105102187806",
      appId: "1:105102187806:web:2be9da2f6689fd2d88fd0f"
    };

    firebase.initializeApp(firebaseConfig);
    const db = firebase.database();

    // Get event name from URL and decode
    const urlParams = new URLSearchParams(window.location.search);
    const eventRaw = urlParams.get("event") || "defaultEvent";
    const eventName = decodeURIComponent(eventRaw.replace(/\+/g, ' '));
    document.getElementById("event-name").textContent = eventName;

    // Set PHP session username into JavaScript
    const username = <?php echo json_encode($username); ?>;

    function sendMessage() {
      const message = document.getElementById("message").value.trim();
      if (!message) return;

      db.ref(`events/${eventName}/messages`).push({
        user: username,
        text: message,
        timestamp: Date.now()
      });

      document.getElementById("message").value = "";
    }

    db.ref(`events/${eventName}/messages`).on("child_added", function(snapshot) {
      const msg = snapshot.val();
      const msgElement = document.createElement("div");
      msgElement.className = "message";
      msgElement.innerHTML = `<strong>${msg.user}</strong>: ${msg.text}`;
      const chatBox = document.getElementById("chat-box");
      chatBox.appendChild(msgElement);
      chatBox.scrollTop = chatBox.scrollHeight;
    });
  </script>

</body>
</html>
