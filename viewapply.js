// เลือกปุ่มฟิลเตอร์โดยใช้ ID
document.getElementById('filter-btn').addEventListener('click', function () {
    // เลือกกรอบข้อความที่ซ่อนโดยใช้ ID
    const message = document.getElementById('hidden-message');
    
    // ตรวจสอบสถานะการแสดงผลของกรอบข้อความ
    if (message.style.display === 'none' || message.style.display === '') {
        message.style.display = 'block'; // แสดงกรอบข้อความ
    } else {
        message.style.display = 'none'; // ซ่อนกรอบข้อความ
    }
});        


// จัดการปุ่มสาขา
const branchButtons = document.querySelectorAll('.selected[id^="branch-btn"]');
branchButtons.forEach(button => {
    button.addEventListener('click', () => {
        // ลบคลาส active จากปุ่มสาขาเดิมทั้งหมด
        branchButtons.forEach(btn => btn.classList.remove('active'));

        // เพิ่มคลาส active ให้ปุ่มที่ถูกเลือก
        button.classList.add('active');

        // แสดงข้อความใน console
        const selectedBranch = button.innerText;
        console.log(`สาขาที่เลือก: ${selectedBranch}`);
    });
});


// จัดการปุ่มชั้นปี
const yearButtons = document.querySelectorAll('.selected[id^="year-btn"]');
yearButtons.forEach(button => {
    button.addEventListener('click', () => {
        // ลบคลาส active จากปุ่มชั้นปีเดิมทั้งหมด
        yearButtons.forEach(btn => btn.classList.remove('active'));

        // เพิ่มคลาส active ให้ปุ่มที่ถูกเลือก
        button.classList.add('active');

        // แสดงข้อความใน console
        const selectedYear = button.innerText;
        console.log(`ชั้นปีที่เลือก: ${selectedYear}`);
    });
});


// ปุ่ม Clear สำหรับรีเซ็ตทั้งปุ่มสาขาและปุ่มชั้นปี
const clearButton = document.getElementById('clear-btn');
clearButton.addEventListener('click', () => {
    // ลบคลาส active จากปุ่มสาขาและปุ่มชั้นปีทั้งหมด
    branchButtons.forEach(btn => btn.classList.remove('active'));
    yearButtons.forEach(btn => btn.classList.remove('active'));

    console.log('ล้างการเลือกสาขาและชั้นปีทั้งหมด');
});


// ปุ่มตกลง
const applyButton = document.getElementById('apply-btn');
applyButton.addEventListener('click', () => {
    // ดึงค่าที่เลือกจากปุ่ม
    const selectedBranch = document.querySelector('.selected[id^="branch-btn"].active')?.innerText || 'ยังไม่ได้เลือกสาขา';
    const selectedYear = document.querySelector('.selected[id^="year-btn"].active')?.innerText || 'ยังไม่ได้เลือกชั้นปี';

    // แสดงผลใน console
    console.log(`สาขาที่เลือก: ${selectedBranch}`);
    console.log(`ชั้นปีที่เลือก: ${selectedYear}`);

    // ซ่อนกล่องข้อความหลังจากกดตกลง
    const messageBox = document.getElementById('hidden-message');
    messageBox.style.display = 'none'; // ซ่อนกล่องข้อความ
});

