<?php
session_start();
$host = 'localhost';
$db = 'restaurant';
$user = 'root';
$pass = '';
$error = "";
$success = "";
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $message = trim($_POST["message"]);
    $rating = isset($_POST["rating"]) ? (int)$_POST["rating"] : 0;
    // Basic validation
    if (empty($name) || empty($email) || empty($message) || $rating < 1) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        // Prepare and bind
        $stmt = $conn->prepare("INSERT INTO feedback (name, email, message, rating) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $name, $email, $message, $rating);
        if ($stmt->execute()) {
            $success = "Thank you for your feedback!";
            $name = $email = $message = ""; // Clear the form fields
        } else {
            $error = "Error submitting feedback. Please try again.";
        }
        $stmt->close();
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Feedback Form</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            padding: 20px;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .container {
            max-width: 500px;
            width: 100%;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        .container:hover {
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.15);
        }
        h2 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 25px;
            font-weight: 600;
        }
        .rating {
            display: flex;
            justify-content: center;
            margin: 20px 0;
            font-size: 30px;
            color: #ffd700;
            cursor: pointer;
        }
        .rating i {
            margin: 0 5px;
            transition: color 0.3s;
        }
        .rating i:hover, .rating i.active {
            color: #ffa500;
        }
        input[type="text"],
        input[type="email"],
        textarea {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: border 0.3s;
        }
        input[type="text"]:focus,
        input[type="email"]:focus,
        textarea:focus {
            border-color: #3498db;
            outline: none;
        }
        textarea {
            height: 120px;
            resize: vertical;
        }
        input[type="submit"] {
            background: linear-gradient(to right, #3498db, #2ecc71);
            color: white;
            padding: 12px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s;
        }
        input[type="submit"]:hover {
            background: linear-gradient(to right, #2980b9, #27ae60);
            transform: translateY(-2px);
        }
        .error {
            color: #e74c3c;
            text-align: center;
            margin: 10px 0;
            font-weight: 500;
        }
        .success {
            color: #27ae60;
            text-align: center;
            margin: 10px 0;
            font-weight: 500;
        }
        #ratingValue {
            text-align: center;
            font-weight: 600;
            color: #7f8c8d;
            margin-top: 10px;
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 12px;
            text-align: center;
            max-width: 400px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
            animation: modalopen 0.5s;
        }
        @keyframes modalopen {
            from { opacity: 0; transform: translateY(-50px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .modal-button {
            background: #2ecc71;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 20px;
            font-weight: 600;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>We Value Your Feedback!</h2>
    <?php if (!empty($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="success"><?= htmlspecialchars($success) ?></div>
        <div id="thanksModal" class="modal">
            <div class="modal-content">
                <i class="fas fa-check-circle" style="font-size: 50px; color: #27ae60; margin-bottom: 20px;"></i>
                <h3>Thank You!</h3>
                <p>Your feedback helps us improve.</p>
                <button class="modal-button" onclick="document.getElementById('thanksModal').style.display='none'">Close</button>
            </div>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                document.getElementById('thanksModal').style.display = 'flex';
            });
        </script>
    <?php endif; ?>
    <form action="" method="post">
        <input type="text" name="name" placeholder="Your Name" required value="<?= htmlspecialchars($name ?? '') ?>">
        <input type="email" name="email" placeholder="Your Email" required value="<?= htmlspecialchars($email ?? '') ?>">
        <div class="rating">
            <i class="far fa-star" data-value="1"></i>
            <i class="far fa-star" data-value="2"></i>
            <i class="far fa-star" data-value="3"></i>
            <i class="far fa-star" data-value="4"></i>
            <i class="far fa-star" data-value="5"></i>
        </div>
        <input type="hidden" name="rating" id="ratingValue" value="0">
        <textarea name="message" placeholder="Your Feedback" required><?= htmlspecialchars($message ?? '') ?></textarea>
        <input type="submit" value="Submit Feedback">
    </form>
</div>
<script>
    const stars = document.querySelectorAll('.rating i');
    stars.forEach((star, index) => {
        star.addEventListener('click', () => {
            document.getElementById('ratingValue').value = index + 1;
            stars.forEach((s, i) => {
                s.classList.toggle('active', i <= index);
                s.classList.toggle('far', i > index);
                s.classList.toggle('fas', i <= index);
            });
        });
    });
</script>
</body>
</html>
