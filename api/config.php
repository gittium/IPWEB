<?php
$host = 'db'; // Replace with your MySQL service name in docker-compose.yml
$port = 3306; // Default MySQL port inside Docker
$username = 'laravel'; // Change based on your MySQL setup
$password = 'laravel'; // Change based on your MySQL setup
$database = 'ipweb5';

// Create connection
$db = new mysqli($host, $username, $password, $database, $port);

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

?>
