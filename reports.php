<?php
session_start();
include 'database.php';
// ตรวจสอบว่า session 'user_id' ถูกตั้งค่าไว้หรือไม่ (หมายถึงผู้ใช้ได้เข้าสู่ระบบ)
if (!isset($_SESSION['user_id'])) {
    // หากไม่ได้เข้าสู่ระบบ ให้รีไดเรกต์ไปหน้า login
    header("Location: ../login.php");
    exit;
}
// ตรวจสอบว่ามีการคลิก View เพื่อต้องการดูโพสต์ที่ถูกรีพอร์ตหรือไม่
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $post_id = $_GET['id'];

    // ดึงข้อมูลโพสต์ที่ถูกรีพอร์ต
    $sql = "SELECT post_job.title, post_job.reward_type_id, post_job.description, post_job.created_at, 
    post_job.number_student, post_job.job_category_id, post_job.job_subcategory_id, post_job.teacher_id, post_job.job_status_id, 
    post_job.image, teacher.teach_name, teacher.teach_email, 
    job_category.job_category_name, job_status.job_status_name, reward_type.reward_type_name
    FROM post_job
    JOIN teacher ON post_job.teacher_id = teacher.teacher_id
    JOIN job_category ON post_job.job_category_id = job_category.job_category_id
    JOIN job_subcategory ON post_job.job_subcategory_id = job_subcategory.job_subcategory_id
    JOIN job_status ON post_job.job_status_id = job_status.job_status_id
    JOIN reward_type ON post_job.reward_type_id = reward_type.reward_type_id
    WHERE post_job.post_job_id = ?";


    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        die("Post not found.");
    }

    $post = $result->fetch_assoc();

    // ตั้งค่า BASE_URL สำหรับรูปภาพ
    $base_url = "http://localhost/P6/admin/"; // ต้องแก้ให้ตรงกับเซิร์ฟเวอร์จริง
    $image_path = !empty($post['image']) ? $base_url . "images/" . htmlspecialchars($post['image']) : $base_url . "images/img1.jpg";
    ?>

<?php
    exit(); // จบการทำงานหลังจากแสดงโพสต์ที่ถูกรีพอร์ต
}

// รับค่าค้นหา & หน้า
$search = isset($_GET['search']) ? $_GET['search'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 5;
$offset = ($page - 1) * $limit;

// ดึงข้อมูลเฉพาะรีพอร์ตที่มีสถานะ `pending`
$sql = "SELECT report.report_id, report.post_job_id, student.stu_name AS reporter_name, post_job.title 
        FROM report 
        JOIN student ON report.user_id = student.student_id
        JOIN post_job ON report.post_job_id = post_job.post_job_id
        WHERE (student.stu_name LIKE ? OR post_job.title LIKE ?)
        AND report.report_status_id = 1
        LIMIT ?, ?";

$stmt = $conn->prepare($sql);
$search_param = "%$search%";
$stmt->bind_param("ssii", $search_param, $search_param, $offset, $limit);
$stmt->execute();
$result = $stmt->get_result();

$reports = [];
while ($row = $result->fetch_assoc()) {
    $reports[] = $row;
}

// นับจำนวนรีพอร์ตทั้งหมดที่มีสถานะ `pending`
$sql_count = "SELECT COUNT(*) AS total FROM report
              JOIN student ON report.user_id = student.student_id
              JOIN post_job ON report.post_job_id = post_job.post_job_id
              WHERE (student.stu_name LIKE ? OR post_job.title LIKE ?)
              AND report.report_status_id = 1";
$stmt_count = $conn->prepare($sql_count);
$stmt_count->bind_param("ss", $search_param, $search_param);
$stmt_count->execute();
$count_result = $stmt_count->get_result();
$total = $count_result->fetch_assoc()['total'];
$totalPages = ceil($total / $limit);


//รีพอร์ต
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];

    // ดึง post_id ที่ถูกรีพอร์ตออกมาก่อน
    $get_post_sql = "SELECT post_job_id FROM report WHERE report_id = ?";
    $get_post_stmt = $conn->prepare($get_post_sql);
    $get_post_stmt->bind_param("i", $delete_id);
    $get_post_stmt->execute();
    $post_result = $get_post_stmt->get_result();
    
    if ($post_result->num_rows > 0) {
        $post_data = $post_result->fetch_assoc();
        $post_id = $post_data['post_job_id'];

        // อัปเดตสถานะของโพสต์เป็น delete (3)
        $update_post_sql = "UPDATE post_job SET job_status_id = 3 WHERE post_job_id = ?";
        $update_post_stmt = $conn->prepare($update_post_sql);
        $update_post_stmt->bind_param("i", $post_id);
        
        // อัปเดตสถานะของรีพอร์ตเป็น closed (2)
        $update_report_sql = "UPDATE report SET report_status_id = 2 WHERE report_id = ?";
        $update_report_stmt = $conn->prepare($update_report_sql);
        $update_report_stmt->bind_param("i", $delete_id);

        // ตรวจสอบว่าอัปเดตสำเร็จหรือไม่
        if ($update_post_stmt->execute() && $update_report_stmt->execute()) {
            header("Location: reports.php?search=$search&page=$page");
            exit();
        } else {
            echo "<script>alert('Failed to update post and report status.');</script>";
        }
    }
}
// เมื่อรับค่า POST สำหรับการปิดรีพอร์ต
if (isset($_POST['close_id'])) {
    $close_id = $_POST['close_id'];

    // อัปเดตสถานะของรีพอร์ตเป็น closed (2)
    $update_close_sql = "UPDATE report SET report_status_id = 2 WHERE report_id = ?";
    $update_close_stmt = $conn->prepare($update_close_sql);
    $update_close_stmt->bind_param("i", $close_id);

    if ($update_close_stmt->execute()) {
        header("Location: reports.php?search=$search&page=$page");
        exit();
    } else {
        echo "<script>alert('Failed to update post and report status.');</script>";
    }
}




?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.gstatic.com" />
    <link
        href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Montserrat:wght@600&display=swap"
        rel="stylesheet" />

    <!-- External CSS -->
    <link rel="stylesheet" href="css/style_reports.css">
    <link rel="stylesheet" href="style_sidebar.css">
</head>
<body>
    
<div class="containersb">
    <div id="sidebar-container">
    </div>
        <main class="main-contentt">
        <button class="menu-toggle">☰ Menu</button>
            <div class="content">
                <h1>Report Management</h1>
                <hr class="my-3">
                <form method="GET">
                    <div class="search-bar">
                        <input type="text" name="search" placeholder="Search" value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit">Search</button>
                    </div>
                </form>
                <div class="table-wrapper">
    <table aria-label="Reports Table">
        <thead>
            <tr>
                <th scope="col">Reporter</th>
                <th scope="col">Post</th>
                <th scope="col">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($reports) > 0): ?>
                <?php foreach ($reports as $report): ?>
                <tr>
                    <td><?php echo htmlspecialchars($report['reporter_name']); ?></td>
                    <td><?php echo htmlspecialchars($report['title']); ?></td>
                    <td>
                        <div class="actions">
                        <a href="joinustest.php?id=<?php echo htmlspecialchars($report['post_job_id']); ?>" class="view-btn"
                        style="text-decoration: none; padding: 6px 17px; background-color: #007bff; color: white; border-radius: 8px;">View</a>
                        <form method="POST" style="display:inline;">
                                <input type="hidden" name="delete_id" value="<?php echo $report['report_id']; ?>">
                                <button type="submit" class="delete-btn" onclick="return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบโพสต์งานนี้?');">Delete</button>
                        </form>
                        <form method="POST" style="display:inline;">
                                <input type="hidden" name="close_id" value="<?php echo $report['report_id']; ?>">
                                <button type="submit" class="close-btn" onclick="return confirm('คุณแน่ใจหรือไม่ว่าโพสต์งานนี้ไม่ละเมิดกฎ?');">Cancel</button>
                        </form>

                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="3" style="text-align: center;">No pending reports found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>


                <div class="pagination">
                    <span>Showing <?php echo ($offset + 1) . " to " . min($offset + $limit, $total) . " of $total entries"; ?></span>
                    <div>
                        <?php if ($page > 1): ?>
                            <a href="?search=<?php echo $search; ?>&page=<?php echo $page - 1; ?>">Previous</a>
                        <?php endif; ?>
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <a href="?search=<?php echo $search; ?>&page=<?php echo $i; ?>" class="<?php echo ($i == $page) ? 'active' : ''; ?>"><?php echo $i; ?></a>
                        <?php endfor; ?>
                        <?php if ($page < $totalPages): ?>
                            <a href="?search=<?php echo $search; ?>&page=<?php echo $page + 1; ?>">Next</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
           
        </main>
    </div>
    </div>
    <script>
        function viewPost(postId) {
            window.location.href = `joinustest.php?id=${postId}`;
        }
    </script>
     <script src="script_sidebar.js"></script>
     <script src="js/script_reports.js"></script>
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
