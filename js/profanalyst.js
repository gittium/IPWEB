// profanaly.js

const chartInstances = {};

document.addEventListener("DOMContentLoaded", function () {
    Promise.all([
        loadCategoryOptions(),
        loadStatusOptions(),
        loadRewardOptions(),
        loadTeacherOptions()
    ]).then(() => {
        // Now that we've loaded all filter options, we can do the main logic
        loadProfessorAnalytics();
    }).catch(err => {
        console.error("Error loading filter options:", err);
        // even if they fail, still attempt to load main logic
        loadProfessorAnalytics();
    });
});

function loadCategoryOptions() {
    return fetch("api/api.php?endpoint=categories-list")
        .then(res => res.json())
        .then(data => {
            const categorySelect = document.getElementById("category");
            categorySelect.innerHTML = '<option value="">All</option>';
            data.forEach(item => {
                const opt = document.createElement("option");
                opt.value = item.job_categories_id;
                opt.textContent = item.categories_name;
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
                opt.textContent = item.reward_name;
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
                opt.value = item.teachers_id;
                opt.textContent = item.name;
                teacherSelect.appendChild(opt);
            });
        });
}


function applyFilters() {
    loadProfessorAnalytics();
}

// Function to load top-paid job posts data
function loadTopPaidJobPosts() {
    const { start, end, category, status, reward, teacher } = getFilterValues();

    // Build the parameters to be passed into the API
    let paramsTopPaidJobs = buildParams("top-paid-jobs", start, end, category, status, reward, teacher);
    fetch("api/api.php?" + paramsTopPaidJobs.toString())  // Fetch data from the API
        .then(res => res.json())  // Parse JSON response
        .then(data => {
            if (data && data.length > 0) {
                const titles = data.map(d => d.title);  // Extract job titles
                const salaries = data.map(d => Number(d.salary));  // Extract salary values

                // Render bar chart for top paid job posts
                renderBarChart("topPaidJobPostsChart", titles, salaries, "Top Paid Job Posts", "#FF5733", true);
            } else {
                console.log("No data for top paid job posts.");
            }
        })
        .catch(err => console.error("Error fetching top paid job posts:", err));  // Log any errors
}

// Main function to load professor analytics
function loadProfessorAnalytics() {
    const { start, end, category, status, reward, teacher } = getFilterValues();

    // Active Professors
    let paramsActive = buildParams("active-professors", start, end, category, status, reward, teacher);
    fetch("api/api.php?" + paramsActive.toString())
        .then(res => res.json())
        .then(data => {
            if (data && data[0]) {
                const total = Number(data[0].total_professors);
                const active = Number(data[0].active_professors);
                const inactive = total - active;
                document.getElementById("activeProfessors").textContent = active;
                renderBarChart("activeProfsChart", ["Active", "Inactive"], [active, inactive], "Professors by Job Posting", "#FF6B00", false);
            }
        })
        .catch(err => console.error("Error fetching active professors:", err));

    // Total Job Posts
    let paramsPosts = buildParams("job-posts", start, end, category, status, reward, teacher);
    fetch("api/api.php?" + paramsPosts.toString())
        .then(res => res.json())
        .then(data => {
            const total = data.reduce((acc, row) => acc + Number(row.total_jobs), 0);
            document.getElementById("totalJobPosts").textContent = total;
            renderBarChart("jobPostsChart", data.map(d => d.categories_name), data.map(d => Number(d.total_jobs)), "Total Job Posts", "#4B0082", false);
        })
        .catch(err => console.error("Error fetching job posts:", err));

    // Top Paid Job Posts
    loadTopPaidJobPosts();  // Call function to load and render the top paid job posts chart

    // Professor Rating
    let paramsRating = buildParams("professor-rating", start, end, category, status, reward, teacher);
    fetch("api/api.php?" + paramsRating.toString())
        .then(res => res.json())
        .then(data => {
            if (data && data.length > 0) {
                const best = data[0];
                document.getElementById("profRating").textContent = `Best: ${best.name} (${best.total_closings})`;
                renderBarChart("profRatingChart", data.map(d => d.name), data.map(d => Number(d.total_closings) || 0), "Professor Closings", "#66BB6A", false);
            }
        })
        .catch(err => console.error("Error fetching professor rating:", err));
}


// Helpers
function getFilterValues() {
    return {
        start: document.getElementById('startDate')?.value || '',
        end: document.getElementById('endDate')?.value || '',
        category: document.getElementById('category')?.value || '',
        status: document.getElementById('status')?.value || '',
        reward: document.getElementById('reward')?.value || '',
        teacher: document.getElementById('teacher')?.value || ''
    };
}

function buildParams(endpoint, start, end, category, status, reward, teacher) {
    const p = new URLSearchParams({ endpoint });
    if (start) p.set('start', start);
    if (end) p.set('end', end);
    if (category) p.set('category', category);
    if (status) p.set('status', status);
    if (reward) p.set('reward', reward);
    if (teacher) p.set('teacher', teacher);
    return p;
}
// Chart wrappers
function renderBarChart(canvasId, labels, values, labelText, bgColor, showValueOnBar = false) {
    // Convert all string values to numbers; if conversion fails, use 0.
    const numericValues = values.map(val => {
        const num = Number(val);
        return isNaN(num) ? 0 : num;
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
                data: numericValues, // Use numeric values here
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
                    // Only display label if the `showValueOnBar` is true
                    display: function (context) {
                        return showValueOnBar && context.dataset.data[context.dataIndex] > 0;
                    },
                    anchor: 'end',
                    align: 'top',
                    offset: -4,  // Adjust this offset to lift labels further above the bar
                    formatter: function (value, context) {
                        if (showValueOnBar) {
                            return `฿${value.toLocaleString()}`;  // Display value in Baht (e.g., ฿100,000)
                        }
                        const jobTitle = context.chart.data.labels[context.dataIndex];
                        const shortTitle = jobTitle.length > 7 ? jobTitle.slice(0, 11) + '...' : jobTitle;
                        return shortTitle + ": " + value;
                    },
                    font: {
                        weight: 'bold',
                        size: 12  // Set the font size to 12 (default)
                    }
                }
            },
            scales: {
                x: {
                    ticks: {
                        callback: function (val, index) {
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

function loadTopPaidJobPosts() {
    const { start, end, category, status, reward, teacher } = getFilterValues();

    // Build the parameters to be passed into the API
    let paramsTopPaidJobs = buildParams("top-paid-jobs", start, end, category, status, reward, teacher);
    fetch("api/api.php?" + paramsTopPaidJobs.toString())  // Fetch data from the API
        .then(res => res.json())  // Parse JSON response
        .then(data => {
            if (data && data.length > 0) {
                const titles = data.map(d => d.title);  // Extract job titles
                const salaries = data.map(d => Number(d.salary));  // Extract salary values

                // Render bar chart for top paid job posts
                renderBarChart("topPaidJobPostsChart", titles, salaries, "Top Paid Job Posts", "#FF5733", true);
            } else {
                console.log("No data for top paid job posts.");
            }
        })
        .catch(err => console.error("Error fetching top paid job posts:", err));  // Log any errors
}
