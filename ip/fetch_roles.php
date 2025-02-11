<?php
include 'db_connect.php';

$page = $_GET['page'] ?? 1;
$entriesPerPage = $_GET['entriesPerPage'] ?? 2;
$offset = ($page - 1) * $entriesPerPage;

// รับค่าค้นหาจาก GET
$search = $_GET['search'] ?? '';

// ปรับ SQL query ให้รองรับการค้นหา
$sql = "SELECT * FROM roles WHERE role_name LIKE ? OR id LIKE ? LIMIT $entriesPerPage OFFSET $offset";
$stmt = $conn->prepare($sql);
$searchParam = "%$search%";
$stmt->bind_param("ss", $searchParam, $searchParam);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr id='role-" . $row["id"] . "'>";
        echo "<td>" . htmlspecialchars($row["role_name"]) . "</td>";
        echo "<td><button onclick='deleteRole(" . $row["id"] . ")'>Delete</button></td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='2'>No roles found</td></tr>";
}

// Get total entries
$totalSql = "SELECT COUNT(*) as total FROM roles WHERE role_name LIKE ? OR id LIKE ?";
$totalStmt = $conn->prepare($totalSql);
$totalStmt->bind_param("ss", $searchParam, $searchParam);
$totalStmt->execute();
$totalResult = $totalStmt->get_result();
$totalRow = $totalResult->fetch_assoc();
$totalEntries = $totalRow['total'];

$conn->close();
?>
