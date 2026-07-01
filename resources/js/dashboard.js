import Chart from "chart.js/auto";
export function initDashboard(labels, values) {
    const canvas = document.getElementById("usdChart");

    if (!canvas) return;

    new Chart(canvas, {
        type: "line",
        data: {
            labels,
            datasets: [
                {
                    data: values,
                    borderColor: "#198754",
                    backgroundColor: "rgba(25,135,84,.15)",
                    fill: true,
                    tension: 0.35,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false,
                },
            },
        },
    });
}
