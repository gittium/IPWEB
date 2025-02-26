<?php
session_start();
include 'database.php';
$_SESSION['teachers_id'] = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'CSIT0131';

 // ใช้เฉพาะในกรณีทดสอบ

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    date_default_timezone_set('Asia/Bangkok'); // ตั้งค่า Timezone เป็นเวลาประเทศไทย
    $created_at = date('Y-m-d H:i:s');
    // รับค่าจากฟอร์ม (แก้ไขให้ตรงกัน)
    $title = $_POST['title'] ?? null;
    $description = $_POST['description'] ?? null;
    $skills = isset($_POST['skills']) && is_array($_POST['skills']) ? $_POST['skills'] : [];
    $start = $_POST['job_start'] ?? date('Y-m-d H:i:s');
    $end = $_POST['job_end'] ?? date('Y-m-d H:i:s');
    $number_student = isset($_POST['number_student']) && is_numeric($_POST['number_student']) ? intval($_POST['number_student']) : null;
    $teachers_id = $_SESSION['teachers_id'];
    $reward_type_id = $_POST['reward_type_id'] ?? null;
    $timeandwage = $_POST['timeandwage'] ?? null;
    $category_id = $_POST['job_categories_id'] ?? null;
    $sub_id = $_POST['job_sub_id'] ?? null;
    $job_status_id = 1;
    $images = $_POST['image'] ?? null;

    // ตรวจสอบค่าที่จำเป็นก่อนบันทึก
    if (empty($title) || empty($reward_type_id) || empty($description) || empty($number_student) || empty($skills)) {
        echo "<script>alert('Error: Missing required fields. กรุณากรอกข้อมูลให้ครบทุกช่อง!'); window.history.back();</script>";
        exit;
    }
    
    

    $skills = implode(",", $skills);  // แปลงเป็น string แบบ comma-separated

    // บันทึกข้อมูล
    // ใช้ Prepared Statement ป้องกัน SQL Injection
    $stmt = $conn->prepare("INSERT INTO post_jobs 
    (title, reward_type_id, description, skills, job_start, job_end, number_student, timeandwage, job_categories_id, job_sub_id, teachers_id, created_at, job_status_id, image) 
    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)");


    $stmt->bind_param(
        "sissssiiiissis",  // แก้ไขเป็น string, int, string, string, int, int, int, int, string
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
        $created_at,
        $job_status_id,
        $images

    );

    if ($stmt->execute()) {
        echo "<script>alert('New job posted successfully'); window.location='teacher_profile.php';</script>";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();

    // ตรวจสอบว่าเวลาสิ้นสุดมากกว่าเวลาเริ่มต้นหรือไม่
    if ($start && $end && $end <= $start) {
        echo "<script>alert('Error: เวลาสิ้นสุดต้องมากกว่าเวลาเริ่มต้น!'); window.history.back();</script>";
        exit;
    }

    // ตรวจสอบว่ากรอกค่าผลตอบแทนหรือไม่
    if ($reward_type_id && !$timeandwage) {
        echo "<script>alert('Error: กรุณากรอกค่าผลตอบแทน!'); window.history.back();</script>";
        exit;
    }

    // ตรวจสอบค่าที่กรอกให้ตรงกับประเภท
    if ($reward_type_id == "1" && (!is_numeric($timeandwage) || $timeandwage <= 0)) {
        echo "<script>alert('Error: กรุณากรอกจำนวนเงินที่ถูกต้อง!'); window.history.back();</script>";
        exit;
    } elseif ($reward_type_id == "2" && (!is_numeric($timeandwage) || $timeandwage <= 0)) {
        echo "<script>alert('Error: กรุณากรอกจำนวนชั่วโมงที่ถูกต้อง!'); window.history.back();</script>";
        exit;
    }



    $conn->close();
}



?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Posting Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/jobpose.css">
    <link rel="stylesheet" href="css/header-footerstyle.css">
    <script src="js/jobpost.js"></script>
</head>
<style>
    .skills-container {
        width: 100%;
        /* ขนาดเต็มพื้นที่ */
        max-width: 800px;
        /* กำหนดความกว้างสูงสุด */
        max-height: 400px;
        /* กำหนดความสูงสูงสุดของกล่อง */
        border: 1px solid #E2E8F0;
        border-radius: 8px;
        /* มุมโค้ง */
        padding: 15px 28px;
        background: #fff;
        overflow-y: auto;
        /* ให้มีแถบเลื่อนแนวตั้ง */
        overflow-x: hidden;
        /* ป้องกันการเลื่อนแนวนอน */
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
</style>

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
            <a href="#">Logout</a>
        </nav>
    </header>

    <!--เครื่องหมายย้อนกลับ-->
    <nav class="back-head">
        <a href="javascript:history.back()"> <i class="bi bi-chevron-left"></i></a>

    </nav>
    <!-- Main Content -->
    <main class="container">

        <!--ส่วนfromต่างๆ-->
        <div class="form-card">
            <h1 class="form-title">Job Posting</h1>
            <?php echo "DEBUG: teachers_id = " . $_SESSION['teachers_id']; ?>
            <form method="POST" action="jobpost2.php">
                <!--ชื่องาน-->
                <div class="form-group">
                    <label class="form-label">Job Name/ชื่องาน</label>
                    <input type="text" class="form-input" name="title" required>
                </div>

                <!--ทักษะ-->
                <?php
                $skills = [];
                $sql = "SELECT skills_id, skills_name 
                            FROM skills
                            ORDER BY skills.skills_id";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $skills[] = $row;
                    }
                }
                ?>

                <!-- Checkbox สำหรับทักษะที่ต้องการ -->
                <label class="form-label">Skills Required / ทักษะที่ต้องการ</label>
                <div class="skills-container">
                    <!-- 🔎 กล่องค้นหา -->
                    <input type="text" id="skillSearch" class="form-control mb-2" placeholder="ค้นหาทักษะ..." onkeyup="searchSkills()">
                    <div class="row" id="skillsList">
                        <?php foreach ($skills as $skill) : ?>
                            <div class="col-md-4 col-sm-6 col-12 skill-item" data-skill="<?php echo strtolower(htmlspecialchars($skill['skills_name'])); ?>">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="skills[]" value="<?php echo $skill['skills_id']; ?>" id="skills<?php echo $skill['skills_id']; ?>">
                                    <label class="form-check-label" for="skills<?php echo $skill['skills_id']; ?>">
                                        <?php echo htmlspecialchars($skill['skills_name']); ?>
                                    </label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>



                <!--ประเภทงาน-->
                <?php
                $categories = [];
                $sql = "SELECT job_categories_id, categories_name 
                            FROM job_categories
                            ORDER BY job_categories.job_categories_id";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $categories[] = $row;
                    }
                }
                ?>
                <div class="form-group">
                    <label class="form-label">Job Category/ประเภทงาน</label>
                    <select class="form-select" name="job_categories_id" id="category-select" required>
                        <option value="">-- เลือกประเภทงาน --</option>
                        <?php foreach ($categories as $category) : ?>
                            <option value="<?php echo $category['job_categories_id']; ?>">
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
                    </select>
                </div>

                <!--จำนวนนิสิตที่รับ-->
                <div class="form-group">
                    <label class="form-label">Student Count Required/จำนวนตำแหน่งที่ต้องการ</label>
                    <input type="number" id="vacancy" name="number_student" placeholder="" min="1">
                </div>

                <!--วันเริ่มงานกับจบงาน-->
                <div class="form-group">
                    <label class="form-label">Start Date & Time/เวลาเริ่มงาน</label>
                    <input type="datetime-local" class="form-input" name="job_start" id="job-start" required>
                </div>

                <div class="form-group">
                    <label class="form-label">End Date & Time/เวลาสิ้นสุดงาน</label>
                    <input type="datetime-local" class="form-input" name="job_end" id="job-end" required>
                </div>


                <div class="form-group">
                    <label class="form-label">Cover photo/ภาพหน้าปกงาน</label>
                    <div class="images">
                        <img src="images/img1.jpg" alt="Image 1" onclick="selectImage(this)">
                        <img src="images/img2.jpg" alt="Image 2" onclick="selectImage(this)">
                        <input type="hidden" name="image" id="selectedImagePath">
                    </div>
                </div>



                <!--ผลตอบแทน-->
                <?php
                $reward = [];
                $sql = "SELECT reward_type_id, reward_name 
                            FROM reward_type
                            ORDER BY reward_type.reward_type_id";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $reward[] = $row;
                    }
                }
                ?>

                <div class="form-group">
                    <label class="form-label">Job Category/ผลตอบแทน</label>
                    <select class="form-select" name="reward_type_id" id="reward-type-select" required>
                        <option value="">-- เลือกประเภทผลตอบแทน --</option>
                        <?php foreach ($reward as $reward_type) : ?>
                            <option value="<?php echo $reward_type['reward_type_id']; ?>">
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
                    <input type="number" class="form-input" name="timeandwage" id="reward-input" min="1" required>
                </div>




                <div class="form-group">
                    <label class="form-label">Job Details/รายละเอียดงาน</label>
                    <textarea id="job-details" name="description" placeholder="" required></textarea>
                </div>

                <!--ปุ่มส่ง-->
                <div class="submit-group">
                    <button type="submit" class="submit-btn">Add Job</button>
                </div>
            </form>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
            <script src="js/jobpost.js"></script>
            <script>
                function searchSkills() {
                    let searchValue = document.getElementById("skillSearch").value.toLowerCase();
                    let skills = document.querySelectorAll("#skillsList .skill-item");

                    skills.forEach(function(skill) {
                        let skillText = skill.getAttribute("data-skill");
                        if (skillText.includes(searchValue)) {
                            skill.style.display = "block"; // ✅ แสดงเมื่อพบ
                        } else {
                            skill.style.display = "none"; // ❌ ซ่อนเมื่อไม่พบ
                        }
                    });
                }
            </script>
        </div>
        </div>
    </main>
    <footer class="footer">
        <p>© CSIT - Computer Science and Information Technology</p>
    </footer>
    <script>
        document.getElementById("category-select").addEventListener("change", function() {
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
        document.getElementById("reward-type-select").addEventListener("change", function() {
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
</body>


</html>