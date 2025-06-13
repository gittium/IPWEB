<?php
// เชื่อมต่อฐานข้อมูล
include 'database.php';
// รวมไฟล์คำนวณรีวิว
include 'calculate_review.php';

// ตรวจสอบว่ามีการส่ง id หรือไม่
$student_id = isset($_GET['student_id']) ? $_GET['student_id'] : null;

// ถ้าเป็น POST (อัปโหลดรูปโปรไฟล์ หรือ อัปเดตข้อมูลอื่นๆ)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $isProfileUpdated = false;
    $isSkillsUpdated = false;
    $isHobbiesUpdated = false;

    // ตรวจสอบว่ามีการอัปโหลดรูปภาพโปรไฟล์ใหม่หรือไม่
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        // อัปโหลดโปรไฟล์ใหม่
        $uploadDir = '../profile/'; //รูปโปรไฟล์ตรงนี้มีน
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $fileTmpPath = $_FILES['profile_image']['tmp_name'];
        $fileName = basename($_FILES['profile_image']['name']);
        $fileNameNew = uniqid('profile_', true) . "_" . $fileName;
        $fileDest = $uploadDir . $fileNameNew;

        if (move_uploaded_file($fileTmpPath, $fileDest)) {
            // อัปเดตข้อมูลรูปโปรไฟล์ในฐานข้อมูล
            $sqlUpdateProfile = "UPDATE student SET profile = ? WHERE student_id = ?";
            $stmtProfile = $conn->prepare($sqlUpdateProfile);
            $stmtProfile->bind_param("ss", $fileDest, $user_id);
            if ($stmtProfile->execute()) {
                $isProfileUpdated = true;
            } else {
                echo json_encode(["success" => false, "message" => "Database error: " . $stmtProfile->error]);
                $stmtProfile->close();
                exit();
            }
            $stmtProfile->close();
        } else {
            echo json_encode(["success" => false, "message" => "Upload failed."]);
            exit();
        }
    }

    // ตรวจสอบและอัปเดตข้อมูล skills และ hobbies
    $selectedSkills = isset($_POST['selectedSkills']) ? explode(',', $_POST['selectedSkills']) : [];
    $selectedHobbies = isset($_POST['selectedHobbies']) ? explode(',', $_POST['selectedHobbies']) : [];

    if (!empty($selectedSkills) || !empty($selectedHobbies)) {
        // อัปเดต skills และ hobbies
        $success = updateSkillsAndHobbies($conn, $user_id, $selectedSkills, $selectedHobbies);
        if ($success) {
            $isSkillsUpdated = true;
            $isHobbiesUpdated = true;
        }
    }

    // อัปเดตข้อมูลทุกส่วนได้สำเร็จ
    if ($isProfileUpdated || $isSkillsUpdated || $isHobbiesUpdated) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => "No data to update"]);
    }

    $conn->close();
    exit();
}

// ฟังก์ชันอัปเดต skills และ hobbies
function updateSkillsAndHobbies($conn, $user_id, $selectedSkills, $selectedHobbies)
{
    $conn->begin_transaction();

    try {
        // ลบข้อมูล skills เดิมที่เกี่ยวข้องกับนิสิต
        $delete_skills_sql = "DELETE FROM student_skill WHERE student_id = ?";
        $stmt = $conn->prepare($delete_skills_sql);
        $stmt->bind_param("s", $user_id);
        $stmt->execute();

        // อัปเดต skills ใหม่
        foreach ($selectedSkills as $skillData) {
            list($skill_id, $subskill_id) = explode("-", $skillData);
            $insert_skill_sql = "INSERT INTO student_skill (student_id, skill_id, subskill_id) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($insert_skill_sql);
            $stmt->bind_param("sii", $user_id, $skill_id, $subskill_id);
            $stmt->execute();
        }

        // ลบข้อมูล hobbies เดิมที่เกี่ยวข้องกับนิสิต
        $delete_hobbies_sql = "DELETE FROM student_hobby WHERE student_id = ?";
        $stmt = $conn->prepare($delete_hobbies_sql);
        $stmt->bind_param("s", $user_id);
        $stmt->execute();

        // อัปเดต hobbies ใหม่
        foreach ($selectedHobbies as $hobbyData) {
            list($hobby_id, $subhobby_id) = explode("-", $hobbyData);
            $insert_hobby_sql = "INSERT INTO student_hobby (student_id, hobby_id, subhobby_id) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($insert_hobby_sql);
            $stmt->bind_param("sii", $user_id, $hobby_id, $subhobby_id);
            $stmt->execute();
        }

        // commit ข้อมูล
        $conn->commit();
        return true;
    } catch (Exception $e) {
        // หากเกิดข้อผิดพลาดให้ rollback ข้อมูล
        $conn->rollback();
        return false;
    }
}

if ($student_id) {
    // ดึงข้อมูลนิสิตจากฐานข้อมูลตาม id
    $sql = "SELECT s.student_id, s.profile, s.stu_name, s.stu_email, s.major_id, s.year, m.major_name
            FROM student s
            JOIN major m ON s.major_id = m.major_id
            WHERE s.student_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $student = $result->fetch_assoc();
    } else {
        echo "ไม่พบข้อมูลนิสิตที่ตรงกับ ID นี้.";
        exit();
    }
} else {
    echo "ไม่พบข้อมูล ID ของนิสิต.";
    exit();
}

/* --- ดึงรายการ Skill พร้อม Subskill --- */
$all_skills = [];
$sql_skills = "SELECT s.skill_id, s.skill_name, ss.subskill_id, ss.subskill_name 
               FROM skill s 
               LEFT JOIN subskill ss ON s.skill_id = ss.skill_id 
               ORDER BY s.skill_name ASC, ss.subskill_name ASC";
$result_skills = $conn->query($sql_skills);

if ($result_skills) {
    while ($row = $result_skills->fetch_assoc()) {
        $sid = $row['skill_id'];
        if (!isset($all_skills[$sid])) {
            $all_skills[$sid] = [
                'skill_id'   => $row['skill_id'],
                'skill_name' => $row['skill_name'],
                'subskills'  => []
            ];
        }
        if (!empty($row['subskill_id'])) {
            $all_skills[$sid]['subskills'][] = [
                'subskill_id'   => $row['subskill_id'],
                'subskill_name' => $row['subskill_name']
            ];
        }
    }
    $all_skills = array_values($all_skills);  // Re-index to reset the array keys
}

/* --- ดึงรายการ Hobby พร้อม Subhobby --- */
$all_hobbies = [];
$sql_hobbies = "SELECT h.hobby_id, h.hobby_name, sh.subhobby_id, sh.subhobby_name
                FROM hobby h 
                LEFT JOIN subhobby sh ON h.hobby_id = sh.hobby_id
                ORDER BY h.hobby_name ASC, sh.subhobby_name ASC";
$result_hobbies = $conn->query($sql_hobbies);

if ($result_hobbies) {
    while ($row = $result_hobbies->fetch_assoc()) {
        $hid = $row['hobby_id'];
        if (!isset($all_hobbies[$hid])) {
            $all_hobbies[$hid] = [
                'hobby_id'   => $row['hobby_id'],
                'hobby_name' => $row['hobby_name'],
                'subhobbies' => []
            ];
        }
        if (!empty($row['subhobby_id'])) {
            $all_hobbies[$hid]['subhobbies'][] = [
                'subhobby_id'   => $row['subhobby_id'],
                'subhobby_name' => $row['subhobby_name']
            ];
        }
    }
    $all_hobbies = array_values($all_hobbies);  // Re-index the array
}


/* --- ดึงข้อมูล Skill ที่นิสิตเลือก --- */
$student_skills = [];
$sql_student_skill = "SELECT skill_id, subskill_id FROM student_skill WHERE student_id = ?";
$stmt_skill = $conn->prepare($sql_student_skill);
$stmt_skill->bind_param("s", $student_id);
$stmt_skill->execute();
$result_student_skill = $stmt_skill->get_result();

while ($row = $result_student_skill->fetch_assoc()) {
    $student_skills[$row['skill_id']][] = $row['subskill_id'];
}
$stmt_skill->close();

/* สร้างข้อความแสดงผล Skill (แสดงชื่อ skill กับ subskill ที่เลือก) */
$skills_display = [];
foreach ($all_skills as $skill) {
    if (isset($student_skills[$skill['skill_id']])) {
        $selected_subskills = [];
        foreach ($skill['subskills'] as $subskill) {
            if (in_array($subskill['subskill_id'], $student_skills[$skill['skill_id']])) {
                $selected_subskills[] = $subskill['subskill_name'];
            }
        }
        if (!empty($selected_subskills)) {
            $skills_display[] = $skill['skill_name'] . ": " . implode(", ", $selected_subskills);
        }
    }
}
// เปลี่ยนจาก " | " เป็น <br> เพื่อเว้นบรรทัด
$skills_list_display = implode("<br>", $skills_display);

/* --- ดึงข้อมูล Hobby ที่นิสิตเลือก --- */
$student_hobbies = [];
$sql_student_hobby = "SELECT hobby_id, subhobby_id FROM student_hobby WHERE student_id = ?";
$stmt_hobby = $conn->prepare($sql_student_hobby);
$stmt_hobby->bind_param("s", $student_id);
$stmt_hobby->execute();
$result_student_hobby = $stmt_hobby->get_result();

while ($row = $result_student_hobby->fetch_assoc()) {
    $student_hobbies[$row['hobby_id']][] = $row['subhobby_id'];
}
$stmt_hobby->close();

/* สร้างข้อความแสดงผล Hobby (แสดงชื่อ hobby กับ subhobby) */
$hobby_display = [];
foreach ($all_hobbies as $hobby) {
    if (isset($student_hobbies[$hobby['hobby_id']])) {
        $selected_subhobbies = [];
        foreach ($hobby['subhobbies'] as $subhobby) {
            if (in_array($subhobby['subhobby_id'], $student_hobbies[$hobby['hobby_id']])) {
                $selected_subhobbies[] = $subhobby['subhobby_name'];
            }
        }
        if (!empty($selected_subhobbies)) {
            $hobby_display[] = $hobby['hobby_name'] . ": " . implode(", ", $selected_subhobbies);
        }
    }
}
$hobby_list_display = implode("<br>", $hobby_display);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Page</title>
    <!-- CSS & Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/stupfstyle.css">
    <link rel="stylesheet" href="css/header-footer.html">
    <!-- JSON data for notifications -->
    <script type="application/json" id="notifications-data">
        <?php echo json_encode($notifications); ?>
    </script>
</head>

<body>
    <!-- Profile Header & Review -->
    <div class="profile-container">
        <div class="header">
            <a href="javascript:history.back()"><i class="bi bi-chevron-left text-white h4"></i></a>
            <div class="profile">
                <img class="profile-pic" id="profile_picture" src="<?php echo "../" . htmlspecialchars($student['profile']);//รูปโปรไฟล์ตรงนี้มีน?>" alt="Profile Picture" style="cursor: default;">
                <input type="file" id="profile_image_input" style="display:none;" accept="image/*">
                <div class="detail-name">
                    <div class="name"><?php echo htmlspecialchars($student['stu_name'], ENT_QUOTES, 'UTF-8'); ?></div>
                    <div class="sub-title">สาขา <?php echo htmlspecialchars($student['major_name'], ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
            </div>
        </div>
        <div class="content">
            <div class="detail-head">
                <a href="review.php?student_id=<?php echo urlencode($student_id); ?>">
                    <div class="review">
                        <div class="rating bg-sumary"><?php echo number_format($calculation['avg_rating'], 1); ?></div>
                        <div class="review-detail">
                            <div class="stars">
                                <?php for ($i = 1; $i <= 5; $i++) {
                                    echo ($i <= $calculation['avg_rating']) ? '★' : '☆';
                                } ?>
                            </div>
                            <small>from <?php echo $calculation['total_groups']; ?> people</small>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
 
    <!-- Main Content Section -->
    <div class="container">
        <!-- Skills Section -->
        <h3>Skills</h3>
        <section class="skills">
            <!-- View Mode -->
            <p id="skills_text_display"><?php echo $skills_list_display; ?></p>
        </section>

        <!-- Hobby Section -->
        <h3>Hobby</h3>
        <section class="hobby">
            <!-- View Mode -->
            <p id="hobby_text_display"><?php echo $hobby_list_display; ?></p>
        </section>

    </div>
</body>

</html>
<?php $conn->close(); ?>