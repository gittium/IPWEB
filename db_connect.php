<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost"; 
$username = "root"; // ค่าเริ่มต้นของ XAMPP
$password = ""; // ค่าเริ่มต้นของ XAMPP (ไม่มีรหัสผ่าน)
$dbname = "ip"; 

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>

