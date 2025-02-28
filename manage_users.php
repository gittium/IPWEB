<?php
// เชื่อมต่อฐานข้อมูล
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ip2";

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
    $sql = "SELECT u.user_id, s.name, s.email, 'นิสิต' AS role, u.role_status_id, 4 AS role_id 
            FROM user u 
            JOIN students s ON u.user_id = s.students_id
            WHERE u.role_id = 4
            UNION
            SELECT u.user_id, t.name, t.email, 'อาจารย์' AS role, u.role_status_id, 3 AS role_id 
            FROM user u 
            JOIN teachers t ON u.user_id = t.teachers_id
            WHERE u.role_id = 3
            ORDER BY role_id DESC, user_id ASC
            LIMIT $start, $limit";
} elseif ($role == "teacher") {
    $sql = "SELECT u.user_id, t.name, t.email, 'อาจารย์' AS role, u.role_status_id, 3 AS role_id
            FROM user u 
            JOIN teachers t ON u.user_id = t.teachers_id
            WHERE u.role_id = 3
            ORDER BY teachers_id ASC
            LIMIT $start, $limit";
} elseif ($role == "student") {
    $sql = "SELECT u.user_id, s.name, s.email, 'นิสิต' AS role, u.role_status_id, 4 AS role_id
            FROM user u 
            JOIN students s ON u.user_id = s.students_id
            WHERE u.role_id = 4
            ORDER BY students_id ASC
            LIMIT $start, $limit";
}

// ** ตรวจสอบการอัปเดตสถานะ (Disable / Activate) ผ่าน AJAX **
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id']) && isset($_POST['status'])) {
    $user_id = $_POST['user_id'];
    $status = (int) $_POST['status']; // แปลงเป็น integer

    // เชื่อมต่อฐานข้อมูล
    $conn = new mysqli("localhost", "root", "", "ip2");

    // เช็คว่ามีปัญหากับการเชื่อมต่อฐานข้อมูลหรือไม่
    if ($conn->connect_error) {
        die(json_encode(['success' => false, 'error' => 'Database connection failed: ' . $conn->connect_error]));
    }

    // อัปเดตฐานข้อมูล
    $sql = "UPDATE user SET role_status_id = ? WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        echo json_encode(['success' => false, 'error' => 'Prepare failed: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param("is", $status, $user_id);
    $response = [];

    if ($stmt->execute()) {
        $response['success'] = true;
    } else {
        $response['success'] = false;
        $response['error'] = $stmt->error;
    }

    $stmt->close();
    $conn->close();

    // บอกให้ browser รู้ว่าเป็น JSON
    header('Content-Type: application/json');
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
    <link rel="stylesheet" href="css/style_managruser.css">
    <link rel="stylesheet" href="style_sidebar&role.css">
<style>
    /* ปุ่ม Pagination */
.pagination a {
    padding: 8px 16px;
    margin: 4px;
    border-radius: 5px;
    text-decoration: none;
    transition: 0.3s;
    color: white;
    background-color: var(--orange); /* สีส้ม */
}

.pagination a:hover {
    background-color: #FF6B00; /* สีส้มเข้มขึ้น */
}

/* ปุ่มที่ Active อยู่ */
.pagination a.active {
    background-color: #FF6B00; /* สีส้มเข้มขึ้น */
    font-weight: bold;
}

       .pagination {
           display: flex;
           justify-content: space-between;
           align-items: center;
           margin-top: 50px;
       }

       .pagination span {
           font-size: 14px;
       }

       .pagination a {
           background-color: #fff;
           border: 1px solid #ccc;
           text-decoration: none;
           padding: 5px 10px;
           border: 1px solid #ccc;
           border-radius: 5px;
           color: #333;
           margin: 0 5px;
       }

       .pagination a:hover {
           background-color: #f4f4f4;
       }

       .pagination .active {
           background-color: var(--orange);
           border-color: var(--orange);
           cursor: default;
       }
    /* ปุ่ม Disable และ Activate */
    .status-btn {
    padding: 8px 16px;
    font-size: 14px;
    font-weight: normal;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: all 0.3s ease-in-out;
    text-transform: uppercase;
    width: 110px; /* กำหนดขนาดปุ่มให้เท่ากัน */
}
  
/* ปุ่ม Disable (สีแดง) */
.status-btn[data-status="2"] {
    background-color: #dc3545; /* สีแดง */
    color: white;
}

.status-btn[data-status="2"]:hover {
    background-color: #b52a37; /* สีแดงเข้มขึ้น */
}

/* ปุ่ม Activate (สีเขียว) */
.status-btn[data-status="1"] {
    background-color: #28a745; /* สีเขียว */
    color: white;
}

.status-btn[data-status="1"]:hover {
    background-color: #1e7e34; /* สีเขียวเข้มขึ้น */
}
</style>
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
                <th style="text-align: center;">Actions</th>
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
                <td style="display: flex; justify-content: center; align-items: center; gap: 40px;">
                <?php if ($row['role_id'] == 4) { ?>
        <a href="admin_student_profile.php?id=<?= $row['user_id']; ?>" class="view-link"
            style="text-decoration: none; padding: 8px 15px; background-color: #007bff; color: white; border-radius: 8px;">
            View
        </a>
    <?php } else { ?>
        <a href="admin_teacher_profile.php?teachers_id=<?= $row['user_id']; ?>" class="view-link"
            style="text-decoration: none; padding: 8px 15px; background-color: #007bff; color: white; border-radius: 8px;">
            View
        </a>
    <?php } ?>


                    <?php if ($row['role_status_id'] == 1) { ?>
                        <button class="status-btn" data-id="<?= $row['user_id']; ?>" data-status="2" 
                            style="background-color: rgba(255, 0, 0, 0.7); color: white; border: none; padding: 8px 15px; border-radius: 8px; cursor: pointer; transition: background-color 0.3s, transform 0.2s;">
                            Disable
                        </button>
                    <?php } else { ?>
                        <button class="status-btn" data-id="<?= $row['user_id']; ?>" data-status="1" 
                            style="background-color: rgba(0, 128, 0, 0.7); color: white; border: none; padding: 8px 15px; border-radius: 8px; cursor: pointer; transition: background-color 0.3s, transform 0.2s;">
                            Activate
                        </button>
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

    <script src="js/script_manage_users.js"></script>
    <script src="script_sidebar&role.js"></script>
</body>

</html>



<?php $conn->close(); ?>
