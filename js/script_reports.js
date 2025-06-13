document.addEventListener("DOMContentLoaded", function() {
    loadReports();

    document.getElementById("searchForm").addEventListener("submit", function(event) {
        event.preventDefault();
        loadReports();
    });
});

function loadReports(page = 1) {
    const searchQuery = document.getElementById("searchInput").value;
    fetch(`reports.php?search=${encodeURIComponent(searchQuery)}&page=${page}`)
        .then(response => response.json())
        .then(data => {
            if (!data.reports) return;
            
            const tableBody = document.getElementById("reportTableBody");
            tableBody.innerHTML = "";

            data.reports.forEach(report => {
                const row = document.createElement("tr");
                row.innerHTML = `
                    <td>${report.reporter_name}</td>
                    <td>${report.title}</td>
                    <td>
                        <button class="view-btn" onclick="viewPost(${report.post_id})">View</button>
                        <button class="delete-btn" onclick="deleteReport(${report.id})">Delete</button>
                    </td>
                `;
                tableBody.appendChild(row);
            });
        });
}

function viewPost(postId) {
    fetch(`reports.php?id=${postId}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === "error") {
                alert(data.message);
                return;
            }

            document.getElementById("postTitle").textContent = data.title;
            document.getElementById("postTeacher").textContent = `${data.teacher_name} (${data.email})`;
            document.getElementById("postDescription").textContent = data.description;

            document.querySelector(".content").classList.add("hidden");
            document.getElementById("postDetail").classList.remove("hidden");
        });
}

function backToReports() {
    document.getElementById("postDetail").classList.add("hidden");
    document.querySelector(".content").classList.remove("hidden");
}


function deleteReport(reportId) {
    if (!confirm("Are you sure you want to close this report and mark the post as deleted?")) return;

    fetch(`reports.php`, {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `delete_id=${reportId}`
    })
    .then(response => response.text())
    .then(() => {
        alert("Report closed and post marked as deleted.");
        location.reload(); // รีโหลดหน้าใหม่เพื่ออัปเดต UI
    })
    .catch(error => console.error("Error:", error));
}

function closeReport(reportId) {
    if (!confirm("Are you sure you want to close this report?")) return;

    // ส่งคำขอ POST ไปยัง PHP เพื่อปิดรีพอร์ต
    fetch(`reports.php`, {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `close_id=${reportId}`
    })
    .then(response => response.json())  // รับข้อมูลจาก PHP ในรูปแบบ JSON
    .then(data => {
        if (data.success) {
            alert("Report closed successfully.");
            location.reload(); // รีโหลดหน้าใหม่เพื่ออัปเดต UI
        } else {
            alert("Error: " + data.message);
        }
    })
    .catch(error => console.error("Error:", error));
}

