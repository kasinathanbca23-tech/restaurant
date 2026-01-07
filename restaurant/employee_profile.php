<?php
session_start();
require_once "config.php"; // Database connection

// Check if user is logged in and is an employee
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employee') {
    // If not logged in or not an employee, redirect to login page
    header("Location: login.php");
    exit();
}

$employee_id = $_SESSION['user_id'];

// Fetch employee info from the database
// Assuming your 'employees' table has columns like employee_id, name, email, phone
$stmt = $conn->prepare("SELECT employee_id, name, email, phone FROM employees WHERE employee_id = ?");
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $employee = $result->fetch_assoc();
} else {
    // If employee not found in database, destroy session and redirect to login
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
    <title>Employee Profile - Restaurant</title>
    <style>
        body {font-family: Arial, sans-serif; margin: 0; background: #f4f6f9;}
        .header {background: linear-gradient(135deg, #28a745, #218838); /* Green gradient */ color: white; padding: 20px; text-align: center; position: relative;}
        .profile-container {display: flex; width: 90%; max-width: 1000px; margin: 30px auto;}
        .sidebar {width: 25%; background: #fff; border-radius: 12px; padding: 20px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);}
        .sidebar h3 {margin-top: 0; color: #28a745;}
        .sidebar p {margin: 8px 0;}
        .sidebar .logout {display: block; margin-top: 15px; padding: 10px; text-align: center; background: #d9534f; color: white; border-radius: 6px; text-decoration: none;}
        .sidebar .logout:hover {background: #c9302c;}
        .main-content {flex: 1; margin-left: 20px;}
        .card {background: #fff; padding: 20px; margin-bottom: 20px; border-radius: 12px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);}
        .card h3 {margin-top: 0; color: #28a745;}
        .card a {
            display: inline-block;
            margin-top: 10px;
            padding: 10px 16px;
            background: linear-gradient(135deg, #28a745, #218838); /* Green gradient */
            color: white;
            text-decoration: none;
            border-radius: 6px;
            transition: background 0.3s;
        }
        .card a:hover {
            background: linear-gradient(135deg, #218838, #1e7e34); /* Darker green gradient */
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
            background: #28a745; /* Green */
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 20px;
        }
        .back-to-dashboard {
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
        .back-to-dashboard:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }
        .back-to-dashboard i {
            margin-right: 8px;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="header">
        <a href="kitchen.php" class="back-to-dashboard">
            <i class="fas fa-arrow-left"></i> Back to Kitchen Dashboard
        </a>
        <h1>Welcome, <?= htmlspecialchars($employee['name']); ?>!</h1>
        <p>Your employee profile dashboard</p>
    </div>

    <div class="profile-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div style="text-align: center; margin-bottom: 15px;">
                <div class="user-avatar">
                    <?= strtoupper(substr($employee['name'], 0, 1)) ?>
                </div>
            </div>
            <h3>My Information</h3>
            <p><b>Employee ID:</b> <?= htmlspecialchars($employee['employee_id']); ?></p>
            <p><b>Name:</b> <?= htmlspecialchars($employee['name']); ?></p>
            <p><b>Email:</b> <?= htmlspecialchars($employee['email']); ?></p>
            <p><b>Phone:</b> <?= htmlspecialchars($employee['phone']); ?></p>
            <a href="logout.php" class="logout">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="welcome-message">
                <div>
                    <h3>Hello, <?= htmlspecialchars(explode(' ', $employee['name'])[0]); ?>!</h3> <!-- Use first name -->
                    <p>Ready to manage orders?</p>
                </div>
                <div class="user-avatar" style="margin-left: 20px;">
                    <?= strtoupper(substr($employee['name'], 0, 1)) ?>
                </div>
            </div>

            <div class="card">
                <h3><i class="fas fa-clipboard-list"></i> Manage Orders</h3>
                <p>View and update the status of assigned customer orders.</p>
                <a href="kitchen.php">Go to Kitchen Dashboard</a>
            </div>

            <div class="card">
                <h3><i class="fas fa-history"></i> My Order Assignments History</h3>
                <p>Review your past assigned orders and their completion status.</p>
                <a href="customer_order_history.php">View Assignments</a>
            </div>

            <!-- You can add more employee-specific cards here -->
            <div class="card">
                <h3><i class="fas fa-user-edit"></i> Update Profile</h3>
                <p>Update your personal contact information.</p>
                <a href="employee_update_profile.php">Edit Profile</a> <!-- Create this page later -->
            </div>
        </div>
    </div>
</body>
</html>