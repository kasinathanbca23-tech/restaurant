<?php
session_start();
require_once "config.php";  // Database connection

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch customer info
$stmt = $conn->prepare("SELECT id, customerId, first_name, last_name, email, phone FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
} else {
    // If user not found in database, destroy session and redirect to login
    session_destroy();
    header("Location: login.php");
    exit();
}
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile - Restaurant</title>
    <style>
        body {font-family: Arial, sans-serif; margin: 0; background: #f4f6f9;}
        .header {background: linear-gradient(135deg, #0a1117ff, #070808dc); color: white; padding: 20px; text-align: center; position: relative;}
        .profile-container {display: flex; width: 90%; margin: 30px auto;}
        .sidebar {width: 25%; background: #fff; border-radius: 12px; padding: 20px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);}
        .sidebar h3 {margin-top: 0; color: #003366;}
        .sidebar p {margin: 8px 0;}
        .sidebar .logout {display: block; margin-top: 15px; padding: 10px; text-align: center; background: #d9534f; color: white; border-radius: 6px; text-decoration: none;}
        .sidebar .logout:hover {background: #c9302c;}
        .main-content {flex: 1; margin-left: 20px;}
        .card {background: #fff; padding: 20px; margin-bottom: 20px; border-radius: 12px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);}
        .card h3 {margin-top: 0; color: #003366;}
        .card a {
            display: inline-block;
            margin-top: 10px;
            padding: 10px 16px;
            background: linear-gradient(135deg, #003366, #0055a5);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            transition: background 0.3s;
        }
        .card a:hover {
            background: linear-gradient(135deg, #0055a5, #0077cc);
            color: white;
        }
        .welcome-message {
            background: white;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #003366;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 20px;
        }
        .back-to-home {
            position: absolute;
            top: 20px;
            left: 20px;
            display: flex;
            align-items: center;
            color: white;
            text-decoration: none;
            font-size: 16px;
            padding: 8px 12px;
            border-radius: 5px;
            background-color: rgba(255, 255, 255, 0.1);
            transition: background-color 0.3s ease;
        }
        .back-to-home:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }
        .back-to-home i {
            margin-right: 8px;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="header">
        <a href="home.php" class="back-to-home">
            <i class="fas fa-arrow-left"></i> Home
        </a>
        <h1>Welcome, <?= htmlspecialchars($user['first_name'] . " " . $user['last_name']); ?>!</h1>
        <p>Your restaurant profile dashboard</p>
    </div>

    <div class="profile-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div style="text-align: center; margin-bottom: 15px;">
                <div class="user-avatar">
                    <?= strtoupper(substr($user['first_name'], 0, 1)) ?>
                </div>
            </div>
            <h3>My Information</h3>
            <p><b>Customer ID:</b> <?= htmlspecialchars($user['customerId']); ?></p>
            <p><b>Name:</b> <?= htmlspecialchars($user['first_name'] . " " . $user['last_name']); ?></p>
            <p><b>Email:</b> <?= htmlspecialchars($user['email']); ?></p>
            <p><b>Phone:</b> <?= htmlspecialchars($user['phone']); ?></p>
            <a href="logout.php" class="logout">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="welcome-message">
                <div>
                    <h3>Hello, <?= htmlspecialchars($user['first_name']); ?>!</h3>
                    <p>What would you like to do today?</p>
                </div>
                <div class="user-avatar" style="margin-left: 20px;">
                    <?= strtoupper(substr($user['first_name'], 0, 1)) ?>
                </div>
            </div>

            <div class="card">
                <h3><i class="fas fa-calendar-alt"></i> Reserve a Table</h3>
                <p>Book your favorite table at your preferred time.</p>
                <a href="reservation.php">Reserve Now</a>
            </div>

            <div class="card">
                <h3><i class="fas fa-utensils"></i> Order Food</h3>
                <p>Browse our menu and order food directly from your profile.</p>
                <a href="menu.php">Order Now</a>
            </div>

            <div class="card">
                <h3><i class="fas fa-history"></i> My Order History</h3>
                <p>See all your past orders and reorder your favorites.</p>
                <a href="customer_order_history.php">View Orders</a>
            </div>

            <div class="card">
                <h3><i class="fas fa-comment-alt"></i> Feedback</h3>
                <p>Share your experience with us to help improve our service.</p>
                <a href="feedback.php">Give Feedback</a>
            </div>
        </div>
    </div>
</body>
</html>