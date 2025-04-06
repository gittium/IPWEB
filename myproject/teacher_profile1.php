<?php
session_start();
include 'database.php';
// รับค่า user_id (มาจาก session หรือ request)
$user_id = $_SESSION['user_id'] ?? 0;

//(1) ตรวจจับ POST: อัปเดต Contact (teachers) + Job (post_jobs)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. รับค่า phone_number, email (Contact)
    $user_id     = $_SESSION['user_id'] ?? 0;
    $phone_number = $_POST['phone_number'] ?? '';
    $email        = $_POST['email'] ?? '';

    // 2. อัปเดตข้อมูลตาราง teachers
    $sqlTeacher = "UPDATE teachers
                   SET phone_number = ?,
                       email = ?
                   WHERE teachers_id = ?";  // เปลี่ยน `id` ให้ตรงกับคอลัมน์ที่ใช้ในฐานข้อมูล
    $stmtT = $conn->prepare($sqlTeacher);
    $stmtT->bind_param("sss", $phone_number, $email, $user_id);  // ใช้ "i" สำหรับ user_id ที่เป็น INTEGER

    if (!$stmtT->execute()) {
        echo "error_teachers";
        exit();
    }
    $stmtT->close();
    // ถ้าไม่มีปัญหาใด ๆ
    $conn->close();
    echo "success";
    exit();
}

// ตรวจสอบการอัปเดตสถานะการแจ้งเตือน
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['notification_id'])) {
    $notification_id = intval($_GET['notification_id']); // ป้องกัน SQL Injection
    if ($notification_id > 0 && $user_id > 0) {
        // ตรวจสอบค่าในฐานข้อมูล
        $sql = "UPDATE `notification` 
                SET `status` = 'read' 
                WHERE `notifications_id` = ? AND `user_id` = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $notification_id, $user_id); // ใช้ "ii" สำหรับ INTEGER

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Notification updated"]);
        } else {
            echo json_encode(["success" => false, "error" => "Database update failed"]);
        }
        $stmt->close();
    } else {
        echo json_encode(["success" => false, "error" => "Invalid ID or user"]);
    }
    exit();
}


// ✅ ดึงข้อมูลแจ้งเตือน
$sql = "SELECT notification.notifications_id AS notifications_id, 
               notification.message, 
               notification.created_at, 
               notification.status, 
               post_jobs.title AS job_title
        FROM notification
        JOIN job_application ON notification.reference_id = job_application.job_app_id
        JOIN post_jobs ON job_application.post_jobs_id = post_jobs.post_jobs_id
        WHERE notification.user_id = ?
        ORDER BY notification.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_id);  // ใช้ "i" สำหรับ user_id ที่เป็น INTEGER
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = [
        'id' => $row['notifications_id'],
        'title' => $row['job_title'],
        'message' => $row['message'],
        'time' => $row['created_at'],
        'status' => strtolower($row['status'])
    ];
}
$stmt->close();

// ✅ ดึงจำนวนแจ้งเตือนที่ยังไม่ได้อ่าน
$sql = "SELECT COUNT(*) AS unread_count FROM notification WHERE status = 'unread' AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_id);  // ใช้ "i" สำหรับ user_id ที่เป็น INTEGER
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$unread_count = $row ? $row['unread_count'] : 0;
$stmt->close();

// ส่ง JSON กลับไปให้ JavaScript
if (isset($_GET['fetch_notifications'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'notifications' => $notifications,
        'unread_count' => $unread_count
    ]);
    exit(); // **ออกจากสคริปต์ทันที** เพื่อให้ PHP ไม่โหลด HTML ด้านล่าง
}


// --3.2 ดึงข้อมูลอาจารย์ (Contact)
$sqlTeacher = "SELECT 
                  t.teachers_id,
                  t.name,
                  t.email,
                  t.major_id,
                  t.phone_number,
                  m.major_id,
                  m.major_name
               FROM teachers t
               JOIN major m ON t.major_id = m.major_id
               WHERE t.teachers_id = ?";
$stmtT = $conn->prepare($sqlTeacher);
$stmtT->bind_param("s", $user_id);  // ใช้ "i" สำหรับ user_id ที่เป็น INTEGER
$stmtT->execute();
$resT = $stmtT->get_result();
$teacher = $resT->fetch_assoc();
$stmtT->close();

// --3.4 ดึง job ของอาจารย์ (post_jobs)
$sqlJobs = "SELECT * 
            FROM post_jobs
            WHERE teachers_id = ?  
            ORDER BY created_at DESC";
$stmtJ = $conn->prepare($sqlJobs);
$stmtJ->bind_param("s", $user_id);  // ใช้ "i" สำหรับ user_id ที่เป็น INTEGER
$stmtJ->execute();
$resJobs = $stmtJ->get_result();
$jobs = [];
while ($rowJob = $resJobs->fetch_assoc()) {
    $jobs[] = $rowJob;
}
$stmtJ->close();

?>




<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Teacher Profile</title>

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
        crossorigin="anonymous">
    <!-- ไฟล์ CSS ของคุณ -->
    <link rel="stylesheet" href="css/header-footerstyle.css">
    <link rel="stylesheet" href="css/teacherprofilestyle.css">
</head>

<body>
    <div class="profile-container">

        <!-- Header -->
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
        <!-- End Header -->

        <!-- โปรไฟล์ส่วนบน -->
        <div class="header">
            <a href="hometest.php"><i class="bi bi-chevron-left text-white h4 "></i></a>
            <div class="profile">
                <div class="profile-pic">
                    <?php echo strtoupper(mb_substr($teacher['name'], 0, 1, 'UTF-8')); ?>
                </div>
                <div class="detail-name">
                    <div class="name">
                        <?php echo htmlspecialchars($teacher['name'], ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                    <div class="sub-title">
                        อาจารย์ภาควิชา <br> <?php echo htmlspecialchars($teacher['major_name'], ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Header Profile -->

        <!-- Content -->
        <div class="content">
            <div class="detail-head">
                <div class="review">
                    <div class="review-detail">
                        <!-- ถ้าจะแสดงคะแนน/รีวิว -->
                    </div>
                </div>
                <div>
                    <!-- Notification btn -->
                    <button class="notification-btn">
                        <i class="bi bi-bell"></i>
                        <span class="notification-badge" <?php echo ($unread_count == 0) ? 'style="display:none;"' : ''; ?>>
                            <?php echo $unread_count; ?>
                        </span>
                    </button>

                    <!-- Notifications card -->
                    <!-- Notifications card -->
                    <div class="notifications-card" id="notifications">
                        <div class="headerNoti">
                            <h1 class="page-title">Notifications</h1>
                            <span class="notification-count" <?php echo ($unread_count == 0) ? 'style="display:none;"' : ''; ?>>
                                <?php echo $unread_count; ?> new
                            </span>
                            <button class="close-button" id="close-notifications">&times;</button>
                        </div>

                        <!-- Tabs for filtering notifications -->
                        <div class="tabs">
                            <div class="tab active" data-filter="all">All</div>
                            <div class="tab" data-filter="unread">Unread</div>
                        </div>

                        <!-- **Notification List (This will scroll if >2 items)** -->
                        <div class="notification-list" id="notification-list">
                            <?php foreach ($notifications as $notification) { ?>
                                <a href="viewnotia.php?id=<?php echo $notification['id']; ?>" class="notification-item" data-status="<?php echo $notification['status']; ?>">
                                    <div class="notification-content">
                                        <h3 class="notification-title"><?php echo $notification['title']; ?></h3>
                                        <p class="notification-message"><?php echo $notification['message']; ?></p>
                                        <span class="notification-time"><?php echo $notification['time']; ?></span>
                                    </div>
                                </a>
                            <?php } ?>
                        </div>
                    </div>


                    <!-- Add Job -->
                    <a href="jobpost2.php">
                        <button class="addJob-button">Add Job</button>
                    </a>

                    <!-- ปุ่ม Edit -->
                    <button class="edit-button" onclick="toggleEdit()">Edit</button>
                </div>
            </div>
        </div>

        <!-- Contact -->
        <div class="container">
            <h3>Contact</h3>
            <section class="Contact">
                <!-- โหมดแสดง -->
                <div id="contact_display">
                    <p>เบอร์โทร : <?php echo htmlspecialchars($teacher['phone_number'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <p>อีเมล : <?php echo htmlspecialchars($teacher['email'], ENT_QUOTES, 'UTF-8'); ?></p>
                </div>

                <!-- โหมดแก้ไข -->
                <div id="contact_edit" style="display:none;">
                    <label for="phone_number_input">เบอร์โทร :</label>
                    <input type="text" id="phone_number_input"
                        value="<?php echo htmlspecialchars($teacher['phone_number'], ENT_QUOTES, 'UTF-8'); ?>">

                    <br><br>
                    <label for="email_input">อีเมล :</label>
                    <input type="email" id="email_input"
                        value="<?php echo htmlspecialchars($teacher['email'], ENT_QUOTES, 'UTF-8'); ?>">
                </div>
            </section>
        </div>
        <!-- ปุ่ม Save อยู่ด้านล่าง job -->
        <div class="container">
            <button class="save-button" style="display:none;" onclick="saveChanges()">Save</button>
        </div>
        <div class="container">
            <div class="menu-review">
                <h3>Job</h3>
                <a href="reviewst.php?teachers_id=<?php echo urlencode($teacher['teachers_id']); ?>" class="btn-review">
                    review
                </a>

            </div>

            <div class="content">
                <div class="grid" id="job_container">
                    <?php foreach ($jobs as $job) { ?>
                        <div class="card" id="<?php echo $job['post_jobs_id']; ?>"
                            onclick="window.location='viewapply.php?id=<?php echo $job['post_jobs_id']; ?>'">

                            <!-- โหมดแสดง -->
                            <div class="job_display" id="job_display_<?php echo $job['post_jobs_id']; ?>">
                                <div class="card-top">
                                    <!-- ตรวจสอบว่า URL ของรูปภาพถูกต้อง -->
                                    <img src="<?php echo htmlspecialchars($job['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="Job Image" class="job-image">
                                </div>
                                <div class="card-body">
                                    <!-- แสดง title -->
                                    <h3><?php echo htmlspecialchars($job['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                    <p class="job-description">
                                        <?php
                                        $description = htmlspecialchars($job['description'], ENT_QUOTES, 'UTF-8');
                                        // แสดงรายละเอียด ถ้าคำอธิบายยาวกว่า 100 ตัวอักษร จะแสดงแบบย่อ
                                        echo (strlen($description) > 100) ? substr($description, 0, 95) . '...' : $description;
                                        ?>
                                        <span class="full-description" style="display:none;"><?php echo $description; ?></span>

                                    </p>
                                    <!-- แสดงจำนวนผู้สมัคร -->
                                    <p><strong>รับจำนวน:</strong> <?php echo htmlspecialchars($job['number_student'], ENT_QUOTES, 'UTF-8'); ?> คน</p>
                                    <!-- แสดงวันที่ประกาศ -->
                                    <p><strong>ประกาศเมื่อ:</strong> <?php echo htmlspecialchars($job['created_at'], ENT_QUOTES, 'UTF-8'); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
        <!-- Footer -->
        <footer class="footer">
            <p>© CSIT - Computer Science and Information Technology</p>
        </footer>
    </div>

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
        document.addEventListener("DOMContentLoaded", function() {
            const tabs = document.querySelectorAll(".tab");
            const notificationList = document.getElementById("notification-list");
            const notificationsCard = document.getElementById("notifications"); // ✅ กล่องแจ้งเตือน
            let currentFilter = "all";

            function fetchNotifications(filterType) {
                fetch("teacher_profile.php?fetch_notifications=1") // ✅ ดึงข้อมูลแจ้งเตือนจาก PHP
                    .then(response => response.json())
                    .then(data => {
                        updateNotifications(data.notifications, filterType);
                        updateUnreadCount(data.unread_count);
                    })
                    .catch(error => console.error("Error fetching notifications:", error));
            }

            function updateNotifications(notifications, filterType) {
                notificationList.innerHTML = ""; // ✅ เคลียร์รายการเดิม
                let unreadCount = 0;

                notifications.forEach(notification => {
                    if (filterType === "all" || (filterType === "unread" && notification.status === "unread")) {
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

                        if (notification.status === "unread") {
                            notificationItem.addEventListener("click", function(event) {
                                event.stopPropagation(); // ✅ ป้องกันการปิด `notifications-card`
                                markAsRead(notification.id, notificationItem);
                            });
                            unreadCount++;
                        }

                        notificationList.appendChild(notificationItem);
                    }
                });

                updateUnreadCount(unreadCount);
            }

            function markAsRead(notificationId, notificationItem) {
                console.log("Marking as read:", notificationId); // ✅ Debugging

                // ✅ อัปเดต UI ทันที โดยเปลี่ยนสีของแจ้งเตือน
                notificationItem.dataset.status = "read";
                notificationItem.classList.remove("unread");
                notificationItem.classList.add("read");

                // ✅ ถ้าอยู่ในแท็บ `unread` ให้ลบออกจากรายการทันที
                let activeTab = document.querySelector(".tab.active").getAttribute("data-filter");
                if (activeTab === "unread") {
                    notificationItem.remove();
                }

                // ✅ อัปเดต Badge แจ้งเตือนทันที
                let unreadCount = parseInt(document.querySelector(".notification-badge").innerText) || 0;
                if (unreadCount > 0) {
                    unreadCount--;
                    updateUnreadCount(unreadCount);
                }

                // ✅ ส่งคำขอไปยัง PHP เพื่ออัปเดตฐานข้อมูล
                fetch(`teacher_profile.php?notification_id=${notificationId}`, {
                        method: "GET"
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log("Server Response:", data); // ✅ Debugging

                        if (!data.success) {
                            console.error("Failed to update notification:", data.error);
                        }
                    })
                    .catch(error => console.error("Error updating notification:", error));
            }


            function updateUnreadCount(count) {
                let notificationBadge = document.querySelector(".notification-badge");
                let notificationCount = document.querySelector(".notification-count");

                if (notificationBadge && notificationCount) {
                    if (count > 0) {
                        notificationBadge.innerText = count;
                        notificationBadge.style.display = "inline-block";
                        notificationCount.innerText = `${count} new`;
                        notificationCount.style.display = "inline-block";
                    } else {
                        notificationBadge.style.display = "none";
                        notificationCount.style.display = "none";
                    }
                }
            }

            // ✅ ป้องกันการปิด `notifications-card` เมื่อกดแจ้งเตือน
            notificationsCard.addEventListener("click", function(event) {
                event.stopPropagation();
            });

            tabs.forEach(tab => {
                tab.addEventListener("click", function() {
                    tabs.forEach(t => t.classList.remove("active"));
                    this.classList.add("active");

                    currentFilter = this.getAttribute("data-filter"); // ✅ อัปเดตค่าแท็บที่เลือก
                    fetchNotifications(currentFilter); // ✅ โหลดแจ้งเตือนใหม่เมื่อเปลี่ยนแท็บ
                });
            });

            fetchNotifications("all"); // ✅ โหลดแจ้งเตือนทั้งหมดตอนเริ่มต้น
        });
    </script>
    <script>
        function toggleEdit() {
            // 1) Contact
            const cDisplay = document.getElementById('contact_display');
            const cEdit = document.getElementById('contact_edit');

            // 2) Job: วนลูป card .job_display / .job_edit
            const jobCards = document.querySelectorAll('.card[data-job-id]');

            // 3) ปุ่ม Save
            const saveBtn = document.querySelector('.save-button');

            // ถ้าปัจจุบันยังเป็นโหมดแสดง => ไปโหมดแก้ไข
            if (cDisplay.style.display !== 'none') {
                // Contact
                cDisplay.style.display = 'none';
                cEdit.style.display = 'block';

                // Job
                jobCards.forEach(card => {
                    const jobId = card.getAttribute('data-job-id');
                    const dispEl = document.getElementById('job_display_' + jobId);
                    const editEl = document.getElementById('job_edit_' + jobId);
                    if (dispEl && editEl) {
                        dispEl.style.display = 'none';
                        editEl.style.display = 'block';
                    }
                });

                // ปุ่ม Save
                saveBtn.style.display = 'inline-block';
            } else {
                // กลับไปโหมดแสดง
                cDisplay.style.display = 'block';
                cEdit.style.display = 'none';

                jobCards.forEach(card => {
                    const jobId = card.getAttribute('data-job-id');
                    const dispEl = document.getElementById('job_display_' + jobId);
                    const editEl = document.getElementById('job_edit_' + jobId);
                    if (dispEl && editEl) {
                        dispEl.style.display = 'block';
                        editEl.style.display = 'none';
                    }
                });

                saveBtn.style.display = 'none';
            }
        }

        function saveChanges() {
            try {
                // รับค่า Contact
                const newPhone = document.getElementById('phone_number_input').value.trim();
                const newEmail = document.getElementById('email_input').value.trim();

                // ตรวจสอบว่าผู้ใช้กรอกข้อมูลหรือไม่
                if (!newPhone || !newEmail) {
                    alert("กรุณากรอกข้อมูลให้ครบถ้วน!");
                    return;
                }

                // สร้าง AJAX request
                let xhr = new XMLHttpRequest();
                xhr.open("POST", "teacher_profile.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4) {
                        if (xhr.status === 200) {
                            console.log("Response:", xhr.responseText);

                            if (xhr.responseText.trim() === "success") {
                                // ✅ อัปเดต UI
                                document.getElementById('contact_display').innerHTML = `
                                    <p>เบอร์โทร : ${newPhone}</p>
                                    <p>อีเมล : ${newEmail}</p>
                                `;
                                toggleEdit(); // ปิดโหมดแก้ไข
                            } else {
                                alert("❌ Update Error: " + xhr.responseText);
                            }
                        } else {
                            alert("❌ Server Error: " + xhr.status);
                        }
                    }
                };

                // ส่งข้อมูลไปที่ PHP
                let postData = "phone_number=" + encodeURIComponent(newPhone) +
                    "&email=" + encodeURIComponent(newEmail);

                xhr.send(postData);
            } catch (error) {
                console.error("❌ Error in saveChanges():", error);
                alert("เกิดข้อผิดพลาด กรุณาลองอีกครั้ง!");
            }
        }
    </script>
</body>

</html>

<?php $conn->close(); ?>