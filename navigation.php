<?php
include_once 'check_session.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>JP 227 Dormitory</title>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/styles.css">
  <style>
    /* Navigation logo styling */
    .nav-brand {
      display: flex;
      align-items: center;
    }
    .nav-brand img.logo {
      max-height: 40px; /* Adjust the height as needed */
      margin-right: 10px; /* Spacing between the logo and the text */
    }
  </style>
</head>
<body>
  <nav>
    <div class="container">
      <a href="index.php" class="nav-brand">
        <img src="assets/logo.png" alt="Logo" class="logo">
        JP 227 Dormitory
      </a>
      <button class="menu-toggle" id="menuToggle">☰</button>
      <div class="nav-links" id="navLinks">
        <a href="index.php">Dashboard</a>
        <a href="tenants.php">Tenants</a>
        <a href="rooms.php">Rooms</a>
        <a href="users.php">Users</a>
        <a href="history.php">History</a>
        <a href="logout.php" id="logoutBtn">Logout</a>
      </div>
    </div>
  </nav>

  <script>
    const navLinks = document.querySelectorAll(".nav-links a");
    const currentPage = window.location.pathname.split("/").pop();

    navLinks.forEach(link => {
      if (link.getAttribute("href") === currentPage) {
        link.classList.add("active");
      } else {
        link.classList.remove("active");
      }
    });
  </script>
</body>
</html>
