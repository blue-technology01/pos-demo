const CHART_COLORS = {
    profit:  '#22c55e',
    revenue: '#3b82f6',
    cost:    '#ef4444',
};

let columnChart = null;

function buildChartOptions() {

    return {

        series: [
            { name: 'Net Profit', data: [] },
            { name: 'Revenue',    data: [] },
            { name: 'Cost',       data: [] },
        ],

        chart: {
            type:       'bar',
            height:     320,
            toolbar:    { show: false },
            animations: { enabled: true, speed: 400 },
            fontFamily: 'inherit',
        },

        plotOptions: {
            bar: {
                horizontal:              false,
                columnWidth:             '55%',
                borderRadius:            5,
                borderRadiusApplication: 'end',
            },
        },

        dataLabels: { enabled: false },

        stroke: {
            show:   true,
            width:  2,
            colors: ['transparent'],
        },

        xaxis: {
            categories: [],
            labels:     { style: AXIS_LABEL_STYLE },
            axisBorder: { show: false },
            axisTicks:  { show: false },
        },

        yaxis: {
            labels: {
                style:     AXIS_LABEL_STYLE,
                formatter: formatUSD,
            },
        },

        tooltip: {
            shared:    true,
            intersect: false,
            y:         { formatter: formatUSD },
        },

        fill: { opacity: 1 },

        grid: {
            borderColor:     '#f1f5f9',
            strokeDashArray: 4,
            padding:         { left: 0, right: 0 },
        },

        legend: {
            position:        'top',
            horizontalAlign: 'right',
        },

        colors: [
            CHART_COLORS.profit,
            CHART_COLORS.revenue,
            CHART_COLORS.cost,
        ],

        noData: {
            text:  'No data for this period',
            style: { color: '#94a3b8', fontSize: '14px' },
        },

    };

}

function initChart() {

    var el = document.querySelector('#chart');

    if (!el) {
        console.warn('[ColumnChart] #chart element not found.');
        return;
    }

    columnChart = new ApexCharts(el, buildChartOptions());
    columnChart.render();

}

function updateColumnChart(data) {

    if (!columnChart) return;

    columnChart.updateOptions(
        {
            xaxis:  { categories: data.categories || [] },

            series: [
                { name: 'Net Profit', data: data.profit  || [] },
                { name: 'Revenue',    data: data.revenue || [] },
                { name: 'Cost',       data: data.cost    || [] },
            ],
        },
        false,
        true
    );

}