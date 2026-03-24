<?php
session_start();
if(!isset($_SESSION['admin_id'])){
    header("Location: adminlogin.php");
    exit();
}

include('../db.php');

// Handle approve/reject actions
if(isset($_GET['action'], $_GET['vid'])){
    $vid = intval($_GET['vid']);
    if($_GET['action']=='approve'){
        mysqli_query($conn, "UPDATE vehicles SET is_approved=1, status='Available' WHERE vehicle_id='$vid'");
        $_SESSION['msg'] = "Vehicle approved successfully!";
    } elseif($_GET['action']=='reject'){
        mysqli_query($conn, "UPDATE vehicles SET is_approved=0, status='Rejected' WHERE vehicle_id='$vid'");
        $_SESSION['msg'] = "Vehicle rejected successfully!";
    }
    header("Location: manage_vehicles.php");
    exit();
}

// Fetch pending vehicles
$pending_vehicles = mysqli_query($conn, "
    SELECT v.vehicle_id, v.vehicle_name, v.vehicle_type, v.price_per_day, v.plate_number, 
           v.quantity, v.description, v.posted_date, v.image, u.full_name AS owner_name
    FROM vehicles v
    JOIN users u ON v.owner_id = u.user_id
    WHERE v.is_approved=0 AND v.status!='Rejected'
    ORDER BY v.posted_date DESC
");

// Fetch approved vehicles
$approved_vehicles = mysqli_query($conn, "
    SELECT v.vehicle_id, v.vehicle_name, v.vehicle_type, v.price_per_day, v.plate_number, 
           v.quantity, v.description, v.posted_date, v.image, u.full_name AS owner_name
    FROM vehicles v
    JOIN users u ON v.owner_id = u.user_id
    WHERE v.is_approved=1
    ORDER BY v.posted_date DESC
");

$msg = isset($_SESSION['msg']) ? $_SESSION['msg'] : "";
unset($_SESSION['msg']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Vehicles - Ridezy Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="../assets/css/admin.css?v=1">
<style>
#page-content-wrapper {
    margin-left: 250px;
    transition: margin-left 0.3s ease;
    padding: 20px;
}
#wrapper.toggled #page-content-wrapper { margin-left: 0; }
</style>
</head>
<body>

<div class="d-flex" id="wrapper">
    <?php include('admin_sidebar.php'); ?>

    <div id="page-content-wrapper" class="flex-grow-1">
        <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">
            <div class="container-fluid">
                <button class="btn btn-outline-primary" id="menu-toggle"><i class="fas fa-bars"></i></button>
            </div>
        </nav>

        <div class="container-fluid p-4">
            <h1 class="mb-4">Manage Vehicles</h1>

            <h3 class="mt-3">Pending Vehicles</h3>
            <div class="table-responsive mb-4">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Owner</th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Price/Day</th>
                            <th>Plate</th>
                            <th>Quantity</th>
                            <th>Description</th>
                            <th>Posted</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($pending_vehicles) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($pending_vehicles)): ?>
                                <tr>
                                    <td><?= $row['vehicle_id'] ?></td>
                                    <td><?= $row['owner_name'] ?></td>
                                    <td><?= $row['vehicle_name'] ?></td>
                                    <td><?= $row['vehicle_type'] ?></td>
                                    <td><?= $row['price_per_day'] ?></td>
                                    <td><?= $row['plate_number'] ?></td>
                                    <td><?= $row['quantity'] ?></td>
                                    <td><?= $row['description'] ?></td>
                                    <td><?= date('d M Y', strtotime($row['posted_date'])) ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-info mb-1 view-btn" data-bs-toggle="modal" data-bs-target="#vehicleModal" data-vehicle='<?= htmlspecialchars(json_encode($row), ENT_QUOTES) ?>'>View</button>
                                        <a href="manage_vehicles.php?action=approve&vid=<?= $row['vehicle_id'] ?>" class="btn btn-sm btn-success mb-1">Approve</a>
                                        <button class="btn btn-sm btn-danger mb-1" onclick="confirmAction('reject', <?= $row['vehicle_id'] ?>)">Reject</button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="10" class="text-center">No pending vehicles</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <h3 class="mt-4">Approved Vehicles</h3>
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-success">
                        <tr>
                            <th>ID</th>
                            <th>Owner</th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Price/Day</th>
                            <th>Plate</th>
                            <th>Quantity</th>
                            <th>Description</th>
                            <th>Posted</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($approved_vehicles) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($approved_vehicles)): ?>
                                <tr>
                                    <td><?= $row['vehicle_id'] ?></td>
                                    <td><?= $row['owner_name'] ?></td>
                                    <td><?= $row['vehicle_name'] ?></td>
                                    <td><?= $row['vehicle_type'] ?></td>
                                    <td><?= $row['price_per_day'] ?></td>
                                    <td><?= $row['plate_number'] ?></td>
                                    <td><?= $row['quantity'] ?></td>
                                    <td><?= $row['description'] ?></td>
                                    <td><?= date('d M Y', strtotime($row['posted_date'])) ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-info mb-1 view-btn" data-bs-toggle="modal" data-bs-target="#vehicleModal" data-vehicle='<?= htmlspecialchars(json_encode($row), ENT_QUOTES) ?>'>View</button>
                                        <button class="btn btn-sm btn-warning mb-1" onclick="confirmAction('reject', <?= $row['vehicle_id'] ?>)">Reject</button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="10" class="text-center">No approved vehicles</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Vehicle Modal -->
<div class="modal fade" id="vehicleModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Vehicle Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="card shadow-sm">
          <div class="row g-0">
            <div class="col-md-4">
              <img src="" id="vehicleImage" class="img-fluid rounded-start" style="object-fit:cover; height:250px;">
            </div>
            <div class="col-md-8">
              <div class="card-body">
                <h5 class="card-title" id="vehicleName"></h5>
                <p class="card-text"><strong>Type:</strong> <span id="vehicleType"></span></p>
                <p class="card-text"><strong>Price/Day:</strong> Rs. <span id="vehiclePrice"></span></p>
                <p class="card-text"><strong>Plate Number:</strong> <span id="vehiclePlate"></span></p>
                <p class="card-text"><strong>Quantity:</strong> <span id="vehicleQuantity"></span></p>
                <p class="card-text"><strong>Description:</strong> <span id="vehicleDescription"></span></p>
                <p class="card-text"><strong>Owner:</strong> <span id="vehicleOwner"></span></p>
                <p class="card-text"><small class="text-muted">Posted on: <span id="vehiclePosted"></span></small></p>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const toggleButton = document.getElementById('menu-toggle');
const wrapper = document.getElementById('wrapper');
toggleButton.addEventListener('click', () => { wrapper.classList.toggle('toggled'); });

function confirmAction(action, vid){
    let actionText = action === 'reject' ? 'reject this vehicle?' : '';
    Swal.fire({
        title: 'Are you sure?',
        text: `You are about to ${actionText}`,
        showCancelButton: true,
        confirmButtonText: 'Yes, proceed',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    }).then((result) => {
        if(result.isConfirmed){
            window.location.href = `manage_vehicles.php?action=${action}&vid=${vid}`;
        }
    });
}

// Populate vehicle modal
document.querySelectorAll('.view-btn').forEach(btn => {
    btn.addEventListener('click', function(){
        const vehicle = JSON.parse(this.dataset.vehicle);
        document.getElementById('vehicleImage').src = vehicle.image ? '../uploads/' + vehicle.image : '../assets/images/placeholder.jpg';
        document.getElementById('vehicleName').textContent = vehicle.vehicle_name;
        document.getElementById('vehicleType').textContent = vehicle.vehicle_type;
        document.getElementById('vehiclePrice').textContent = vehicle.price_per_day;
        document.getElementById('vehiclePlate').textContent = vehicle.plate_number;
        document.getElementById('vehicleQuantity').textContent = vehicle.quantity;
        document.getElementById('vehicleDescription').textContent = vehicle.description;
        document.getElementById('vehicleOwner').textContent = vehicle.owner_name;
        document.getElementById('vehiclePosted').textContent = new Date(vehicle.posted_date).toLocaleDateString();
    });
});

<?php if($msg!=""): ?>
Swal.fire({
    icon: '<?php echo strpos($msg,"successfully")!==false?"success":"info"; ?>',
    title: '<?php echo strpos($msg,"successfully")!==false?"Success":"Info"; ?>',
    text: '<?php echo $msg; ?>',
    timer: 1500,
    showConfirmButton: false
});
<?php endif; ?>
</script>
</body>
</html>
