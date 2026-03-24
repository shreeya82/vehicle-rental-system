<?php
session_start();
if(!isset($_SESSION['admin_id'])){
    header("Location: adminlogin.php");
    exit();
}
include('../db.php'); 
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title> Booking History - Ridezy Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css?v=1">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* Sidebar toggle effect for page content */
        #page-content-wrapper {
            margin-left: 250px; /* match sidebar width */
            transition: margin-left 0.3s ease;
            padding: 20px;
        }
        #wrapper.toggled #page-content-wrapper {
            margin-left: 0;
        }

        /* Optional: make table more readable */
        table.table th, table.table td {
            vertical-align: middle;
        }
    </style>
</head>
<body>

<div class="d-flex" id="wrapper">

    <?php include('admin_sidebar.php'); ?>

    <div id="page-content-wrapper" class="flex-grow-1">

        <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">
            <div class="container-fluid">
                <button class="btn btn-outline-primary" id="menu-toggle">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </nav>

        <div class="container-fluid p-4">
            <h1 class="mb-4">Booking History</h1>

            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Booking ID</th>
                            <th>User</th>
                            <th>Vehicle</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Return Time</th>
                            <th>Purpose</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = mysqli_query($conn, "
                            SELECT 
                                b.booking_id, 
                                u.full_name AS user_name, 
                                v.vehicle_name, 
                                b.start_date, 
                                b.end_date, 
                                b.return_time, 
                                b.purpose, 
                                b.total_amount, 
                                b.status
                            FROM bookings b
                            JOIN users u ON b.user_id = u.user_id
                            JOIN vehicles v ON b.vehicle_id = v.vehicle_id
                            ORDER BY b.booking_id DESC
                        ");
                        while($row = mysqli_fetch_assoc($query)) {
                            echo "<tr>
                                <td>{$row['booking_id']}</td>
                                <td>".htmlspecialchars($row['user_name'])."</td>
                                <td>".htmlspecialchars($row['vehicle_name'])."</td>
                                <td>{$row['start_date']}</td>
                                <td>{$row['end_date']}</td>
                                <td>{$row['return_time']}</td>
                                <td>".htmlspecialchars($row['purpose'])."</td>
                                <td>{$row['total_amount']}</td>
                                <td><span class='badge ".($row['status']=='Confirmed'?'bg-success':($row['status']=='Pending'?'bg-warning':'bg-danger'))."'>".ucfirst($row['status'])."</span></td>
                            </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Sidebar toggle
    const toggleButton = document.getElementById('menu-toggle');
    const wrapper = document.getElementById('wrapper');
    toggleButton.addEventListener('click', () => {
        wrapper.classList.toggle('toggled');
    });

    // Safe logout listener
    const logoutLink = document.getElementById('adminLogoutLink');
    if(logoutLink){
        logoutLink.addEventListener('click', function(e){
            e.preventDefault();
            Swal.fire({
                title: 'Confirm Logout?',
                text: "Are you sure you want to log out?",
                showCancelButton: true,
                confirmButtonText: 'Yes, logout',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if(result.isConfirmed){
                    window.location.href = 'admin_logout.php';
                }
            });
        });
    }
</script>

</body>
</html>
