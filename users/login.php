<?php
session_start();
include '../db.php'; 

if(isset($_POST['email']) && isset($_POST['password'])){
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    $redirect_url = isset($_POST['redirect']) ? $_POST['redirect'] : 'index.php';

    $query = mysqli_query($conn, "SELECT * FROM users WHERE email='$email' AND role='user'"); 
    if(mysqli_num_rows($query) == 1){
        $user = mysqli_fetch_assoc($query);

        if(password_verify($password, $user['password'])) {

            if($user['is_approved'] == 0){
                $_SESSION['login_error'] = "Your account is not approved yet. Please wait for admin approval.";
                header("Location: $redirect_url");
                exit();
            }

            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['full_name'] = $user['full_name'];  
            $_SESSION['role'] = 'user';                 
            $_SESSION['login_success'] = "Welcome back, {$user['full_name']}!";

            header("Location: $redirect_url"); 
            exit();

        } else {
            $_SESSION['login_error'] = "Incorrect password!";
            header("Location: $redirect_url");
            exit();
        }
    } else {
        $_SESSION['login_error'] = "User not found!";
        header("Location: $redirect_url");
        exit();
    }
}
?>
