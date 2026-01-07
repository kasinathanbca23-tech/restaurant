<?php
session_start();
$conn = new mysqli("localhost", "root", "", "restaurant");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$message = "";
$reservationSlip = null;

// Handle reservation form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['table_id'])) {
    $tableId = intval($_POST['table_id']);
    $reservationTime = $_POST['reservation_time'];
    $reservationDate = date('Y-m-d'); // Today's date

    // Check if table is available
    $result = $conn->query("SELECT * FROM reservation WHERE table_id = $tableId AND table_status = 'Available'");
    if ($result->num_rows > 0) {
        $table = $result->fetch_assoc();
        // Update status to Reserved
        $update = $conn->query("UPDATE reservation SET table_status='Reserved' WHERE table_id=$tableId");
        if ($update) {
            // Insert reservation slip into table_reservations
            $stmt = $conn->prepare("INSERT INTO table_reservations (table_id, table_number, capacity, reservation_date, reservation_time) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("iiiss", $table['table_id'], $table['table_number'], $table['capacity'], $reservationDate, $reservationTime);
            $stmt->execute();
            $reservationId = $stmt->insert_id;
            $stmt->close();

            // Fetch reservation slip
            $slipResult = $conn->query("SELECT * FROM table_reservations WHERE reservation_id = $reservationId");
            $reservationSlip = $slipResult->fetch_assoc();

            // Store in session for profile page
            $_SESSION['reservation'] = $reservationSlip;
            $message = "<p class='message success'>✔️ Table reserved successfully! Show the slip below to the manager.</p>";
        } else {
            $message = "<p class='message error'>❌ Failed to reserve the table.</p>";
        }
    } else {
        $message = "<p class='message error'>❌ Table already reserved or doesn't exist.</p>";
    }
}

// Handle download request
if (isset($_GET['download']) && isset($_SESSION['reservation'])) {
    $reservation = $_SESSION['reservation'];

    // Set headers for download
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="reservation_'.$reservation['reservation_id'].'.pdf"');

    // Create PDF content
    require_once('tcpdf/tcpdf.php');

    // Create new PDF document
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Set document information
    $pdf->SetCreator('Restaurant Reservation System');
    $pdf->SetAuthor('Restaurant');
    $pdf->SetTitle('Reservation Invoice');
    $pdf->SetSubject('Table Reservation');

    // Add a page
    $pdf->AddPage();

    // Set font
    $pdf->SetFont('helvetica', '', 12);

    // Add content
    $html = '
    <h1 style="text-align: center; color: #0066cc;">Table Reservation Invoice</h1>
    <table border="0" cellpadding="5">
        <tr>
            <td><strong>Reservation ID:</strong></td>
            <td>'.$reservation['reservation_id'].'</td>
        </tr>
        <tr>
            <td><strong>Table Number:</strong></td>
            <td>'.$reservation['table_number'].'</td>
        </tr>
        <tr>
            <td><strong>Capacity:</strong></td>
            <td>'.$reservation['capacity'].' people</td>
        </tr>
        <tr>
            <td><strong>Date:</strong></td>
            <td>'.$reservation['reservation_date'].'</td>
        </tr>
        <tr>
            <td><strong>Time:</strong></td>
            <td>'.$reservation['reservation_time'].'</td>
        </tr>
        <tr>
            <td><strong>Reservation Date:</strong></td>
            <td>'.date('Y-m-d H:i:s').'</td>
        </tr>
    </table>
    <p style="text-align: center; margin-top: 30px;">Thank you for your reservation!</p>
    <p style="text-align: center; font-size: 10px; color: #999;">This is a computer-generated invoice. No signature required.</p>
    ';

    $pdf->writeHTML($html, true, false, true, false, '');

    // Output PDF
    $pdf->Output('reservation_'.$reservation['reservation_id'].'.pdf', 'D');
    exit();
}

// Fetch all tables for the visual map
$tablesResult = $conn->query("SELECT * FROM reservation");
$tables = [];
while ($row = $tablesResult->fetch_assoc()) {
    $tables[] = $row;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Restaurant Table Reservation</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f7f6;
            background-image: url('tables.jpg');
            background-size: cover;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 30px;
        }
        .container {
            background: rgba(255, 255, 255, 0.9);
            padding: 40px;
            border-radius: 15px;
            max-width: 900px;
            width: 100%;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            backdrop-filter: blur(5px);
        }
        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }
        .restaurant-layout {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }
        .table {
            position: relative;
            background-color: #fff;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
            border: 2px solid #e0e0e0;
        }
        .table:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.12);
        }
        .table.available {
            border-color: #28a745;
        }
        .table.reserved {
            border-color: #dc3545;
            background-color: #f8d7da;
            cursor: not-allowed;
            opacity: 0.7;
        }
        .table.selected {
            border-color: #007bff;
            box-shadow: 0 0 15px rgba(0, 123, 255, 0.5);
            transform: scale(1.05);
        }
        .table .fa-utensils {
            font-size: 2.5em;
            color: #333;
            margin-bottom: 10px;
        }
        .table-number {
            font-weight: 600;
            font-size: 1.2em;
        }
        .table-capacity {
            color: #666;
            font-size: 0.9em;
        }
        .table-status {
            font-size: 0.8em;
            font-weight: 600;
            margin-top: 10px;
            padding: 3px 8px;
            border-radius: 12px;
            color: #fff;
        }
        .table-status.available {
            background-color: #28a745;
        }
        .table-status.reserved {
            background-color: #dc3545;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1em;
        }
        input[type="submit"] {
            width: 100%;
            padding: 15px;
            margin-top: 15px;
            border-radius: 8px;
            border: none;
            background-color: #007bff;
            color: white;
            font-weight: bold;
            font-size: 1.1em;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        input[type="submit"]:hover {
            background-color: #0056b3;
        }
        input[type="submit"]:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }
        .slip {
            background: #e9f5ff;
            margin-top: 30px;
            padding: 20px;
            border-radius: 8px;
            border-left: 5px solid #007bff;
            animation: fadeIn 0.5s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .slip h3 {
            margin-top: 0;
            color: #0056b3;
        }
        .slip p {
            margin: 10px 0;
        }
        .slip p i {
            margin-right: 10px;
            color: #007bff;
        }
        .message {
            text-align: center;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .today-notice {
            text-align: center;
            margin-bottom: 20px;
            padding: 10px;
            background-color: #e7f3ff;
            border-radius: 8px;
            color: #0056b3;
        }
        .download-btn {
            display: block;
            width: 100%;
            padding: 12px;
            margin-top: 15px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .download-btn:hover {
            background-color: #218838;
        }
        .btn-container {
            display: flex;
            gap: 10px;
        }
        .btn-container button {
            flex: 1;
        }
    </style>
</head>
<body>
<div class="container">
    <h2><i class="fas fa-calendar-alt"></i> Reserve Your Table</h2>
    <div class="today-notice">
        <i class="fas fa-info-circle"></i> You can only reserve tables for today.
    </div>
    <?= $message ?>
    <form method="POST" action="" id="reservationForm" autocomplete="off">
        <!-- Time Selection -->
        <div class="form-group">
            <label for="reservation_time">Select Time:</label>
            <input type="time" id="reservation_time" name="reservation_time" required min="11:00" max="22:00">
        </div>
        <div class="restaurant-layout">
            <?php foreach ($tables as $table): ?>
                <div class="table <?= strtolower($table['table_status']) ?>" data-table-id="<?= $table['table_id'] ?>">
                    <i class="fas fa-utensils"></i>
                    <div class="table-number">Table <?= htmlspecialchars($table['table_number']) ?></div>
                    <div class="table-capacity"><i class="fas fa-users"></i> Capacity: <?= htmlspecialchars($table['capacity']) ?></div>
                    <div class="table-status <?= strtolower($table['table_status']) ?>"><?= htmlspecialchars($table['table_status']) ?></div>
                </div>
            <?php endforeach; ?>
        </div>
        <input type="hidden" name="table_id" id="selected_table_id">
        <input type="submit" value="Reserve Selected Table" id="reserveBtn" disabled>
    </form>
    <?php if ($reservationSlip): ?>
        <div class="slip">
            <h3><i class="fas fa-receipt"></i> Reservation Confirmed!</h3>
            <p><i class="fas fa-ticket-alt"></i><strong>Reservation ID:</strong> <?= htmlspecialchars($reservationSlip['reservation_id']) ?></p>
            <p><i class="fas fa-utensils"></i><strong>Table Number:</strong> <?= htmlspecialchars($reservationSlip['table_number']) ?></p>
            <p><i class="fas fa-users"></i><strong>Capacity:</strong> <?= htmlspecialchars($reservationSlip['capacity']) ?></p>
            <p><i class="far fa-calendar-alt"></i><strong>Date:</strong> <?= htmlspecialchars($reservationSlip['reservation_date']) ?></p>
            <p><i class="far fa-clock"></i><strong>Time:</strong> <?= htmlspecialchars($reservationSlip['reservation_time']) ?></p>

            <div class="btn-container">
                <button class="download-btn" onclick="window.print()">
                    <i class="fas fa-print"></i> Print Receipt
                </button>
                <a href="?download=1" class="download-btn">
                    <i class="fas fa-file-pdf"></i> Download PDF
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>
<script>
    const tables = document.querySelectorAll('.table');
    const selectedTableIdInput = document.getElementById('selected_table_id');
    const reserveBtn = document.getElementById('reserveBtn');
    tables.forEach(table => {
        table.addEventListener('click', () => {
            if (table.classList.contains('reserved')) {
                return;
            }
            // Remove 'selected' from all
            tables.forEach(t => t.classList.remove('selected'));
            // Add 'selected' to clicked
            table.classList.add('selected');
            selectedTableIdInput.value = table.dataset.tableId;
            reserveBtn.disabled = false;
        });
    });
</script>
</body>
</html>
