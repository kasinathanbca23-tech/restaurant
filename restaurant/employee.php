<?php
session_start();
// Ensure only admin can access
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== 'admin') {
    header("Location: home.php");
    exit();
}

// Database connection
$conn = new mysqli("localhost", "root", "", "restaurant");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = '';
$error = '';
$roles = ['Admin', 'Employee']; // Only Admin and Employee roles

// Handle form submission for adding new employee
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $role = $_POST['role'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $join_date = date('Y-m-d');
    $salary = $_POST['salary'] ?? 0;

    // Validate inputs
    if (empty($name) || empty($email) || empty($phone) || empty($role) || empty($_POST['password'])) {
        $error = "All fields are required!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format!";
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT employee_id FROM employees WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $error = "Email already exists!";
        } else {
            // Insert new employee
            $stmt = $conn->prepare("INSERT INTO employees (name, email, phone, role, password, join_date, salary)
                                   VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssd", $name, $email, $phone, $role, $password, $join_date, $salary);
            if ($stmt->execute()) {
                $message = "Employee registered successfully!";
            } else {
                $error = "Error: " . $conn->error;
            }
            $stmt->close();
        }
    }
}

// Handle employee deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $employee_id = $_POST['employee_id'];
    $stmt = $conn->prepare("DELETE FROM employees WHERE employee_id = ?");
    $stmt->bind_param("i", $employee_id);
    if ($stmt->execute()) {
        $message = "Employee deleted successfully!";
    } else {
        $error = "Error: " . $conn->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register New Employee</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f5f7fa;
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        h1 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 30px;
        }
        .back-btn {
            display: inline-block;
            margin-bottom: 20px;
            padding: 8px 15px;
            background: #2c3e50;
            color: white;
            text-decoration: none;
            border-radius: 6px;
        }
        .back-btn:hover {
            background: #1a252f;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }
        input, select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
        }
        .submit-btn {
            background: #27ae60;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
            transition: background 0.3s;
        }
        .submit-btn:hover {
            background: #219653;
        }
        .delete-btn {
            background: #e74c3c;
            color: white;
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .delete-btn:hover {
            background: #c0392b;
        }
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 6px;
            text-align: center;
        }
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .employee-list {
            margin-top: 40px;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .employee-list h2 {
            margin-top: 0;
            color: #2c3e50;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #f8f9fa;
            font-weight: 600;
        }
        tr:hover {
            background: #f5f5f5;
        }
        .role-tag {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            font-weight: bold;
            display: inline-block;
        }
        .admin {
            background: #ffebee;
            color: #c62828;
        }
        .employee {
            background: #e3f2fd;
            color: #1976d2;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="admin_dashboard.php" class="back-btn">← Back to Dashboard</a>
        <h1><i class="fas fa-user-plus"></i> Register New Employee</h1>
        <?php if ($message): ?>
            <div class="message success"><?= $message ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="message error"><?= $error ?></div>
        <?php endif; ?>
        <form method="POST">
            <input type="hidden" name="register" value="1">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" required>
            </div>
            <div class="form-group">
                <label for="role">Role</label>
                <select id="role" name="role" required>
                    <option value="">-- Select Role --</option>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?= $role ?>"><?= $role ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="salary">Salary (₹)</label>
                <input type="number" id="salary" name="salary" min="0" value="0">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required minlength="6">
            </div>
            <button type="submit" class="submit-btn">Register Employee</button>
        </form>

        <div class="employee-list">
            <h2><i class="fas fa-users"></i> Current Employees</h2>
            <?php
            $employees = $conn->query("SELECT * FROM employees ORDER BY join_date DESC");
            if ($employees->num_rows > 0):
            ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Role</th>
                        <th>Salary</th>
                        <th>Join Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($emp = $employees->fetch_assoc()): ?>
                    <tr>
                        <td><?= $emp['employee_id'] ?></td>
                        <td><?= htmlspecialchars($emp['name']) ?></td>
                        <td><?= htmlspecialchars($emp['email']) ?></td>
                        <td><?= htmlspecialchars($emp['phone']) ?></td>
                        <td>
                            <span class="role-tag <?= strtolower($emp['role']) ?>">
                                <?= htmlspecialchars($emp['role']) ?>
                            </span>
                        </td>
                        <td>₹<?= number_format($emp['salary'], 2) ?></td>
                        <td><?= date('M d, Y', strtotime($emp['join_date'])) ?></td>
                        <td>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="employee_id" value="<?= $emp['employee_id'] ?>">
                                <button type="submit" name="delete" class="delete-btn" onclick="return confirm('Are you sure you want to delete this employee?');">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
                <p>No employees registered yet.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
