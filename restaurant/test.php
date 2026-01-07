<?php
session_start();
$orderId = '';
$orders = [];
$userId = $_SESSION['user_id'] ?? null;
$is_paid = false;
if (!$userId) {
    die("User not logged in.");
}
$cartFile = "cart_user_$userId.txt";
$ordersFile = "orders.txt";

// Handle payment confirmation (user clicks "Payment Done" button)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_payment'])) {
    if (file_exists($cartFile)) {
        $content = file_get_contents($cartFile);
        $cartData = json_decode($content, true);
        if ($cartData && empty($cartData['is_paid'])) {
            $cartData['is_paid'] = true;
            file_put_contents($cartFile, json_encode($cartData) . PHP_EOL);
            
            // Save order to orders.txt for kitchen
            $orderData = [
                'orderId' => $cartData['orderId'],
                'userId' => $userId, // Include user ID
                'items' => $cartData['items']
            ];
            file_put_contents($ordersFile, json_encode($orderData) . PHP_EOL, FILE_APPEND);
        }
    }
    // Redirect to prevent form resubmission
    header("Location: cart.php");
    exit();
}

// Handle new cart/order POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['orderData'])) {
    $decoded = json_decode($_POST['orderData'], true);
    if (!empty($decoded['items'])) {
        $orderId = $decoded['orderId'] ?? 'ORD' . time() . rand(100,999);
        $orders = $decoded['items'];
        $dataToSave = [
            'orderId' => $orderId,
            'items' => $orders,
            'is_paid' => false
        ];
        file_put_contents($cartFile, json_encode($dataToSave) . PHP_EOL);
    }
} else {
    if (file_exists($cartFile)) {
        $content = file_get_contents($cartFile);
        $lastOrder = json_decode($content, true);
        if (isset($lastOrder['items']) && is_array($lastOrder['items'])) {
            $orders = $lastOrder['items'];
            $orderId = $lastOrder['orderId'] ?? 'ORD' . time() . rand(100,999);
            $is_paid = !empty($lastOrder['is_paid']);
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Your Cart</title>
  <style>
    body { font-family: Arial; background: #f9f9f9; padding: 20px; }
    .container {
      max-width: 600px; margin: auto; background: white; padding: 20px;
      border-radius: 10px; box-shadow: 0 0 10px gray;
    }
    h2 { text-align: center; }
    .item {
      padding: 10px 0;
      border-bottom: 1px solid #ddd;
    }
    .status {
      font-weight: bold;
      margin-left: 10px;
    }
    .status-in-progress { color: #ffc107; }
    .status-ready { color: #28a745; }
    .status-delivered { color: #17a2b8; }
    .total {
      text-align: right;
      font-weight: bold;
      margin-top: 15px;
    }
    a {
      display: inline-block;
      margin-top: 20px;
      text-decoration: none;
      color: #007bff;
    }
    .back-menu {
      display: inline-block;
      padding: 8px 15px;
      background-color: #343a40;
      color: white;
      text-decoration: none;
      border-radius: 5px;
      font-weight: bold;
      margin-bottom: 20px;
    }
    .back-menu:hover {
      background-color: #23272b;
    }
    .pay-btn {
      background: #28a745;
      color: white;
      padding: 10px 15px;
      border-radius: 5px;
      text-decoration: none;
      border: none;
      font-size: 16px;
      cursor: pointer;
      margin-top: 10px;
    }
  </style>
</head>
<body>
<a href="menu.php" class="back-menu">← Back</a>
<div class="container">
  <h2>🛒 Your Cart</h2>
  <?php if (empty($orders)): ?>
    <p>No items in cart.</p>
  <?php else: ?>
    <p><strong>Order ID:</strong> <?= htmlspecialchars($orderId) ?></p>
    <?php $grand = 0; ?>
    <?php foreach ($orders as $item): ?>
      <?php
        $qty = (int)($item['qty'] ?? 0);
        $price = (int)($item['price'] ?? 0);
        $name = htmlspecialchars($item['name'] ?? '');
        $line = $qty * $price;
        $grand += $line;
        $status = $item['status'] ?? 'In Progress';
        $statusClass = 'status-' . strtolower(str_replace(' ', '-', $status));
      ?>
      <div class="item">
        <?= $qty ?> × <?= $name ?> = ₹<?= $line ?>
        <?php if ($is_paid): ?>
          <span class="status <?= $statusClass ?>">(<?= htmlspecialchars($status) ?>)</span>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
    <div class="total">Total: ₹<?= $grand ?></div>
    <?php
      // UPI Payment Details
      $upi_id = "kasinathanpb23@oksbi";
      $payee_name = "YourRestaurant";
      $upi_link = "upi://pay?pa=" . urlencode($upi_id) . "&pn=" . urlencode($payee_name) . "&am=" . urlencode($grand) . "&cu=INR";
    ?>
    <div style="margin-top: 20px;">
      <?php if (!$is_paid): ?>
        <h3>Pay via UPI</h3>
        <p><strong>Scan the QR or click the link below:</strong></p>
        <img src="GooglePay_QR.png" alt="UPI QR" width="200"><br><br>
        <a href="<?= htmlspecialchars($upi_link) ?>" class="pay-btn" target="_blank">
          💳 Pay ₹<?= $grand ?> via UPI
        </a>
        <p style="margin-top:10px;"><strong>UPI ID:</strong> <?= htmlspecialchars($upi_id) ?></p>
        <form method="post" style="margin-top:20px;">
          <input type="hidden" name="confirm_payment" value="1">
          <button type="submit" class="pay-btn">I've Paid, Place My Order</button>
        </form>
        <p style="color: #c00; margin-top:10px;">
          <strong>Important:</strong> After paying, click "I've Paid, Place My Order". Payment is not automatically verified.
        </p>
      <?php else: ?>
        <h3 style="color:green;">✅ Payment Received. Your Order is Placed!</h3>
        <p>Your food is being prepared. Thank you for your payment.</p>
        <p>Current status: 
          <?php 
            $allStatus = [];
            foreach ($orders as $item) {
                $allStatus[] = $item['status'] ?? 'In Progress';
            }
            echo implode(', ', array_unique($allStatus));
          ?>
        </p>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</div>
</body>
</html>