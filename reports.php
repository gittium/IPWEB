<?php
$servername = "localhost";
$username = "root"; // แก้เป็น username ของ MySQL
$password = ""; // แก้เป็น password ของ MySQL
$dbname = "ip"; // แก้เป็นชื่อฐานข้อมูลที่ใช้งาน

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ตรวจสอบว่ามีการคลิก View เพื่อต้องการดูโพสต์ที่ถูกรีพอร์ตหรือไม่
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $post_id = $_GET['id'];

    // ดึงข้อมูลโพสต์ที่ถูกรีพอร์ต
    $sql = "SELECT post_jobs.title, post_jobs.description, teachers.name AS teacher_name, teachers.email 
            FROM post_jobs
            JOIN teachers ON post_jobs.teacher_id = teachers.id
            WHERE post_jobs.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        die("Post not found.");
    }

    $post = $result->fetch_assoc();
    ?>

    <!DOCTYPE html>
    <html lang="th">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Post Detail</title>
        <link rel="stylesheet" href="style_reports.css">
        <link rel="stylesheet" href="style_sidebar&role.css">
    </head>
    <body>
        <div class="container">
            <div id="sidebar-container"></div>

            <main class="main-contentt">
                <div class="content">
                    <h1><?php echo htmlspecialchars($post['title']); ?></h1>
                    <p><strong>อาจารย์ผู้โพสต์:</strong> <?php echo htmlspecialchars($post['teacher_name']); ?> (<?php echo htmlspecialchars($post['email']); ?>)</p>
                    <hr>
                    <p><?php echo nl2br(htmlspecialchars($post['description'])); ?></p>

                    <a href="reports.php" class="btn-back">Back to Reports</a>
                </div>
            </main>
        </div>
    </body>
    </html>

    <?php
    exit(); // จบการทำงานหลังจากแสดงโพสต์ที่ถูกรีพอร์ต
}

// รับค่าค้นหา & หน้า
$search = isset($_GET['search']) ? $_GET['search'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 5;
$offset = ($page - 1) * $limit;

// ดึงข้อมูลรีพอร์ต
$sql = "SELECT reports.id, reports.post_id, students.name AS reporter_name, post_jobs.title 
        FROM reports 
        JOIN students ON reports.reporter_id = students.id
        JOIN post_jobs ON reports.post_id = post_jobs.id
        WHERE students.name LIKE ? OR post_jobs.title LIKE ?
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

// นับจำนวนรีพอร์ตทั้งหมด
$sql_count = "SELECT COUNT(*) AS total FROM reports 
              JOIN students ON reports.reporter_id = students.id
              JOIN post_jobs ON reports.post_id = post_jobs.id
              WHERE students.name LIKE ? OR post_jobs.title LIKE ?";
$stmt_count = $conn->prepare($sql_count);
$stmt_count->bind_param("ss", $search_param, $search_param);
$stmt_count->execute();
$count_result = $stmt_count->get_result();
$total = $count_result->fetch_assoc()['total'];
$totalPages = ceil($total / $limit);

// ลบรีพอร์ต
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];
    $delete_sql = "DELETE FROM reports WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $delete_id);
    if ($delete_stmt->execute()) {
        header("Location: reports.php?search=$search&page=$page");
        exit();
    } else {
        echo "<script>alert('Failed to delete report.');</script>";
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
    <link rel="stylesheet" href="style_reports.css">
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
                            <?php foreach ($reports as $report): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($report['reporter_name']); ?></td>
                                <td><?php echo htmlspecialchars($report['title']); ?></td>
                                <td>
                                    <div class="actions">
                                        <button class="view-btn" onclick="viewPost(<?php echo $report['post_id']; ?>)">View</button>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="delete_id" value="<?php echo $report['id']; ?>">
                                            <button type="submit" class="delete-btn" onclick="return confirm('Are you sure you want to delete this report?');">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
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
            window.location.href = `reports.php?id=${postId}`;
        }
    </script>
     <script src="script_sidebar&role.js"></script>
     <script src="script_reports.js"></script>
</body>
</html>
