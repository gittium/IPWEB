<div class="containersb">
<div class="container">
    <!-- ปุ่ม toggle -->
<button class="sidebar-toggle" onclick="toggleSidebar()">☰</button>

<div class="sidebar" id="sidebar"
>
  <!-- เมนูปกติ -->
  <div>
        <a href="Home_page.php" class="nav-item" style="margin-top: 60px;">
            <i class="bi bi-house"></i>
            <span>Home</span>
        </a>
        <a href="manage_users.php" class="nav-item">
            <i class="bi bi-people"></i>
            <span>Manage User</span>
        </a>
        <a href="reports.php" class="nav-item">
            <i class="bi bi-flag"></i>
            <span>Reports</span>
        </a>
        <div class="dropdown">
            <div class="nav-item dropdown-toggle" onclick="toggleDropdown()" class="nav-item">
                <i class="bi bi-person"></i>
                <span>General</span>
                <i class="bi bi-chevron-down" style="font-size: 15px;"></i>
            </div>
            <div class="dropdown-menu" class="nav-item">
                <a href="Role_page.php" class="nav-item">Role</a>
                <a href="Permission_page.php" class="nav-item">Permission</a>
            </div>
        </div>
    </div>
        <a href="../logout.php" class="nav-item">
            <i class="bi bi-box-arrow-right"></i>
            <span>Logout</span>
        </a>
    </div>
</div>
</div>
