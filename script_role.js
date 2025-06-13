
/*// เปิด Pop-up เมื่อกดปุ่ม Create Role
document.getElementById('create-button').addEventListener('click', function () {
    document.getElementById('rolePopup').style.display = 'block';
});

// ปิด Pop-up เมื่อกดปุ่ม Cancel
document.getElementById('cancelBtn').addEventListener('click', function () {
    document.getElementById('rolePopup').style.display = 'none';
});

// ปิด Pop-up เมื่อกดปุ่ม Confirm
document.getElementById('confirmBtn').addEventListener('click', function () {
    const roleName = document.getElementById('roleName').value;
    if (roleName) {
        alert(`Role "${roleName}" created successfully!`);
        document.getElementById('rolePopup').style.display = 'none';
    } else {
        alert('Please enter a role name.');
    }
});


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

// Pagination function
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
}*/
