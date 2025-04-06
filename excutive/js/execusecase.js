// execusecase.js

const chartInstances = {};

document.addEventListener("DOMContentLoaded", function () {
  loadExecUseCases();
});

function applyFilters() {
  loadExecUseCases();
}

function loadExecUseCases() {
  const { start, end, category, status, reward, teacher } = getFilterValues();

  // (A) Supply vs Demand
  let paramsSD = buildParams("supply-demand-stats", start, end, category, status, reward, teacher);
  fetch("api/api.php?" + paramsSD.toString())
    .then(res => res.json())
    .then(data => {
      // data = [{skill_id, skill_name, demand, supply}, ...]
      const labels = data.map(d => d.skill_name);
      const demandVals = data.map(d => d.demand);
      const supplyVals = data.map(d => d.supply);

      // e.g. stacked bar
      renderStackedBarChart("skillSupplyDemandChart", labels, demandVals, supplyVals);
    })
    .catch(err => console.error("Error fetch supply-demand:", err));

  // (B) job-duration-stats
  let paramsJD = buildParams("job-duration-stats", start, end, category, status, reward, teacher);
  fetch("api/api.php?" + paramsJD.toString())
    .then(res => res.json())
    .then(data => {
      // data = [ {post_jobs_id, title, duration_days}, ... ]
      // e.g. sort by desc
      const labels = data.map(d => d.title);
      const values = data.map(d => Number(d.duration_days));
      renderBarChart("jobDurationChart", labels, values, "Duration (Days)", "#FF6B00");
    })
    .catch(err => console.error("Error fetch job-duration:", err));

  // (C) GPA vs Rating
  let paramsGR = buildParams("gpa-rating-correlation", start, end, category, status, reward, teacher);
  fetch("api/api.php?" + paramsGR.toString())
    .then(res => res.json())
    .then(data => {
      // data = [ {students_id, name, avg_gpa, avg_rating}, ... ]
      const scatterData = data.map(r => {
        return {
          x: Number(r.avg_gpa) || 0,
          y: Number(r.avg_rating) || 0,
          label: r.name
        }
      });
      renderScatterChart("gpaRatingChart", scatterData, "GPA vs. Rating", "#4B0082");
    })
    .catch(err => console.error("Error fetch gpa-rating-correlation:", err));

  // (D) turnover-stats
  let paramsTO = buildParams("turnover-stats", start, end, category, status, reward, teacher);
  fetch("api/api.php?" + paramsTO.toString())
    .then(res => res.json())
    .then(data => {
      // data = [ { accepted_count: 10, rejected_count: 3, pending_count: 2 } ]
      const row = data[0] || { accepted_count:0, rejected_count:0, pending_count:0 };
      renderPieChart("turnoverChart",
        ["Accepted", "Rejected", "Pending"],
        [row.accepted_count, row.rejected_count, row.pending_count],
        ["#66BB6A", "#FF7043", "#FFCE56"]
      );
    })
    .catch(err => console.error("Error fetch turnover-stats:", err));

  // (E) monthly-expenditure
  let paramsME = buildParams("monthly-expenditure", start, end, category, status, reward, teacher);
  fetch("api/api.php?" + paramsME.toString())
    .then(res => res.json())
    .then(data => {
      // data = [ {month:'2025-03', total_payout: 30000} ... ]
      const labels = data.map(d => d.month);
      const values = data.map(d => Number(d.total_payout));
      renderLineChart("monthlyExChart", labels, values, "Monthly Expenditure", "#EC407A");
    })
    .catch(err => console.error("Error fetch monthly-expenditure:", err));

  // (F) top-teachers-by-success
  let paramsTT = buildParams("top-teachers-by-success", start, end, category, status, reward, teacher);
  fetch("api/api.php?" + paramsTT.toString())
    .then(res => res.json())
    .then(data => {
      // data = [ { name:'CSIT0131', total_posts:10, success_count:8 }, ...]
      const labels = data.map(d => d.name);
      const successVals = data.map(d => Number(d.success_count));
      renderBarChart("topTeachersSuccessChart", labels, successVals, "Success Count", "#29B6F6");
    })
    .catch(err => console.error("Error fetch top-teachers-by-success:", err));

}

// ============== HELPER ==============
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
  let p = new URLSearchParams({ endpoint });
  if (start) p.set('start', start);
  if (end) p.set('end', end);
  if (category) p.set('category', category);
  if (status) p.set('status', status);
  if (reward) p.set('reward', reward);
  if (teacher) p.set('teacher', teacher);
  return p;
}



// ============== Chart Renders ==============
function renderBarChart(canvasId, labels, values, labelText, bgColor) {
  if (chartInstances[canvasId]) chartInstances[canvasId].destroy();
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
    options: { responsive: true, maintainAspectRatio: false }
  });
}

function renderPieChart(canvasId, labels, values, colorsArray) {
  if (chartInstances[canvasId]) chartInstances[canvasId].destroy();
  chartInstances[canvasId] = new Chart(document.getElementById(canvasId), {
    type: "pie",
    data: {
      labels,
      datasets: [{
        data: values,
        backgroundColor: colorsArray
      }]
    },
    options: { responsive: true, maintainAspectRatio: false }
  });
}

function renderLineChart(canvasId, labels, values, labelText, color) {
  if (chartInstances[canvasId]) chartInstances[canvasId].destroy();
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

// ตัวอย่าง renderScatterChart
function renderScatterChart(canvasId, scatterData, labelText, color) {
  if (chartInstances[canvasId]) chartInstances[canvasId].destroy();
  chartInstances[canvasId] = new Chart(document.getElementById(canvasId), {
    type: 'scatter',
    data: {
      datasets: [{
        label: labelText,
        data: scatterData,  // [{x:..., y:..., label: "..."}]
        backgroundColor: color
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        x: { title: { display: true, text: 'GPA' } },
        y: { title: { display: true, text: 'Rating' } }
      },
      plugins: {
        tooltip: {
          callbacks: {
            label: function(context) {
              const labelName = context.raw.label || 'NoName';
              return labelName + ` (GPA:${context.raw.x}, Rating:${context.raw.y})`;
            }
          }
        }
      }
    }
  });
}

// ตัวอย่าง renderStackedBarChart
function renderStackedBarChart(canvasId, labels, demandVals, supplyVals) {
  if (chartInstances[canvasId]) chartInstances[canvasId].destroy();
  chartInstances[canvasId] = new Chart(document.getElementById(canvasId), {
    type: 'bar',
    data: {
      labels,
      datasets: [
        {
          label: "Demand",
          data: demandVals,
          backgroundColor: "#FF6B00"
        },
        {
          label: "Supply",
          data: supplyVals,
          backgroundColor: "#4B0082"
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        x: { stacked: true },
        y: { stacked: true }
      }
    }
  });
}
