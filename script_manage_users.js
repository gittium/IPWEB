document.addEventListener("DOMContentLoaded", function () {
    // เมื่อเลือก role ใน dropdown
    document.getElementById("roleSelect").addEventListener("change", function () {
        let role = this.value;
        window.location.href = "manage_users.php?role=" + role;
    });

    // ฟังก์ชันอัปเดตสถานะ (Disable/Activate)
    function updateStatus(userId, status) {
        fetch("manage_users.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `user_id=${userId}&status=${status}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("Status updated successfully!");
                location.reload();
            } else {
                alert("Error updating status.");
            }
        })
        .catch(error => console.error("Error:", error));
    }

    // ใช้ Event Delegation เพื่อตรวจจับการคลิกที่ปุ่ม Disable / Activate
    document.body.addEventListener("click", function (event) {
        if (event.target.classList.contains("status-btn")) {
            let userId = event.target.getAttribute("data-id");
            let status = event.target.getAttribute("data-status");
            updateStatus(userId, status);
        }
    });
});
