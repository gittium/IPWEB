<?php
session_start();
include 'database.php'; // เชื่อมต่อกับฐานข้อมูล

// ตรวจสอบว่าได้รับพารามิเตอร์ 'id' จาก URL หรือไม่
if (isset($_GET['id'])) {
    $notification_id = intval($_GET['id']); // ป้องกัน SQL Injection

    // อัปเดตสถานะของการแจ้งเตือนเป็น "read"
    $update_sql = "UPDATE notification SET status = 'read' WHERE notification_id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("i", $notification_id);

    if ($stmt->execute()) {
        // หากอัปเดตสำเร็จ จะทำการดึงข้อมูลแจ้งเตือนตาม id ที่เลือก
        $select_sql = "SELECT notification.notification_id, 
                              notification.message, 
                              notification.created_at, 
                              notification.status, 
                              notification.reference_id,  -- reference_id เป็น accepted_app_id
                              accepted_application.job_application_id,  -- ดึง job_app_id จาก accepted_application
                              accepted_application.accept_status_id, 
                              accept_status.accept_status_name 
                       FROM notification 
                       JOIN accepted_application ON notification.reference_id = accepted_application.accepted_application_id 
                       JOIN accept_status ON accepted_application.accept_status_id = accept_status.accept_status_id 
                       WHERE notification.notification_id = ?";

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
            $job_application_id = $notification['job_application_id'];  // ดึง job_app_id จาก accepted_application
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
if ($job_application_id) {
    // ดึงข้อมูลการสมัครงานจาก job_application โดยใช้ job_app_id
    $sql = "SELECT post_job.title, post_job.post_job_id, job_application.resume, student.stu_name,student.profile , student.student_id, 
            major.major_name, student.year, job_application.GPA, student.stu_email, job_application.stu_phone_number
            FROM job_application 
            JOIN post_job ON job_application.post_job_id = post_job.post_job_id
            JOIN student ON job_application.student_id = student.student_id
            JOIN major ON student.major_id = major.major_id
            WHERE job_application.job_application_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $job_application_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $job_application = $result->fetch_assoc();
    } else {
        echo $job_application_id . "Job application not found.";
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

        .applicant-photo-name {
            width: auto;
            /* กำหนดความกว้างเท่าเดิม */
            height: 300px;
            /* กำหนดความสูงเท่าเดิม */
            background-color: transparent;
            /* ไม่ต้องมีพื้นหลัง */
            margin: 0px 0;
            /* ระยะห่างเท่าเดิม */
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            /* ลบ border และ box-shadow */
        }

        .resume {
            width: 50%;
            /* ใช้พื้นที่เต็มความกว้างของคอนเทนเนอร์ */
            max-width: 600px;
            /* กำหนดความกว้างสูงสุด */
            min-height: 100px;
            /* ลดความสูงขั้นต่ำ */
            background-color: #f0f0f0;
            /* เปลี่ยนสีพื้นหลังให้อ่อนลง */
            margin: 20px 0;
            /* เพิ่มระยะห่างด้านบนและล่าง */
            display: flex;
            justify-content: center;
            /* จัดเนื้อหาให้อยู่ตรงกลางแนวนอน */
            align-items: center;
            /* จัดเนื้อหาให้อยู่ตรงกลางแนวตั้ง */
            border-radius: 5px;
            /* ทำมุมให้โค้ง */
            padding: 10px;
            /* เพิ่ม padding ภายใน */
            overflow: hidden;
            /* ซ่อนส่วนเกินของเนื้อหา */
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
            /* เปลี่ยนเคอร์เซอร์เมื่อโฮเวอร์ */
            /* box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);  เพิ่มเงาให้ดูมีมิติ */
        }

        .resume img-fluid {
            max-width: 60%;
            /* ปรับขนาดรูปภาพให้พอดีกับกรอบ */
            max-height: 600px;
            /* ป้องกันไม่ให้รูปภาพล้นกรอบ */
            border-radius: 5px;
            /* ทำมุมของรูปภาพให้โค้ง */
            object-fit: contain;
            /* ปรับรูปภาพให้ขนาดพอดีกับกรอบ */
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
                    echo "คุณได้รับการตอบรับเข้าทำงาน<br><a href='joinustest.php?id=" . htmlspecialchars($job_application['post_job_id']) . "'>" . htmlspecialchars($job_application['title']) . "</a>";
                } elseif ($notification['accept_status_id'] == 2) {
                    echo "คุณไม่ได้รับการตอบรับเข้าทำงาน<br><a href='joinustest.php?id=" . htmlspecialchars($job_application['post_job_id']) . "'>" . htmlspecialchars($job_application['title']) . "</a>";
                } else {
                    echo "<a href='nextfile.php?post_jobs_id=" . htmlspecialchars($job_application['post_job_id']) . "'>" . htmlspecialchars($job_application['title']) . "</a>";
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
                        echo '<img src="' . $job_application['profile'] . '" alt="Applicant Photo" class="applicant-photo-img">';
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