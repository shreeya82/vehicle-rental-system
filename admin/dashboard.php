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
    <title>Ridezy Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css?v=1">
    <style>
        /* Page wrapper for toggle */
        #page-content-wrapper {
            margin-left: 250px; /* match sidebar width */
            transition: margin-left 0.3s ease;
            padding: 20px;
        }

        #wrapper.toggled #page-content-wrapper {
            margin-left: 0;
        }
    </style>
</head>
<body>
<div class="d-flex" id="wrapper">

    <?php include('admin_sidebar.php'); ?>

    <div id="page-content-wrapper" class="flex-grow-1">

        <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">
            <div class="container-fluid">
                <!-- Hamburger toggle -->
                <button class="btn btn-outline-primary" id="menu-toggle">
                    <i class="fas fa-bars"></i>
                </button>

                <div class="ms-auto fw-bold">Welcome, <?= htmlspecialchars($_SESSION['admin_username']); ?></div>
            </div>
        </nav>

        <div class="container-fluid p-4">
            <h1 class="mb-4">Welcome, <?= htmlspecialchars($_SESSION['admin_username']); ?>!</h1>
            <p class="mb-4">Here you can manage users, vehicles, and bookings efficiently.</p>

            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card shadow-sm border-0 h-100 text-white bg-primary">
                        <div class="card-body d-flex align-items-center">
                            <i class="fas fa-users fa-3x me-3"></i>
                            <div>
                                <h5 class="card-title">Users</h5>
                                <p class="card-text fs-3">
                                    <?php
                                    $query = mysqli_query($conn, "SELECT COUNT(*) as total FROM users");
                                    $data = mysqli_fetch_assoc($query);
                                    echo $data['total'];
                                    ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card shadow-sm border-0 h-100 text-white bg-success">
                        <div class="card-body d-flex align-items-center">
                            <i class="fas fa-car fa-3x me-3"></i>
                            <div>
                                <h5 class="card-title">Vehicles</h5>
                                <p class="card-text fs-3">
                                    <?php
                                    $query = mysqli_query($conn, "SELECT COUNT(*) as total FROM vehicles");
                                    $data = mysqli_fetch_assoc($query);
                                    echo $data['total'];
                                    ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card shadow-sm border-0 h-100 text-white bg-warning">
                        <div class="card-body d-flex align-items-center">
                            <i class="fas fa-file-alt fa-3x me-3"></i>
                            <div>
                                <h5 class="card-title">Bookings</h5>
                                <p class="card-text fs-3">
                                    <?php
                                    $query = mysqli_query($conn, "SELECT COUNT(*) as total FROM bookings");
                                    $data = mysqli_fetch_assoc($query);
                                    echo $data['total'];
                                    ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4 mb-4">
                <a href="manage_vehicles.php" class="btn btn-primary me-2"><i class="fas fa-car me-1"></i> View Vehicles</a>
                <a href="manage_users.php" class="btn btn-success me-2"><i class="fas fa-user-plus me-1"></i> Add User</a>
                <a href="manage_bookings.php" class="btn btn-warning"><i class="fas fa-list me-1"></i> View Bookings</a>
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

    // Logout SweetAlert
    const logoutLink = document.getElementById('adminLogoutLink');
    if(logoutLink){
        logoutLink.addEventListener('click', function(e) {
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
