<?php

include 'database.php';
$user_id = isset($_GET['teachers_id']) ? htmlspecialchars($_GET['teachers_id'], ENT_QUOTES, 'UTF-8') : '';

if (empty($user_id)) {
    die("Error: ค่า teachers_id ไม่ถูกต้อง");
}

// --3.2 ดึงข้อมูลอาจารย์ (Contact)
$sqlTeacher = "SELECT 
                  t.teachers_id,
                  t.name,
                  t.email,
                  t.major_id,
                  t.phone_number,
                  m.major_name
               FROM teachers t
               JOIN major m ON t.major_id = m.major_id
               WHERE t.teachers_id = ?";
$stmtT = $conn->prepare($sqlTeacher);
$stmtT->bind_param("s", $user_id); // เปลี่ยนจาก "i" เป็น "s" เพราะเป็น VARCHAR
$stmtT->execute();
$resT = $stmtT->get_result();
$teacher = $resT->fetch_assoc();

if (!$teacher) {
    die("Error: ไม่พบข้อมูลอาจารย์ teachers_id = " . $user_id);
}

// --3.4 ดึง job ของอาจารย์ (post_jobs)
$sqlJobs = "SELECT * 
            FROM post_jobs
            WHERE teachers_id = ?  
            ORDER BY created_at DESC";
$stmtJ = $conn->prepare($sqlJobs);
$stmtJ->bind_param("s", $user_id);  // ✅ ใช้ "i" สำหรับ INT

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
    <title>Teacher Profile</title>

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
        crossorigin="anonymous">
    <!-- ไฟล์ CSS ของคุณ -->
    <link rel="stylesheet" href="css/teacher_profilestyle.css">
</head>

<body>
    <div class="profile-container">
        <!-- โปรไฟล์ส่วนบน -->
        <div class="header">
            <a href="manage_users.php"><i class="bi bi-chevron-left text-white h4 "></i></a>
            <div class="profile">
                <div class="profile-pic">
                    <?php echo strtoupper(mb_substr($teacher['name'], 0, 1, 'UTF-8')); ?>
                </div>
                <div class="detail-name">
                    <div class="name">
                        <?php echo htmlspecialchars($teacher['name'], ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                    <div class="sub-title">
                        อาจารย์ภาควิชา <br> <?php echo htmlspecialchars($teacher['major_name'], ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Header Profile -->

        <!-- Content -->
        <div class="content">
            <div class="detail-head">
                <div class="review">
                    <div class="review-detail">
                        <!-- ถ้าจะแสดงคะแนน/รีวิว -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact -->
        <div class="container">
            <h3>Contact</h3>
            <section class="Contact">
                <!-- โหมดแสดง -->
                <div id="contact_display">
                    <p>เบอร์โทร : <?php echo htmlspecialchars($teacher['phone_number'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <p>อีเมล : <?php echo htmlspecialchars($teacher['email'], ENT_QUOTES, 'UTF-8'); ?></p>
                </div>

                <!-- โหมดแก้ไข -->
                <div id="contact_edit" style="display:none;">
                    <label for="phone_number_input">เบอร์โทร :</label>
                    <input type="text" id="phone_number_input"
                        value="<?php echo htmlspecialchars($teacher['phone_number'], ENT_QUOTES, 'UTF-8'); ?>">

                    <br><br>
                    <label for="email_input">อีเมล :</label>
                    <input type="email" id="email_input"
                        value="<?php echo htmlspecialchars($teacher['email'], ENT_QUOTES, 'UTF-8'); ?>">
                </div>
            </section>
        </div>
        <!-- ปุ่ม Save อยู่ด้านล่าง job -->
        <div class="container">
            <div class="content">
                <div class="grid" id="job_container">
                    <?php foreach ($jobs as $job) { ?>
                        <div class="card" id="<?php echo $job['post_jobs_id']; ?>">

                            <!-- โหมดแสดง -->
                            <div class="job_display" id="job_display_<?php echo $job['post_jobs_id']; ?>">
                                <div class="card-top">
                                    <!-- ตรวจสอบว่า URL ของรูปภาพถูกต้อง -->
                                    <img src="<?php echo htmlspecialchars($job['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="Job Image" class="job-image">
                                </div>
                                <div class="card-body">
                                    <!-- แสดง title -->
                                    <h3><?php echo htmlspecialchars($job['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                    <p class="job-description">
                                        <?php
                                        $description = htmlspecialchars($job['description'], ENT_QUOTES, 'UTF-8');
                                        // แสดงรายละเอียด ถ้าคำอธิบายยาวกว่า 100 ตัวอักษร จะแสดงแบบย่อ
                                        echo (strlen($description) > 100) ? substr($description, 0, 95) . '...' : $description;
                                        ?>
                                        <span class="full-description" style="display:none;"><?php echo $description; ?></span>

                                    </p>
                                    <!-- แสดงจำนวนผู้สมัคร -->
                                    <p><strong>รับจำนวน:</strong> <?php echo htmlspecialchars($job['number_student'], ENT_QUOTES, 'UTF-8'); ?> คน</p>
                                    <!-- แสดงวันที่ประกาศ -->
                                    <p><strong>ประกาศเมื่อ:</strong> <?php echo htmlspecialchars($job['created_at'], ENT_QUOTES, 'UTF-8'); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
</body>

</html>

<?php $conn->close(); ?>