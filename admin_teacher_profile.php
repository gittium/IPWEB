<?php
include 'database.php';

// รับค่า teacher_id จาก URL
$teacher_id = isset($_GET['teacher_id']) ? $_GET['teacher_id'] : '';

// ตรวจสอบว่า teacher_id ไม่เป็นค่าว่าง
if (empty($teacher_id)) {
    echo "Invalid teacher ID."; // แสดงข้อความผิดพลาด
    exit();
}
// ตรวจสอบว่ามีการส่ง POST หรือไม่
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. หากมีการส่งไฟล์รูป (Profile Image Upload)
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../profile/'; // โฟลเดอร์เป้าหมาย //รูปโปรไฟล์ตรงนี้มีน
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileTmpPath = $_FILES['profile_image']['tmp_name'];
        $fileName = basename($_FILES['profile_image']['name']);
        $fileNameNew = uniqid('profile_', true) . "_" . $fileName;
        $fileDest = $uploadDir . $fileNameNew;

        if (move_uploaded_file($fileTmpPath, $fileDest)) {
            $sqlUpdateProfile = "UPDATE teacher SET profile = ? WHERE teacher_id = ?";
            $stmtProfile = $conn->prepare($sqlUpdateProfile);
            $stmtProfile->bind_param("ss", $fileDest, $user_id);
            if ($stmtProfile->execute()) {
                echo "success";
            } else {
                echo "db_error";
            }
            $stmtProfile->close();
        } else {
            echo "upload_failed";
        }
        $conn->close();
        exit();
    }
    // 2. หากไม่มีการส่งไฟล์ ให้ถือว่าเป็นการอัปเดตข้อมูลติดต่อ (Contact Update)
    else {
        $phone_number = $_POST['phone_number'] ?? '';
        $email = $_POST['email'] ?? '';
        $sqlTeacher = "UPDATE teacher SET teach_phone_number = ?, teach_email = ? WHERE teacher_id = ?";
        $stmtT = $conn->prepare($sqlTeacher);
        $stmtT->bind_param("sss", $phone_number, $email, $user_id);
        if (!$stmtT->execute()) {
            echo "error_teachers";
            exit();
        }
        $stmtT->close();
        $conn->close();
        echo "success";
        exit();
    }
}


// ดึงข้อมูลอาจารย์
$sqlTeacher = "SELECT 
                  t.teacher_id,
                  t.teach_name,
                  t.teach_email,
                  t.profile,
                  t.major_id,
                  t.teach_phone_number,
                  m.major_name
               FROM teacher t
               JOIN major m ON t.major_id = m.major_id
               WHERE t.teacher_id = ?";
$stmtT = $conn->prepare($sqlTeacher);
$stmtT->bind_param("s", $teacher_id); // ใช้ integer สำหรับ teacher_id
$stmtT->execute();
$resT = $stmtT->get_result();
$teacher = $resT->fetch_assoc();
$stmtT->close();

// ดึงข้อมูลงานที่โพสต์โดยอาจารย์
$sqlJobs = "SELECT * FROM post_job WHERE teacher_id = ? ORDER BY created_at DESC";
$stmtJ = $conn->prepare($sqlJobs);
$stmtJ->bind_param("s", $teacher_id);  // ใช้ teacher_id ในการดึงข้อมูล
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
    <!-- CSS ของคุณ -->
    <link rel="stylesheet" href="css/header-footerstyle.css">
    <link rel="stylesheet" href="css/teacherprofilestyle.css">
    <style>
        .container-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 90px;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            justify-items: start;
        }

        .card {
            background-color: #FFFFFF;
            padding: 10px;
            text-align: left;
            border: 1px solid #D1D5DB;
            border-radius: 16px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            width: 100%;
            /* ให้ card ใช้พื้นที่เต็มคอลัมน์ที่ได้ */
            max-width: 300px;
            /* กำหนดความกว้างสูงสุดให้เท่ากัน */
        }

        .card-top {
            background-color: #E5E7EB;
            height: 200px;
            border-top-left-radius: 16px;
            border-top-right-radius: 16px;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            /* ป้องกันเนื้อหาล้นออกมา */
        }

        .job-filter {
            margin: 20px 0;
            text-align: center;
        }

        .job-filter .filter-btn {
            margin: 0 5px;
            padding: 10px 20px;
            background-color: #f1f1f1;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .job-filter .filter-btn:hover {
            background-color: #ddd;
        }

        .job-filter .filter-btn.active {
            background-color: #FF7C00;
            color: #fff;
        }

        /* จุดแดงงงงงงงงงงงงงงงงงงงงงงงงงงงงงงงงงงงงงงงงงงงงงงงงงงงงงงงง */
        .unread-dot {
            display: inline-block;
            width: 10px;
            height: 10px;
            background-color: red;
            border-radius: 50%;
            margin-left: 10px;
        }
    </style>
</head>

<body>
    <div class="profile-container">
        <!-- Profile Header -->
        <div class="header">
            <a href="Home_page.php"><i class="bi bi-chevron-left text-white h4"></i></a>
            <div class="profile">
                <img class="profile-pic"
                    id="profile_picture"
                    src="<?php  echo "../" . htmlspecialchars($teacher['profile']); //รูปโปรไฟล์ตรงนี้มีน ?>"
                    alt="Profile Picture"
                    style="cursor: pointer;"
                    onclick="handleProfileClick();">

                <!-- input file แบบซ่อน -->
                <input type="file" id="profile_image_input" style="display:none;" accept="image/*">
                <div class="detail-name">
                    <div class="name"><?php echo $teacher['teach_name']; ?></div>
                    <div class="sub-title">
                        อาจารย์ภาควิชา <br>
                        <?php echo htmlspecialchars($teacher['major_name'], ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Profile Header -->

        <!-- Contact Section -->
        <div class="container-content">
            <div class="container">
                <h3>Contact</h3>
                <section class="Contact">
                    <!-- Display Mode -->
                    <div id="contact_display">
                        <p>เบอร์โทร : <?php echo htmlspecialchars($teacher['teach_phone_number'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <p>อีเมล : <?php echo htmlspecialchars($teacher['teach_email'], ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                </section>
            </div>
            <!-- Job Section -->
            <div class="container">
                <div class="menu-review">
                    <h3>Job</h3>
                </div>
                <div class="content">
                    <!-- ตัวกรองงาน -->
                    <div class="job-filter">
                        <button class="filter-btn active" data-filter="all">ทั้งหมด</button>
                        <button class="filter-btn" data-filter="1">เปิด</button>
                        <button class="filter-btn" data-filter="4">เต็ม</button>
                        <button class="filter-btn" data-filter="2">ปิด</button>
                    </div>


                    <!-- ส่วนแสดงงาน -->
                    <div class="grid" id="job_container">
                        <?php foreach ($jobs as $job) { ?>
                            <?php
                            // job_status_id = 2 หมายถึง "ปิด"
                            $link = ($job['job_status_id'] == 2)
                                ? "viewclosejob.php?post_job_id=" . $job['post_job_id']
                                : "viewapply.php?post_job_id=" . $job['post_job_id'];
                            ?>
                            <!-- สมมุติว่าในฐานข้อมูลงานมีคอลัมน์ 'status' ที่เก็บสถานะงาน -->
                            <div class="card" id="<?php echo $job['post_job_id']; ?>" data-status="<?php echo $job['job_status_id']; ?>">
                                <div class="job_display" id="job_display_<?php echo $job['post_job_id']; ?>">
                                    <div class="card-top">
                                        <img src="<?php echo "../" . htmlspecialchars($job['image'], ENT_QUOTES, 'UTF-8'); //รูปงานตรงนี้มีน?>" alt="Job Image" class="job-image">
                                    </div>
                                    <div class="card-body">
                                        <h3><?php echo htmlspecialchars($job['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                        <p class="job-description">
                                            <?php
                                            $description = htmlspecialchars($job['description'], ENT_QUOTES, 'UTF-8');
                                            echo (strlen($description) > 100) ? substr($description, 0, 95) . '...' : $description;
                                            ?>
                                        </p>
                                        <p><strong>รับจำนวน:</strong> <?php echo htmlspecialchars($job['number_student'], ENT_QUOTES, 'UTF-8'); ?> คน</p>
                                        <p><strong>ประกาศเมื่อ:</strong> <?php echo htmlspecialchars($job['created_at'], ENT_QUOTES, 'UTF-8'); ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <!-- Footer -->
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const filterButtons = document.querySelectorAll('.job-filter .filter-btn');

            filterButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    // ลบ active class ออกจากทุกปุ่ม
                    filterButtons.forEach(b => b.classList.remove('active'));
                    // เพิ่ม active ให้กับปุ่มที่ถูกคลิก
                    this.classList.add('active');

                    const filter = this.getAttribute('data-filter');
                    const jobCards = document.querySelectorAll("#job_container .card");
                    jobCards.forEach(card => {
                        if (filter === "all") {
                            card.style.display = "block";
                        } else {
                            const status = card.getAttribute("data-status");
                            card.style.display = (status === filter) ? "block" : "none";
                        }
                    });
                });
            });
        });
    </script>
</body>

</html>
<?php $conn->close(); ?>