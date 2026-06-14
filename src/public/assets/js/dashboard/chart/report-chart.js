let reportChartInstance = null;

function initializeReportChart(data = null) {
    if (reportChartInstance) {
        reportChartInstance.destroy();
        reportChartInstance = null;
    }

    const chartElement = document.querySelector('#reportChart');
    if (!chartElement) {
        console.warn('Report chart element (#reportChart) not found');
        return;
    }
    chartElement.innerHTML = '';

    const chartData = data || {
        categories: [],
        series: [{ name: 'Revenue', data: [] }]
    };

    const options = {
        series: Array.isArray(chartData.series) ? chartData.series : [chartData.series],
        chart: {
            type: 'area',
            height: 380,
            toolbar: { show: false },
            zoom: { enabled: false }
        },
        xaxis: {
            categories: chartData.categories,
            axisBorder: { show: false },
            axisTicks: { show: false }
        },
        yaxis: {
            labels: {
                formatter: function (value) {
                    return '$' + Number(value).toLocaleString();
                }
            }
        },
        stroke: {
            curve: 'smooth',
            width: 3
        },
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.55,
                opacityTo: 0.10,
                stops: [0, 90]
            }
        },
        markers: {
            size: 4,
            hover: { size: 6 }
        },
        dataLabels: { enabled: false },
        colors: ['#3b82f6'],
        tooltip: {
            theme: 'light',
            y: {
                formatter: function (value) {
                    return '$' + Number(value).toLocaleString(undefined, {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                }
            }
        },
        grid: {
            borderColor: '#e2e8f0',
            strokeDashArray: 4
        },
        noData: {
            text: 'No revenue data found'
        },
    };

    reportChartInstance = new ApexCharts(chartElement, options);
    reportChartInstance.render();
}

window.initializeReportChart = initializeReportChart;

document.addEventListener('turbo:load', () => {
    initializeReportChart(window.reportChartData);
});

document.addEventListener('DOMContentLoaded', () => {
    initializeReportChart(window.reportChartData);
});
