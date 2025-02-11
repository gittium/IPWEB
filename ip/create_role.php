<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $role_name = $_POST["role_name"];
    $sql = "INSERT INTO roles (role_name) VALUES ('$role_name', NOW())";
    $conn->query($sql);
}
$conn->close();
?>
