<?php
include 'database.php';
$job_id = isset($_GET['id']) ? intval($_GET['id']) : null;

if ($job_id) {
    // ดึงข้อมูลรายละเอียดของงานตาม ID
    $sql = "SELECT post_job.title, post_job.job_start, post_job.job_end, post_job.number_student,
            post_job.image , post_job.description, post_job.time_and_wage, teacher.profile, post_job.reward_type_id,
            reward_type.reward_type_name AS reward_name, job_category.job_category_name AS category, teacher.teach_name AS teacher
            FROM post_job
            JOIN teacher ON post_job.teacher_id = teacher.teacher_id
            JOIN job_category ON post_job.job_category_id = job_category.job_category_id
            JOIN job_subcategory ON post_job.job_subcategory_id = job_subcategory.job_subcategory_id
            JOIN reward_type ON post_job.reward_type_id = reward_type.reward_type_id
            WHERE post_job.post_job_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $job_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $job = $result->fetch_assoc();
    $stmt->close();

    $sql = "SELECT post_job_id,
               GROUP_CONCAT(subskill.subskill_name ORDER BY subskill.subskill_name SEPARATOR ', ') AS skills
        FROM post_job_skill
        JOIN subskill ON post_job_skill.subskill_id= subskill.subskill_id
        WHERE post_job_id = ?
        GROUP BY post_job_id";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $job_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $skill = $result->fetch_assoc();
    $stmt->close();
}

// ดึงกฎการรายงานจากฐานข้อมูล
$report_reasons = [];
$sql = "SELECT report_category_id as id, report_category_name FROM report_category";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $report_reasons[] = $row;
    }
}

$sql = "SELECT * FROM reward_type WHERE reward_type_id = " . $job['reward_type_id'];
$reward = $conn->query($sql);

// ปิดการเชื่อมต่อ
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Join Us Page">
    <title>Join Us</title>
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
    <a href="#" onclick="goBackOrHome()" class="back-arrow"></a>

<script>
    function goBackOrHome() {
        window.location.href = "reports.php"; // Go to home page
    }
    </script>
    <div class="container">
        <div class="applicant-card">
            <div class="applicant-photo-joinus">
                <img src="<?php echo '../' . htmlspecialchars($job['image']); //รูปงานตรงนี้มีน?>" alt="Job Image">
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
                <span>สกิลที่ต้องการ : <?php echo nl2br(htmlspecialchars($skill['skills'])); ?></span>
            </div>
            <div class="applicant-reward">
                <span>ผลตอบแทน : </span>
                <span><?php echo "  " . nl2br(htmlspecialchars($job['time_and_wage']));
                        if (htmlspecialchars($job['reward_type_id']) != 1) {
                            echo " บาท ";
                        } else {
                            echo " ชั่วโมง ";
                        }
                        $row = $reward->fetch_assoc();
                        echo '(' . htmlspecialchars($row['reward_type_name']) . ')';
                        ?></span>

            </div>

            <div class="applicant-details-name">
                <img class="profile-pic"
                    id="profile_picture"
                    src="<?php echo '../' . htmlspecialchars($job['profile']); //รูปโปรไฟล์ตรงนี้มีน?>"
                    alt="Profile Picture">
                <?php echo htmlspecialchars($job['teacher']); ?>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>