<?php
include 'siderbar.php';?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.gstatic.com" />
    <link
        href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Montserrat:wght@600&display=swap"
        rel="stylesheet" />

    <!-- External CSS -->
    <link rel="stylesheet" href="style_home.css">
    <link rel="stylesheet" href="style_sidebar&role.css">
</head>

<body>
    <!-- Placeholder for Sidebar -->
    <!--<div id="sidebar-container"></div>-->

    <!-- Main Content -->
    <div class="main-content">
        <h1>ระบบจัดหางานและพัฒนาทักษะ CSIT</h1>
        <p>ยินดีต้อนรับสู่ระบบจัดการข้อมูล</p>
        <hr class="my-4">

        <div class="dashboard">
            <div class="card" id="users-card">
                <h3>ผู้ใช้งาน</h3>
                <p id="user-count">0</p> <!-- อัปเดตค่าที่นี่ -->
            </div>
            <div class="card" id="roles-card">
                <h3>บทบาท</h3>
                <div class="role-container">
                    <div>
                        <h3>อาจารย์</h3>
                        <p id="role-teacher-count">0</p> <!-- อัปเดตค่าที่นี่ -->
                    </div>
                    <div>
                        <h3>นิสิต</h3>
                        <p id="role-student-count">0</p> <!-- อัปเดตค่าที่นี่ -->
                    </div>
                </div>
            </div>
            <div class="card" id="reports-card">
                <h3>รายงาน</h3>
                <p id="report-count">0</p> <!-- อัปเดตค่าที่นี่ -->
            </div>
        </div>
    </div>
    
    <!-- External JavaScript -->
    <script src="script_home.js"></script>
    <script src="script_sidebar.js"></script>
</body>
</html>
