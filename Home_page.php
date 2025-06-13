<?php
session_start();
include('db_connect.php'); // ถ้ามีไฟล์เชื่อมต่อ DB

// ตรวจสอบว่า session 'user_id' ถูกตั้งค่าไว้หรือไม่ (หมายถึงผู้ใช้ได้เข้าสู่ระบบ)
if (!isset($_SESSION['user_id'])) {
    // หากไม่ได้เข้าสู่ระบบ ให้รีไดเรกต์ไปหน้า login
    header("Location: ../login.php");
    exit;
}
// 1. นับจำนวนผู้ใช้
$user_count = 0;
$sql_user = "SELECT COUNT(*) AS total FROM user";
$result_user = mysqli_query($conn, $sql_user);
if ($result_user) {
    $row = mysqli_fetch_assoc($result_user);
    $user_count = $row['total'];
}

// 2. ดึงรายการบทบาท
$roles = mysqli_query($conn, "SELECT * FROM role");

// 3. นับรายงาน
$report_count = 0;
$sql_report = "SELECT COUNT(*) AS total FROM report";
$result_report = mysqli_query($conn, $sql_report);
if ($result_report) {
    $row = mysqli_fetch_assoc($result_report);
    $report_count = $row['total'];
}
?>


<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.gstatic.com" />
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Montserrat:wght@600&display=swap" rel="stylesheet" />

    <!-- External CSS -->
    <link rel="stylesheet" href="style_home.css">
    <link rel="stylesheet" href="style_sidebar.css">
    <style>
        /* ตั้งค่าพื้นฐาน */
body {
    font-family: 'Roboto', sans-serif;
    background-color: #f4f7fc;
    color: #333;
    margin: 0;
    padding: 0;
}
.sidebar {
  position: fixed;
  height: 95.5vh !important;
  top: 0;
  left: 0;
  z-index: 1000;
  transition: transform 0.3s ease-in-out;

}

/* จัดสไตล์เนื้อหาหลัก */
.main-content {
    max-width: 1200px;
    margin: auto;
    padding-left: 350px;
    text-align: center;
}

/* หัวข้อหลัก */
h1 {
    font-size: 2rem;
    color: #2c3e50;
}

p {
    font-size: 1.1rem;
    color: #555;
}

/* เส้นคั่น */
hr {
    width: 80%;
    border: 1px solid #ddd;
    margin: 20px auto;
}

/* กริดของ Dashboard */
.dashboard {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

/* การ์ด */
.card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    padding: 20px;
    text-align: center;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
}

.card h3 {
    font-size: 1.5rem;
    margin-bottom: 10px;
    color: #34495e;
}

.card p {
    font-size: 1.8rem;
    font-weight: bold;
    color: #3498db;
}
/* Container สำหรับบทบาท */
.role-container {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-top: 15px;
}

/* รายการบทบาท */
.role-item {
    background: #3498db;
    color: white;
    padding: 10px;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: bold;
    transition: background 0.3s ease;
}

.role-item:hover {
    background: #2980b9;
}
.menu-toggle {
    display: none;
    background-color: var(--orange);
    color: var(--white);
    padding: 0.6rem 1.2rem;
    border: none;
    border-radius: 4px;
    font-size: 1rem;
    cursor: pointer;
    margin-bottom: 1.5rem;
    align-self: flex-end;
    transition: background-color 0.3s ease;
}

.menu-toggle:hover {
    background-color: #ff9a5c;
}
  
.menu-toggle {
  display: none;
}
/* === Responsive สำหรับจอเล็ก (เช่นมือถือ) === */
@media (max-width: 768px) {
  #sidebar-container {
    transform: translateX(-100%);
  }

  #sidebar-container.active {
    transform: translateX(0);
  }

  .main-content {
    margin-left: 0;
    padding: 20px;
  }

  .menu-toggle {
    display: block;
    background-color: #FF6F00;
    color: white;
    padding: 0.6rem 1.2rem;
    border: none;
    border-radius: 4px;
    font-size: 1rem;
    cursor: pointer;
    margin-bottom: 1rem;
    position: relative;
    z-index: 1001;
  }
}

    </style>
</head>

<body>
        <!-- Sidebar -->
    
<div id="sidebar-container"class="sidebar">
        </div>
    <!-- Main Content -->
    <div class="main-content">
    <button id="menuToggle" class="menu-toggle">☰ Menu</button>
        <h1>ระบบจัดหางานและพัฒนาทักษะ CSIT</h1>
        <p>ยินดีต้อนรับสู่ระบบจัดการข้อมูล</p>
        <hr class="my-4">

        <div class="dashboard">
            <!-- การ์ดแสดงจำนวนผู้ใช้งาน -->
            <div class="card" id="users-card">
                <h3>ผู้ใช้งาน</h3>
                <p id="user-count"><?php echo $user_count; ?></p>
            </div>

            <!-- การ์ดแสดงบทบาททั้งหมด -->
            <a href="Role_page.php" style="text-decoration: none;">
            <div class="card" id="roles-card">
                <h3>บทบาท</h3>
                <div class="role-container">
                <?php if ($roles && mysqli_num_rows($roles) > 0): ?>
    <?php while ($row = $roles->fetch_assoc()) : ?>
        <div class="role-item"><?php echo htmlspecialchars($row['role_name']); ?></div>
    <?php endwhile; ?>
<?php else: ?>
    <p>ไม่มีข้อมูลบทบาท</p>
<?php endif; ?>

                </div>
            </div>
</a>
            <!-- การ์ดแสดงจำนวนรายงาน -->
            <div class="card" id="reports-card">
                <h3>รายงาน</h3>
                <p id="report-count"><?php echo $report_count; ?></p>
            </div>
        </div>
    </div>

    <!-- External JavaScript -->
    
    <script src="script_sidebar.js"></script>
    <script src="script_home.js"></script>
    <script>
 document.addEventListener("DOMContentLoaded", function () {
  const sidebar = document.querySelector('.sidebar');
  const toggleBtn = document.getElementById('menuToggle');

  console.log('sidebar:', sidebar); // ✅ ต้องไม่เป็น null
  console.log('toggleBtn:', toggleBtn);

  if (sidebar && toggleBtn) {
    toggleBtn.addEventListener('click', function () {
      sidebar.classList.toggle('active');
    });
  }
});

</script>

</body>

</html>
