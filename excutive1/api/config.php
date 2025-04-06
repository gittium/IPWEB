<?php
$servername = "db"; // หรือ IP ของเซิร์ฟเวอร์ฐานข้อมูล
$username = "root"; // เปลี่ยนเป็นชื่อผู้ใช้ฐานข้อมูลของคุณ
$password = "MYSQL_ROOT_PASSWORD"; // เปลี่ยนเป็นรหัสผ่านฐานข้อมูลของคุณ
$dbname = "ipweb4"; // เปลี่ยนเป็นชื่อฐานข้อมูลของคุณ

// สร้างการเชื่อมต่อ
$db = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($db->connect_error) {
    die("❌ Connection failed: " . $db->connect_error);
}

// Set character encoding
if (!$db->set_charset("utf8mb4")) {
    die("❌ Error loading character set utf8mb4: " . $db->error);
}
// Test a simple query



// Close connection
