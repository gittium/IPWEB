// skillanaly.js

// We'll track chart references here, too:
const skillChartInstances = {};

document.addEventListener("DOMContentLoaded", function () {
    console.log("Loading Skills Analysis Data...");

    // Most Demanded Skills
    fetch("api/api.php?endpoint=most-demanded-skills")
        .then(response => response.json())
        .then(data => {
            if (!data || data.length === 0) {
                console.error("No demanded skills data found.");
                return;
            }
            const labels = data.map(item => item.skill_name);
            const values = data.map(item => item.demand_score);
            createChart("demandedSkillsChart", "bar", labels, values, "Demand Score", "#FF6B00");
        })
        .catch(error => console.error("Error fetching demanded skills:", error));

    // Skills Gap
    fetch("api/api.php?endpoint=skills-gap")
        .then(response => response.json())
        .then(data => {
            if (!data || data.length === 0) {
                console.error("No skills gap data found.");
                return;
            }
            const labels = data.map(item => item.skill_name);
            const requiredSkills = data.map(item => item.required_score);
            const studentSkills = data.map(item => item.current_score);
            createChart("skillsGapChart", "bar", labels, [requiredSkills, studentSkills], ["Required Skills", "Current Student Skills"], ["#4B0082", "#FF6B00"]);
        })
        .catch(error => console.error("Error fetching skills gap data:", error));

    // Skills Dev Timeline
    fetch("api/api.php?endpoint=skills-timeline")
        .then(response => response.json())
        .then(data => {
            if (!data || data.length === 0) {
                console.error("No skills timeline data found.");
                return;
            }
            const labels = data.map(item => item.month);
            const webDev = data.map(item => item.web_development);
            const mobileDev = data.map(item => item.mobile_development);
            createChart("developmentTimeline", "line", labels, [webDev, mobileDev], ["Web Development", "Mobile Development"], ["#4B0082", "#FF6B00"]);
        })
        .catch(error => console.error("Error fetching skills timeline data:", error));
});

function createChart(chartId, type, labels, values, labelName, bgColor) {
    if (skillChartInstances[chartId]) {
        skillChartInstances[chartId].destroy();
    }
    // Single or multi-dataset logic
    const isMultiDataset = Array.isArray(values[0]);
    const datasets = isMultiDataset ? values.map((dataArr, i) => ({
        label: labelName[i],
        data: dataArr,
        backgroundColor: bgColor[i],
        borderRadius: 6
    })) : [{
        label: labelName,
        data: values,
        backgroundColor: bgColor,
        borderRadius: 6
    }];

    skillChartInstances[chartId] = new Chart(document.getElementById(chartId), {
        type,
        data: { labels, datasets },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' },
                title: {
                    display: (type !== "line"),
                    text: isMultiDataset ? labelName.join(" & ") : labelName,
                    font: { size: 14, weight: "bold" }
                }
            }
        }
    });
}

// If you prefer to unify all charts in a single object, you can rename skillChartInstances -> chartInstances.
