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

// Handle search/filter
$search = isset($_GET['search']) ? $_GET['search'] : '';
$date_filter = isset($_GET['date']) ? $_GET['date'] : '';

// Build the query
$query = "SELECT p.*, c.name AS customer_name, c.email AS customer_email
          FROM payments p
          LEFT JOIN customers c ON p.customer_id = c.customer_id
          WHERE 1=1";

if (!empty($search)) {
    $query .= " AND (c.name LIKE '%$search%' OR c.email LIKE '%$search%' OR p.payment_id LIKE '%$search%')";
}

if (!empty($date_filter)) {
    $query .= " AND DATE(p.payment_date) = '$date_filter'";
}

$query .= " ORDER BY p.payment_date DESC";
$payments = $conn->query($query);

// Get total revenue
$total_revenue_result = $conn->query("SELECT SUM(amount) AS total FROM payments");
$total_revenue = $total_revenue_result->fetch_assoc()['total'] ?: 0;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Payments History - Admin</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f0f0f0;
            padding: 30px;
        }
        .container {
            background: white;
            max-width: 1200px;
            margin: auto;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }
        .back {
            display: inline-block;
            margin-bottom: 20px;
            text-decoration: none;
            color: #007bff;
        }
        .filters {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            align-items: center;
        }
        .filter-input {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            flex: 1;
        }
        .filter-btn {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
        }
        .summary-card {
            background: #e9f7ef;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .summary-value {
            font-size: 24px;
            font-weight: bold;
            color: #28a745;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .status-paid {
            color: #28a745;
            font-weight: bold;
        }
        .status-pending {
            color: #ffc107;
            font-weight: bold;
        }
        .status-failed {
            color: #dc3545;
            font-weight: bold;
        }
        .no-payments {
            text-align: center;
            padding: 20px;
            color: #666;
        }
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            gap: 5px;
        }
        .page-btn {
            padding: 8px 12px;
            border: 1px solid #ddd;
            background: white;
            cursor: pointer;
            border-radius: 4px;
        }
        .page-btn.active {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }
    </style>
</head>
<body>
<div class="container">
    <a href="admin_dashboard.php" class="back">← Back to Dashboard</a>
    <h2>Payments History</h2>

    <div class="summary-card">
        <div>
            <h3>Total Revenue</h3>
            <p>All time payments</p>
        </div>
        <div class="summary-value">$<?= number_format($total_revenue, 2) ?></div>
    </div>

    <div class="filters">
        <input type="text" class="filter-input" name="search" placeholder="Search by customer, email or payment ID..."
               value="<?= htmlspecialchars($search) ?>">
        <input type="date" class="filter-input" name="date" value="<?= htmlspecialchars($date_filter) ?>">
        <button class="filter-btn" onclick="applyFilters()">Filter</button>
    </div>

    <?php if ($payments->num_rows > 0): ?>
    <table>
        <thead>
            <tr>
                <th>Payment ID</th>
                <th>Customer</th>
                <th>Email</th>
                <th>Amount</th>
                <th>Payment Method</th>
                <th>Status</th>
                <th>Date</th>
                <th>Time</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($payment = $payments->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($payment['payment_id']) ?></td>
                <td><?= htmlspecialchars($payment['customer_name'] ?: 'Guest') ?></td>
                <td><?= htmlspecialchars($payment['customer_email'] ?: 'N/A') ?></td>
                <td>$<?= number_format($payment['amount'], 2) ?></td>
                <td><?= htmlspecialchars($payment['payment_method']) ?></td>
                <td class="status-<?= strtolower($payment['status']) ?>">
                    <?= htmlspecialchars($payment['status']) ?>
                </td>
                <td><?= date('M d, Y', strtotime($payment['payment_date'])) ?></td>
                <td><?= date('h:i A', strtotime($payment['payment_date'])) ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
    <div class="no-payments">
        <p>No payments found<?= !empty($search) || !empty($date_filter) ? " matching your criteria" : "" ?></p>
    </div>
    <?php endif; ?>
</div>

<script>
    function applyFilters() {
        const search = document.querySelector('input[name="search"]').value;
        const date = document.querySelector('input[name="date"]').value;
        let url = "payments.php?";
        if (search) url += `search=${encodeURIComponent(search)}&`;
        if (date) url += `date=${encodeURIComponent(date)}&`;
        window.location.href = url.slice(0, -1); // Remove trailing &
    }
</script>
</body>
</html>
