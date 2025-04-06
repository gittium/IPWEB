<?php
require 'database.php'; // ไฟล์เชื่อมต่อฐานข้อมูล

$job_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$current_status = isset($_POST['status']) ? intval($_POST['status']) : 1;
$close_detail_id = isset($_POST['close_detail_id']) ? intval($_POST['close_detail_id']) : null;
$detail = isset($_POST['detail']) ? trim($_POST['detail']) : null;

// ถ้าจะเปลี่ยนจาก Open (1) -> Close (2) ต้องเลือกเหตุผลก่อน
if ($current_status == 1 && $close_detail_id === null) {
    echo json_encode(['success' => false, 'message' => 'กรุณาเลือกเหตุผลในการปิดงาน']);
    exit;
}

// ถ้าเลือก close_detail_id = 12 ต้องกรอกเหตุผลเพิ่มเติม
if ($close_detail_id == 12 && empty($detail)) {
    echo json_encode(['success' => false, 'message' => 'กรุณากรอกเหตุผลเพิ่มเติม']);
    exit;
}

$new_status = ($current_status == 1) ? 2 : 1;

// อัปเดตสถานะในตาราง post_jobs
$sql = "UPDATE post_jobs SET job_status_id = ? WHERE post_jobs_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $new_status, $job_id);
$stmt->execute();
$stmt->close();

// ถ้าเปลี่ยนเป็น Close (2) ต้องบันทึกเหตุผลลง close_jobs
if ($new_status == 2) {
    $sql = "INSERT INTO close_jobs (post_jobs_id, close_detail_id, detail) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $job_id, $close_detail_id, $detail);
    $stmt->execute();
    $stmt->close();
}

echo json_encode(['success' => true, 'new_status' => $new_status]);
?>
