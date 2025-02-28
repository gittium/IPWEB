<?php
include 'database.php';


// รับค่า id จาก URL
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $user_id = $_GET['id'];
} 

//การดึงข้อมูลนักศึกษา พร้อมข้อมูล skills, interest, และ other
$sql = "
SELECT s.students_id, s.name, s.email, s.major_id, s.year, 
       m.major_name, 
       s.skills, s.interest, s.other, 
       GROUP_CONCAT(DISTINCT sk.skills_name ORDER BY sk.skills_id SEPARATOR ', ') AS skills_list,
       GROUP_CONCAT(DISTINCT i.interests_name ORDER BY i.interests_id SEPARATOR ', ') AS interest_list
FROM students s
JOIN major m ON s.major_id = m.major_id
LEFT JOIN skills sk ON FIND_IN_SET(sk.skills_id, s.skills) > 0
LEFT JOIN interests i ON FIND_IN_SET(i.interests_id, s.interest) > 0
WHERE s.students_id = ?
GROUP BY s.students_id
";


$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_id);  // ผูกค่าพารามิเตอร์ user_id
$stmt->execute();
$result = $stmt->get_result();

// ตรวจสอบข้อมูลและเก็บไว้ในตัวแปร $student
if ($result->num_rows > 0) {
    $student = $result->fetch_assoc(); // เก็บข้อมูลของนักศึกษาในตัวแปร $student
} else {
    echo "ไม่พบข้อมูลนักศึกษาที่ตรงกับ ID";
}




// ดึงข้อมูลรีวิวทั้งหมดของนิสิตพร้อมกับคำนวณค่าเฉลี่ยจาก rating ทั้งหมด
$sql = "SELECT AVG(rating) AS avg_rating, COUNT(*) AS review_count 
        FROM reviews 
        WHERE students_id = ? AND reviews_cat_id BETWEEN 1 AND 5"; // เฉพาะ category ที่เป็น 1 ถึง 5
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_id); // ใช้ 's' เพราะ students_id เป็น varchar
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $avg_rating = round($row['avg_rating'], 1); // ปัดเศษค่าคะแนนเฉลี่ย
    $review_count = ceil($row['review_count'] / 5); // แบ่งจำนวนรีวิวทั้งหมดด้วย 5
} else {
    $avg_rating = 0; // ถ้าไม่มีรีวิว ให้คะแนนเป็น 0
    $review_count = 0; // จำนวนรีวิวเป็น 0
}

//echo "User ID: " . $_SESSION['user_id'];
//echo '<pre>';
//print_r($notifications);
//echo '</pre>';

// ดึงรายการ skills ทั้งหมด
$sql_skills = "SELECT skills_id, skills_name FROM skills";
$result_skills = $conn->query($sql_skills);
$all_skills = [];
if ($result_skills->num_rows > 0) {
    while ($row = $result_skills->fetch_assoc()) {
        $all_skills[] = $row;
    }
}

// ดึงรายการ interests ทั้งหมด
$sql_interests = "SELECT interests_id, interests_name FROM interests";
$result_interests = $conn->query($sql_interests);
$all_interests = [];
if ($result_interests->num_rows > 0) {
    while ($row = $result_interests->fetch_assoc()) {
        $all_interests[] = $row;
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="css/stupfstyle.css">
    <style>
        /* จัดรูปแบบสำหรับ container checkbox (Skills และ Interest) */
        #skills_checkbox_list,
        #interest_checkbox_list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 8px;
            padding: 10px;
            background-color: #f9f9f9;
            border: 1px solid #ccc;
            border-radius: 4px;
            max-height: 200px;
            /* กำหนดความสูงสูงสุด */
            overflow-y: auto;
            /* เลื่อน scrollbar หากมี checkbox จำนวนมาก */
        }

        /* จัดรูปแบบสำหรับแต่ละ checkbox label */
        #skills_checkbox_list label,
        #interest_checkbox_list label {
            display: flex;
            align-items: center;
            padding: 4px 8px;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        /* เปลี่ยนสีพื้นหลังเมื่อ hover */
        #skills_checkbox_list label:hover,
        #interest_checkbox_list label:hover {
            background-color: #eef;
        }

        /* ปรับระยะห่างระหว่าง checkbox กับข้อความ */
        #skills_checkbox_list input[type="checkbox"],
        #interest_checkbox_list input[type="checkbox"] {
            margin-right: 6px;
        }
    </style>

</head>

<body>
    <!-- รีวิว -->
    <div class="profile-container">
        <div class="header">
            <a href="javascript:history.back()"><i class="bi bi-chevron-left text-white h4 "></i></a>
            <div class="profile">
                <div class="profile-pic">
                    <?php echo strtoupper(mb_substr($student['name'], 0, 1, 'UTF-8')); ?>
                </div>
                <div class="detail-name">
                    <div class="name"><?php echo htmlspecialchars($student['name'], ENT_QUOTES, 'UTF-8'); ?></div>
                    <div class="sub-title">สาขา <?php echo htmlspecialchars($student['major_name'], ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
            </div>
        </div>

        <div class="content">
            <div class="detail-head">
                <a href="review.php?id=<?php echo $user_id; ?>">
                    <div class="review">
                        <div class="rating bg-sumary"><?php echo $avg_rating; ?></div>
                        <div class="review-detail">
                            <div class="stars">
                                <?php
                                // สร้างดาวตามคะแนนเฉลี่ย
                                for ($i = 1; $i <= 5; $i++) {
                                    if ($i <= $avg_rating) {
                                        echo '★'; // ถ้า i <= avg_rating ให้แสดงดาวเต็ม
                                    } else {
                                        echo '☆'; // ถ้า i > avg_rating ให้แสดงดาวว่าง
                                    }
                                }
                                ?>
                            </div>
                            <small>from <?php echo $review_count; ?> people</small>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
    
    <!-- ส่วนเนื้อหา -->
    <div class="container">
        <h3>Skills</h3>
        <section class="skills">
            <!-- แสดงรายการ skills ที่เลือก (เป็นข้อความ) -->
            <p id="skills_text_display"><?php echo htmlspecialchars($student['skills_list'], ENT_QUOTES, 'UTF-8'); ?></p>

            <!-- Container ช่องค้นหา (ซ่อนอยู่เริ่มต้น) -->
            <div id="skills_search_container" style="display:none; margin-bottom: 10px;">
                <input type="text" id="skills_search" placeholder="ค้นหา skills..." style="padding: 6px; width: 100%;">
            </div>

            <!-- ส่วนแก้ไขแบบ checkbox (ซ่อนอยู่ก่อน) -->
            <div id="skills_checkbox_list" style="display:none;">
                <?php
                // แปลงค่าสกิลที่นักศึกษามี (เก็บเป็น id ในฟิลด์ s.skills) ให้อยู่ในรูปแบบ array
                $student_skills = array_map('trim', explode(',', $student['skills']));
                foreach ($all_skills as $skill): ?>
                    <label>
                        <input type="checkbox" name="skills[]" value="<?php echo $skill['skills_id']; ?>"
                            <?php echo in_array($skill['skills_id'], $student_skills) ? 'checked' : ''; ?>>
                        <?php echo htmlspecialchars($skill['skills_name'], ENT_QUOTES, 'UTF-8'); ?>
                    </label><br>
                <?php endforeach; ?>
            </div>
        </section>


        <h3>Interest</h3>
        <section class="interest">
            <!-- แสดงรายการ interest ที่เลือก (เป็นข้อความ) -->
            <p id="interest_text_display"><?php echo htmlspecialchars($student['interest_list'], ENT_QUOTES, 'UTF-8'); ?></p>

            <!-- Container ช่องค้นหา (ซ่อนอยู่เริ่มต้น) -->
            <div id="interest_search_container" style="display:none; margin-bottom: 10px;">
                <input type="text" id="interest_search" placeholder="ค้นหา interest..." style="padding: 6px; width: 100%;">
            </div>

            <!-- ส่วนแก้ไขแบบ checkbox (ซ่อนอยู่ก่อน) -->
            <div id="interest_checkbox_list" style="display:none;">
                <?php
                // แปลงค่าสนใจของนักศึกษา (จาก s.interest) ให้อยู่ในรูปแบบ array
                $student_interests = array_map('trim', explode(',', $student['interest']));
                foreach ($all_interests as $interest): ?>
                    <label>
                        <input type="checkbox" name="interest[]" value="<?php echo $interest['interests_id']; ?>"
                            <?php echo in_array($interest['interests_id'], $student_interests) ? 'checked' : ''; ?>>
                        <?php echo htmlspecialchars($interest['interests_name'], ENT_QUOTES, 'UTF-8'); ?>
                    </label><br>
                <?php endforeach; ?>
            </div>
        </section>




        <h3>Other</h3>
        <section class="about-me">
            <!-- แสดงข้อมูลปกติ -->
            <p id="about_text_display"><?php echo htmlspecialchars($student['other'], ENT_QUOTES, 'UTF-8'); ?></p>
            <!-- ฟอร์มให้แก้ไขข้อมูล -->
            <textarea class="text_edit" id="about_text_edit" style="display:none;"><?php echo htmlspecialchars($student['other'], ENT_QUOTES, 'UTF-8'); ?></textarea>
        </section>
    </div>



    </div>
    
</body>

</html>
<?php $conn->close(); ?>