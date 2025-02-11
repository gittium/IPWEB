document.addEventListener('click', (event) => {
    console.log("คลิกที่:", event.target); // ✅ ดูว่าคลิกที่อะไร

    const notificationButton = document.querySelector('.notification-btn');
    const notificationsCard = document.getElementById('notifications');

    if (notificationButton.contains(event.target)) {
        console.log("🔔 กดปุ่มแจ้งเตือน");
        notificationsCard.style.display = 'block';
        return;
    }

    if (!notificationsCard.contains(event.target)) {
        console.log("❌ กดนอกแจ้งเตือน, ปิดแจ้งเตือน");
        notificationsCard.style.display = 'none';
    }
});



function toggleDescription(button) {
    let parent = button.parentElement;
    let shortDesc = parent.childNodes[0];
    let fullDesc = parent.querySelector(".full-description");

    if (fullDesc.style.display === "none") {
        fullDesc.style.display = "inline";
        button.innerText = "ซ่อน";
    } else {
        fullDesc.style.display = "none";
        button.innerText = "อ่านเพิ่มเติม";
    }
}
document.addEventListener("DOMContentLoaded", function () {
    const tabs = document.querySelectorAll(".tab");
    const notificationList = document.getElementById("notification-list");
    const notificationsCard = document.getElementById("notifications"); // ✅ กล่องแจ้งเตือน
    let currentFilter = "all";

    function fetchNotifications(filterType) {
        fetch("teacher_profile.php?fetch_notifications=1") // ✅ ดึงข้อมูลแจ้งเตือนจาก PHP
            .then(response => response.json())
            .then(data => {
                updateNotifications(data.notifications, filterType);
                updateUnreadCount(data.unread_count);
            })
            .catch(error => console.error("Error fetching notifications:", error));
    }

    function updateNotifications(notifications, filterType) {
        notificationList.innerHTML = ""; // ✅ เคลียร์รายการเดิม
        let unreadCount = 0;

        notifications.forEach(notification => {
            if (filterType === "all" || (filterType === "unread" && notification.status === "unread")) {
                const notificationItem = document.createElement("div");
                notificationItem.classList.add("notification-item", notification.status);
                notificationItem.setAttribute("data-status", notification.status);
                notificationItem.setAttribute("data-id", notification.id);
                notificationItem.innerHTML = `
                    <div class="notification-content">
                        <h3 class="notification-title">${notification.title}</h3>
                        <p class="notification-message">${notification.message}</p>
                        <span class="notification-time">${notification.time}</span>
                    </div>
                `;

                if (notification.status === "unread") {
                    notificationItem.addEventListener("click", function (event) {
                        event.stopPropagation(); // ✅ ป้องกันการปิด `notifications-card`
                        markAsRead(notification.id, notificationItem);
                    });
                    unreadCount++;
                }

                notificationList.appendChild(notificationItem);
            }
        });

        updateUnreadCount(unreadCount);
    }

    function markAsRead(notificationId, notificationItem) {
        console.log("Marking as read:", notificationId); // ✅ Debugging

        // ✅ อัปเดต UI ทันที โดยเปลี่ยนสีของแจ้งเตือน
        notificationItem.dataset.status = "read";
        notificationItem.classList.remove("unread");
        notificationItem.classList.add("read");

        // ✅ ถ้าอยู่ในแท็บ `unread` ให้ลบออกจากรายการทันที
        let activeTab = document.querySelector(".tab.active").getAttribute("data-filter");
        if (activeTab === "unread") {
            notificationItem.remove();
        }

        // ✅ อัปเดต Badge แจ้งเตือนทันที
        let unreadCount = parseInt(document.querySelector(".notification-badge").innerText) || 0;
        if (unreadCount > 0) {
            unreadCount--;
            updateUnreadCount(unreadCount);
        }

        // ✅ ส่งคำขอไปยัง PHP เพื่ออัปเดตฐานข้อมูล
        fetch(`teacher_profile.php?notification_id=${notificationId}`, {
            method: "GET"
        })
            .then(response => response.json())
            .then(data => {
                console.log("Server Response:", data); // ✅ Debugging

                if (!data.success) {
                    console.error("Failed to update notification:", data.error);
                }
            })
            .catch(error => console.error("Error updating notification:", error));
    }

    function updateUnreadCount(count) {
        let notificationBadge = document.querySelector(".notification-badge");
        let notificationCount = document.querySelector(".notification-count");

        if (notificationBadge && notificationCount) {
            if (count > 0) {
                notificationBadge.innerText = count;
                notificationBadge.style.display = "inline-block";
                notificationCount.innerText = `${count} new`;
                notificationCount.style.display = "inline-block";
            } else {
                notificationBadge.style.display = "none";
                notificationCount.style.display = "none";
            }
        }
    }

    // ✅ ป้องกันการปิด `notifications-card` เมื่อกดแจ้งเตือน
    notificationsCard.addEventListener("click", function (event) {
        event.stopPropagation();
    });

    tabs.forEach(tab => {
        tab.addEventListener("click", function () {
            tabs.forEach(t => t.classList.remove("active"));
            this.classList.add("active");

            currentFilter = this.getAttribute("data-filter"); // ✅ อัปเดตค่าแท็บที่เลือก
            fetchNotifications(currentFilter); // ✅ โหลดแจ้งเตือนใหม่เมื่อเปลี่ยนแท็บ
        });
    });

    fetchNotifications("all"); // ✅ โหลดแจ้งเตือนทั้งหมดตอนเริ่มต้น
});


function toggleEdit() {
    // 1) Contact
    const cDisplay = document.getElementById('contact_display');
    const cEdit = document.getElementById('contact_edit');

    // 2) Job: วนลูป card .job_display / .job_edit
    const jobCards = document.querySelectorAll('.card[data-job-id]');

    // 3) ปุ่ม Save
    const saveBtn = document.querySelector('.save-button');

    // ถ้าปัจจุบันยังเป็นโหมดแสดง => ไปโหมดแก้ไข
    if (cDisplay.style.display !== 'none') {
        // Contact
        cDisplay.style.display = 'none';
        cEdit.style.display = 'block';

        // Job
        jobCards.forEach(card => {
            const jobId = card.getAttribute('data-job-id');
            const dispEl = document.getElementById('job_display_' + jobId);
            const editEl = document.getElementById('job_edit_' + jobId);
            if (dispEl && editEl) {
                dispEl.style.display = 'none';
                editEl.style.display = 'block';
            }
        });

        // ปุ่ม Save
        saveBtn.style.display = 'inline-block';
    } else {
        // กลับไปโหมดแสดง
        cDisplay.style.display = 'block';
        cEdit.style.display = 'none';

        jobCards.forEach(card => {
            const jobId = card.getAttribute('data-job-id');
            const dispEl = document.getElementById('job_display_' + jobId);
            const editEl = document.getElementById('job_edit_' + jobId);
            if (dispEl && editEl) {
                dispEl.style.display = 'block';
                editEl.style.display = 'none';
            }
        });

        saveBtn.style.display = 'none';
    }
}

function saveChanges() {
    try {
        // รับค่า Contact
        const newPhone = document.getElementById('phone_number_input').value.trim();
        const newEmail = document.getElementById('email_input').value.trim();

        // ตรวจสอบว่าผู้ใช้กรอกข้อมูลหรือไม่
        if (!newPhone || !newEmail) {
            alert("กรุณากรอกข้อมูลให้ครบถ้วน!");
            return;
        }

        // สร้าง AJAX request
        let xhr = new XMLHttpRequest();
        xhr.open("POST", "teacher_profile.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    console.log("Response:", xhr.responseText);

                    if (xhr.responseText.trim() === "success") {
                        // ✅ อัปเดต UI
                        document.getElementById('contact_display').innerHTML = `
                                    <p>เบอร์โทร : ${newPhone}</p>
                                    <p>อีเมล : ${newEmail}</p>
                                `;
                        toggleEdit(); // ปิดโหมดแก้ไข
                    } else {
                        alert("❌ Update Error: " + xhr.responseText);
                    }
                } else {
                    alert("❌ Server Error: " + xhr.status);
                }
            }
        };

        // ส่งข้อมูลไปที่ PHP
        let postData = "phone_number=" + encodeURIComponent(newPhone) +
            "&email=" + encodeURIComponent(newEmail);

        xhr.send(postData);
    } catch (error) {
        console.error("❌ Error in saveChanges():", error);
        alert("เกิดข้อผิดพลาด กรุณาลองอีกครั้ง!");
    }
}


