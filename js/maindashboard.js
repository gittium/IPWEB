// maindash.js

// Dictionary to store chart instances
const chartInstances = {};

// Object to store last used filters for each chart
const chartFilters = {
    activeJobs: {},
    students: {},
    jobCategories: {},
    applications: {},
    jobsOverTime: {}
};

document.addEventListener("DOMContentLoaded", function () {

    const semesterSelect = document.getElementById('jobsOverTime_semester');
    const yearSelect = document.getElementById('jobsOverTime_year');
    const dateRangeInfo = document.getElementById('dateRangeInfo');
    
    if (semesterSelect && yearSelect && dateRangeInfo) {
        const updateDateRange = function() {
            const semester = semesterSelect.value;
            const year = yearSelect.value;
            
            if (semester && year) {
                const dateRange = getSemesterDateRange(semester, year);
                dateRangeInfo.innerHTML = `
                    <strong>Selected period:</strong> ${dateRange.label}<br>
                    <span class="text-secondary">Date range: ${formatDate(dateRange.start)} - ${formatDate(dateRange.end)}</span>
                `;
            } else {
                dateRangeInfo.innerHTML = '';
            }
        };
        
        semesterSelect.addEventListener('change', updateDateRange);
        yearSelect.addEventListener('change', updateDateRange);
    }

    Promise.all([
        loadCategoryOptions(), 
        loadStatusOptions(),
        loadRewardOptions(),
        loadTeacherOptions(),
        loadMajorOptions()
    ]).then(() => {
        // หลังจากโหลด options สำหรับ filters ทั้งหมดแล้ว
        initializeCharts();
        
        // โหลดข้อมูลทุก chart ด้วย filter เริ่มต้น
        loadAllCharts();
        
        // เก็บ default filters เพื่อใช้ตอน reset
        saveDefaultFilters();
    }).catch(err => {
        console.error("Error loading filter options:", err);
        // ถึงแม้จะมี error ก็ยังลองโหลด charts
        initializeCharts();
        loadAllCharts();
    });
});

function formatDate(dateStr) {
    if (!dateStr) return '';
    
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

// ฟังก์ชันใหม่สำหรับบันทึกค่า filters เริ่มต้น
function saveDefaultFilters() {
    // บันทึกค่า filters เริ่มต้นของแต่ละ chart
    for (let chartId in chartFilters) {
        chartFilters[chartId] = getChartFilters(chartId);
    }
}

// ฟังก์ชันเริ่มต้นสำหรับเตรียม charts ทั้งหมด
function initializeCharts() {
    // สร้าง charts เปล่าไว้ก่อน จะใส่ข้อมูลทีหลัง
    renderBarChart("activeJobsChart", [], [], "Active Jobs", "#FF6B00");
    renderBarChart("studentsChart", [], [], "Student Comparison", "#4B0082");
    renderPieChart("jobCategoriesChart", [], [], ["#FF6B00", "#4B0082", "#FFA726", "#66BB6A", "#29B6F6"], "Job Categories");
    renderBarChart("applicationsChart", [], [], "Applications", "#4B0082");
    renderMultiLineChart("jobsOverTimeChart", [], [], []);
}

// โหลดข้อมูลทุก chart
function loadAllCharts() {
    loadChartData('activeJobs', getChartFilters('activeJobs'));
    loadChartData('students', getChartFilters('students'));
    loadChartData('jobCategories', getChartFilters('jobCategories'));
    loadChartData('applications', getChartFilters('applications'));
    loadChartData('jobsOverTime', getChartFilters('jobsOverTime'));
}

// ฟังก์ชันสำหรับโหลด options สำหรับ filter ต่างๆ

function loadCategoryOptions() {
    return fetch("api/api.php?endpoint=categories-list")
        .then(res => res.json())
        .then(data => {
            // โหลดข้อมูลลงใน global filter
            const categorySelect = document.getElementById("category");
            categorySelect.innerHTML = '<option value="">All</option>';
            
            // โหลดข้อมูลลงใน chart filters ทุกอันที่ต้องการ category
            const chartSelects = [
                "activeJobs_category", 
                "applications_category", 
                "jobsOverTime_category"
            ];
            
            data.forEach(item => {
                const opt = document.createElement("option");
                opt.value = item.job_categories_id;
                opt.textContent = item.categories_name;
                
                // เพิ่ม option ลงใน global filter
                if (categorySelect) {
                    categorySelect.appendChild(opt.cloneNode(true));
                }
                
                // เพิ่ม option ลงใน chart filters
                chartSelects.forEach(selectId => {
                    const select = document.getElementById(selectId);
                    if (select) {
                        select.appendChild(opt.cloneNode(true));
                    }
                });
            });
        });
}

function loadStatusOptions() {
    return fetch("api/api.php?endpoint=status-list")
        .then(res => res.json())
        .then(data => {
            // โหลดข้อมูลลงใน global filter
            const statusSelect = document.getElementById("status");
            statusSelect.innerHTML = '<option value="">All</option>';
            
            // โหลดข้อมูลลงใน chart filters ทุกอันที่ต้องการ status
            const chartSelects = [
                "activeJobs_status", 
                "categories_status"
            ];
            
            data.forEach(item => {
                const opt = document.createElement("option");
                opt.value = item.job_status_id;
                opt.textContent = item.job_status_name;
                
                // เพิ่ม option ลงใน global filter
                if (statusSelect) {
                    statusSelect.appendChild(opt.cloneNode(true));
                }
                
                // เพิ่ม option ลงใน chart filters
                chartSelects.forEach(selectId => {
                    const select = document.getElementById(selectId);
                    if (select) {
                        select.appendChild(opt.cloneNode(true));
                    }
                });
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
                opt.textContent = item.reward_name;
                rewardSelect.appendChild(opt);
            });
        });
}

function loadTeacherOptions() {
    return fetch("api/api.php?endpoint=teacher-list")
        .then(res => res.json())
        .then(data => {
            const teacherSelects = document.querySelectorAll("[id$='_teacher']");
            teacherSelects.forEach(select => {
                select.innerHTML = '<option value="">All</option>';
                data.forEach(item => {
                    const opt = document.createElement("option");
                    opt.value = item.teachers_id;
                    opt.textContent = item.name;
                    select.appendChild(opt.cloneNode(true));
                });
            });
        });
}

function loadMajorOptions() {
    return fetch("api/api.php?endpoint=major-list")
        .then(res => res.json())
        .then(data => {
            const majorSelects = document.querySelectorAll("[id$='_major']");
            majorSelects.forEach(select => {
                select.innerHTML = '<option value="">All</option>';
                data.forEach(item => {
                    const opt = document.createElement("option");
                    opt.value = item.major_id;
                    opt.textContent = item.major_name;
                    select.appendChild(opt.cloneNode(true));
                });
            });
        });
}

// ฟังก์ชันสำหรับจัดการ filter แยกสำหรับแต่ละ chart

// ฟังก์ชันเมื่อกด Apply filter สำหรับ chart ใดๆ
function applyFilterToChart(chartId) {
    // ดึงค่า filters จาก UI
    const filters = getChartFilters(chartId);
    
    // แสดงผลสถานะ filter ที่ใช้งาน
    updateFilterStatus(chartId, filters);
    
    // ดำเนินการโหลดข้อมูลตามปกติ
    loadChartData(chartId, filters);
    
    // บันทึก filter ปัจจุบันเพื่อใช้ตอน reset
    chartFilters[chartId] = {...filters};
    
    // ถ้าเป็น jobsOverTime ให้แสดงข้อมูลเพิ่มเติมเกี่ยวกับช่วงเวลาที่เลือก
    if (chartId === 'jobsOverTime') {
        const semester = document.getElementById('jobsOverTime_semester').value;
        const year = document.getElementById('jobsOverTime_year').value;
        
        if (semester && year) {
            const dateRange = getSemesterDateRange(semester, year);
            const dateRangeInfo = document.getElementById('dateRangeInfo');
            
            if (dateRangeInfo) {
                dateRangeInfo.innerHTML = `
                    <strong>Selected period:</strong> ${dateRange.label}<br>
                    <span class="text-secondary">Date range: ${formatDate(dateRange.start)} - ${formatDate(dateRange.end)}</span>
                `;
            }
        }
    }
}

// ฟังก์ชันใหม่เพื่อแสดงสถานะ filter
function updateFilterStatus(chartId, filters) {
    const chartElement = document.getElementById(chartId + 'Chart');
    if (!chartElement) {
        console.error(`Chart element not found: ${chartId}Chart`);
        return;
    }
    
    const filterStatusDiv = document.createElement('div');
    filterStatusDiv.className = 'filter-status small text-muted mt-2';
    filterStatusDiv.innerHTML = '<strong>Active Filters:</strong> ';
    
    // สร้างรายการ filter ที่ใช้งาน
    const activeFilters = [];
    
    if (filters.start && filters.end) {
        activeFilters.push(`Date: ${formatDate(filters.start)} to ${formatDate(filters.end)}`);
    } else if (filters.start) {
        activeFilters.push(`Date from: ${formatDate(filters.start)}`);
    } else if (filters.end) {
        activeFilters.push(`Date until: ${formatDate(filters.end)}`);
    }
    
    if (filters.semester && filters.year) {
        const dateRange = getSemesterDateRange(filters.semester, filters.year);
        activeFilters.push(`${dateRange.label}`);
    }
    
    if (filters.category) {
        const categorySelect = document.getElementById(chartId + '_category');
        if (categorySelect) {
            const selectedOption = categorySelect.options[categorySelect.selectedIndex];
            activeFilters.push(`Category: ${selectedOption.text}`);
        } else {
            activeFilters.push(`Category ID: ${filters.category}`);
        }
    }
    
    if (filters.status) {
        const statusSelect = document.getElementById(chartId + '_status');
        if (statusSelect) {
            const selectedOption = statusSelect.options[statusSelect.selectedIndex];
            activeFilters.push(`Status: ${selectedOption.text}`);
        } else {
            activeFilters.push(`Status ID: ${filters.status}`);
        }
    }
    
    if (filters.viewType) {
        const viewSelect = document.getElementById(chartId + '_viewType');
        if (viewSelect) {
            const selectedOption = viewSelect.options[viewSelect.selectedIndex];
            activeFilters.push(`View: ${selectedOption.text}`);
        }
    }
    
    // แสดงรายการ filter หรือข้อความว่าไม่มี filter
    if (activeFilters.length > 0) {
        filterStatusDiv.innerHTML += activeFilters.join(', ');
    } else {
        filterStatusDiv.innerHTML += 'None (showing all data)';
    }
    
    // ลบสถานะเดิม (ถ้ามี) แล้วเพิ่มสถานะใหม่
    const chartContainer = chartElement.closest('.chart-container');
    if (chartContainer) {
        const oldStatus = chartContainer.querySelector('.filter-status');
        if (oldStatus) oldStatus.remove();
        
        chartContainer.appendChild(filterStatusDiv);
    }
}

// ฟังก์ชันเมื่อกด Reset filter สำหรับ chart ใดๆ
function resetFilterForChart(chartId) {
    // Reset inputs กลับไปเป็นค่าเริ่มต้น
    resetChartFilterInputs(chartId);
    
    // โหลดข้อมูลใหม่ด้วย filter เริ่มต้น
    const defaultFilters = {};
    loadChartData(chartId, defaultFilters);
}

// ฟังก์ชันสำหรับ reset ค่า inputs ของ filter กลับไปเป็นค่าเริ่มต้น
function resetChartFilterInputs(chartId) {
    const prefix = chartId + '_';
    
    // Reset selects
    document.querySelectorAll(`select[id^="${prefix}"]`).forEach(el => {
        el.value = '';
    });
    
    // Reset dates
    document.querySelectorAll(`input[type="date"][id^="${prefix}"]`).forEach(el => {
        el.value = '';
    });
    
    // Special case for year in jobsOverTime
    if (chartId === 'jobsOverTime' && document.getElementById('jobsOverTime_year')) {
        document.getElementById('jobsOverTime_year').value = '2025';
    }
}

// ฟังก์ชันสำหรับดึงค่า filter ของแต่ละ chart
// ฟังก์ชันสำหรับดึงค่า filter ของแต่ละ chart
function getChartFilters(chartId) {
    const filters = {};
    const prefix = chartId + '_';
    
    // ดึงค่า filter ทั่วไปที่อาจมีในทุก chart
    if (document.getElementById(prefix + 'category')) {
        filters.category = document.getElementById(prefix + 'category').value || '';
    }
    
    if (document.getElementById(prefix + 'status')) {
        filters.status = document.getElementById(prefix + 'status').value || '';
    }
    
    if (document.getElementById(prefix + 'startDate')) {
        filters.start = document.getElementById(prefix + 'startDate').value || '';
    }
    
    if (document.getElementById(prefix + 'endDate')) {
        filters.end = document.getElementById(prefix + 'endDate').value || '';
    }
    
    if (document.getElementById(prefix + 'limit')) {
        filters.limit = document.getElementById(prefix + 'limit').value || '';
    }
    
    // ดึงค่า filter เฉพาะของแต่ละ chart
    switch(chartId) {
        case 'students':
            if (document.getElementById('students_major')) {
                filters.major = document.getElementById('students_major').value || '';
            }
            if (document.getElementById('students_year')) {
                filters.year = document.getElementById('students_year').value || '';
            }
            break;
            
        case 'jobsOverTime':
            if (document.getElementById('jobsOverTime_semester')) {
                filters.semester = document.getElementById('jobsOverTime_semester').value || '';
            }
            if (document.getElementById('jobsOverTime_year')) {
                filters.year = document.getElementById('jobsOverTime_year').value || '';
            }
            if (document.getElementById('jobsOverTime_viewType')) {
                filters.viewType = document.getElementById('jobsOverTime_viewType').value || '';
            }
            
            // ถ้ามีการเลือก semester และ year ให้คำนวณ start_date และ end_date
            if (filters.semester && filters.year) {
                console.log("Converting semester/year to date range");
                const dateRange = getSemesterDateRange(filters.semester, filters.year);
                console.log("Date range calculated:", dateRange);
                
                // เขียนทับค่า start และ end (ถ้ามี)
                filters.start = dateRange.start;
                filters.end = dateRange.end;
                
                // ยังคงส่ง semester และ year ไปด้วย เพื่อให้ backend จัดการได้
            }
            break;
    }
    
    // แสดงผล filters ใน console เพื่อการตรวจสอบ
    console.log(`Filters for ${chartId}:`, filters);
    
    return filters;
}


function loadJobsOverTimeChart(filters) {
    // สร้าง params สำหรับ jobs-over-time และ jobs-taken-overtime
    const paramsOverTime = new URLSearchParams({ endpoint: "jobs-over-time" });
    const paramsTakenOvertime = new URLSearchParams({ endpoint: "jobs-taken-overtime" });

    if (filters.viewType === 'yearly') {
        paramsOverTime.set('groupBy', 'year');
        paramsTakenOvertime.set('groupBy', 'year');
      } else if (filters.viewType === 'semester') {
        paramsOverTime.set('groupBy', 'semester');
        paramsTakenOvertime.set('groupBy', 'semester');
      } else {
        // monthly เป็นค่า default
        paramsOverTime.set('groupBy', 'month');
        paramsTakenOvertime.set('groupBy', 'month');
      }

    // เพิ่ม filters ทั้งหมดลงในทั้งสอง params
    Object.entries(filters).forEach(([key, value]) => {
        if (value) {
            paramsOverTime.set(key, value);
            paramsTakenOvertime.set(key, value);
        }
    });
    
    // Debug info
    console.log("Fetching jobs-over-time with params:", paramsOverTime.toString());
    console.log("Fetching jobs-taken-overtime with params:", paramsTakenOvertime.toString());
    
    // แสดงสถานะการโหลดข้อมูล (optional)
    const debugElement = document.getElementById('jobsOverTime_debug');
    if (debugElement) {
        debugElement.textContent = `Loading data with filters: ${paramsOverTime.toString()}`;
        debugElement.parentNode.style.display = 'block';
    }
    
    Promise.all([
        fetch("api/api.php?" + paramsOverTime.toString())
            .then(res => {
                if (!res.ok) {
                    return res.text().then(text => {
                        throw new Error(`API responded with status ${res.status}: ${text}`);
                    });
                }
                return res.json();
            }),
        fetch("api/api.php?" + paramsTakenOvertime.toString())
            .then(res => {
                if (!res.ok) {
                    return res.text().then(text => {
                        throw new Error(`API responded with status ${res.status}: ${text}`);
                    });
                }
                return res.json();
            })
    ]).then(([dataOverTime, dataTakenOvertime]) => {
        console.log("Data received for jobs-over-time:", dataOverTime);
        console.log("Data received for jobs-taken-overtime:", dataTakenOvertime);
        
        // อัปเดต debug info
        if (debugElement) {
            debugElement.textContent = `Data received. Processing...`;
        }
        
        // ตรวจสอบว่ามีข้อมูลหรือไม่
        if (!dataOverTime || !Array.isArray(dataOverTime) || dataOverTime.length === 0) {
            console.warn("No data returned from jobs-over-time endpoint");
            
            const chartContainer = document.getElementById("jobsOverTimeChart").closest('.chart-container');
            if (chartContainer) {
                chartContainer.innerHTML = `
                    <div class="alert alert-info mt-3">
                        No data available for the selected filters. Try adjusting your filter criteria.
                    </div>
                    <canvas id="jobsOverTimeChart"></canvas>
                `;
            }
            return;
        }
        
        // ตรวจสอบว่าข้อมูลมี error หรือไม่
        if (dataOverTime.error) {
            throw new Error(`API Error: ${dataOverTime.error}`);
        }
        
        if (dataTakenOvertime.error) {
            throw new Error(`API Error: ${dataTakenOvertime.error}`);
        }
        
        // สร้าง Data Map จากข้อมูลที่ได้
        const dataMap = {};
        
        // กรอกข้อมูล Jobs Posted (จาก created_at)
        dataOverTime.forEach(item => {
            dataMap[item.month] = { total: Number(item.total_posts || 0), closed: 0 };
        });
        
        // รวมข้อมูล Closed Jobs (จาก job_end)
        dataTakenOvertime.forEach(item => {
            if (dataMap[item.month]) {
                dataMap[item.month].closed = Number(item.total_jobs_closed || 0);
            } else {
                dataMap[item.month] = { total: 0, closed: Number(item.total_jobs_closed || 0) };
            }
        });
        
        // เรียงลำดับเดือน
        const months = Object.keys(dataMap).sort();
        const totalPosts = months.map(month => dataMap[month].total);
        const jobsClosed = months.map(month => dataMap[month].closed);
        
        // แสดง Debug ข้อมูล
        console.log("Processed data:", { months, totalPosts, jobsClosed });
        
        // รีเซ็ต chart container กลับมาเป็น canvas เพื่อวาดกราฟใหม่
        const chartContainer = document.getElementById("jobsOverTimeChart").closest('.chart-container');
        if (chartContainer && chartContainer.querySelector('.alert')) {
            chartContainer.innerHTML = '<canvas id="jobsOverTimeChart"></canvas>';
        }
        
        // วาด multi-line chart ด้วยข้อมูลทั้งสอง datasets
        renderMultiLineChart("jobsOverTimeChart", months, totalPosts, jobsClosed);
        
        // อัปเดตสถานะ filter
        updateFilterStatus('jobsOverTime', filters);
        
        // อัปเดต debug info
        if (debugElement) {
            debugElement.textContent = `Chart rendered successfully with ${months.length} data points.`;
        }
    }).catch(err => {
        console.error("Error fetching job comparison data:", err);
        
        const chartContainer = document.getElementById("jobsOverTimeChart")?.closest('.chart-container');
        if (chartContainer) {
            chartContainer.innerHTML = `
                <div class="alert alert-danger mt-3">
                    <strong>Error loading data:</strong> ${err.message}
                    <br><small>Please check console for more details.</small>
                </div>
                <div class="mt-2 text-center">
                    <button class="btn btn-primary" onclick="resetFilterForChart('jobsOverTime')">Reset Filter</button>
                </div>
            `;
        }
        
        // อัปเดต debug info
        if (debugElement) {
            debugElement.textContent = `Error: ${err.message}`;
        }
    });
}

// ฟังก์ชันสำหรับโหลดข้อมูลเฉพาะ chart
function loadChartData(chartId, filters) {
    switch(chartId) {
        case 'activeJobs':
            // สร้าง URLSearchParams สำหรับ API request
            const params = new URLSearchParams({ endpoint: "job-market" });
            
            // เพิ่ม filter ที่ไม่ว่าง
            Object.entries(filters).forEach(([key, value]) => {
                if (value) params.set(key, value);
            });
            
            // เรียก API
            fetch("api/api.php?" + params.toString())
                .then(res => res.json())
                .then(data => {
                    const totalJobs = data.reduce((acc, row) => acc + Number(row.total_jobs), 0);
                    document.getElementById("activeJobs").textContent = totalJobs;

                    const labels = data.map(d => d.category_name);
                    const values = data.map(d => d.total_jobs);
                    renderBarChart("activeJobsChart", labels, values, "Active Jobs", "#FF6B00");
                })
                .catch(err => console.error("Error fetching job-market:", err));
            break;
            
        case 'students':
            // ส่ง filter เพิ่มเติมสำหรับ active-students
            const studentsParams = new URLSearchParams({ endpoint: "active-students" });
            
            Object.entries(filters).forEach(([key, value]) => {
                if (value) studentsParams.set(key, value);
            });
            
            fetch("api/api.php?" + studentsParams.toString())
                .then(res => res.json())
                .then(data => {
                    if (data && data[0]) {
                        // Parse the three metrics
                        const total = Number(data[0].total_students);
                        const accepted = Number(data[0].accepted_students);
                        const notAccepted = Number(data[0].not_accepted_students);

                        // อัปเดตค่ารวมนิสิต
                        document.getElementById("totalStudents").textContent = total;

                        // Render bar chart แสดงข้อมูลเปรียบเทียบ
                        renderBarChart(
                            "studentsChart", 
                            ["Total Students", "Job Accepted", "No Job"], 
                            [total, accepted, notAccepted], 
                            "Student Comparison", 
                            "#4B0082"
                        );
                    }
                })
                .catch(err => console.error("Error fetching active-students:", err));
            break;
            
        case 'jobCategories':
            // สร้าง params สำหรับ job-market (ใช้สำหรับ categories)
            const categoriesParams = new URLSearchParams({ endpoint: "job-market" });
            
            Object.entries(filters).forEach(([key, value]) => {
                if (value) categoriesParams.set(key, value);
            });
            
            fetch("api/api.php?" + categoriesParams.toString())
                .then(res => res.json())
                .then(data => {
                    const labels = data.map(d => d.category_name);
                    const values = data.map(d => Number(d.total_jobs));
                    
                    // กรองข้อมูลที่มีค่าเป็น 0 ออก
                    const filteredData = labels.reduce((acc, label, index) => {
                        if (values[index] > 0) {
                            acc.labels.push(label);
                            acc.values.push(values[index]);
                        }
                        return acc;
                    }, { labels: [], values: [] });
                    
                    // ถ้ามีการกำหนด limit ให้ตัดข้อมูลตาม limit
                    if (filters.limit && parseInt(filters.limit) > 0) {
                        // เรียงลำดับข้อมูลก่อน
                        const combinedData = filteredData.labels.map((label, index) => {
                            return { label, value: filteredData.values[index] };
                        });
                        
                        combinedData.sort((a, b) => b.value - a.value);
                        
                        // ตัดข้อมูลตาม limit
                        const limitedData = combinedData.slice(0, parseInt(filters.limit));
                        
                        filteredData.labels = limitedData.map(item => item.label);
                        filteredData.values = limitedData.map(item => item.value);
                    }
                    
                    // Render pie chart
                    renderPieChart("jobCategoriesChart", filteredData.labels, filteredData.values, [
                        "#FF6B00", "#4B0082", "#FFA726", "#66BB6A", "#29B6F6"
                    ], "Job Categories Distribution");
                    
                    // Render table
                    renderJobCategoriesTable(labels, values);
                })
                .catch(err => console.error("Error fetching categories:", err));
            break;
            
        case 'applications':
            // สร้าง params สำหรับ applications
            const applicationsParams = new URLSearchParams({ endpoint: "applications" });
            
            Object.entries(filters).forEach(([key, value]) => {
                if (value) applicationsParams.set(key, value);
            });
            
            fetch("api/api.php?" + applicationsParams.toString())
                .then(res => res.json())
                .then(data => {
                    let labels = data.map(d => d.title);
                    let values = data.map(d => Number(d.total_applications));
                    
                    // ถ้ามีการกำหนด limit ให้ตัดข้อมูลตาม limit
                    if (filters.limit && parseInt(filters.limit) > 0) {
                        // เรียงลำดับข้อมูลก่อน
                        const combinedData = labels.map((label, index) => {
                            return { label, value: values[index] };
                        });
                        
                        combinedData.sort((a, b) => b.value - a.value);
                        
                        // ตัดข้อมูลตาม limit
                        const limitedData = combinedData.slice(0, parseInt(filters.limit));
                        
                        labels = limitedData.map(item => item.label);
                        values = limitedData.map(item => item.value);
                    }
                    
                    renderBarChart("applicationsChart", labels, values, "Applications", "#4B0082");
                })
                .catch(err => console.error("Error fetching applications:", err));
            break;
            
        case 'jobsOverTime':
            // ใช้ฟังก์ชันแยกเพื่อให้ง่ายต่อการดูแล
            loadJobsOverTimeChart(filters);
            break;
    }
}

// Global Filters
function applyGlobalFilters() {
    const globalFilters = getGlobalFilterValues();
    
    // Apply global filters to all charts
    for (let chartId in chartFilters) {
        // ใช้ global filters แทนที่ filters ปัจจุบันของ chart
        const filters = {...globalFilters};
        
        // โหลดข้อมูลใหม่ด้วย global filters
        loadChartData(chartId, filters);
    }
}

function resetGlobalFilters() {
    // Reset global filter inputs
    document.getElementById("startDate").value = '';
    document.getElementById("endDate").value = '';
    document.getElementById("category").value = '';
    document.getElementById("status").value = '';
    document.getElementById("reward").value = '';
    
    // Reset filters for all charts
    for (let chartId in chartFilters) {
        resetFilterForChart(chartId);
    }
}

// ดึงค่า Global Filters
function getGlobalFilterValues() {
    return {
        start: document.getElementById("startDate")?.value || "",
        end: document.getElementById("endDate")?.value || "",
        category: document.getElementById("category")?.value || "",
        status: document.getElementById("status")?.value || "",
        reward: document.getElementById("reward")?.value || ""
    };
}

// Function to get date range for a semester
function getSemesterDateRange(semester, year) {
    if (!semester || !year) {
        return { start: '', end: '', label: 'All Periods' };
    }
    
    // แปลงปีให้เป็นตัวเลข
    year = parseInt(year);
    
    // กำหนดวันที่สำหรับแต่ละ semester
    switch(semester) {
        case 'first':
            return {
                start: `${year}-06-25`,
                end: `${year}-10-25`,
                label: `First Semester ${year} (Jun 25 - Oct 25)`
            };
        case 'second':
            return {
                start: `${year}-11-25`,
                end: `${year+1}-03-25`,
                label: `Second Semester ${year}-${year+1} (Nov 25 - Mar 25)`
            };
        case 'summer':
            return {
                start: `${year}-03-30`,
                end: `${year}-06-01`,
                label: `Summer ${year} (Mar 30 - Jun 1)`
            };
        default:
            return { start: '', end: '', label: 'Invalid Semester' };
    }
}

// Function to render a table with all job categories and counts
function renderJobCategoriesTable(labels, values) {
    let tableHTML = `<table class="table table-striped">
        <thead>
            <tr>
                <th>Category</th>
                <th>Job Count</th>
            </tr>
        </thead>
        <tbody>`;
    for (let i = 0; i < labels.length; i++) {
        tableHTML += `<tr>
            <td>${labels[i]}</td>
            <td>${values[i]}</td>
        </tr>`;
    }
    tableHTML += `</tbody></table>`;
    document.getElementById("jobCategoriesTableContainer").innerHTML = tableHTML;
}

// Renders a Bar Chart with Data Labels Always Visible
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
                        return value;
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

// Renders a Pie Chart with Data Labels Always Visible
function renderPieChart(canvasId, labels, values, colorsArray, chartTitle) {
    // Calculate total sum of values
    const total = values.reduce((acc, cur) => acc + cur, 0);

    if (chartInstances[canvasId]) {
        chartInstances[canvasId].destroy();
    }
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
                    display: true,
                    text: chartTitle || "",
                    font: {
                        size: 16
                    }
                },
                datalabels: {
                    display: true,
                    anchor: 'center',
                    align: 'end',
                    offset: 2,
                    formatter: function(value, context) {
                        const label = context.chart.data.labels[context.dataIndex];
                        // Calculate percentage and format to one decimal place
                        const percent = ((value / total) * 100).toFixed(1);
                        return label + "\n" +"     " + percent + '%';
                    },
                    color: '#fff',
                    font: {
                        weight: 'bold',
                        size: 12
                    },
                    padding: 6,
                    clip: true 
                }
            }
        },
        plugins: [ChartDataLabels]
    });
}

// Renders a Multi-Line Chart
function renderMultiLineChart(canvasId, labels, valuesTotal, valuesClosed) {
    if (chartInstances[canvasId]) {
        chartInstances[canvasId].destroy();
    }
    
    // เพิ่มส่วนแสดงสถานะ filter ที่ใช้งาน
    const filterStatus = document.createElement('div');
    filterStatus.className = 'filter-status small text-muted mb-2';
    
    const semester = document.getElementById("jobsOverTime_semester")?.value || '';
    const year = document.getElementById("jobsOverTime_year")?.value || '';
    
    if (semester && year) {
        const dateRange = getSemesterDateRange(semester, year);
        filterStatus.innerHTML = `<strong>Active Filter:</strong> ${dateRange.label}`;
    } else {
        // ตรวจสอบค่าจาก document แทนการใช้ตัวแปร filters
        const startDate = document.getElementById("jobsOverTime_startDate")?.value || '';
        const endDate = document.getElementById("jobsOverTime_endDate")?.value || '';
        
        if (startDate && endDate) {
            filterStatus.innerHTML = `<strong>Date Range:</strong> ${startDate} to ${endDate}`;
        } else {
            filterStatus.innerHTML = '<strong>Showing:</strong> All data';
        }
    }
    
    // แทรกส่วนแสดงสถานะเข้าไปก่อน canvas
    const chartContainer = document.getElementById(canvasId)?.closest('.chart-container');
    if (chartContainer) {
        const existingStatus = chartContainer.querySelector('.filter-status');
        if (existingStatus) {
            existingStatus.remove();
        }
        chartContainer.insertBefore(filterStatus, chartContainer.firstChild);
    }
    
    // สร้างกราฟแบบเดิม
    try {
        const canvas = document.getElementById(canvasId);
        if (!canvas) {
            console.error(`Canvas element not found: ${canvasId}`);
            return;
        }
        
        chartInstances[canvasId] = new Chart(canvas, {
            type: "line",
            data: {
                labels,
                datasets: [
                    {
                        label: "Total Jobs Posted",
                        data: valuesTotal,
                        borderColor: "#FF6B00",
                        backgroundColor: "rgba(255, 107, 0, 0.1)",
                        fill: false,
                        tension: 0.1
                    },
                    {
                        label: "Jobs Completed (Closed)",
                        data: valuesClosed,
                        borderColor: "#4B0082",
                        backgroundColor: "rgba(75, 0, 130, 0.1)",
                        fill: false,
                        tension: 0.1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    datalabels: {
                        display: true,
                        align: 'top',
                        formatter: Math.round,
                        font: {
                            weight: 'bold',
                            size: 10
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    },
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                scales: {
                    y: { 
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    },
                    x: {
                        ticks: {
                            maxRotation: 45,
                            minRotation: 45
                        }
                    }
                }
            },
            plugins: [ChartDataLabels]
        });
        console.log("Chart rendered successfully:", canvasId);
    } catch (error) {
        console.error("Error rendering chart:", error);
    }
}