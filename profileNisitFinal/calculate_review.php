<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'database.php';

// ตรวจสอบว่าผู้ใช้ล็อกอินอยู่หรือไม่
if (!isset($_SESSION['user_id'])) {
    // รับ user_id จาก URL (GET) ถ้าไม่มี session
    $user_id = isset($_GET['user_id']) ? $_GET['user_id'] : null;
} else {
    // รับ user_id จาก session
    $user_id = $_SESSION['user_id'];
}


/* ============================================================
   1. ดึงข้อมูลรีวิวของนักศึกษา (พร้อมข้อมูลอาจารย์และงาน)
   ============================================================ */
// ใช้ตาราง review (ไม่ใช่ reviews) และตาราง post_job (ไม่ใช่ post_jobs)
// ในที่นี้ เงื่อนไขคือ student_id ต้องตรงกับ $user_id
$sql = "SELECT r.rating, r.comment, r.review_category_id, rc.review_category_name, 
               t.teacher_id, t.teach_name AS teacher_name, r.created_at, 
               pj.post_job_id, pj.title
        FROM review r
        LEFT JOIN teacher t ON r.teacher_id = t.teacher_id
        LEFT JOIN post_job pj ON r.post_job_id = pj.post_job_id
        JOIN review_category rc ON r.review_category_id = rc.review_category_id
        WHERE r.student_id = ?
        ORDER BY r.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_id); // student_id เป็น VARCHAR
$stmt->execute();
$result = $stmt->get_result();
$reviews = [];
while ($row = $result->fetch_assoc()) {
    $reviews[] = [
        'teacher_id'           => $row['teacher_id'],           // รหัสอาจารย์
        'teacher_name'         => $row['teacher_name'],         // ชื่ออาจารย์
        'post_job_id'          => $row['post_job_id'],          // รหัสงาน
        'title'                => $row['title'],                // ชื่องาน
        'comment'              => $row['comment'],              // คอมเมนต์
        'rating'               => $row['rating'],               // คะแนน
        'review_category_name' => $row['review_category_name'], // ชื่อหมวดรีวิว
        'review_category_id'   => $row['review_category_id']    // รหัสหมวดรีวิว
    ];
}
$stmt->close();

/* ============================================================
   2. ดึงข้อมูลรีวิวดิบเพื่อคำนวณคะแนนรวมและนับจำนวนคะแนน
   ============================================================ */
$sql = "SELECT rating, COUNT(*) as count 
        FROM review 
        WHERE student_id = ? 
        GROUP BY rating";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$rating_counts = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
$total_reviews = 0;
$total_score = 0;
while ($row = $result->fetch_assoc()) {
    $rating_counts[$row['rating']] = $row['count'];
    $total_reviews += $row['count'];
    $total_score += $row['rating'] * $row['count'];
}
$avg_rating = $total_reviews > 0 ? round($total_score / $total_reviews, 1) : 0;
$stmt->close();

/* ============================================================
   3. กำหนดค่าระดับคะแนนสูงสุด (max_possible_rating)
       (ในที่นี้กำหนดให้สูงสุดคือ 5 ดาว)
   ============================================================ */
$max_possible_rating = 5;

/* ============================================================
   4. จัดกลุ่มรีวิวตาม teacher_id และ post_job_id
       โดยกลุ่มจะรวมรีวิวที่ไม่ใช่หมวดความคิดเห็น (review_category_id != 1)
       และเก็บคอมเมนต์ของหมวดความคิดเห็น (review_category_id == 1) ไว้ใน key 'comments_cat1'
   ============================================================ */
$grouped_reviews = [];
foreach ($reviews as $review) {
    $key = $review['teacher_id'] . '|' . $review['post_job_id'];
    if (!isset($grouped_reviews[$key])) {
        $grouped_reviews[$key] = [
            'teacher_id'            => $review['teacher_id'],
            'teacher_name'          => $review['teacher_name'],
            'post_job_id'           => $review['post_job_id'],
            'title'                 => $review['title'],
            'comments'              => [], // สำหรับรีวิวที่ให้คะแนน
            'review_category_names' => [], // สำหรับรีวิวที่ให้คะแนน
            'review_category_ids'   => [], // สำหรับรีวิวที่ให้คะแนน
            'ratings'               => [], // สำหรับรีวิวที่ให้คะแนน
            'comments_cat1'         => []  // สำหรับคอมเมนต์จากหมวดที่ไม่มีคะแนน (review_category_id = 1)
        ];
    }
    if ($review['review_category_id'] == 1) {
        // หมวดที่เป็นความคิดเห็น (rating = 0) ให้นำคอมเมนต์มาเก็บใน comments_cat1
        $grouped_reviews[$key]['comments_cat1'][] = $review['comment'];
    } else {
        $grouped_reviews[$key]['comments'][] = $review['comment'];
        $grouped_reviews[$key]['review_category_names'][] = $review['review_category_name'];
        $grouped_reviews[$key]['review_category_ids'][]   = $review['review_category_id'];
        $grouped_reviews[$key]['ratings'][] = $review['rating'];
    }
}

/* ============================================================
   5. คำนวณค่าเฉลี่ยคะแนนสำหรับแต่ละกลุ่มรีวิว (เฉพาะรีวิวที่ให้คะแนน)
       และรวมคอมเมนต์ที่ไม่ซ้ำกันในแต่ละกลุ่ม
   ============================================================ */
foreach ($grouped_reviews as &$group) {
    $count = count($group['ratings']);
    $group['avg_rating'] = $count > 0 ? round(array_sum($group['ratings']) / $count, 1) : 0;
    $group['comment'] = implode(" ", array_unique($group['comments']));
}
unset($group);

/* ============================================================
   6. สรุปข้อมูลรีวิวกลุ่มทั้งหมด (Summary)
       - นับจำนวนกลุ่มรีวิว
       - คำนวณค่าเฉลี่ยคะแนนเฉพาะกลุ่ม
       - แจกแจงจำนวนกลุ่มที่ได้คะแนนแต่ละระดับ
       - คำนวณเปอร์เซ็นต์ของแต่ละระดับคะแนนในกลุ่ม
   ============================================================ */
$total_groups = count($grouped_reviews);
$group_rating_counts = array_fill(1, $max_possible_rating, 0);
$group_total_score = 0;
foreach ($grouped_reviews as $group) {
    $avg = $group['avg_rating'];
    $rounded = round($avg);
    $rounded = max(1, min($max_possible_rating, $rounded));
    $group_rating_counts[$rounded]++;
    $group_total_score += $avg;
}
$group_avg_rating = $total_groups > 0 ? round($group_total_score / $total_groups, 1) : 0;
$group_rating_percentages = [];
for ($i = $max_possible_rating; $i >= 1; $i--) {
    $group_rating_percentages[$max_possible_rating - $i] = ($total_groups > 0) 
        ? ($group_rating_counts[$i] / $total_groups) * 100 
        : 0;
}

/* ============================================================
   7. คำนวณค่าเฉลี่ยคะแนนสำหรับแต่ละหมวดรีวิว (Category Averages)
       - เราจะไม่นับหมวดที่ review_category_id = 1 (ความคิดเห็น) เพราะคะแนนจะเป็น 0
   ============================================================ */
$category_ratings = []; // เก็บคะแนนรวมและจำนวนรีวิวสำหรับแต่ละหมวด
foreach ($reviews as $review) {
    // ข้ามหมวดที่เป็นความคิดเห็น (id = 1)
    if ($review['review_category_id'] == 1) {
        continue;
    }
    $cat_id = $review['review_category_id'];
    if (!isset($category_ratings[$cat_id])) {
        $category_ratings[$cat_id] = [
            'review_category_name' => $review['review_category_name'],
            'total' => 0,
            'count' => 0
        ];
    }
    $category_ratings[$cat_id]['total'] += $review['rating'];
    $category_ratings[$cat_id]['count']++;
}
$category_averages = [];
foreach ($category_ratings as $cat_id => $data) {
    $average = $data['count'] > 0 ? round($data['total'] / $data['count'], 1) : 0;
    $category_averages[$cat_id] = [
        'review_category_name' => $data['review_category_name'],
        'average' => $average,
        'count' => $data['count']
    ];
}

/* ============================================================
   8. รวมผลลัพธ์การคำนวณในอาเรย์สำหรับใช้งานต่อ
   ============================================================ */
$calculation = [
    'avg_rating' => $avg_rating,                         // ค่าเฉลี่ยจากรีวิวดิบ
    'rating_counts' => $rating_counts,                   // จำนวนรีวิวในแต่ละระดับคะแนน (raw review)
    'grouped_reviews' => $grouped_reviews,               // กลุ่มรีวิวที่จัดกลุ่มตาม teacher_id และ post_job_id
    'total_groups' => $total_groups,                     // จำนวนกลุ่มรีวิว
    'group_avg_rating' => $group_avg_rating,             // ค่าเฉลี่ยคะแนนเฉพาะกลุ่ม
    'group_rating_counts' => $group_rating_counts,       // จำนวนกลุ่มที่ได้คะแนนแต่ละระดับ
    'group_rating_percentages' => $group_rating_percentages, // เปอร์เซ็นต์ของแต่ละระดับคะแนนในกลุ่ม
    'category_averages' => $category_averages            // ค่าเฉลี่ยคะแนนในแต่ละหมวดรีวิว (ไม่รวมหมวด id=1)
];

// ตอนนี้ตัวแปร $calculation และ $category_averages พร้อมใช้งานแล้ว
// คุณสามารถ include ไฟล์นี้ในส่วนแสดงผล (review.php) ได้

?>
