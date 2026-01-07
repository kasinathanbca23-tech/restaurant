<?php
session_start();
$conn = new mysqli("localhost", "root", "", "restaurant");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Fetch menu items grouped by category
$result = $conn->query("SELECT * FROM menu ORDER BY category, name");
$menuByCategory = [];
while ($row = $result->fetch_assoc()) {
    $menuByCategory[$row['category']][] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Restaurant Menu</title>
<style>
body {
    font-family: 'Segoe UI', Arial, sans-serif;
    background: #f4f4f9;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}
header {
    background: #222;
    color: #fff;
    padding: 24px 0 16px 0;
    text-align: center;
    font-size: 2em;
    letter-spacing: 2px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}
.main-container {
    display: flex;
    flex: 1;
    padding: 20px;
    gap: 20px;
    max-width: 1400px;
    margin: 0 auto;
    width: 100%;
}
.menu-section {
    flex: 1;
    background: #fff;
    box-shadow: 0 2px 15px rgba(0,0,0,0.04);
    border-radius: 12px;
    padding: 24px 18px;
    overflow-y: auto;
}
.menu-category {
    border-radius: 8px;
    margin-bottom: 18px;
    background: #e7eafd;
    box-shadow: 0 1px 4px rgba(0,0,0,0.05);
    padding: 8px 0 0 0;
    overflow: hidden;
}
.menu-category h3 {
    font-size: 1.35em;
    font-weight: bold;
    padding: 8px 18px;
    margin: 0;
    cursor: default;
}
.menu-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 18px;
    padding: 18px;
}
.menu-card {
    background: #fcfcfe;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    width: 220px;
    padding: 12px;
    display: flex;
    flex-direction: column;
    align-items: center;
}
.menu-card img {
    width: 180px;
    height: 120px;
    object-fit: cover;
    border-radius: 7px;
    margin-bottom: 8px;
    background: #ececec;
}
.menu-card h4 {
    margin: 6px 0 4px 0;
    font-size: 1.1em;
    font-weight: 600;
}
.menu-card .desc {
    font-size: 0.97em;
    color: #555;
    margin-bottom: 6px;
}
.menu-card .price {
    color: #2353c5;
    font-weight: bold;
    margin-bottom: 10px;
    font-size: 1.12em;
}
.order-controls {
    display: flex;
    gap: 10px;
    align-items: center;
}
.order-controls button {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    border: none;
    background: #2353c5;
    color: #fff;
    font-weight: bold;
    font-size: 1.1em;
    cursor: pointer;
}
.order-count {
    font-size: 1em;
    padding: 2px 10px;
}
.cart-link {
    position: fixed;
    top: 16px;
    right: 20px;
    background: #2353c5;
    color: white;
    padding: 10px 15px;
    text-decoration: none;
    border-radius: 5px;
    font-weight: bold;
    font-size: 1.04em;
    z-index: 101;
}
.back-btn {
    position: fixed;
    top: 16px;
    left: 20px;
    background: #ff7043;
    color: white;
    padding: 10px 15px;
    text-decoration: none;
    border-radius: 5px;
    font-weight: bold;
    font-size: 1.04em;
    z-index: 101;
}
/* Order Summary Styles */
.order-summary {
    width: 320px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 15px rgba(0,0,0,0.1);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    height: fit-content;
    position: sticky;
    top: 80px;
}
.order-summary-header {
    background: #2353c5;
    color: #fff;
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.order-summary-header h3 {
    margin: 0;
    font-size: 1.2em;
}
.order-summary-content {
    padding: 20px;
}
.order-summary ul {
    padding: 0;
    margin: 0 0 15px 0;
    list-style: none;
    max-height: 300px;
    overflow-y: auto;
}
.order-summary li {
    margin-bottom: 12px;
    font-size: 1em;
    display: flex;
    justify-content: space-between;
    padding-bottom: 8px;
    border-bottom: 1px solid #eee;
    align-items: center;
}
.order-summary li .item-info {
    flex: 1;
}
.order-summary li .remove-btn {
    background: #ff4444;
    color: white;
    border: none;
    border-radius: 4px;
    padding: 4px 8px;
    margin-left: 10px;
    cursor: pointer;
    font-size: 0.8em;
}
.place-order-btn {
    background: #2353c5;
    color: #fff;
    width: 100%;
    padding: 14px;
    font-size: 1.1em;
    border: none;
    border-radius: 6px;
    font-weight: bold;
    cursor: pointer;
    margin-top: 10px;
    transition: background 0.3s;
}
.place-order-btn:hover {
    background: #1a4091;
}
.order-total {
    font-weight: bold;
    font-size: 1.2em;
    margin-top: 15px;
    padding-top: 10px;
    border-top: 2px solid #eee;
    display: flex;
    justify-content: space-between;
}
.item-count {
    font-size: 0.9em;
    color: #f0f0f0;
    font-weight: normal;
}
.empty-cart {
    text-align: center;
    color: #888;
    padding: 20px 0;
}
@media (max-width: 1024px) {
    .main-container {
        flex-direction: column;
    }
    .order-summary {
        width: 100%;
        position: relative;
        top: 0;
        margin-top: 20px;
    }
}
@media (max-width: 600px) {
    .menu-grid { flex-direction: column; align-items: center;}
    .menu-card { width: 95vw; }
}
</style>
</head>
<body>
<header>🍽️ Our Menu</header>
<a href="home.php" class="back-btn">← Home</a>
<a href="cart.php" class="cart-link">🛒 View Cart</a>
<div class="main-container">
    <div class="menu-section">
    <?php if (empty($menuByCategory)): ?>
        <p>No menu items found.</p>
    <?php else: ?>
        <?php $index = 0; foreach ($menuByCategory as $category => $items): ?>
            <div class="menu-category">
                <h3><?php echo htmlspecialchars($category); ?></h3>
                <div class="menu-grid">
                    <?php foreach ($items as $item): ?>
                        <div class="menu-card">
                            <img src="uploads/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" onerror="this.src='uploads/default-food.jpg';">
                            <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                            <?php if(!empty($item['description'])): ?>
                                <div class="desc"><?php echo htmlspecialchars($item['description']); ?></div>
                            <?php endif; ?>
                            <div class="price">₹<?php echo $item['price']; ?></div>
                            <div class="order-controls">
                                <button type="button" onclick="changeQty(<?php echo $index; ?>, -1)">-</button>
                                <span class="order-count" id="count<?php echo $index; ?>">0</span>
                                <button type="button" onclick="changeQty(<?php echo $index; ?>, 1)">+</button>
                            </div>
                        </div>
                    <?php $index++; endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
    </div>
    <div class="order-summary">
        <div class="order-summary-header">
            <h3>Your Order <span class="item-count" id="itemCount">(0 items)</span></h3>
        </div>
        <div class="order-summary-content">
            <form method="POST" action="cart.php" onsubmit="return prepareOrderData();">
                <ul id="orderList">
                    <li class="empty-cart">No items selected</li>
                </ul>
                <div class="order-total" id="orderTotal">
                    <span>Total:</span>
                    <span>₹0</span>
                </div>
                <input type="hidden" name="orderData" id="orderData">
                <button type="submit" class="place-order-btn">Place Order</button>
            </form>
        </div>
    </div>
</div>
<script>
const menuItems = <?php
    $flatItems = [];
    foreach ($menuByCategory as $items) {
        foreach ($items as $item) $flatItems[] = $item;
    }
    echo json_encode($flatItems);
?>;
menuItems.forEach(i => i.orders = 0);
function changeQty(index, change) {
    menuItems[index].orders += change;
    if (menuItems[index].orders < 0) menuItems[index].orders = 0;
    document.getElementById(`count${index}`).textContent = menuItems[index].orders;
    updateOrderList();
}
function updateOrderList() {
    const ul = document.getElementById('orderList');
    const itemCount = document.getElementById('itemCount');
    const orderTotal = document.getElementById('orderTotal');
    const totalSpan = orderTotal.querySelector('span:last-child');
    ul.innerHTML = '';
    let hasItems = false;
    let totalItems = 0;
    let totalPrice = 0;
    menuItems.forEach((item, index) => {
        if (item.orders > 0) {
            hasItems = true;
            totalItems += item.orders;
            totalPrice += item.orders * item.price;
            const li = document.createElement('li');
            li.innerHTML = `
                <div class="item-info">
                    <div>${item.name}</div>
                    <div style="font-size: 0.9em; color: #777;">₹${item.price} × ${item.orders}</div>
                </div>
                <div>
                    <div>₹${item.orders * item.price}</div>
                    <button type="button" class="remove-btn" onclick="removeItem(${index})">Remove</button>
                </div>
            `;
            ul.appendChild(li);
        }
    });
    if (!hasItems) {
        ul.innerHTML = '<li class="empty-cart">No items selected</li>';
    }
    itemCount.textContent = `(${totalItems} items)`;
    totalSpan.textContent = `₹${totalPrice}`;
}
function removeItem(index) {
    menuItems[index].orders = 0;
    document.getElementById(`count${index}`).textContent = '0';
    updateOrderList();
}
function prepareOrderData() {
    const selected = menuItems.filter(item => item.orders > 0);
    if (selected.length === 0) {
        alert("❗ Please select items.");
        return false;
    }
    const orderId = 'ORD' + Date.now();
    const data = {
        orderId: orderId,
        items: selected.map(item => ({
            id: item.id,
            name: item.name,
            qty: item.orders,
            price: item.price,
            total: item.orders * item.price
        }))
    };
    document.getElementById('orderData').value = JSON.stringify(data);
    return true;
}
// Initialize order list
updateOrderList();
</script>
</body>
</html>
