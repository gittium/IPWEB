<?php
include 'database.php';

// รับค่า id จาก URL
$job_id = isset($_GET['id']) ? $_GET['id'] : null;
$user_ip = isset($_GET['ip']) ? $_GET['ip'] : null;

if ($job_id) {
    // ดึงข้อมูลรายละเอียดของงานตาม ID
    $sql = "SELECT post_jobs.title, post_jobs.image, post_jobs.description,reward_type.reward_name, job_categories.category_name AS category, teachers.name AS teacher
            FROM post_jobs 
            JOIN teachers ON post_jobs.teacher_id = teachers.id
            JOIN job_categories ON post_jobs.category_id = job_categories.id
            JOIN reward_type ON post_jobs.reward_id = reward_type.id
            WHERE post_jobs.id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $job_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $job = $result->fetch_assoc();
}


// ดึงกฎการรายงานจากฐานข้อมูล
$report_reasons = [];
$sql = "SELECT id, report_category_name FROM report_categories";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $report_reasons[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Join Us Page">
    <title>joinus</title>
    <link rel="stylesheet" href="css/joinus.css">
    <link rel="stylesheet" href="css/header-footerstyle.css">

<body>
    <!-- Header -->
    <header class="headerTop">
        <div class="headerTopImg">
            <img src="logo.png" alt="Naresuan University Logo">
            <a href="#">Naresuan University</a>
        </div>
        <nav class="header-nav">
            <a href="#">About Us</a>
            <a href="#">News</a>
            <a href="#">Profile</a>
        </nav>
    </header>


    <!-- Main Content -->
    <a href="hometest.php" class="back-arrow"></a>
    <div class="container">
        <div class="applicant-card">

            <div class="applicant-photo-joinus">
                <img src="<?php echo htmlspecialchars($job['image']); ?>" alt="Job Image">
            </div>
        </div>

        <div class="title-container">
            <h1 class="section-title"><?php echo htmlspecialchars($job['title']); ?></h1>
        </div>

        <div class="applicant-card">

            <div class="applicant-details">
                <span><?php echo nl2br(htmlspecialchars($job['description'])); ?></span>
            </div>
            <div class="applicant-reward">
                <span>ผลตอบแทน :</span>
                <span><?php echo nl2br(htmlspecialchars($job['reward_name'])); ?></span>
            </div>

            <div class="applicant-details-name">
                <span class="emoji">👤</span>
                <?php echo htmlspecialchars($job['teacher']); ?>
            </div>

            <!-- Button Container -->
            <div class="button-container">
                <button class="report-btn" onclick="showReportModal()">รายงาน</button>
                <a href=""><button class="joinus-btn">Join us</button>
            </div>
        </div>
    </div>

    <!-- Report Modal -->
    <div class="modal" id="reportModal">
        <div class="modal-content">
            <div class="modal-header">
                <span>รายงาน</span>
            </div>
            <div class="modal-body">
                <label>สาเหตุการรายงาน</label>
                <?php foreach ($report_reasons as $reason) : ?>
                    <label><?php echo htmlspecialchars($reason['id']) . " : " . htmlspecialchars($reason['report_category_name']); ?></label>
                <?php endforeach; ?>

                <select id="report-reason">
                    <?php foreach ($report_reasons as $reason) : ?>
                        <option value="<?php echo $reason['id']; ?>">
                            <?php echo "Reason " . " " . htmlspecialchars($reason['id']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="modal-footer">
                <button class="cancel-btn" onclick="closeReportModal()">ยกเลิก</button>
                <button class="confirm-btn" id="submit-report" onclick="submitReport()">ยืนยันการรายงาน</button>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <p>© CSIT - Computer Science and Information Technology</p>
    </footer>

    <script src="js/joinus.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>



</body>

</html>

<?php
$conn->close();
?>