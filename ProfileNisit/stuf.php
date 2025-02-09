<?php
// เชื่อมต่อฐานข้อมูล
include 'database.php';

// ตรวจสอบและอัปเดตสถานะแจ้งเตือนเมื่อมีการส่งค่าผ่าน URL (GET)
if (isset($_GET['id'])) {
    $notification_id = intval($_GET['id']); // แปลงเป็นตัวเลขเพื่อป้องกัน SQL Injection

    $update_sql = "UPDATE notifications SET status = 'read' WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("i", $notification_id);

    if ($stmt->execute()) {
        echo "Success";
    } else {
        echo "Error: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
    exit(); // จบการทำงานของ PHP ทันที ไม่ให้โหลด HTML
}

// ตรวจสอบว่ามี session หรือไม่
if (!isset($_SESSION['user_id'])) {
    //echo "Session user_id is not set. Setting default value.<br>";
    $_SESSION['user_id'] = 64312132;  // กำหนดค่าเริ่มต้น
}
$user_id = $_SESSION['user_id'];

// ตรวจสอบว่า user มีอยู่ในฐานข้อมูลหรือไม่
$user_sql = "SELECT id FROM users WHERE id = ?";
$stmt = $conn->prepare($user_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();

/*if ($user_result->num_rows === 0) {
    die("User not found in database: ID " . $user_id);
} else {
    echo "User found in database: ID " . $user_id;
}*/

// ดึงข้อมูลแจ้งเตือนเฉพาะของผู้ใช้ที่ล็อกอินอยู่
$sql = "SELECT notifications.id AS notification_id, 
               notifications.message, 
               notifications.created_at, 
               notifications.status, 
               accepted_applications.accept_status_id, 
               accept_status.accept_name_status
        FROM notifications
        JOIN accepted_applications ON notifications.reference_id = accepted_applications.id
        JOIN accept_status ON accepted_applications.accept_status_id = accept_status.id
        WHERE notifications.user_id = ?
        ORDER BY notifications.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = [
        'id' => $row['notification_id'],  // ใช้ alias ที่ถูกต้อง
        'title' => $row['accept_name_status'],
        'message' => $row['message'],
        'time' => $row['created_at'],
        'status' => strtolower($row['status']), // ใช้ status จาก notifications
        'accept_status_id' => $row['accept_status_id'] ?? null  // ตรวจสอบว่า null หรือไม่
    ];
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
    <link rel="stylesheet" href="stupfstyle.css">
    <link rel="stylesheet" href="header-footer.html">
</head>

<body>
    <header class="headerTop">
        <div class="headerTopImg">
            <img src="logo.png" alt="Naresuan University Logo">
            <a href="#">Naresuan University</a>
        </div>
        <nav class="header-nav">

            <a href="/homenisit/index.html"><button type="button" class="btn btn-warning">Home</button></a>
            <a href="#"><button type="button" class="btn btn-danger">Logout</button></a>
        </nav>
    </header>
    <!-- รีวิว -->
    <div class="profile-container">
        <div class="header">
            <div class="profile">
                <div class="profile-pic">N</div>
                <div class="detail-name">
                    <div class="name">ณัฐชา55 ศิริพันธ์</div>
                    <div class="sub-title">สาขา วิทยาการคอมพิวเตอร์</div>
                </div>
            </div>
        </div>

        <div class="content">
            <div class="detail-head">
                <a href="review.html">
                    <div class="review">
                        <div class="rating bg-sumary">4</div>
                        <div class="review-detail">
                            <div class="stars">★★★★★</div>
                            <small>from 6 people</small>
                        </div>
                    </div>
                </a>
                <div>
                    <button class="notification-btn">
                        <i class="bi bi-bell"></i>
                        <span class="notification-badge">1</span>
                        <button class="edit-button">Edit</button>
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
            <span class="notification-count">1 new</span>
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
                <div class="notification-item" data-status="<?php echo $notification['status']; ?>">
                    <div class="notification-icon <?php echo $notification['status']; ?>">✓</div>
                    <div class="notification-content">
                        <h3 class="notification-title"><?php echo $notification['title']; ?></h3>
                        <p class="notification-message"><?php echo $notification['message']; ?></p>
                        <span class="notification-time"><?php echo $notification['time']; ?></span>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>

    <!--ส่วนเนื้อหา-->
    <div class="container">
        <h3>About Me</h3>
        <section class="about-me">
            <p>นางสาวณัฐชา ศิริพันธุ์ นิสิตชั้นปีที่ 3 สาขาวิชาการคอมพิวเตอร์ คณะวิทยาศาสตร์ มหาวิทยาลัยนเรศวร</p>
            <div class="contact">
                <p>Contact: เบอร์โทร: 0xx-xxx-xxxx, อีเมล: nutchas6x@nu.ac.th</p>
            </div>
        </section>
        <h3>Experience</h3>
        <section class="experience">
            <p>การพัฒนาโปรเจควิเคราะห์ข้อมูลเชิงคณิตศาสตร์, การสร้างและจัดการระบบซอฟต์แวร์ด้วยแนวคิด Agile,
                การปรับเปลี่ยนและพัฒนาหัวข้อโปรเจคให้สอดคล้องกับความสนใจและสถานการณ์</p>
        </section>

        <h3>Skills</h3>
        <section class="skills">
            <p>Programming Languages: Python, Java, C++</p>
            <p>Data Analysis: Logistic Regression, Data Visualization และการจัดการข้อมูลด้วย Pandas</p>
            <p>Software Development: การทำงานในรูปแบบ Agile, การเขียนและจัดการ User Story, และการพัฒนาโปรเจคบน Git
            </p>
            <p>Communication: การทำงานเป็นทีม, การนำเสนอโปรเจค, และการเขียนเอกสารเชิงเทคนิค</p>
        </section>

        <h3>Interest</h3>
        <section class="interest">

            <p>Data Science และ Machine Learning, การพัฒนาซอฟต์แวร์: การทำงานร่วมกันกับทีมพัฒนาซอฟต์แวร์
                และการปรับปรุงกระบวนการทำงานแบบ Agile</p>
        </section>
    </div>


    </div>
    <footer class="footer">
        <p>© CSIT - Computer Science and Information Technology</p>
    </footer>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const tabs = document.querySelectorAll(".tab");
            const notificationList = document.getElementById("notification-list");

            // รับข้อมูลการแจ้งเตือนจาก PHP และตรวจสอบว่ามีข้อมูลหรือไม่
            let notifications = <?php echo json_encode($notifications); ?> || [];

            function updateNotifications(filterType) {
                notificationList.innerHTML = ""; // เคลียร์รายการเดิม

                notifications.forEach((notification, index) => {
                    if (filterType === "all" ||
                        (filterType === "unread" && notification.status === "unread") ||
                        (filterType === "accepted" && notification.title === "Accepted") ||
                        (filterType === "reject" && notification.title === "Rejected")) {

                        const notificationItem = document.createElement("div");
                        notificationItem.classList.add("notification-item", notification.status);
                        notificationItem.innerHTML = `
                    <div class="notification-icon ${notification.status}">✓</div>
                    <div class="notification-content">
                        <h3 class="notification-title">${notification.title}</h3>
                        <p class="notification-message">${notification.message}</p>
                        <span class="notification-time">${notification.time}</span>
                    </div>
                `;

                        // ถ้าเป็น unread ให้เพิ่ม event listener
                        if (notification.status === "unread") {
                            notificationItem.addEventListener("click", function() {
                                markAsRead(notification.id, index);
                            });
                        }

                        notificationList.appendChild(notificationItem);
                    }
                });
            }

            function markAsRead(notificationId, index) {
                let xhr = new XMLHttpRequest();
                xhr.open("GET", window.location.href.split('?')[0] + "?id=" + notificationId, true); // ส่งผ่าน URL (GET)
                xhr.send();

                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        console.log("Notification marked as read: " + notificationId);

                        // เปลี่ยน status ใน array เป็น 'read' แทนที่จะลบออก
                        notifications[index].status = "read";
                        updateNotifications(document.querySelector(".tab.active").getAttribute("data-filter"));
                    }
                };
            }

            tabs.forEach(tab => {
                tab.addEventListener("click", function() {
                    tabs.forEach(t => t.classList.remove("active"));
                    this.classList.add("active");

                    const filterType = this.getAttribute("data-filter");
                    updateNotifications(filterType);
                });
            });

            updateNotifications("all");
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

</body>

</html>
<?php $conn->close(); ?>