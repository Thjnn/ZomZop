// Biểu đồ trang Tổng quan của quản lý chi nhánh (ApexCharts)
import ApexCharts from "apexcharts";

const dataEl = document.getElementById("dashboard-data");

if (dataEl) {
    const { series, status } = JSON.parse(dataEl.textContent);
    const RED = "#ef4444";
    const money = (v) => new Intl.NumberFormat("vi-VN").format(v) + "đ";
    const shortMoney = (v) =>
        v >= 1e6 ? +(v / 1e6).toFixed(1) + "tr" : v >= 1e3 ? Math.round(v / 1e3) + "k" : v;

    const base = {
        chart: { height: 288, toolbar: { show: false }, zoom: { enabled: false }, fontFamily: "inherit" },
        dataLabels: { enabled: false },
        grid: { borderColor: "#e2e8f0", strokeDashArray: 4 },
        xaxis: { axisBorder: { show: false }, axisTicks: { show: false }, labels: { style: { colors: "#64748b" } } },
        colors: [RED],
    };

    const charts = {
        orders: new ApexCharts(document.querySelector("#orders-chart"), {
            ...base,
            chart: { ...base.chart, type: "area" },
            stroke: { curve: "monotoneCubic", width: 2.5 },
            fill: { type: "gradient", gradient: { opacityFrom: 0.35, opacityTo: 0.02 } },
            series: [{ name: "Số đơn", data: series.year.orders }],
            xaxis: { ...base.xaxis, categories: series.year.labels },
            yaxis: { min: 0, forceNiceScale: true, labels: { formatter: (v) => Math.round(v) } },
        }),
        revenue: new ApexCharts(document.querySelector("#revenue-chart"), {
            ...base,
            chart: { ...base.chart, type: "bar" },
            plotOptions: { bar: { borderRadius: 4, columnWidth: "45%" } },
            series: [{ name: "Doanh thu", data: series.year.revenue }],
            xaxis: { ...base.xaxis, categories: series.year.labels },
            yaxis: { min: 0, labels: { formatter: shortMoney } },
            tooltip: { y: { formatter: money } },
        }),
    };
    Object.values(charts).forEach((c) => c.render());

    // Tab Năm/Tháng/7 ngày: mỗi card đổi dữ liệu biểu đồ của chính nó
    document.querySelectorAll("[data-chart-card]").forEach((card) => {
        const key = card.dataset.chartCard;
        card.querySelectorAll("[data-range]").forEach((btn) => {
            btn.addEventListener("click", () => {
                const s = series[btn.dataset.range];
                charts[key].updateOptions({
                    xaxis: { categories: s.labels },
                    series: [{ data: s[key] }],
                });
                card.querySelectorAll("[data-range]").forEach((b) => {
                    const active = b === btn;
                    b.classList.toggle("bg-red-500", active);
                    b.classList.toggle("text-white", active);
                    b.classList.toggle("text-red-500", !active);
                    b.classList.toggle("hover:bg-red-50", !active);
                });
            });
        });
    });

    const statusEl = document.querySelector("#status-chart");
    if (statusEl) {
        const total = status.reduce((sum, s) => sum + s.count, 0);
        new ApexCharts(statusEl, {
            chart: { type: "donut", height: 240, fontFamily: "inherit" },
            series: status.map((s) => s.count),
            labels: status.map((s) => s.label),
            colors: status.map((s) => s.color),
            legend: { show: false },
            dataLabels: { enabled: false },
            stroke: { width: 2 },
            plotOptions: {
                pie: {
                    donut: {
                        size: "68%",
                        labels: {
                            show: true,
                            value: { fontSize: "22px", fontWeight: 700 },
                            total: { show: true, showAlways: true, label: "Đơn", formatter: () => total },
                        },
                    },
                },
            },
        }).render();
    }
}
