<?php
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== 'admin') {
    header("Location: home.php");
    exit();
}

$name = $_SESSION["name"];
?>

<!DOCTYPE html>
<html>
<head>
  <title>Admin Dashboard</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f8f8f8;
      margin: 0;
      padding: 0;
    }

    .header {
      background-color: #343a40;
      color: white;
      padding: 15px;
      text-align: center;
    }

    .container {
      padding: 20px;
    }

    .card {
      background-color: white;
      border-radius: 8px;
      padding: 20px;
      margin-bottom: 20px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }

    a.button {
      display: inline-block;
      padding: 10px 15px;
      margin-top: 10px;
      background-color: #007bff;
      color: white;
      text-decoration: none;
      border-radius: 5px;
    }

    a.button:hover {
      background-color: #0056b3;
    }

    .back-home {
      position: absolute;
      left: 15px;
      top: 20px;
      display: inline-block;
      padding: 8px 15px;
      background-color: #000000ff;
      color: white;
      text-decoration: none;
      border-radius: 5px;
      font-weight: bold;
      margin-bottom: 20px;
    }

    .back-home:hover {
      background-color: #23272b;
    }
  </style>
</head>
<body>

<a href="home.php" class="back-home">← Back</a>
<div class="header">
  <h1>Admin Dashboard</h1>
  <p>Welcome, <?= htmlspecialchars($name) ?> (Admin)</p>
</div>

<div class="container">
  <div class="card">
    <h2>Manage Menu</h2>
    <p>Add, update, or delete food items from the menu.</p>
    <a href="admin_add_menu.php" class="button">Go to Menu Management</a>
  </div>

  <div class="card">
    <h2>View Orders</h2>
    <p>Check current and past orders placed by users.</p>
    <a href="kitchen.php" class="button">Go to Kitchen Orders</a>
  </div>

  <div class="card">
    <h2>Registered Users</h2>
    <p>View user information.</p>
    <a href="register.php" class="button">Go to User List</a>
  </div>

  <div class="card">
    <h2>Register Employee</h2>
    <p>Add a new employee to the system with employee role access.</p>
    <a href="employee.php" class="button">Register New Employee</a>
  </div>

  <div class="card">
    <h2>Manage Table Reservations</h2>
    <p>Add or manage tables for user reservations.</p>
    <a href="admin_add_table.php" class="button">Manage Tables</a>
  </div>

  <div class="card">
    <h2>View Customer Feedback</h2>
    <p>Check feedback submitted by customers.</p>
    <a href="view_feedback.php" class="button">View Feedback</a>
  </div>
  
   <div class="card">
    <h2>View payments</h2>
    <p>Check payments by customers.</p>
    <a href="payments.php" class="button">View Payments</a>
  </div>

  <div class="card">
    <a href="logout.php" class="button" style="background-color: red;">Logout</a>
  </div>
</div>

</body>
</html>
