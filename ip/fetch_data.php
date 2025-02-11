<?php
include 'db_connect.php';

// ตรวจสอบการเชื่อมต่อฐานข้อมูล
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ดึงข้อมูลจากฐานข้อมูล
$user_count = 0;
$teacher_count = 0;
$student_count = 0;
$report_count = 0;

// นับจำนวนผู้ใช้งาน
$sql = "SELECT COUNT(*) AS user_count FROM users";
$result = $conn->query($sql);
if ($result === false) {
    die("Error in users query: " . $conn->error);
}
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $user_count = $row['user_count'];
}

// นับจำนวนอาจารย์
$sql = "SELECT COUNT(*) AS teacher_count FROM roles WHERE role_name = 'อาจารย์'";
$result = $conn->query($sql);
if ($result === false) {
    die("Error in teacher query: " . $conn->error);
}
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $teacher_count = $row['teacher_count'];
}

// นับจำนวนนิสิต
$sql = "SELECT COUNT(*) AS student_count FROM roles WHERE role_name = 'นิสิต'";
$result = $conn->query($sql);
if ($result === false) {
    die("Error in student query: " . $conn->error);
}
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $student_count = $row['student_count'];
}

// นับจำนวนรายงาน
$sql = "SELECT COUNT(*) AS report_count FROM reports";
$result = $conn->query($sql);
if ($result === false) {
    die("Error in reports query: " . $conn->error);
}
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $report_count = $row['report_count'];
}

// ส่งข้อมูลกลับเป็น JSON
$response = [
    "user_count" => $user_count,
    "teacher_count" => $teacher_count,
    "student_count" => $student_count,
    "report_count" => $report_count
];

header('Content-Type: application/json');
echo json_encode($response);
?>
