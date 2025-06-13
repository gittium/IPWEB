<?php
session_start();
include 'database.php';
// ตรวจสอบว่า session 'user_id' ถูกตั้งค่าไว้หรือไม่ (หมายถึงผู้ใช้ได้เข้าสู่ระบบ)
if (!isset($_SESSION['user_id'])) {
    // หากไม่ได้เข้าสู่ระบบ ให้รีไดเรกต์ไปหน้า login
    header("Location: ../login.php");
    exit;
}
// รับค่าหน้าปัจจุบัน
$limit = 5; // จำนวนผู้ใช้ที่แสดงต่อหน้า
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max($page, 1); // ป้องกันค่าติดลบ
$start = ($page - 1) * $limit;
 
// รับค่า role ที่เลือก
$role = isset($_GET['role']) ? $_GET['role'] : 'all';
// รับค่า search ที่กรอกเข้ามา
$search = isset($_GET['search']) ? $_GET['search'] : '';
 
// Query นับจำนวนข้อมูลทั้งหมด
$count_sql = "SELECT COUNT(*) AS total FROM user WHERE role_id IN (3,4)";
if ($role == "teacher") {
    $count_sql = "SELECT COUNT(*) AS total FROM user WHERE role_id = 3";
} elseif ($role == "student") {
    $count_sql = "SELECT COUNT(*) AS total FROM user WHERE role_id = 4";
}
 
$count_result = $conn->query($count_sql);
$total_users = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_users / $limit);
 
// Query ดึงข้อมูลนิสิต + อาจารย์ พร้อมแบ่งหน้า
if ($role == "all") {
    $sql = "SELECT u.user_id, s.stu_name, s.stu_email, 'นิสิต' AS role, u.role_status_id, 4 AS role_id
            FROM user u
            JOIN student s ON u.user_id = s.student_id
            WHERE u.role_id = 4
            AND (s.stu_name LIKE '%$search%' OR s.stu_email LIKE '%$search%')
            UNION
            SELECT u.user_id, t.teach_name, t.teach_email, 'อาจารย์' AS role, u.role_status_id, 3 AS role_id
            FROM user u
            JOIN teacher t ON u.user_id = t.teacher_id
            WHERE u.role_id = 3
            AND (t.teach_name LIKE '%$search%' OR t.teach_email LIKE '%$search%')
            ORDER BY role_id DESC, user_id ASC
            LIMIT $start, $limit";
} elseif ($role == "teacher") {
    $sql = "SELECT u.user_id, t.teach_name, t.teach_email, 'อาจารย์' AS role, u.role_status_id, 3 AS role_id
            FROM user u
            JOIN teacher t ON u.user_id = t.teacher_id
            WHERE u.role_id = 3
            AND (t.teach_name LIKE '%$search%' OR t.teach_email LIKE '%$search%')
            ORDER BY t.teacher_id ASC
            LIMIT $start, $limit";
} elseif ($role == "student") {
    $sql = "SELECT u.user_id, s.stu_name, s.stu_email, 'นิสิต' AS role, u.role_status_id, 4 AS role_id
            FROM user u
            JOIN student s ON u.user_id = s.student_id
            WHERE u.role_id = 4
            AND (s.stu_name LIKE '%$search%' OR s.stu_email LIKE '%$search%')
            ORDER BY s.student_id ASC
            LIMIT $start, $limit";
}
 
// ** ตรวจสอบการอัปเดตสถานะ (Disable / Activate) ผ่าน AJAX **
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id']) && isset($_POST['status'])) {
    header('Content-Type: application/json');
 
    $user_id = $conn->real_escape_string($_POST['user_id']);
    $status = (int) $_POST['status'];
 
    $sql = "UPDATE user SET role_status_id = ? WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
 
    if ($stmt === false) {
        echo json_encode(['success' => false, 'error' => 'Prepare failed: ' . $conn->error]);
        exit;
    }
 
    $stmt->bind_param("is", $status, $user_id);
 
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'new_status' => $status]);
    } else {
        echo json_encode(['success' => false, 'error' => $stmt->error]);
    }
 
    $stmt->close();
    $conn->close();
    exit;
}
$result = $conn->query($sql);
?>
 
<!DOCTYPE html>
<html lang="en">
 
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
 
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.gstatic.com" />
    <link
        href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Montserrat:wght@600&display=swap"
        rel="stylesheet" />
 
    <!-- External CSS -->
    <link rel="stylesheet" href="css/style_managruser.css">
    <link rel="stylesheet" href="style_sidebar.css">
</head>
 
<body>
<div class="containersb">
        <div id="sidebar-container">
        </div>
        <main class="main-content">
        <button class="menu-toggle">☰ Menu</button>
            <div class="content">
    <h1>Manage User</h1>
    <hr class="my-3">
    <div class="role-group">
        <span class="role-label">Role:</span>
        <select id="roleSelect" class="role-select">
            <option value="all" <?= $role == "all" ? "selected" : "" ?>>ทั้งหมด</option>
            <option value="teacher" <?= $role == "teacher" ? "selected" : "" ?>>อาจารย์</option>
            <option value="student" <?= $role == "student" ? "selected" : "" ?>>นิสิต</option>
        </select>
    </div>

    <form method="GET" action="manage_users.php">
    <div class="search-bar">
        <input type="text" name="search" placeholder="Search" value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit">Search</button>
    </div>
</form>
 
 
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Status</th>
                <th style="text-align: center;">Actions</th>
            </tr>
        </thead>
        <tbody>
<?php while ($row = $result->fetch_assoc()) { ?>
    <tr>
        <td><?= $row['stu_name'] ?? $row['teach_name']; ?></td> <!-- Display either student's or teacher's name -->
        <td><a href="mailto:<?= $row['stu_email'] ?? $row['teach_email']; ?>"><?= $row['stu_email'] ?? $row['teach_email']; ?></a></td> <!-- Display either student's or teacher's email -->
        <td class="status-text">
            <?= $row['role_status_id'] == 1 ? '<span style="color:green;">Activate</span>' : '<span style="color:red;">Disable</span>'; ?>
        </td>  
        <td style="display: flex; justify-content: center; align-items: center; gap: 40px;">
        <div class="actions">
            <?php if ($row['role_id'] == 4) { ?>
                <a href="admin_student_profile.php?student_id=<?= $row['user_id']; ?>" class="view-link"
                style="text-decoration: none; padding: 6px 17px; background-color: #007bff; color: white; border-radius: 8px;">
                   View
                </a>
            <?php } else { ?>
                <a href="admin_teacher_profile.php?teacher_id=<?= $row['user_id']; ?>" class="view-link"
                style="text-decoration: none; padding: 6px 17px; background-color: #007bff; color: white; border-radius: 8px;">
                   View
                </a>
            <?php } ?>
 
            <?php if ($row['role_status_id'] == 1) { ?>
                <button class="status-btn"  data-id="<?= $row['user_id']; ?>" data-status="2">Disable</button>
            <?php } else { ?>
                <button class="status-btn" data-id="<?= $row['user_id']; ?>" data-status="1">Activate</button>
            <?php } ?>
        </td>
    </tr>
<?php } ?>
</tbody>
 
 
    </table>
 
    <!-- Pagination -->

<!-- Pagination -->
<!-- Pagination -->
<div class="pagination">
    <span>
        Showing <?= ($start + 1); ?> to <?= min($start + $limit, $total_users); ?> of <?= $total_users; ?> entries
    </span>
    <div>
        <?php if ($page > 1) { ?>
            <a href="?role=<?= $role; ?>&search=<?= urlencode($search); ?>&page=<?= $page - 1; ?>">Previous</a>
        <?php } ?>
 
        <?php for ($i = 1; $i <= $total_pages; $i++) { ?>
            <a href="?role=<?= $role; ?>&search=<?= urlencode($search); ?>&page=<?= $i; ?>" class="<?= ($i == $page) ? 'active' : ''; ?>"><?= $i; ?></a>
        <?php } ?>
 
        <?php if ($page < $total_pages) { ?>
            <a href="?role=<?= $role; ?>&search=<?= urlencode($search); ?>&page=<?= $page + 1; ?>">Next</a>
        <?php } ?>
    </div>
</div>



        </main>
    </div>
    </div>
    <script src="js/script_manage_users.js"></script>
    <script src="script_sidebar.js"></script>
    <script>
document.addEventListener("DOMContentLoaded", function () {
  const toggleButton = document.querySelector(".menu-toggle");
  const sidebar = document.getElementById("sidebar-container");

  toggleButton.addEventListener("click", function () {
    sidebar.classList.toggle("active");
  });
});
</script>



</body>
</html>
<?php $conn->close(); ?>