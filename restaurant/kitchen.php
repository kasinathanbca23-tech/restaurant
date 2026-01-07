<?php
session_start();
// Ensure only admins or employees can access
if (!isset($_SESSION["user_id"]) || (!in_array($_SESSION["role"], ['admin', 'employee']))) {
    header("Location: login.php");
    exit();
}

$ordersFile = "orders.txt";
$allOrders = [];

// Handle status update
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update_status"])) {
    $orderIndex = (int)$_POST["order_index"];
    $itemIndex = (int)$_POST["item_index"];
    $newStatus = $_POST["status"];
    $lines = file_exists($ordersFile) ? file($ordersFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
    if (isset($lines[$orderIndex])) {
        $order = json_decode($lines[$orderIndex], true);
        if (isset($order['items'][$itemIndex])) {
            // Update the status in orders.txt
            $order['items'][$itemIndex]['status'] = $newStatus;
            $lines[$orderIndex] = json_encode($order);
            file_put_contents($ordersFile, implode(PHP_EOL, $lines) . PHP_EOL);

            // Also update the user's cart file if it exists
            if (isset($order['userId'])) {
                $cartFile = "cart_user_" . $order['userId'] . ".txt";
                if (file_exists($cartFile)) {
                    $cartContent = file_get_contents($cartFile);
                    $cartData = json_decode($cartContent, true);

                    // Update the status in the user's cart file
                    if ($cartData && isset($cartData['items']) && $cartData['orderId'] === $order['orderId']) {
                        foreach ($cartData['items'] as &$cartItem) {
                            if ($cartItem['id'] === $order['items'][$itemIndex]['id']) {
                                $cartItem['status'] = $newStatus;
                                break;
                            }
                        }
                        file_put_contents($cartFile, json_encode($cartData) . PHP_EOL);
                    }
                }
            }
        }
    }
    header("Location: kitchen.php");
    exit();
}

// Clear all orders
if (isset($_POST['clear'])) {
    file_put_contents($ordersFile, "");
    header("Location: kitchen.php");
    exit();
}

// Load orders
if (file_exists($ordersFile)) {
    $lines = file($ordersFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $order = json_decode($line, true);
        if (is_array($order)) {
            $allOrders[] = $order;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Kitchen Orders</title>
  <style>
    body { font-family: Arial; background: #eef2f3; padding: 20px; }
    .container {
      max-width: 800px; margin: auto; background: white; padding: 20px;
      border-radius: 10px; box-shadow: 0 0 10px gray;
    }
    h2 { text-align: center; }
    .order {
      border-bottom: 1px solid #ccc; margin-bottom: 20px; padding-bottom: 10px;
    }
    .item { margin-left: 15px; margin-bottom: 5px; }
    .status-select { margin-left: 10px; }
    .clear-btn {
      display: block;
      margin: 0 auto 20px auto;
      background: #dc3545;
      color: white;
      border: none;
      padding: 10px 20px;
      font-size: 16px;
      border-radius: 5px;
      cursor: pointer;
    }
    .clear-btn:hover {
      background: #c82333;
    }
    select, button { padding: 5px; }
    .back-dashboard {
      display: inline-block;
      padding: 8px 15px;
      background-color: #343a40;
      color: white;
      text-decoration: none;
      border-radius: 5px;
      font-weight: bold;
      margin-bottom: 20px;
    }
    .back-dashboard:hover {
      background-color: #23272b;
    }
    .user-info {
      font-size: 0.9em;
      color: #666;
      font-style: italic;
    }
  </style>
</head>
<body>
  <?php if ($_SESSION["role"] === 'admin'): ?>
    <a href="admin_dashboard.php" class="back-dashboard">← Back to Admin Dashboard</a>
  <?php else: ?>
    <a href="home.php" class="back-dashboard">← Back to Dashboard</a>
  <?php endif; ?>
  <div class="container">
    <h2>👨‍🍳 Kitchen Order Summary</h2>
    <?php if (!empty($allOrders)): ?>
      <form method="POST">
        <button class="clear-btn" type="submit" name="clear">🗑️ Clear Order History</button>
      </form>
    <?php endif; ?>
    <?php if (empty($allOrders)): ?>
      <p>No orders yet.</p>
    <?php else: ?>
      <?php foreach ($allOrders as $i => $order): ?>
        <div class="order">
          <h3>🧾 <?= htmlspecialchars($order['orderId'] ?? ('Order ' . ($i + 1))) ?>
            <?php if (isset($order['userId'])): ?>
              <span class="user-info">(User ID: <?= htmlspecialchars($order['userId']) ?>)</span>
            <?php endif; ?>
          </h3>
          <?php $total = 0; ?>
          <?php foreach ($order['items'] as $j => $item): ?>
            <?php $line = $item['qty'] * $item['price']; $total += $line; ?>
            <div class="item">
              <?= $item['qty'] ?> × <?= htmlspecialchars($item['name']) ?> = ₹<?= $line ?>
              (Status: <strong><?= htmlspecialchars($item['status'] ?? 'In Progress') ?></strong>)
              <form method="POST" style="display:inline;">
                <input type="hidden" name="order_index" value="<?= $i ?>">
                <input type="hidden" name="item_index" value="<?= $j ?>">
                <select name="status" class="status-select">
                  <option value="In Progress" <?= ($item['status'] ?? '') === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                  <option value="Ready" <?= ($item['status'] ?? '') === 'Ready' ? 'selected' : '' ?>>Ready</option>
                  <option value="Delivered" <?= ($item['status'] ?? '') === 'Delivered' ? 'selected' : '' ?>>Delivered</option>
                </select>
                <button type="submit" name="update_status">Update</button>
              </form>
            </div>
          <?php endforeach; ?>
          <strong>Total: ₹<?= $total ?></strong>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</body>
</html>
