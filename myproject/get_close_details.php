<?php
require 'database.php'; // เชื่อมต่อฐานข้อมูล

$sql = "SELECT close_detail_id, close_detail_name FROM close_detail ORDER BY close_detail_id";
$result = $conn->query($sql);

$close_details = [];

while ($row = $result->fetch_assoc()) {
    $close_details[] = $row;
}

// ส่ง JSON กลับไปให้ JavaScript
header('Content-Type: application/json');
echo json_encode($close_details);
?>
