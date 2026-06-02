let topProductChartInstance = null;

window.initTopProductChart = function () {
    // Destroy any existing chart first
    if (topProductChartInstance) {
        try {
            topProductChartInstance.destroy();
        } catch (e) {
            console.log("Chart destroy error:", e);
        }
        topProductChartInstance = null;
    }

    const container = document.querySelector("#topProductsChart");
    if (!container) {
        console.warn("#topProductsChart not found");
        return null;
    }

    // Clear previous rendered content
    container.innerHTML = '';

    const options = {
        chart: {
            type: 'bar',
            height: 350,
            toolbar: { show: false }
        },
        series: [{
            name: 'Units Sold',
            data: [842, 612, 398, 756, 284, 210, 142]
        }],
        xaxis: {
            categories: ['Coca-Cola', 'Pepsi', 'Rice', 'Noodles', 'Shampoo', 'Oil', 'Broom']
        },
        colors: ['#6366f1'],
        plotOptions: {
            bar: { borderRadius: 6, columnWidth: '45%' }
        },
        dataLabels: { enabled: false },
        tooltip: {
            y: { formatter: (val) => val + " units" }
        }
    };

    topProductChartInstance = new ApexCharts(container, options);
    topProductChartInstance.render();

    return topProductChartInstance;
};
