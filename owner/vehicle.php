<?php
session_start();
if(!isset($_SESSION['owner_id'])){
    header("Location: login.php");
    exit();
}

include '../db.php';

$owner_id = $_SESSION['owner_id'];

if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);

    mysqli_query($conn, "
        DELETE FROM vehicles
        WHERE vehicle_id = '$delete_id'
        AND owner_id = '$owner_id'
    ");

    header("Location: vehicle.php");
    exit();
}

$edit_mode = false;
$show_form = false;

$vehicle_name_val = '';
$vehicle_type_val = '';
$price_val = '';
$description_val = '';
$plate_val = '';
$quantity_val = 1;
$error = '';

if(isset($_GET['edit_id'])){
    $edit_id = intval($_GET['edit_id']);
    $res = mysqli_query($conn, "SELECT * FROM vehicles WHERE vehicle_id='$edit_id' AND owner_id='$owner_id'");
    if($res && mysqli_num_rows($res) > 0){
        $edit_mode = true;
        $show_form = true;
        $edit_vehicle = mysqli_fetch_assoc($res);
    }
}

if(isset($_POST['add_vehicle'])){
    $vehicle_name_val = trim($_POST['vehicle_name']);
    $vehicle_type_val = trim($_POST['vehicle_type']);
    $price_val = trim($_POST['price_per_day']);
    $description_val = trim($_POST['description']);
    $plate_val = trim($_POST['plate_number']);
    $quantity_val = intval($_POST['quantity']);

    if(empty($vehicle_name_val) || empty($vehicle_type_val) || empty($price_val) || empty($plate_val) || $quantity_val < 1){
        $error = "Please fill all required fields and quantity must be at least 1.";
        $show_form = true;
    } else {
        $plate_check_sql = "SELECT * FROM vehicles WHERE plate_number='$plate_val' AND owner_id='$owner_id'";
        if($edit_mode) $plate_check_sql .= " AND vehicle_id!={$edit_vehicle['vehicle_id']}";
        $plate_check = mysqli_query($conn, $plate_check_sql);
        if(mysqli_num_rows($plate_check) > 0){
            $error = "You have already registered a vehicle with this plate number!";
            $show_form = true;
        } else {
            $upload_dir = '../uploads/';
            if(!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            if(isset($_FILES['image']) && $_FILES['image']['error']==0){
                $filename = time().'_'.basename($_FILES['image']['name']);
                move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir.$filename);
            } else {
                $filename = $edit_mode ? $edit_vehicle['image'] : null;
            }

            if($edit_mode){
                $vid = $edit_vehicle['vehicle_id'];
                mysqli_query($conn, "UPDATE vehicles SET 
                    vehicle_name='$vehicle_name_val', 
                    vehicle_type='$vehicle_type_val', 
                    price_per_day='$price_val',
                    description='$description_val', 
                    plate_number='$plate_val', 
                    quantity='$quantity_val', 
                    image='$filename',
                    status='Pending',
                    is_approved=0
                    WHERE vehicle_id='$vid' AND owner_id='$owner_id'");
            } else {
                mysqli_query($conn, "INSERT INTO vehicles 
                    (vehicle_name, vehicle_type, price_per_day, description, plate_number, quantity, image, owner_id, is_approved, status, posted_date)
                    VALUES ('$vehicle_name_val','$vehicle_type_val','$price_val','$description_val','$plate_val','$quantity_val','$filename','$owner_id',0,'Pending',NOW())");
            }

            header("Location: vehicle.php");
            exit();
        }
    }
}

$vehicles = mysqli_query($conn, "
    SELECT v.*,
           IFNULL(SUM(CASE WHEN b.status='Confirmed' AND b.returned_status='Not Returned' THEN 1 ELSE 0 END),0) AS booked_units,
           (v.quantity - IFNULL(SUM(CASE WHEN b.status='Confirmed' AND b.returned_status='Not Returned' THEN 1 ELSE 0 END),0)) AS remaining
    FROM vehicles v
    LEFT JOIN bookings b ON v.vehicle_id = b.vehicle_id
    WHERE v.owner_id='$owner_id'
    GROUP BY v.vehicle_id
    ORDER BY v.vehicle_id DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Owner Vehicles</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../assets/css/admin.css?v=1">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
.field-error { font-size: 0.85em; color: #dc3545; margin-top: 0.25rem; }
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
            <div class="ms-auto fw-bold">Welcome, <?php echo htmlspecialchars($_SESSION['owner_name']); ?></div>
        </div>
    </nav>

    <div class="container-fluid p-4">
        <h2 class="mb-4">My Vehicles</h2>

        <button class="btn btn-primary mb-3" id="showAddBtn" <?php if($edit_mode || $show_form) echo 'style="display:none;"'; ?>>
            <i class="fas fa-plus me-1"></i> Add Vehicle
        </button>

        <?php if($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="card mb-4 shadow-sm" id="formCard" style="display: <?php echo ($edit_mode || $show_form) ? 'block' : 'none'; ?>;">
            <div class="card-body">
                <h4 class="mb-3"><?php echo $edit_mode ? "Edit Vehicle" : "Add New Vehicle"; ?></h4>
                <form method="POST" enctype="multipart/form-data" id="vehicleForm">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <input type="text" name="vehicle_name" class="form-control" placeholder="Vehicle Name" 
                                   value="<?php echo htmlspecialchars($edit_mode ? $edit_vehicle['vehicle_name'] : $vehicle_name_val); ?>" required autocomplete="off">
                        </div>
                        <div class="col-md-3">
                            <select name="vehicle_type" class="form-select" required>
                                <option value="">Type</option>
                                <option value="Car" <?php if(($edit_mode && $edit_vehicle['vehicle_type']=='Car') || (!$edit_mode && $vehicle_type_val=='Car')) echo 'selected'; ?>>Car</option>
                                <option value="Bike" <?php if(($edit_mode && $edit_vehicle['vehicle_type']=='Bike') || (!$edit_mode && $vehicle_type_val=='Bike')) echo 'selected'; ?>>Bike</option>
                                <option value="Other" <?php if(($edit_mode && $edit_vehicle['vehicle_type']=='Other') || (!$edit_mode && $vehicle_type_val=='Other')) echo 'selected'; ?>>Other</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="number" step="0.01" name="price_per_day" class="form-control" placeholder="Price per day"
                                   value="<?php echo htmlspecialchars($edit_mode ? $edit_vehicle['price_per_day'] : $price_val); ?>" required>
                        </div>
                        <div class="col-md-3">
                            <input type="text" name="plate_number" class="form-control" placeholder="Plate Number"
                                   value="<?php echo htmlspecialchars($edit_mode ? $edit_vehicle['plate_number'] : $plate_val); ?>" required>
                        </div>
                        <div class="col-md-3">
                            <input type="number" name="quantity" class="form-control" placeholder="Quantity"
                                   value="<?php echo htmlspecialchars($edit_mode ? $edit_vehicle['quantity'] : $quantity_val); ?>" min="1" required>
                        </div>
                        <div class="col-md-9">
                            <textarea name="description" class="form-control" placeholder="Description"><?php echo $edit_mode ? $edit_vehicle['description'] : htmlspecialchars($description_val); ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <input type="file" name="image" class="form-control">
                            <?php if($edit_mode && $edit_vehicle['image']): ?>
                                <small>Current: <img src="../uploads/<?php echo $edit_vehicle['image']; ?>" style="width:60px;height:40px;object-fit:cover;"></small>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6 d-flex gap-2 mt-2">
                            <button type="submit" name="add_vehicle" class="btn btn-primary flex-fill">
                                <?php echo $edit_mode ? 'Update Vehicle' : 'Add Vehicle'; ?>
                            </button>
                            <button type="button" id="cancelBtn" class="btn btn-secondary flex-fill">Cancel</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <h5>All My Vehicles</h5>
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Price/Day</th>
                            <th>Plate</th>
                            <th>Quantity</th>
                            <th>Remaining</th>
                            <th>Posted</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $i=1;
                        while($v = mysqli_fetch_assoc($vehicles)){
                            $remaining = $v['remaining'];
                            echo "<tr>
                                <td>{$i}</td>
                                <td>";
                                if($v['image']) echo "<img src='../uploads/{$v['image']}' style='width:80px; height:50px; object-fit:cover'>";
                                else echo "No Image";
                            echo "</td>
                                <td>{$v['vehicle_name']}</td>
                                <td>{$v['vehicle_type']}</td>
                                <td>{$v['price_per_day']}</td>
                                <td>{$v['plate_number']}</td>
                                <td>{$v['quantity']}</td>
                                <td>{$remaining}</td>
                                <td>".date('d M Y', strtotime($v['posted_date']))."</td>
                                <td>";
                                if($v['is_approved'] == 1){
                                    echo 'Approved';
                                } else {
                                    echo htmlspecialchars($v['status']);
                                }
                            echo "</td>
                                <td>";
                                echo "<a href='vehicle.php?edit_id={$v['vehicle_id']}' class='btn btn-sm btn-success mb-1'>Edit</a>
                                      <button class='btn btn-sm btn-danger mb-1' onclick='confirmDelete({$v['vehicle_id']})'>Delete</button>";
                            echo "</td>
                            </tr>";
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
<script src="../assets/js/owner.js?v=1"></script>

<script>
const toggleButton = document.getElementById('menu-toggle');
const wrapper = document.getElementById('wrapper');
toggleButton.addEventListener('click', ()=>{wrapper.classList.toggle('toggled');});

const formCard = document.getElementById('formCard');
const showBtn = document.getElementById('showAddBtn');
const cancelBtn = document.getElementById('cancelBtn');

if(showBtn){
    showBtn.addEventListener('click', ()=>{ formCard.style.display = 'block'; showBtn.style.display = 'none'; });
}
if(cancelBtn){
    cancelBtn.addEventListener('click', ()=>{
        formCard.style.display = 'none';
        if(showBtn) showBtn.style.display = 'block';
        if(<?php echo $edit_mode ? 'true' : 'false'; ?> === false){
            formCard.querySelectorAll('input, textarea, select').forEach(el => el.value = '');
        }
    });
}

document.getElementById('ownerLogoutLink').addEventListener('click', function(e){
    e.preventDefault();
    Swal.fire({
        title: 'Confirm Logout?',
        text: "Are you sure you want to log out?",
        showCancelButton: true,
        confirmButtonText: 'Yes, logout',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    }).then((result)=>{
        if(result.isConfirmed){
            window.location.href = 'ownerlogout.php';
        }
    });
});

function confirmDelete(vehicleId){
    Swal.fire({
        title: 'Are you sure?',
        text: "This vehicle will be deleted permanently!",
        showCancelButton: true,
        confirmButtonText: 'Yes, Comfirm',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    }).then((result)=>{
        if(result.isConfirmed){
            window.location.href = `vehicle.php?delete_id=${vehicleId}`;
        }
    });
}
</script>

</body>
</html>
