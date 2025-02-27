<?php
session_start();
include 'database.php';

// ตรวจสอบว่าเป็น AJAX Request หรือไม่
$is_ajax = isset($_GET['ajax']) && $_GET['ajax'] == "1";

// รับค่า id และฟิลเตอร์
$post_jobs_id = isset($_GET['post_jobs_id']) ? intval($_GET['post_jobs_id']) : '';
$filter_major_name = isset($_GET['major_name']) ? urldecode($_GET['major_name']) : '';
$filter_year = isset($_GET['year']) ? intval($_GET['year']) : 0;

// ตรวจสอบค่าที่รับมา
if ($post_jobs_id <= 0) {
    die(json_encode(["error" => "ไม่พบข้อมูลงานที่ต้องการ"]));
}

// ตรวจสอบค่าที่ได้รับจาก URL (Debugging)
error_log("post_jobs_id: " . $post_jobs_id);
error_log("major_name: " . $filter_major_name);
error_log("year: " . $filter_year);

$sql = "SELECT DISTINCT 
    ja.job_app_id AS job_application_id, 
    ja.post_jobs_id,pj.title,
    s.students_id AS student_id, 
    s.name, 
    s.year, 
    m.major_name 
FROM job_application ja 
JOIN students s ON ja.students_id = s.students_id
JOIN major m ON s.major_id = m.major_id
JOIN post_jobs pj ON ja.post_jobs_id = pj.post_jobs_id
WHERE ja.post_jobs_id = ?";

$params = [$post_jobs_id];
$types = "i";

// เพิ่มตัวกรอง Major โดยใช้ `major_name`
if (!empty($filter_major_name)) {
    $sql .= " AND m.major_name = ?";
    $params[] = $filter_major_name;
    $types .= "s";
}

// เพิ่มตัวกรอง Year (ปีการศึกษา)
if ($filter_year > 0) {
    $sql .= " AND s.year = ?";
    $params[] = $filter_year;
    $types .= "i";
}

// ตรวจสอบ SQL Query ที่จะถูกเรียกใช้ (Debugging)
error_log("SQL Query: " . $sql);
error_log("Parameters: " . implode(", ", $params));

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die(json_encode(["error" => "SQL Error: " . $conn->error]));
}

$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

if ($is_ajax) {
    $applicants = [];
    while ($row = $result->fetch_assoc()) {
        $applicants[] = $row;
    }
    echo json_encode($applicants);
    exit;
}
$sqlJobs = "SELECT * 
            FROM post_jobs
            WHERE post_jobs_id = ?";
$stmtJ = $conn->prepare($sqlJobs);
$stmtJ->bind_param("i", $post_jobs_id);
$stmtJ->execute();
$resJobs = $stmtJ->get_result();
$jobs = [];
while ($rowJob = $resJobs->fetch_assoc()) {
    $jobs[] = $rowJob;
}
$stmtJ->close();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Teaching Assistant View Applications Page">
    <title>Teaching Assistant Applications</title>
    <!-- Google Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&family=Montserrat:wght@600&display=swap"
        rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/header-footerstyle.css">
    <link rel="stylesheet" href="css/viewapply.css">
    <script src="js/viewapply.js"></script>
</head>

<body>
    <!-- Header -->
    <header class="headerTop">
        <div class="headerTopImg">
            <img src="logo.png" alt="Naresuan University Logo">
            <a href="#">Naresuan University</a>
        </div>
        <nav class="header-nav">
            <?php
            // ตรวจสอบสถานะการล็อกอิน
            if (isset($_SESSION['user_id'])) {
                echo '<a href="logout.php">Logout</a>';
            } else {
                // หากยังไม่ได้ล็อกอิน แสดงปุ่มเข้าสู่ระบบ
                echo '<a href="login.php">Login</a>';
            }
            ?>
        </nav>
    </header>

    <!-- ปุ่ม back -->
    <div>
        <a href="teacher_profile.php" class="back-arrow"></a>
    </div>

    <!-- Main Content -->
    <div class="container">
        <div class="title-container">
            <a href="viewapply.php?post_jobs_id=<?php echo $post_jobs_id; ?>">View Applications</a>
            <!-- ลิงก์ไปยัง viewapply.php -->
            <a href="jobmanage.php?post_jobs_id=<?php echo $post_jobs_id; ?>">Manage Job</a>
            <!-- ลิงก์ไปยัง jobmanage.php -->
        </div>
        <br>
        <div>
            <?php
                if (!empty($jobs)) {
                    echo '<h1>' . htmlspecialchars($jobs[0]['title']) . '</h1>';
                } else {
                    echo '<h1>No job found</h1>';
                }
            ?>
        </div>
        <br>
        <div class="bar">
            <a href="viewapply.php" class="<?= empty($_GET['major_name']) ? 'active' : '' ?>">ทั้งหมด</a>
            <a href="viewapply.php?major_name=Computer Science"
                class="<?= isset($_GET['major_name']) && $_GET['major_name'] == 'Computer Science' ? 'active' : '' ?>">วิทยาการคอมพิวเตอร์</a>
            <a href="viewapply.php?major_name=Information Technology"
                class="<?= isset($_GET['major_name']) && $_GET['major_name'] == 'Information Technology' ? 'active' : '' ?>">เทคโนโลยีสารสนเทศ</a>
            <i class="bi bi-filter ms-auto" id="filter-btn" style="cursor: pointer;"></i>
        </div>

        <!-- ฟิลเตอร์ -->
        <div id="hidden-message" class="message-box">สาขา<br>
            <button class="branch-btn" data-major="Computer Science">วิทยาการคอมพิวเตอร์</button>
            <button class="branch-btn" data-major="Information Technology">เทคโนโลยีสารสนเทศ</button>

            <br><br>
            <p>ชั้นปี</p>
            <button class="year-btn" data-year="1">ปี 1</button>
            <button class="year-btn" data-year="2">ปี 2</button>
            <button class="year-btn" data-year="3">ปี 3</button>
            <button class="year-btn" data-year="4">ปี 4</button>

            <br><br>
            <button id="clear-btn">ล้าง</button>
            <button id="apply-btn">ตกลง</button>
        </div>
    </div>

    <div class="application-list">
        <!-- รายการใบสมัคร -->
        <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
        <div class="application-card">

            <div class="profile-img"></div>
            <div class="details">
                <div class="name"><?= htmlspecialchars($row['name']) ?></div>
                <div class="department">สาขา <?= htmlspecialchars($row['major_name']) ?></div>
                <div class="year">ปี <?= htmlspecialchars($row['year']) ?></div>
            </div>
            <a href="viewapply2.php?job_app_id=<?php echo $row['job_application_id']; ?>" class="chevron-link">
                <i class="bi bi-chevron-right"></i>
            </a>
        </div>
        <?php endwhile; ?>
        <?php else: ?>
        <p>ไม่พบข้อมูลการสมัคร</p>
        <?php endif; ?>
    </div>

    </div>
    <!-- Footer -->
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const barLinks = document.querySelectorAll(".bar a");

        barLinks.forEach(link => {
            link.addEventListener("click", function(event) {
                event.preventDefault(); // ป้องกันการโหลดหน้าใหม่

                let params = new URLSearchParams(window.location.search);
                let major_name = new URL(this.href).searchParams.get("major_name");

                if (major_name) {
                    major_name = decodeURIComponent(major_name).replace(/\+/g, " ");
                    params.set("major_name", major_name);
                } else {
                    params.delete("major_name");
                }

                console.log("🔄 กำลังเปลี่ยน URL และโหลดข้อมูลใหม่:", params
            .toString()); // Debug
                history.pushState({}, "", "viewapply.php?" + params.toString());
                fetchApplications(params); // โหลดข้อมูลใหม่
            });
        });
    });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-..."
        crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-..."
        crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>