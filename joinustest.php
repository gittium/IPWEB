
<?php

include 'database.php';

// รับค่า id จาก URL
$job_id = isset($_GET['id']) ? intval($_GET['id']) : null;

if ($job_id) {
    // ดึงข้อมูลรายละเอียดของงานตาม ID
    $sql = "SELECT post_jobs.title,post_jobs.skills,post_jobs.job_start,post_jobs.job_end,post_jobs.number_student,
            post_jobs.image , post_jobs.description, post_jobs.timeandwage,  post_jobs.reward_type_id,
            reward_type.reward_name, job_categories.categories_name AS category, teachers.name AS teacher
            FROM post_jobs 
            JOIN teachers ON post_jobs.teachers_id = teachers.teachers_id
            JOIN job_categories ON post_jobs.job_categories_id = job_categories.job_categories_id
            JOIN job_subcategories ON post_jobs.job_sub_id = job_subcategories.job_sub_id
            JOIN reward_type ON post_jobs.reward_type_id = reward_type.reward_type_id
            WHERE post_jobs.post_jobs_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $job_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $job = $result->fetch_assoc();
    $stmt->close();
}

// ดึงกฎการรายงานจากฐานข้อมูล
$report_reasons = [];
$sql = "SELECT report_categories_id as id, report_categories_name FROM report_categories";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $report_reasons[] = $row;
    }
}

$sql = "SELECT pj.post_jobs_id, pj.title, 
               CONCAT('สกิลที่ต้องการ : ', GROUP_CONCAT(s.skills_name ORDER BY s.skills_id SEPARATOR ', ')) AS skill_list
        FROM post_jobs pj
        JOIN skills s ON FIND_IN_SET(s.skills_id, pj.skills) > 0
        WHERE pj.post_jobs_id = ?
        GROUP BY pj.post_jobs_id, pj.title";

// เตรียมคำสั่ง SQL
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $job_id); // ผูกค่าพารามิเตอร์
$stmt->execute();
$result = $stmt->get_result();

// ดึงข้อมูล
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $skill_list = $row['skill_list']; // ดึงค่าลงตัวแปร
} else {
    $skill_list = "ไม่มีข้อมูล"; // กรณีไม่มีข้อมูล
}

// ปิดการเชื่อมต่อ
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Join Us Page">
    <title>Join Us</title>
    <link rel="stylesheet" href="css/header-footerstyle.css">
    <link rel="stylesheet" href="css/joinus.css">
    <style>
        .applicant-details {
            display: flex;
            flex-direction: column;
            align-items: start;
        }
    </style>
</head>

<body>
    <!-- Main Content -->


    <div class="container">
        <div class="applicant-card">
            <div class="applicant-photo-joinus">
                <img src="<?php echo htmlspecialchars($job['image']); ?>" alt="Job Image">
            </div>
        </div>

        <div class="title-container">
            <h1 class="section-title"><?php echo htmlspecialchars($job['title']); ?> </h1>
        </div>

        <div class="applicant-card">
            <div class="applicant-details">
                <span><?php echo nl2br(htmlspecialchars($job['description'])); ?></span>
                <br>
                <span>วันเริ่มงาน : <?php echo nl2br(htmlspecialchars($job['job_start'])); ?></span>
                <br>
                <span>วันสิ้นสุดงาน : <?php echo nl2br(htmlspecialchars($job['job_end'])); ?></span>
                <br>
                <span>จำนวนนิสิตที่รับ : <?php echo nl2br(htmlspecialchars($job['number_student']) . " คน"); ?></span>
                <br>
                <span><?php echo nl2br(htmlspecialchars($skill_list)); ?></span>
            </div>
            <div class="applicant-reward">
                <span>ผลตอบแทน : </span>
                <span><?php echo " " . nl2br(htmlspecialchars($job['timeandwage']));
                        if (htmlspecialchars($job['reward_type_id']) == 1) {
                            echo " บาท ";
                        } else {
                            echo " ชั่วโมง ";
                        }
                        ?></span>

            </div>

            <div class="applicant-details-name">
                <span class="emoji">👤</span>
                <?php echo htmlspecialchars($job['teacher']); ?>
            </div>

           
        </div>
    </div>

   


    <script src="js/joinus.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<?php
$conn->close();
?>