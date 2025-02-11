<?php
include 'db_connect.php';
$conn->query("DELETE FROM roles");
$conn->close();
?>
