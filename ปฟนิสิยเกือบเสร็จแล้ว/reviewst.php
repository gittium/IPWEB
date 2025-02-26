<?php
session_start();
include 'database.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$teachers_id = 'CSIT0132';

// ✅ โหลดรายการงานที่ปิดแล้ว
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    if ($_GET['action'] === 'get_jobs') {
        $sql = "SELECT post_jobs_id, title FROM post_jobs WHERE teachers_id = ? AND job_status_id = 2";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $teachers_id);
        $stmt->execute();
        echo json_encode($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
        exit();
    }

    if ($_GET['action'] === 'get_students') {
        $post_jobs_id = intval($_GET['post_jobs_id'] ?? 0);
        $sql = "SELECT s.students_id, s.name AS student_name
                FROM accepted_application aa
                JOIN students s ON aa.students_id = s.students_id
                WHERE aa.post_jobs_id = ? AND aa.accept_status_id = 1";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $post_jobs_id);
        $stmt->execute();
        echo json_encode($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
        exit();
    }

    if ($_GET['action'] === 'get_categories') {
        $sql = "SELECT reviews_cat_id, reviews_cat_name FROM reviews_categories";
        echo json_encode($conn->query($sql)->fetch_all(MYSQLI_ASSOC));
        exit();
    }
}

// ✅ บันทึกข้อมูลรีวิว
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $students_id = $_POST['students_id'] ?? '';
    $post_jobs_id = intval($_POST['post_jobs_id'] ?? 0);
    $comment = trim($_POST['comment_cat6'] ?? '');

    if (empty($students_id) || $post_jobs_id == 0) {
        echo json_encode(["success" => false, "error" => "ข้อมูลไม่ถูกต้อง"]);
        exit();
    }

    $categories_sql = "SELECT reviews_cat_id FROM reviews_categories";
    $categories_result = $conn->query($categories_sql);
    $categories = $categories_result->fetch_all(MYSQLI_ASSOC);

    $sql = "INSERT INTO reviews (post_jobs_id, students_id, teachers_id, reviews_cat_id, rating, comment) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    foreach ($categories as $category) {
        $reviews_cat_id = $category['reviews_cat_id'];
        $rating = ($reviews_cat_id == 6) ? 0 : intval($_POST["rating$reviews_cat_id"] ?? 0);
        $review_comment = ($reviews_cat_id == 6) ? $comment : '';

        if ($reviews_cat_id != 6 && ($rating < 1 || $rating > 5)) {
            echo json_encode(["success" => false, "error" => "กรุณาให้คะแนนทุกหมวดหมู่"]);
            exit();
        }

        $stmt->bind_param("isssis", $post_jobs_id, $students_id, $teachers_id, $reviews_cat_id, $rating, $review_comment);
        if (!$stmt->execute()) {
            echo json_encode(["success" => false, "error" => "SQL Error: " . $stmt->error]);
            exit();
        }
    }

    echo json_encode(["success" => true]);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>ให้คะแนนนิสิต</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/header-footerstyle.css">
    <style>
        #submitBtn {
    background-color: #4E2A84; /* สีม่วง */
    border-color: #4E2A84; /* สีขอบของปุ่ม */
}

#submitBtn:hover {
    background-color: #6A3E9F; /* เปลี่ยนสีเมื่อเอาเมาส์ไปวาง */
    border-color: #6A3E9F; /* สีขอบเมื่อวางเมาส์ */
}

h4.mt-3 {
    text-align: center; /* จัดข้อความให้ตรงกลาง */
}

        /* การ์ด (container) สำหรับข้อมูลรีวิว */
        .container {
            max-width: 600px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin: 20px auto;
            /* ให้การ์ดอยู่ตรงกลาง */
        }

        /* ส่วนที่ไม่อยู่ในการ์ด */
        .form-container {
            max-width: 600px;
            margin: 20px auto;
            /* ให้อยู่ตรงกลาง */
            padding: 20px;
        }

        .rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: flex-start;
        }

        .rating input {
            display: none;
        }

        .rating label {
            font-size: 24px;
            color: lightgray;
            cursor: pointer;
        }

        .rating input:checked~label,
        .rating label:hover,
        .rating label:hover~label {
            color: gold;
        }

        
    </style>
</head>

<body>

    <!-- ✅ Header -->
    <header class="headerTop">
        <div class="headerTopImg">
            <img src="logo.png" alt="Logo">
            <a href="#">Naresuan University</a>
        </div>
        <nav class="header-nav">
            <a href="index.php">หน้าหลัก</a>
            <a href="about.php">เกี่ยวกับ</a>
            <a href="contact.php">ติดต่อ</a>
        </nav>
    </header>

    <!-- ส่วนที่ไม่อยู่ในการ์ด -->
    <div class="form-container">
        <h2 class="text-center">ให้คะแนนนิสิต</h2>
        <form id="reviewForm">
            <label>เลือกงาน :</label>
            <select id="post_jobs_id" name="post_jobs_id" class="form-control" required>
                <option value="">-- กรุณาเลือกงาน --</option>
            </select>

            <label>นิสิต :</label>
            <select id="students_id" name="students_id" class="form-control" required disabled>
                <option value="">-- กรุณาเลือกงานก่อน --</option>
            </select>

            <h4 class="mt-3">คะแนน</h4>
            <div id="ratingContainer"></div>

            <button type="submit" id="submitBtn" class="btn btn-primary w-100 mt-3" disabled>ส่งรีวิว</button>
        </form>
        <p id="statusMessage" class="text-center mt-2"></p>
    </div>

    <!-- การ์ด (container) สำหรับข้อมูลรีวิว -->
    <div class="container">
        <!-- เนื้อหาที่จะอยู่ในการ์ด เช่น รายละเอียด หรือการแสดงผลอื่นๆ -->
    </div>

    <!-- ✅ Footer -->
    <footer class="footer">
        <p>© CSIT - Computer Science and Information Technology</p>
    </footer>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const jobSelect = document.getElementById("post_jobs_id");
            const studentSelect = document.getElementById("students_id");
            const ratingContainer = document.getElementById("ratingContainer");
            const submitButton = document.getElementById("submitBtn");

            fetch("?action=get_jobs")
                .then(res => res.json())
                .then(data => {
                    jobSelect.innerHTML = '<option value="">-- กรุณาเลือกงาน --</option>';
                    data.forEach(job => jobSelect.innerHTML += `<option value="${job.post_jobs_id}">${job.title}</option>`);
                });

            jobSelect.addEventListener("change", function() {
                studentSelect.innerHTML = '<option value="">-- กรุณาเลือกนิสิต --</option>';
                studentSelect.disabled = true;

                if (!jobSelect.value) return;

                fetch(`?action=get_students&post_jobs_id=${jobSelect.value}`)
                    .then(res => res.json())
                    .then(data => {
                        data.forEach(student => {
                            studentSelect.innerHTML += `<option value="${student.students_id}">${student.student_name}</option>`;
                        });
                        studentSelect.disabled = false;
                    });
            });

            fetch("?action=get_categories")
                .then(res => res.json())
                .then(categories => {
                    ratingContainer.innerHTML = "";
                    categories.forEach(cat => {
                        const div = document.createElement("div");

                        if (cat.reviews_cat_id == 6) {
                            div.innerHTML = `
                                <label>${cat.reviews_cat_name}</label>
                                <textarea name="comment_cat6" class="form-control"></textarea>
                            `;
                        } else {
                            div.innerHTML = `
                                <label>${cat.reviews_cat_name}</label>
                                <div class="rating">
                                    ${[5, 4, 3, 2, 1].map(val => `
                                        <input type="radio" name="rating${cat.reviews_cat_id}" value="${val}" id="star${cat.reviews_cat_id}_${val}">
                                        <label for="star${cat.reviews_cat_id}_${val}">&#9733;</label>
                                    `).join('')}
                                </div>
                            `;
                        }
                        ratingContainer.appendChild(div);
                    });

                    ratingContainer.addEventListener("change", validateForm);
                });

            studentSelect.addEventListener("change", validateForm);

            function validateForm() {
                let allRated = true;
                document.querySelectorAll(".rating input[type='radio']").forEach(input => {
                    if (!document.querySelector(`input[name="${input.name}"]:checked`)) {
                        allRated = false;
                    }
                });

                submitButton.disabled = !(studentSelect.value && allRated);
            }

            document.getElementById("reviewForm").addEventListener("submit", function(event) {
                event.preventDefault();
                const formData = new FormData(this);

                fetch("", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded"
                        },
                        body: new URLSearchParams(formData)
                    })
                    .then(res => res.json())
                    .then(data => {
                        document.getElementById("statusMessage").innerText = data.success ? "รีวิวถูกบันทึกแล้ว" : "❌ " + data.error;
                        if (data.success) this.reset();
                    });
            });
        });
    </script>

</body>

</html>