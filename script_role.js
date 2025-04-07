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