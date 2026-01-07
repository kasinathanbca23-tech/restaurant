<?php
session_start(); // Start the session at the very beginning
require_once "config.php";  // Database connection

// If already logged in, redirect to appropriate page
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin_dashboard.php");
    } elseif ($_SESSION['role'] === 'employee') {
        header("Location: kitchen.php");
    } else {
        // Redirect regular users to index.php (home page) if already logged in
        header("Location: home.php"); 
    }
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];
    $selectedRole = $_POST["role"];

    // Check in users table for user or admin
    if ($selectedRole === 'user' || $selectedRole === 'admin') {
        $stmt = $conn->prepare("SELECT id, first_name, last_name, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password']) && $user['role'] === $selectedRole) {
                // Regenerate session ID to prevent session fixation
                session_regenerate_id();

                $_SESSION["user_id"] = $user['id'];
                $_SESSION["name"] = $user['first_name'] . " " . $user['last_name'];
                $_SESSION["role"] = $user['role'];

                // Redirect to the originally requested page or to appropriate dashboard
                if (isset($_GET['redirect'])) {
                    header("Location: " . urldecode($_GET['redirect']));
                } else {
                    if ($user['role'] === 'admin') {
                        header("Location: admin_dashboard.php");
                    } else {
                        // Redirect regular users to index.php (home page) after successful login
                        header("Location: home.php"); 
                    }
                }
                exit();
            } else {
                $error = "Invalid email, password, or role!";
            }
        } else {
            $error = "Email not found or incorrect role!";
        }
    }
    // Check in employees table for employee
    elseif ($selectedRole === 'employee') {
        $stmt = $conn->prepare("SELECT employee_id, name, password FROM employees WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $employee = $result->fetch_assoc();

            if (password_verify($password, $employee['password'])) {
                // Regenerate session ID to prevent session fixation
                session_regenerate_id();

                $_SESSION["user_id"] = $employee['employee_id'];
                $_SESSION["name"] = $employee['name'];
                $_SESSION["role"] = 'employee';

                // Redirect to the originally requested page or to kitchen
                if (isset($_GET['redirect'])) {
                    header("Location: " . urldecode($_GET['redirect']));
                } else {
                    header("Location: kitchen.php");
                }
                exit();
            } else {
                $error = "Invalid email, password, or role!";
            }
        } else {
            $error = "Email not found or incorrect role!";
        }
    } else {
        $error = "Please select a valid role!";
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            width: 350px;
        }
        h2 {
            text-align: center;
            color: #343a40;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        input[type="email"],
        input[type="password"],
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            margin-top: 5px;
        }
        button {
            width: 100%;
            padding: 10px;
            background: linear-gradient(135deg, #003366, #0055a5);
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 10px;
        }
        button:hover {
            background: linear-gradient(135deg, #0055a5, #0077cc);
        }
        .error {
            color: #d9534f;
            text-align: center;
            margin-bottom: 15px;
            font-weight: bold;
        }
        .register-link {
            text-align: center;
            margin-top: 15px;
            font-size: 14px;
        }
        .register-link a {
            color: #007bff;
            text-decoration: none;
        }
        .register-link a:hover {
            text-decoration: underline;
        }
        .role-option {
            display: flex;
            align-items: center;
        }
        .role-icon {
            margin-right: 8px;
            width: 20px;
            text-align: center;
        }
        .user-icon { color: #17a2b8; }
        .admin-icon { color: #dc3545; }
        .employee-icon { color: #28a745; }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="login-container">
        <h2><i class="fas fa-user"></i> Login</h2>

        <?php if (!empty($error)): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div class="form-group">
                <label for="role">Login As</label>
                <select name="role" id="role" required>
                    <option value="">-- Select Role --</option>
                    <option value="user">
                        <div class="role-option">
                            <span class="role-icon user-icon"><i class="fas fa-user"></i></span>
                            User
                        </div>
                    </option>
                    <option value="admin">
                        <div class="role-option">
                            <span class="role-icon admin-icon"><i class="fas fa-user-shield"></i></span>
                            Admin
                        </div>
                    </option>
                    <option value="employee">
                        <div class="role-option">
                            <span class="role-icon employee-icon"><i class="fas fa-user-tie"></i></span>
                            Employee
                        </div>
                    </option>
                </select>
            </div>

            <button type="submit">Login</button>
        </form>

        <div class="register-link">
            Don't have an account? <a href="register.php">Register here</a>
        </div>
    </div>
</body>
</html>