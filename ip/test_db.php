<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ip";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
} else {
    echo "✅ Database Connected Successfully!<br>";
}

$sql = "SELECT * FROM roles";
$result = $conn->query($sql);

if ($result) {
    if ($result->num_rows > 0) {
        echo "📋 Found " . $result->num_rows . " roles:<br>";
        while ($row = $result->fetch_assoc()) {
            echo "🆔 ID: " . $row["id"] . " - Name: " . $row["role_name"] . "<br>";
        }
    } else {
        echo "⚠️ No roles found in the database.";
    }
} else {
    echo "❌ SQL Error: " . $conn->error;
}
?>
