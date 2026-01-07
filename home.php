<?php
session_start();
// Get name and role from session
$name = $_SESSION['name'] ?? null;
$role = $_SESSION['role'] ?? null;
// Set welcome message
$welcomeMsg = '';
if ($name) {
    if ($role === 'admin') {
        $welcomeMsg = "Welcome Admin";
    } elseif ($role === 'employee') {
        $welcomeMsg = "Welcome Employee";
    } else {
        $welcomeMsg = "Welcome, " . htmlspecialchars($name) . "!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Welcome to Our Restaurant</title>
  <!-- Font Awesome for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #fefefe;
      color: #333;
      line-height: 1.6;
    }
    header {
      background-color: #111;
      color: #fff;
      padding: 30px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
    }
    header h1 {
      font-size: 32px;
    }
    nav {
      display: flex;
      flex-wrap: wrap;
      gap: 15px;
      align-items: center;
    }
    nav a {
      color: #fff;
      text-decoration: none;
      font-weight: bold;
      font-size: 16px;
      transition: color 0.3s;
    }
    nav a:hover {
      color: #f39c12;
    }
    .welcome {
      font-size: 18px;
      font-weight: bold;
      color: #f39c12;
      margin-left: 15px;
    }
    .quote {
      background: #222;
      color: #fff;
      padding: 40px 20px;
      text-align: center;
      font-size: 24px;
      font-style: italic;
    }
    .gallery {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 20px;
      padding: 40px 20px;
      background: #fafafa;
    }
    .gallery img {
      width: 100%;
      height: 200px;
      object-fit: cover;
      border-radius: 12px;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }
    .gallery img:hover {
      transform: scale(1.03);
      box-shadow: 0 8px 16px rgba(0,0,0,0.3);
    }
    .about {
      background: #fff;
      padding: 40px 20px;
      text-align: center;
    }
    .about h2 {
      font-size: 28px;
      color: #222;
      margin-bottom: 20px;
    }
    .about p {
      max-width: 800px;
      margin: 0 auto 20px auto;
      color: #555;
      font-size: 16px;
    }
    /* Footer */
    footer {
      background: #111;
      color: #fff;
      padding: 30px 20px;
      text-align: center;
    }
    .footer-content {
      display: flex;
      flex-direction: column;
      gap: 20px;
      align-items: center;
    }
    .opening-hours, .contact-details {
      width: 100%;
      max-width: 500px;
    }
    .opening-hours h3, .contact-details h3 {
      margin-bottom: 10px;
      color: #f39c12;
    }
    .social-links {
      display: flex;
      gap: 20px;
      margin-top: 15px;
    }
    .social-links a {
      color: #fff;
      font-size: 24px;
      transition: color 0.3s;
    }
    .social-links a:hover {
      color: #f39c12;
    }
    @media (max-width: 600px) {
      header {
        flex-direction: column;
        text-align: center;
      }
      nav {
        justify-content: center;
        margin-top: 10px;
      }
      .welcome {
        margin-top: 10px;
        display: block;
      }
    }
  </style>
</head>
<body>
<header>
  <h1>Our Premium Restaurant</h1>
  <nav>
    <a href="menu.php">Menu</a>
    <a href="reservation.php">Reservation</a>
    <a href="feedback.php">Feedback</a>
    <?php if ($role === 'admin'): ?>
        <a href="admin_dashboard.php">Admin Dashboard</a>
        <a href="profile.php">Profile</a> <!-- Admin also gets a general profile -->
        <a href="logout.php">Logout</a>
    <?php elseif ($role === 'employee'): ?>
        <a href="employee_profile.php">Employee Profile</a>
        <a href="logout.php">Logout</a>
    <?php elseif ($name): /* This condition handles regular users */ ?>
        <a href="profile.php">Profile</a>
        <a href="logout.php">Logout</a>
    <?php else: ?>
        <a href="login.php">Login</a>
    <?php endif; ?>
    <?php if ($welcomeMsg): ?>
        <span class="welcome"><?= $welcomeMsg ?></span>
    <?php endif; ?>
  </nav>
</header>
<div class="quote">
  Good food is the foundation of genuine happiness
</div>
<div class="gallery">
  <img src="pizza.jpg" alt="Pizza Delight">
  <img src="b.jpg" alt="Grilled Chicken">
  <img src="pasta.jpg" alt="Creamy Pasta">
  <img src="burger (2).jpg" alt="Crispy Fries">
</div>
<div class="about">
  <h2>About Our Restaurant</h2>
  <p>We pride ourselves on crafting meals from the finest ingredients. From iconic classics to gourmet innovations, our dishes satisfy every craving with elegance and care.</p>
  <p>Join us for a warm, welcoming, and unforgettable dining experience. Whether a cozy lunch or a special evening, our passion for food shines in every plate.</p>
</div>
<footer>
  <div class="footer-content">
    <div class="opening-hours">
      <h3>Opening Hours</h3>
      <p>Mon-Fri: 11:00 AM - 10:00 PM</p>
      <p>Sat-Sun: 10:00 AM - 11:00 PM</p>
    </div>
    <div class="contact-details">
      <h3>Contact Us</h3>
      <p><i class="fas fa-map-marker-alt"></i> 123 Main Street, City, Country</p>
      <p><i class="fas fa-phone"></i> +91 85 4560 7890</p>
      <p><i class="fas fa-envelope"></i> premium@restaurant.com</p>
    </div>
    <div class="social-links">
      <a href="https://facebook.com/yourpage" target="_blank"><i class="fab fa-facebook"></i></a>
      <a href="https://instagram.com/yourpage" target="_blank"><i class="fab fa-instagram"></i></a>
      <a href="https://twitter.com/yourpage" target="_blank"><i class="fab fa-twitter"></i></a>
    </div>
  </div>
</footer>
</body>
</html>