document.addEventListener("DOMContentLoaded", function () {
    // ดึงข้อมูลจากฐานข้อมูล
    fetchData();
});

function fetchData() {
    // ดึงข้อมูลจากเซิร์ฟเวอร์
    fetch('fetch_data.php') // เปลี่ยนชื่อไฟล์ตรงนี้
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json(); // แปลงข้อมูลเป็น JSON
        })
        .then(data => {
            // อัปเดตจำนวนผู้ใช้งาน
            document.getElementById('user-count').textContent = data.user_count;

            // อัปเดตจำนวนบทบาท (อาจารย์และนิสิต)
            document.getElementById('role-teacher-count').textContent = data.teacher_count;
            document.getElementById('role-student-count').textContent = data.student_count;

            // อัปเดตจำนวนรายงาน
            document.getElementById('report-count').textContent = data.report_count;
        })
        .catch(error => console.error('Error fetching data:', error));
}
