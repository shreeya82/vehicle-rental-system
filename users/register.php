<?php
session_start();
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $redirect = $_POST['redirect'] ?? 'index.php';

    $errors = [];
    $old = [
        'full_name' => $full_name,
        'phone' => $phone,
        'email' => $email
    ];

    // --- Validation ---
    if (!preg_match('/^[A-Za-z]+(\s[A-Za-z]+)+$/', $full_name)) 
        $errors['full_name'] = 'Full name must include first and last name';
    
    if (!preg_match('/^(97|98)\d{8}$/', $phone)) 
        $errors['phone'] = 'Phone must start with 97 or 98 and be 10 digits';
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) 
        $errors['email'] = 'Invalid email';
    
    if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d).{6,}$/', $password)) 
        $errors['password'] = 'Password must be at least 6 characters and include letters and numbers';

    // Check if email already exists
    $check = mysqli_query($conn, "SELECT user_id FROM users WHERE email='$email'");
    if ($check && mysqli_num_rows($check) > 0) 
        $errors['email'] = 'Email already registered';

    // --- If there are errors, save in session and redirect ---
    if (!empty($errors)) {
        $_SESSION['register_errors'] = $errors;
        $_SESSION['register_old'] = $old;
        header("Location: $redirect");
        exit();
    }

    // --- Insert user ---
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $insert = mysqli_query($conn, "INSERT INTO users (full_name, phone, email, password) VALUES ('$full_name','$phone','$email','$hashed')");

    if ($insert) {
        // Success message: wait for admin approval
        $_SESSION['register_success'] = "Please wait for admin's approval";

        // Insert notification for admin
        $user_id = mysqli_insert_id($conn); 
        $user_name = mysqli_real_escape_string($conn, $full_name);
        $message = "New user registered: $user_name (ID: $user_id)";
        mysqli_query($conn, "INSERT INTO notifications (type, reference_id, message) VALUES ('user_registration', $user_id, '$message')");
    } else {
        // Insert failed
        $_SESSION['register_errors'] = ['general' => 'Registration failed. Try again.'];
        $_SESSION['register_old'] = $old;
    }

    // Redirect back
    header("Location: $redirect");
    exit();
}
