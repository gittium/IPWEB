// ระบบค้นหาแบบ Live Search
function setupLiveSearch(inputId, itemClass, dataAttr) {
  const input = document.getElementById(inputId);
  if (!input) return;

  input.addEventListener("input", () => {
    const filter = input.value.trim().toLowerCase();
    document.querySelectorAll(`.${itemClass}`).forEach(item => {
      const attr = item.getAttribute(dataAttr) || "";
      item.style.display = attr.toLowerCase().includes(filter) ? "" : "none";
    });
  });
}

document.addEventListener("DOMContentLoaded", () => {
  const searchConfigs = [
    { inputId: "skill-search", listId: "skill-list", dataAttr: "skill" },
    { inputId: "subskill-search", listId: "subskill-list", dataAttr: "subskill" }
  ];

  searchConfigs.forEach(({ inputId, listId, dataAttr }) => {
    const input = document.getElementById(inputId);
    const list = document.getElementById(listId);

    input.addEventListener("input", () => {
      const filter = input.value.toLowerCase();
      const items = list.querySelectorAll(".item-box");

      items.forEach(item => {
        const text = item.dataset[dataAttr]?.toLowerCase() || "";
        item.style.display = text.includes(filter) ? "" : "none";
      });
    });
  });
});
  
  // ฟังก์ชันสำหรับแสดง/ซ่อนรายการย่อย ตามการติ๊กของรายการหลัก (ใช้สำหรับ Skill, Hobby, Job Category)
  function setupLinking(mainCheckboxSelector, subListSelector, dataAttr) {
    const checkboxes = document.querySelectorAll(mainCheckboxSelector);
    const subItems = document.querySelectorAll(subListSelector);
  
    function updateVisibility() {
      const selectedIds = Array.from(checkboxes)
        .filter(cb => cb.checked)
        .map(cb => cb.value);
      subItems.forEach(item => {
        const parentId = item.dataset[dataAttr];
        item.style.display = selectedIds.includes(parentId) ? "" : "none";
      });
    }
    checkboxes.forEach(cb => cb.addEventListener("change", updateVisibility));
    updateVisibility();
  }
  
  // เปิดและปิด modal โดยรับ id
  function openModal(id) {
    document.getElementById(id).style.display = 'block';
  }
  
  function closeModal(id) {
    document.getElementById(id).style.display = 'none';
  }
  
  // ฟังก์ชันสำหรับส่งข้อมูล AJAX
  function ajaxPost(url, params, callback) {
    fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams(params)
    })
    .then(response => response.json())
    .then(data => callback(null, data))
    .catch(err => callback(err, null));
  }
  
  /* =======================
     Functions สำหรับเพิ่มข้อมูลใหม่
     ======================= */
  
  // Skill
  function saveNewSkill() {
    const name = document.getElementById("new-skill-name").value.trim();
    if(!name){
      alert("กรุณากรอกชื่อทักษะ");
      return;
    }
    ajaxPost('Permission_action.php', { action: 'add_skill', skill_name: name }, (err, data) => {
      if(err || !data.success){
        alert("เกิดข้อผิดพลาด: " + (data ? data.error : err));
      } else {
        alert("เพิ่มทักษะเรียบร้อย");
        location.reload();
      }
    });
  }
  
  // Subskill
  function saveNewSubskill() {
    const name = document.getElementById("new-subskill-name").value.trim();
    if(!name){
      alert("กรุณากรอกชื่อทักษะย่อย");
      return;
    }
    const checkedSkills = document.querySelectorAll(".skill-checkbox:checked");
    if(checkedSkills.length !== 1){
      alert("กรุณาติ๊กเลือกทักษะหลัก 1 ตัวก่อนเพิ่มทักษะย่อย");
      return;
    }
    const skillId = checkedSkills[0].value;
    ajaxPost('Permission_action.php', { action: 'add_subskill', subskill_name: name, skill_id: skillId }, (err, data) => {
      if(err || !data.success){
        alert("เกิดข้อผิดพลาด: " + (data ? data.error : err));
      } else {
        alert("เพิ่มทักษะย่อยเรียบร้อย");
        location.reload();
      }
    });
  }
  
  // Hobby
  function saveNewHobby() {
    const name = document.getElementById("new-hobby-name").value.trim();
    if(!name){
      alert("กรุณากรอกชื่องานอดิเรก");
      return;
    }
    ajaxPost('Permission_action.php', { action: 'add_hobby', hobby_name: name }, (err, data) => {
      if(err || !data.success){
        alert("เกิดข้อผิดพลาด: " + (data ? data.error : err));
      } else {
        alert("เพิ่มงานอดิเรกเรียบร้อย");
        location.reload();
      }
    });
  }
  
  // Subhobby
  function saveNewSubhobby() {
    const name = document.getElementById("new-subhobby-name").value.trim();
    if(!name){
      alert("กรุณากรอกชื่องานอดิเรกย่อย");
      return;
    }
    const checkedHobbies = document.querySelectorAll(".hobby-checkbox:checked");
    if(checkedHobbies.length !== 1){
      alert("กรุณาติ๊กเลือกงานอดิเรก 1 ตัวก่อนเพิ่มงานอดิเรกย่อย");
      return;
    }
    const hobbyId = checkedHobbies[0].value;
    ajaxPost('Permission_action.php', { action: 'add_subhobby', subhobby_name: name, hobby_id: hobbyId }, (err, data) => {
      if(err || !data.success){
        alert("เกิดข้อผิดพลาด: " + (data ? data.error : err));
      } else {
        alert("เพิ่มงานอดิเรกย่อยเรียบร้อย");
        location.reload();
      }
    });
  }
  
  // Job Category
  function saveNewJobCat() {
    const name = document.getElementById("new-jobcat-name").value.trim();
    if(!name){
      alert("กรุณากรอกชื่อประเภทงาน");
      return;
    }
    ajaxPost('Permission_action.php', { action: 'add_job_category', job_category_name: name }, (err, data) => {
      if(err || !data.success){
        alert("เกิดข้อผิดพลาด: " + (data ? data.error : err));
      } else {
        alert("เพิ่มประเภทงานเรียบร้อย");
        location.reload();
      }
    });
  }
  
  // Job Subcategory
  function saveNewJobSubCat() {
    const name = document.getElementById("new-jobsubcat-name").value.trim();
    if(!name){
      alert("กรุณากรอกชื่องานย่อย");
      return;
    }
    const checkedJobCats = document.querySelectorAll(".jobcat-checkbox:checked");
    if(checkedJobCats.length !== 1){
      alert("กรุณาติ๊กเลือกประเภทงาน 1 ตัวก่อนเพิ่มงานย่อย");
      return;
    }
    const jobCatId = checkedJobCats[0].value;
    ajaxPost('Permission_action.php', { action: 'add_job_subcategory', job_subcategory_name: name, job_category_id: jobCatId }, (err, data) => {
      if(err || !data.success){
        alert("เกิดข้อผิดพลาด: " + (data ? data.error : err));
      } else {
        alert("เพิ่มงานย่อยเรียบร้อย");
        location.reload();
      }
    });
  }
  
  /* =======================
     ฟังก์ชันสำหรับแก้ไขข้อมูล (ใช้ร่วมกันได้ทุกประเภท)
     ======================= */
  document.getElementById("editForm").addEventListener("submit", e => {
    e.preventDefault();
    const id = document.getElementById("edit-id").value;
    const newName = document.getElementById("edit-name").value.trim();
    const type = document.getElementById("edit-type").value;
    if(!newName){
      alert("กรุณากรอกชื่อใหม่");
      return;
    }
    let action;
    switch(type) {
      case "skill": action = "edit_skill"; break;
      case "subskill": action = "edit_subskill"; break;
      case "hobby": action = "edit_hobby"; break;
      case "subhobby": action = "edit_subhobby"; break;
      case "job_category": action = "edit_job_category"; break;
      case "job_subcategory": action = "edit_job_subcategory"; break;
      default:
        alert("ประเภทไม่ถูกต้อง");
        return;
    }
    ajaxPost('Permission_action.php', { action: action, id: id, name: newName }, (err, data) => {
      if(err || !data.success){
        alert("เกิดข้อผิดพลาด: " + (data ? data.error : err));
      } else {
        alert("แก้ไขเรียบร้อย");
        location.reload();
      }
      closeModal('editModal');
    });
  });
  
  // จัดการ event สำหรับปุ่ม + เพิ่มเติม และปุ่มแก้ไข
  document.addEventListener("DOMContentLoaded", () => {
    // Live search สำหรับทุก section
    setupLiveSearch("skill-search", "item-box", "data-skill");
    setupLiveSearch("subskill-search", "item-box", "data-subskill");
    setupLiveSearch("hobby-search", "item-box", "data-hobby");
    setupLiveSearch("subhobby-search", "item-box", "data-subhobby");
    setupLiveSearch("jobcat-search", "item-box", "data-jobcat");
    setupLiveSearch("jobsubcat-search", "item-box", "data-jobsubcat");
  
    // Setup linking สำหรับ Subskill, Subhobby, Job Subcategory (ตามการติ๊กของรายการหลัก)
    setupLinking(".skill-checkbox", "#subskill-list .item-box", "skillId");
    setupLinking(".hobby-checkbox", "#subhobby-list .item-box", "hobbyId");
    setupLinking(".jobcat-checkbox", "#jobsubcat-list .item-box", "jobcatId");
  
    // ปุ่มเพิ่มสำหรับแต่ละ section
    const addBtns = document.querySelectorAll(".add-btn");
    // ปุ่มเพิ่ม Skill และ Subskill
    if (addBtns.length >= 1) {
      addBtns[0].addEventListener("click", () => openModal("addSkillModal"));
    }
    if (addBtns.length >= 2) {
      addBtns[1].addEventListener("click", () => openModal("addSubskillModal"));
    }
    // ปุ่มเพิ่ม Hobby และ Subhobby
    if (addBtns.length >= 3) {
      addBtns[2].addEventListener("click", () => openModal("addHobbyModal"));
    }
    if (addBtns.length >= 4) {
      addBtns[3].addEventListener("click", () => openModal("addSubhobbyModal"));
    }
    // ปุ่มเพิ่ม Job Category และ Job Subcategory
    if (addBtns.length >= 5) {
      addBtns[4].addEventListener("click", () => openModal("addJobCatModal"));
    }
    if (addBtns.length >= 6) {
      addBtns[5].addEventListener("click", () => openModal("addJobSubCatModal"));
    }
  
    // Event สำหรับปุ่มแก้ไข (ใช้ร่วมกันได้ทุก section)
    document.querySelectorAll(".edit-btn").forEach(button => {
      button.addEventListener("click", e => {
        const itemBox = e.target.closest(".item-box");
        const type = itemBox.getAttribute("data-type"); // ควรเป็น skill, subskill, hobby, subhobby, job_category, job_subcategory
        const id = itemBox.getAttribute("data-id");
        let name = "";
        switch(type) {
          case "skill":
            name = (itemBox.querySelector(".skill-name") || { textContent: "" }).textContent.trim();
            break;
          case "subskill":
            name = (itemBox.querySelector(".subskill-name") || { textContent: "" }).textContent.trim();
            break;
          case "hobby":
            name = (itemBox.querySelector(".hobby-name") || { textContent: "" }).textContent.trim();
            break;
          case "subhobby":
            name = (itemBox.querySelector(".subhobby-name") || { textContent: "" }).textContent.trim();
            break;
          case "job_category":
            name = (itemBox.querySelector(".jobcat-name") || { textContent: "" }).textContent.trim();
            break;
          case "job_subcategory":
            name = (itemBox.querySelector(".jobsubcat-name") || { textContent: "" }).textContent.trim();
            break;
          default:
            name = itemBox.textContent.replace("แก้ไข", "").trim();
        }
        document.getElementById("edit-id").value = id;
        document.getElementById("edit-name").value = name;
        document.getElementById("edit-type").value = type;
        openModal("editModal");
      });
    });
  });
  
  // ปิด modal เมื่อคลิกนอก modal (สำหรับ editModal)
  window.addEventListener("click", e => {
    const modal = document.getElementById("editModal");
    if (e.target === modal) {
      closeModal("editModal");
    }
  });

  document.addEventListener("DOMContentLoaded", () => {
    const skillCheckboxes = document.querySelectorAll(".skill-checkbox");
    const subskillItems = document.querySelectorAll("#subskill-list .item-box");
  
    skillCheckboxes.forEach(checkbox => {
      checkbox.addEventListener("change", () => {
        const selectedSkillIds = Array.from(skillCheckboxes)
          .filter(cb => cb.checked)
          .map(cb => cb.value);
  
        subskillItems.forEach(item => {
          const itemSkillId = item.dataset.skillId;
          item.style.display = selectedSkillIds.includes(itemSkillId) ? "" : "none";
        });
      });
    });
  });
