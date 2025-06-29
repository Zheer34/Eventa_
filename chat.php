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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>EventaChat - Event-Specific Chat</title>
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
      max-width: 1000px;
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
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 15px;
    }

    .header p {
      font-size: 1.1rem;
      opacity: 0.9;
      margin: 0;
    }

    .event-info {
      background: rgba(255,255,255,0.1);
      border-radius: 15px;
      padding: 20px;
      margin-bottom: 20px;
      text-align: center;
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255,255,255,0.2);
    }

    .event-name {
      font-size: 1.3rem;
      font-weight: 600;
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
    }

    .chat-wrapper {
      background: rgba(255,255,255,0.95);
      border-radius: 20px;
      padding: 0;
      box-shadow: 0 15px 35px rgba(0,0,0,0.1);
      backdrop-filter: blur(10px);
      overflow: hidden;
    }

    .chat-header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 20px 25px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .chat-title {
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 1.2rem;
      font-weight: 600;
    }

    .online-indicator {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 0.9rem;
      opacity: 0.9;
    }

    .online-dot {
      width: 8px;
      height: 8px;
      background: #00b894;
      border-radius: 50%;
      animation: pulse 2s infinite;
    }

    @keyframes pulse {
      0% { opacity: 1; }
      50% { opacity: 0.5; }
      100% { opacity: 1; }
    }

    #chat-box {
      height: 400px;
      padding: 25px;
      overflow-y: auto;
      background: #f8f9fa;
      border-bottom: 1px solid #e9ecef;
    }

    .message {
      margin: 15px 0;
      display: flex;
      flex-direction: column;
      animation: fadeInUp 0.3s ease;
    }

    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(10px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .message-bubble {
      background: white;
      padding: 12px 18px;
      border-radius: 18px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      max-width: 70%;
      word-wrap: break-word;
    }

    .message.own .message-bubble {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      margin-left: auto;
    }

    .message-header {
      display: flex;
      align-items: center;
      gap: 8px;
      margin-bottom: 5px;
      font-size: 0.85rem;
      color: #6c757d;
    }

    .message.own .message-header {
      color: rgba(255,255,255,0.8);
    }

    .message-user {
      font-weight: 600;
      color: #667eea;
    }

    .message.own .message-user {
      color: rgba(255,255,255,0.9);
    }

    .message-time {
      font-size: 0.75rem;
      opacity: 0.7;
    }

    .message-text {
      line-height: 1.4;
    }

    .chat-input-wrapper {
      padding: 20px 25px;
      background: white;
      border-top: 1px solid #e9ecef;
    }

    .chat-input {
      display: flex;
      gap: 12px;
      align-items: center;
    }

    .chat-input input {
      flex: 1;
      padding: 15px 20px;
      border: 2px solid #e1e5e9;
      border-radius: 25px;
      font-size: 1rem;
      transition: all 0.3s ease;
      outline: none;
    }

    .chat-input input:focus {
      border-color: #667eea;
      box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .send-button {
      background: linear-gradient(135deg, #00b894 0%, #00a085 100%);
      color: white;
      border: none;
      width: 50px;
      height: 50px;
      border-radius: 50%;
      cursor: pointer;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.2rem;
    }

    .send-button:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(0, 184, 148, 0.4);
    }

    .send-button:disabled {
      background: #ccc;
      cursor: not-allowed;
      transform: none;
      box-shadow: none;
    }

    .nav-buttons {
      display: flex;
      justify-content: center;
      gap: 15px;
      margin-top: 20px;
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

    .typing-indicator {
      display: none;
      padding: 10px 20px;
      font-style: italic;
      color: #6c757d;
      font-size: 0.9rem;
    }

    .empty-chat {
      text-align: center;
      color: #6c757d;
      padding: 40px;
      font-style: italic;
    }

    .empty-chat i {
      font-size: 3rem;
      margin-bottom: 15px;
      display: block;
      opacity: 0.5;
    }

    /* Scrollbar styling */
    #chat-box::-webkit-scrollbar {
      width: 6px;
    }

    #chat-box::-webkit-scrollbar-track {
      background: #f1f1f1;
      border-radius: 3px;
    }

    #chat-box::-webkit-scrollbar-thumb {
      background: #c1c1c1;
      border-radius: 3px;
    }

    #chat-box::-webkit-scrollbar-thumb:hover {
      background: #a8a8a8;
    }

    @media (max-width: 768px) {
      .container {
        padding: 0 10px;
      }
      
      .header h1 {
        font-size: 2rem;
      }
      
      #chat-box {
        height: 300px;
        padding: 20px;
      }
      
      .message-bubble {
        max-width: 85%;
      }
      
      .chat-input input {
        padding: 12px 18px;
      }
      
      .send-button {
        width: 45px;
        height: 45px;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <h1>
        <i class='bx bxs-chat'></i>
        EventaChat
      </h1>
      <p>Real-time event discussions</p>
    </div>

    <div class="event-info">
      <div class="event-name">
        <i class='bx bxs-calendar-event'></i>
        <span id="event-name">Loading event...</span>
      </div>
    </div>

    <div class="chat-wrapper">
      <div class="chat-header">
        <div class="chat-title">
          <i class='bx bxs-message-dots'></i>
          Event Discussion
        </div>
        <div class="online-indicator">
          <div class="online-dot"></div>
          <span>Live Chat</span>
        </div>
      </div>

      <div id="chat-box">
        <div class="empty-chat">
          <i class='bx bx-message'></i>
          <p>No messages yet. Start the conversation!</p>
        </div>
      </div>

      <div class="typing-indicator" id="typing-indicator">
        Someone is typing...
      </div>

      <div class="chat-input-wrapper">
        <div class="chat-input">
          <input type="text" id="message" placeholder="Type your message..." />
          <button class="send-button" onclick="sendMessage()" id="send-btn">
            <i class='bx bx-send'></i>
          </button>
        </div>
      </div>
    </div>

    <div class="nav-buttons">
      <a href="events.php" class="nav-btn">
        <i class='bx bx-calendar'></i>
        Back to Events
      </a>
      <a href="index.php" class="nav-btn">
        <i class='bx bx-home'></i>
        Home
      </a>
    </div>
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
    let messageCount = 0;

    function sendMessage() {
      const messageInput = document.getElementById("message");
      const message = messageInput.value.trim();
      if (!message) return;

      const sendBtn = document.getElementById("send-btn");
      sendBtn.disabled = true;

      db.ref(`events/${eventName}/messages`).push({
        user: username,
        text: message,
        timestamp: Date.now()
      }).then(() => {
        messageInput.value = "";
        sendBtn.disabled = false;
        messageInput.focus();
      });
    }

    function formatTime(timestamp) {
      const date = new Date(timestamp);
      return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }

    function createMessageElement(msg) {
      const isOwnMessage = msg.user === username;
      
      const messageDiv = document.createElement("div");
      messageDiv.className = `message ${isOwnMessage ? 'own' : ''}`;
      
      messageDiv.innerHTML = `
        <div class="message-bubble">
          <div class="message-header">
            <span class="message-user">${msg.user}</span>
            <span class="message-time">${formatTime(msg.timestamp)}</span>
          </div>
          <div class="message-text">${msg.text}</div>
        </div>
      `;
      
      return messageDiv;
    }

    // Remove empty chat indicator
    function removeEmptyIndicator() {
      const emptyChat = document.querySelector('.empty-chat');
      if (emptyChat) {
        emptyChat.remove();
      }
    }

    db.ref(`events/${eventName}/messages`).on("child_added", function(snapshot) {
      const msg = snapshot.val();
      removeEmptyIndicator();
      
      const chatBox = document.getElementById("chat-box");
      const messageElement = createMessageElement(msg);
      chatBox.appendChild(messageElement);
      chatBox.scrollTop = chatBox.scrollHeight;
      
      messageCount++;
    });

    // Handle Enter key press
    document.getElementById("message").addEventListener("keypress", function(e) {
      if (e.key === "Enter") {
        sendMessage();
      }
    });

    // Auto-focus message input
    document.getElementById("message").focus();

    // Update send button state based on input
    document.getElementById("message").addEventListener("input", function(e) {
      const sendBtn = document.getElementById("send-btn");
      sendBtn.disabled = !e.target.value.trim();
    });

    // Initial button state
    document.getElementById("send-btn").disabled = true;
  </script>

</body>
</html>
