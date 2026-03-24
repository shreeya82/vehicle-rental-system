<?php
session_start();
include '../db.php';

// ==============================
// AUTO-RETURN VEHICLES
// ==============================
// Marks bookings as returned if end date/time has passed and payment is done
// Also decreases booked_units in vehicles automatically
$conn->query("
    UPDATE bookings b
    JOIN vehicles v ON b.vehicle_id = v.vehicle_id
    SET b.returned_status = 'Returned',
        b.returned_date = NOW(),
        v.booked_units = GREATEST(v.booked_units - 1, 0)
    WHERE b.payment_status = 'Paid'
      AND b.returned_status = 'Not Returned'
      AND CONCAT(b.end_date, ' ', b.return_time) <= NOW()
");

// ==============================
// REDIRECT IF NOT LOGGED IN
// ==============================
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* =========================
   NOTIFICATIONS
========================= */

// 1️⃣ Approved bookings (status = Confirmed but notify_approved = 0)
$approved_notify_q = mysqli_query($conn, "
SELECT b.booking_id, v.vehicle_name
FROM bookings b
JOIN vehicles v ON b.vehicle_id = v.vehicle_id
WHERE b.user_id='$user_id'
AND b.status='Confirmed'
AND notify_approved = 0
");
$approved_count = mysqli_num_rows($approved_notify_q);

// 2️⃣ Rentals ending today (Confirmed, Not Returned)
$today = date('Y-m-d');
$ending_today_q = mysqli_query($conn, "
SELECT b.booking_id, v.vehicle_name, b.payment_status
FROM bookings b
JOIN vehicles v ON b.vehicle_id = v.vehicle_id
WHERE b.user_id='$user_id'
AND b.status='Confirmed'
AND b.returned_status='Not Returned'
AND b.end_date = '$today'
");
$ending_count = 0;
$ending_today_rows = [];
while($row = mysqli_fetch_assoc($ending_today_q)){
    if($row['payment_status'] == 'Pending') $ending_count++;
    $ending_today_rows[] = $row; // store to display alerts later
}

// Total notifications
$notify_count = $approved_count + $ending_count;

/* =========================
   BOOKINGS QUERIES
========================= */
// Pending Approval
$pending = mysqli_query($conn, "
SELECT b.*, v.vehicle_name, v.price_per_day,
(DATEDIFF(b.end_date,b.start_date)+1)*v.price_per_day AS total_price
FROM bookings b
JOIN vehicles v ON b.vehicle_id=v.vehicle_id
WHERE b.user_id='$user_id'
AND b.status='Pending'
ORDER BY b.booking_id DESC
");

// Active Rentals (Confirmed, Not Returned)
$active = mysqli_query($conn, "
SELECT b.*, v.vehicle_name, v.price_per_day,
(DATEDIFF(b.end_date,b.start_date)+1)*v.price_per_day AS total_price
FROM bookings b
JOIN vehicles v ON b.vehicle_id=v.vehicle_id
WHERE b.user_id='$user_id'
AND b.status='Confirmed'
AND b.returned_status='Not Returned'
ORDER BY b.booking_id DESC
");

// Returned Rentals
$returned = mysqli_query($conn, "
SELECT b.*, v.vehicle_name, v.price_per_day,
(DATEDIFF(b.end_date,b.start_date)+1)*v.price_per_day AS total_price
FROM bookings b
JOIN vehicles v ON b.vehicle_id=v.vehicle_id
WHERE b.user_id='$user_id'
AND b.returned_status='Returned'
ORDER BY b.booking_id DESC
");

include 'navbar.php'; // navbar now has notification badge
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Bookings - Ridezy</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/user.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body style="padding-top:80px;">

<div class="container">
<h2 class="mb-4">My Bookings</h2>

<!-- ================= ALERTS ================= -->
<?php
// Approved bookings notifications (user can see once, clears automatically)
while($row = mysqli_fetch_assoc($approved_notify_q)){
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">';
    echo '✅ Your booking for <strong>'.htmlspecialchars($row['vehicle_name']).'</strong> has been approved!';
    echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    mysqli_query($conn, "UPDATE bookings SET notify_approved = 1 WHERE booking_id = ".$row['booking_id']);
}

// Rentals ending today (only pending payments show warning)
foreach($ending_today_rows as $row){
    if($row['payment_status']=='Pending'){
        echo '<div class="alert alert-warning alert-dismissible fade show" role="alert">';
        echo '⚠️ Your rental for <strong>'.htmlspecialchars($row['vehicle_name']).'</strong> ends today. Please complete payment or return it on time.';
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    }
}
?>

<!-- ================= TABS ================= -->
<ul class="nav nav-tabs mb-3" role="tablist">
  <li class="nav-item">
    <button class="nav-link active" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" role="tab">Pending Approval</button>
  </li>
  <li class="nav-item">
    <button class="nav-link" id="active-tab" data-bs-toggle="tab" data-bs-target="#active" role="tab">Active Rentals</button>
  </li>
  <li class="nav-item">
    <button class="nav-link" id="returned-tab" data-bs-toggle="tab" data-bs-target="#returned" role="tab">Returned</button>
  </li>
</ul>

<div class="tab-content">

<!-- ================= PENDING ================= -->
<div class="tab-pane fade show active" id="pending" role="tabpanel">
<?php if(mysqli_num_rows($pending)>0): ?>
<div class="table-responsive">
<table class="table table-bordered">
<thead class="table-dark">
<tr>
<th>#</th><th>Vehicle</th><th>Start</th><th>End</th><th>Total</th><th>Status</th>
</tr>
</thead>
<tbody>
<?php $i=1; while($row=mysqli_fetch_assoc($pending)): ?>
<tr>
<td><?= $i++ ?></td>
<td><?= htmlspecialchars($row['vehicle_name']) ?></td>
<td><?= $row['start_date'] ?></td>
<td><?= $row['end_date'] ?></td>
<td><?= number_format($row['total_price'],2) ?></td>
<td><span class="badge bg-warning">Pending Approval</span></td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
</div>
<?php else: ?>
<div class="alert alert-info">No pending approvals.</div>
<?php endif; ?>
</div>

<!-- ================= ACTIVE ================= -->
<div class="tab-pane fade" id="active" role="tabpanel">
<?php if(mysqli_num_rows($active)>0): ?>
<div class="table-responsive">
<table class="table table-bordered">
<thead class="table-dark">
<tr>
<th>#</th><th>Vehicle</th><th>Start</th><th>End</th><th>Total</th><th>Action</th>
</tr>
</thead>
<tbody>
<?php $i=1; while($row=mysqli_fetch_assoc($active)): ?>
<tr>
<td><?= $i++ ?></td>
<td><?= htmlspecialchars($row['vehicle_name']) ?></td>
<td><?= $row['start_date'] ?></td>
<td><?= $row['end_date'] ?></td>
<td><?= number_format($row['total_price'],2) ?></td>
<td>
<?php if($row['payment_status']=='Pending'): ?>
<a href="payment.php?booking_id=<?= $row['booking_id'] ?>" class="btn btn-success btn-sm">Pay Now</a>
<?php else: ?>
<span class="badge bg-success">Paid</span>
<?php endif; ?>
</td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
</div>
<?php else: ?>
<div class="alert alert-info">No active rentals.</div>
<?php endif; ?>
</div>

<!-- ================= RETURNED ================= -->
<div class="tab-pane fade" id="returned" role="tabpanel">
<?php if(mysqli_num_rows($returned)>0): ?>
<div class="table-responsive">
<table class="table table-bordered">
<thead class="table-dark">
<tr>
<th>#</th><th>Vehicle</th><th>Total</th><th>Payment</th><th>Returned</th>
</tr>
</thead>
<tbody>
<?php $i=1; while($row=mysqli_fetch_assoc($returned)): ?>
<tr>
<td><?= $i++ ?></td>
<td><?= htmlspecialchars($row['vehicle_name']) ?></td>
<td><?= number_format($row['total_price'],2) ?></td>
<td><?= $row['payment_date'] ?></td>
<td><?= $row['returned_date'] ?></td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
</div>
<?php else: ?>
<div class="alert alert-info">No returned rentals.</div>
<?php endif; ?>
</div>

</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// SweetAlert payment popup
document.querySelectorAll('.pay-btn').forEach(btn=>{
  btn.onclick=()=>{
    Swal.fire({
      title:'Confirm Payment',
      html:`Pay <strong>NPR ${btn.dataset.amount}</strong>?`,
      icon:'question',
      showCancelButton:true,
      confirmButtonText:'Pay Now'
    }).then(res=>{
      if(res.isConfirmed){
        fetch('pay_ajax.php',{
          method:'POST',
          headers:{'Content-Type':'application/x-www-form-urlencoded'},
          body:'booking_id='+btn.dataset.id
        }).then(()=>location.reload());
      }
    });
  }
});
</script>

</body>
</html>
