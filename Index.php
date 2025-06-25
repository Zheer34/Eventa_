<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Eventa - Personal Brand Portfolio</title>
  <link rel="stylesheet" href="styles.css" />
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
</head>
<body>

  <header class="hero" style="position: relative;">
    <h1>Eventa</h1>
    <p>DO NOT MISS IMPORTANT EVENTS AGAIN</p>

    <a href="#contact" class="btn" style="position: absolute; top: 1rem; left: 1rem;">Get in touch</a>
    <a href="events.html" class="btn" style="margin-left: 10px;">Events</a>
    <a href="SignUp_LogIn_Form.php" id="auth-link" class="btn" style="position: absolute; top: 1rem; right: 1rem;">Login</a>
    <a href="my_events.php" class="btn">My Events</a>
    <a href="organizers.php" class="btn" style="margin-left: 10px;">Organizers</a>
    
    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
      <a href="VerifyOrg.php" class="btn">Verify Event Organizers</a>
      <a href="manage_users.php" class="btn">Manage Users</a>
    <?php endif; ?>

    <div class="notification-container" style="position: absolute; top: 1rem; left: 1rem;">
      <i id="notification-icon" class="bx bx-bell" style="font-size: 24px; cursor: pointer;"></i>
      <div id="notification-box" class="notification-box" style="display: none;">
        <h4>Notifications</h4>
        <ul id="notification-list"></ul>
      </div>
    </div>

    <!-- hero illustration -->
    <img src="Images/image0.jpg" alt="Event planning illustration" class="hero-img">
  </header>

  <section class="intro">
    <!-- intro illustration -->
    <img src="Images/image1.jpg" alt="User interface illustration" class="intro-img">
    <h2>User Friendly Interface</h2>
    <p>
      Eventa is going to organize and structure your events with a simple and interactive user interface. In a world full of distractions, we believe in special platforms to help you not miss important events.
    </p>
  </section>

  <section class="features">
    <h2>Features</h2>
    <div class="feature-boxes">
      <div class="feature">
        <img src="Images/image2.jpg" alt="Event management icon" class="feature-icon">
        <h3>Event Management</h3>
        <p>Make, update, remove and edit events; set locations; add details.</p>
      </div>
      <div class="feature">
        <img src="Images/image3.jpg" alt="Notifications icon" class="feature-icon">
        <h3>Notifications & Reminders</h3>
        <p>Email reminders and alerts before events.</p>
      </div>
      <div class="feature">
        <img src="Images/image4.jpg" alt="Ticketing icon" class="feature-icon">
        <h3>Ticketing</h3>
        <p>Free or paid ticket options with online payment and user tracking.</p>
      </div>
    </div>
  </section>

  <section class="mission-vision">
    <h2>Mission and Vision</h2>
    <div class="columns">
      <div class="column">
        <img src="Images/image5.jpg" alt="Mission icon" class="mission-icon">
        <h3>Mission</h3>
        <p>Provide a simple, structured platform for organizing events effectively.</p>
      </div>
      <div class="column">
        <img src="Images/image6.jpg" alt="Vision icon" class="vision-icon">
        <h3>Vision</h3>
        <p>Return valuable time to users and increase productivity in a distracted world.</p>
      </div>
    </div>
  </section>

  <section class="contact" id="contact">
    <h2>Contact</h2>
    <img src="Images/image7.jpg" alt="Productivity illustration" class="contact-icon">
    <p>Save your time & increase your productivity</p>
    <p><strong>Phone:</strong> (123) 456-7890</p>
    <p><strong>Email:</strong> hello@reallygreatsite.com</p>
    <div class="socials">
      <!-- Add icons/links here if needed -->
      <p>Socials coming soon</p>
    </div>
  </section>

  <footer>
    <p>&copy; 2025 Eventa. All rights reserved.</p>
  </footer>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const notificationIcon = document.getElementById('notification-icon');
      const notificationBox = document.getElementById('notification-box');
      const notificationList = document.getElementById('notification-list');

      notificationIcon.addEventListener('click', async () => {
        notificationBox.style.display = notificationBox.style.display === 'none' ? 'block' : 'none';

        if (notificationBox.style.display === 'block') {
          try {
            const response = await fetch('fetch_notifications.php');
            if (!response.ok) throw new Error('Failed to fetch notifications');
            const notifications = await response.json();
            notificationList.innerHTML = notifications.map(
              notif => `<li>${notif.message} <br><small>${new Date(notif.created_at).toLocaleString()}</small></li>`
            ).join('');
          } catch (error) {
            console.error('Error fetching notifications:', error);
            notificationList.innerHTML = '<li>Error loading notifications.</li>';
          }
        }
      });

      document.addEventListener('click', (event) => {
        if (!notificationBox.contains(event.target) && event.target !== notificationIcon) {
          notificationBox.style.display = 'none';
        }
      });
    });

    document.addEventListener('DOMContentLoaded', async () => {
      const authLink = document.getElementById('auth-link');

      try {
        const response = await fetch('get_user_info.php');
        const userInfo = await response.json();

        if (userInfo.username) {
          authLink.textContent = 'Log Out';
          authLink.href = 'logout.php';
        } else {
          authLink.textContent = 'Login';
          authLink.href = 'SignUp_LogIn_Form.php';
        }
      } catch (error) {
        console.error('Error fetching user info:', error);
      }
    });
  </script>

</body>
</html>
