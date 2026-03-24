<?php
session_start();
include '../db.php';

// Redirect if not logged in
if(!isset($_SESSION['user_id'])){
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get booking ID from GET
if(!isset($_GET['booking_id']) || empty($_GET['booking_id'])){
    $_SESSION['error'] = "Invalid booking selected.";
    header("Location: mybookings.php");
    exit();
}

$booking_id = intval($_GET['booking_id']);

// Fetch booking info
$booking_q = mysqli_query($conn, "
    SELECT b.*, v.vehicle_name, v.owner_id
    FROM bookings b
    JOIN vehicles v ON b.vehicle_id = v.vehicle_id
    WHERE b.booking_id = $booking_id
    AND b.user_id = $user_id
    LIMIT 1
");

if(mysqli_num_rows($booking_q) == 0){
    $_SESSION['error'] = "Booking not found.";
    header("Location: mybookings.php");
    exit();
}

$booking = mysqli_fetch_assoc($booking_q);

if(isset($_POST['pay_now'])){
    $payment_method = mysqli_real_escape_string($conn, $_POST['payment_method']);
    $amount = floatval($booking['total_amount'] ?? 0);

    mysqli_query($conn, "
        UPDATE bookings
        SET payment_status='Paid',
            payment_date=NOW()
        WHERE booking_id=$booking_id
    ");

    mysqli_query($conn, "
        INSERT INTO transactions (booking_id, user_id, vehicle_id, owner_id, amount, payment_method, status)
        VALUES (
            {$booking['booking_id']},
            $user_id,
            {$booking['vehicle_id']},
            {$booking['owner_id']},
            $amount,
            '$payment_method',
            'Completed'
        )
    ");

    // SweetAlert success via session
    $_SESSION['payment_success'] = "Payment of NPR {$amount} completed successfully!";
    header("Location: mybookings.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Payment - Ridezy</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body style="padding-top:80px;">
<div class="container">
<h2 class="mb-4">Payment for Booking #<?= $booking['booking_id'] ?></h2>

<div class="card shadow-sm p-4 mb-4">
    <h5>Vehicle: <?= htmlspecialchars($booking['vehicle_name']) ?></h5>
    <p>Rental Period: <?= $booking['start_date'] ?> to <?= $booking['end_date'] ?></p>
    <p>Total Amount: <strong>NPR <?= number_format($booking['total_amount'],2) ?></strong></p>
</div>

<form method="POST">
    <div class="mb-3">
        <label class="form-label">Payment Method</label>
        <select name="payment_method" class="form-select" required>
            <option value="">Select Method</option>
            <option value="Cash">Cash</option>
            <option value="eSewa">eSewa</option>
            <option value="Khalti">Khalti</option>
        </select>
    </div>
    <button type="submit" name="pay_now" class="btn btn-success">Confirm Payment</button>
    <a href="mybookings.php" class="btn btn-secondary">Cancel</a>
</form>
</div>

</body>
</html>
