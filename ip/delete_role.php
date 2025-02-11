<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = $_POST["id"];
    $sql = "DELETE FROM roles WHERE id = $id";
    $conn->query($sql);
}
$conn->close();
?>
