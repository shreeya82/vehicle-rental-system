<?php
session_start();
if(!isset($_SESSION['admin_id'])){
    header("Location: adminlogin.php");
    exit();
}
include('../db.php'); 

if(isset($_GET['delete_id'])){
    $delete_id = intval($_GET['delete_id']);
    $delete_query = mysqli_query($conn, "DELETE FROM users WHERE user_id = $delete_id");
    $_SESSION['msg'] = $delete_query ? "User deleted successfully!" : "Error deleting user: " . mysqli_error($conn);
    header("Location: manage_users.php");
    exit();
}

if(isset($_GET['approve_id'])){
    $approve_id = intval($_GET['approve_id']);
    $approve_query = mysqli_query($conn, "UPDATE users SET is_approved = 1 WHERE user_id = $approve_id");
    $_SESSION['msg'] = $approve_query ? "User approved successfully!" : "Error approving user: " . mysqli_error($conn);
    header("Location: manage_users.php");
    exit();
}

if(isset($_POST['edit_user'])){
    $user_id = intval($_POST['user_id']);
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    if(!empty($password)){
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $update_query = mysqli_query($conn, "UPDATE users SET full_name='$full_name', phone='$phone', email='$email', password='$password_hash' WHERE user_id=$user_id");
    } else {
        $update_query = mysqli_query($conn, "UPDATE users SET full_name='$full_name', phone='$phone', email='$email' WHERE user_id=$user_id");
    }

    $_SESSION['msg'] = $update_query ? "User updated successfully!" : "Error updating user: " . mysqli_error($conn);
    header("Location: manage_users.php");
    exit();
}

if(isset($_POST['add_user'])){
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Admin-added users are auto-approved
    $insert_query = mysqli_query($conn, "INSERT INTO users (full_name, phone, email, password, is_approved) 
    VALUES ('$full_name', '$phone', '$email', '$password', 1)");
    $_SESSION['msg'] = $insert_query ? "User added successfully!" : "Error adding user: " . mysqli_error($conn);
    header("Location: manage_users.php");
    exit();
}

$msg = isset($_SESSION['msg']) ? $_SESSION['msg'] : "";
unset($_SESSION['msg']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Users - Ridezy Admin</title>
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
                <button class="btn btn-outline-primary" id="menu-toggle">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </nav>

        <div class="container-fluid p-4">
            <h1 class="mb-4">Manage Users</h1>

            <?php if($msg!=""): ?>
                <div class="alert alert-info"><?php echo $msg; ?></div>
            <?php endif; ?>

            <div class="mb-3">
                <button class="btn btn-success" id="addUserBtn">
                    <i class="fas fa-user-plus me-1"></i> Add User
                </button>
            </div>

            <h4>Pending Users</h4>
            <div class="table-responsive mb-4">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>User ID</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $pending_query = mysqli_query($conn, "SELECT * FROM users WHERE is_approved=0 ORDER BY user_id DESC");
                        while($user = mysqli_fetch_assoc($pending_query)){
                            echo "<tr>
                                <td>{$user['user_id']}</td>
                                <td>{$user['full_name']}</td>
                                <td>{$user['email']}</td>
                                <td>Pending</td>
                                <td>
                                    <a href='manage_users.php?approve_id={$user['user_id']}' class='btn btn-sm btn-success me-1'>Approve</a>
                                    <button class='btn btn-sm btn-primary me-1 view-btn' data-id='{$user['user_id']}' data-name='{$user['full_name']}' data-phone='{$user['phone']}' data-email='{$user['email']}'>View</button>
                                    <button class='btn btn-sm btn-danger delete-btn' data-id='{$user['user_id']}'>Delete</button>
                                </td>
                            </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <h4>All Users</h4>
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>User ID</th>
                            <th>Full Name</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $all_users = mysqli_query($conn, "SELECT * FROM users ORDER BY user_id DESC");
                        while($row = mysqli_fetch_assoc($all_users)) {
                            $status = $row['is_approved'] ? 'Approved' : 'Pending';
                            echo "<tr>
                                <td>{$row['user_id']}</td>
                                <td>{$row['full_name']}</td>
                                <td>{$row['phone']}</td>
                                <td>{$row['email']}</td>
                                <td>{$status}</td>
                                <td>
                                    <button class='btn btn-sm btn-primary me-1 view-btn' 
                                        data-id='{$row['user_id']}' 
                                        data-name='{$row['full_name']}' 
                                        data-phone='{$row['phone']}' 
                                        data-email='{$row['email']}'>
                                        <i class='fas fa-eye'></i> View
                                    </button>
                                    <button class='btn btn-sm btn-warning me-1 edit-btn' 
                                        data-id='{$row['user_id']}' 
                                        data-name='{$row['full_name']}' 
                                        data-phone='{$row['phone']}' 
                                        data-email='{$row['email']}'>
                                        <i class='fas fa-edit'></i> Edit
                                    </button>
                                    <button class='btn btn-sm btn-danger delete-btn' data-id='{$row['user_id']}'>
                                        <i class='fas fa-trash-alt'></i> Delete
                                    </button>
                                </td>
                            </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" id="addUserForm" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add New User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
            <label>Full Name</label>
            <input type="text" class="form-control" name="full_name" required>
        </div>
        <div class="mb-3">
            <label>Phone</label>
            <input type="text" class="form-control" name="phone" required>
        </div>
        <div class="mb-3">
            <label>Email</label>
            <input type="email" class="form-control" name="email" required>
        </div>
        <div class="mb-3">
            <label>Password</label>
            <input type="password" class="form-control" name="password" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" name="add_user" class="btn btn-success">Add User</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" id="editUserForm" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="user_id" id="edit-user-id">
        <div class="mb-3">
            <label>Full Name</label>
            <input type="text" class="form-control" id="edit-full-name" name="full_name" required>
        </div>
        <div class="mb-3">
            <label>Phone</label>
            <input type="text" class="form-control" id="edit-phone" name="phone" required>
        </div>
        <div class="mb-3">
            <label>Email</label>
            <input type="email" class="form-control" id="edit-email" name="email" required>
        </div>
        <div class="mb-3">
            <label>Password (leave blank to keep current)</label>
            <input type="password" class="form-control" id="edit-password" name="password">
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" name="edit_user" class="btn btn-primary">Save Changes</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>

<!-- View User Modal -->
<div class="modal fade" id="viewUserModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">View User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p><strong>Full Name:</strong> <span id="view-full-name"></span></p>
        <p><strong>Phone:</strong> <span id="view-phone"></span></p>
        <p><strong>Email:</strong> <span id="view-email"></span></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/admin.js"></script>
<script>
const toggleButton = document.getElementById('menu-toggle');
const wrapper = document.getElementById('wrapper');
toggleButton.addEventListener('click', () => wrapper.classList.toggle('toggled'));

// Delete user
document.querySelectorAll('.delete-btn').forEach(button => {
    button.addEventListener('click', function() {
        const userId = this.dataset.id;
        Swal.fire({
            title: 'Confirm Delete?',
            text: "User will be removed permanently!",
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Delete',
            reverseButtons: true
        }).then((result) => {
            if(result.isConfirmed){
                window.location.href = `manage_users.php?delete_id=${userId}`;
            }
        });
    });
});

// Auto-hide in-page alert
const alertDiv = document.querySelector('.alert');
if(alertDiv){
    setTimeout(()=> {
        alertDiv.style.transition = 'opacity 0.5s';
        alertDiv.style.opacity = '0';
        setTimeout(()=> alertDiv.remove(), 500);
    }, 3000);
}

// SweetAlert popup
<?php if($msg != ""): ?>
Swal.fire({
    icon: '<?php echo strpos($msg,"successfully")!==false ? "success":"error"; ?>',
    title: '<?php echo strpos($msg,"successfully")!==false ? "Success":"Error"; ?>',
    text: '<?php echo $msg; ?>',
    timer: 1200,
    showConfirmButton: false
});
<?php endif; ?>

// Edit modal
const editModal = new bootstrap.Modal(document.getElementById('editUserModal'));
document.querySelectorAll('.edit-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('edit-user-id').value = this.dataset.id;
        document.getElementById('edit-full-name').value = this.dataset.name;
        document.getElementById('edit-phone').value = this.dataset.phone;
        document.getElementById('edit-email').value = this.dataset.email;
        editModal.show();
    });
});

// View modal
const viewModal = new bootstrap.Modal(document.getElementById('viewUserModal'));
document.querySelectorAll('.view-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('view-full-name').textContent = this.dataset.name;
        document.getElementById('view-phone').textContent = this.dataset.phone;
        document.getElementById('view-email').textContent = this.dataset.email;
        viewModal.show();
    });
});

// Add user modal
const addUserModal = new bootstrap.Modal(document.getElementById('addUserModal'));
document.getElementById('addUserBtn').addEventListener('click', () => addUserModal.show());

// Logout
document.getElementById('adminLogoutLink').addEventListener('click', function(e) {
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
</script>
</body>
</html>
