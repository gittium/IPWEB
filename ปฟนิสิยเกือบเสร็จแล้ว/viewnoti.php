<?php
session_start();
include 'database.php'; // เชื่อมต่อกับฐานข้อมูล

// ตรวจสอบว่าได้รับพารามิเตอร์ 'id' จาก URL หรือไม่
if (isset($_GET['id'])) {
    $notification_id = intval($_GET['id']); // ป้องกัน SQL Injection

    // อัปเดตสถานะของการแจ้งเตือนเป็น "read"
    $update_sql = "UPDATE notification SET status = 'read' WHERE notifications_id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("i", $notification_id);

    if ($stmt->execute()) {
        // หากอัปเดตสำเร็จ จะทำการดึงข้อมูลแจ้งเตือนตาม id ที่เลือก
        $select_sql = "SELECT notification.notifications_id, 
                              notification.message, 
                              notification.created_at, 
                              notification.status, 
                              notification.reference_id,  -- reference_id เป็น accepted_app_id
                              accepted_application.job_app_id,  -- ดึง job_app_id จาก accepted_application
                              accepted_application.accept_status_id, 
                              accept_status.accept_status_name 
                       FROM notification 
                       JOIN accepted_application ON notification.reference_id = accepted_application.accepted_app_id 
                       JOIN accept_status ON accepted_application.accept_status_id = accept_status.accept_status_id 
                       WHERE notification.notifications_id = ?";

        $stmt_select = $conn->prepare($select_sql);
        $stmt_select->bind_param("i", $notification_id);
        $stmt_select->execute();
        $result = $stmt_select->get_result();

        if ($result->num_rows > 0) {
            $notification = $result->fetch_assoc();
            // ข้อมูลของแจ้งเตือนที่เลือก
            $title = $notification['accept_status_name'];
            $message = $notification['message'];
            $created_at = $notification['created_at'];

            // ดึง job_app_id จาก accepted_application
            $job_application_job_app_id = $notification['job_app_id'];  // ดึง job_app_id จาก accepted_application
        } else {
            echo "Notification not found.";
            exit();
        }

        $stmt_select->close();
    } else {
        echo "Error updating notification status: " . $conn->error;
        exit();
    }

    $stmt->close();
} else {
    echo "Error: Missing notification id.";
    exit();
}

// ดึงข้อมูลรายละเอียดการสมัครงานตาม job_app_id
if ($job_application_job_app_id) {
    // ดึงข้อมูลการสมัครงานจาก job_application โดยใช้ job_app_id
    $sql = "SELECT post_jobs.title, post_jobs.post_jobs_id, job_application.resume, students.name, students.students_id, 
            major.major_name, students.year, job_application.GPA, students.email, job_application.phone_number
            FROM job_application 
            JOIN post_jobs ON job_application.post_jobs_id = post_jobs.post_jobs_id
            JOIN students ON job_application.students_id = students.students_id
            JOIN major ON students.major_id = major.major_id
            WHERE job_application.job_app_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $job_application_job_app_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $job_application = $result->fetch_assoc();
    } else {
        echo "Job application not found.";
        exit();
    }
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
    <link rel="stylesheet" href="css/viewnoti.css">
    <link rel="stylesheet" href="css/header-footerstyle.css">
    <style>
        .notistatus-btn {
            padding: 10px 20px;
            font-size: 16px;
            border: none;
            border-radius: 5px;
            color: white;
            background-color: #ccc;
            cursor: not-allowed;
            transition: background-color 0.3s ease;
        }

        .notistatus-btn:disabled {
            background-color: #ccc;
            color: #666;
            cursor: not-allowed;
        }

        .notistatus-btn:hover {
            background-color: #ccc;
        }

        /* Style for clickable job title */
        .section-title a {
            text-decoration: none;
            color: inherit;
            transition: color 0.3s ease, text-decoration 0.3s ease;
        }

        .section-title a:hover {
            color: #4E2A84;
            text-decoration: underline;
        }

        .section-title h1 {
            font-size: 1.5rem;
            color: #333;
        }
    </style>
</head>

<body>
    <header class="headerTop">
        <div class="headerTopImg">
            <img src="logo.png" alt="Naresuan University Logo">
            <a href="#">Naresuan University</a>
        </div>
        <nav class="header-nav">
            <?php
            if (isset($_SESSION['user_id'])) {
                echo '<a href="logout.php">Logout</a>';
            } else {
                echo '<a href="login.php">Login</a>';
            }
            ?>
        </nav>
    </header>

    <a href="javascript:window.history.back();" class="back-arrow"></a>
    <div class="container">
        <div class="title-container">
            <h1 class="section-title">
                <?php
                if ($notification['accept_status_id'] == 1) {
                    echo "คุณได้รับการตอบรับเข้าทำงาน<br><a href='joinustest.php?id=" . htmlspecialchars($job_application['post_jobs_id']) . "'>" . htmlspecialchars($job_application['title']) . "</a>";
                } elseif ($notification['accept_status_id'] == 2) {
                    echo "คุณไม่ได้รับการตอบรับเข้าทำงาน<br><a href='joinustest.php?id=" . htmlspecialchars($job_application['post_jobs_id']) . "'>" . htmlspecialchars($job_application['title']) . "</a>";
                } else {
                    echo "<a href='nextfile.php?post_jobs_id=" . htmlspecialchars($job_application['post_jobs_id']) . "'>" . htmlspecialchars($job_application['title']) . "</a>";
                }
                ?>
            </h1>

            <div class="status-form">
                <?php if ($notification['accept_status_id'] == 1): ?>
                    <button class="notistatus-btn" type="button" disabled>Accepted</button>
                <?php elseif ($notification['accept_status_id'] == 2): ?>
                    <button class="notistatus-btn" type="button" disabled>Rejected</button>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($job_application): ?>
            <div class="applicant-card">
                <div class="applicant-photo-name">
                    <div class="applicant-photo">
                        <?php
                        $photoPath = "path/to/student/photo.jpg";
                        if (file_exists($photoPath)) {
                            echo '<img src="' . $photoPath . '" alt="Applicant Photo" class="applicant-photo-img">';
                        } else {
                            echo '<span>' . strtoupper(mb_substr($job_application['name'], 0, 1, 'UTF-8')) . '</span>';
                        }
                        ?>
                    </div>
                </div>

                <div class="details">
                    <label for="resume">Resume / เรซูเม่</label>
                </div>

                <div class="resume" onclick="openFullscreenResume('<?php echo htmlspecialchars($job_application['resume']); ?>')">
                    <?php
                    $resumeFile = $job_application['resume'];
                    $fileType = pathinfo($resumeFile, PATHINFO_EXTENSION);

                    if (!empty($resumeFile) && file_exists($resumeFile)) {
                    ?>
                        <div class="resume-box">
                            <a href="<?php echo htmlspecialchars($resumeFile); ?>" target="_blank" class="resume-link">
                                <div class="resume-content">
                                    <?php
                                    if ($fileType == 'pdf') {
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
                    <span> <?= htmlspecialchars($job_application['name']) ?> </span>

                    <label>Field / สาขา :</label>
                    <span> <?= htmlspecialchars($job_application['major_name']) ?> </span>

                    <label>Year / ชั้นปี :</label>
                    <span> <?= htmlspecialchars($job_application['year']) ?> </span>

                    <label>GPAX / เกรดเฉลี่ย :</label>
                    <span> <?= number_format($job_application['GPA'], 1) ?> </span>
                    <label>E-mail / อีเมล :</label>
                    <span><a href="mailto:<?= htmlspecialchars($job_application['email']) ?>">
                            <?= htmlspecialchars($job_application['email']) ?> </a></span>

                    <label>Phone / เบอร์ติดต่อ :</label>
                    <span> <?= htmlspecialchars($job_application['phone_number']) ?> </span>
                </div>
            </div>
        <?php else: ?>
            <p>No application found.</p>
        <?php endif; ?>
    </div>

    <footer class="footer">
        <p>© CSIT - Computer Science and Information Technology</p>
    </footer>
    <script src="js/fullscreenResume.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
