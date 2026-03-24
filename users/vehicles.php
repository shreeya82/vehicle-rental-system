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
    <title>Available Vehicles | Ridezy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/user.css?v=1.3">
</head>
<body>

<div class="container py-5 mt-5">
    <h2 class="text-center mb-4">Our Vehicles</h2>

<form method="GET" class="mb-4 d-flex justify-content-end">
    <div class="position-relative" style="max-width: 350px;">
        <input type="text" name="search" class="form-control rounded-pill ps-5" 
               placeholder="Search vehicles"
               value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">

        <i class="fas fa-search position-absolute" style="left: 15px; top: 50%; transform: translateY(-50%); color:#888;"></i>

        <?php if(!empty($_GET['search'])): ?>
            <a href="vehicles.php" class="btn btn-outline-secondary position-absolute" 
               style="right:0; top:0; height:100%; border-top-right-radius:50px; border-bottom-right-radius:50px;">
               &times;
            </a>
        <?php else: ?>
            <button class="btn btn-primary position-absolute" type="submit" 
               style="right:0; top:0; height:100%; border-top-right-radius:50px; border-bottom-right-radius:50px;">
               Search
            </button>
        <?php endif; ?>
    </div>
</form>

<div class="row">

<?php
$search = trim($_GET['search'] ?? '');
$searchSQL = "";

if(!empty($search)){
    $searchEscaped = mysqli_real_escape_string($conn, $search);
    
    // Split search into words
    $words = explode(' ', $searchEscaped);
    $conditions = [];
    foreach($words as $word){
        $word = trim($word);
        if($word === '') continue;
        $conditions[] = "(v.vehicle_name LIKE '%$word%' 
                         OR v.vehicle_type LIKE '%$word%' 
                         OR v.description LIKE '%$word%')";
    }
    if(count($conditions) > 0){
        // Use OR between words for broad search
        $searchSQL = " AND (" . implode(" OR ", $conditions) . ")";
    }
}

$query = mysqli_query($conn, "
    SELECT v.*,
           (v.quantity - IFNULL(SUM(CASE WHEN b.status='Confirmed' AND b.returned_status='Not Returned' THEN 1 ELSE 0 END),0)) AS remaining_units
    FROM vehicles v
    LEFT JOIN bookings b ON v.vehicle_id = b.vehicle_id
    WHERE v.is_approved = 1 $searchSQL
    GROUP BY v.vehicle_id
    ORDER BY v.vehicle_id DESC
");



if(mysqli_num_rows($query) == 0){
    echo "<div class='col-12 text-center text-muted'><h5>No vehicles found matching your search.</h5></div>";
}

while($row = mysqli_fetch_assoc($query)) {
    $imagePath = !empty($row['image']) ? "../uploads/".$row['image'] : "../assets/images/placeholder.jpg"; 

    if($row['remaining_units'] > 0){
        $availabilityText = "Available";
        $availabilityClass = "text-success";
        $disabled = "";
    } else {
        $availabilityText = "Not Available";
        $availabilityClass = "text-danger";
        $disabled = "disabled";
    }
    ?>

    <div class='col-md-4 mb-4'>
        <div class='card h-100 shadow'>
            <img src='<?php echo $imagePath; ?>' class='card-img-top' alt='<?php echo htmlspecialchars($row['vehicle_name']); ?>' style='height:200px; object-fit:cover;'>
            <div class='card-body'>
                <h5 class='card-title'><?php echo htmlspecialchars($row['vehicle_name']); ?></h5>
                <p class='card-text'>
                    Price: <strong>Rs. <?php echo $row['price_per_day']; ?> / day</strong><br>
                    Status: <span class='<?php echo $availabilityClass; ?>'><?php echo $availabilityText; ?></span><br>
                    <small>Remaining: <?php echo $row['remaining_units']; ?></small>
                </p>

                <div class="d-grid gap-2">
                    <!-- View Button -->
                    <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#vehicleModal"
                            data-vehicle='<?php echo json_encode($row); ?>'>View</button>

                    <!-- Book Now -->
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <a href='book_vehicle.php?id=<?php echo $row['vehicle_id']; ?>' class='btn btn-primary <?php echo $disabled; ?>'>Book Now</a>
                    <?php else: ?>
                        <a href="#" class='btn btn-primary <?php echo $disabled; ?>' data-bs-toggle="modal" data-bs-target="#loginModal">Book Now</a>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>

<?php
}
?>

</div>
</div>

<!-- Vehicle Modal & Login Modal -->
<div class="modal fade" id="vehicleModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Vehicle Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
            <div class="col-md-5">
                <img src="" id="vehicleImage" class="img-fluid rounded" style="height:250px; object-fit:cover;">
            </div>
            <div class="col-md-7">
                <h4 id="vehicleName"></h4>
                <p><strong>Type:</strong> <span id="vehicleType"></span></p>
                <p><strong>Price/Day:</strong> Rs. <span id="vehiclePrice"></span></p>

                <p><strong>Description:</strong> <span id="vehicleDescription"></span></p>
            </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

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
<script>
document.querySelectorAll('.btn-info').forEach(btn => {
    btn.addEventListener('click', function(){
        const vehicle = JSON.parse(this.dataset.vehicle);
        document.getElementById('vehicleImage').src = vehicle.image ? '../uploads/' + vehicle.image : '../assets/images/placeholder.jpg';
        document.getElementById('vehicleName').textContent = vehicle.vehicle_name;
        document.getElementById('vehicleType').textContent = vehicle.vehicle_type;
        document.getElementById('vehiclePrice').textContent = vehicle.price_per_day;

        document.getElementById('vehicleDescription').textContent = vehicle.description;
    });
});
</script>
<script src="../assets/js/user.js"></script>
</body>
</html>
