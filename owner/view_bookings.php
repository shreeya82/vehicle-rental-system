<?php
session_start();
if (!isset($_SESSION['owner_id'])) {
    header("Location: login.php");
    exit();
}

include '../db.php';

$owner_id = $_SESSION['owner_id'];

/* ================= UPDATE BOOKING ================= */
if (isset($_POST['update_status_booking'])) {
    $bid = intval($_POST['booking_id']);
    $new_status = $_POST['status'];

    $booking = mysqli_fetch_assoc(
        mysqli_query($conn, "SELECT * FROM bookings WHERE booking_id='$bid'")
    );

    if ($booking) {
        if ($booking['owner_updated'] == 1 || $booking['returned_status'] == 'Returned') {
            $_SESSION['msg'] = "You cannot update this booking!";
        } else {
            mysqli_query(
                $conn,
                "UPDATE bookings 
                 SET status='$new_status', owner_updated=1 
                 WHERE booking_id='$bid'"
            );

            if ($new_status == 'Confirmed' && $booking['status'] != 'Confirmed') {
                $vehicle_id = $booking['vehicle_id'];
                mysqli_query(
                    $conn,
                    "UPDATE vehicles 
                     SET booked_units = booked_units + 1 
                     WHERE vehicle_id='$vehicle_id'"
                );
            }

            $_SESSION['msg'] = "Booking status updated!";
        }
    }

    header("Location: view_bookings.php");
    exit();
}

/* ================= DELETE BOOKING ================= */
if (isset($_GET['delete'], $_GET['bid'])) {
    $bid = intval($_GET['bid']);
    mysqli_query($conn, "DELETE FROM bookings WHERE booking_id='$bid'");
    $_SESSION['msg'] = "Booking deleted!";
    header("Location: view_bookings.php");
    exit();
}

/* ================= FETCH BOOKINGS ================= */
$res = mysqli_query($conn, "
    SELECT b.*, v.vehicle_name, u.full_name AS user_name,
           CASE 
               WHEN b.returned_status='Returned' THEN 'Returned'
               ELSE 'Active'
           END AS rental_status
    FROM bookings b
    JOIN vehicles v ON b.vehicle_id = v.vehicle_id
    JOIN users u ON b.user_id = u.user_id
    WHERE v.owner_id='$owner_id'
    ORDER BY b.start_date DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Bookings | Owner</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../assets/css/admin.css?v=1">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
table.table thead th {
    background: #000;
    color: #fff;
    text-transform: uppercase;
    letter-spacing: .5px;
    font-weight: 600;
    border: none;
}
</style>
</head>

<body>
<div class="d-flex" id="wrapper">

<?php include 'owner_sidebar.php'; ?>

<div id="page-content-wrapper" class="flex-grow-1">
<nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">
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
<h2 class="mb-4">Bookings for My Vehicles</h2>

<div class="card shadow-sm">
<div class="card-body">

<table class="table table-bordered table-striped align-middle">
<thead>
<tr>
<th>#</th>
<th>Vehicle</th>
<th>User</th>
<th>Start Date</th>
<th>End Date</th>
<th>Total Amount</th>
<th>Status</th>
<th>Rental Status</th>
<th>Actions</th>
</tr>
</thead>

<tbody>
<?php
$i = 1;
while ($row = mysqli_fetch_assoc($res)) {
?>
<tr>
<td><?= $i ?></td>
<td><?= $row['vehicle_name'] ?></td>
<td><?= $row['user_name'] ?></td>
<td><?= $row['start_date'] ?></td>
<td><?= $row['end_date'] ?></td>
<td><?= $row['total_amount'] ?></td>

<td>
<span class="badge <?= $row['status']=='Confirmed'?'bg-success':($row['status']=='Pending'?'bg-warning':'bg-danger') ?>">
<?= ucfirst($row['status']) ?>
</span>
</td>

<td>
<span class="badge <?= $row['rental_status']=='Active'?'bg-success':'bg-secondary' ?>">
<?= $row['rental_status'] ?>
</span>
</td>

<td>
<?php if ($row['owner_updated']==1 || $row['returned_status']=='Returned'): ?>
<span class="text-muted">Locked</span>
<?php else: ?>
<button class="btn btn-sm btn-primary me-1 update-btn"
        data-id="<?= $row['booking_id'] ?>"
        data-status="<?= $row['status'] ?>">
Update
</button>
<?php endif; ?>

<button class="btn btn-sm btn-danger delete-btn"
        data-id="<?= $row['booking_id'] ?>">
Delete
</button>
</td>
</tr>
<?php
$i++;
}
?>
</tbody>
</table>

</div>
</div>
</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Sidebar toggle
document.getElementById('menu-toggle')
?.addEventListener('click', () => {
    document.getElementById('wrapper').classList.toggle('toggled');
});

// Logout SweetAlert (safe)
const logoutLink = document.getElementById('ownerLogoutLink');
if (logoutLink) {
    logoutLink.addEventListener('click', function(e){
        e.preventDefault();
        Swal.fire({
            title: 'Confirm Logout?',
            text: 'Are you sure you want to log out?',
            showCancelButton: true,
            confirmButtonText: 'Yes, logout',
            cancelButtonText: 'Cancel'
        }).then((result)=>{
            if(result.isConfirmed){
                window.location.href='ownerlogout.php';
            }
        });
    });
}

// Delete booking
document.querySelectorAll('.delete-btn').forEach(btn=>{
    btn.addEventListener('click', function(){
        const bid = this.dataset.id;
        Swal.fire({
            title: 'Confirm Delete?',
            text: 'This booking will be permanently deleted!',
            showCancelButton: true,
            confirmButtonText: 'Delete'
        }).then((result)=>{
            if(result.isConfirmed){
                window.location.href = 'view_bookings.php?delete=1&bid='+bid;
            }
        });
    });
});

// Update booking
document.querySelectorAll('.update-btn').forEach(btn=>{
    btn.addEventListener('click', function(){
        const bid = this.dataset.id;
        const currentStatus = this.dataset.status;

        Swal.fire({
            title: 'Update Booking Status',
            input: 'select',
            inputOptions: {
                Pending: 'Pending',
                Confirmed: 'Confirmed',
                Cancelled: 'Cancelled'
            },
            inputValue: currentStatus,
            showCancelButton: true
        }).then((result)=>{
            if(result.isConfirmed){
                const form = document.createElement('form');
                form.method = 'POST';
                form.style.display = 'none';
                form.innerHTML = `
                    <input type="hidden" name="booking_id" value="${bid}">
                    <input type="hidden" name="status" value="${result.value}">
                    <input type="hidden" name="update_status_booking" value="1">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        });
    });
});

// Success message
<?php if(isset($_SESSION['msg'])): ?>
Swal.fire({
    icon: 'success',
    title: 'Success',
    text: '<?= $_SESSION['msg'] ?>',
    timer: 1500,
    showConfirmButton: false
});
<?php unset($_SESSION['msg']); endif; ?>
</script>

</body>
</html>
