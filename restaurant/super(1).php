<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Restaurant Menu</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f2f2f2;
      margin: 0;
      padding: 20px;
    }
    h1 {
      text-align: center;
      margin-bottom: 30px;
    }
    .menu-container {
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
      justify-content: center;
    }
    .menu-card {
      background: white;
      border-radius: 10px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.2);
      width: 300px;
      overflow: hidden;
      transition: 0.3s;
    }
    .menu-card:hover {
      transform: scale(1.03);
    }
    .menu-card img {
      width: 100%;
      height: 200px;
      object-fit: cover;
    }
    .menu-card .content {
      padding: 15px;
    }
    .menu-card h3 {
      margin-top: 0;
    }
    .menu-card p {
      margin: 5px 0;
    }
    .order-btn {
      background: #28a745;
      color: white;
      border: none;
      padding: 10px;
      width: 100%;
      cursor: pointer;
      font-size: 16px;
      margin-top: 10px;
    }
    .order-btn:hover {
      background: #218838;
    }
    .order-controls {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-top: 10px;
    }
    .order-controls button {
      width: 30px;
      height: 30px;
      font-size: 18px;
      font-weight: bold;
      border: none;
      border-radius: 50%;
      background-color: #007bff;
      color: white;
      cursor: pointer;
    }
    .order-controls button:hover {
      background-color: #0056b3;
    }
    .order-count {
      font-size: 18px;
      font-weight: bold;
      color: #007bff;
    }
  </style>
</head>
<body>

  <h1>🍽️ Our Menu</h1>
  <div class="menu-container" id="menuContainer">
    <!-- Menu items will be loaded here by JS -->
  </div>

  <script>
    const menuItems = [
      {
        id: 1,
        name: "Masala Dosa",
        image: "Dosa.jpg",
        description: "Crispy dosa with spicy potato filling.",
        category: "South Indian",
        price: 80,
        orders: 0
      },
      {
        id: 2,
        name: "Chicken Biryani",
        image: "Biriyani.jpg",
        description: "Aromatic rice with tender chicken and spices.",
        category: "Indian",
        price: 180,
        orders: 0
      },
      {
        id: 3,
        name: "Cold Coffee",
        image: "Cofee.jpg",
        description: "Chilled coffee with cream and ice.",
        category: "Beverage",
        price: 60,
        orders: 0
      },
  {
        id: 3,
        name: "Cold Coffee",
        image: "Cofee.jpg",
        description: "Chilled coffee with cream and ice.",
        category: "Beverage",
        price: 60,
        orders: 0
      }
    ];

    const container = document.getElementById('menuContainer');

    function renderMenu() {
      container.innerHTML = '';
      menuItems.forEach((item, index) => {
        const card = document.createElement('div');
        card.className = 'menu-card';

        card.innerHTML = `
          <img src="${item.image}" alt="${item.name}">
          <div class="content">
            <h3>${item.name}</h3>
            <p><strong>ID:</strong> ${item.id}</p>
            <p><strong>Category:</strong> ${item.category}</p>
            <p><strong>Description:</strong> ${item.description}</p>
            <p><strong>Price:</strong> ₹${item.price}</p>
            <div class="order-controls">
              <button onclick="decreaseOrder(${index})">-</button>
              <span class="order-count" id="orderCount${index}">${item.orders}</span>
              <button onclick="increaseOrder(${index})">+</button>
            </div>
            <button class="order-btn" onclick="orderItem(${index})">Order Now</button>
          </div>
        `;

        container.appendChild(card);
      });
    }

    function increaseOrder(index) {
      menuItems[index].orders++;
      updateOrderCount(index);
    }

    function decreaseOrder(index) {
      if (menuItems[index].orders > 0) {
        menuItems[index].orders--;
        updateOrderCount(index);
      }
    }

    function updateOrderCount(index) {
      document.getElementById(`orderCount${index}`).textContent = menuItems[index].orders;
    }

    function orderItem(index) {
      const item = menuItems[index];
      if (item.orders > 0) {
        alert(`✅ You have ordered ${item.orders} × ${item.name} for ₹${item.orders * item.price}`);
      } else {
        alert(`❗ Please select at least one ${item.name} to order.`);
      }
    }

    renderMenu();
  </script>

</body>
</html>