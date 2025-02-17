<?php
include 'db_config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>JP 227 Dormitory - Login</title>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/login.css">
</head>
<body>
  <nav>
    <div class="container">
      <a href="/" class="nav-brand">JP 227 Dormitory</a>
    </div>
  </nav>

  <main>
    <div class="login-wrapper">
      <div class="login-form">
        <h3>Login</h3>
        <form id="loginForm" action="process/process_login.php" method="POST">
          <div class="form-group">
            <label for="email" class="form-label">Email</label>
            <input type="text" id="email" name="email" class="form-input" required placeholder="Enter your email">
          </div>
          <div class="form-group">
            <label for="password" class="form-label">Password</label>
            <input type="password" id="password" name="password" class="form-input" required placeholder="Enter your password">
          </div>
          <button type="submit" class="btn btn-primary full-width">Login</button>
        </form>
      </div>
      <div class="login-logo-container">
        <img src="assets/logo.png" alt="JP 227 Dormitory Logo" class="login-logo">
      </div>
    </div>
  </main>

  <script src="script.js"></script>
</body>
</html>
