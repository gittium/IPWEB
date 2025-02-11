<?php
include 'db_connect.php';

// เพิ่ม Role
if (isset($_POST['add_role'])) {
    $name = $_POST['name'];

    // หาเลข ID ที่ถูกลบไป
    $result = $conn->query("SELECT id FROM roles ORDER BY id");
    $ids = [];
    while ($row = $result->fetch_assoc()) {
        $ids[] = $row['id'];
    }

    // หาช่องว่างของเลข ID
    $new_id = 1;
    foreach ($ids as $id) {
        if ($id > $new_id) {
            break;
        }
        $new_id++;
    }

    // เพิ่มข้อมูลด้วยเลข ID ใหม่
    $stmt = $conn->prepare("INSERT INTO roles (id, role_name) VALUES (?, ?)");
    $stmt->bind_param("is", $new_id, $name);
    $stmt->execute();
    $stmt->close();
}

// ลบ Role
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    
    // ลบข้อมูลในตาราง roles
    $stmt = $conn->prepare("DELETE FROM roles WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

// ลบทั้งหมด
if (isset($_GET['delete_all'])) {
    // ลบข้อมูลทั้งหมดในตาราง roles
    $stmt = $conn->prepare("DELETE FROM roles");
    $stmt->execute();
    $stmt->close();
}

// ค้นหา Role
$search = isset($_GET['search']) ? $_GET['search'] : '';
$where = $search ? "WHERE role_name LIKE '%$search%'" : "";

// ดึงข้อมูล Role
$entriesPerPage = isset($_GET['entries']) ? (int)$_GET['entries'] : 2;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $entriesPerPage;

$totalQuery = "SELECT COUNT(*) as total FROM roles $where";
$totalResult = $conn->query($totalQuery);
$totalRows = $totalResult->fetch_assoc()['total'];

$query = "SELECT * FROM roles $where LIMIT $entriesPerPage OFFSET $offset";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Role Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="style_role.css">
    <link rel="stylesheet" href="style_sidebar.css">
</head>
<body>
<div class="container">
    <!-- Sidebar -->
    <div class="sidebar">
        <!-- เนื้อหาของ Sidebar -->
        <?php include 'siderbar.php'; ?>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h1 class="page-title">Role</h1>
        </div>
        <hr class="my-4">

        <!-- ปุ่ม + Create Role -->
        <p align="right">
            <button id="createRoleBtn" class="create-button">
                <span>+</span>
                <span>Create Role</span>
            </button>
        </p>

        <!-- Modal -->
        <div id="createRoleModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span> <br>
                <form method="POST" action="">
                    <h2>Create Role</h2>
                    <br>
                    <input type="text" id="roleName" name="name" placeholder="Enter role name" required>
                    <button type="submit" name="add_role">Confirm</button>
                    <button type="button" onclick="closeCreateRoleModal()" class="cancelBtn">Cancel</button>
                </form>
            </div>
        </div>
        <br><br>

        <div class="table-controls">
            <div class="entries-control">
                <span>Show</span>
                <select class="entries-select" id="entries" onchange="updateEntries()">
                    <option value="2" <?= $entriesPerPage == 2 ? 'selected' : '' ?>>2</option>
                    <option value="5" <?= $entriesPerPage == 5 ? 'selected' : '' ?>>5</option>
                    <option value="10" <?= $entriesPerPage == 10 ? 'selected' : '' ?>>10</option>
                </select>
                <span>entries</span>
            </div>
            <div class="search-wrapper">
                <i class="bi bi-search"></i>
                <input type="search" id="search" placeholder="Search" class="search-input" oninput="searchRoles()">
            </div>
        </div>

        <div style="text-align: right; margin-bottom: 10px;">
            <button id="deleteAllBtn" onclick="deleteAllRoles()" class="delete-button">Delete All</button>
        </div>
        
        <table class="role-table" id="roleTable">
            <thead>
                <tr>
                    <th>Name</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="role-table-body">
                <?php while ($row = $result->fetch_assoc()) : ?>
                    <tr>
                        <td><?= $row['role_name'] ?></td>
                        <td>
                            <button onclick="deleteRole(<?= $row['id'] ?>)" class="delete-button">Delete</button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- Pagination moved inside the container -->
        <div class="pagination">
            <div class="pagination-info" id="showingEntries">
                Showing <?= $offset + 1 ?> to <?= min($offset + $entriesPerPage, $totalRows) ?> of <?= $totalRows ?> entries
            </div>
            <div class="pagination-controls">
                <button class="pagination-button" id="prevBtn" onclick="prevPage()" <?= $page == 1 ? 'disabled' : '' ?>>Previous</button>
                <span class="pagination-current" id="currentPage"><?= $page ?></span>
                <button class="pagination-button" id="nextBtn" onclick="nextPage()" <?= $page * $entriesPerPage >= $totalRows ? 'disabled' : '' ?>>Next</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Delete Role
    function deleteRole(id) {
        if (confirm('Are you sure you want to delete this role?')) {
            window.location.href = `?delete_id=${id}`;
        }
    }

    // Delete All Roles
    function deleteAllRoles() {
        if (confirm('Are you sure you want to delete all roles?')) {
            window.location.href = '?delete_all=1';
        }
    }

    // Update Entries Per Page
    function updateEntries() {
        const entries = document.getElementById('entries').value;
        window.location.href = `?entries=${entries}`;
    }

    // Search Roles
    function searchRoles() {
        const search = document.getElementById('search').value;
        window.location.href = `?search=${search}`;
    }

    // Pagination
    function prevPage() {
        const page = <?= $page ?>;
        if (page > 1) {
            window.location.href = `?page=${page - 1}`;
        }
    }

    function nextPage() {
        const page = <?= $page ?>;
        const totalPages = Math.ceil(<?= $totalRows ?> / <?= $entriesPerPage ?>);
        if (page < totalPages) {
            window.location.href = `?page=${page + 1}`;
        }
    }

    // ดึง Element ที่เกี่ยวข้อง
    const createRoleBtn = document.getElementById("createRoleBtn");
    const modal = document.getElementById("createRoleModal");
    const closeBtn = document.querySelector(".close");

    // เมื่อคลิกปุ่ม + Create Role
    createRoleBtn.addEventListener("click", () => {
        modal.style.display = "block"; // แสดง Modal
    });

    // เมื่อคลิกปุ่มปิด (×)
    closeBtn.addEventListener("click", () => {
        modal.style.display = "none"; // ซ่อน Modal
    });

    // เมื่อคลิกนอก Modal ให้ซ่อน Modal
    window.addEventListener("click", (event) => {
        if (event.target === modal) {
            modal.style.display = "none";
        }
    });
</script>
<script src="script_sidebar.js"></script>
</body>
</html>
