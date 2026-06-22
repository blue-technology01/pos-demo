const CHART_COLORS = {
    profit:  '#22c55e',
    revenue: '#3b82f6',
    cost:    '#ef4444',
};

let columnChart = null;
let currentChartData = {};
let currentChartMetric = 'revenue';

const CHART_LABELS = {
    revenue: 'Revenue',
    profit:  'Profit',
    cost:    'Cost',
};

function buildChartOptions() {

    return {

        series: [
            { name: CHART_LABELS[currentChartMetric], data: [] },
        ],

        chart: {
            type:       'area',
            height:     320,
            toolbar:    { show: false },
            zoom:       { enabled: false },
            animations: { enabled: true, speed: 350 },
            fontFamily: 'inherit',
        },

        dataLabels: { enabled: false },

        stroke: {
            curve:   'smooth',
            width:   3,
            lineCap: 'round',
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

        markers: {
            size: 0,
            strokeWidth: 0,
            hover: { size: 5 },
        },

        tooltip: {
            shared:    true,
            intersect: false,
            y:         { formatter: formatUSD },
        },

        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.24,
                opacityTo: 0.02,
                stops: [0, 95, 100],
            },
        },

        grid: {
            borderColor:     '#f1f5f9',
            strokeDashArray: 4,
            padding:         { left: 4, right: 12, top: 0, bottom: 0 },
        },

        legend: {
            show: false,
        },

        colors: [CHART_COLORS[currentChartMetric]],

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

    document.querySelectorAll('.chart-tab').forEach(function (tab) {
        tab.addEventListener('click', function () {
            setAreaChartMetric(tab.dataset.chart || 'revenue');
        });
    });

}

function updateColumnChart(data) {

    if (!columnChart) return;

    currentChartData = data || {};

    columnChart.updateOptions(
        {
            colors: [CHART_COLORS[currentChartMetric]],
            stroke: { curve: 'smooth', width: 3, lineCap: 'round' },
            xaxis:  { categories: currentChartData.categories || [] },

            series: [
                {
                    name: CHART_LABELS[currentChartMetric],
                    data: currentChartData[currentChartMetric] || [],
                },
            ],
        },
        false,
        true
    );

}

function setAreaChartMetric(metric) {

    if (!CHART_LABELS[metric]) return;

    currentChartMetric = metric;

    document.querySelectorAll('.chart-tab').forEach(function (tab) {
        tab.classList.toggle('active', tab.dataset.chart === metric);
    });

    updateColumnChart(currentChartData);

}
