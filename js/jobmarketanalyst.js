// jobmarket.js

const chartInstances = {};

document.addEventListener('DOMContentLoaded', function() {
    Promise.all([
        loadCategoryOptions(), 
        loadStatusOptions(),
        loadRewardOptions(),
        loadTeacherOptions(),
        loadSubcategoryOptions()
    ]).then(() => {
        // ทำการ initialize ทุก chart โดยใช้ global filters
        initializeAllCharts();

        // ทำการเติมข้อมูลให้กับ filter ของแต่ละ chart โดยใช้ option ที่โหลดมาแล้ว
        initializeAllChartFilters();
        
        // เพิ่ม event listeners สำหรับปุ่ม Apply Filters ของแต่ละ chart
        setupFilterEventListeners();
        
    }).catch(err => {
        console.error("Error loading filter options:", err);
        // even if they fail, still attempt to load main logic
        initializeAllCharts();
    });

    // Add event listener for category changes to update subcategories
    const categorySelect = document.getElementById("category");
    if (categorySelect) {
        categorySelect.addEventListener('change', function() {
            loadSubcategoryOptions(this.value);
        });
    }
    
    // Add event listeners for subcategory-specific category selectors
    const subcatCategorySelect = document.getElementById("subcategoriesCategory");
    if (subcatCategorySelect) {
        subcatCategorySelect.addEventListener('change', function() {
            // No need to update subcategory dropdown here since we're filtering main categories
        });
    }
});

// เติม options สำหรับ filters ทั้งหมดที่มีค่าเหมือนกัน
function initializeAllChartFilters() {
    // Category Options
    const categorySelectors = [
        "activeJobCategory", "payRateCategory", "completionRateCategory", 
        "appStatusCategory", "categoriesStatus", "statusCategory", 
        "rewardCategory", "subcategoriesCategory", "trendCategory", "closedCategory"
    ];
    
    categorySelectors.forEach(selector => {
        const selectElement = document.getElementById(selector);
        if (selectElement) {
            copyOptionsFromSelect("category", selectElement);
        }
    });
    
    // Status Options
    const statusSelectors = [
        "categoriesStatus", "rewardStatus", "subcategoriesStatus", "trendStatus"
    ];
    
    statusSelectors.forEach(selector => {
        const selectElement = document.getElementById(selector);
        if (selectElement) {
            copyOptionsFromSelect("status", selectElement);
        }
    });
    
    // Reward Type Options
    const rewardSelectors = [
        "appStatusReward", "categoriesReward", "statusReward", "closedReward"
    ];
    
    rewardSelectors.forEach(selector => {
        const selectElement = document.getElementById(selector);
        if (selectElement) {
            copyOptionsFromSelect("reward", selectElement);
        }
    });
    
    // Teacher Options
    const teacherSelectors = [
        "payRateTeacher", "completionRateTeacher", "appStatusTeacher"
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
    
    // Active Jobs Filters
    document.getElementById("applyActiveJobsFilters")?.addEventListener("click", function() {
        loadActiveJobsChart();
    });
    
    // Pay Rate Filters
    document.getElementById("applyPayRateFilters")?.addEventListener("click", function() {
        loadPayRateChart();
    });
    
    // Completion Rate Filters
    document.getElementById("applyCompletionRateFilters")?.addEventListener("click", function() {
        loadCompletionRateChart();
    });
    
    // Application Status Filters
    document.getElementById("applyAppStatusFilters")?.addEventListener("click", function() {
        loadApplicationStatusChart();
    });
    
    // Categories Filters
    document.getElementById("applyCategoriesFilters")?.addEventListener("click", function() {
        loadJobCategoriesChart();
    });
    
    // Status Filters
    document.getElementById("applyStatusFilters")?.addEventListener("click", function() {
        loadJobStatusChart();
    });
    
    // Reward Filters
    document.getElementById("applyRewardFilters")?.addEventListener("click", function() {
        loadRewardTypeChart();
    });
    
    // Subcategories Filters
    document.getElementById("applySubcategoriesFilters")?.addEventListener("click", function() {
        loadJobSubcatChart();
    });
    
    // Trend Filters
    document.getElementById("applyTrendFilters")?.addEventListener("click", function() {
        loadJobsTrendChart();
    });
    
    // Closed Filters
    document.getElementById("applyClosedFilters")?.addEventListener("click", function() {
        loadJobsClosedChart();
    });
}

// รีเซ็ต filters ทั้งหมด
function resetFilters() {
    // Global Filters
    document.getElementById('startDate').value = '';
    document.getElementById('endDate').value = '';
    document.getElementById('category').value = '';
    document.getElementById('subcategory').value = '';
    document.getElementById('status').value = '';
    document.getElementById('reward').value = '';
    document.getElementById('teacher').value = '';
    
    // ฟังก์ชันช่วยรีเซ็ต filter ของแต่ละชาร์ต
    function resetChartFilters(prefix, fields) {
        fields.forEach(field => {
            const element = document.getElementById(prefix + field);
            if (element) element.value = '';
        });
    }
    
    // Reset Active Jobs Filters
    resetChartFilters('activeJob', ['Status', 'Category']);
    
    // Reset Pay Rate Filters
    resetChartFilters('payRate', ['Category', 'Teacher']);
    
    // Reset Completion Rate Filters
    resetChartFilters('completionRate', ['Category', 'Teacher']);
    
    // Reset Application Status Filters
    resetChartFilters('appStatus', ['Category', 'Teacher', 'Reward']);
    
    // Reset Categories Filters
    resetChartFilters('categories', ['Status', 'Reward']);
    
    // Reset Status Filters
    resetChartFilters('status', ['Category', 'Reward']);
    
    // Reset Reward Filters
    resetChartFilters('reward', ['Category', 'Status']);
    
    // Reset Subcategories Filters
    resetChartFilters('subcategories', ['Category', 'Status']);
    
    // Reset Trend & Closed Filters
    document.getElementById('trendViewType').value = 'monthly';
    document.getElementById('closedViewType').value = 'monthly';
    resetChartFilters('trend', ['Category', 'Status']);
    resetChartFilters('closed', ['Category', 'Reward']);
    
    // Apply reset to reload all charts
    applyGlobalFilters();
}

// Load filter options
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

// Apply Global Filters to all charts
function applyGlobalFilters() {
    initializeAllCharts();
}

// Initialize all charts with global filters
function initializeAllCharts() {
    loadActiveJobsChart();
    loadPayRateChart();
    loadCompletionRateChart();
    loadApplicationStatusChart();
    loadJobCategoriesChart();
    loadJobStatusChart();
    loadRewardTypeChart();
    loadJobSubcatChart();
    loadJobsTrendChart();
    loadJobsClosedChart();
}

// Load individual charts with their specific filters

// 1. Active Jobs Chart
function loadActiveJobsChart() {
    const globalFilters = getGlobalFilterValues();
    const specificFilters = {
        status: document.getElementById('activeJobStatus')?.value || '',
        category: document.getElementById('activeJobCategory')?.value || ''
    };
    
    // Use specific filters if provided, otherwise fall back to global filters
    const status = specificFilters.status || globalFilters.status;
    const category = specificFilters.category || globalFilters.category;
    
    let params = buildParams("job-market", globalFilters.start, globalFilters.end, 
                              category, globalFilters.subcategory, status, 
                              globalFilters.reward, globalFilters.teacher);
    
    fetch("api/api.php?" + params.toString())
        .then(res => res.json())
        .then(data => {
            if (!data || data.length === 0) return;
            const totalJobs = data.reduce((sum, row) => sum + Number(row.total_jobs), 0);
            if (document.getElementById("activeJobs")) {
                document.getElementById("activeJobs").textContent = totalJobs;
            }
            renderBarChart("activeJobsChart",
                data.map(d => d.category_name),
                data.map(d => d.total_jobs),
                "Active Jobs by Category",
                "#FF6B00"
            );
        })
        .catch(err => console.error("Error fetching active jobs:", err));
}

// 2. Pay Rate Chart
function loadPayRateChart() {
    const globalFilters = getGlobalFilterValues();
    const specificFilters = {
        category: document.getElementById('payRateCategory')?.value || '',
        teacher: document.getElementById('payRateTeacher')?.value || ''
    };
    
    // Use specific filters if provided, otherwise fall back to global filters
    const category = specificFilters.category || globalFilters.category;
    const teacher = specificFilters.teacher || globalFilters.teacher;
    
    let params = buildParams("pay-rate", globalFilters.start, globalFilters.end, 
                              category, globalFilters.subcategory, globalFilters.status, 
                              globalFilters.reward, teacher);
    
    fetch("api/api.php?" + params.toString())
        .then(res => res.json())
        .then(data => {
            if (data && data.length > 0) {
                const payRateStats = data[0];
                if (document.getElementById("averagePayRate")) {
                    document.getElementById("averagePayRate").innerHTML = `
                        <div class="text-lg">
                            <div>Money: ${payRateStats.money_percentage}%</div>
                            <div>Experience: ${payRateStats.experience_percentage}%</div>
                        </div>
                    `;
                }
                renderPieChart("payRateChart",
                    ["Money", "Experience"],
                    [payRateStats.money_percentage, payRateStats.experience_percentage],
                    ["#36A2EB", "#FF6384"],
                    "Reward Distribution"
                );
            }
        })
        .catch(err => console.error("Error fetching pay-rate:", err));
}

// 3. Completion Rate Chart
function loadCompletionRateChart() {
    const globalFilters = getGlobalFilterValues();
    const specificFilters = {
        category: document.getElementById('completionRateCategory')?.value || '',
        teacher: document.getElementById('completionRateTeacher')?.value || ''
    };
    
    // Use specific filters if provided, otherwise fall back to global filters
    const category = specificFilters.category || globalFilters.category;
    const teacher = specificFilters.teacher || globalFilters.teacher;
    
    let params = buildParams("completion-rate", globalFilters.start, globalFilters.end, 
                              category, globalFilters.subcategory, globalFilters.status, 
                              globalFilters.reward, teacher);
    
    fetch("api/api.php?" + params.toString())
        .then(res => res.json())
        .then(data => {
            if (data && data.length > 0) {
                const stats = data[0];
                if (document.getElementById("completionRate")) {
                    document.getElementById("completionRate").innerHTML = `
                        <div class="text-lg">${stats.completion_percentage}%</div>
                        <div class="text-sm">(${stats.accepted_applications}/${stats.total_applications} apps)</div>
                    `;
                }
                renderPieChart("successRateChart",
                    ["Accepted", "Other"],
                    [
                        stats.completion_percentage,
                        (100 - stats.completion_percentage)
                    ],
                    ["#4BC0C0", "#FF6384"],
                    "Job Application Success Rate"
                );
            }
        })
        .catch(err => console.error("Error fetching completion-rate:", err));
}

// 4. Application Status Chart
function loadApplicationStatusChart() {
    const globalFilters = getGlobalFilterValues();
    const specificFilters = {
        category: document.getElementById('appStatusCategory')?.value || '',
        teacher: document.getElementById('appStatusTeacher')?.value || '',
        reward: document.getElementById('appStatusReward')?.value || ''
    };
    
    // Use specific filters if provided, otherwise fall back to global filters
    const category = specificFilters.category || globalFilters.category;
    const teacher = specificFilters.teacher || globalFilters.teacher;
    const reward = specificFilters.reward || globalFilters.reward;
    
    let params = buildParams("application-status-distribution", globalFilters.start, globalFilters.end, 
                              category, globalFilters.subcategory, globalFilters.status, 
                              reward, teacher);
    
    fetch("api/api.php?" + params.toString())
        .then(res => res.json())
        .then(data => {
            if (data && data.length > 0) {
                const stats = data[0];
                renderPieChart("applicationStatusChart",
                    ["Accepted", "Rejected", "Pending"],
                    [stats.accepted_applications, stats.rejected_applications, stats.pending_applications],
                    ["#36A2EB", "#FF6384", "#FFCE56"], // Colors for Accepted, Rejected, Pending
                    "Application Status Distribution"
                );
            }
        })
        .catch(err => console.error("Error fetching application status distribution:", err));
}

// 5. Job Categories Chart
function loadJobCategoriesChart() {
    const globalFilters = getGlobalFilterValues();
    const specificFilters = {
        status: document.getElementById('categoriesStatus')?.value || '',
        reward: document.getElementById('categoriesReward')?.value || ''
    };
    
    // Use specific filters if provided, otherwise fall back to global filters
    const status = specificFilters.status || globalFilters.status;
    const reward = specificFilters.reward || globalFilters.reward;
    
    let params = buildParams("job-categories-stats", globalFilters.start, globalFilters.end, 
                              globalFilters.category, globalFilters.subcategory, status, 
                              reward, globalFilters.teacher);
    
    fetch("api/api.php?" + params.toString())
        .then(res => res.json())
        .then(data => {
            renderPieChart("jobCategoriesChart",
                data.map(d => `${d.job_category_name} (${d.percentage}%)`),
                data.map(d => d.total_jobs),
                [
                    "#FF6384", "#36A2EB", "#FFCE56", "#4BC0C0", "#9966FF",
                    "#FF9F40", "#4BC0C0", "#FF6384", "#36A2EB", "#FFCE56"
                ],
                "Job Categories Distribution"
            );
        })
        .catch(err => console.error("Error fetching categories stats:", err));
}

// 6. Job Status Chart
function loadJobStatusChart() {
    const globalFilters = getGlobalFilterValues();
    const specificFilters = {
        category: document.getElementById('statusCategory')?.value || '',
        reward: document.getElementById('statusReward')?.value || ''
    };
    
    // Use specific filters if provided, otherwise fall back to global filters
    const category = specificFilters.category || globalFilters.category;
    const reward = specificFilters.reward || globalFilters.reward;
    
    let params = buildParams("job-status-stats", globalFilters.start, globalFilters.end, 
                              category, globalFilters.subcategory, globalFilters.status, 
                              reward, globalFilters.teacher);
    
    fetch("api/api.php?" + params.toString())
        .then(res => res.json())
        .then(data => {
            renderPieChart("jobStatusChart",
                data.map(d => `${d.job_status_name} (${d.percentage}%)`),
                data.map(d => d.total_jobs),
                ["#36A2EB", "#FF6384", "#FFCE56", "#4BC0C0"],
                "Job Status Distribution"
            );
        })
        .catch(err => console.error("Error fetching status stats:", err));
}

// 7. Reward Type Chart
function loadRewardTypeChart() {
    const globalFilters = getGlobalFilterValues();
    const specificFilters = {
        category: document.getElementById('rewardCategory')?.value || '',
        status: document.getElementById('rewardStatus')?.value || ''
    };
    
    // Use specific filters if provided, otherwise fall back to global filters
    const category = specificFilters.category || globalFilters.category;
    const status = specificFilters.status || globalFilters.status;
    
    let params = buildParams("reward-type-stats", globalFilters.start, globalFilters.end, 
                              category, globalFilters.subcategory, status, 
                              globalFilters.reward, globalFilters.teacher);
    
    fetch("api/api.php?" + params.toString())
        .then(res => res.json())
        .then(data => {
            renderPieChart("rewardTypeChart",
                data.map(d => `${d.reward_type_name} (${d.percentage}%)`),
                data.map(d => d.total_jobs),
                ["#4BC0C0", "#FF6384", "#FFCE56"],
                "Reward Type Distribution"
            );
        })
        .catch(err => console.error("Error fetching reward type:", err));
}

// 8. Job Subcategories Chart
function loadJobSubcatChart() {
    const globalFilters = getGlobalFilterValues();
    const specificFilters = {
        category: document.getElementById('subcategoriesCategory')?.value || '',
        status: document.getElementById('subcategoriesStatus')?.value || ''
    };
    
    // Use specific filters if provided, otherwise fall back to global filters
    const category = specificFilters.category || globalFilters.category;
    const status = specificFilters.status || globalFilters.status;
    
    let params = buildParams("job-subcategories-stats", globalFilters.start, globalFilters.end, 
                              category, globalFilters.subcategory, status, 
                              globalFilters.reward, globalFilters.teacher);
    
    fetch("api/api.php?" + params.toString())
        .then(res => res.json())
        .then(data => {
            renderPieChart("jobSubcatChart",
                data.map(d => `${d.subcategories_name} (${d.total_jobs})`),
                data.map(d => Number(d.total_jobs)),
                [
                    "#FFD700", "#C0C0C0", "#CD7F32", "#FF6B00", "#4B0082", "#7E57C2",
                    "#4CAF50", "#2196F3", "#F44336", "#9C27B0", "#00BCD4", "#FF9800"
                ],
                "Job Subcategories Distribution"
            );
        })
        .catch(err => console.error("Error fetching subcategories:", err));
}

// 9. Jobs Trend Chart
function loadJobsTrendChart() {
    const globalFilters = getGlobalFilterValues();
    const specificFilters = {
        viewType: document.getElementById('trendViewType')?.value || 'monthly',
        category: document.getElementById('trendCategory')?.value || '',
        status: document.getElementById('trendStatus')?.value || ''
    };
    
    // Use specific filters if provided, otherwise fall back to global filters
    const viewType = specificFilters.viewType;
    const category = specificFilters.category || globalFilters.category;
    const status = specificFilters.status || globalFilters.status;
    
    const paramsTrend = new URLSearchParams({
        endpoint: 'jobs-over-time',
        viewType: viewType
    });
    
    if (globalFilters.start) paramsTrend.set('start', globalFilters.start);
    if (globalFilters.end) paramsTrend.set('end', globalFilters.end);
    if (category) paramsTrend.set('category', category);
    if (globalFilters.subcategory) paramsTrend.set('subcategory', globalFilters.subcategory);
    if (status) paramsTrend.set('status', status);
    
    fetch("api/api.php?" + paramsTrend.toString())
        .then(res => res.json())
        .then(data => {
            const labels = viewType === 'monthly' ? 
                data.map(d => d.month) : 
                data.map(d => d.year_label);
            const values = data.map(d => d.total_posts);
            
            renderLineChart("jobsTrendChart", 
                labels, 
                values, 
                "Jobs Posted Over Time", 
                "#FF6B00"
            );
        })
        .catch(err => console.error("Error fetching jobs trend:", err));
}

// 10. Jobs Closed Chart
function loadJobsClosedChart() {
    const globalFilters = getGlobalFilterValues();
    const specificFilters = {
        viewType: document.getElementById('closedViewType')?.value || 'monthly',
        category: document.getElementById('closedCategory')?.value || '',
        reward: document.getElementById('closedReward')?.value || ''
    };
    
    // Use specific filters if provided, otherwise fall back to global filters
    const viewType = specificFilters.viewType;
    const category = specificFilters.category || globalFilters.category;
    const reward = specificFilters.reward || globalFilters.reward;
    
    const paramsClosed = new URLSearchParams({
        endpoint: 'jobs-taken-overtime',
        viewType: viewType
    });
    
    if (globalFilters.start) paramsClosed.set('start', globalFilters.start);
    if (globalFilters.end) paramsClosed.set('end', globalFilters.end);
    if (category) paramsClosed.set('category', category);
    if (globalFilters.subcategory) paramsClosed.set('subcategory', globalFilters.subcategory);
    if (reward) paramsClosed.set('reward', reward);
    
    fetch("api/api.php?" + paramsClosed.toString())
        .then(res => res.json())
        .then(data => {
            const labels = viewType === 'monthly' ? 
                data.map(d => d.month) : 
                data.map(d => d.year_label);
            const values = data.map(d => d.total_jobs_closed);
            
            renderLineChart("jobsClosedChart", 
                labels, 
                values, 
                "Jobs Closed Over Time", 
                "#4BC0C0"
            );
        })
        .catch(err => console.error("Error fetching jobs closed:", err));
}

// ============== Helper Functions ==============
function getGlobalFilterValues() {
    return {
        start: document.getElementById('startDate')?.value || '',
        end: document.getElementById('endDate')?.value || '',
        category: document.getElementById('category')?.value || '',
        subcategory: document.getElementById('subcategory')?.value || '',
        status: document.getElementById('status')?.value || '',
        reward: document.getElementById('reward')?.value || '',
        teacher: document.getElementById('teacher')?.value || ''
    };
}

function buildParams(endpoint, start, end, category, subcategory, status, reward, teacher, viewType) {
    const p = new URLSearchParams({ endpoint });
    if (start) p.set('start', start);
    if (end) p.set('end', end);
    if (category) p.set('category', category);
    if (subcategory) p.set('subcategory', subcategory);
    if (status) p.set('status', status);
    if (reward) p.set('reward', reward);
    if (teacher) p.set('teacher', teacher);
    if (viewType) p.set('viewType', viewType);
    return p;
}

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
                backgroundColor: bgColor 
            }] 
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                },
                title: {
                    display: true,
                    text: labelText,
                    font: { size: 16 }
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
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
                    align: 'end',
                    offset: 2,  // conditionally display labels
                    formatter: function(value, context) {
                        let label = context.chart.data.labels[context.dataIndex] || "";
                        // Optionally, break the label into multiple lines if it's long:
                        if (label.length > 15) {
                            label = label.match(/.{1,15}/g).join("\n");
                        }
                        const total = context.chart.data.datasets[0].data.reduce((acc, cur) => acc + Number(cur), 0);
                        const percent = ((value / total) * 100).toFixed(1) + '%';
                        return label + "\n" + "        " + percent;
                    },
                    color: '#000000',
                    font: { weight: 'bold', size: 12 },
                    padding: 12,
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