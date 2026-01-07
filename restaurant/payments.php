<?php
session_start();
// Ensure only admin can access
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== 'admin') {
    header("Location: home.php");
    exit();
}

$paymentsFile = "payments.txt";
$allPayments = [];
$totalRevenue = 0;
$filterDate = $_GET['date'] ?? date('Y-m-d');

// Load payments from file
if (file_exists($paymentsFile)) {
    $lines = file($paymentsFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $payment = json_decode($line, true);
        if (is_array($payment)) {
            // Filter by date if specified
            if (empty($filterDate) || date('Y-m-d', strtotime($payment['date'])) === $filterDate) {
                $allPayments[] = $payment;
                $totalRevenue += $payment['amount'];
            }
        }
    }
    // Sort by date (newest first)
    usort($allPayments, function($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });
}

// Get unique customers for filter
$customers = [];
foreach ($allPayments as $payment) {
    if (!empty($payment['customer_id'])) {
        $customers[$payment['customer_id']] = true;
    }
}
$customerCount = count($customers);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Payment History - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f5f7fa;
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 1000px;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        h1 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 30px;
        }
        .summary-card {
            display: flex;
            justify-content: space-between;
            background: #e8f5e9;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
        }
        .summary-item {
            text-align: center;
            flex: 1;
        }
        .summary-value {
            font-size: 1.8em;
            font-weight: bold;
            color: #27ae60;
            margin: 5px 0;
        }
        .filters {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            align-items: center;
            flex-wrap: wrap;
        }
        .filter-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        select, input {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
        }
        button {
            padding: 10px 15px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
        }
        button:hover {
            background: #2980b9;
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
            background: #f8f9fa;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 0.9em;
        }
        tr:hover {
            background: #f5f5f5;
        }
        .status-tag {
            padding: 6px 10px;
            border-radius: 12px;
            font-size: 0.8em;
            font-weight: bold;
            text-align: center;
            display: inline-block;
            min-width: 80px;
        }
        .status-paid {
            background: #27ae60;
            color: white;
        }
        .status-pending {
            background: #f39c12;
            color: white;
        }
        .status-failed {
            background: #e74c3c;
            color: white;
        }
        .no-results {
            text-align: center;
            padding: 30px;
            color: #7f8c8d;
        }
        .export-btn {
            background: #27ae60;
            margin-left: auto;
        }
        .export-btn:hover {
            background: #219653;
        }
        .payment-id {
            font-family: monospace;
            color: #2980b9;
        }
    </style>
</head>
<body>
<div class="container">
    <a href="admin_dashboard.php" class="back-btn">← Back to Dashboard</a>
    <h1><i class="fas fa-receipt"></i> Payment History</h1>

    <div class="summary-card">
        <div class="summary-item">
            <div class="summary-label">Total Revenue</div>
            <div class="summary-value">₹<?= number_format($totalRevenue, 2) ?></div>
            <div class="summary-date"><?= !empty($filterDate) ? "for " . date('M d, Y', strtotime($filterDate)) : "All Time" ?></div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Total Payments</div>
            <div class="summary-value"><?= count($allPayments) ?></div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Unique Customers</div>
            <div class="summary-value"><?= $customerCount ?></div>
        </div>
    </div>

    <div class="filters">
        <div class="filter-group">
            <label for="date">Date:</label>
            <input type="date" id="date" name="date" value="<?= htmlspecialchars($filterDate) ?>">
        </div>

        <button onclick="applyFilters()">Apply Filters</button>
    </div>

    <?php if (empty($allPayments)): ?>
        <div class="no-results">
            <p><i class="fas fa-exclamation-circle"></i> No payments found<?= !empty($filterDate) ? " for selected date" : "" ?></p>
        </div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Payment ID</th>
                    <th>Customer ID</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Date/Time</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($allPayments as $payment): ?>
                <tr>
                    <td class="payment-id"><?= htmlspecialchars($payment['payment_id']) ?></td>
                    <td><?= htmlspecialchars($payment['customer_id'] ?? 'Guest') ?></td>
                    <td>₹<?= number_format($payment['amount'], 2) ?></td>
                    <td>
                        <span class="status-tag status-<?= strtolower($payment['status']) ?>">
                            <?= htmlspecialchars($payment['status']) ?>
                        </span>
                    </td>
                    <td><?= date('M d, Y h:i A', strtotime($payment['date'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<script>
    function applyFilters() {
        const date = document.getElementById('date').value;
        let url = "payments_history.php?";
        if (date) url += `date=${encodeURIComponent(date)}`;
        window.location.href = url;
    }
</script>
</body>
</html>
