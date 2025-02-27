<?php
session_start();
include 'database.php';

// ตรวจสอบว่าผู้ใช้ล็อกอินอยู่หรือไม่
if (!isset($_SESSION['user_id'])) {
    $user_id = isset($_GET['id']) ? intval($_GET['id']) : null;
} else {
    $user_id = $_SESSION['user_id']; // ดึง user_id จาก session
}

// Query สำหรับดึงข้อมูลรีวิว (เพิ่ม teachers_id และ post_jobs_id สำหรับ grouping)
$sql = "SELECT r.rating, r.comment, r.reviews_cat_id, rc.reviews_cat_name, 
               t.teachers_id, t.name AS teacher_name, r.created_at, 
               pj.post_jobs_id, pj.title
        FROM reviews r
        LEFT JOIN teachers t ON r.teachers_id = t.teachers_id
        LEFT JOIN post_jobs pj ON r.post_jobs_id = pj.post_jobs_id
        JOIN reviews_categories rc ON r.reviews_cat_id = rc.reviews_cat_id
        WHERE r.students_id = ?
        ORDER BY r.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_id); // ใช้ "s" เนื่องจาก students_id เป็น VARCHAR
$stmt->execute();
$result = $stmt->get_result();
$reviews = [];

while ($row = $result->fetch_assoc()) {
    $reviews[] = [
        'teachers_id'       => $row['teachers_id'],       // รหัสอาจารย์
        'teacher_name'      => $row['teacher_name'],      // ชื่ออาจารย์
        'post_jobs_id'      => $row['post_jobs_id'],      // รหัสงาน
        'title'             => $row['title'],             // ชื่องาน
        'comment'           => $row['comment'],           // คอมเมนต์
        'rating'            => $row['rating'],            // คะแนน
        'reviews_cat_name'  => $row['reviews_cat_name'],  // ชื่อหมวดหมู่รีวิว
        'reviews_cat_id'    => $row['reviews_cat_id']     // รหัสหมวดหมู่รีวิว
    ];
}

// Query สำหรับนับคะแนนรีวิว (raw review)
$sql = "SELECT rating, COUNT(*) as count 
        FROM reviews 
        WHERE students_id = ? 
        GROUP BY rating";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_id);  // ใช้ "s" เนื่องจาก students_id เป็น VARCHAR
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

// คำนวณค่าเฉลี่ยจาก raw reviews (อาจใช้ในส่วนอื่นถ้าจำเป็น)
$avg_rating = $total_reviews > 0 ? round($total_score / $total_reviews, 1) : 0;

// จัดกลุ่มรีวิวตาม teachers_id และ post_jobs_id พร้อมรวม reviews_cat_name, reviews_cat_id และ ratings
// จัดกลุ่มรีวิวตาม teachers_id และ post_jobs_id พร้อมรวม reviews_cat_name, reviews_cat_id, ratings และ comments
$grouped_reviews = [];
foreach ($reviews as $review) {
    $key = $review['teachers_id'] . '|' . $review['post_jobs_id'];
    if (!isset($grouped_reviews[$key])) {
        $grouped_reviews[$key] = [
            'teachers_id'       => $review['teachers_id'],
            'teacher_name'      => $review['teacher_name'],
            'post_jobs_id'      => $review['post_jobs_id'],
            'title'             => $review['title'],
            'comments'          => [],              // เก็บคอมเมนต์ทั้งหมดในกลุ่ม
            'reviews_cat_names' => [],              // เก็บชื่อหมวดหมู่รีวิวเป็น array
            'reviews_cat_ids'   => [],              // เก็บรหัสหมวดหมู่รีวิวเป็น array
            'ratings'           => []               // เก็บคะแนนรีวิวของแต่ละหมวด
        ];
    }
    // เก็บข้อมูลรีวิวเข้าในกลุ่ม
    $grouped_reviews[$key]['comments'][] = $review['comment'];
    $grouped_reviews[$key]['reviews_cat_names'][] = $review['reviews_cat_name'];
    $grouped_reviews[$key]['reviews_cat_ids'][]   = $review['reviews_cat_id'];
    $grouped_reviews[$key]['ratings'][]           = $review['rating'];
}

// คำนวณค่าเฉลี่ย rating สำหรับแต่ละกลุ่มรีวิว และรวมคอมเมนต์ (ใช้เฉพาะรีวิวที่ไม่ซ้ำกัน)
foreach ($grouped_reviews as &$group) {
    $count = count($group['ratings']);
    $group['avg_rating'] = $count > 0 ? array_sum($group['ratings']) / $count+1 : 0;
    // รวมคอมเมนต์ทั้งหมด (กรองค่าซ้ำออก)
    $group['comment'] = implode("", array_unique($group['comments']));
}
unset($group); // ป้องกัน reference

// คำนวณ Summary จาก grouped reviews
$total_groups = count($grouped_reviews);
$group_rating_counts = array_fill(1, 5, 0);
$group_total_score = 0;
foreach ($grouped_reviews as $group) {
    // ใช้ค่าเฉลี่ยของกลุ่มรีวิวที่คำนวณไว้
    $avg = $group['avg_rating'];
    // ปัดค่าเฉลี่ยเป็นจำนวนเต็ม (1-5) เพื่อใช้ในการแจกแจง breakdown
    $rounded = round($avg);
    // ตรวจสอบให้แน่ใจว่าอยู่ในช่วง 1-5
    $rounded = max(1, min(5, $rounded));
    $group_rating_counts[$rounded]++;
    $group_total_score += $avg;
}
$group_avg_rating = $total_groups > 0 ? round($group_total_score / $total_groups, 1) : 0;

// คำนวณเปอร์เซ็นต์สำหรับแต่ละระดับดาวจากกลุ่มรีวิว
$group_rating_percentages = [];
for ($i = 5; $i >= 1; $i--) {
    $group_rating_percentages[5 - $i] = ($total_groups > 0) ? ($group_rating_counts[$i] / $total_groups) * 100 : 0;
}

unset($review); // ป้องกันปัญหา reference ใน loop
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reviews & Ratings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/reviewstyle.css">
    <link rel="stylesheet" href="css/header-footerstyle.css">
    <style>
        /* สไตล์สำหรับกล่องคอมเมนต์ */
        .review-card .user-details span.comment-box {
            display: block;
            padding: 10px 15px;
            margin-top: 10px;
            background-color: #f8f9fa;
            /* สีพื้นหลังอ่อน */
            border-left: 4px solid #0d6efd;
            /* เส้นสีฟ้าด้านซ้าย */
            border-radius: 4px;
            font-style: italic;
            color: #333;
        }

        /* ปรับปรุงการจัดวางของกล่องรีวิว */
        .review-card {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #e3e3e3;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            background-color: #fff;
            width: auto;
        }

        .review-card .user-details {
            margin-left: 10px;
        }

        .review-card .user-icon {
            font-size: 2rem;
            margin-right: 10px;
        }

        /* สไตล์สำหรับคะแนนรีวิว */
        .review-score {
            font-size: 1.5rem;
            font-weight: bold;
            color: #ffc107;
            margin-top: 10px;
        }

        .review-cat-item {
            padding: 8px;
            font-size: 0.9rem;
            color: #333;
        }
        .review-score{
            margin-right: 15px;
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
            // ตรวจสอบสถานะการล็อกอิน
            if (isset($_SESSION['user_id'])) {
                echo '<a href="logout.php">Logout</a>';
            } else {
                echo '<a href="login.php">Login</a>';
            }
            ?>
        </nav>
    </header>
    <nav class="review-head">
        <a href="javascript:history.back()"><i class="bi bi-chevron-left"></i></a>
        <h1 class="review-head-text">Review</h1>
    </nav>
    <div class="content">
        <!-- Reviews Section -->
        <div class="container">
            <?php foreach ($grouped_reviews as $group): ?>
                <div class="review-card">
                    <div class="user-info">
                        <div class="user-icon">👤</div>
                        <div class="user-details">
                            <span><?php echo htmlspecialchars($group['teacher_name']); ?></span>
                            <span><?php echo htmlspecialchars($group['title']); ?></span>
                            <!-- วนลูปแสดงหมวดหมู่รีวิวทีละรายการ (ไม่แสดงที่ reviews_cat_id = 6) -->
                            <div class="reviews-cat-grid">
                                <?php foreach ($group['reviews_cat_names'] as $index => $cat_name): ?>
                                    <?php if ($group['reviews_cat_ids'][$index] != 6): ?>
                                        <div class="review-cat-item">
                                            <?php echo htmlspecialchars($cat_name); ?> - ★ <?php echo number_format($group['ratings'][$index], 1); ?>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>

                            <!-- กล่องคอมเมนต์ที่ตกแต่ง -->
                            <?php if (!empty($group['comment'])): ?>
                                <span class="comment-box"><?php echo nl2br(htmlspecialchars($group['comment'])); ?></span>
                            <?php endif; ?>

                        </div>

                    </div>
                    <div class="review-score">★ <?php echo number_format($group['avg_rating'], 1); ?></div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Summary Section -->
        <div class="summary">
            <h4>รีวิวจากอาจารย์ (<?php echo $total_groups; ?>)</h4>
            <div class="bg-sumary">
                <div class="average"><?php echo number_format($group_avg_rating, 1); ?></div>
                <div class="fullscore">จาก 5 คะแนน</div>
            </div>
            <div class="breakdown">
                <?php for ($i = 5; $i >= 1; $i--): ?>
                    <div>
                        <span>
                            <?php for ($j = 1; $j <= 5; $j++): ?>
                                <i class="bi bi-star-fill <?php echo $j <= $i ? '' : 'graystar'; ?>"></i>
                            <?php endfor; ?>
                        </span>
                        <div class="bar">
                            <div class="fill" style="width: <?php echo number_format($group_rating_percentages[5 - $i], 2); ?>%;"></div>
                        </div>
                        <span>(<?php echo $group_rating_counts[$i]; ?>)</span>
                    </div>
                <?php endfor; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<?php
$stmt->close();
$conn->close();
?>