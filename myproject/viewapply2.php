<?php
session_start();
include 'database.php'; // เชื่อมต่อกับฐานข้อมูล

// รับค่า id และ IP จาก URL
$job_application_job_application_id = isset($_GET['job_application_id']) ? $_GET['job_application_id'] : null;

$job_application = null;
if ($job_application_job_application_id) {  
    // ดึงข้อมูลรายละเอียดของงานตาม ID
    $sql = "SELECT post_job.title, post_job.post_job_id, student.profile, job_application.resume, student.stu_name, student.student_id, 
            major.major_name, student.year, job_application.GPA, student.stu_email, job_application.stu_phone_number
            FROM job_application 
            JOIN post_job ON job_application.post_job_id = post_job.post_job_id
            JOIN student ON job_application.student_id = student.student_id
            JOIN major ON student.major_id = major.major_id
            WHERE job_application.job_application_id = ?";

    $stmt = $conn->prepare($sql); // เตรียมคำสั่ง SQL
    $stmt->bind_param("i", $job_application_job_application_id); // ผูกค่าพารามิเตอร์
    $stmt->execute(); // ประมวลผลคำสั่ง SQL
    $result = $stmt->get_result();
    $job_application = $result->fetch_assoc(); // ดึงข้อมูลเป็น array
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="View Applications Page">
    <title>View Applications</title>
    <link rel="stylesheet" href="css/viewapply2.css">
    <link rel="stylesheet" href="css/header-footerstyle.css">
</head>

<body>
    <!-- Header ส่วนหัวของเว็บไซต์ -->
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

    <!-- Main Content ส่วนแสดงรายละเอียดของผู้สมัคร -->
    <a href="javascript:window.history.back();" class="back-arrow"></a>
    <div class="container">
        <div class="title-container">
            <h1 class="section-title"><?php echo htmlspecialchars($job_application['title']); ?></h1>
        </div>

        <?php if ($job_application): ?>
        <!-- Applicant Card การ์ดแสดงรายละเอียดของผู้สมัคร -->
        <div class="applicant-card">
            <a href="profilestapplication.php?student_id=<?= htmlspecialchars($job_application['student_id']) ?>"
                class="photo-link">
                <div class="applicant-photo-name">
                    <div class="applicant-photo">

                        <img class="applicant-photo-img" id="applicant-photo-img"
                            src="<?php echo htmlspecialchars($job_application['profile']); ?>" alt="Applicant Photo"
                            style="cursor: default;">
                        <input type="file" id="Applicant Photo" style="display:none;" accept="image/*">

                    </div>
                </div>
            </a>

            <div class="details">
                <label for="resume">Resume / เรซูเม่</label>
            </div>

            <!-- แก้ไขส่วนนี้เพื่อเรียกใช้ฟังก์ชัน openFullscreenResume -->
            <div class="resume"
                onclick="openFullscreenResume('<?php echo htmlspecialchars($job_application['resume']); ?>')">

                <!-- ตรวจสอบว่า resume มีอยู่จริง -->
                <?php
                $resumeFile = $job_application['resume']; // ดึงพาธไฟล์จากฐานข้อมูล
                $fileType = pathinfo($resumeFile, PATHINFO_EXTENSION); // ตรวจสอบประเภทไฟล์

                if (!empty($resumeFile) && file_exists($resumeFile)) {
                ?>
                <div class="resume-box">
                    <a href="<?php echo htmlspecialchars($resumeFile); ?>" target="_blank" class="resume-link">
                        <div class="resume-content">
                            <?php 
                            if ($fileType == 'pdf') {
                                // แสดงเป็นไอคอนหรือข้อความถ้าเป็น PDF
                                echo '<p>📄 คลิกเพื่อเปิดเรซูเม่ (PDF)</p>';
                            }
                            ?>
                        </div>
                    </a>
                </div>
                <?php 
                } else {
                    echo '<p class="text-warning">❌ ไม่พบไฟล์เรซูเม่!</p>';
                }
                ?>
            </div>


            <div class="details">
                <label>Name / ชื่อ :</label>
                <span> <?= htmlspecialchars($job_application['stu_name']) ?> </span>

                <label>Field / สาขา :</label>
                <span> <?= htmlspecialchars($job_application['major_name']) ?> </span>

                <label>Year / ชั้นปี :</label>
                <span> <?= htmlspecialchars($job_application['year']) ?> </span>

                <label>GPAX / เกรดเฉลี่ย :</label>
                <span> <?= number_format($job_application['GPA'], 1) ?> </span>
                <label>E-mail / อีเมล :</label>
                <span><a href="mailto:<?= htmlspecialchars($job_application['stu_email']) ?>">
                        <?= htmlspecialchars($job_application['stu_email']) ?> </a></span>

                <label>Phone / เบอร์ติดต่อ :</label>
                <span> <?= htmlspecialchars($job_application['stu_phone_number']) ?> </span>
            </div>

            <div id="message-container"></div>
            <form method="POST" action="approve_application.php">
                <input type="hidden" name="id" value="<?= htmlspecialchars($job_application_job_application_id) ?>">
                <input type="hidden" name="student_id" value="<?= htmlspecialchars($job_application['student_id']) ?>">
                <input type="hidden" name="post_id" value="<?= htmlspecialchars($job_application['post_job_id']) ?>">

                <!-- ปุ่ม Reject -->
                <button type="button" class="reject-btn" id="reject-btn"
                    data-application-id="<?= htmlspecialchars($job_application_job_application_id) ?>"
                    data-action="reject">Reject</button>

                <!-- ปุ่ม Approve -->
                <button type="button" class="approve-btn" id="approve-btn"
                    data-application-id="<?= htmlspecialchars($job_application_job_application_id) ?>"
                    data-action="approve">Approve</button>
            </form>

        </div>
        <?php else: ?>
        <p>No application found.</p>
        <?php endif; ?>
    </div>

    <!-- Footer ส่วนท้ายของเว็บไซต์ -->
    <footer class="footer">
        <p>© CSIT - Computer Science and Information Technology</p>
    </footer>

    <script src="js/fullscreenResume.js"></script>
    <script src="js/approve.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>