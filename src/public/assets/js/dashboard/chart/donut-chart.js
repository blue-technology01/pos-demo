var donutOptions = {
    series: [38, 27, 19, 10, 6],
    chart: {
        type: 'donut',
        height: 320,
    },
    labels: ['Groceries', 'Beverages', 'Snacks & Bakery', 'Household', 'Personal Care'],
    colors: ['#3b82f6', '#22c55e', '#f59e0b', '#a855f7', '#ef4444'],
    legend: {
        position: 'bottom',
        fontSize: '13px',
        fontFamily: 'Plus Jakarta Sans, sans-serif',
        fontWeight: 500,
        markers: {
            width: 10,
            height: 10,
            radius: 3,
        },
        itemMargin: {
            horizontal: 10,
            vertical: 4,
        },
    },
    plotOptions: {
        pie: {
            donut: {
                size: '65%',
                labels: {
                    show: true,
                    name: {
                        show: true,
                        fontSize: '13px',
                        fontFamily: 'Plus Jakarta Sans, sans-serif',
                        color: '#94a3b8',
                    },
                    value: {
                        show: true,
                        fontSize: '20px',
                        fontFamily: 'Plus Jakarta Sans, sans-serif',
                        fontWeight: 700,
                        color: '#0f172a',
                        formatter: function (val) {
                            return '$' + Number(val).toLocaleString() + 'k'
                        },
                    },
                    total: {
                        show: true,
                        label: 'Total Income',
                        fontSize: '12px',
                        fontFamily: 'Plus Jakarta Sans, sans-serif',
                        color: '#94a3b8',
                        formatter: function (w) {
                            var total = w.globals.seriesTotals.reduce((a, b) => a + b, 0)
                            return '$' + total + 'k'
                        },
                    },
                },
            },
        },
    },
    dataLabels: {
        enabled: false,
    },
    stroke: {
        width: 2,
        colors: ['#ffffff'],
    },
    responsive: [
        {
            breakpoint: 480,
            options: {
                chart: { width: 260 },
                legend: { position: 'bottom' },
            },
        },
    ],
    tooltip: {
        y: {
            formatter: function (val) {
                return '$' + val + 'k this period'
            },
        },
    },
}

var donutChart = new ApexCharts(document.querySelector('#donutChart'), donutOptions)
donutChart.render()
