const chartInstances = {};
const profAnalyticsChartFilters = {
    activeProfs: {},
    jobPosts: {},
    topPaidJobPosts: {},
    profRating: {}
};

// รอให้โหลด global filter controls (filter.html) และตัวเลือกฟิลเตอร์อื่นๆ ก่อน
document.addEventListener("DOMContentLoaded", function () {
    Promise.all([
        loadFilterSection(),  // โหลด global filters จาก filter.html
        loadCategoryOptions(),
        loadStatusOptions(),
        loadRewardOptions(),
        loadTeacherOptions()
    ]).then(() => {
        loadProfessorAnalytics();
    }).catch(err => {
        console.error("Error loading filter options:", err);
        loadProfessorAnalytics();
    });
});

async function loadFilterSection() {
    try {
        const response = await fetch('filter.html');
        if (!response.ok) throw new Error('Failed to load filter section');
        const filterHTML = await response.text();
        document.getElementById('filter-placeholder').innerHTML = filterHTML;
        // reinitialize collapse elements
        document.querySelectorAll('.collapse').forEach(el => new bootstrap.Collapse(el, { toggle: false }));
    } catch (error) {
        console.error('Error loading filter section:', error);
    }
}

function loadCategoryOptions() {
    return fetch("api/api.php?endpoint=categories-list")
        .then(res => res.json())
        .then(data => {
            // Global filter
            const categorySelect = document.getElementById("category");
            if (categorySelect) {
                categorySelect.innerHTML = '<option value="">All</option>';
                data.forEach(item => {
                    const opt = document.createElement("option");
                    opt.value = item.job_categories_id;
                    opt.textContent = item.categories_name;
                    categorySelect.appendChild(opt);
                });
            }
            // For individual filters (elements ending with _category)
            document.querySelectorAll("select[id$='_category']").forEach(select => {
                select.innerHTML = '<option value="">All</option>';
                data.forEach(item => {
                    const opt = document.createElement("option");
                    opt.value = item.job_categories_id;
                    opt.textContent = item.categories_name;
                    select.appendChild(opt);
                });
            });
        });
}

function loadStatusOptions() {
    return fetch("api/api.php?endpoint=status-list")
        .then(res => res.json())
        .then(data => {
            const statusSelect = document.getElementById("status");
            if (statusSelect) {
                statusSelect.innerHTML = '<option value="">All</option>';
                data.forEach(item => {
                    const opt = document.createElement("option");
                    opt.value = item.job_status_id;
                    opt.textContent = item.job_status_name;
                    statusSelect.appendChild(opt);
                });
            }
            document.querySelectorAll("select[id$='_status']").forEach(select => {
                select.innerHTML = '<option value="">All</option>';
                data.forEach(item => {
                    const opt = document.createElement("option");
                    opt.value = item.job_status_id;
                    opt.textContent = item.job_status_name;
                    select.appendChild(opt);
                });
            });
        });
}

function loadRewardOptions() {
    return fetch("api/api.php?endpoint=reward-list")
        .then(res => res.json())
        .then(data => {
            const rewardSelect = document.getElementById("reward");
            if (rewardSelect) {
                rewardSelect.innerHTML = '<option value="">All</option>';
                data.forEach(item => {
                    const opt = document.createElement("option");
                    opt.value = item.reward_type_id;
                    opt.textContent = item.reward_name;
                    rewardSelect.appendChild(opt);
                });
            }
            document.querySelectorAll("select[id$='_reward']").forEach(select => {
                select.innerHTML = '<option value="">All</option>';
                data.forEach(item => {
                    const opt = document.createElement("option");
                    opt.value = item.reward_type_id;
                    opt.textContent = item.reward_name;
                    select.appendChild(opt);
                });
            });
        });
}

function loadTeacherOptions() {
    return fetch("api/api.php?endpoint=teacher-list")
        .then(res => res.json())
        .then(data => {
            const teacherSelect = document.getElementById("teacher");
            if (teacherSelect) {
                teacherSelect.innerHTML = '<option value="">All</option>';
                data.forEach(item => {
                    const opt = document.createElement("option");
                    opt.value = item.teachers_id;
                    opt.textContent = item.name;
                    teacherSelect.appendChild(opt);
                });
            }
            document.querySelectorAll("select[id$='_teacher']").forEach(select => {
                select.innerHTML = '<option value="">All</option>';
                data.forEach(item => {
                    const opt = document.createElement("option");
                    opt.value = item.teachers_id;
                    opt.textContent = item.name;
                    select.appendChild(opt);
                });
            });
        });
}

function getFilterValues() {
    const container = document.getElementById('filter-placeholder');
    const filters = {
        start: container.querySelector('#startDate')?.value || '',
        end: container.querySelector('#endDate')?.value || '',
        category: container.querySelector('#category')?.value || '',
        status: container.querySelector('#status')?.value || '',
        reward: container.querySelector('#reward')?.value || '',
        teacher: container.querySelector('#teacher')?.value || ''
    };
    console.log("Current filter values:", filters);  // ตรวจสอบค่าฟิลเตอร์
    return filters;
}

function buildParams(endpoint, start, end, category, status, reward, teacher) {
    const p = new URLSearchParams({ endpoint });
    if (start) p.set('start', start);
    if (end) p.set('end', end);
    if (category) p.set('category', category);
    if (status) p.set('status', status);
    if (reward) p.set('reward', reward);
    if (teacher) p.set('teacher', teacher);

    console.log("Built parameters:", p.toString());  // ตรวจสอบค่าพารามิเตอร์ที่ส่งไปยัง API
    return p;
}


// ดึงค่าฟิลเตอร์สำหรับแต่ละกราฟใน profanaly
function getProfFilters(chartId) {
    const filters = {};
    const prefix = chartId + '_';
    const start = document.getElementById(prefix + 'startDate');
    if (start) filters.start = start.value || '';
    const end = document.getElementById(prefix + 'endDate');
    if (end) filters.end = end.value || '';
    const category = document.getElementById(prefix + 'category');
    if (category) filters.category = category.value || '';
    const status = document.getElementById(prefix + 'status');
    if (status) filters.status = status.value || '';
    const reward = document.getElementById(prefix + 'reward');
    if (reward) filters.reward = reward.value || '';
    const teacher = document.getElementById(prefix + 'teacher');
    if (teacher) filters.teacher = teacher.value || '';
    return filters;
}

function applyFilterToProfAnalytics(chartId) {
    const filters = getProfFilters(chartId);
    profAnalyticsChartFilters[chartId] = { ...filters };
    switch (chartId) {
        case 'activeProfs':
            loadActiveProfessorsChart(filters);
            break;
        case 'jobPosts':
            loadJobPostsChart(filters);
            break;
        case 'topPaidJobPosts':
            loadTopPaidJobPostsChart(filters);
            break;
        case 'profRating':
            loadProfessorRatingChart(filters);
            break;
        default:
            console.warn("Unknown chart ID: " + chartId);
    }
}

function resetFilterForProfAnalytics(chartId) {
    const prefix = chartId + '_';
    document.querySelectorAll(`select[id^="${prefix}"]`).forEach(el => el.value = '');
    document.querySelectorAll(`input[type="date"][id^="${prefix}"]`).forEach(el => el.value = '');
    applyFilterToProfAnalytics(chartId);
}

// โหลดข้อมูลกราฟ
function loadActiveProfessorsChart(filters = {}) {
    const globalFilters = getFilterValues();
    const merged = { ...globalFilters, ...filters };
    let paramsActive = buildParams("active-professors", merged.start, merged.end, merged.category, merged.status, merged.reward, merged.teacher);
    fetch("api/api.php?" + paramsActive.toString())
        .then(res => res.json())
        .then(data => {
            if (data && data[0]) {
                const total = Number(data[0].total_professors);
                const active = Number(data[0].active_professors);
                const inactive = total - active;
                document.getElementById("activeProfessors").textContent = active;
                renderBarChart("activeProfsChart", ["Active", "Inactive"], [active, inactive], "Professors by Job Posting", "#FF6B00");
            }
        })
        .catch(err => console.error("Error fetching active professors:", err));
}

function loadJobPostsChart(filters = {}) {
    const globalFilters = getFilterValues();
    const merged = { ...globalFilters, ...filters };
    let paramsPosts = buildParams("job-posts", merged.start, merged.end, merged.category, merged.status, merged.reward, merged.teacher);

    fetch("api/api.php?" + paramsPosts.toString())
        .then(res => res.json())
        .then(data => {
            console.log(data); // ตรวจสอบข้อมูลที่ได้รับจาก API

            // ตรวจสอบว่าข้อมูลมีอยู่และไม่เป็น null หรือ undefined
            if (data && Array.isArray(data)) {
                const total = data.reduce((acc, row) => acc + Number(row.total_jobs), 0);
                document.getElementById("totalJobPosts").textContent = total;

                // ตรวจสอบว่าค่าของ total_jobs และ categories_name ไม่เป็น NaN หรือ undefined
                const labels = data.map(d => d.categories_name);
                const values = data.map(d => Number(d.total_jobs));

                // ตรวจสอบให้แน่ใจว่า values ไม่มี NaN ก่อนที่จะแสดงกราฟ
                const numericValues = values.map(val => {
                    const num = Number(val);
                    return isNaN(num) ? 0 : num;
                });

                renderBarChart("jobPostsChart", labels, numericValues, "Total Job Posts", "#4B0082");
            } else {
                console.error("Invalid data format:", data);
            }
        })
        .catch(err => console.error("Error fetching job posts:", err));
}



function loadTopPaidJobPostsChart(filters = {}) {
    const globalFilters = getFilterValues();
    const merged = { ...globalFilters, ...filters };
    let paramsTopPaid = buildParams("top-paid-jobs", merged.start, merged.end, merged.category, merged.status, merged.reward, merged.teacher);
    fetch("api/api.php?" + paramsTopPaid.toString())
        .then(res => res.json())
        .then(data => {
            if (data && data.length > 0) {
                const titles = data.map(d => d.title);
                const salaries = data.map(d => Number(d.salary));
                renderBarChart("topPaidJobPostsChart", titles, salaries, "Top Paid Job Posts", "#FF5733", true);
            } else {
                renderBarChart("topPaidJobPostsChart", [], [], "Top Paid Job Posts", "#FF5733", true);
            }
        })
        .catch(err => console.error("Error fetching top paid job posts:", err));
}

function loadProfessorRatingChart(filters = {}) {
    const globalFilters = getFilterValues();
    const merged = { ...globalFilters, ...filters };
    let paramsRating = buildParams("professor-rating", merged.start, merged.end, merged.category, merged.status, merged.reward, merged.teacher);
    fetch("api/api.php?" + paramsRating.toString())
        .then(res => res.json())
        .then(data => {
            if (data && data.length > 0) {
                const best = data[0];
                document.getElementById("profRating").textContent = `Best: ${best.name} (${best.total_closings})`;
                renderBarChart("profRatingChart", data.map(d => d.name), data.map(d => Number(d.total_closings) || 0), "Professor Closings", "#66BB6A");
            }
        })
        .catch(err => console.error("Error fetching professor rating:", err));
}

function renderBarChart(canvasId, labels, values, labelText, bgColor, showValueOnBar = false) {
    const numericValues = values.map(val => {
        const num = Number(val);
        return isNaN(num) ? 0 : num;  // ถ้าค่าไม่ใช่ตัวเลข จะใช้ค่าเป็น 0
    });

    if (chartInstances[canvasId]) {
        chartInstances[canvasId].destroy();
    }

    chartInstances[canvasId] = new Chart(document.getElementById(canvasId), {
        type: "bar",
        data: {
            labels,
            datasets: [{
                label: labelText,
                data: numericValues,
                backgroundColor: bgColor,
                barPercentage: 1,
                categoryPercentage: 0.5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                datalabels: {
                    display: function (context) {
                        return showValueOnBar && context.dataset.data[context.dataIndex] > 0;
                    },
                    anchor: 'end',
                    align: 'top',
                    offset: -4,
                    formatter: function (value, context) {
                        if (showValueOnBar) {
                            return `฿${value.toLocaleString()}`;
                        }
                        const jobTitle = context.chart.data.labels[context.dataIndex];
                        const shortTitle = jobTitle.length > 7 ? jobTitle.slice(0, 11) + '...' : jobTitle;
                        return shortTitle + ": " + value;
                    },
                    font: {
                        weight: 'bold',
                        size: 12
                    }
                }
            },
            scales: {
                x: {
                    ticks: {
                        autoSkip: false
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



function renderPieChart(canvasId, labels, values, colorsArray) {
    if (chartInstances[canvasId]) {
        chartInstances[canvasId].destroy();
    }
    chartInstances[canvasId] = new Chart(document.getElementById(canvasId), {
        type: "pie",
        data: { labels, datasets: [{ data: values, backgroundColor: colorsArray }] },
        options: { responsive: true, maintainAspectRatio: false }
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
                fill: false
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });
}

function loadProfessorAnalytics() {
    const globalFilters = getFilterValues();
    loadActiveProfessorsChart(profAnalyticsChartFilters.activeProfs || {});
    loadJobPostsChart(profAnalyticsChartFilters.jobPosts || {});
    loadTopPaidJobPostsChart(profAnalyticsChartFilters.topPaidJobPosts || {});
    loadProfessorRatingChart(profAnalyticsChartFilters.profRating || {});
}

function applyGlobalFilters() {
    const globalFilters = getFilterValues();
    loadActiveProfessorsChart(profAnalyticsChartFilters.activeProfs ? { ...globalFilters, ...profAnalyticsChartFilters.activeProfs } : globalFilters);
    loadJobPostsChart(profAnalyticsChartFilters.jobPosts ? { ...globalFilters, ...profAnalyticsChartFilters.jobPosts } : globalFilters);
    loadTopPaidJobPostsChart(profAnalyticsChartFilters.topPaidJobPosts ? { ...globalFilters, ...profAnalyticsChartFilters.topPaidJobPosts } : globalFilters);
    loadProfessorRatingChart(profAnalyticsChartFilters.profRating ? { ...globalFilters, ...profAnalyticsChartFilters.profRating } : globalFilters);
}

function resetGlobalFilters() {
    document.getElementById("startDate").value = '';
    document.getElementById("endDate").value = '';
    document.getElementById("category").value = '';
    document.getElementById("status").value = '';
    document.getElementById("reward").value = '';
    document.getElementById("teacher").value = '';
    applyGlobalFilters();
}

// เปลี่ยนฟังก์ชัน resetGlobalFilters() เป็นเวอร์ชันนี้
function resetGlobalFilters() {
    const container = document.getElementById('filter-placeholder');
    if (!container) return;
    const fields = ['startDate', 'endDate', 'category', 'status', 'reward', 'teacher'];
    fields.forEach(id => {
        const el = container.querySelector('#' + id);
        if (el) el.value = '';
    });
    applyGlobalFilters();
}