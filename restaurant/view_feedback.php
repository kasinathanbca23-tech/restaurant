<?php
session_start();
require_once "config.php";
// Ensure only admin can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle feedback deletion
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM feedback WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();
    $success = "Feedback deleted successfully.";
}

// Fetch all feedbacks with user details
$sql = "SELECT id, name, email, message, rating, created_at FROM feedback ORDER BY created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Customer Feedbacks</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .header {
            background: linear-gradient(135deg, #003366, #0055a5);
            color: white;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .container {
            width: 95%;
            margin: 30px auto;
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        h2 {
            color: #003366;
            margin-bottom: 20px;
            font-weight: 600;
        }
        .success {
            color: #27ae60;
            text-align: center;
            margin: 10px 0;
            font-weight: 500;
        }
        .search-filter {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        #search {
            padding: 10px;
            width: 300px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        .filter {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }
        th {
            background: #003366;
            color: white;
            cursor: pointer;
        }
        th:hover {
            background: #004488;
        }
        tr:hover {
            background: #f1f1f1;
        }
        .stars {
            color: gold;
            font-size: 16px;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        .action-buttons a {
            text-decoration: none;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 14px;
        }
        .reply-btn {
            background: #27ae60;
        }
        .delete-btn {
            background: #e74c3c;
        }
        .reply-btn:hover {
            background: #219653;
        }
        .delete-btn:hover {
            background: #c0392b;
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            color: #003366;
            font-weight: 600;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        @media (max-width: 768px) {
            .container {
                width: 100%;
                padding: 15px;
            }
            .search-filter {
                flex-direction: column;
                gap: 10px;
            }
            #search, .filter {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1><i class="fas fa-comments"></i> Customer Feedbacks</h1>
    </div>
    <div class="container">
        <?php if (isset($success)): ?>
            <div class="success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <h2>All Customer Feedbacks</h2>
        <div class="search-filter">
            <input type="text" id="search" placeholder="Search by name, email, or message...">
            <select class="filter" id="ratingFilter">
                <option value="">All Ratings</option>
                <option value="5">★★★★★</option>
                <option value="4">★★★★☆</option>
                <option value="3">★★★☆☆</option>
                <option value="2">★★☆☆☆</option>
                <option value="1">★☆☆☆☆</option>
            </select>
        </div>
        <table id="feedbacksTable">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Email</th>
                    <th>Feedback</th>
                    <th>Rating</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['name']); ?></td>
                            <td><?= htmlspecialchars($row['email']); ?></td>
                            <td><?= htmlspecialchars($row['message']); ?></td>
                            <td class="stars">
                                <?= str_repeat("★", $row['rating']) . str_repeat("☆", 5 - $row['rating']); ?>
                            </td>
                            <td><?= htmlspecialchars(date("M j, Y g:i a", strtotime($row['created_at']))); ?></td>
                            <td class="action-buttons">
                                <a href="reply_feedback.php?id=<?= $row['id'] ?>" class="reply-btn"><i class="fas fa-reply"></i> Reply</a>
                                <a href="?delete_id=<?= $row['id'] ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this feedback?')"><i class="fas fa-trash"></i> Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center;">No feedbacks yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <a href="admin_dashboard.php" class="back-link">⬅ Back to Dashboard</a>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('search');
            const ratingFilter = document.getElementById('ratingFilter');
            const tableRows = document.querySelectorAll('#feedbacksTable tbody tr');

            // Search functionality
            searchInput.addEventListener('keyup', function() {
                const searchTerm = this.value.toLowerCase();
                tableRows.forEach(row => {
                    const name = row.cells[0].textContent.toLowerCase();
                    const email = row.cells[1].textContent.toLowerCase();
                    const message = row.cells[2].textContent.toLowerCase();
                    if (name.includes(searchTerm) || email.includes(searchTerm) || message.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });

            // Rating filter functionality
            ratingFilter.addEventListener('change', function() {
                const selectedRating = this.value;
                tableRows.forEach(row => {
                    const ratingCell = row.cells[3];
                    if (selectedRating === '' || ratingCell.textContent.includes('★'.repeat(selectedRating))) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        });
    </script>
</body>
</html>
