<?php
session_start();
include '../db.php';

$error = "";

if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email='$email' AND role='owner'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) === 1) {
        $owner = mysqli_fetch_assoc($result);

        if ($owner['password'] === $password) { 
    $_SESSION['owner_id'] = $owner['user_id'];
    $_SESSION['owner_name'] = $owner['full_name'];

    header("Location: /FinalProject/owner/dashboard.php");
    exit();

        } else {
            $error = "Incorrect password";
        }
    } else {
        $error = "Invalid owner login";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Owner Login</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
    body {
        min-height: 100vh;
        background-color: #030948; 
        display: flex;
        justify-content: center;
        align-items: center;
        font-family: Arial, sans-serif;
    }

    .login-card {
        background-color: #0f172a;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        width: 100%;
        max-width: 400px;
    }

    .login-card h3 {
        color: #ffffff;
        font-weight: 600;
        text-align: center;
        margin-bottom: 25px;
    }

    .login-card label {
        color: #ffffff;
    }

    .login-card input {
        background-color: #020617;
        color: #ffffff;
        border: 1px solid #334155;
        width: 100%;
        padding: 10px;
        margin-bottom: 15px;
        border-radius: 5px;
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

    .btn-owner {
        background-color: #4471dbff;
        color: #ffffff;
        border: none;
        width: 100%;
        padding: 10px;
        border-radius: 5px;
        font-weight: 500;
    }

    .btn-owner:hover {
        background-color: #1d4ed8;
    }

    .error {
        color: #ff6b6b;
        text-align: center;
        margin-top: 15px;
        font-weight: 500;
    }
</style>
</head>

<body>

<div class="login-card">
    <h3>Owner Login</h3>

    <form method="post">
        <label>Email</label>
        <input type="email" name="email" placeholder="Email" required value="<?php echo isset($email) ? $email : '' ?>">

        <label>Password</label>
        <input type="password" name="password" placeholder="Password" required>

        <button type="submit" name="login" class="btn-owner">Login</button>
    </form>

    <?php if($error): ?>
        <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>
</div>

</body>
</html>
