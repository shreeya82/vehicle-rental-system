<?php
session_start();
include '../db.php';
include 'navbar.php';
?>
<?php include 'register_modal.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>About Us - Ridezy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/user.css?v=1.0">
</head>
<body>


<section class="about-section py-5" style="padding-top:100px;">
  <div class="container">
    <div class="row align-items-center">

      <div class="col-md-6">
        <img src="../assets/images/6.jpg" class="img-fluid rounded shadow" alt="About Ridezy">
      </div>
      <div class="col-md-6">
        <h2>About Ridezy</h2>
        <p class="lead">
          Ridezy is your trusted online vehicle rental platform. We provide cars, bikes, and trucks for short or long-term rentals at affordable prices.
        </p>
        <p>
          Our mission is to make your journey hassle-free, with easy booking, reliable vehicles, and excellent customer service.
        </p>
       
      </div>
    </div>
  </div>
</section>
<section class="py-5 bg-light">
  <div class="container">
    <h2 class="text-center mb-4">Why Choose Ridezy?</h2>
    <div class="row text-center">
      <div class="col-md-4">
        <h5>🚗 Vehicle Selection</h5>
        <p>Choose from cars, bikes suitable for every journey.</p>
      </div>
      <div class="col-md-4">
        <h5>💰 Affordable Pricing</h5>
        <p>Transparent pricing with no hidden costs.</p>
      </div>
      <div class="col-md-4">
        <h5>⚡ Easy Booking</h5>
        <p>Book your ride in just a few clicks with instant confirmation.</p>
      </div>
    </div>
  </div>
</section>
<section class="py-5">
  <div class="container">
    <div class="row">
      <div class="col-md-6">
        <h3>Our Mission</h3>
        <p>
          To provide a seamless and reliable vehicle rental experience using modern
          technology and excellent customer service.
        </p>
      </div>
      <div class="col-md-6">
        <h3>Our Vision</h3>
        <p>
          To become Nepal’s most trusted digital vehicle rental platform.
        </p>
      </div>
    </div>
  </div>
</section>

<footer class="custom-footer fixed-bottom">
  <div class="container text-center">
    <p>Contact Us: ridezy@vehiclerental.com | +977-9812345678</p>
    <p>Address: Satdobato, Lalitpur</p>
  </div>
</footer>

<div class="modal fade" id="loginModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="login.php" method="POST" autocomplete="off">
        <div class="modal-header">
          <h5 class="modal-title">User Login</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
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
          <button type="submit" class="btn btn-primary">Login</button>
        </div>
      </form>
    </div>
  </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="../assets/js/user.js"></script>


</body>
</html>
