<?php
session_start();
include '../db.php';

/* SIMPLE OWNER CHECK */
if (!isset($_SESSION['owner_id'])) {
    header("Location: login.php");
    exit();
}

$owner_id = $_SESSION['owner_id'];
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Owner Transactions - Ridezy</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../assets/css/admin.css?v=1">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
table.table thead th {
    background: linear-gradient(135deg, #000000ff, #000000ff);
    color: #fff;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 600;
    border: none;
}
</style>
</head>
<body>
<div class="d-flex" id="wrapper">

    <?php include 'owner_sidebar.php'; ?>

    <div id="page-content-wrapper" class="flex-grow-1">
        <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">
            <div class="container-fluid">
                <button class="btn btn-outline-primary" id="menu-toggle"><i class="fas fa-bars"></i></button>
                <div class="ms-auto fw-bold">Welcome, <?= htmlspecialchars($_SESSION['owner_name']); ?></div>
            </div>
        </nav>

        <div class="container-fluid p-4">
            <h2 class="mb-4">My Transactions</h2>

            <?php
            // Fetch transactions for this owner
            $res = mysqli_query($conn, "
                SELECT t.*, u.full_name AS user_name, v.vehicle_name
                FROM transactions t
                JOIN users u ON t.user_id = u.user_id
                JOIN vehicles v ON t.vehicle_id = v.vehicle_id
                WHERE t.owner_id='$owner_id'
                ORDER BY t.transaction_date DESC
            ");
            ?>

            <div class="card shadow-sm">
                <div class="card-body">
                    <?php if(mysqli_num_rows($res) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Booking ID</th>
                                    <th>User</th>
                                    <th>Vehicle</th>
                                    <th>Amount</th>
                                    <th>Payment Method</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php $i=1; while($row = mysqli_fetch_assoc($res)): ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td><?= $row['booking_id'] ?></td>
                                    <td><?= htmlspecialchars($row['user_name']) ?></td>
                                    <td><?= htmlspecialchars($row['vehicle_name']) ?></td>
                                    <td><?= number_format($row['amount'],2) ?></td>
                                    <td><?= htmlspecialchars($row['payment_method']) ?></td>
                                    <td>
                                        <?php
                                        if($row['status']=='Completed') echo '<span class="badge bg-success">Completed</span>';
                                        elseif($row['status']=='Pending') echo '<span class="badge bg-warning">Pending</span>';
                                        else echo '<span class="badge bg-danger">'.htmlspecialchars($row['status']).'</span>';
                                        ?>
                                    </td>
                                    <td><?= $row['transaction_date'] ?></td>
                                </tr>
                            <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                        <div class="alert alert-info">No transactions found.</div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Toggle sidebar
const toggleButton = document.getElementById('menu-toggle');
const wrapper = document.getElementById('wrapper');
toggleButton.addEventListener('click', ()=>{ wrapper.classList.toggle('toggled'); });
</script>
</body>
</html>
