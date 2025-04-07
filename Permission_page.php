<!-- user_permissions.php -->
<?php
include 'siderbar.php';
require_once 'db_connect.php';
require_once 'permission_handler.php';
$permissionManager = new PermissionManager($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Permission</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.gstatic.com" />
    <link
        href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Montserrat:wght@600&display=swap"
        rel="stylesheet" />

    <!-- External CSS -->
    <link rel="stylesheet" href="style_sidebar.css">
    <link rel="stylesheet" href="style_permission.css">
</head>
<body>
    
    <!-- Placeholder for Sidebar -->
    <!--<div id="sidebar-container"></div>-->

    <!-- Main Content -->
    <main class="main-content">
        <div class="permission-container">
            <div class="permission-header">
                <h1 class="page-title">Permissions</h1>
                <div class="user-selector">
                    <span class="role-label">User:</span>
                    <select class="role-select" id="userSelect">
                        <?php
                        // ดึงรายชื่อผู้ใช้ทั้งหมด
                        $users = $permissionManager->getAllUsers();
                        foreach ($users as $user) {
                            echo "<option value='{$user['id']}'>{$user['name']} ({$user['role']})</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>

            <!-- Jobs Posting Management -->
            <div class="permission-group">
                <h2 class="group-title">จัดการโพสงาน (Jobs Posting Management)</h2>
                <div class="permission-list">
                    <div class="permission-item">
                        <span class="permission-name">สร้างโพสงานใหม่</span>
                        <label class="toggle-switch">
                            <input type="checkbox" data-id="1"> <!-- เพิ่ม data-id -->
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    <div class="permission-item">
                        <span class="permission-name">แก้ไขหรือลบโพสงานที่สร้าง</span>
                        <label class="toggle-switch">
                            <input type="checkbox" data-id="2"> <!-- เพิ่ม data-id -->
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    <div class="permission-item">
                        <span class="permission-name">ปิดการรับสมัครงาน</span>
                        <label class="toggle-switch">
                            <input type="checkbox" data-id="3"> <!-- เพิ่ม data-id -->
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Applications Management -->
            <div class="permission-group">
                <h2 class="group-title">จัดการการสมัครงาน (Applications Management)</h2>
                <div class="permission-list">
                    <div class="permission-item">
                        <span class="permission-name">ดูรายชื่อนิสิตที่สมัครเข้าช่วยงาน</span>
                        <label class="toggle-switch">
                            <input type="checkbox" data-id="4"> <!-- เพิ่ม data-id -->
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    <div class="permission-item">
                        <span class="permission-name">อนุมัติหรือปฏิเสธการสมัครของนิสิต</span>
                        <label class="toggle-switch">
                            <input type="checkbox" data-id="5"> <!-- เพิ่ม data-id -->
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Profile Management -->
            <div class="permission-group">
                <h2 class="group-title">แก้ไขโปรไฟล์ (Edit Profile)</h2>
                <div class="permission-list">
                    <div class="permission-item">
                        <span class="permission-name">แก้ไขข้อมูลส่วนตัวและข้อมูลการติดต่อ</span>
                        <label class="toggle-switch">
                            <input type="checkbox" data-id="6"> <!-- เพิ่ม data-id -->
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
        
    </main>
