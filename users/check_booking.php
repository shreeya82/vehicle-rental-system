<?php
session_start();
include '../db.php';

if(!isset($_SESSION['user_id'])){
    echo json_encode(['exists'=>false]);
    exit();
}

$user_id = $_SESSION['user_id'];
$start_date = $_POST['start_date'];
$end_date = $_POST['end_date'];

$sql = "SELECT b.*, v.vehicle_name FROM bookings b
        JOIN vehicles v ON b.vehicle_id = v.vehicle_id
        WHERE b.user_id = ?
        AND b.status IN ('Pending','Confirmed')
        AND (
             (? BETWEEN b.start_date AND b.end_date)
          OR (? BETWEEN b.start_date AND b.end_date)
          OR (b.start_date BETWEEN ? AND ?)
        ) LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("issss",$user_id,$start_date,$end_date,$start_date,$end_date);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows > 0){
    $row = $result->fetch_assoc();
    echo json_encode([
        'exists'=>true,
        'vehicle_name'=>$row['vehicle_name'],
        'start_date'=>$row['start_date'],
        'end_date'=>$row['end_date']
    ]);
} else {
    echo json_encode(['exists'=>false]);
}
?>
