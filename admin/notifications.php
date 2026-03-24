<?php
session_start();
include '../db.php';

if(isset($_SESSION['admin_id'])){
    mysqli_query($conn, "DELETE FROM notifications");
}

header("Location: " . $_SERVER['HTTP_REFERER']);
exit();
?>
