<?php
include 'db_connect.php';

if (isset($_POST['add_role'])) {
    $name = $_POST['name'];
    $name_th = $_POST['name_th'];

    if (empty($name_th)) {
        $name_th = 'ไม่ระบุ';
    }

    $stmt = $conn->prepare("INSERT INTO role (role_name, role_name_th) VALUES (?, ?)");
    $stmt->bind_param("ss", $name, $name_th);
    $stmt->execute();
    $stmt->close();
}

if (isset($_POST['edit_role'])) {
    $id = $_POST['edit_id'];
    $name = $_POST['edit_name'];
    $name_th = $_POST['edit_name_th'];

    $stmt = $conn->prepare("UPDATE role SET role_name = ?, role_name_th = ? WHERE role_id = ?");
    $stmt->bind_param("ssi", $name, $name_th, $id);
    $stmt->execute();
    $stmt->close();
}

$search = isset($_GET['search']) ? $_GET['search'] : '';
$where = $search ? "WHERE role_name LIKE '%$search%' OR role_name_th LIKE '%$search%'" : '';

$entriesPerPage = isset($_GET['entries']) ? (int)$_GET['entries'] : 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $entriesPerPage;

$totalQuery = "SELECT COUNT(*) as total FROM role $where";
$totalResult = $conn->query($totalQuery);
$totalRows = $totalResult->fetch_assoc()['total'];

$query = "SELECT * FROM role $where LIMIT $entriesPerPage OFFSET $offset";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Role Management</title>
    <link rel="stylesheet" href="style_role.css">
    <link rel="stylesheet" href="style_sidebar.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
<div class="containersb">
<div id="sidebar-container" class="sidebar">
        </div>
    <div class="main-content">
    <button id="menuToggle" class="menu-toggle">☰ Menu</button>
        <h1 class="page-title">Role Management</h1>
        <hr class="my-4">

        <p align="right">
            <button id="createRoleBtn" class="create-button">+ Create Role</button>
        </p>

        <!-- Modal Create Role -->
        <div id="createRoleModal" class="modal">
            <div class="modal-content">
                <span class="close" id="createModalClose">&times;</span>
                <h2>Create Role</h2>
                <form method="POST" action="">
                    <input type="text" name="name" placeholder="Enter role name (EN)" required> <br><br>
                    <input type="text" name="name_th" placeholder="Enter role name (TH)" required>
                    <button type="submit" name="add_role" class="confirm-btn">Confirm</button>
                </form>
            </div>
        </div>

        <!-- Modal Edit Role -->
        <div id="editRoleModal" class="modal">
            <div class="modal-content">
                <span class="close" id="editModalClose">&times;</span>
                <h2>Edit Role</h2>
                <form method="POST" action="">
                    <input type="hidden" name="edit_id" id="edit_id">
                    <input type="text" name="edit_name" id="edit_name" placeholder="Enter new role name (EN)" required><br><br>
                    <input type="text" name="edit_name_th" id="edit_name_th" placeholder="Enter new role name (TH)" required>
                    <button type="submit" name="edit_role" class="confirm-btn">Save</button>
                </form>
            </div>
        </div>
        
        <!-- Table Controls -->
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
            <!-- เปลี่ยนจาก id="search" เป็น role-search -->
<div class="search-wrapper">
  <input type="search" id="role-search" placeholder="Search" class="search-input">
</div>

        </div>

        <!-- ตาราง Role -->
        <table class="role-table" id="roleTable">
            <thead>
                <tr>
                    <th>Role (EN)</th>
                    <th>Role (TH)</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = $result->fetch_assoc()) : ?>
                <tr>
                    <td><?= htmlspecialchars($row['role_name']) ?></td>
                    <td><?= htmlspecialchars($row['role_name_th']) ?></td>
                    <td>
                        <button class="edit-button"
                            onclick="openEditModal(<?= $row['role_id'] ?>, '<?= htmlspecialchars($row['role_name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($row['role_name_th'], ENT_QUOTES) ?>')">Edit</button>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="pagination">
            <div class="pagination-info">
                Showing <?= $offset + 1 ?> to <?= min($offset + $entriesPerPage, $totalRows) ?> of <?= $totalRows ?> entries
            </div>
            <div class="pagination-controls">
                <button onclick="prevPage()" <?= $page == 1 ? 'disabled' : '' ?>>Previous</button>
                <span><?= $page ?></span>
                <button onclick="nextPage()" <?= $page * $entriesPerPage >= $totalRows ? 'disabled' : '' ?>>Next</button>
            </div>
        </div>
    </div>
</div>
<script src="script_sidebar.js"></script>
<script>
    function openEditModal(id, name, name_th) {
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_name_th').value = name_th;
        document.getElementById('editRoleModal').style.display = "block";
    }

    // Modal control
    const createModal = document.getElementById("createRoleModal");
    const editModal = document.getElementById("editRoleModal");

    document.getElementById("createRoleBtn").addEventListener("click", () => {
        createModal.style.display = "block";
    });

    document.getElementById("createModalClose").addEventListener("click", () => {
        createModal.style.display = "none";
    });

    document.getElementById("editModalClose").addEventListener("click", () => {
        editModal.style.display = "none";
    });

    window.addEventListener("click", (event) => {
        if (event.target === createModal) createModal.style.display = "none";
        if (event.target === editModal) editModal.style.display = "none";
    });

    // Pagination functions
    function updateEntries() {
        const entries = document.getElementById("entries").value;
        const url = new URL(window.location.href);
        url.searchParams.set('entries', entries);
        url.searchParams.set('page', 1);
        window.location.href = url;
    }

    function prevPage() {
        const url = new URL(window.location.href);
        let page = parseInt(url.searchParams.get('page') || 1);
        if (page > 1) {
            url.searchParams.set('page', page - 1);
            window.location.href = url;
        }
    }

    function nextPage() {
        const url = new URL(window.location.href);
        let page = parseInt(url.searchParams.get('page') || 1);
        url.searchParams.set('page', page + 1);
        window.location.href = url;
    }

    function searchRoles() {
        const search = document.getElementById("search").value;
        const url = new URL(window.location.href);
        url.searchParams.set('search', search);
        url.searchParams.set('page', 1);
        window.location.href = url;
    }

</script>
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

document.addEventListener("DOMContentLoaded", () => {
  const searchConfigs = [
    { inputId: "skill-search", listId: "skill-list", dataAttr: "skill" },
    { inputId: "subskill-search", listId: "subskill-list", dataAttr: "subskill" }
  ];

  searchConfigs.forEach(({ inputId, listId, dataAttr }) => {
    const input = document.getElementById(inputId);
    const list = document.getElementById(listId);

    input.addEventListener("input", () => {
      const filter = input.value.toLowerCase();
      const items = list.querySelectorAll(".item-box");

      items.forEach(item => {
        const text = item.dataset[dataAttr]?.toLowerCase() || "";
        item.style.display = text.includes(filter) ? "" : "none";
      });
    });
  });
});


document.addEventListener("DOMContentLoaded", () => {
  const searchInput = document.getElementById("role-search");
  const rows = document.querySelectorAll("#roleTable tbody tr");

  if (searchInput) {
    searchInput.addEventListener("input", () => {
      const filter = searchInput.value.trim().toLowerCase();

      rows.forEach(row => {
        const roleEN = row.children[0].textContent.toLowerCase();
        const roleTH = row.children[1].textContent.toLowerCase();
        const isMatch = roleEN.includes(filter) || roleTH.includes(filter);
        row.style.display = isMatch ? "" : "none";
      });
    });
  }
});

</script>
</body>
</html>
