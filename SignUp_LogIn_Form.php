<?php
session_start();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login/Signup Form</title>
    <link rel="stylesheet" href="SignUp_LogIn_Form.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
  <body>

    <!-- credits to the writter @leonam-silva-de-souza -->
      <div class="container">
          <div class="form-box login">
              <form action="login.php" method="POST">
                  <h1>Login</h1>
                  <!-- Display error message -->
                  <?php if (isset($_SESSION['error'])): ?>
                      <p class="error-message" style="color: red;"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
                  <?php endif; ?>
                  <div class="input-box">
                      <input type="text" name="username" placeholder="Username" required>
                      <i class='bx bxs-user'></i>
                  </div>
                  <div class="input-box">
                      <input type="password" name="password" placeholder="Password" required>
                      <i class='bx bxs-lock-alt' ></i>
                  </div>
                 
                  <button type="submit" class="btn">Login</button>
                  
              </form>
          </div>

          <div class="form-box register">
              <form action="register.php" method="POST" enctype="multipart/form-data">
                  <h1>Registration</h1>
                  <div class="input-box">
                      <input type="text" name="username" placeholder="Username" required>
                      <i class='bx bxs-user'></i>
                  </div>
                 
                  <div class="input-box">
                      <input type="password" name="password" placeholder="Password" required>
                      <i class='bx bxs-lock-alt' ></i>
                  </div>

                  <div class="input-box">
                <label for="role" class="form-label">Role:</label>
                <select id="role" name="role" class="form-input" required onchange="toggleOrganizerFields()">
                    <option value="user">User</option>
                    <option value="event_organizer">Event Organizer</option>
                </select>
            </div>
            
            <!-- Organizer-specific fields -->
            <div id="organizer-fields" style="display: none;">
                <div class="input-box">
                    <input type="text" name="full_name" placeholder="Full Name">
                    <i class='bx bxs-user-detail'></i>
                </div>
                <div class="input-box">
                    <input type="text" name="organization" placeholder="Organization Name">
                    <i class='bx bxs-buildings'></i>
                </div>
                <div class="input-box">
                    <textarea name="past_experience" placeholder="Describe your past experiences" rows="2"></textarea>
                </div>
                <div class="input-box">
                    <label for="cv_file" style="display: block; margin-bottom: 3px; color: #666; font-size: 12px;">Upload CV (PDF only):</label>
                    <input type="file" name="cv_file" id="cv_file" accept=".pdf" style="padding: 6px; border: 2px dashed #ccc; border-radius: 5px; width: 100%; box-sizing: border-box; font-size: 11px; background-color: #f9f9f9;">
                </div>
            </div>
            
                  <button type="submit" class="btn">Register</button>

                  
                 
              </form>
          </div>

          <div class="toggle-box">
              <div class="toggle-panel toggle-left">
                  <h1>Hello, Welcome!</h1>
                  <p>Don't have an account?</p>
                  <button class="btn register-btn">Register</button>
              </div>

              <div class="toggle-panel toggle-right">
                  <h1>Welcome Back!</h1>
                  <p>Already have an account?</p>
                  <button class="btn login-btn">Login</button>
              </div>
          </div>
      </div>

      <script src="SignUp_LogIn_Form.js"></script>
  </body>
</html>