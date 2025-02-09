<?php
// เชื่อมต่อฐานข้อมูล
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ip";

$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// รับค่าหน้าปัจจุบัน
$limit = 10; // จำนวนผู้ใช้ที่แสดงต่อหน้า
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max($page, 1); // ป้องกันค่าติดลบ
$start = ($page - 1) * $limit;

// รับค่า role ที่เลือก
$role = isset($_GET['role']) ? $_GET['role'] : 'all';

// Query นับจำนวนข้อมูลทั้งหมด
$count_sql = "SELECT COUNT(*) AS total FROM users WHERE role_id IN (3,4)";
if ($role == "teacher") {
    $count_sql = "SELECT COUNT(*) AS total FROM users WHERE role_id = 3";
} elseif ($role == "student") {
    $count_sql = "SELECT COUNT(*) AS total FROM users WHERE role_id = 4";
}
$count_result = $conn->query($count_sql);
$total_users = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_users / $limit);

// Query ดึงข้อมูลนิสิต + อาจารย์ พร้อมแบ่งหน้า
if ($role == "all") {
    $sql = "SELECT u.id, s.name, s.email, 'นิสิต' AS role, u.role_status_id 
            FROM users u 
            JOIN students s ON u.id = s.id
            WHERE u.role_id = 4
            UNION
            SELECT u.id, t.name, t.email, 'อาจารย์' AS role, u.role_status_id 
            FROM users u 
            JOIN teachers t ON u.id = t.id
            WHERE u.role_id = 3
            LIMIT $start, $limit";
} elseif ($role == "teacher") {
    $sql = "SELECT u.id, t.name, t.email, 'อาจารย์' AS role, u.role_status_id 
            FROM users u 
            JOIN teachers t ON u.id = t.id
            WHERE u.role_id = 3
            LIMIT $start, $limit";
} elseif ($role == "student") {
    $sql = "SELECT u.id, s.name, s.email, 'นิสิต' AS role, u.role_status_id 
            FROM users u 
            JOIN students s ON u.id = s.id
            WHERE u.role_id = 4
            LIMIT $start, $limit";
}

// ** ตรวจสอบการอัปเดตสถานะ (Disable / Activate) ผ่าน AJAX **
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id']) && isset($_POST['status'])) {
    $user_id = (int) $_POST['user_id'];
    $status = (int) $_POST['status'];

    // อัปเดต role_status_id (1 = Available, 2 = Unavailable)
    $sql = "UPDATE users SET role_status_id = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $status, $user_id);
    $response = [];

    if ($stmt->execute()) {
        $response['success'] = true;
    } else {
        $response['success'] = false;
    }

    $stmt->close();
    $conn->close();

    echo json_encode($response);
    exit;
}

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Manage User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.gstatic.com" />
    <link
        href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Montserrat:wght@600&display=swap"
        rel="stylesheet" />

    <!-- External CSS -->
    <link rel="stylesheet" href="style_managruser.css">
    <link rel="stylesheet" href="style_sidebar&role.css">
</head>

<body>
    <!-- ======= MAIN CONTENT เนื้อหาหลัก ======= -->
    <div class="container">
        <!-- Placeholder for Sidebar -->
        <div id="sidebar-container"></div>

        <main class="main-content">
            <div class="content">
    <h1>Manage Users</h1>

    <div class="role-group">
        <span class="role-label">Role:</span>
        <select id="roleSelect" class="role-select">
            <option value="all" <?= $role == "all" ? "selected" : "" ?>>ทั้งหมด</option>
            <option value="teacher" <?= $role == "teacher" ? "selected" : "" ?>>อาจารย์</option>
            <option value="student" <?= $role == "student" ? "selected" : "" ?>>นิสิต</option>
        </select>
    </div>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?= $row['name']; ?></td>
                    <td><a href="mailto:<?= $row['email']; ?>"><?= $row['email']; ?></a></td>
                    <td>
                        <?= $row['role_status_id'] == 1 ? '<span style="color:green;">Available</span>' : '<span style="color:red;">Unavailable</span>'; ?>
                    </td>
                    <td>
    <a href="user_profile.php?id=<?= $row['id']; ?>" class="view-link">View</a>
    <?php if ($row['role_status_id'] == 1) { ?>
        <button class="status-btn" data-id="<?= $row['id']; ?>" data-status="2">Disable</button>
    <?php } else { ?>
        <button class="status-btn" data-id="<?= $row['id']; ?>" data-status="1">Activate</button>
    <?php } ?>
</td>

                </tr>
            <?php } ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <div class="pagination">
        <span>Showing <?= ($start + 1); ?> to <?= min($start + $limit, $total_users); ?> of <?= $total_users; ?> entries</span>
        <div>
            <?php if ($page > 1) { ?>
                <a href="?role=<?= $role; ?>&page=<?= $page - 1; ?>">Previous</a>
            <?php } ?>

            <?php for ($i = 1; $i <= $total_pages; $i++) { ?>
                <a href="?role=<?= $role; ?>&page=<?= $i; ?>" class="<?= ($i == $page) ? 'active' : ''; ?>"><?= $i; ?></a>
            <?php } ?>

            <?php if ($page < $total_pages) { ?>
                <a href="?role=<?= $role; ?>&page=<?= $page + 1; ?>">Next</a>
            <?php } ?>
        </div>
    </div>
    </div>
        </main>
    </div>
    </div>

    <script src="script_manage_users.js"></script>
    <script src="script_sidebar&role.js"></script>
</body>

</html>

<?php $conn->close(); ?>
