// studentanaly.js

// Dictionary to store Chart.js instances
const chartInstances = {};

document.addEventListener("DOMContentLoaded", function () {
    Promise.all([
        loadCategoryOptions(), 
        loadStatusOptions(),
        loadRewardOptions(),
        loadTeacherOptions(),
        loadMajorOptions(),
        loadYearOptions()
    ]).then(() => {
        // ทำการ initialize ทุก chart โดยใช้ global filters
        initializeAllCharts();

        // ทำการเติมข้อมูลให้กับ filter ของแต่ละ chart
        initializeAllChartFilters();
        
        // เพิ่ม event listeners สำหรับปุ่ม Apply Filters ของแต่ละ chart
        setupFilterEventListeners();
        
    }).catch(err => {
        console.error("Error loading filter options:", err);
        // even if they fail, still attempt to load main logic
        initializeAllCharts();
    });
});

// เติม options สำหรับ filters ทั้งหมดที่มีค่าเหมือนกัน
function initializeAllChartFilters() {
    // Category Options
    const categorySelectors = [
        "ratingCategory", "majorCategory", "gpaCategory"
    ];
    
    categorySelectors.forEach(selector => {
        const selectElement = document.getElementById(selector);
        if (selectElement) {
            copyOptionsFromSelect("category", selectElement);
        }
    });
    
    // Teacher Options
    const teacherSelectors = [
        "ratingTeacher"
    ];
    
    teacherSelectors.forEach(selector => {
        const selectElement = document.getElementById(selector);
        if (selectElement) {
            copyOptionsFromSelect("teacher", selectElement);
        }
    });
}

// ช่วยคัดลอก options จาก select หนึ่งไปอีก select หนึ่ง
function copyOptionsFromSelect(sourceId, targetElement) {
    const sourceElement = document.getElementById(sourceId);
    if (!sourceElement || !targetElement) return;
    
    // เก็บค่าเดิมไว้ถ้ามี
    const currentValue = targetElement.value;
    
    // คัดลอก options ทั้งหมด
    targetElement.innerHTML = sourceElement.innerHTML;
    
    // ใส่ค่าเดิมกลับ (ถ้ามี)
    if (currentValue) {
        targetElement.value = currentValue;
    }
}

// เพิ่ม event listeners สำหรับปุ่ม Apply Filters ของแต่ละ chart
function setupFilterEventListeners() {
    // Global Filters
    document.getElementById("applyGlobalFilters")?.addEventListener("click", function() {
        applyGlobalFilters();
    });
    
    // Active Students Filters
    document.getElementById("applyActiveStudentsFilters")?.addEventListener("click", function() {
        loadActiveStudentsChart();
    });
    
    // Rating Students Filters
    document.getElementById("applyRatingFilters")?.addEventListener("click", function() {
        loadStudentRatingChart();
    });
    
    // Major Distribution Filters
    document.getElementById("applyMajorFilters")?.addEventListener("click", function() {
        loadMajorDistributionChart();
    });
    
    // GPA Filters
    document.getElementById("applyGpaFilters")?.addEventListener("click", function() {
        loadAvgGpaChart();
    });
    
    // Top 5 Filters
    // document.getElementById("applyTop5Filters")?.addEventListener("click", function() {
    //     loadTop5CompletedChart();
    // });
    
    // Apps Per Student Filters
    document.getElementById("applyAppsPerStudentFilters")?.addEventListener("click", function() {
        loadAppsPerStudentChart();
    });
}

// รีเซ็ต filters ทั้งหมด
function resetFilters() {
    // Global Filters
    document.getElementById('startDate').value = '';
    document.getElementById('endDate').value = '';
    document.getElementById('category').value = '';
    document.getElementById('status').value = '';
    document.getElementById('reward').value = '';
    document.getElementById('teacher').value = '';
    document.getElementById('major').value = '';
    document.getElementById('year').value = '';
    
    // ฟังก์ชันช่วยรีเซ็ต filter ของแต่ละชาร์ต
    function resetChartFilters(prefix, fields) {
        fields.forEach(field => {
            const element = document.getElementById(prefix + field);
            if (element) element.value = '';
        });
    }
    
    // Reset Active Students Filters
    resetChartFilters('activeStudents', ['Major', 'Year']);
    
    // Reset Rating Filters
    resetChartFilters('rating', ['Category', 'Teacher']);
    
    // Reset Major Filters
    resetChartFilters('major', ['Year', 'Category']);
    
    // Reset GPA Filters
    resetChartFilters('gpa', ['Major', 'Year', 'Category']);
    
    // Reset Top 5 Filters
    resetChartFilters('top5', ['Major', 'Year']);
    document.getElementById('top5Limit').value = '5';
    
    // Reset Apps Per Student Filters
    resetChartFilters('appsPerStudent', ['Major', 'Year']);
    document.getElementById('appsPerStudentLimit').value = '5';
    
    // Apply reset to reload all charts
    applyGlobalFilters();
}

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

function loadMajorOptions() {
    return fetch("api/api.php?endpoint=major-list")
      .then(res => res.json())
      .then(data => {
        const majorSelect = document.getElementById("major");
        majorSelect.innerHTML = '<option value="">All</option>';
        data.forEach(item => {
          const opt = document.createElement("option");
          opt.value = item.major_id;
          opt.textContent = item.major_name;
          majorSelect.appendChild(opt);
        });
      });
}

function loadYearOptions() {
    return fetch("api/api.php?endpoint=year-list")
      .then(res => res.json())
      .then(data => {
        const yearSelect = document.getElementById("year");
        yearSelect.innerHTML = '<option value="">All</option>';
        data.forEach(item => {
          const opt = document.createElement("option");
          opt.value = item.year;
          opt.textContent = "Year " + item.year;
          yearSelect.appendChild(opt);
        });
      });
}

// Apply Global Filters to all charts
function applyGlobalFilters() {
    initializeAllCharts();
}

// Initialize all charts with global filters
function initializeAllCharts() {
    loadActiveStudentsChart();
    loadStudentRatingChart();
    loadMajorDistributionChart();
    loadAvgGpaChart();
    // loadTop5CompletedChart();
    loadAppsPerStudentChart();
}

// ============== Individual Chart Loaders ==============

// 1. Active Students Chart
function loadActiveStudentsChart() {
    const globalFilters = getGlobalFilterValues();
    const specificFilters = {
        major: document.getElementById('activeStudentsMajor')?.value || '',
        year: document.getElementById('activeStudentsYear')?.value || ''
    };
    
    // Use specific filters if provided, otherwise fall back to global filters
    const major = specificFilters.major || globalFilters.major;
    const year = specificFilters.year || globalFilters.year;
    
    // Build params
    let params = new URLSearchParams({ endpoint: "active-students" });
    if (globalFilters.start) params.set('start', globalFilters.start);
    if (globalFilters.end) params.set('end', globalFilters.end);
    if (major) params.set('major', major);
    if (year) params.set('year', year);
    
    fetch("api/api.php?" + params.toString())
        .then(res => res.json())
        .then(data => {
            if (data && data[0]) {
                // Parse the three metrics
                const total = Number(data[0].total_students);
                const accepted = Number(data[0].accepted_students);
                const notAccepted = Number(data[0].not_accepted_students);

                // Update the total students text
                document.getElementById("totalStudents").textContent = total;

                // Render a bar chart comparing all three metrics
                renderBarChart(
                    "studentsChart", 
                    ["Job Accepted", "No Job"], 
                    [accepted, notAccepted], 
                    "Student Comparison", 
                    "#4B0082"
                );
            }
        })
        .catch(err => console.error("Error fetching active-students:", err));
}

// 2. Student Rating Chart
function loadStudentRatingChart() {
    const globalFilters = getGlobalFilterValues();
    const specificFilters = {
        category: document.getElementById('ratingCategory')?.value || '',
        teacher: document.getElementById('ratingTeacher')?.value || ''
    };
    
    // Use specific filters if provided, otherwise fall back to global filters
    const category = specificFilters.category || globalFilters.category;
    const teacher = specificFilters.teacher || globalFilters.teacher;
    
    let params = buildParams("student-performance", globalFilters.start, globalFilters.end, 
                              category, globalFilters.status, globalFilters.reward, teacher,
                              globalFilters.major, globalFilters.year);
    
    fetch("api/api.php?" + params.toString())
        .then(res => res.json())
        .then(data => {
            if (!data || data.length === 0) return;
            document.getElementById("studentRating").textContent = data.length;
            const labels = data.map(d => d.name);
            const values = data.map(d => Number(d.avg_rating) || 0);
            renderPieChart("studentRatingChart", labels, values, ["#4B0082","#FF6B00","#FFA726","#66BB6A","#EC407A"]);
        })
        .catch(err => console.error("Error fetching student performance:", err));
}

// 3. Major Distribution Chart
function loadMajorDistributionChart() {
    const globalFilters = getGlobalFilterValues();
    const specificFilters = {
        year: document.getElementById('majorYear')?.value || '',
        category: document.getElementById('majorCategory')?.value || ''
    };
    
    // Use specific filters if provided, otherwise fall back to global filters
    const year = specificFilters.year || globalFilters.year;
    const category = specificFilters.category || globalFilters.category;
    
    let params = buildParams("major-distribution", globalFilters.start, globalFilters.end, 
                              category, globalFilters.status, globalFilters.reward, globalFilters.teacher,
                              globalFilters.major, year);
    
    fetch("api/api.php?" + params.toString())
        .then(res => res.json())
        .then(data => {
            const labels = data.map(d => d.major_name || "No Major");
            const values = data.map(d => Number(d.total_students));
            renderPieChart("majorDistChart", labels, values, ["#66BB6A","#FF7043","#FFB74D","#26C6DA","#AB47BC"]);
        })
        .catch(err => console.error("Error fetching major-distribution:", err));
}

// 4. Average GPA Chart
function loadAvgGpaChart() {
    const globalFilters = getGlobalFilterValues();
    const specificFilters = {
        major: document.getElementById('gpaMajor')?.value || '',
        year: document.getElementById('gpaYear')?.value || '',
        category: document.getElementById('gpaCategory')?.value || ''
    };
    
    // Build params
    let params = new URLSearchParams({ endpoint: "avg-gpa" });
    
    // Add filters directly
    if (globalFilters.start) params.set('start', globalFilters.start);
    if (globalFilters.end) params.set('end', globalFilters.end);
    if (specificFilters.major) params.set('major', specificFilters.major);
    if (specificFilters.year) params.set('year', specificFilters.year);
    if (specificFilters.category) params.set('category', specificFilters.category);

    console.log("GPA params:", params.toString()); // Debug log

    fetch("api/api.php?" + params.toString())
        .then(res => res.json())
        .then(data => {
            if (!data || !data[0]) {
                console.log("No GPA data received");
                return;
            }
            const gpaVal = Number(data[0].avg_gpa) || 0;
            document.getElementById("avgGpa").textContent = 
                `Avg GPA: ${gpaVal.toFixed(2)}`;
            renderBarChart("avgGpaChart", ["GPA"], [gpaVal], 
                         "Average GPA", "#673AB7");
        })
        .catch(err => console.error("Error fetching avg-gpa:", err));
}

// 5. Top 5 Completed Chart
// function loadTop5CompletedChart() {
//     const globalFilters = getGlobalFilterValues();
//     const specificFilters = {
//         major: document.getElementById('top5Major')?.value || '',
//         year: document.getElementById('top5Year')?.value || '',
//         limit: document.getElementById('top5Limit')?.value || '5'
//     };
    
//     // Build params
//     let params = new URLSearchParams({ 
//         endpoint: "top5-completed",
//         limit: specificFilters.limit
//     });

//     // Add filters directly rather than using buildParams helper
//     if (globalFilters.start) params.set('start', globalFilters.start);
//     if (globalFilters.end) params.set('end', globalFilters.end);
//     if (specificFilters.major) params.set('major', specificFilters.major);
//     if (specificFilters.year) params.set('year', specificFilters.year);

//     console.log("Top 5 params:", params.toString()); // Debug log

//     fetch("api/api.php?" + params.toString())
//         .then(res => res.json())
//         .then(data => {
//             if (!data || data.length === 0) {
//                 console.log("No data received for top 5");
//                 return;
//             }
//             const labels = data.map(d => d.name);
//             const values = data.map(d => Number(d.completed_count));
//             renderBarChart("top5CompletedChart", labels, values, 
//                          `Top ${specificFilters.limit} Completed Jobs`, "#FF6B00");
//         })
//         .catch(err => console.error("Error fetching top5-completed:", err));
// }

// 6. Applications per Student Chart
function loadAppsPerStudentChart() {
    const globalFilters = getGlobalFilterValues();
    const specificFilters = {
        major: document.getElementById('appsPerStudentMajor')?.value || '',
        year: document.getElementById('appsPerStudentYear')?.value || '',
        limit: document.getElementById('appsPerStudentLimit')?.value || '5'
    };
    
    // Use specific filters if provided, otherwise fall back to global filters
    const major = specificFilters.major || globalFilters.major;
    const year = specificFilters.year || globalFilters.year;
    const limit = specificFilters.limit;
    
    let params = buildParams("applications-per-student", globalFilters.start, globalFilters.end,
                             globalFilters.category, globalFilters.status, globalFilters.reward,
                             globalFilters.teacher, major, year);
    
    // Add limit parameter
    params.set('limit', limit);
    
    fetch("api/api.php?" + params.toString())
        .then(res => res.json())
        .then(data => {
            if (!data || data.length === 0) return;
            
            // Limit the data based on the specified limit
            const limitedData = data.slice(0, Number(limit));
            
            const total = limitedData.reduce((acc, row) => acc + Number(row.total_apps), 0);
            document.getElementById("appsPerStudent").textContent = total + " total apps";
            
            renderBarChart("appsPerStudentChart",
                limitedData.map(d => d.name),
                limitedData.map(d => Number(d.total_apps)),
                `Applications per Student (Top ${limit})`,
                "#FF6B00"
            );
        })
        .catch(err => console.error("Error fetching apps per student:", err));
}

// ============== Helper Functions ==============
function getGlobalFilterValues() {
    return {
        start: document.getElementById('startDate')?.value || '',
        end: document.getElementById('endDate')?.value || '',
        category: document.getElementById('category')?.value || '',
        status: document.getElementById('status')?.value || '',
        reward: document.getElementById('reward')?.value || '',
        teacher: document.getElementById('teacher')?.value || '',
        major: document.getElementById('major')?.value || '',
        year: document.getElementById('year')?.value || ''
    };
}

// Enhanced buildParams function that supports additional parameters
function buildParams(endpoint, start, end, category, status, reward, teacher, major, year) {
    const p = new URLSearchParams({ endpoint });
    if (start) p.set('start', start);
    if (end) p.set('end', end);
    if (category) p.set('category', category);
    if (status) p.set('status', status);
    if (reward) p.set('reward', reward);
    if (teacher) p.set('teacher', teacher);
    
    // Add the new parameters
    if (major) p.set('major', major);
    if (year) p.set('year', year);
    
    return p;
}

// Chart renderer wrappers (store in chartInstances)
function renderBarChart(canvasId, labels, values, labelText, bgColor) {
    if (chartInstances[canvasId]) {
        chartInstances[canvasId].destroy();
    }
    chartInstances[canvasId] = new Chart(document.getElementById(canvasId), {
        type: "bar",
        data: {
            labels,
            datasets: [{
                label: labelText,
                data: values,
                backgroundColor: bgColor,
                barPercentage: 1,  // Adjust bar width (smaller = more spacing)
                categoryPercentage: 0.5  // Adjust space between categories
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                datalabels: {
                    display: true,
                    anchor: 'end',
                    align: 'top',
                    offset: -4,  // Adjust this offset to lift labels further above the bar
                    formatter: function(value, context) {
                        const jobTitle = context.chart.data.labels[context.dataIndex];
                        // Shorten label if needed to avoid overlap
                        const shortTitle = jobTitle.length > 7 ? jobTitle.slice(0, 11) + '...' : jobTitle;
                        return shortTitle + ": " + value;
                    },
                    font: {
                        weight: 'bold',
                        size: 9  // Adjust font size as needed
                    }
                }
            },
            scales: {
                x: {
                    ticks: {
                        callback: function(val, index) {
                            const maxLen = 100;
                            const label = this.getLabelForValue(val);
                            return (label.length > maxLen)
                                ? label.slice(0, maxLen) + '...'
                                : label;
                        }
                    }
                },
                y: {
                    beginAtZero: true
                }
            }
        },
        plugins: [ChartDataLabels]
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
                    display: showDataLabels,
                    anchor: 'center',
                    align: 'center',
                    offset: 2,  // conditionally display labels
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
                    color: '#000000',
                    font: { weight: 'bold', size: 15 },
                    padding: 9,
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

function renderLineChart(canvasId, labels, values, labelText, color) {
    if (chartInstances[canvasId]) {
        chartInstances[canvasId].destroy();
    }
    chartInstances[canvasId] = new Chart(document.getElementById(canvasId), {
        type: "line",
        data: {
            labels,
            datasets: [{
                label: labelText,
                data: values,
                borderColor: color,
                backgroundColor: color + "33", // Add transparency
                fill: true,
                tension: 0.1, // Slightly curved lines
                pointBackgroundColor: color,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: true,
                    text: labelText,
                    font: { size: 16 }
                },
                tooltip: {
                    mode: 'index',
                    intersect: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
}