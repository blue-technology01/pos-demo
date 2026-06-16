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

    const data = window.topProductsData || [];

    const categories = data.map(item => item.name);
    const qtySold    = data.map(item => item.qty_sold);
    const revenue    = data.map(item => item.total_revenue);

    if (data.length === 0) {
        container.innerHTML = '<p style="text-align:center; color:#9ca3af; padding:40px;">No data available</p>';
        return null;
    }

    const options = {
        chart: {
            type: 'bar',
            height: 350,
            toolbar: { show: false }
        },
        series: [
            {
                name: 'Qty Sold',
                data: qtySold
            },
            {
                name: 'Revenue ($)',
                data: revenue
            }
        ],
        xaxis: {
            categories: categories,
            labels: {
                rotate: -45,
                style: { fontSize: '12px' }
            }
        },
        colors: ['#6366f1', '#10b981'],
        plotOptions: {
            bar: {
                borderRadius: 6,
                columnWidth: '45%'
            }
        },
        dataLabels: { enabled: false },
        tooltip: {
            y: {
                formatter: function (val, opts) {
                    if (opts.seriesIndex === 0) {
                        return val + ' units';
                    }
                    return '$' + val.toFixed(2);
                }
            }
        },
        legend: {
            position: 'top'
        },
        yaxis: [
            {
                title: { text: 'Qty Sold' },
                labels: {
                    formatter: val => val.toFixed(0)
                }
            },
            {
                opposite: true,
                title: { text: 'Revenue ($)' },
                labels: {
                    formatter: val => '$' + val.toFixed(0)
                }
            }
        ]
    };

    topProductChartInstance = new ApexCharts(container, options);
    topProductChartInstance.render();

    return topProductChartInstance;
};
