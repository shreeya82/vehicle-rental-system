<?php
session_start();
include '../db.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user'){
    header("Location: index.php");
    exit();
}

if(!isset($_GET['id'])){
    header("Location: vehicles.php");
    exit();
}

$vehicle_id = intval($_GET['id']);
$vehicle_query = mysqli_query($conn, "SELECT * FROM vehicles WHERE vehicle_id = $vehicle_id");
if(mysqli_num_rows($vehicle_query) == 0){
    echo "<script>alert('Vehicle not found'); window.location='vehicles.php';</script>";
    exit();
}
$vehicle = mysqli_fetch_assoc($vehicle_query);

$check_booking = mysqli_query($conn, "
    SELECT COUNT(*) as booked_count FROM bookings
    WHERE vehicle_id=$vehicle_id 
    AND status='Confirmed'
    AND returned_status='Not Returned'
");
$row = mysqli_fetch_assoc($check_booking);
$remaining_units = $vehicle['quantity'] - $row['booked_count'];
if($remaining_units < 0) $remaining_units = 0;


$force_booking = isset($_POST['force_booking']) ? 1 : 0;

if(isset($_POST['book_vehicle'])){
    $user_id = $_SESSION['user_id'];
    $start_date = mysqli_real_escape_string($conn, $_POST['start_date']);
    $end_date = mysqli_real_escape_string($conn, $_POST['end_date']);
    $return_time = mysqli_real_escape_string($conn, $_POST['return_time']);
    $purpose_array = $_POST['purpose'] ?? [];
    $purpose = implode(', ', $purpose_array);

    $total_days = (strtotime($end_date) - strtotime($start_date))/(60*60*24) + 1;
    if($total_days <= 0){
        $_SESSION['booking_error'] = "End date must be after start date.";
        header("Location: book_vehicle.php?id=$vehicle_id"); exit();
    }
    if($total_days > 10){
        $_SESSION['booking_error'] = "Booking cannot exceed 10 days.";
        header("Location: book_vehicle.php?id=$vehicle_id"); exit();
    }

    if($remaining_units <= 0){
        $_SESSION['booking_error'] = "No units available for selected dates.";
        header("Location: book_vehicle.php?id=$vehicle_id"); exit();
    }

    $total_amount = $total_days * $vehicle['price_per_day'];

    $insert = mysqli_query($conn, "
        INSERT INTO bookings (user_id, vehicle_id, start_date, end_date, return_time, purpose, total_amount, status)
        VALUES ($user_id, $vehicle_id, '$start_date', '$end_date', '$return_time', '$purpose', $total_amount, 'Pending')
    ");

    if($insert){
        $booking_id = mysqli_insert_id($conn);
        $message = "New booking: {$vehicle['vehicle_name']} by user ID $user_id";
        mysqli_query($conn, "INSERT INTO notifications (type, reference_id, message) VALUES ('booking',$booking_id,'$message')");

        $_SESSION['user_msg'] = "Booking successful! Total: Rs. $total_amount";
        header("Location: vehicles.php");
        exit();
    } else {
        $_SESSION['booking_error'] = "Error booking vehicle: ".mysqli_error($conn);
        header("Location: book_vehicle.php?id=$vehicle_id");
        exit();
    }
}


include 'navbar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Book Vehicle | Ridezy</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/user.css?v=1.0">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<div class="container py-5 mt-5">
    <a href="vehicles.php" class="btn btn-secondary mb-3">&larr; Back to Vehicles</a>
    <h2 class="text-center mb-4">Book Vehicle</h2>

    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <img src="<?= !empty($vehicle['image']) ? '../uploads/'.$vehicle['image'] : '../assets/images/placeholder.jpg'; ?>" class="card-img-top" style="height:250px; object-fit:cover;" alt="<?= $vehicle['vehicle_name']; ?>">
                <div class="card-body">
                    <h5 class="card-title"><?= $vehicle['vehicle_name']; ?></h5>
                    <p class="card-text">
                        Type: <?= $vehicle['vehicle_type']; ?><br>
                        Price/Day: Rs. <?= $vehicle['price_per_day']; ?><br>
                        Available Units: <span id="remaining_units"><?= $remaining_units; ?></span><br>
                        Status: <?= $vehicle['availability']; ?>
                    </p>

                    <?php if(isset($_SESSION['booking_error'])): ?>
                        <div class="alert alert-danger"><?= $_SESSION['booking_error']; unset($_SESSION['booking_error']); ?></div>
                    <?php endif; ?>

                    <form method="POST" id="bookingForm">
                        <input type="hidden" name="book_vehicle" value="1">
                        <input type="hidden" id="price" value="<?= $vehicle['price_per_day']; ?>">

                        <div class="mb-3">
                            <label>Start Date</label>
                            <input type="date" name="start_date" id="start_date" class="form-control" min="<?= date('Y-m-d'); ?>">
                        </div>

                        <div class="mb-3">
                            <label>End Date</label>
                            <input type="date" name="end_date" id="end_date" class="form-control" min="<?= date('Y-m-d'); ?>">
                            <small class="text-muted">Maximum booking duration: 10 days</small>
                        </div>

                        <div class="mb-3">
                            <label>Return Time</label>
                            <select name="return_time" id="return_time" class="form-control">
                              <option value="">Select Return Time</option>
                              <option value="12:00">12:00 PM</option>
                              <option value="20:00">08:00 PM</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label>Purpose</label>
                            <div class="purpose-box p-3 border rounded">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="purpose[]" id="long_tour" value="Long Tour">
                                    <label class="form-check-label" for="long_tour">Long Tour</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="purpose[]" id="ride_out" value="Ride Out">
                                    <label class="form-check-label" for="ride_out">Ride Out</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="purpose[]" id="daily_commute" value="Daily Commute">
                                    <label class="form-check-label" for="daily_commute">Daily Commute</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="purpose[]" id="others" value="Others">
                                    <label class="form-check-label" for="others">Others</label>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Book Now</button>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/user.js"></script>

<script>
document.getElementById('bookingForm').addEventListener('submit', function(e){
    e.preventDefault();

    let start_date = document.getElementById('start_date').value;
    let end_date = document.getElementById('end_date').value;
    let price = parseFloat(document.getElementById('price').value);

    if(!start_date || !end_date){
        Swal.fire('Error','Please select inputs','error');
        return;
    }

    // Calculate total days
    let start = new Date(start_date);
    let end = new Date(end_date);
    let total_days = Math.floor((end - start)/(1000*60*60*24)) + 1;
    let total_price = total_days * price;

    // First, check for overlapping bookings
    fetch('check_booking.php', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: `start_date=${start_date}&end_date=${end_date}`
    })
    .then(res => res.json())
    .then(data => {

        // Function to show confirmation with total price
        function showConfirmPopup(){
            Swal.fire({
                title: 'Confirm Booking',
                html: `Total days: <b>${total_days}</b><br>Total price: <b>Rs. ${total_price}</b>`,
                showCancelButton: true,
                confirmButtonText: 'Confirm Booking',
                cancelButtonText: 'Cancel'
            }).then((confirmResult) => {
                if(confirmResult.isConfirmed){
                    document.getElementById('bookingForm').submit();
                }
            });
        }

        if(data.exists){
            // Overlapping booking exists → show warning first
            Swal.fire({
                title: 'You already have a booking!',
                html: `Booking: <b>${data.vehicle_name}</b> from <b>${data.start_date}</b> to <b>${data.end_date}</b><br>Do you want to continue?`,
                showCancelButton: true,
                confirmButtonText: 'Yes, continue',
                cancelButtonText: 'No, cancel'
            }).then((result) => {
                if(result.isConfirmed){
                    // Add hidden force_booking input
                    if(!document.getElementById('force_booking')){
                        let input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'force_booking';
                        input.id = 'force_booking';
                        input.value = '1';
                        document.getElementById('bookingForm').appendChild(input);
                    }
                    // Then show total price confirmation
                    showConfirmPopup();
                } else {
                    // Cancel → go back
                    window.location.href = 'vehicles.php';
                }
            });
        } else {
            // No existing booking → just show total price confirmation
            showConfirmPopup();
        }
    });
});

</script>

</body>
</html>
