<?php
session_start();
// เชื่อมต่อฐานข้อมูล
include 'database.php';

// ตรวจสอบและอัปเดตสถานะแจ้งเตือนเมื่อมีการส่งค่าผ่าน URL (GET)
if (isset($_GET['id'])) {
    $notification_id = intval($_GET['id']); // ป้องกัน SQL Injection

    $update_sql = "UPDATE notification SET status = 'read' WHERE notifications_id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("i", $notification_id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "id" => $notification_id]);
    } else {
        echo json_encode(["success" => false, "error" => $conn->error]);
    }

    $stmt->close();
    $conn->close();
    exit(); // หยุดการทำงานของ PHP
}


$user_id = $_SESSION['user_id'];


// ตรวจสอบว่า user มีอยู่ในฐานข้อมูลหรือไม่
$user_sql = "SELECT user_id FROM user WHERE user_id = ?";
$stmt = $conn->prepare($user_sql);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();


function getNotifications($conn, $user_id)
{
    $sql = "SELECT notification.notifications_id AS notifications_id, 
                   notification.message, 
                   notification.created_at, 
                   notification.status, 
                   accepted_application.accept_status_id, 
                   accept_status.accept_status_name
            FROM notification
            JOIN accepted_application ON notification.reference_id = accepted_application.accepted_app_id
            JOIN accept_status ON accepted_application.accept_status_id = accept_status.accept_status_id
            WHERE notification.user_id = ? 
            ORDER BY notification.created_at DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $user_id);  // Binding user_id with parameter
    $stmt->execute();
    $result = $stmt->get_result();

    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $notifications[] = [
            'id' => $row['notifications_id'],
            'title' => $row['accept_status_name'],
            'message' => $row['message'],
            'time' => $row['created_at'],
            'status' => strtolower($row['status']),
            'accept_status_id' => $row['accept_status_id'] ?? null
        ];
    }
    return $notifications;
}
$notifications = getNotifications($conn, $user_id);

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


// ✅ อัปเดตสถานะแจ้งเตือนเป็น "read"
if (isset($_GET['id'])) {
    $notification_id = intval($_GET['id']);
    $update_sql = "UPDATE notification SET status = 'read' WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("s", $notification_id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "id" => $notification_id]);
    } else {
        echo json_encode(["success" => false, "error" => $conn->error]);
    }
    $stmt->close();
    $conn->close();
    exit();
}
// ✅ โหลดแจ้งเตือนทั้งหมด (เมื่อโหลดหน้าเว็บ)
$notifications = getNotifications($conn, $user_id);
$unread_count = count(array_filter($notifications, function ($n) {
    return $n['status'] === 'unread';
}));


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
    <link rel="stylesheet" href="css/header-footer.html">
    <script type="application/json" id="notifications-data">
        <?php echo json_encode($notifications); ?>
    </script>
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
                <a href="review.php">
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
                <div>
                    <button class="notification-btn">
                        <i class="bi bi-bell"></i>
                        <span class="notification-badge" <?php echo ($unread_count == 0) ? 'style="display:none;"' : ''; ?>>
                            <?php echo $unread_count; ?>
                        </span>
                        <button class="edit-button" onclick="toggleEdit()">Edit</button>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- Content Section -->
    <!--พวกรีวิวแจ้งเตือนและปุ่มแก้ไข-->
    <div class="notifications-card" id="notifications">
        <div class="headerNoti">
            <h1 class="page-title">Notifications</h1>
            <span class="notification-count" <?php echo ($unread_count == 0) ? 'style="display:none;"' : ''; ?>>
                <?php echo $unread_count; ?> new</span>
            <button class="close-button" id="close-notifications">&times;</button>
        </div>
        <div class="tabs">
            <div class="tab active" data-filter="all">All</div>
            <div class="tab" data-filter="unread">Unread</div>
            <div class="tab" data-filter="accepted">Accepted</div>
            <div class="tab" data-filter="reject">Rejected</div>
        </div>
        <div class="notification-list" id="notification-list">
            <?php foreach ($notifications as $notification) { ?>
                <a href="viewnoti.php?id=<?php echo $notification['id']; ?>" class="notification-item" data-status="<?php echo $notification['status']; ?>">
                    <div class="notification-content">
                        <h3 class="notification-title"><?php echo $notification['title']; ?></h3>
                        <p class="notification-message"><?php echo $notification['message']; ?></p>
                        <span class="notification-time"><?php echo $notification['time']; ?></span>
                    </div>
                </a>
            <?php } ?>
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

        <button class="save-button" style="display:none;" onclick="saveChanges()">Save</button>
    </div>



    </div>
    <footer class="footer">
        <p>© CSIT - Computer Science and Information Technology</p>
    </footer>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const tabs = document.querySelectorAll(".tab");
            const notificationList = document.getElementById("notification-list");
            const notificationBadge = document.querySelector(".notification-badge");
            const notificationCount = document.querySelector(".notification-count");

            // ฟังก์ชันเพื่อดึงข้อมูลแจ้งเตือนจากเซิร์ฟเวอร์
            function fetchNotifications(filterType) {
                fetch(window.location.href) // โหลดข้อมูลจากไฟล์เดียวกัน
                    .then(response => response.text())
                    .then(html => {
                        let parser = new DOMParser();
                        let doc = parser.parseFromString(html, "text/html");
                        let notifications = JSON.parse(doc.getElementById("notifications-data").textContent);
                        updateNotifications(notifications, filterType);
                    })
                    .catch(error => console.error("Error fetching notifications:", error));
            }

            // ฟังก์ชันอัปเดต UI ของการแจ้งเตือน
            function updateNotifications(notifications, filterType) {
                const notificationList = document.getElementById("notification-list");
                notificationList.innerHTML = ""; // เคลียร์รายการเดิม
                let unreadCount = 0;

                notifications.forEach((notification, index) => {
                    // ฟิลเตอร์การแสดงผลตามประเภทต่าง ๆ
                    if (filterType === "all" ||
                        (filterType === "unread" && notification.status === "unread") ||
                        (filterType === "accepted" && notification.title === "Accepted") ||
                        (filterType === "reject" && notification.title === "Rejected")) {

                        const notificationItem = document.createElement("div");
                        notificationItem.classList.add("notification-item", notification.status);
                        notificationItem.setAttribute("data-status", notification.status);
                        notificationItem.setAttribute("data-id", notification.id);
                        notificationItem.innerHTML = `
                    <div class="notification-content">
                        <h3 class="notification-title">${notification.title}</h3>
                        <p class="notification-message">${notification.message}</p>
                        <span class="notification-time">${notification.time}</span>
                    </div>
                `;
                        // เพิ่ม event listener ให้สามารถคลิกได้ทุกสถานะ
                        notificationItem.addEventListener("click", function(e) {
                            e.preventDefault(); // ป้องกันไม่ให้ลิงก์ทำงานทันที

                            // เปลี่ยนสถานะเป็น "read" ทุกครั้งที่คลิก
                            markAsRead(notification.id);

                            // หลังจากการคลิกให้ลิงก์ไปที่ viewnoti.php
                            setTimeout(function() {
                                window.location.href = "viewnoti.php?id=" + notification.id; // ไปยังหน้า viewnoti.php พร้อมกับส่ง id ของการแจ้งเตือน
                            }, 100); // 100ms delay เพื่อให้สถานะถูกอัปเดตก่อนเปลี่ยนหน้า
                        });

                        // ถ้าเป็น unread ให้เพิ่ม event listener
                        if (notification.status === "unread") {
                            notificationItem.addEventListener("click", function(e) {
                                e.preventDefault(); // ป้องกันไม่ให้ลิงก์ทำงานทันที
                                markAsRead(notification.id); // เปลี่ยนสถานะเป็น "read"
                            });
                            unreadCount++; // นับจำนวน unread
                        }

                        notificationList.appendChild(notificationItem);
                    }
                });

                // อัปเดต badge และ notification count
                if (unreadCount > 0) {
                    notificationBadge.innerText = unreadCount;
                    notificationBadge.style.display = "inline-block";
                    notificationCount.innerText = `${unreadCount} new`;
                    notificationCount.style.display = "inline-block";
                } else {
                    notificationBadge.style.display = "none";
                    notificationCount.style.display = "none";
                }
            }

            // ฟังก์ชันเพื่อเปลี่ยนสถานะการแจ้งเตือนเป็น "read"
            function markAsRead(notificationId) {
                fetch(`?id=${notificationId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            console.log("Notification marked as read:", notificationId);

                            // ค้นหา notification item ที่คลิก
                            let notificationItem = document.querySelector(`.notification-item[data-id="${notificationId}"]`);
                            if (notificationItem) {
                                // ถ้าอยู่ในแท็บ Unread, ลบออกทันที
                                let activeTab = document.querySelector(".tab.active").getAttribute("data-filter");
                                if (activeTab === "unread") {
                                    notificationItem.remove(); // ลบแจ้งเตือนออกจาก Unread
                                } else {
                                    // ถ้าอยู่ในแท็บอื่นให้เปลี่ยนเป็น Read
                                    notificationItem.dataset.status = "read";
                                    notificationItem.classList.remove("unread");
                                    notificationItem.classList.add("read");
                                }
                            }

                            // อัปเดต badge และ notification count
                            let currentCount = parseInt(notificationBadge.innerText) || 0;
                            if (currentCount > 0) {
                                currentCount--;
                                notificationBadge.innerText = currentCount;
                                notificationCount.innerText = `${currentCount} new`;

                                if (currentCount === 0) {
                                    notificationBadge.style.display = "none";
                                    notificationCount.style.display = "none";
                                }
                            }

                            // แท็บ Unread อัปเดตให้แสดงเป็น "Unread" โดยไม่มีตัวเลข
                            let unreadTab = document.querySelector(".tab[data-filter='unread']");
                            if (unreadTab) {
                                unreadTab.innerText = "Unread";
                            }
                        }
                    })
                    .catch(error => console.error("Error updating notification:", error));
            }

            // ฟังก์ชันคลิกแท็บเพื่อกรองการแจ้งเตือน
            tabs.forEach(tab => {
                tab.addEventListener("click", function() {
                    tabs.forEach(t => t.classList.remove("active"));
                    this.classList.add("active");

                    const filterType = this.getAttribute("data-filter");
                    fetchNotifications(filterType); // โหลดข้อมูลใหม่ทุกครั้งที่เปลี่ยนแท็บ
                });
            });

            // โหลดแจ้งเตือนทั้งหมดเมื่อเริ่มต้น
            fetchNotifications("all");
        });
    </script>
    <script>
        const notificationButton = document.querySelector('.notification-btn');
        const notificationsCard = document.getElementById('notifications');
        const closeButton = document.getElementById('close-notifications');

        notificationButton.addEventListener('click', () => {
            notificationsCard.style.display = 'block';
        });

        closeButton.addEventListener('click', () => {
            notificationsCard.style.display = 'none';
        });

        document.addEventListener('click', (event) => {
            if (!notificationsCard.contains(event.target) && !notificationButton.contains(event.target)) {
                notificationsCard.style.display = 'none';
            }
        });
    </script>
    <script>
        function toggleEdit() {
            // สำหรับ skills: display text กับ checkbox list
            const skillsDisplay = document.getElementById('skills_text_display');
            const skillsEdit = document.getElementById('skills_checkbox_list');
            // Container ช่องค้นหา
            const skillsSearchContainer = document.getElementById('skills_search_container');

            // สำหรับ interest: display text กับ checkbox list
            const interestDisplay = document.getElementById('interest_text_display');
            const interestEdit = document.getElementById('interest_checkbox_list');
            // Container ช่องค้นหา
            const interestSearchContainer = document.getElementById('interest_search_container');

            // สำหรับ about ยังคงใช้ textarea เดิม
            const aboutDisplay = document.getElementById('about_text_display');
            const aboutEdit = document.getElementById('about_text_edit');

            // สลับการแสดงสำหรับ skills
            if (skillsDisplay.style.display !== "none") {
                skillsDisplay.style.display = "none";
                skillsEdit.style.display = "block";
                skillsSearchContainer.style.display = "block"; // แสดงช่องค้นหา
            } else {
                skillsDisplay.style.display = "block";
                skillsEdit.style.display = "none";
                skillsSearchContainer.style.display = "none"; // ซ่อนช่องค้นหา
            }

            // สลับการแสดงสำหรับ interest
            if (interestDisplay.style.display !== "none") {
                interestDisplay.style.display = "none";
                interestEdit.style.display = "block";
                interestSearchContainer.style.display = "block"; // แสดงช่องค้นหา
            } else {
                interestDisplay.style.display = "block";
                interestEdit.style.display = "none";
                interestSearchContainer.style.display = "none"; // ซ่อนช่องค้นหา
            }

            // สลับการแสดงสำหรับ about
            if (aboutDisplay.style.display !== "none") {
                aboutDisplay.style.display = "none";
                aboutEdit.style.display = "block";
            } else {
                aboutDisplay.style.display = "block";
                aboutEdit.style.display = "none";
            }

            // สลับแสดงปุ่ม Save
            const saveButton = document.querySelector('.save-button');
            saveButton.style.display = saveButton.style.display === "none" ? "inline-block" : "none";
        }



        function saveChanges() {
            // ดึงค่าจาก checkbox ที่ถูกเลือก (สำหรับ skills)
            let skillsCheckboxes = document.querySelectorAll('input[name="skills[]"]:checked');
            let selectedSkills = Array.from(skillsCheckboxes).map(cb => cb.value).join(',');

            // ดึงค่าจาก checkbox ที่ถูกเลือก (สำหรับ interest)
            let interestCheckboxes = document.querySelectorAll('input[name="interest[]"]:checked');
            let selectedInterests = Array.from(interestCheckboxes).map(cb => cb.value).join(',');

            let aboutText = document.getElementById('about_text_edit').value;

            let xhr = new XMLHttpRequest();
            xhr.open("POST", "update_profile.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    // อัปเดตข้อความที่แสดงโดยอ้างอิงจาก checkbox ที่ถูกเลือก (skills)
                    let skillsTextDisplay = Array.from(skillsCheckboxes)
                        .map(cb => cb.parentElement.textContent.trim())
                        .join(', ');
                    document.getElementById('skills_text_display').innerText = skillsTextDisplay;

                    // อัปเดตข้อความที่แสดงสำหรับ interest
                    let interestTextDisplay = Array.from(interestCheckboxes)
                        .map(cb => cb.parentElement.textContent.trim())
                        .join(', ');
                    document.getElementById('interest_text_display').innerText = interestTextDisplay;

                    document.getElementById('about_text_display').innerText = aboutText;
                    toggleEdit();
                }
            };

            xhr.send("skills_text=" + encodeURIComponent(selectedSkills) +
                "&interest_text=" + encodeURIComponent(selectedInterests) +
                "&about_text=" + encodeURIComponent(aboutText));
        }

        document.getElementById('skills_search').addEventListener('keyup', function() {
            var filter = this.value.toLowerCase();
            var labels = document.querySelectorAll('#skills_checkbox_list label');
            labels.forEach(function(label) {
                if (label.textContent.toLowerCase().indexOf(filter) > -1) {
                    label.style.display = "";
                } else {
                    label.style.display = "none";
                }
            });
        });

        document.getElementById('interest_search').addEventListener('keyup', function() {
            var filter = this.value.toLowerCase();
            var labels = document.querySelectorAll('#interest_checkbox_list label');
            labels.forEach(function(label) {
                if (label.textContent.toLowerCase().indexOf(filter) > -1) {
                    label.style.display = "";
                } else {
                    label.style.display = "none";
                }
            });
        });
    </script>

</body>

</html>
<?php $conn->close(); ?>