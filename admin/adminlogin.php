<?php
session_start();
include '../db.php'; 

if(isset($_POST['username'], $_POST['password'])){
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $query = mysqli_query($conn, "SELECT * FROM admins WHERE username='$username'");
    if(mysqli_num_rows($query) == 0){
        $_SESSION['login_error'] = "Admin not found!";
        header("Location: adminlogin.php");
        exit();
    }

    $admin = mysqli_fetch_assoc($query);

    if(!password_verify($password, $admin['password'])){
        $_SESSION['login_error'] = "Wrong password!";
        header("Location: adminlogin.php");
        exit();
    }

    $_SESSION['admin_id'] = $admin['admin_id'];
    $_SESSION['admin_username'] = $admin['username'];
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Login</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
    body {
        min-height: 100vh;
        background-color: #030948; /* dark background */
    }

    .login-card {
        background-color: #0f172a;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.5);
    }

    .login-card h3 {
        color: #ffffff;
        font-weight: 600;
    }

    .login-card label {
        color: #ffffff;
    }

    .login-card input {
        background-color: #020617;
        color: #ffffff;
        border: 1px solid #334155;
    }

    .login-card input::placeholder {
        color: #cbd5f5;
    }

    .login-card input:focus {
        background-color: #020617;
        color: #ffffff;
        border-color: #60a5fa;
        box-shadow: none;
    }

    .btn-admin {
        background-color: #4471dbff;
        color: #ffffff;
        border: none;
    }

    .btn-admin:hover {
        background-color: #1d4ed8;
    }
</style>
</head>

<body class="d-flex justify-content-center align-items-center">

<div class="col-md-4">
    <div class="login-card">

        <h3 class="mb-4 text-center">Admin Login</h3>

        <?php if(isset($_SESSION['login_error'])): ?>
            <div class="alert alert-danger text-center">
                <?php echo $_SESSION['login_error']; unset($_SESSION['login_error']); ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-admin w-100 py-2">
                Login
            </button>
        </form>

    </div>
</div>

</body>
</html>
