<?php
session_start();
$orderId = '';
$orders = [];
$userId = $_SESSION['user_id'] ?? null;
$is_paid = false;
$paymentId = '';
$totalAmount = 0;

if (!$userId) {
    die("User not logged in.");
}

// Database connection
$conn = null;
try {
    $conn = new mysqli("localhost", "root", "", "restaurant");
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    $conn = null;
}

$cartFile = "cart_user_$userId.txt";
$ordersFile = "orders.txt";
$paymentsFile = "payments.txt";

// Handle payment confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_payment'])) {
    if (file_exists($cartFile)) {
        $content = file_get_contents($cartFile);
        $cartData = json_decode($content, true);

        if ($cartData && empty($cartData['is_paid'])) {
            // Calculate total amount
            $totalAmount = 0;
            foreach ($cartData['items'] as $item) {
                $totalAmount += (int)($item['qty'] ?? 0) * (int)($item['price'] ?? 0);
            }

            // Generate payment ID
            $paymentId = 'PAY' . date('YmdHis') . rand(100, 999);
            $status = 'Paid';
            $method = 'UPI';

            // Save payment record to file
            $paymentRecord = [
                'payment_id' => $paymentId,
                'customer_id' => $userId,
                'amount' => $totalAmount,
                'status' => $status,
                'payment_method' => $method,
                'date' => date('Y-m-d H:i:s')
            ];
            file_put_contents($paymentsFile, json_encode($paymentRecord) . PHP_EOL, FILE_APPEND);

            // If database is available, save to database too
            if ($conn) {
                try {
                    // First check if customer exists, if not create them
                    $checkCustomer = $conn->prepare("SELECT customer_id FROM customers WHERE customer_id = ?");
                    $checkCustomer->bind_param("i", $userId);
                    $checkCustomer->execute();
                    $checkCustomer->store_result();

                    if ($checkCustomer->num_rows === 0) {
                        // Create customer if they don't exist
                        $customerName = "Customer_" . $userId;
                        $insertCustomer = $conn->prepare("INSERT INTO customers (customer_id, name) VALUES (?, ?)");
                        $insertCustomer->bind_param("is", $userId, $customerName);
                        $insertCustomer->execute();
                        $insertCustomer->close();
                    }
                    $checkCustomer->close();

                    // Now insert payment
                    $stmt = $conn->prepare("INSERT INTO payments (payment_id, customer_id, amount, payment_method, status, payment_date) VALUES (?, ?, ?, ?, ?, NOW())");
                    $stmt->bind_param("sidis", $paymentId, $userId, $totalAmount, $method, $status);
                    $stmt->execute();
                    $stmt->close();
                } catch (Exception $e) {
                    // Log error but continue with file-based system
                    error_log("Database error: " . $e->getMessage());
                }
            }

            // Update cart as paid
            $cartData['is_paid'] = true;
            $cartData['payment_id'] = $paymentId;
            file_put_contents($cartFile, json_encode($cartData) . PHP_EOL);

            // Save order to orders.txt for kitchen
            $orderData = [
                'orderId' => $cartData['orderId'],
                'userId' => $userId,
                'items' => $cartData['items'],
                'payment_id' => $paymentId,
                'amount' => $totalAmount,
                'status' => 'Received',
                'timestamp' => date('Y-m-d H:i:s')
            ];
            file_put_contents($ordersFile, json_encode($orderData) . PHP_EOL, FILE_APPEND);
        }
    }
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
            $paymentId = $lastOrder['payment_id'] ?? '';
            $totalAmount = 0;
            foreach ($lastOrder['items'] as $item) {
                $totalAmount += (int)($item['qty'] ?? 0) * (int)($item['price'] ?? 0);
            }
        }
    }
}

// Get customer name for display
$customerName = "Customer";
if ($userId && $conn) {
    $result = $conn->query("SELECT name FROM customers WHERE customer_id = $userId");
    if ($result && $result->num_rows > 0) {
        $customer = $result->fetch_assoc();
        $customerName = $customer['name'];
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Your Cart - Payment</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f9f9f9;
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 20px;
        }
        .order-details {
            background: #fefefe;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #eee;
        }
        .item {
            padding: 12px 0;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
        }
        .status {
            font-weight: bold;
            margin-left: 10px;
            font-size: 0.9em;
        }
        .status-in-progress {
            color: #f39c12;
        }
        .status-ready {
            color: #27ae60;
        }
        .status-delivered {
            color: #3498db;
        }
        .total {
            text-align: right;
            font-weight: bold;
            margin: 20px 0;
            font-size: 1.3em;
            border-top: 2px solid #27ae60;
            padding-top: 10px;
        }
        .back-menu {
            display: inline-block;
            padding: 10px 15px;
            background-color: #2c3e50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .back-menu:hover {
            background-color: #1a252f;
        }
        .pay-btn {
            background: #27ae60;
            color: white;
            padding: 12px 20px;
            border-radius: 5px;
            text-decoration: none;
            border: none;
            font-size: 16px;
            cursor: pointer;
            margin: 15px 5px 15px 0;
            display: inline-block;
            transition: background 0.3s;
        }
        .pay-btn:hover {
            background: #219653;
        }
        .payment-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
            text-align: center;
        }
        .upi-details {
            background: #e8f4fc;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            text-align: left;
            font-size: 1.1em;
        }
        .qr-code {
            margin: 15px 0;
            text-align: center;
        }
        .payment-instructions {
            color: #e74c3c;
            font-weight: bold;
            margin: 15px 0;
            font-size: 0.95em;
        }
        .receipt {
            background: #e8f8f5;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
            border: 1px solid #27ae60;
        }
        .customer-info {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .amount-display {
            font-size: 1.5em;
            font-weight: bold;
            color: #27ae60;
        }
    </style>
</head>
<body>
<a href="menu.php" class="back-menu">← Back to Menu</a>
<div class="container">
    <h2>🛒 Your Cart</h2>

    <?php if (empty($orders)): ?>
        <p style="text-align: center; padding: 30px;">Your cart is empty</p>
        <div style="text-align: center;">
            <a href="menu.php" style="background: #27ae60; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none;">Browse Menu</a>
        </div>
    <?php else: ?>
        <div class="order-details">
            <div class="customer-info">
                <p><strong>Order ID:</strong> <?= htmlspecialchars($orderId) ?></p>
                <p><strong>Customer:</strong> <?= htmlspecialchars($customerName) ?></p>
            </div>

            <?php $grand = 0; ?>
            <?php foreach ($orders as $item): ?>
                <?php
                $qty = (int)($item['qty'] ?? 0);
                $price = (int)($item['price'] ?? 0);
                $name = htmlspecialchars($item['name'] ?? '');
                $line = $qty * $price;
                $grand += $line;
                $status = $item['status'] ?? 'In Progress';
                ?>
                <div class="item">
                    <span><?= $qty ?> × <?= $name ?></span>
                    <span>₹<?= number_format($line, 2) ?>
                        <?php if ($is_paid): ?>
                            <span class="status status-<?= strtolower(str_replace(' ', '-', $status)) ?>">
                                (= <?= htmlspecialchars($status) ?>)
                            </span>
                        <?php endif; ?>
                    </span>
                </div>
            <?php endforeach; ?>

            <div class="total">Total: ₹<?= number_format($grand, 2) ?></div>
        </div>

        <div class="payment-section">
            <?php if (!$is_paid): ?>
                <h3>Payment Options</h3>
                <p>Scan the QR code below or click the payment link:</p>

                <div class="qr-code">
                    <img src="GooglePay_QR.png" alt="UPI QR Code" width="200">
                </div>

                <?php
                $upi_id = "kasinathanpb23@oksbi";
                $payee_name = "YourRestaurant";
                $upi_link = "upi://pay?pa=" . urlencode($upi_id) . "&pn=" . urlencode($payee_name) . "&am=" . number_format($grand, 2) . "&cu=INR";
                ?>

                <div class="upi-details">
                    <p><strong>UPI ID:</strong> <?= htmlspecialchars($upi_id) ?></p>
                    <p><strong>Amount:</strong> <span class="amount-display">₹<?= number_format($grand, 2) ?></span></p>
                    <p><strong>Name:</strong> <?= htmlspecialchars($payee_name) ?></p>
                </div>

                <a href="<?= htmlspecialchars($upi_link) ?>" class="pay-btn" target="_blank">
                    💳 Pay ₹<?= number_format($grand, 2) ?> via UPI
                </a>

                <form method="post" style="margin-top: 15px;">
                    <input type="hidden" name="confirm_payment" value="1">
                    <button type="submit" class="pay-btn" style="background: #2c3e50;">
                        ✅ I've Paid, Place My Order
                    </button>
                </form>

                <div class="payment-instructions">
                    Important: After completing payment, click the "I've Paid" button to confirm your order.
                </div>
            <?php else: ?>
                <div class="receipt">
                    <h3 style="color: #27ae60;">✅ Payment Successful!</h3>
                    <p><strong>Order ID:</strong> <?= htmlspecialchars($orderId) ?></p>
                    <p><strong>Payment ID:</strong> <?= htmlspecialchars($paymentId) ?></p>
                    <p><strong>Amount:</strong> ₹<?= number_format($grand, 2) ?></p>
                    <p><strong>Status:</strong>
                        <?php
                        $allStatus = [];
                        foreach ($orders as $item) {
                            $allStatus[] = $item['status'] ?? 'In Progress';
                        }
                        echo implode(', ', array_unique($allStatus));
                        ?>
                    </p>
                    <p>Your order has been received successfully. Your food is being prepared.</p>
                    <p>Thank you!</p>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
