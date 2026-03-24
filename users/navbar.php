<?php 
if (session_status() == PHP_SESSION_NONE) { session_start(); } 
include '../db.php';

// Calculate notification count for logged-in users
if(isset($_SESSION['user_id'])){
    $user_id = $_SESSION['user_id'];

    // Approved bookings not yet seen (Pending payment)
    $approved_q = mysqli_query($conn, "
        SELECT COUNT(*) AS count
        FROM bookings
        WHERE user_id='$user_id'
        AND status='Confirmed'
        AND payment_status='Pending'
        AND notify_approved = 0
    ");
    $approved_count = mysqli_fetch_assoc($approved_q)['count'];

    // Rentals ending today
    $today = date('Y-m-d');
    $ending_q = mysqli_query($conn, "
        SELECT COUNT(*) AS count
        FROM bookings
        WHERE user_id='$user_id'
        AND status='Confirmed'
        AND returned_status='Not Returned'
        AND end_date='$today'
    ");
    $ending_count = mysqli_fetch_assoc($ending_q)['count'];

    $notify_count = $approved_count + $ending_count;
} else {
    $notify_count = 0;
}
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top"> 
  <div class="container"> 
    <a class="navbar-brand" href="/FinalProject/users/index.php">RIDEZY</a> 
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"> 
      <span class="navbar-toggler-icon"></span> 
    </button> 
    <div class="collapse navbar-collapse" id="navbarNav"> 
      <ul class="navbar-nav ms-auto"> 
        <li class="nav-item"><a class="nav-link" href="/FinalProject/users/index.php">Home</a></li> 
        <li class="nav-item"><a class="nav-link" href="/FinalProject/users/about.php">About Us</a></li> 
        <li class="nav-item"><a class="nav-link" href="/FinalProject/users/vehicles.php">Vehicles</a></li> 

        <?php if(isset($_SESSION['user_id'])): ?> 
          <li class="nav-item">
            <a class="nav-link" href="/FinalProject/users/mybookings.php">
              My Bookings
              <?php if($notify_count > 0): ?>
                <span class="badge bg-danger"><?= $notify_count ?></span>
              <?php endif; ?>
            </a>
          </li>
          <li class="nav-item"> 
            <a class="nav-link" href="#" id="logoutLink">Logout</a> 
          </li> 
          <li class="nav-item"> 
            <span class="nav-link disabled"> 
              Hello, <?= isset($_SESSION['full_name']) ? htmlspecialchars($_SESSION['full_name']) : 'User'; ?> 
            </span>
          </li> 
        <?php else: ?> 
          <li class="nav-item"><a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#loginModal">Login</a></li> 
          <li class="nav-item"><a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#registerModal">Register</a></li> 
        <?php endif; ?> 

      </ul> 
    </div> 
  </div> 
</nav> 

<!-- Login Modal -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-hidden="true"> 
  <div class="modal-dialog"> 
    <div class="modal-content"> 
      <form action="login.php" method="POST" autocomplete="off"> 
        <input type="hidden" name="redirect" value="<?= basename($_SERVER['PHP_SELF']); ?>"> 
        <div class="modal-header"> 
          <h5 class="modal-title">User Login</h5> 
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button> 
        </div> 
        <div class="modal-body"> 
          <?php if(isset($_SESSION['login_error'])): ?> 
            <div class="alert alert-danger"><?= $_SESSION['login_error']; ?></div> 
          <?php endif; ?> 
          <div class="mb-3"> 
            <label>Email</label> 
            <input type="email" name="email" class="form-control" required> 
          </div> 
          <div class="mb-3"> 
            <label>Password</label> 
            <input type="password" name="password" class="form-control" required> 
          </div> 
        </div> 
        <div class="modal-footer"> 
          <button type="submit" class="btn btn-primary w-100">Login</button> 
        </div> 
      </form> 
    </div> 
  </div> 
</div> 

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> 

<?php if(isset($_SESSION['user_id'])): ?>
<script> 
document.getElementById('logoutLink').addEventListener('click', function(e) { 
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
            window.location.href = 'logout.php'; 
        } 
    }); 
}); 
</script>
<?php endif; ?>
