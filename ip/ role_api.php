<?php
include 'db_connect.php';

$action = $_POST['action'] ?? '';

if ($action === 'add') {
    $name = $_POST['name'] ?? '';
    if (!empty($name)) {
        $stmt = $conn->prepare("INSERT INTO roles (role_name) VALUES (?)");
        $stmt->bind_param("s", $name);
        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "id" => $conn->insert_id, "name" => $name]);
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
            echo json_encode(["status" => "success", "message" => "Role deleted successfully"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to delete role"]);
        }
        $stmt->close();
    }
} elseif ($action === 'delete_all') {
    $stmt = $conn->prepare("DELETE FROM roles");
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "All roles deleted successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to delete all roles"]);
    }
    $stmt->close();
}

$conn->close();
?>
