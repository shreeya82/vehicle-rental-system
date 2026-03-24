<?php
$activePage = basename($_SERVER['PHP_SELF']); // detect current page
?>

<div id="sidebar-wrapper" class="d-flex flex-column">
    <div class="sidebar-heading p-3 border-bottom">Ridezy Admin</div>

    <div class="list-group list-group-flush flex-grow-1">
        <a href="dashboard.php" class="list-group-item sidebar-link <?= $activePage=='dashboard.php'?'active':'' ?>">
            <i class="fas fa-tachometer-alt me-2"></i> Dashboard
        </a>
        <a href="manage_users.php" class="list-group-item sidebar-link <?= $activePage=='manage_users.php'?'active':'' ?>">
            <i class="fas fa-users me-2"></i> Manage Users
        </a>
        <a href="manage_vehicles.php" class="list-group-item sidebar-link <?= $activePage=='manage_vehicles.php'?'active':'' ?>">
            <i class="fas fa-car me-2"></i> Manage Vehicles
        </a>
        <a href="manage_bookings.php" class="list-group-item sidebar-link <?= $activePage=='manage_bookings.php'?'active':'' ?>">
            <i class="fas fa-file-alt me-2"></i> Booking History
        </a>
    
    </div>

    <div class="px-3 mb-3 mt-auto">
        <!-- Logout button with SweetAlert -->
        <a href="#" id="logoutBtn" class="btn btn-outline-danger w-100">
            <i class="fas fa-sign-out-alt me-1"></i> Logout
        </a>
    </div>
</div>

<style>
#sidebar-wrapper {
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    width: 250px;
    background: #030948;
    display: flex;
    flex-direction: column;
    z-index: 1000;
}

#sidebar-wrapper .list-group {
    overflow: visible;
}

#sidebar-wrapper .sidebar-link.active {
    background-color: #0d6efd;
    color: #fff;
}

#page-content-wrapper {
    margin-left: 250px;
    transition: margin-left 0.3s ease;
}

#wrapper.toggled #sidebar-wrapper {
    margin-left: -250px;
}
</style>

<!-- SweetAlert for Logout -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const logoutBtn = document.getElementById('logoutBtn');
    if(logoutBtn){
        logoutBtn.addEventListener('click', function(e){
            e.preventDefault();

            Swal.fire({
                title: 'Logout?',
                text: 'Are you sure you want to log out?',
                showCancelButton: true,
                confirmButtonText: 'Yes, Logout',
                cancelButtonText: 'Cancel',
                width: '350px',      // smaller width
                padding: '1.2rem',   // tighter padding
                allowOutsideClick: false
            }).then((result) => {
                if(result.isConfirmed){
                    window.location.href = 'admin_logout.php';
                }
            });
        });
    }
});
</script>

