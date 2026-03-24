<?php
session_start();
if (!isset($_SESSION['owner_id'])) {
    header("Location: /FinalProject/owner/login.php");
    exit();
}

include('../db.php');
$owner_id = $_SESSION['owner_id'];

/* ================= STATS ================= */

$totalVehicles = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) as total FROM vehicles WHERE owner_id='$owner_id'"
))['total'];

$totalBookings = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) as total 
     FROM bookings b 
     JOIN vehicles v ON b.vehicle_id=v.vehicle_id 
     WHERE v.owner_id='$owner_id'"
))['total'];

$pendingBookings = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) as total 
     FROM bookings b 
     JOIN vehicles v ON b.vehicle_id=v.vehicle_id 
     WHERE v.owner_id='$owner_id' AND b.status='Pending'"
))['total'];

$activeRentals = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) as total 
     FROM bookings b 
     JOIN vehicles v ON b.vehicle_id=v.vehicle_id 
     WHERE v.owner_id='$owner_id' 
     AND b.status='Confirmed' 
     AND b.returned_status='Not Returned'"
))['total'];

$totalEarnings = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT SUM(amount) as total 
     FROM transactions 
     WHERE owner_id='$owner_id' 
     AND status='Completed'"
))['total'] ?? 0;

$overdue = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT COUNT(*) as total
FROM bookings b
JOIN vehicles v ON b.vehicle_id=v.vehicle_id
WHERE v.owner_id='$owner_id'
AND b.end_date < CURDATE()
AND b.returned_status='Not Returned'
"))['total'];
?>

<!DOCTYPE html>
<html>
<head>
<title>Owner Dashboard</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<!-- KEEP YOUR SIDEBAR CSS -->
<link rel="stylesheet" href="../assets/css/admin.css?v=6">

<style>
body { background:#f4f6f9; }

.stat-card {
    border-radius:15px;
    color:white;
    padding:20px;
    transition:.3s;
}
.stat-card:hover { transform:translateY(-5px); }

.gradient1 { background:linear-gradient(45deg,#4e73df,#224abe); }
.gradient2 { background:linear-gradient(45deg,#1cc88a,#13855c); }
.gradient3 { background:linear-gradient(45deg,#f6c23e,#dda20a); }
.gradient4 { background:linear-gradient(45deg,#36b9cc,#258391); }
.gradient5 { background:linear-gradient(45deg,#e74a3b,#be2617); }

.activity-item {
    padding:12px;
    border-bottom:1px solid #eee;
}

.progress { height:10px; }

.section-card { border-radius:15px; }
</style>
</head>

<body>

<div class="d-flex" id="wrapper">

<?php include 'owner_sidebar.php'; ?>

<div id="page-content-wrapper" class="flex-grow-1">

<nav class="navbar navbar-light bg-light border-bottom">
    <div class="container-fluid">
        <button class="btn btn-outline-primary" id="menu-toggle">
            <i class="fas fa-bars"></i>
        </button>
        <div class="ms-auto fw-bold">
            Welcome, <?= htmlspecialchars($_SESSION['owner_name']); ?>
        </div>
    </div>
</nav>

<div class="container-fluid p-4">

<h2 class="fw-bold mb-4">Owner Dashboard</h2>

<?php if($overdue > 0): ?>
<div class="alert alert-danger shadow-sm">
⚠️ You have <?= $overdue ?> overdue rental(s).
</div>
<?php endif; ?>

<!-- STATS -->
<div class="row g-4 mb-4">

<div class="col-md-4">
<div class="stat-card gradient1 shadow">
<h6>Total Vehicles</h6>
<h3><?= $totalVehicles ?></h3>
<i class="fas fa-car fa-2x opacity-50"></i>
</div>
</div>

<div class="col-md-4">
<div class="stat-card gradient2 shadow">
<h6>Total Bookings</h6>
<h3><?= $totalBookings ?></h3>
<i class="fas fa-file-alt fa-2x opacity-50"></i>
</div>
</div>

<div class="col-md-4">
<div class="stat-card gradient3 shadow text-dark">
<h6>Pending Bookings</h6>
<h3><?= $pendingBookings ?></h3>
<i class="fas fa-clock fa-2x opacity-50"></i>
</div>
</div>

<div class="col-md-4">
<div class="stat-card gradient4 shadow">
<h6>Active Rentals</h6>
<h3><?= $activeRentals ?></h3>
<i class="fas fa-key fa-2x opacity-50"></i>
</div>
</div>

<div class="col-md-4">
<div class="stat-card gradient5 shadow">
<h6>Total Earnings</h6>
<h3>Rs. <?= number_format($totalEarnings,2) ?></h3>
<i class="fas fa-money-bill-wave fa-2x opacity-50"></i>
</div>
</div>

</div>

<div class="row">

<div class="col-md-6">
<div class="card section-card shadow mb-4">
<div class="card-header bg-dark text-white">
Recent Bookings
</div>
<div class="card-body p-0">

<?php
$recent = mysqli_query($conn,"
SELECT b.booking_id,u.full_name,v.vehicle_name,b.status
FROM bookings b
JOIN vehicles v ON b.vehicle_id=v.vehicle_id
JOIN users u ON b.user_id=u.user_id
WHERE v.owner_id='$owner_id'
ORDER BY b.booking_id DESC
LIMIT 5
");

while($row = mysqli_fetch_assoc($recent)){
$statusColor = $row['status']=='Confirmed'?'success':
($row['status']=='Pending'?'warning':'danger');
?>

<div class="activity-item">
<strong>#<?= $row['booking_id'] ?></strong>
— <?= $row['full_name'] ?> booked 
<b><?= $row['vehicle_name'] ?></b>
<span class="badge bg-<?= $statusColor ?> float-end">
<?= $row['status'] ?>
</span>
</div>

<?php } ?>

</div>
</div>
</div>

<div class="col-md-6">
<div class="card section-card shadow mb-4">
<div class="card-header bg-secondary text-white">
Top Vehicles Performance
</div>
<div class="card-body">

<?php
$performance = mysqli_query($conn,"
SELECT v.vehicle_name, COUNT(b.booking_id) as total
FROM vehicles v
LEFT JOIN bookings b ON v.vehicle_id=b.vehicle_id
WHERE v.owner_id='$owner_id'
GROUP BY v.vehicle_id
ORDER BY total DESC
LIMIT 5
");

$maxBookings = 1;
$temp = mysqli_query($conn,"
SELECT MAX(total) as maxTotal FROM (
SELECT COUNT(b.booking_id) as total
FROM vehicles v
LEFT JOIN bookings b ON v.vehicle_id=b.vehicle_id
WHERE v.owner_id='$owner_id'
GROUP BY v.vehicle_id
) as t
");
$maxBookings = mysqli_fetch_assoc($temp)['maxTotal'] ?? 1;

while($row = mysqli_fetch_assoc($performance)){
$percent = ($row['total'] / $maxBookings) * 100;
?>

<div class="mb-3">
<label><?= $row['vehicle_name'] ?> (<?= $row['total'] ?>)</label>
<div class="progress">
<div class="progress-bar bg-primary" style="width: <?= $percent ?>%"></div>
</div>
</div>

<?php } ?>

</div>
</div>
</div>

</div>

</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.getElementById('menu-toggle').addEventListener('click', function(){
    document.getElementById('wrapper').classList.toggle('toggled');
});
</script>

</body>
</html>
