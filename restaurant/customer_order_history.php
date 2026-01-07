<?php
require_once "config.php";

// Ensure only logged-in users can access
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

$ordersFile = "orders.txt";
$allOrders = [];
$userId = $_SESSION["user_id"];
$isAdmin = ($_SESSION["role"] === 'admin');
$isEmployee = ($_SESSION["role"] === 'employee');

// Load orders based on user role
if (file_exists($ordersFile)) {
    $lines = file($ordersFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $order = json_decode($line, true);
        if (is_array($order)) {
            // For regular users, only show their own orders
            if (!$isAdmin && !$isEmployee && isset($order['userId']) && $order['userId'] == $userId) {
                $allOrders[] = $order;
            }
            // For employees, only show orders they can manage
            // An order is manageable by an employee if it's not assigned or assigned to them
            elseif ($isEmployee) {
                // Ensure 'assignedEmployee' is handled if it might not exist
                $assignedEmployeeId = isset($order['assignedEmployee']) ? $order['assignedEmployee'] : null;
                if ($assignedEmployeeId === null || $assignedEmployeeId == $userId) {
                    $allOrders[] = $order;
                }
            }
            // Admins can see all orders
            elseif ($isAdmin) {
                $allOrders[] = $order;
            }
        }
    }
    // Sort orders by date (newest first) - Added robust check for 'orderDate'
    usort($allOrders, function($a, $b) {
        $dateA = isset($a['orderDate']) ? strtotime($a['orderDate']) : 0; // Default to epoch start if missing
        $dateB = isset($b['orderDate']) ? strtotime($b['orderDate']) : 0; // Default to epoch start if missing
        return $dateB - $dateA; // Newest first
    });
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>
        <?= ($isAdmin) ? 'Order History' :
            (($isEmployee) ? 'My Order Assignments' : 'My Order History') ?>
    </title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .header {
            background-color: #343a40;
            color: white;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .container {
            max-width: 1000px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1, h2, h3 {
            color: #343a40;
        }
        .back-btn {
            display: inline-block;
            padding: 8px 15px;
            background-color: #343a40;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .back-btn:hover {
            background-color: #23272b;
        }
        .user-info {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #007bff;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: bold;
            margin-right: 15px;
        }
        .user-details h2 {
            margin: 0;
            color: #343a40;
        }
        .user-details p {
            margin: 5px 0;
            color: #666;
        }
        .order {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
            background-color: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .order-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .order-id {
            font-weight: bold;
            color: #007bff;
        }
        .order-date {
            color: #666;
            font-size: 0.9em;
        }
        .customer-info {
            margin-bottom: 10px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .order-items {
            margin-top: 10px;
        }
        .item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .item:last-child {
            border-bottom: none;
        }
        .item-name {
            flex: 2;
            font-weight: 500;
        }
        .item-qty {
            flex: 1;
            text-align: center;
            color: #666;
        }
        .item-price {
            flex: 1;
            text-align: right;
        }
        .status {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            font-weight: bold;
            text-align: center;
            min-width: 80px;
            margin-top: 5px;
        }
        .status-inprogress {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-ready {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        .status-delivered {
            background-color: #d4edda;
            color: #155724;
        }
        .total {
            margin-top: 15px;
            text-align: right;
            font-weight: bold;
            font-size: 1.1em;
            padding-top: 10px;
            border-top: 1px solid #eee;
        }
        .filter-section {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }
        select, input[type="date"], input[type="text"] {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            min-width: 150px;
        }
        button {
            padding: 8px 15px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        .no-orders {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        .no-orders i {
            font-size: 50px;
            color: #ddd;
            display: block;
            margin-bottom: 10px;
        }
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }
        .pagination button {
            margin: 0 5px;
            background-color: #ddd;
            color: #333;
            padding: 5px 10px;
        }
        .pagination button.active {
            background-color: #007bff;
            color: white;
        }
        .table-info {
            color: #666;
            font-size: 0.9em;
            margin-top: 5px;
        }
        .role-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            font-weight: bold;
            margin-left: 10px;
        }
        .admin-badge {
            background-color: #dc3545;
            color: white;
        }
        .employee-badge {
            background-color: #28a745;
            color: white;
        }
        .user-badge {
            background-color: #17a2b8;
            color: white;
        }
        .assigned-info {
            color: #666;
            font-size: 0.9em;
            margin-top: 5px;
        }
        .order-actions {
            margin-top: 10px;
            text-align: right;
        }
        .receipt-btn {
            padding: 5px 10px;
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 0.8em;
            margin-right: 5px;
        }
        .receipt-btn:hover {
            background-color: #5a6268;
        }
        .reorder-btn {
            padding: 5px 10px;
            background-color: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 0.8em;
        }
        .reorder-btn:hover {
            background-color: #218838;
        }
        @media (max-width: 768px) {
            .order-header {
                flex-direction: column;
                align-items: flex-start;
            }
            .item {
                flex-direction: column;
                align-items: flex-start;
            }
            .item-qty, .item-price {
                margin-top: 5px;
                text-align: left;
            }
            .filter-section {
                flex-direction: column;
                align-items: stretch;
            }
            select, input[type="date"], input[type="text"], button {
                width: 100%;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php if ($isAdmin): ?>
        <a href="admin_dashboard.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
    <?php elseif ($isEmployee): ?>
        <a href="kitchen.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Kitchen</a>
    <?php else: ?>
        <a href="profile.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Profile</a>
    <?php endif; ?>

    <div class="container">
        <div class="header">
            <h1>
                <?= ($isAdmin) ? '<i class="fas fa-history"></i> Order History' :
                   (($isEmployee) ? '<i class="fas fa-clipboard-list"></i> My Order Assignments' :
                   '<i class="fas fa-shopping-bag"></i> My Order History') ?>
                <span class="role-badge
                    <?= ($isAdmin) ? 'admin-badge' :
                       (($isEmployee) ? 'employee-badge' : 'user-badge') ?>">
                    <?= ($isAdmin) ? 'Admin' :
                       (($isEmployee) ? 'Employee' : 'User') ?>
                </span>
            </h1>
        </div>

        <div class="user-info">
            <div class="user-avatar">
                <?= strtoupper(substr($_SESSION["name"] ?? '?', 0, 1)) ?>
            </div>
            <div class="user-details">
                <h2><?= htmlspecialchars($_SESSION["name"] ?? 'Guest') ?></h2>
                <p><?= ($isAdmin) ? 'Administrator' :
                     (($isEmployee) ? 'Kitchen Staff' : 'Customer') ?></p>
            </div>
        </div>

        <div class="filter-section">
            <form method="GET" action="">
                <label for="status">Status:</label>
                <select name="status" id="status">
                    <option value="">All Statuses</option>
                    <option value="In Progress" <?= isset($_GET['status']) && $_GET['status'] === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                    <option value="Ready" <?= isset($_GET['status']) && $_GET['status'] === 'Ready' ? 'selected' : '' ?>>Ready</option>
                    <option value="Delivered" <?= isset($_GET['status']) && $_GET['status'] === 'Delivered' ? 'selected' : '' ?>>Delivered</option>
                </select>

                <label for="date">Date:</label>
                <input type="date" name="date" id="date" value="<?= isset($_GET['date']) ? $_GET['date'] : '' ?>">

                <?php if ($isAdmin): ?>
                    <label for="customer">Customer:</label>
                    <input type="text" name="customer" id="customer" placeholder="Customer ID or Name"
                           value="<?= isset($_GET['customer']) ? $_GET['customer'] : '' ?>">

                    <label for="employee">Employee:</label>
                    <input type="text" name="employee" id="employee" placeholder="Employee ID or Name"
                           value="<?= isset($_GET['employee']) ? $_GET['employee'] : '' ?>">
                <?php endif; ?>

                <button type="submit">Apply Filters</button>

                <?php if (isset($_GET['status']) || isset($_GET['date']) ||
                         ($isAdmin && (isset($_GET['customer']) || isset($_GET['employee'])))): ?>
                    <a href="<?= basename($_SERVER['PHP_SELF']) ?>" style="margin-left: auto;">
                        <button type="button" style="background-color: #6c757d;">Clear Filters</button>
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <?php
        // Apply filters if set
        $filteredOrders = $allOrders;

        if (isset($_GET['status']) && !empty($_GET['status'])) {
            $statusFilter = $_GET['status'];
            $filteredOrders = array_filter($filteredOrders, function($order) use ($statusFilter) {
                // An order is considered to match the status filter if ANY of its items match
                if (isset($order['items']) && is_array($order['items'])) {
                    foreach ($order['items'] as $item) {
                        if (isset($item['status']) && $item['status'] === $statusFilter) {
                            return true;
                        }
                    }
                }
                return false;
            });
        }

        if (isset($_GET['date']) && !empty($_GET['date'])) {
            $dateFilter = $_GET['date'];
            $filteredOrders = array_filter($filteredOrders, function($order) use ($dateFilter) {
                return isset($order['orderDate']) && date('Y-m-d', strtotime($order['orderDate'])) === $dateFilter;
            });
        }

        if ($isAdmin) {
            if (isset($_GET['customer']) && !empty($_GET['customer'])) {
                $customerFilter = strtolower($_GET['customer']);
                $filteredOrders = array_filter($filteredOrders, function($order) use ($customerFilter) {
                    return (isset($order['userId']) && strpos(strtolower($order['userId']), $customerFilter) !== false) ||
                           (isset($order['customerName']) && strpos(strtolower($order['customerName']), $customerFilter) !== false);
                });
            }

            if (isset($_GET['employee']) && !empty($_GET['employee'])) {
                $employeeFilter = strtolower($_GET['employee']);
                $filteredOrders = array_filter($filteredOrders, function($order) use ($employeeFilter) {
                    return isset($order['assignedEmployee']) &&
                           (strpos(strtolower($order['assignedEmployee']), $employeeFilter) !== false ||
                            (isset($order['employeeName']) && strpos(strtolower($order['employeeName']), $employeeFilter) !== false));
                });
            }
        }

        // Pagination
        $ordersPerPage = 5;
        $totalOrders = count($filteredOrders);
        $totalPages = ceil($totalOrders / $ordersPerPage);
        // Ensure currentPage is within valid bounds
        $currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $currentPage = min($currentPage, $totalPages > 0 ? $totalPages : 1); // If no orders, max page is 1
        
        $startIndex = ($currentPage - 1) * $ordersPerPage;
        $paginatedOrders = array_slice($filteredOrders, $startIndex, $ordersPerPage);
        ?>

        <?php if (empty($paginatedOrders) && empty($_GET)): // No orders AND no filters ?>
            <div class="no-orders">
                <i class="fas
                    <?= ($isAdmin) ? 'fa-clipboard-list' :
                       (($isEmployee) ? 'fa-utensils' : 'fa-shopping-bag') ?>">
                </i>
                <p>
                    <?= ($isAdmin) ? 'No orders found in the system.' :
                       (($isEmployee) ? 'You have no assigned orders.' :
                       'You haven\'t placed any orders yet.') ?>
                </p>
                <?php if (!$isAdmin && !$isEmployee): ?>
                    <p><a href="menu.php" style="color: #007bff;">Browse our menu</a> to place your first order!</p>
                <?php endif; ?>
            </div>
        <?php elseif (empty($paginatedOrders)): // No orders matching filters ?>
            <div class="no-orders">
                <i class="fas fa-search"></i>
                <p>No orders found matching your filters.</p>
                <a href="<?= basename($_SERVER['PHP_SELF']) ?>" style="color: #007bff;">Show all orders</a>
            </div>
        <?php else: // Orders are found ?>
            <?php foreach ($paginatedOrders as $order): ?>
                <div class="order">
                    <div class="order-header">
                        <div>
                            <h3 class="order-id">Order #<?= htmlspecialchars($order['orderId'] ?? 'N/A') ?></h3>
                            <?php if (isset($order['tableNumber'])): ?>
                                <div class="table-info">
                                    <i class="fas fa-chair"></i> Table <?= htmlspecialchars($order['tableNumber']) ?>
                                </div>
                            <?php endif; ?>
                            <?php if (($isAdmin || $isEmployee) && isset($order['assignedEmployee']) && !empty($order['assignedEmployee'])): ?>
                                <div class="assigned-info">
                                    <i class="fas fa-user-tie"></i> Assigned to: <?= htmlspecialchars($order['employeeName'] ?? $order['assignedEmployee']) ?>
                                </div>
                            <?php elseif (($isAdmin || $isEmployee) && (!isset($order['assignedEmployee']) || empty($order['assignedEmployee']))): ?>
                                <div class="assigned-info">
                                    <i class="fas fa-user-tie"></i> Not Yet Assigned
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="order-date"><?= date('M d, Y • h:i A', strtotime($order['orderDate'] ?? time())) ?></div>
                    </div>

                    <?php if (!$isAdmin && !$isEmployee): ?>
                        <div class="customer-info">
                            <strong>My Order</strong>
                        </div>
                    <?php else: ?>
                        <div class="customer-info">
                            <strong>Customer:</strong>
                            <?= isset($order['customerName']) ? htmlspecialchars($order['customerName']) : 'Guest' ?>
                            <?= isset($order['userId']) ? " (ID: " . htmlspecialchars($order['userId']) . ")" : "" ?>
                        </div>
                    <?php endif; ?>

                    <div class="order-items">
                        <?php $total = 0; ?>
                        <?php 
                        // Ensure $order['items'] is an array before iterating
                        $items = $order['items'] ?? []; 
                        foreach ($items as $item): ?>
                            <?php
                            // Handle cases where qty or price might be missing
                            $qty = $item['qty'] ?? 0;
                            $price = $item['price'] ?? 0;
                            $itemTotal = $qty * $price;
                            $total += $itemTotal;
                            ?>
                            <div class="item">
                                <div class="item-name"><?= htmlspecialchars($item['name'] ?? 'Unknown Item') ?></div>
                                <div class="item-qty"><?= $qty ?> × ₹<?= number_format($price, 2) ?></div>
                                <div class="item-price">₹<?= number_format($itemTotal, 2) ?></div>
                            </div>
                            <div style="text-align: right; margin-top: 5px;">
                                <span class="status
                                    <?= strtolower($item['status'] ?? '') === 'in progress' ? 'status-inprogress' :
                                       (strtolower($item['status'] ?? '') === 'ready' ? 'status-ready' : 'status-delivered') ?>">
                                    <?= htmlspecialchars($item['status'] ?? 'In Progress') ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="total">Order Total: ₹<?= number_format($total, 2) ?></div>

                    <?php 
                    // Determine if the "Order Again" and "View Receipt" buttons should be shown
                    // Only for regular users, and if at least one item is delivered
                    $showCustomerActions = false;
                    if (!$isAdmin && !$isEmployee && isset($order['items']) && is_array($order['items'])) {
                        foreach ($order['items'] as $item) {
                            if (strtolower($item['status'] ?? '') === 'delivered') {
                                $showCustomerActions = true;
                                break;
                            }
                        }
                    }
                    ?>
                    <?php if ($showCustomerActions): ?>
                        <div class="order-actions">
                            <a href="#" class="receipt-btn" onclick="alert('Receipt feature coming soon!')">
                                <i class="fas fa-receipt"></i> View Receipt
                            </a>
                            <a href="menu.php?reorder=<?= urlencode($order['orderId'] ?? '') ?>" class="reorder-btn">
                                <i class="fas fa-redo"></i> Order Again
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php 
                    // Helper function to build query string for pagination links
                    function buildPaginationQuery($page, $getParams, $isAdmin) {
                        $query = ['page' => $page];
                        if (isset($getParams['status'])) $query['status'] = $getParams['status'];
                        if (isset($getParams['date'])) $query['date'] = $getParams['date'];
                        if ($isAdmin) {
                            if (isset($getParams['customer'])) $query['customer'] = $getParams['customer'];
                            if (isset($getParams['employee'])) $query['employee'] = $getParams['employee'];
                        }
                        return http_build_query($query);
                    }
                    ?>

                    <?php if ($currentPage > 1): ?>
                        <a href="<?= basename($_SERVER['PHP_SELF']) ?>?<?= buildPaginationQuery($currentPage - 1, $_GET, $isAdmin) ?>">
                            <button type="button"><i class="fas fa-chevron-left"></i> Previous</button>
                        </a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="<?= basename($_SERVER['PHP_SELF']) ?>?<?= buildPaginationQuery($i, $_GET, $isAdmin) ?>">
                            <button type="button" <?= $i === $currentPage ? 'class="active"' : '' ?>><?= $i ?></button>
                        </a>
                    <?php endfor; ?>

                    <?php if ($currentPage < $totalPages): ?>
                        <a href="<?= basename($_SERVER['PHP_SELF']) ?>?<?= buildPaginationQuery($currentPage + 1, $_GET, $isAdmin) ?>">
                            <button type="button">Next <i class="fas fa-chevron-right"></i></button>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>