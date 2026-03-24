<?php
session_start();
include '../db.php';
include 'navbar.php';

/* ===================== END DATE POPUP CHECK ===================== */
$endRental = null;

if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];

    $sql = "
        SELECT 
            b.booking_id,
            v.vehicle_name,
            b.end_date
        FROM bookings b
        JOIN vehicles v ON b.vehicle_id = v.vehicle_id
        WHERE b.user_id = $uid
          AND DATE(b.end_date) = CURDATE()
          AND b.returned_status != 'Returned'
        LIMIT 1
    ";

    $res = mysqli_query($conn, $sql);
    if ($res && mysqli_num_rows($res) > 0) {
        $endRental = mysqli_fetch_assoc($res);
    }
}



$register_errors = $_SESSION['register_errors'] ?? [];
$register_old = $_SESSION['register_old'] ?? [];
unset($_SESSION['register_errors'], $_SESSION['register_old']);

$login_error = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']);
?>
<?php include 'register_modal.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ridezy User Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/user.css?v=1.4">
    <style>
        .error-message { 
            color: red; 
            font-size: 0.9rem; 
            margin-top: 4px; 
        }
        .alert-dismissible .btn-close {
            position: absolute;
            top: 0.75rem;
            right: 0.5rem;
        }

        /* HERO - FULL SCREEN CENTERED, TEXT SLIGHTLY HIGHER */
        .hero {
            height: 88vh;
            background: linear-gradient(rgba(0,0,0,0), rgba(0,0,0,0)),
                        url('../assets/images/4.jpg') center/cover no-repeat;
            color: black;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            text-align: center;
            padding-top: 19vh; /* nudges the text downward from top */
        }
        .hero-text {
            transform: translateY(-10%);
        }
        .hero h1 {
            font-size: 3rem;
            font-weight: 700;
        }
        .hero p {
            font-size: 1.5rem;
            opacity: 0.6;
            margin-top: 2px;
        }

       
    </style>
</head>
<body style="padding-top: 80px;">

    <!-- ================= HERO ================= -->
    <section class="hero">
        <div class="container">
            <div class="hero-text">
                <h1>Welcome to Ridezy</h1>
                <p>Your Journey, Our Ride. Rent vehicles easily and quickly online.</p>
                <a href="/FinalProject/users/vehicles.php" class="btn btn-primary btn-lg mt-3 px-4 rounded-pill">
                    Browse Vehicles
                </a>
            </div>
        </div>
    </section>

 
    <!-- ================= LOGIN MODAL ================= -->
    <div class="modal fade" id="loginModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="login.php" method="POST" novalidate>
                    <div class="modal-header">
                        <h5 class="modal-title">User Login</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">

                        <?php if($login_error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert" id="loginErrorAlert">
                                <?php echo htmlspecialchars($login_error); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <div class="text-danger small login-error-email"></div>
                            <label>Email</label>
                            <input type="text" name="email" class="form-control">
                        </div>
                        <div class="mb-3">
                            <div class="text-danger small login-error-password"></div>
                            <label>Password</label>
                            <input type="password" name="password" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Login</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ================= FOOTER ================= -->
    <footer class="custom-footer py-3 fixed-bottom">
        <div class="container text-center">
            <p>Contact Us: ridezy@vehiclerental.com | +977-9812345678</p>
            <p>Address: Satdobato, Lalitpur</p>
        </div>
    </footer>

    <!-- SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php if (isset($_SESSION['login_success'])): ?>
    <script>
    Swal.fire({
        icon: 'success',
        title: 'Welcome',
        text: <?= json_encode($_SESSION['login_success']) ?>,
        timer: 2200,
        showConfirmButton: false
    }).then(() => {
        <?php if ($endRental): ?>
        Swal.fire({
            title: 'Rental Ends Today',
            html: `
                <p><strong><?= htmlspecialchars($endRental['vehicle_name']) ?></strong></p>
                <p>Your rental period ends today.</p>
                <p>Please complete payment.</p>
            `,
            showCloseButton: true,
            showConfirmButton: true,
            confirmButtonText: 'Pay Now 💳',
            allowOutsideClick: false
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "payment.php?booking_id=<?= $endRental['booking_id'] ?>";
            }
        });
        <?php endif; ?>
    });
    </script>
    <?php unset($_SESSION['login_success']); ?>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/user.js"></script>

    <script>
        <?php if(!empty($register_errors)): ?>
            var registerModal = new bootstrap.Modal(document.getElementById('registerModal'));
            registerModal.show();
        <?php endif; ?>

        var loginModal = document.getElementById('loginModal');
        loginModal.addEventListener('hidden.bs.modal', function () {
            var alertDiv = document.getElementById('loginErrorAlert');
            if(alertDiv) alertDiv.remove();
        });

        <?php if($login_error): ?>
            var bsLoginModal = new bootstrap.Modal(document.getElementById('loginModal'));
            bsLoginModal.show();
        <?php endif; ?>
    </script>

</body>
</html>
