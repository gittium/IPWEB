<?php
include 'database.php';
header('Content-Type: application/json');

$categoryId = $_GET['category_id'] ?? '';

if ($categoryId) {
    $stmt = $conn->prepare("SELECT job_sub_id, subcategories_name FROM job_subcategories WHERE job_categories_id = ?");
    $stmt->bind_param("i", $categoryId);
    $stmt->execute();
    $result = $stmt->get_result();

    $jobSubcategories = [];
    while ($row = $result->fetch_assoc()) {
        $jobSubcategories[] = ['id' => $row['job_sub_id'], 'name' => $row['subcategories_name']];
    }

    echo json_encode($jobSubcategories);
} else {
    echo json_encode([]);
}
?>
