<?php
$servername = "localhost";
$username = "root"; // แก้เป็น username ของ MySQL
$password = ""; // แก้เป็น password ของ MySQL
$dbname = "ip2"; // แก้เป็นชื่อฐานข้อมูลที่ใช้งาน

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ตรวจสอบว่ามีการคลิก View เพื่อต้องการดูโพสต์ที่ถูกรีพอร์ตหรือไม่
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $post_id = $_GET['id'];

    // ดึงข้อมูลโพสต์ที่ถูกรีพอร์ต
    $sql = "SELECT post_jobs.title, post_jobs.reward_type_id, post_jobs.description, post_jobs.created_at, 
    post_jobs.number_student, post_jobs.job_categories_id, post_jobs.teachers_id, post_jobs.job_status_id, 
    post_jobs.image, teachers.name AS teacher_name, teachers.email, 
    job_categories.categories_name, job_status.job_status_name, reward_type.reward_name
    FROM post_jobs
    JOIN teachers ON post_jobs.teachers_id = teachers.teachers_id
    JOIN job_categories ON post_jobs.job_categories_id = job_categories.job_categories_id
    JOIN job_status ON post_jobs.job_status_id = job_status.job_status_id
    JOIN reward_type ON post_jobs.reward_type_id = reward_type.reward_type_id
    WHERE post_jobs.post_jobs_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        die("Post not found.");
    }

    $post = $result->fetch_assoc();

    // ตั้งค่า BASE_URL สำหรับรูปภาพ
    $base_url = "http://localhost/P5/Admin/"; // ต้องแก้ให้ตรงกับเซิร์ฟเวอร์จริง
    $image_path = !empty($post['image']) ? $base_url . "images/" . htmlspecialchars($post['image']) : $base_url . "images/img1.jpg";
    ?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Detail</title>
     <!-- Google Fonts -->
     <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Montserrat:wght@600&display=swap"
        rel="stylesheet" />

    <!-- External CSS -->
    <link rel="stylesheet" href="style_reports.css">
    <link rel="stylesheet" href="style_sidebar&role.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            padding: 0;
        }
        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .main-content {
            width: 60%;
            background: #fff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .post-image-container {
            display: flex;
            justify-content: center; 
            align-items: center;
            width: auto; max-width: 100%;
            margin-bottom: 50px;
            margin-top:1000px;
            overflow: hidden;
        }
        .post-image {
            height: auto;
            object-fit: cover;
            border-radius: 10px;
        }
        .post-section {
            background: #eef2f7;
            padding: 15px;
            border-radius: 10px;
            text-align: left;
            margin: auto;
            width: 75%;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            margin-bottom: 40px;
        }
        .post-meta {
            width: 60%;
            text-align: left;
            margin-bottom: 40px;
        }
        .post-meta p {
            font-size: 16px;
            color: #333;
            margin: 5px 0;
        }
        .button-container {
    display: flex;
    justify-content: center;
    margin-top: 20px;

        }
        .btn-back {
            display: inline-block;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
            transition: 0.3s;
        }
        .btn-back:hover {
            background: #0056b3;
        }
    </style>

</head>

</html>



    <?php
    exit(); // จบการทำงานหลังจากแสดงโพสต์ที่ถูกรีพอร์ต
}

// รับค่าค้นหา & หน้า
$search = isset($_GET['search']) ? $_GET['search'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 5;
$offset = ($page - 1) * $limit;

// ดึงข้อมูลเฉพาะรีพอร์ตที่มีสถานะ `pending`
$sql = "SELECT reports.reports_id, reports.post_jobs_id, students.name AS reporter_name, post_jobs.title 
        FROM reports 
        JOIN students ON reports.user_id = students.students_id
        JOIN post_jobs ON reports.post_jobs_id = post_jobs.post_jobs_id
        WHERE (students.name LIKE ? OR post_jobs.title LIKE ?)
        AND reports.report_status_id = 1
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
$sql_count = "SELECT COUNT(*) AS total FROM reports 
              JOIN students ON reports.user_id = students.students_id
              JOIN post_jobs ON reports.post_jobs_id = post_jobs.post_jobs_id
              WHERE (students.name LIKE ? OR post_jobs.title LIKE ?)
              AND reports.report_status_id = 1";
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
    $get_post_sql = "SELECT post_jobs_id FROM reports WHERE reports_id = ?";
    $get_post_stmt = $conn->prepare($get_post_sql);
    $get_post_stmt->bind_param("i", $delete_id);
    $get_post_stmt->execute();
    $post_result = $get_post_stmt->get_result();
    
    if ($post_result->num_rows > 0) {
        $post_data = $post_result->fetch_assoc();
        $post_id = $post_data['post_jobs_id'];

        // อัปเดตสถานะของโพสต์เป็น `delete` (3)
        $update_post_sql = "UPDATE post_jobs SET job_status_id = 3 WHERE post_jobs_id = ?";
        $update_post_stmt = $conn->prepare($update_post_sql);
        $update_post_stmt->bind_param("i", $post_id);
        
        // อัปเดตสถานะของรีพอร์ตเป็น `closed` (2)
        $update_report_sql = "UPDATE reports SET report_status_id = 2 WHERE reports_id = ?";
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

?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.gstatic.com" />
    <link
        href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Montserrat:wght@600&display=swap"
        rel="stylesheet" />

    <!-- External CSS -->
    <link rel="stylesheet" href="css/style_reports.css">
    <link rel="stylesheet" href="style_sidebar&role.css">
</head>
<body>
    <div class="container">
        <div id="sidebar-container"></div>

        <main class="main-contentt">
            <div class="content">
                <h1>Reports Management</h1>
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
                        <a href="joinustest.php?id=<?php echo htmlspecialchars($report['post_jobs_id']); ?>" class="view-btn">View</a>
        <form method="POST" style="display:inline;">
                                <input type="hidden" name="delete_id" value="<?php echo $report['reports_id']; ?>">
                                <button type="submit" class="delete-btn" onclick="return confirm('Are you sure you want to process this report?');">Delete</button>
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

    <script>
        function viewPost(postId) {
            window.location.href = `joinustest.php?id=${postId}`;
        }
    </script>
     <script src="script_sidebar&role.js"></script>
     <script src="js/script_reports.js"></script>
</body>
</html>
