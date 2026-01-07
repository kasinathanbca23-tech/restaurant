
<?php
$host = 'localhost';
$db = 'restaurant';
$user = 'root';
$pass = '';
$error = "";
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $customerId = $_POST["customerId"];
    $first_name = $_POST["first_name"];
    $last_name = $_POST["last_name"];
    $phone = $_POST["phone"];
    $email = $_POST["email"];
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
    $role = "user"; // Role is set automatically
    $check = $conn->prepare("SELECT id FROM users WHERE customerId = ?");
    $check->bind_param("s", $customerId);
    $check->execute();
    $check->store_result();
    if ($check->num_rows > 0) {
        $error = "Customer ID already exists!";
    } else {
        $insert = $conn->prepare("INSERT INTO users (customerId, first_name, last_name, phone, email, password, role) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $insert->bind_param("sssssss", $customerId, $first_name, $last_name, $phone, $email, $password, $role);
        if ($insert->execute()) {
            header("Location: login.php?success=1");
            exit();
        } else {
            $error = "Error: Could not register. Try again.";
        }
        $insert->close();
    }
    $check->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Registration Form</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-image: url('glass.jpg');
      background-size: cover;
      background-position: center;
      padding: 20px;
    }
    .back-button {
      position: absolute;
      top: 20px;
      left: 20px;
    }
    .back-button a {
      background-color: #444;
      color: white;
      padding: 8px 12px;
      border-radius: 5px;
      text-decoration: none;
      font-size: 14px;
    }
    .container {
      max-width: 350px;
      margin: 80px auto;
      background: rgba(0, 0, 0, 0.7);
      padding: 40px;
      border-radius: 8px;
      color: white;
    }
    h2 {
      text-align: center;
      margin-bottom: 20px;
    }
    input[type="text"],
    input[type="email"],
    input[type="password"] {
      width: 100%;
      padding: 10px;
      margin-top: 8px;
      margin-bottom: 15px;
      border: 1px solid #ccc;
      border-radius: 5px;
    }
    input[type="submit"] {
      width: 100%;
      background-color: #5cb85c;
      color: white;
      padding: 12px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      font-size: 16px;
    }
    .error {
      text-align: center;
      color: red;
      font-weight: bold;
      margin-bottom: 15px;
    }
  </style>
</head>
<body>
  <div class="back-button">
    <a href="login.php">&larr; Back</a>
  </div>
  <div class="container">
    <h2>Registration Form</h2>
    <?php if (!empty($error)): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post" action="">
      <label for="customerId">Customer ID:</label>
      <input type="text" id="customerId" name="customerId" required>
      <label for="first_name">First Name:</label>
      <input type="text" id="first_name" name="first_name" required>
      <label for="last_name">Last Name:</label>
      <input type="text" id="last_name" name="last_name" required>
      <label for="phone">Phone Number:</label>
      <input type="text" id="phone" name="phone" required>
      <label for="email">Email:</label>
      <input type="email" id="email" name="email" required>
      <label for="password">Password:</label>
      <input type="password" id="password" name="password" required>
      <input type="submit" value="Register">
    </form>
  </div>
</body>
</html> 