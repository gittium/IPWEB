    // responsive sidebar
    const menuToggle = document.querySelector('.menu-toggle');//ใช้เพื่อเข้าถึงปุ่มสลับเมนูเพื่อเพิ่มactionให้กับมัน
    const sidebar = document.querySelector('.sidebar'); //ใช้เพื่อเข้าถึงแถบด้านข้างเพื่อทำการเปิดหรือปิด
    const body = document.body;//การป้องกันการเลื่อนหน้าเว็บเมื่อ Sidebar เปิดอยู่

    menuToggle.addEventListener('click', (e) => { //เมื่อผู้ใช้คลิกที่ปุ่มสลับเมนู จะเรียกใช้ฟังก์ชันที่กำหนดภายในวงเล็บปีกกา
      e.stopPropagation(); // Prevent event bubbling
      sidebar.classList.toggle('open');
      body.classList.toggle('nav-open');//การป้องกันการเลื่อนหน้าเว็บ
    });

    // Close sidebar when clicking outside (on overlay)
    //ใช้สำหรับปิด Sidebar เมื่อผู้ใช้คลิกนอก Sidebar (บน Overlay)
    body.addEventListener('click', (e) => { //เมื่อผู้ใช้คลิกที่ใดๆ บนหน้าเว็บ จะเรียกใช้ฟังก์ชันที่กำหนดเพื่อตรวจสอบว่าควรปิด Sidebar หรือไม่
      if (body.classList.contains('nav-open') && !sidebar.contains(e.target) && !menuToggle.contains(e.target)) { //ตรวจสอบว่าผู้ใช้คลิกนอก Sidebar และปุ่มสลับเมนู ในกรณีที่ Sidebar เปิดอยู่ ให้ทำการปิด Sidebar
        sidebar.classList.remove('open'); //ปิด Sidebar โดยการลบคลาสที่ทำให้ Sidebar เปิดอยู่
        body.classList.remove('nav-open');
      }
    });

    // Prevent clicks inside the sidebar from closing it
    //ใช้เพื่อป้องกันไม่ให้การคลิกภายใน Sidebar ทำให้ Sidebar ปิด
    sidebar.addEventListener('click', (e) => { //เมื่อผู้ใช้คลิกภายใน Sidebar จะเรียกใช้ฟังก์ชันที่กำหนด
      e.stopPropagation(); //ป้องกันไม่ให้ Sidebar ปิดอัตโนมัติ
    });