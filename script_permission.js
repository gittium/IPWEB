document.getElementById('roleSearch').addEventListener('input', function() {
    const searchValue = this.value;
    fetchRoles(searchValue);
});

function fetchRoles(searchValue) {
    fetch('fetch_roles.php?search=' + searchValue)
        .then(response => response.json())
        .then(data => {
            document.addEventListener('DOMContentLoaded', function() {
                loadSidebar();
                const userSelect = document.getElementById('userSelect');
                
                // โหลดข้อมูลสิทธิ์เมื่อเลือกผู้ใช้
                userSelect.addEventListener('change', async function() {
                    const userId = this.value;
                    await loadUserPermissions(userId);
                    await loadPermissionLogs(userId);
                });
        
                // โหลดข้อมูลเริ่มต้น
                if (userSelect.value) {
                    loadUserPermissions(userSelect.value);
                    loadPermissionLogs(userSelect.value);
                }
            });
        
            async function loadUserPermissions(userId) {
                const response = await fetch(`get_user_permissions.php?user_id=${userId}`);
                const permissions = await response.json();
                
                // ตั้งค่า checkbox ตามสิทธิ์ที่ได้จากเซิร์ฟเวอร์
                const toggles = document.querySelectorAll('.toggle-switch input');
                toggles.forEach(toggle => {
                    const permissionId = toggle.dataset.id;
                    toggle.checked = permissions.includes(parseInt(permissionId));
                    
                    toggle.addEventListener('change', async function() {
                        const status = this.checked ? 1 : 0;
                        
                        try {
                            const response = await fetch('update_permission.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded',
                                },
                                body: `permission_id=${permissionId}&status=${status}`
                            });
                            
                            if (!response.ok) {
                                throw new Error('Failed to update permission');
                            }
                        } catch (error) {
                            console.error('Error updating permission:', error);
                            this.checked = !this.checked; // revert the toggle if update failed
                        }
                    });
                });
            }
        
            async function loadPermissionLogs(userId) {
                const response = await fetch(`get_permission_logs.php?user_id=${userId}`);
                const html = await response.text();
                document.getElementById('permissionLogs').innerHTML = html;
            }    const roleSelect = document.getElementById('roleSelect');
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
