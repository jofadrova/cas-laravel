import Chart from "chart.js/auto";

export function initDashboard(quotations) {
    const canvas = document.getElementById("usdChart");
    const monthSelect = document.getElementById("quotationMonth");
    const tableBody = document.getElementById("quotationTableBody");
    const downloadButton = document.getElementById("downloadQuotations");
    const chartMonthLabel = document.getElementById("chartMonthLabel");

    if (!canvas || !monthSelect || !tableBody || !downloadButton) return;

    const now = new Date();
    const currentMonth = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, "0")}`;
    const availableMonths = [...new Set(quotations.map(({ date }) => date.slice(0, 7)))];

    if (!availableMonths.includes(currentMonth)) availableMonths.push(currentMonth);

    availableMonths.sort((a, b) => b.localeCompare(a)).forEach((month) => {
        const option = document.createElement("option");
        const [year, monthNumber] = month.split("-").map(Number);
        option.value = month;
        option.textContent = new Intl.DateTimeFormat("es-BO", {
            month: "long",
            year: "numeric",
            timeZone: "UTC",
        }).format(new Date(Date.UTC(year, monthNumber - 1, 1)));
        monthSelect.appendChild(option);
    });

    monthSelect.value = currentMonth;

    const chart = new Chart(canvas, {
        type: "line",
        data: {
            labels: [],
            datasets: [{
                data: [],
                borderColor: "#198754",
                backgroundColor: "rgba(25,135,84,.15)",
                fill: true,
                tension: 0.35,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
        },
    });

    const selectedQuotations = () => quotations
        .filter(({ date }) => date.startsWith(monthSelect.value))
        .sort((a, b) => a.date.localeCompare(b.date));

    const renderMonth = () => {
        const rows = selectedQuotations();
        tableBody.replaceChildren();

        if (rows.length === 0) {
            const row = tableBody.insertRow();
            const cell = row.insertCell();
            cell.colSpan = 2;
            cell.className = "text-center text-muted py-3";
            cell.textContent = "No hay cotizaciones para este mes.";
        } else {
            [...rows].reverse().forEach(({ date, usd }) => {
                const row = tableBody.insertRow();
                row.insertCell().textContent = date.split("-").reverse().join("/");
                const valueCell = row.insertCell();
                valueCell.className = "text-end fw-bold";
                valueCell.textContent = Number(usd).toFixed(2);
            });
        }

        chart.data.labels = rows.map(({ date }) => `${date.slice(8, 10)}/${date.slice(5, 7)}`);
        chart.data.datasets[0].data = rows.map(({ usd }) => usd);
        chart.update();

        if (chartMonthLabel) {
            chartMonthLabel.textContent = monthSelect.options[monthSelect.selectedIndex]?.textContent ?? "";
        }

        downloadButton.disabled = rows.length === 0;
    };

    monthSelect.addEventListener("change", renderMonth);
    downloadButton.addEventListener("click", () => {
        const csv = [
            "Fecha,USD/BOB",
            ...selectedQuotations().map(({ date, usd }) => `${date},${Number(usd).toFixed(2)}`),
        ].join("\r\n");
        const url = URL.createObjectURL(new Blob(["\uFEFF" + csv], { type: "text/csv;charset=utf-8" }));
        const link = document.createElement("a");
        link.href = url;
        link.download = `cotizaciones-${monthSelect.value}.csv`;
        link.click();
        URL.revokeObjectURL(url);
    });

    renderMonth();
}
