<?php
include 'database.php';
session_start();
$_SESSION['teachers_id'] = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'CSIT0131';
$job_id = isset($_GET['post_jobs_id']) ? intval($_GET['post_jobs_id']) : 3;

// ตรวจสอบว่า job_id มีค่าและเป็นตัวเลขที่ถูกต้อง
if ($job_id) {
    // Query หรืออื่นๆ เพื่อดึงข้อมูลที่ใช้ job_id
    $sql = "SELECT post_jobs.title, post_jobs.image, post_jobs.description, post_jobs.job_categories_id, post_jobs.job_status_id, 
                   post_jobs.number_student, post_jobs.created_at, post_jobs.reward_type_id, post_jobs.skills, post_jobs.job_start, post_jobs.job_end,
                   post_jobs.job_sub_id,
                   reward_type.reward_name, job_categories.categories_name AS categories, teachers.name AS teacher
            FROM post_jobs 
            LEFT JOIN job_categories ON post_jobs.job_categories_id = job_categories.job_categories_id
            LEFT JOIN reward_type ON post_jobs.reward_type_id = reward_type.reward_type_id
            LEFT JOIN teachers ON post_jobs.teachers_id = teachers.teachers_id
            LEFT JOIN skills ON post_jobs.skills = skills.skills_id
            LEFT JOIN job_subcategories ON post_jobs.job_sub_id = job_subcategories.job_sub_id
            WHERE post_jobs.post_jobs_id = ?";

    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $job_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $job = $result->fetch_assoc();
        } else {
            echo "Job not found!";
            exit();
        }

        $stmt->close(); // ปิด statement หลังจากใช้งานเสร็จ
    } else {
        echo "Database query failed!";
        exit();
    }
} else {
    echo "Invalid Job ID!";
    exit();
}
//skill
// ดึงข้อมูลประเภทงานจากฐานข้อมูล
$sql = "SELECT skills_id, skills_name FROM skills ORDER BY skills_id";
$result = $conn->query($sql);
$skills = [];
while ($row = $result->fetch_assoc()) {
    $skills[] = $row;
}
// ดึงข้อมูลประเภทงานจากฐานข้อมูล
$sql = "SELECT job_categories_id, categories_name FROM job_categories ORDER BY job_categories_id";
$result = $conn->query($sql);
$categories = [];
while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
}

// ดึงข้อมูลประเภทผลตอบแทนจากฐานข้อมูล
$sql = "SELECT reward_type_id, reward_name FROM reward_type ORDER BY reward_type_id";
$result = $conn->query($sql);
$reward = []; // 
while ($row = $result->fetch_assoc()) {
    $reward[] = $row;
}

// ดึงข้อมูลเหตุผลจากฐานข้อมูล
$sql = "SELECT close_detail_id, close_detail_name FROM close_detail ORDER BY close_detail_id";
$result = $conn->query($sql);
$close_detali = []; // 
while ($row = $result->fetch_assoc()) {
    $close_detail[] = $row;
}




if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['done'])) {
    date_default_timezone_set('Asia/Bangkok');

    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $skills = isset($_POST['skills']) ? implode(",", $_POST['skills']) : "";
    $start = !empty($_POST['job_start']) ? $_POST['job_start'] : date('Y-m-d H:i:s');
    $end = !empty($_POST['job_end']) ? $_POST['job_end'] : date('Y-m-d H:i:s');
    $category_id = isset($_POST['job_categories_id']) ? intval($_POST['job_categories_id']) : null;
    $reward_type_id = isset($_POST['reward_type_id']) ? intval($_POST['reward_type_id']) : 1;
    $sub_id = !empty($_POST['job_sub_id']) ? $_POST['job_sub_id'] : null;
    $timeandwage = !empty($_POST['timeandwage']) ? $_POST['timeandwage'] : null;
    $number_student = isset($_POST['number_student']) ? intval($_POST['number_student']) : 1;
    $images = !empty($_POST['image']) ? $_POST['image'] : ($job['image'] ?? null);
    $teachers_id = $_SESSION['teachers_id'];

    $job_status_id = 1; // ตั้งให้เป็น 1 (เปิด) เมื่อกดปุ่ม Done

    // ✅ ตรวจสอบว่า job_status_id มีอยู่ในฐานข้อมูลจริง
    $checkStatusQuery = "SELECT job_status_id FROM job_status WHERE job_status_id = ?";
    $checkStmt = $conn->prepare($checkStatusQuery);
    $checkStmt->bind_param("i", $job_status_id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows === 0) {
        die("Error: job_status_id is invalid!");
    }
    $checkStmt->close();

    // ✅ UPDATE ข้อมูลงาน
    $sql = "UPDATE post_jobs SET 
            title = ?, 
            reward_type_id = ?, 
            description = ?, 
            skills = ?,
            job_start = ?,
            job_end = ?,
            number_student = ?,
            timeandwage = ?,
            job_categories_id = ?, 
            job_sub_id = ?,
            teachers_id = ?,
            created_at = NOW(),
            job_status_id = ?,
            image = ? 
            WHERE post_jobs_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "sissssiiiisisi", 
        $title,
        $reward_type_id,
        $description,
        $skills,
        $start,
        $end,
        $number_student,
        $timeandwage,
        $category_id,
        $sub_id,
        $teachers_id,
        $job_status_id,
        $images,
        $job_id
    );

    if ($stmt->execute()) {
        echo "<script>alert('Job updated successfully'); window.location='teacher_profile.php';</script>";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

    // ✅ เมื่อกดปุ่ม Delete
    if (isset($_POST['delete_job'])) {
        $delete_status = 3; // เปลี่ยนสถานะเป็นลบ
        $stmt = $conn->prepare("UPDATE post_jobs SET job_status_id = ? WHERE post_jobs_id = ?");
        $stmt->bind_param("ii", $delete_status, $job_id);
        if ($stmt->execute()) {
            // ถ้าการลบสำเร็จ
            echo "<script>alert('Job marked as deleted'); window.location='teacher_profile.php';</script>";
            // หรือถ้าต้องการกลับไปที่หน้า jobmanage.php
            // echo "<script>alert('Job marked as deleted'); window.location='jobmanage.php';</script>";
        } else {
            echo "Error: " . $stmt->error;
        }
    }
    
        
    
    


// ดึงข้อมูลจากฐานข้อมูล
if ($job_id) {
    $sql = "SELECT * FROM post_jobs WHERE post_jobs_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $job_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $job = $result->fetch_assoc();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Posting Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/header-footerstyle.css">
    <script src="js/jobmanage.js"></script>
    <link rel="stylesheet" href="css/jobmanage.css">
    <style>
        #statusBtn {
            font-size: 18px;
            font-weight: bold;
            padding: 12px 24px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
            outline: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        /* สถานะเปิด (สีเขียวพาสเทล) */
        .open {
            background-color:rgb(98, 214, 98);
            /* เขียวพาสเทล */
            color:rgb(255, 255, 255);
            box-shadow: 0px 4px 10px rgba(160, 231, 160, 0.8);
        }

        /* สถานะปิด (สีส้มพาสเทล) */
        .close {
            background-color:rgb(233, 117, 93);
            /* ส้มพาสเทล */
            color:rgb(255, 255, 255);
            box-shadow: 0px 4px 10px rgba(255, 181, 167, 0.8);
        }

        /* เอฟเฟกต์ตอน hover */
        #statusBtn:hover {
            transform: scale(1.1);
        }

        /* เอฟเฟกต์ตอนกด */
        #statusBtn:active {
            transform: scale(0.9);
        }
        .skills-container {
        width: 100%; /* ขนาดเต็มพื้นที่ */
        max-width: 800px; /* กำหนดความกว้างสูงสุด */
        max-height: 400px; /* กำหนดความสูงสูงสุดของกล่อง */
        border: 1px solid #E2E8F0;
        border-radius: 8px; /* มุมโค้ง */
        padding: 15px 28px;
        background: #fff;
        overflow-y: auto; /* ให้มีแถบเลื่อนแนวตั้ง */
        overflow-x: hidden; /* ป้องกันการเลื่อนแนวนอน */
        margin-bottom: 20px;
        }

        /* ปรับให้ Checkbox ไม่ชิดกันเกินไป */
        .form-check {
            padding: 5px;
        }

        .skills-container::-webkit-scrollbar {
            width: 8px;
        }

        .skills-container::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        .skills-container::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        #reward-label {
            display: flex;
            justify-content: space-between; /* จัดข้อความให้ขยายไปทั้งสองข้าง */
            align-items: center; /* จัดให้ข้อความและหน่วยอยู่แนวเดียวกัน */
        }

        #reward-label-text {
            flex-grow: 1; /* ทำให้ข้อความหลักขยายเต็มที่ */
        }

        #reward-unit {
            margin-left: 5px;  /* เพิ่มระยะห่างระหว่างข้อความหลักกับหน่วย */
            font-weight: normal; /* กำหนดให้หน่วยไม่หนา */
        }
        
    </style>
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


    <!--เครื่องหมายย้อนกลับ-->
    <nav class="back-head">
        <a href="javascript:history.back()"> <i class="bi bi-chevron-left"></i></a>
    </nav>

    <div class="title-container">
        <a href="viewapply.php?id=<?php echo $job['post_jobs_id']; ?>" class="nav-link ">View Applications</a>
        <a href="#" class="nav-link bg-gray" onclick="toggleManageJob(this)">Manage Job</a>
    </div>


    <!-- Main Content -->
    <main class="container">

        <!--ส่วนfromต่างๆ-->
        <div class="form-card">
            <div class="d-flex justify-content-between text-center">
                <h4 class="head-title">Manage Job</h4>
                <button id="statusBtn" onclick="toggleStatus()" class="btn" data-status="<?php echo $job['job_status_id']; ?>"></button>
                
                <div id="modalOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 998;"></div>

                <div id="closeReasonModal" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 25px; border-radius: 12px; box-shadow: 0 8px 20px rgba(0, 0, 0, 0.4); width: 400px; text-align: center; z-index: 999;">
                    <h3 style="margin-bottom: 15px; font-size: 20px; color: #333;">เลือกเหตุผลในการปิดงาน</h3>
                    <select id="close_detail_id" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #ccc; font-size: 16px;">
                        <option value="">-- กรุณาเลือกเหตุผล --</option>
                    </select>
                    <div id="additionalDetail" style="display: none; margin-top: 15px;">
                        <input type="text" id="detail" placeholder="กรอกเหตุผลเพิ่มเติม" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #ccc; font-size: 16px;">
                    </div>
                    <div style="margin-top: 20px; display: flex; justify-content: space-between;">
                        <button id="closeModalBtn" 
                            style="background-color:rgb(241, 71, 88); color: white; padding: 10px 20px; border: none; border-radius: 6px; font-size: 16px; cursor: pointer;">ยกเลิก</button>
                        <button id="confirmCloseBtn" style="background-color:rgb(72, 208, 103); color: white; padding: 10px 20px; border: none; border-radius: 6px; font-size: 16px; cursor: pointer;"
                             data-job-id="<?php echo $job['post_jobs_id']; ?>">ยืนยัน</button>
                    </div>
                </div>
            </div>
        </div>



        <form method="POST" action="jobmanage.php?id=<?php echo $job_id; ?>">

            <div class="form-group">
                <label class="form-label">Job Name/ชื่องาน</label>
                <input type="text" class="form-input" name="title" value="<?php echo htmlspecialchars($job['title']); ?>" required>
            </div>

            <!-- Checkbox สำหรับทักษะที่ต้องการ -->
            <label class="form-label">Skills Required / ทักษะที่ต้องการ</label>
                <div class="skills-container">
                     <!-- 🔎 กล่องค้นหา -->
                     <input type="text" id="skillSearch" class="form-control mb-2" placeholder="🔍 ค้นหาทักษะ..." onkeyup="searchSkills()">
                    <div class="row" id="skillsList">
                        <?php foreach ($skills as $skill) : ?>
                            <div class="col-md-4 col-sm-6 col-12 skill-item" data-skill="<?php echo strtolower(htmlspecialchars($skill['skills_name'])); ?>">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="skills[]" value="<?php echo $skill['skills_id']; ?>" id="skills<?php echo $skill['skills_id']; ?>" <?php echo in_array($skill['skills_id'], explode(',', $job['skills'])) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="skills<?php echo $skill['skills_id']; ?>">
                                        <?php echo htmlspecialchars($skill['skills_name']); ?>
                                    </label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Job Category/ประเภทงาน</label>
                    <select class="form-select" name="job_categories_id" id="category-select" required>
                        <option value="">-- เลือกประเภทงาน --</option>
                        <?php foreach ($categories as $category) : ?>
                            <option value="<?php echo $category['job_categories_id']; ?>" <?php echo ($category['job_categories_id'] == $job['job_categories_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['categories_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Dropdown สำหรับ Job Sub -->
                <div class="form-group" id="job-sub-container" style="display: none;">
                    <label class="form-label">Job Subcategory/งานย่อย</label>
                    <select class="form-select" name="job_sub_id" id="job-sub-select" required>
                        <option value="">-- เลือกงานย่อย --</option>
                            <?php foreach ($subcategories as $subcategory) : ?>
                                <option value="<?php echo $subcategory['job_sub_id']; ?>" <?php echo ($subcategory['job_sub_id'] == $job['job_sub_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($subcategory['subcategory_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        
                    </select>
                </div>
                <!--จำนวนนิสิตที่รับ-->
                <div class="form-group">
                        <label class="form-label">Student Count Required/จำนวนตำแหน่งที่ต้องการ</label>
                        <input type="number" name="number_student" value="<?php echo htmlspecialchars($job['number_student']); ?>" min="1" required>
                    </div>

                    <!--วันเริ่มงานกับจบงาน-->
                    <div class="form-group">
                        <label class="form-label">Start Date & Time/เวลาเริ่มงาน</label>
                        <input type="datetime-local" name="job_start" value="<?php echo htmlspecialchars($job['job_start']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">End Date & Time/เวลาสิ้นสุดงาน</label>
                        <input type="datetime-local" name="job_end" value="<?php echo htmlspecialchars($job['job_end']); ?>" required>
                    </div>


                    <div class="form-group">
                        <label class="form-label">Cover photo/ภาพหน้าปกงาน</label>
                        <div class="images">
                            <img src="images/img1.jpg" alt="Image 1" onclick="selectImage(this)">
                            <img src="images/img2.jpg" alt="Image 2" onclick="selectImage(this)">
                            <input type="hidden" name="image" id="selectedImagePath">
                        </div>
                    </div>



                    <div class="form-group">
                        <label class="form-label">Job Category/ผลตอบแทน</label>
                        <select class="form-select" name="reward_type_id" id="reward-type-select"  required>
                            <option value="">-- เลือกประเภทผลตอบแทน --</option>
                            <?php foreach ($reward as $reward_type) : ?>
                                <option value="<?php echo $reward_type['reward_type_id']; ?>" <?php echo ($reward_type['reward_type_id'] == $job['reward_type_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($reward_type['reward_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- ช่องสำหรับกรอกค่าตามประเภท -->
                    <div class="form-group" id="reward-input-container" style="display: none;">
                        <label class="form-label" id="reward-label">
                            <span id="reward-label-text"></span> <span id="reward-unit"></span>
                        </label>
                        <input type="number" class="form-input" name="timeandwage" id="reward-input" value="<?php echo htmlspecialchars($job['timeandwage']); ?>" min="1" required>
                    </div>




                    <div class="form-group">
                        <label class="form-label">Job Details/รายละเอียดงาน</label>
                        <textarea name="description" required><?php echo htmlspecialchars($job['description']); ?></textarea>
                    </div>



            <!--ปุ่มส่ง-->
            <div class="submit-group">
                <button type="submit" name="delete_job" class="delete-btn" style="background-color: <?php echo ($job['job_status_id'] == 3) ? 'gray' : 'white'; ?>;">
                    <?php echo ($job['job_status_id'] == 3) ? 'Deleted' : 'Delete'; ?>
                </button>
                <button type="submit" name="done" class="submit-btn">Done</button>
            </div>
        </form>


        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>
        <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>
        </div>
    </main>
    <footer class="footer">
        <p>© CSIT - Computer Science and Information Technology</p>
    </footer>
    <script>
        // เปลี่ยนสถานะของภาพที่เลือก
        function selectImage(imageElement) {
            // ลบคลาส selected ออกจากภาพทั้งหมด
            var images = document.querySelectorAll('.images img');
            images.forEach(img => img.classList.remove('selected'));

            // เพิ่มคลาส selected ให้กับภาพที่ถูกเลือก
            imageElement.classList.add('selected');

            // ดึง path ของภาพที่ถูกเลือก (เอาแค่ชื่อไฟล์ไม่รวม URL)
            var imagePath = imageElement.src.split('/').pop(); // หรือใช้ substring เพื่อดึงชื่อไฟล์

            // อัปเดตค่า imagePath ให้กับ input hidden
            document.getElementById('selectedImagePath').value = "images/" + imagePath;
        }

        function updateJobStatus(toggle) {
            // Get the new status (1 = Open, 2 = Close)
            var status = toggle.checked ? 1 : 2;

            // Update the hidden field with the new status value
            document.getElementById('jobStatus').value = status;

            // Send the new status to the server via AJAX
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "update_job_status.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    // Handle success response (optional)
                    console.log("Status updated successfully!");
                }
            };
            xhr.send("job_id=<?php echo $job['id']; ?>&job_status_id=" + status);
        }

        // เมื่อคลิกที่ลิงก์ "View Applications" หรือ "Manage Job" 
        // เพื่อให้ลิงก์ที่คลิกแสดงสถานะ active
        function toggleViewApp(element) {
            document.querySelectorAll('.nav-link').forEach(link => link.classList.remove('active'));
            element.classList.add('active');
        }

        // เมื่อคลิกที่ลิงก์ "Manage Job" เพื่อให้ลิงก์ที่คลิกแสดงสถานะ active
        function toggleManageJob(element) {
            document.querySelectorAll('.nav-link').forEach(link => link.classList.remove('active'));
            element.classList.add('active');
        }
    </script>
    <script>
        let jobId = <?php echo $job_id; ?>; // ใส่ ID จริงของงาน

        function loadStatus() {
            fetch(`get_status.php?id=${jobId}`)
                .then(response => response.json())
                .then(data => {
                    updateButton(data.status);
                });
        }

        function toggleStatus() {
            let currentStatus = document.getElementById('statusBtn').dataset.status;

            fetch('update_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `id=${jobId}&status=${currentStatus}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateButton(data.new_status);
                    }
                });
        }

        function openPopup() {
        document.getElementById("statusPopup").style.display = "flex";
    }

    function closePopup() {
        document.getElementById("statusPopup").style.display = "none";
    }

    function changeStatus(status) {
        let btn = document.getElementById("statusBtn");

        // อัปเดตปุ่มหลัก
        btn.dataset.status = status;
        btn.innerText = (status == 1) ? 'เปิด (Open)' : 'ปิด (Close)';
        btn.classList.remove("open", "close");
        btn.classList.add(status == 1 ? "open" : "close");

        // ส่งค่าไปอัปเดตในฐานข้อมูล (AJAX)
        fetch('update_status.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${jobId}&status=${status}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("Status updated successfully!");
            } else {
                alert("Failed to update status!");
            }
        });

        closePopup();
    }

        function updateButton(status) {
            let btn = document.getElementById('statusBtn');
            btn.dataset.status = status;
            btn.innerText = (status == 1) ? 'เปิด (Open) ' : 'ปิด (Close) ';

            // ลบคลาสเดิม แล้วเพิ่มคลาสใหม่
            btn.classList.remove('open', 'close');
            btn.classList.add(status == 1 ? 'open' : 'close');
        }


        loadStatus(); // โหลดสถานะเริ่มต้นเมื่อหน้าโหลด
    </script>
    <script>
        document.getElementById("category-select").addEventListener("change", function () {
            const categoryId = this.value;
            const jobSubSelect = document.getElementById("job-sub-select");
            const jobSubContainer = document.getElementById("job-sub-container");

            // ล้างตัวเลือกเดิม
            jobSubSelect.innerHTML = '<option value="">-- เลือกงานย่อย --</option>';

            if (categoryId) {
                fetch(`get_job_subcategories.php?category_id=${categoryId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.length > 0) {
                            data.forEach(sub => {
                                const option = document.createElement("option");
                                option.value = sub.id;
                                option.textContent = sub.name;
                                jobSubSelect.appendChild(option);
                            });

                            // แสดง dropdown งานย่อย
                            jobSubContainer.style.display = "block";
                        } else {
                            jobSubContainer.style.display = "none";
                        }
                    })
                    .catch(error => console.error('Error:', error));
            } else {
                jobSubContainer.style.display = "none";
            }
        });
    </script>
    <script>
        document.getElementById("job-end").addEventListener("change", function () {
            const startTime = document.getElementById("job-start").value;
            const endTime = this.value;

            if (startTime && endTime && endTime <= startTime) {
                alert("เวลาสิ้นสุดต้องมากกว่าเวลาเริ่มต้น!");
                this.value = ""; // ล้างค่าเวลาสิ้นสุด
            }
        });
    </script>
    <script>
        document.getElementById("reward-type-select").addEventListener("change", function () {
            const rewardType = this.value;
            const rewardInputContainer = document.getElementById("reward-input-container");
            const rewardLabelText = document.getElementById("reward-label-text");
            const rewardUnit = document.getElementById("reward-unit");
            const rewardInput = document.getElementById("reward-input");

            if (rewardType) {
                // ถ้าผู้ใช้เลือกประเภทเป็น "เงิน"
                if (rewardType == "1") {
                    rewardLabelText.textContent = "Amount/จำนวนเงิน";
                    rewardUnit.textContent = "บาท";
                }
                // ถ้าผู้ใช้เลือกประเภทเป็น "ชั่วโมงประสบการณ์"
                else if (rewardType == "2") {
                    rewardLabelText.textContent = "Hours/จำนวนชั่วโมง";
                    rewardUnit.textContent = "ชั่วโมง";
                }

                // แสดงช่องกรอกค่าตามประเภทที่เลือก
                rewardInputContainer.style.display = "block";
            } else {
                // ซ่อนช่องกรอกค่าหากไม่ได้เลือกประเภทผลตอบแทน
                rewardInputContainer.style.display = "none";
            }
        });
    </script>
    <script>
        function searchSkills() {
            let searchValue = document.getElementById("skillSearch").value.toLowerCase();
            let skills = document.querySelectorAll("#skillsList .skill-item");

            skills.forEach(function(skill) {
                let skillText = skill.getAttribute("data-skill");
                if (skillText.includes(searchValue)) {
                    skill.style.display = "block";  // ✅ แสดงเมื่อพบ
                } else {
                    skill.style.display = "none";   // ❌ ซ่อนเมื่อไม่พบ
                }
            });
        }
    </script>
    <script>
document.addEventListener("DOMContentLoaded", function() {
    const statusBtn = document.getElementById("statusBtn");
    const closeModalBtn = document.getElementById("closeModalBtn");
    const confirmCloseBtn = document.getElementById("confirmCloseBtn");
    const closeModal = document.getElementById("closeReasonModal");
    const modalOverlay = document.getElementById("modalOverlay");
    const closeDetailSelect = document.getElementById("close_detail_id");
    const additionalDetail = document.getElementById("additionalDetail");
    const detailInput = document.getElementById("detail");

    function toggleStatus() {
        const jobId = statusBtn.getAttribute("data-job-id");
        let currentStatus = statusBtn.getAttribute("data-status");
        
        if (currentStatus == "1") { // ถ้าเป็น Open (1) ให้แสดง popup
            closeModal.style.display = "block";
            modalOverlay.style.display = "block";
        } else { // ถ้าเป็น Close (2) ให้เปลี่ยนเป็น Open (1) ทันที
            fetch("update_status.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `id=${jobId}&status=1` // เปลี่ยนเป็น Open
            })
            .then(response => response.json())
            
        }
    }

    // ดึงข้อมูลจาก database
    fetch("get_close_details.php")
        .then(response => response.json())
        .then(data => {
            data.forEach(item => {
                let option = document.createElement("option");
                option.value = item.close_detail_id;
                option.textContent = item.close_detail_name;
                closeDetailSelect.appendChild(option);
            });
        });

    // ดึงข้อมูลประเภทงานจาก database
    fetch("get_job_categories.php")
        .then(response => response.json())
        .then(data => {
            const categorySelect = document.getElementById("category-select");
            data.forEach(category => {
                let option = document.createElement("option");
                option.value = category.job_categories_id;
                option.textContent = category.categories_name;
                categorySelect.appendChild(option);
            });
        });

    statusBtn.addEventListener("click", function() {
        const currentStatus = statusBtn.getAttribute("data-status");
        if (currentStatus == "2") { // ถ้าเป็น Close ให้เปลี่ยนเป็น Open ทันที
            toggleStatus();
        } else {
            closeModal.style.display = "block";
            modalOverlay.style.display = "block";
        }
    });

    

    closeModalBtn.addEventListener("click", function() {
        closeModal.style.display = "none";
        modalOverlay.style.display = "none";
    });

    closeDetailSelect.addEventListener("change", function() {
        if (this.value == "12") {
            additionalDetail.style.display = "block";
        } else {
            additionalDetail.style.display = "none";
            detailInput.value = "";
        }
    });

    confirmCloseBtn.addEventListener("click", function() {
        const jobId = this.getAttribute("data-job-id");
        const closeDetailId = closeDetailSelect.value;
        let detail = detailInput.value.trim(); // ดึงค่าที่กรอกจาก input

        // ถ้า close_detail_id ไม่ใช่ 12 ให้ใช้ชื่อเหตุผลที่เลือกแทน
        if (closeDetailId !== "12") {
            detail = closeDetailSelect.options[closeDetailSelect.selectedIndex].text;
        }

        if (!closeDetailId) {
            alert("กรุณาเลือกเหตุผลก่อนเปลี่ยนสถานะ");
            return;
        }

        fetch("update_status.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `id=${jobId}&status=1&close_detail_id=${closeDetailId}&detail=${detail}`
        })
        .then(response => response.json())
        .then(data => {
           if (data.success) {
                fetch("save_close_reason.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: `id=${jobId}&close_detail_id=${closeDetailId}&detail=${detail}`
                }).then(() => {
                    closeModal.style.display = "none";
                    statusBtn.setAttribute("data-status", "2");
                    location.reload();
                });
            }
        });
    });
});
</script>

    
    
    
</body>

</html>