// top55.js (อัพเดต)

// We'll store references to each chart in a dictionary:
const chartInstances = {};

// ======= 1) Load Filter Options on DOMContentLoaded
document.addEventListener("DOMContentLoaded", function () {
  Promise.all([
    loadCategoryOptions(),
    loadStatusOptions(),
    loadRewardOptions(),
    loadTeacherOptions(),
    loadSkillOptions(),
    loadMajorOptions(),
    loadYearOptions(),
    loadGenderOptions(),  // เพิ่มใหม่
    loadHobbyOptions(),   // เพิ่มใหม่
    loadSubcategoryOptions() // เพิ่มใหม่
  ]).then(() => {
    // Now that we've loaded all filter options, we can do the main logic
    
    loadTopRankings();
    
    
    // Add event listeners
    addEventListeners();
  }).catch(err => {
    console.error("Error loading filter options:", err);
    // even if they fail, still attempt to load main logic
    loadTopRankings();
  });
});

// ฟังก์ชันสำหรับโหลดเฉพาะแต่ละชาร์ต
function loadStudentsChart() {
  const filters = getFilterValues();
  const sortBy = document.getElementById("studentSort")?.value || "rating";
  let paramsStudents = buildParams("top5-students", filters);
  paramsStudents.set("sort", sortBy);

  fetch("api/api.php?" + paramsStudents.toString())
    .then(res => res.json())
    .then(data => {
      const limit = parseInt(filters.studentLimit) || 5;
      const top5 = data.slice(0, limit);
      const labels = top5.map(d => d.name || d.stu_name);
      let values, chartLabel;
      if (sortBy === "accept") {
        values = top5.map(d => Number(d.accept_count) || 0);
        chartLabel = "Accepted Jobs";
      } else {
        values = top5.map(d => Number(d.avg_rating) || 0);
        chartLabel = "Avg Rating";
      }
      renderBarChart("studentsChart", labels, values, chartLabel, "#FF6B00");
    })
    .catch(err => console.error("Error fetching top5-students:", err));
}

function loadProfessorsChart() {
  const filters = getFilterValues();
  let paramsProf = buildParams("top-professors", filters);
  fetch("api/api.php?" + paramsProf.toString())
    .then(res => res.json())
    .then(data => {
      const labels = data.map(d => d.name || d.teach_name);
      let values, chartLabel;
      
      if (filters.activityType === "success") {
        values = data.map(d => Number(d.success_count || 0));
        chartLabel = "Successful Jobs";
      } else if (filters.activityType === "rate") {
        values = data.map(d => {
          const total = Number(d.total_posts || 0);
          const success = Number(d.success_count || 0);
          return total > 0 ? (success / total * 100).toFixed(1) : 0;
        });
        chartLabel = "Success Rate (%)";
      } else {
        values = data.map(d => Number(d.job_count));
        chartLabel = "Posted Jobs";
      }
      
      renderBarChart("professorsChart", labels, values, chartLabel, "#4B0082");
    })
    .catch(err => console.error("Error fetching top-professors:", err));
}

function loadJobsChart() {
  const filters = getFilterValues();
  let paramsJobs = buildParams("top-jobs", filters);
  fetch("api/api.php?" + paramsJobs.toString())
    .then(res => res.json())
    .then(data => {
      const labels = data.map(d => d.title);
      const values = data.map(d => Number(d.total_applications));
      renderPieChart("jobsChart", labels, values, ["#FFD700","#C0C0C0","#CD7F32","#FF6B00","#4B0082"]);
    })
    .catch(err => console.error("Error fetching top-jobs:", err));
}

function loadSkillsChart() {
  const filters = getFilterValues();
  let paramsSupply = buildParams("supply-demand-skills", filters);
  console.log("Supply vs Demand Parameters:", paramsSupply.toString());
  fetch("api/api.php?" + paramsSupply.toString())
    .then(res => res.json())
    .then(data => {
      console.log("Supply vs Demand Raw Data:", data);
      let filtered = data;
      
      // กรองตาม displayMode ถ้ามีการเลือก
      if (filters.skillDisplayMode === 'high-demand') {
        filtered = data.filter(d => Number(d.demand) > Number(d.supply));
      } else if (filters.skillDisplayMode === 'surplus') {
        filtered = data.filter(d => Number(d.supply) > Number(d.demand));
      } else {
        // กรองแบบพื้นฐาน - ตัดข้อมูลที่ทั้ง supply และ demand เป็น 0
        filtered = data.filter(d => Number(d.supply) > 0 || Number(d.demand) > 0);
      }
      
      // Debug: ตรวจสอบข้อมูลหลังกรอง
      console.log("Supply vs Demand Filtered Data:", filtered);
      
      // ถ้าไม่มีข้อมูลหลังการกรอง
      if (filtered.length === 0) {
        console.warn("No data to display after filtering");
        // สร้างกราฟว่าง หรือแสดงข้อความไม่มีข้อมูล
        renderEmptyChart("supplyDemandChart", "No data available for the selected filters");
        return;
      }
      
      const labels = filtered.map(d => d.skills_name || d.skill_name);
      const supplyValues = filtered.map(d => Number(d.supply));
      const demandValues = filtered.map(d => Number(d.demand));
      
      renderGroupedBarChart("supplyDemandChart", labels, supplyValues, demandValues, "Supply", "Demand");
    })
    .catch(err => {
      console.error("Error fetching supply-demand-skills:", err);
      renderEmptyChart("supplyDemandChart", "Error loading data");
    });
}
function renderEmptyChart(canvasId, message) {
  if (chartInstances[canvasId]) {
    chartInstances[canvasId].destroy();
  }
  
  const canvas = document.getElementById(canvasId);
  const ctx = canvas.getContext('2d');
  
  // เคลียร์ canvas
  ctx.clearRect(0, 0, canvas.width, canvas.height);
  
  // แสดงข้อความกลาง canvas
  ctx.font = '16px Arial';
  ctx.fillStyle = '#666';
  ctx.textAlign = 'center';
  ctx.fillText(message, canvas.width / 2, canvas.height / 2);
}

// Add event listeners for dependent filters
function addEventListeners() {
  // When category changes, update subcategory
  const categorySelect = document.getElementById("category");
  if (categorySelect) {
    categorySelect.addEventListener('change', function() {
      loadSubcategoryOptions(this.value);
    });
  }
  
  // When mainSkill changes, update subskill
  const mainSkillSelect = document.getElementById("mainSkill");
  if (mainSkillSelect) {
    mainSkillSelect.addEventListener('change', function() {
      loadSubskillOptions(this.value);
    });
  }
  
  // When hobby changes, update subhobby
  const hobbySelect = document.getElementById("hobby");
  if (hobbySelect) {
    hobbySelect.addEventListener('change', function() {
      loadSubhobbyOptions(this.value);
    });
  }
  document.getElementById("applyStudentFilters")?.addEventListener("click", function() {
    loadStudentsChart();
  });
  
  document.getElementById("applyProfessorFilters")?.addEventListener("click", function() {
    loadProfessorsChart();
  });
  
  document.getElementById("applyJobFilters")?.addEventListener("click", function() {
    loadJobsChart();
  });
  
  document.getElementById("applySkillFilters")?.addEventListener("click", function() {
    loadSkillsChart();
  });
  
  // ปุ่ม Apply สำหรับ Global Filters ควรโหลดทุกชาร์ต
  document.getElementById("applyGlobalFilters")?.addEventListener("click", function() {
    loadTopRankings();
  });
}

// ======= 2) Loading Filter Options (category, status, reward, teacher)
function loadCategoryOptions() {
  return fetch("api/api.php?endpoint=categories-list")
    .then(res => res.json())
    .then(data => {
      const categorySelect = document.getElementById("category");
      categorySelect.innerHTML = '<option value="">All</option>';
      data.forEach(item => {
        const opt = document.createElement("option");
        opt.value = item.job_category_id;
        opt.textContent = item.job_category_name;
        categorySelect.appendChild(opt);
      });
    });
}

function loadStatusOptions() {
  return fetch("api/api.php?endpoint=status-list")
    .then(res => res.json())
    .then(data => {
      const statusSelect = document.getElementById("status");
      statusSelect.innerHTML = '<option value="">All</option>';
      data.forEach(item => {
        const opt = document.createElement("option");
        opt.value = item.job_status_id;
        opt.textContent = item.job_status_name;
        statusSelect.appendChild(opt);
      });
    });
}

function loadRewardOptions() {
  return fetch("api/api.php?endpoint=reward-list")
    .then(res => res.json())
    .then(data => {
      const rewardSelect = document.getElementById("reward");
      rewardSelect.innerHTML = '<option value="">All</option>';
      data.forEach(item => {
        const opt = document.createElement("option");
        opt.value = item.reward_type_id;
        opt.textContent = item.reward_type_name;
        rewardSelect.appendChild(opt);
      });
    });
}

function loadTeacherOptions() {
  return fetch("api/api.php?endpoint=teacher-list")
    .then(res => res.json())
    .then(data => {
      const teacherSelect = document.getElementById("teacher");
      teacherSelect.innerHTML = '<option value="">All</option>';
      data.forEach(item => {
        const opt = document.createElement("option");
        opt.value = item.teacher_id;
        opt.textContent = item.teach_name;
        teacherSelect.appendChild(opt);
      });
    });
}

function loadSkillOptions() {
  return fetch("api/api.php?endpoint=skill-list")
    .then(res => res.json())
    .then(data => {
      // เพิ่มใหม่: เติมข้อมูลลงใน mainSkill และ skillFilter
      const skillSelects = [
        document.getElementById("skillFilter"),
        document.getElementById("mainSkill")
      ];
      
      skillSelects.forEach(select => {
        if (select) {
          select.innerHTML = '<option value="">All</option>';
          data.forEach(item => {
            const opt = document.createElement("option");
            opt.value = item.skill_id;
            opt.textContent = item.skill_name;
            select.appendChild(opt);
          });
        }
      });
    });
}

// เพิ่มฟังก์ชันโหลดตัวเลือกสาขาวิชา
function loadMajorOptions() {
  return fetch("api/api.php?endpoint=major-list")
    .then(res => res.json())
    .then(data => {
      // เติมข้อมูลลงใน select ทั้ง major และ studentMajor และ professorMajor
      const majorSelects = [
        document.getElementById("major"),
        document.getElementById("studentMajor"),
        document.getElementById("professorMajor")
      ];
      
      majorSelects.forEach(select => {
        if (select) {
          select.innerHTML = '<option value="">All</option>';
          data.forEach(item => {
            const opt = document.createElement("option");
            opt.value = item.major_id;
            opt.textContent = item.major_name;
            select.appendChild(opt);
          });
        }
      });
    });
}

// เพิ่มฟังก์ชันโหลดตัวเลือกชั้นปี
function loadYearOptions() {
  return fetch("api/api.php?endpoint=year-list")
    .then(res => res.json())
    .then(data => {
      const yearSelects = [
        document.getElementById("year"),
        document.getElementById("studentYear")
      ];
      
      yearSelects.forEach(select => {
        if (select) {
          select.innerHTML = '<option value="">All</option>';
          data.forEach(item => {
            const opt = document.createElement("option");
            opt.value = item.year;
            opt.textContent = item.year;
            select.appendChild(opt);
          });
        }
      });
    });
}

// เพิ่มฟังก์ชันใหม่สำหรับตัวเลือกเพศ
function loadGenderOptions() {
  return fetch("api/api.php?endpoint=gender-list")
    .then(res => res.json())
    .then(data => {
      const genderSelects = [
        document.getElementById("gender"),
        document.getElementById("studentGender"),
        document.getElementById("teacherGender")
      ];
      
      genderSelects.forEach(select => {
        if (select) {
          select.innerHTML = '<option value="">All</option>';
          data.forEach(item => {
            const opt = document.createElement("option");
            opt.value = item.gender_id;
            opt.textContent = item.gender_name;
            select.appendChild(opt);
          });
        }
      });
    });
}

// เพิ่มฟังก์ชันใหม่สำหรับตัวเลือกหมวดหมู่ย่อย
function loadSubcategoryOptions(categoryId) {
  let url = "api/api.php?endpoint=subcategory-list";
  if (categoryId) {
    url += `&category=${categoryId}`;
  }
  
  return fetch(url)
    .then(res => res.json())
    .then(data => {
      const subcategorySelect = document.getElementById("subcategory");
      if (subcategorySelect) {
        subcategorySelect.innerHTML = '<option value="">All</option>';
        data.forEach(item => {
          const opt = document.createElement("option");
          opt.value = item.job_subcategory_id;
          opt.textContent = item.job_subcategory_name;
          subcategorySelect.appendChild(opt);
        });
      }
    });
}

// เพิ่มฟังก์ชันใหม่สำหรับตัวเลือกทักษะย่อย
function loadSubskillOptions(skillId) {
  let url = "api/api.php?endpoint=subskill-list";
  if (skillId) {
    url += `&skill=${skillId}`;
  }
  
  return fetch(url)
    .then(res => res.json())
    .then(data => {
      const subskillSelect = document.getElementById("subSkill");
      if (subskillSelect) {
        subskillSelect.innerHTML = '<option value="">All</option>';
        data.forEach(item => {
          const opt = document.createElement("option");
          opt.value = item.subskill_id;
          opt.textContent = item.subskill_name;
          subskillSelect.appendChild(opt);
        });
      }
    });
}

// เพิ่มฟังก์ชันใหม่สำหรับตัวเลือกงานอดิเรก
function loadHobbyOptions() {
  return fetch("api/api.php?endpoint=hobby-list")
    .then(res => res.json())
    .then(data => {
      const hobbySelect = document.getElementById("hobby");
      if (hobbySelect) {
        hobbySelect.innerHTML = '<option value="">All</option>';
        data.forEach(item => {
          const opt = document.createElement("option");
          opt.value = item.hobby_id;
          opt.textContent = item.hobby_name;
          hobbySelect.appendChild(opt);
        });
      }
    });
}

// เพิ่มฟังก์ชันใหม่สำหรับตัวเลือกงานอดิเรกย่อย
function loadSubhobbyOptions(hobbyId) {
  let url = "api/api.php?endpoint=subhobby-list";
  if (hobbyId) {
    url += `&hobby=${hobbyId}`;
  }
  
  return fetch(url)
    .then(res => res.json())
    .then(data => {
      const subhobbySelect = document.getElementById("subHobby");
      if (subhobbySelect) {
        subhobbySelect.innerHTML = '<option value="">All</option>';
        data.forEach(item => {
          const opt = document.createElement("option");
          opt.value = item.subhobby_id;
          opt.textContent = item.subhobby_name;
          subhobbySelect.appendChild(opt);
        });
      }
    });
}

// ======= 3) When user clicks "Apply" button
function applyFilters() {
  loadTopRankings();
}

// ======= 4) Load the top rankings and charts
function loadTopRankings() {
  loadStudentsChart();
  loadProfessorsChart();
  loadJobsChart();
  loadSkillsChart();
  const filters = getFilterValues();

  // (A) Top Students: rating or accept
  const sortBy = document.getElementById("studentSort")?.value || "rating";
  let paramsStudents = buildParams("top5-students", filters);
  paramsStudents.set("sort", sortBy);

  fetch("api/api.php?" + paramsStudents.toString())
    .then(res => res.json())
    .then(data => {
      const limit = parseInt(filters.studentLimit) || 5;
      const top5 = data.slice(0, limit);
      const labels = top5.map(d => d.name || d.stu_name);
      let values, chartLabel;
      if (sortBy === "accept") {
        values = top5.map(d => Number(d.accept_count) || 0);
        chartLabel = "Accepted Jobs";
      } else {
        values = top5.map(d => Number(d.avg_rating) || 0);
        chartLabel = "Avg Rating";
      }
      renderBarChart("studentsChart", labels, values, chartLabel, "#FF6B00");
    })
    .catch(err => console.error("Error fetching top5-students:", err));

  // (B) Top Professors
  let paramsProf = buildParams("top-professors", filters);
  fetch("api/api.php?" + paramsProf.toString())
    .then(res => res.json())
    .then(data => {
      const labels = data.map(d => d.name || d.teach_name);
      let values, chartLabel;
      
      // กรณีมี activityType ซึ่งจะระบุประเภทการวัดความมีส่วนร่วม
      if (filters.activityType === "success") {
        values = data.map(d => Number(d.success_count || 0));
        chartLabel = "Successful Jobs";
      } else if (filters.activityType === "rate") {
        values = data.map(d => {
          const total = Number(d.total_posts || 0);
          const success = Number(d.success_count || 0);
          return total > 0 ? (success / total * 100).toFixed(1) : 0;
        });
        chartLabel = "Success Rate (%)";
      } else {
        values = data.map(d => Number(d.job_count));
        chartLabel = "Posted Jobs";
      }
      
      renderBarChart("professorsChart", labels, values, chartLabel, "#4B0082");
    })
    .catch(err => console.error("Error fetching top-professors:", err));

  // (C) Top Jobs
  let paramsJobs = buildParams("top-jobs", filters);
  fetch("api/api.php?" + paramsJobs.toString())
    .then(res => res.json())
    .then(data => {
      const labels = data.map(d => d.title);
      const values = data.map(d => Number(d.total_applications));
      renderPieChart("jobsChart", labels, values, ["#FFD700","#C0C0C0","#CD7F32","#FF6B00","#4B0082"]);
    })
    .catch(err => console.error("Error fetching top-jobs:", err));

  // (D) Supply vs Demand (Skills)
  let paramsSupply = buildParams("supply-demand-skills", filters);
  fetch("api/api.php?" + paramsSupply.toString())
    .then(res => res.json())
    .then(data => {
      // Filter data based on displayMode if provided
      let filtered = data;
      
      if (filters.skillDisplayMode === 'high-demand') {
        filtered = data.filter(d => Number(d.demand) > Number(d.supply));
      } else if (filters.skillDisplayMode === 'surplus') {
        filtered = data.filter(d => Number(d.supply) > Number(d.demand));
      } else {
        // 'all' mode - just filter out zeros in both
        filtered = data.filter(d => Number(d.supply) > 0 || Number(d.demand) > 0);
      }
      
      const labels = filtered.map(d => d.skills_name || d.skill_name);
      const supplyValues = filtered.map(d => Number(d.supply));
      const demandValues = filtered.map(d => Number(d.demand));
      renderGroupedBarChart("supplyDemandChart", labels, supplyValues, demandValues, "Supply", "Demand");
    })
    .catch(err => console.error("Error fetching supply-demand-skills:", err));
}

// ======= Helper Functions =======
function getFilterValues() {
  return {
    // Global filters
    start: document.getElementById('startDate')?.value || '',
    end: document.getElementById('endDate')?.value || '',
    category: document.getElementById('category')?.value || '',
    status: document.getElementById('status')?.value || '',
    reward: document.getElementById('reward')?.value || '',
    teacher: document.getElementById('teacher')?.value || '',
    major: document.getElementById('major')?.value || '',
    year: document.getElementById('year')?.value || '',
    
    // Chart-specific filters 
    // (ระบุตัวเลือกเฉพาะแต่ละชาร์ต ถ้าไม่มีจะใช้ค่า global filters)
    
    // Top Students filters
    studentMajor: document.getElementById('studentMajor')?.value || document.getElementById('major')?.value || '',
    studentYear: document.getElementById('studentYear')?.value || document.getElementById('year')?.value || '',
    studentGender: document.getElementById('studentGender')?.value || document.getElementById('gender')?.value || '',
    studentLimit: document.getElementById('studentLimit')?.value || '5',
    
    // Top Professors filters
    professorMajor: document.getElementById('professorMajor')?.value || document.getElementById('major')?.value || '',
    teacherGender: document.getElementById('teacherGender')?.value || document.getElementById('gender')?.value || '',
    activityType: document.getElementById('activityType')?.value || 'posts',
    professorLimit: document.getElementById('professorLimit')?.value || '5',
    
    // Most Popular Jobs filters
    jobSortBy: document.getElementById('jobSortBy')?.value || 'applications',
    subcategory: document.getElementById('subcategory')?.value || '',
    jobLimit: document.getElementById('jobLimit')?.value || '5',
    
    // Supply vs Demand Skills filters
    mainSkill: document.getElementById('mainSkill')?.value || '',
    subSkill: document.getElementById('subSkill')?.value || '',
    hobby: document.getElementById('hobby')?.value || '',
    subHobby: document.getElementById('subHobby')?.value || '',
    gender: document.getElementById('gender')?.value || '',
    skillFilter: document.getElementById('skillFilter')?.value || '',
    skillDisplayMode: document.querySelector('input[name="skillDisplayMode"]:checked')?.value || 'all'
  };
}

function buildParams(endpoint, filters) {
  const p = new URLSearchParams({ endpoint });
  
  // Add global filters
  if (filters.start) p.set('start', filters.start);
  if (filters.end) p.set('end', filters.end);
  if (filters.category) p.set('category', filters.category);
  if (filters.status) p.set('status', filters.status);
  if (filters.reward) p.set('reward', filters.reward);
  if (filters.teacher) p.set('teacher', filters.teacher);
  
  // Add specific filters for each endpoint
  switch(endpoint) {
    case 'top5-students':
      // สำหรับ Top Students
      if (filters.studentMajor || filters.major) p.set('major', filters.studentMajor || filters.major);
      if (filters.studentYear || filters.year) p.set('year', filters.studentYear || filters.year);
      if (filters.studentGender || filters.gender) p.set('gender', filters.studentGender || filters.gender);
      if (filters.studentLimit) p.set('limit', filters.studentLimit);
      if (filters.hobby) p.set('hobby', filters.hobby);
      if (filters.subHobby) p.set('subhobby', filters.subHobby);
      if (filters.mainSkill) p.set('skill', filters.mainSkill);
      if (filters.subSkill) p.set('subskill', filters.subSkill);
      break;
      
    case 'top-professors':
      // สำหรับ Top Professors
      if (filters.professorMajor || filters.major) p.set('major', filters.professorMajor || filters.major);
      if (filters.teacherGender || filters.gender) p.set('gender', filters.teacherGender || filters.gender);
      if (filters.activityType) p.set('activityType', filters.activityType);
      if (filters.professorLimit) p.set('limit', filters.professorLimit);
      break;
      
    case 'top-jobs':
      // สำหรับ Top Jobs
      if (filters.category) p.set('category', filters.category);
      if (filters.subcategory) p.set('subcategory', filters.subcategory);
      if (filters.jobSortBy) p.set('sortBy', filters.jobSortBy);
      if (filters.jobLimit) p.set('limit', filters.jobLimit);
      if (filters.mainSkill) p.set('skill', filters.mainSkill);
      if (filters.subSkill) p.set('subskill', filters.subSkill);
      break;
      
    case 'supply-demand-skills':
      // สำหรับ Supply vs Demand
      if (filters.major) p.set('major', filters.major);
      if (filters.year) p.set('year', filters.year);
      if (filters.gender) p.set('gender', filters.gender);
      if (filters.mainSkill) p.set('skill', filters.mainSkill);
      if (filters.skillFilter) p.set('skill', filters.skillFilter);
      if (filters.subSkill) p.set('subskill', filters.subSkill);
      if (filters.hobby) p.set('hobby', filters.hobby);
      if (filters.subHobby) p.set('subhobby', filters.subHobby);
      break;
  }
  
  return p;
}

// ======= Chart Renderers =======
function renderBarChart(canvasId, labels, values, labelText, bgColor) {
  if (chartInstances[canvasId]) {
    chartInstances[canvasId].destroy();
  }
  chartInstances[canvasId] = new Chart(document.getElementById(canvasId), {
    type: 'bar',
    data: {
      labels,
      datasets: [{
        label: labelText,
        data: values,
        backgroundColor: bgColor
      }]
    },
    options: { responsive: true, maintainAspectRatio: false }
  });
}

function renderPieChart(canvasId, labels, values, colorsArray, chartTitle = "") {
  // Calculate total sum of values
  const total = values.reduce((acc, cur) => acc + Number(cur), 0);

  if (chartInstances[canvasId]) {
      chartInstances[canvasId].destroy();
  }

  // Set a threshold: show data labels if there are 8 or fewer slices
  const showDataLabels = labels.length <= 8;

  chartInstances[canvasId] = new Chart(document.getElementById(canvasId), {
      type: "pie",
      data: {
          labels,
          datasets: [{
              data: values,
              backgroundColor: colorsArray
          }]
      },
      options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
              title: {
                  display: chartTitle !== "",
                  text: chartTitle,
                  font: { size: 16 }
              },
              datalabels: {
                  display: showDataLabels,  // conditionally display labels
                  formatter: function(value, context) {
                      let label = context.chart.data.labels[context.dataIndex] || "";
                      // Optionally, break the label into multiple lines if it's long:
                      if (label.length > 15) {
                          label = label.match(/.{1,15}/g).join("\n");
                      }
                      const total = context.chart.data.datasets[0].data.reduce((acc, cur) => acc + Number(cur), 0);
                      const percent = ((value / total) * 100).toFixed(1) + '%';
                      return label + "\n" + percent;
                  },
                  color: '#fff',
                  font: { weight: 'bold', size: 8 },
                  padding: 6,
                  clip: true
              },
              tooltip: {
                  enabled: true
              }
          }
      },
      plugins: [ChartDataLabels]
  });
}

// Render a grouped bar chart for Supply vs Demand skills
function renderGroupedBarChart(canvasId, labels, supplyValues, demandValues, supplyLabel, demandLabel) {
    if (chartInstances[canvasId]) {
      chartInstances[canvasId].destroy();
    }
    chartInstances[canvasId] = new Chart(document.getElementById(canvasId), {
      type: 'bar',
      data: {
        labels,
        datasets: [
          {
            label: supplyLabel,
            data: supplyValues,
            backgroundColor: '#66BB6A'
          },
          {
            label: demandLabel,
            data: demandValues,
            backgroundColor: '#FF7043'
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          x: { stacked: false, ticks: { font: { size: 14 } } },
          y: { stacked: false, beginAtZero: true, ticks: { font: { size: 14 } } }
        }
      }
    });
  }