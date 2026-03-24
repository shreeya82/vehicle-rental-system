<?php
include '../db.php';
$id = $_POST['booking_id'];

mysqli_query($conn,"
UPDATE bookings
SET payment_status='Paid',
    payment_date=NOW()
WHERE booking_id='$id'
");
