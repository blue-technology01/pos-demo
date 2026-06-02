var columnOptions = {
    series: [
        {
            name: 'Net Profit',
            data: [44, 55, 57, 56, 61, 58, 63, 60, 66],
        },
        {
            name: 'Revenue',
            data: [76, 85, 101, 98, 87, 105, 91, 114, 94],
        },
        {
            name: 'Free Cash Flow',
            data: [35, 41, 36, 26, 45, 48, 52, 53, 41],
        },
    ],
    chart: {
        type: 'bar',
        height: 320,
        toolbar: { show: false },
    },
    plotOptions: {
        bar: {
            horizontal: false,
            columnWidth: '55%',
            borderRadius: 5,
            borderRadiusApplication: 'end',
        },
    },
    dataLabels: {
        enabled: false,
    },
    stroke: {
        show: true,
        width: 2,
        colors: ['transparent'],
    },
    xaxis: {
        categories: ['Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct'],
        labels: { style: { colors: '#94a3b8', fontSize: '12px' } },
    },
    yaxis: {
        title: { text: '$ (thousands)' },
        labels: { style: { colors: '#94a3b8' } },
    },
    fill: {
        opacity: 1,
    },
    tooltip: {
        y: {
            formatter: function (val) {
                return '$ ' + val + ' thousands'
            },
        },
    },
    grid: {
        borderColor: '#f1f5f9',
        strokeDashArray: 4,
    },
    legend: {
        position: 'top',
        horizontalAlign: 'right',
    },
    colors: ['#3b82f6', '#22c55e', '#a855f7'],
}

var columnChart = new ApexCharts(document.querySelector('#chart'), columnOptions)
columnChart.render()
