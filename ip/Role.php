<?php
include 'db_connect.php';

// รับค่าจาก AJAX
$action = $_POST['action'] ?? '';

if ($action === 'add') {
    $name = $_POST['name'] ?? '';
    if (!empty($name)) {
        $stmt = $conn->prepare("INSERT INTO roles (name) VALUES (?)");
        $stmt->bind_param("s", $name);
        if ($stmt->execute()) {
            $inserted_id = $stmt->insert_id; // ดึง ID ที่เพิ่มเข้าไป
            echo json_encode(["status" => "success", "id" => $inserted_id, "name" => $name]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to add role"]);
        }
        $stmt->close();
    }
} elseif ($action === 'delete') {
    $id = $_POST['id'] ?? 0;
    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM roles WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to delete role"]);
        }
        $stmt->close();
    }
}

$conn->close();
?>
