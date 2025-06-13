<?php
include 'database.php';
// รวมไฟล์คำนวณรีวิว
include 'calculate_review.php';
$student_id = isset($_GET['student_id']) ? $_GET['student_id'] : null;

// ตอนนี้ตัวแปร $calculation จะมีข้อมูลที่คำนวณไว้
// นอกจากนี้คุณอาจต้องการดึงข้อมูล notifications หรือข้อมูลเพิ่มเติมอื่นๆ ตามที่คุณต้องการ
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reviews & Ratings</title>
    <!-- Bootstrap CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- สไตล์สำหรับรีวิว -->
    <link rel="stylesheet" href="css/reviewstyle.css">
    <link rel="stylesheet" href="css/header-footerstyle.css">
    <script type="application/json" id="notifications-data">
        <?php // หากมีการดึงข้อมูล notifications ให้แสดงในรูปแบบ JSON 
        ?>
    </script>
    <style>
  
    </style>
</head>

<body>
    <nav class="review-head">
        <a href="javascript:history.back()"><i class="bi bi-chevron-left"></i></a>
        <h1 class="review-head-text">Review</h1>
    </nav>
    <div class="content">
        <!-- แสดงรีวิว -->
        <div class="container">
            <?php foreach ($grouped_reviews as $group): ?>
                <div class="review-card">
                    <div class="user-info">
                        <div class="user-icon">👤</div>
                        <div class="user-details">
                            <span><?php echo htmlspecialchars($group['teacher_name']); ?></span>
                            <span><?php echo htmlspecialchars($group['title']); ?></span>
                            <!-- แสดงหมวดรีวิวและคะแนน -->
                            <div class="reviews-cat-grid">
                                <?php foreach ($group['review_category_names'] as $index => $cat_name): ?>
                                    <!-- แสดงทุกหมวดที่มีคะแนน (รวมถึงหมวดอื่น ๆ ที่อาจมีคะแนน) -->
                                    <div class="review-cat-item">
                                        <?php echo htmlspecialchars($cat_name); ?> - ★ <?php echo number_format($group['ratings'][$index], 1); ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <!-- แสดงคอมเมนต์จากรีวิวที่มีคะแนน -->
                            <?php if (!empty($group['comment'])): ?>
                                <span class="comment-box"><?php echo nl2br(htmlspecialchars($group['comment'])); ?></span>
                            <?php endif; ?>
                            <!-- แสดงคอมเมนต์เพิ่มเติมจากรีวิวที่เป็นความคิดเห็น (review_category_id = 1) -->
                            <?php if (!empty($group['comments_cat1'])): ?>
                                <span class="comment-box"><?php echo nl2br(htmlspecialchars(implode(" ", array_unique($group['comments_cat1'])))); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="review-score">★ <?php echo number_format($group['avg_rating'], 1); ?></div>
                </div>
            <?php endforeach; ?>
        </div>
 <!-- สรุปคะแนน (Summary) -->
 <div class="summary">
            <h4>รีวิวจากอาจารย์ (<?php echo $total_groups; ?>)</h4>
            <div class="bg-sumary">
                <div class="average"><?php echo number_format($group_avg_rating, 1); ?></div>
                <div class="fullscore">จาก <?php echo $max_possible_rating; ?> คะแนน</div>
            </div>
            <div class="category-breakdown">
                <?php foreach ($category_averages as $cat_id => $cat_data): ?>
                    <div class="category-item" style="margin-bottom: 10px;">
                        <!-- แสดงชื่อหมวดรีวิว -->
                        <div class="category-name" style="font-weight: bold;">
                            <?php echo htmlspecialchars($cat_data['review_category_name']); ?>
                        </div>
                        <!-- แสดงดาวตามค่าเฉลี่ย -->
                        <div class="category-rating">
                            <?php
                            // ค่าเฉลี่ยคะแนนของหมวดนี้
                            $avg = $cat_data['average'];
                            // ปัดค่าเฉลี่ยเป็นจำนวนเต็มเพื่อใช้แสดงดาว (สามารถปรับเปลี่ยนได้ตามที่ต้องการ)
                            $rounded = round($avg);
                            for ($i = 1; $i <= 5; $i++) {
                                echo ($i <= $rounded) ? '<i class="bi bi-star-fill"></i>' : '<i class="bi bi-star-fill graystar"></i>';
                            }
                            ?>
                            <span style="margin-left: 5px;">(<?php echo $avg; ?>)</span>
                        </div>
                        <!-- แสดงจำนวนรีวิวในหมวดนี้ -->
                        <div class="category-count" style="font-size: 0.9rem; color: #666;">
                            (<?php echo $cat_data['count']; ?> รีวิว)
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<?php
$conn->close();
?>