let reportChartInstance = null;

function initializeReportChart(data = null) {
    // Destroy existing instance to prevent memory leaks (Turbo/Hotwire)
    if (reportChartInstance) {
        reportChartInstance.destroy();
        reportChartInstance = null;
    }

    const chartElement = document.querySelector("#reportChart");

    if (!chartElement) {
        console.warn("Report chart element (#reportChart) not found");
        return null;
    }

    // Default data (for demo/fallback)
    const defaultData = {
        series: [{
            name: "Revenue",
            data: [1200, 1900, 3000, 2500, 4200, 3800, 5000]
        }],
        categories: ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"]
    };

    const chartData = data || defaultData;

    const options = {
        series: chartData.series,

        chart: {
            type: 'area',
            height: 380,           // Slightly taller for better visuals
            toolbar: { show: false },
            zoom: { enabled: false }
        },

        xaxis: {
            categories: chartData.categories,
            axisBorder: { show: false },
            axisTicks: { show: false }
        },

        stroke: {
            curve: 'smooth',
            width: 3
        },

        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.65,
                opacityTo: 0.25,
                stops: [0, 90]
            }
        },

        dataLabels: { enabled: false },
        colors: ['#3b82f6'],

        tooltip: {
            theme: 'light',
            y: {
                formatter: function (val) {
                    return "$ " + val.toLocaleString();
                }
            }
        },

        grid: {
            borderColor: '#e2e8f0',
            strokeDashArray: 4,
        }
    };

        reportChartInstance = new ApexCharts(chartElement, options);
        reportChartInstance.render();

    return reportChartInstance;
}

// Make it globally available
window.initializeReportChart = initializeReportChart;

// Auto-initialize if script loads after DOM
if (document.readyState === 'complete') {
    setTimeout(() => {
        if (typeof initializeReportChart === 'function') {
            initializeReportChart();
        }
    }, 100);
}
