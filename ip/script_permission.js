document.getElementById('roleSearch').addEventListener('input', function() {
    const searchValue = this.value;
    fetchRoles(searchValue);
});

function fetchRoles(searchValue) {
    fetch('fetch_roles.php?search=' + searchValue)
        .then(response => response.json())
        .then(data => {
            const roleSelect = document.getElementById('roleSelect');
            roleSelect.innerHTML = '<option value="">เลือก Role</option>'; // เคลียร์ตัวเลือกเก่า
            data.forEach(role => {
                const option = document.createElement('option');
                option.value = role.id; // ใช้ ID ของ Role
                option.textContent = role.name; // ใช้ชื่อ Role
                roleSelect.appendChild(option);
            });
        });
}

function fetchPermissions(roleId) {
    // ฟังก์ชันนี้จะดึงสิทธิ์จากฐานข้อมูลตาม Role ที่เลือก
    fetch('fetch_permissions.php?role_id=' + roleId)
        .then(response => response.json())
        .then(data => {
            // อัปเดต toggle switches ตามข้อมูลที่ได้รับ
            // ตัวอย่างการอัปเดต toggle switches ที่นี่
        });
}
