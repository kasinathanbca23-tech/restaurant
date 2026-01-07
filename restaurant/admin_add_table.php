<?php
session_start();
// Ensure only admin can access
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== 'admin') {
    header("Location: home.php");
    exit();
}
// Connect to the database
$conn = new mysqli("localhost", "root", "", "restaurant");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$message = '';
// Handle form submission to add table
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add_table'])) {
    $table_number = $_POST["table_number"];
    $capacity = $_POST["capacity"];
    $table_status = $_POST["table_status"];
    $stmt = $conn->prepare("INSERT INTO reservation (table_number, capacity, table_status) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $table_number, $capacity, $table_status);
    if ($stmt->execute()) {
        $message = "<p style='color:green;'>Table added successfully!</p>";
    } else {
        $message = "<p style='color:red;'>Error: " . $conn->error . "</p>";
    }
    $stmt->close();
}
// Handle toggle status action
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['toggle_id'])) {
    $toggle_id = intval($_POST['toggle_id']);
    $current_status = $_POST['current_status'];
    // Determine new status
    $new_status = ($current_status === "Available") ? "Reserved" : "Available";
    $conn->query("UPDATE reservation SET table_status = '$new_status' WHERE table_id = $toggle_id");
    $message = "<p style='color:green;'>Table status updated successfully!</p>";
}
// Handle delete table action
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['delete_id'])) {
    $delete_id = intval($_POST['delete_id']);

    // Start transaction
    $conn->begin_transaction();

    try {
        // First delete from table_reservations if exists
        $conn->query("DELETE FROM table_reservations WHERE table_id = $delete_id");

        // Then delete from reservation
        $conn->query("DELETE FROM reservation WHERE table_id = $delete_id");

        // Commit transaction
        $conn->commit();
        $message = "<p style='color:green;'>Table deleted successfully!</p>";
    } catch (Exception $e) {
        // Roll back transaction if error occurs
        $conn->rollback();
        $message = "<p style='color:red;'>Error deleting table: " . $e->getMessage() . "</p>";
    }
}
// Fetch all tables with reservation details (if any)
$tables = $conn->query("
    SELECT r.*, tr.reservation_id, tr.reservation_time
    FROM reservation r
    LEFT JOIN table_reservations tr ON r.table_id = tr.table_id
    ORDER BY r.table_id DESC
");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Tables - Admin</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f0f0f0;
            padding: 30px;
        }
        .container {
            background: white;
            max-width: 900px;
            margin: auto;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2, h3 {
            text-align: center;
            margin-bottom: 20px;
        }
        label, select, input {
            display: block;
            width: 100%;
            margin-top: 10px;
            padding: 10px;
            font-size: 14px;
        }
        input[type="submit"], .toggle-btn, .delete-btn {
            font-weight: bold;
            border: none;
            margin-top: 5px;
            cursor: pointer;
            padding: 8px 15px;
            border-radius: 4px;
            transition: background 0.2s;
        }
        input[type="submit"] {
            background-color: #28a745;
            color: white;
            width: 100%;
        }
        input[type="submit"]:hover {
            background-color: #218838;
        }
        .toggle-btn {
            background-color: #007bff;
            color: white;
            margin-right: 5px;
        }
        .toggle-btn:hover {
            background-color: #0056b3;
        }
        .delete-btn {
            background-color: #dc3545;
            color: white;
        }
        .delete-btn:hover {
            background-color: #c82333;
        }
        .back {
            display: inline-block;
            margin-bottom: 20px;
            text-decoration: none;
            color: #007bff;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
        }
        table, th, td {
            border: 1px solid #aaa;
        }
        th, td {
            padding: 10px;
            text-align: center;
        }
        .message {
            text-align: center;
            margin-bottom: 20px;
        }
        .reserved-row {
            background: #f8d7da;
        }
        .available-row {
            background: #d4edda;
        }
        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 5px;
        }
    </style>
</head>
<body>
<div class="container">
    <a href="admin_dashboard.php" class="back">← Back to Dashboard</a>
    <h2>Manage Tables</h2>
    <div class="message"><?= $message ?></div>
    <form method="POST">
        <label>Table Number:</label>
        <input type="number" name="table_number" required>
        <label>Capacity:</label>
        <input type="number" name="capacity" required>
        <label>Table Status:</label>
        <select name="table_status" required>
            <option value="Available">Available</option>
            <option value="Reserved">Reserved</option>
        </select>
        <input type="submit" name="add_table" value="Add Table">
    </form>
    <h3>Existing Tables</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>Table Number</th>
            <th>Capacity</th>
            <th>Status</th>
            <th>Reservation ID</th>
            <th>Reserved Time</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $tables->fetch_assoc()): ?>
        <tr class="<?= $row['table_status'] === 'Reserved' ? 'reserved-row' : 'available-row' ?>">
            <td><?= $row['table_id'] ?></td>
            <td><?= $row['table_number'] ?></td>
            <td><?= $row['capacity'] ?></td>
            <td><?= $row['table_status'] ?></td>
            <td><?= !empty($row['reservation_id']) ? $row['reservation_id'] : 'N/A' ?></td>
            <td><?= !empty($row['reservation_time']) ? $row['reservation_time'] : 'N/A' ?></td>
            <td class="action-buttons">
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="toggle_id" value="<?= $row['table_id'] ?>">
                    <input type="hidden" name="current_status" value="<?= $row['table_status'] ?>">
                    <button type="submit" class="toggle-btn">
                        Set <?= $row['table_status'] === 'Available' ? 'Reserved' : 'Available' ?>
                    </button>
                </form>
                <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this table?');">
                    <input type="hidden" name="delete_id" value="<?= $row['table_id'] ?>">
                    <button type="submit" class="delete-btn">Delete</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>
</body>
</html>
