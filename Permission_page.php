<?php
include 'db_connect.php';

// ดึงข้อมูลจากตาราง Skill และ Subskill
$skill_sql = "SELECT * FROM skill ORDER BY skill_name";
$skills = $conn->query($skill_sql);
$subskill_sql = "SELECT * FROM subskill ORDER BY subskill_name";
$subskills = $conn->query($subskill_sql);

// ดึงข้อมูลจากตาราง Hobby และ Subhobby
$hobby_sql = "SELECT * FROM hobby ORDER BY hobby_name";
$hobbies = $conn->query($hobby_sql);
$subhobby_sql = "SELECT * FROM subhobby ORDER BY subhobby_name";
$subhobbies = $conn->query($subhobby_sql);

// ดึงข้อมูลจากตาราง Job Category และ Job Subcategory
$job_cat_sql = "SELECT * FROM job_category ORDER BY job_category_name";
$job_categories = $conn->query($job_cat_sql);
$job_subcat_sql = "SELECT * FROM job_subcategory ORDER BY job_subcategory_name";
$job_subcategories = $conn->query($job_subcat_sql);
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>Permission</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- bootstrap-icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <!-- CSS สำหรับ Permission และ Sidebar -->
  <link rel="stylesheet" href="style_permission.css">
  <link rel="stylesheet" href="style_sidebar.css">
  
</head>
<body>
<div class="containersb">
    
    <!-- Sidebar -->
<div id="sidebar-container"class="sidebar">
        </div>
    
    <!-- Main Content -->
    <div class="main-content">
    <button id="menuToggle" class="menu-toggle">☰ Menu</button>
        <div class="header">
        <h1 class="page-title">Permission</h1>
      </div>
      <hr class="my-4">

      <!-- SECTION 1: Skill & Subskill -->
      <h2>Skill & Subskill</h2>
      <!-- Skill Section -->
      <div class="permission-box">
        <div class="permission-header">
          <span>ทักษะ (Skill)</span>
          <div class="search-box">
            <span>Search</span>
            <input type="text" id="skill-search" class="search-input" placeholder="ค้นหาทักษะ">
          </div>
        </div>
        <div class="permission-list" id="skill-list">
          <?php while ($row = $skills->fetch_assoc()): ?>
            <div class="item-box" data-type="skill" data-id="<?= $row['skill_id'] ?>" data-skill="<?= strtolower($row['skill_name']) ?>">
              <div class="left">
                <input type="checkbox" class="skill-checkbox" value="<?= $row['skill_id'] ?>">
                <span class="skill-name"><?= htmlspecialchars($row['skill_name']) ?></span>
              </div>
              <button class="edit-btn">แก้ไข</button>
            </div>
          <?php endwhile; ?>
          <button class="add-btn" id="addSkillBtn">+ เพิ่มทักษะ</button>
        </div>
      </div>
      <!-- Subskill Section -->
      <div class="permission-box">
        <div class="permission-header">
          <span>ทักษะย่อย (Subskill)</span>
          <div class="search-box">
            <span>Search</span>
            <input type="text" id="subskill-search" class="search-input" placeholder="ค้นหาทักษะย่อย">
          </div>
        </div>
        <div class="permission-list" id="subskill-list">
          <?php while ($row = $subskills->fetch_assoc()): ?>
            <div class="item-box" data-type="subskill" data-id="<?= $row['subskill_id'] ?>" data-skill-id="<?= $row['skill_id'] ?>" data-subskill="<?= strtolower($row['subskill_name']) ?>">
              <span class="subskill-name"><?= htmlspecialchars($row['subskill_name']) ?></span>
              <button class="edit-btn">แก้ไข</button>
            </div>
          <?php endwhile; ?>
          <button class="add-btn" id="addSubskillBtn">+ เพิ่มทักษะย่อย</button>
        </div>
      </div>

      <!-- SECTION 2: Hobby & Subhobby -->
      <h2>Hobby & Subhobby</h2>
      <!-- Hobby Section -->
      <div class="permission-box">
        <div class="permission-header">
          <span>งานอดิเรก (Hobby)</span>
          <div class="search-box">
            <span>Search</span>
            <input type="text" id="hobby-search" class="search-input" placeholder="ค้นหางานอดิเรก">
          </div>
        </div>
        <div class="permission-list" id="hobby-list">
          <?php while ($row = $hobbies->fetch_assoc()): ?>
            <div class="item-box" data-type="hobby" data-id="<?= $row['hobby_id'] ?>" data-hobby="<?= strtolower($row['hobby_name']) ?>">
              <div class="left">
                <input type="checkbox" class="hobby-checkbox" value="<?= $row['hobby_id'] ?>">
                <span class="hobby-name"><?= htmlspecialchars($row['hobby_name']) ?></span>
              </div>
              <button class="edit-btn">แก้ไข</button>
            </div>
          <?php endwhile; ?>
          <button class="add-btn" id="addHobbyBtn">+ เพิ่มงานอดิเรก</button>
        </div>
      </div>
      <!-- Subhobby Section -->
      <div class="permission-box">
        <div class="permission-header">
          <span>งานอดิเรกย่อย (Subhobby)</span>
          <div class="search-box">
            <span>Search</span>
            <input type="text" id="subhobby-search" class="search-input" placeholder="ค้นหางานอดิเรกย่อย">
          </div>
        </div>
        <div class="permission-list" id="subhobby-list">
          <?php while ($row = $subhobbies->fetch_assoc()): ?>
            <div class="item-box" data-type="subhobby" data-id="<?= $row['subhobby_id'] ?>" data-hobby-id="<?= $row['hobby_id'] ?>" data-subhobby="<?= strtolower($row['subhobby_name']) ?>">
              <span class="subhobby-name"><?= htmlspecialchars($row['subhobby_name']) ?></span>
              <button class="edit-btn">แก้ไข</button>
            </div>
          <?php endwhile; ?>
          <button class="add-btn" id="addSubhobbyBtn">+ เพิ่มงานอดิเรกย่อย</button>
        </div>
      </div>

      <!-- SECTION 3: Job Category & Job Subcategory -->
      <h2>Job Category & Job Subcategory</h2>
      <!-- Job Category Section -->
      <div class="permission-box">
        <div class="permission-header">
          <span>ประเภทงาน (Job Category)</span>
          <div class="search-box">
            <span>Search</span>
            <input type="text" id="jobcat-search" class="search-input" placeholder="ค้นหาประเภทงาน">
          </div>
        </div>
        <div class="permission-list" id="jobcat-list">
          <?php while ($row = $job_categories->fetch_assoc()): ?>
            <div class="item-box" data-type="job_category" data-id="<?= $row['job_category_id'] ?>" data-jobcat="<?= strtolower($row['job_category_name']) ?>">
              <div class="left">
                <input type="checkbox" class="jobcat-checkbox" value="<?= $row['job_category_id'] ?>">
                <span class="jobcat-name"><?= htmlspecialchars($row['job_category_name']) ?></span>
              </div>
              <button class="edit-btn">แก้ไข</button>
            </div>
          <?php endwhile; ?>
          <button class="add-btn" id="addJobCatBtn">+ เพิ่มประเภทงาน</button>
        </div>
      </div>
      <!-- Job Subcategory Section -->
      <div class="permission-box">
        <div class="permission-header">
          <span>งานย่อย (Job Subcategory)</span>
          <div class="search-box">
            <span>Search</span>
            <input type="text" id="jobsubcat-search" class="search-input" placeholder="ค้นหางานย่อย">
          </div>
        </div>
        <div class="permission-list" id="jobsubcat-list">
          <?php while ($row = $job_subcategories->fetch_assoc()): ?>
            <div class="item-box" data-type="job_subcategory" data-id="<?= $row['job_subcategory_id'] ?>" data-jobcat-id="<?= $row['job_category_id'] ?>" data-jobsubcat="<?= strtolower($row['job_subcategory_name']) ?>">
              <span class="jobsubcat-name"><?= htmlspecialchars($row['job_subcategory_name']) ?></span>
              <button class="edit-btn">แก้ไข</button>
            </div>
          <?php endwhile; ?>
          <button class="add-btn" id="addJobSubCatBtn">+ เพิ่มงานย่อย</button>
        </div>
      </div>

      <!-- Modal สำหรับเพิ่ม Skill -->
      <div id="addSkillModal" class="modal" style="display:none;">
        <div class="modal-content">
          <span class="close" onclick="closeModal('addSkillModal')">&times;</span>
          <h3>เพิ่มทักษะ</h3>
          <input type="text" placeholder="ชื่อทักษะ" id="new-skill-name">
          <button onclick="saveNewSkill()">บันทึก</button>
        </div>
      </div>

      <!-- Modal สำหรับเพิ่ม Subskill -->
      <div id="addSubskillModal" class="modal" style="display:none;">
        <div class="modal-content">
          <span class="close" onclick="closeModal('addSubskillModal')">&times;</span>
          <h3>เพิ่มทักษะย่อย</h3>
          <input type="text" placeholder="ชื่อทักษะย่อย" id="new-subskill-name">
          <button onclick="saveNewSubskill()">บันทึก</button>
        </div>
      </div>

      <!-- Modal สำหรับเพิ่ม Hobby -->
      <div id="addHobbyModal" class="modal" style="display:none;">
        <div class="modal-content">
          <span class="close" onclick="closeModal('addHobbyModal')">&times;</span>
          <h3>เพิ่มงานอดิเรก</h3>
          <input type="text" placeholder="ชื่องานอดิเรก" id="new-hobby-name">
          <button onclick="saveNewHobby()">บันทึก</button>
        </div>
      </div>

      <!-- Modal สำหรับเพิ่ม Subhobby -->
      <div id="addSubhobbyModal" class="modal" style="display:none;">
        <div class="modal-content">
          <span class="close" onclick="closeModal('addSubhobbyModal')">&times;</span>
          <h3>เพิ่มงานอดิเรกย่อย</h3>
          <input type="text" placeholder="ชื่องานอดิเรกย่อย" id="new-subhobby-name">
          <button onclick="saveNewSubhobby()">บันทึก</button>
        </div>
      </div>

      <!-- Modal สำหรับเพิ่ม Job Category -->
      <div id="addJobCatModal" class="modal" style="display:none;">
        <div class="modal-content">
          <span class="close" onclick="closeModal('addJobCatModal')">&times;</span>
          <h3>เพิ่มประเภทงาน</h3>
          <input type="text" placeholder="ชื่อประเภทงาน" id="new-jobcat-name">
          <button onclick="saveNewJobCat()">บันทึก</button>
        </div>
      </div>

      <!-- Modal สำหรับเพิ่ม Job Subcategory -->
      <div id="addJobSubCatModal" class="modal" style="display:none;">
        <div class="modal-content">
          <span class="close" onclick="closeModal('addJobSubCatModal')">&times;</span>
          <h3>เพิ่มงานย่อย</h3>
          <input type="text" placeholder="ชื่องานย่อย" id="new-jobsubcat-name">
          <button onclick="saveNewJobSubCat()">บันทึก</button>
        </div>
      </div>

      <!-- Modal สำหรับแก้ไขข้อมูล (ใช้ร่วมกันได้ทุกประเภท) -->
      <div id="editModal" class="modal" style="display:none;">
        <div class="modal-content">
          <span class="close" onclick="closeModal('editModal')">&times;</span>
          <form id="editForm">
            <h3>แก้ไขข้อมูล</h3>
            <input type="hidden" id="edit-id">
            <input type="hidden" id="edit-type">
            <input type="text" id="edit-name" placeholder="ชื่อใหม่" required>
            <button type="submit">บันทึก</button>
          </form>
        </div>
      </div>

    </div>
  </div>
  <script src="script_permission.js"></script>
  <script src="script_sidebar.js"></script>
  <script>
 document.addEventListener("DOMContentLoaded", function () {
  const sidebar = document.querySelector('.sidebar');
  const toggleBtn = document.getElementById('menuToggle');

  console.log('sidebar:', sidebar); // ✅ ต้องไม่เป็น null
  console.log('toggleBtn:', toggleBtn);

  if (sidebar && toggleBtn) {
    toggleBtn.addEventListener('click', function () {
      sidebar.classList.toggle('active');
    });
  }
});

</script>


</body>
</html>