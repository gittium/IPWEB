document.addEventListener("DOMContentLoaded", function () {
    // เมื่อเลือก role ใน dropdown
    document.getElementById("roleSelect").addEventListener("change", function () {
        let role = this.value;
        let search = document.querySelector('input[name="search"]').value;
        window.location.href = "manage_users.php?role=" + role + "&search=" + encodeURIComponent(search);
    });
 
 
    // ฟังก์ชันอัปเดตสถานะ (Disable/Activate)
    function updateStatus(userId, status, button) {
        fetch("manage_users.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `user_id=${userId}&status=${parseInt(status)}`
        })
        .then(response => response.json()) // รับค่าเป็น JSON ทันที
        .then(data => {
            if (data.success) {
                alert("สถานะอัปเดตสำเร็จ!");
 
                // ค้นหาแถวของผู้ใช้ที่มี user_id ตรงกัน
                let row = document.querySelector(`button[data-id='${userId}']`).closest("tr");
 
                if (row) {
                    // อัปเดตข้อความสถานะใน <td class="status-text">
                    let statusText = row.querySelector(".status-text span");
                    if (statusText) {
                        if (data.new_status == 1) {
                            statusText.textContent = "Activate";
                            statusText.style.color = "green";
                        } else {
                            statusText.textContent = "Disable";
                            statusText.style.color = "red";
                        }
                    }
 
                    // อัปเดตปุ่มกด
                    let newStatus = data.new_status == 1 ? "2" : "1";
                    button.setAttribute("data-status", newStatus);
                    button.textContent = newStatus === "1" ? "Activate" : "Disable";
                }
            } else {
                alert("Error updating status: " + (data.error || "Unknown error"));
            }
        })
        .catch(error => console.error("Fetch error:", error));
    }
 
    // ใช้ Event Delegation เพื่อตรวจจับการคลิกที่ปุ่ม Disable / Activate
    document.body.addEventListener("click", function (event) {
        if (event.target.classList.contains("status-btn")) {
            let userId = event.target.getAttribute("data-id");
            let status = event.target.getAttribute("data-status");
 
            if (!userId || !status) {
                alert("เกิดข้อผิดพลาด: ไม่พบข้อมูล UserID หรือ Status");
                return;
            }
 
            updateStatus(userId, status, event.target);
        }
    });
});
 