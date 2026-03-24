<?php
$activePage = basename($_SERVER['PHP_SELF']); // detect current page
?>

<div id="sidebar-wrapper" class="d-flex flex-column">
    <div class="sidebar-heading p-3 border-bottom">Ridezy Owner</div>

    <div class="list-group list-group-flush flex-grow-1">
        <a href="dashboard.php" class="list-group-item sidebar-link <?= $activePage=='dashboard.php'?'active':'' ?>">
            <i class="fas fa-tachometer-alt me-2"></i> Dashboard
        </a>
        <a href="vehicle.php" class="list-group-item sidebar-link <?= $activePage=='vehicle.php'?'active':'' ?>">
            <i class="fas fa-car me-2"></i> My Vehicles
        </a>
        <a href="view_bookings.php" class="list-group-item sidebar-link <?= $activePage=='view_bookings.php'?'active':'' ?>">
            <i class="fas fa-file-alt me-2"></i> Manage Bookings
        </a>
        <a href="owner_transactions.php" class="list-group-item sidebar-link <?= $activePage=='owner_transactions.php'?'active':'' ?>">
            <i class="fas fa-credit-card me-2"></i> Check Transactions
        </a>
    </div>

    <div class="px-3 mb-3 mt-auto">
        <a href="ownerlogout.php" id="ownerLogoutBtn" class="btn btn-outline-danger w-100">
            <i class="fas fa-sign-out-alt me-1"></i> Logout
        </a>
    </div>
</div>

<style>
/* Sidebar styling */
#sidebar-wrapper {
    position: fixed;           
    top: 0;
    left: 0;
    width: 250px;              
    height: 100vh;             
    background-color: #030948; 
    color: #fff;               
    z-index: 1000;
    display: flex;
    flex-direction: column;
    box-shadow: 2px 0 5px rgba(0,0,0,0.1);
}

#sidebar-wrapper .sidebar-heading {
    color: #fff;
    font-weight: 600;
    background-color: #030948; 
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

#sidebar-wrapper .sidebar-link {
    color: #adb5bd;            
    border: none;
}
#sidebar-wrapper .sidebar-link.active,
#sidebar-wrapper .sidebar-link:hover {
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
#wrapper.toggled #page-content-wrapper {
    margin-left: 0;
}
</style>

<!-- SweetAlert Logout Confirmation -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const logoutBtn = document.getElementById('ownerLogoutBtn');
    if(logoutBtn){
        logoutBtn.addEventListener('click', function(e){
            e.preventDefault(); // prevent immediate redirect

            Swal.fire({
                title: 'Logout?',
                text: 'Are you sure you want to log out?',
                showCancelButton: true,
                confirmButtonText: 'Yes, Logout',
                cancelButtonText: 'Cancel',
                width: '350px',
                padding: '1.2rem',
                allowOutsideClick: false
            }).then((result) => {
                if(result.isConfirmed){
                    window.location.href = logoutBtn.href; // redirect if confirmed
                }
            });
        });
    }
});
</script>
