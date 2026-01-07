<?php
$conn = new mysqli("localhost", "root", "", "restaurant");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$success = "";
$error = "";

// Delete logic
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    // Get image filename before deleting
    $img_res = $conn->query("SELECT image FROM menu WHERE id='$delete_id'");
    $img_row = $img_res ? $img_res->fetch_assoc() : null;
    if ($conn->query("DELETE FROM menu WHERE id='$delete_id'")) {
        // Delete image from uploads if exists
        if ($img_row && !empty($img_row['image']) && file_exists('uploads/' . $img_row['image'])) {
            unlink('uploads/' . $img_row['image']);
        }
        $success = "✔️ Item deleted successfully!";
    } else {
        $error = "❌ Failed to delete item.";
    }
}

// Add logic
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['name'])) {
    $name = trim($_POST['name']);
    $price = floatval($_POST['price']);
    $category = $_POST['category'] ?? '';
    $imageName = "";

    // Validate required fields
    if (!$category) {
        $error = "❗ Please select a category.";
    } elseif (empty($_FILES['image']['name'])) {
        $error = "❗ Please select an image.";
    } else {
        $targetDir = "uploads/";
        // Ensure uploads folder exists
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        // Sanitize and prevent overwriting image file
        $originalName = basename($_FILES["image"]["name"]);
        $imageFileType = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($imageFileType, $allowedTypes)) {
            $error = "❌ Only JPG, JPEG, PNG, and GIF files are allowed.";
        } else {
            // Sanitize filename
            $safeName = preg_replace('/[^a-zA-Z0-9\.\-\_]/', '_', pathinfo($originalName, PATHINFO_FILENAME));
            $imageName = $safeName . "." . $imageFileType;
            $targetFile = $targetDir . $imageName;
            $i = 1;
            while (file_exists($targetFile)) {
                $imageName = $safeName . "_$i." . $imageFileType;
                $targetFile = $targetDir . $imageName;
                $i++;
            }

            // Move file and insert DB record
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
                $stmt = $conn->prepare("INSERT INTO menu (name, image, price, category) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssds", $name, $imageName, $price, $category);
                if ($stmt->execute()) {
                    $success = "✔️ Food item added!";
                } else {
                    $error = "❌ Database insert failed: " . $stmt->error;
                    // Remove uploaded file if DB insert failed
                    unlink($targetFile);
                }
                $stmt->close();
            } else {
                $error = "❌ Failed to upload image. Check folder permissions.";
            }
        }
    }
}

// Fetch menu items for display
$menuItems = [];
$result = $conn->query("SELECT * FROM menu ORDER BY category, name");
while ($row = $result->fetch_assoc()) {
    $menuItems[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Menu Item</title>
    <style>
        body {
            font-family: Arial;
            background-color: #f4f4f4;
            padding: 20px;
        }
        form {
            background: white;
            padding: 25px;
            max-width: 500px;
            margin: auto;
            border-radius: 10px;
            box-shadow: 0 0 8px rgba(0,0,0,0.2);
        }
        input[type="text"], input[type="number"], input[type="file"] {
            width: 100%;
            margin-bottom: 10px;
            padding: 10px;
        }
        button {
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            border: none;
            width: 100%;
            cursor: pointer;
        }
        .msg {
            text-align: center;
            margin-bottom: 10px;
            font-weight: bold;
        }
        a.back-home {
            display: inline-block;
            margin-bottom: 20px;
            padding: 8px 15px;
            background-color: #343a40;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .category-box {
            border: 1px solid #ccc;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 15px;
            background-color: #f9f9f9;
        }
        .category-box label {
            display: block;
            margin-bottom: 8px;
            cursor: pointer;
        }
        .category-box input[type="radio"] {
            margin-right: 10px;
        }
        .menu-list-box {
            max-width: 700px;
            margin: 40px auto 0 auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 8px rgba(0,0,0,0.12);
            padding: 20px;
        }
        .menu-list-title {
            font-size: 1.2em;
            margin-bottom: 12px;
            color: #333;
        }
        table.menu-list {
            width: 100%;
            border-collapse: collapse;
        }
        table.menu-list th, table.menu-list td {
            border: 1px solid #e2e2e2;
            padding: 10px 8px;
            text-align: center;
        }
        table.menu-list th {
            background: #f1f3fd;
            font-weight: bold;
        }
        table.menu-list td img {
            width: 60px;
            height: 40px;
            object-fit: cover;
            border-radius: 5px;
        }
        a.delete-btn {
            color: #fff;
            background: #e74c3c;
            padding: 5px 12px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: bold;
            transition: background 0.2s;
        }
        a.delete-btn:hover {
            background: #c0392b;
        }
    </style>
</head>
<body>

<a href="admin_dashboard.php" class="back-home">← Back to Dashboard</a>

<form method="POST" enctype="multipart/form-data">
    <h2>Add New Menu Item</h2>
    <?php if ($success) echo "<div class='msg' style='color:green;'>$success</div>"; ?>
    <?php if ($error) echo "<div class='msg' style='color:red;'>$error</div>"; ?>
    
    <input type="text" name="name" placeholder="Food Name" required />
    <input type="file" name="image" accept="image/*" required />
    <input type="number" name="price" placeholder="Price (e.g., 150)" step="0.01" required />

    <!-- Category Box -->
    <label><strong>Select Category:</strong></label>
    <div class="category-box">
        <label><input type="radio" name="category" value="Starters" required> Starters</label>
        <label><input type="radio" name="category" value="Main Course"> Main Course</label>
        <label><input type="radio" name="category" value="Snacks"> Snacks</label>
        <label><input type="radio" name="category" value="Pizza"> Pizza</label>
        <label><input type="radio" name="category" value="Burger"> Burger</label>
        <label><input type="radio" name="category" value="Drinks"> Drinks</label>
        <label><input type="radio" name="category" value="Desserts"> Desserts</label>
    </div>

    <button type="submit">Add Item</button>
</form>

<?php if (!empty($menuItems)): ?>
<div class="menu-list-box">
    <div class="menu-list-title">Current Menu Items</div>
    <table class="menu-list">
        <tr>
            <th>#</th>
            <th>Image</th>
            <th>Name</th>
            <th>Category</th>
            <th>Price (₹)</th>
            <th>Action</th>
        </tr>
        <?php foreach ($menuItems as $idx => $item): ?>
        <tr>
            <td><?= $idx + 1 ?></td>
            <td>
                <?php if (!empty($item['image']) && file_exists('uploads/' . $item['image'])): ?>
                    <img src="uploads/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                <?php else: ?>
                    <span style="color:#888;">No Image</span>
                <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($item['name']) ?></td>
            <td><?= htmlspecialchars($item['category']) ?></td>
            <td><?= number_format($item['price'],2) ?></td>
            <td>
                <a href="?delete=<?= $item['id'] ?>" class="delete-btn" onclick="return confirm('Delete this item?')">Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php endif; ?>

</body>
</html>