const CHART_COLORS = {
    profit:  '#22c55e',
    revenue: '#3b82f6',
    cost:    '#ef4444',
};

const CHART_LABELS = {
    revenue: 'Revenue',
    profit:  'Profit',
    cost:    'Cost',
};

let columnChartInstance = null;
let currentChartData    = {};
let currentChartMetric  = 'revenue';

function buildChartOptions() {
    return {
        series: [
            {
                name: CHART_LABELS[currentChartMetric],
                data: currentChartData[currentChartMetric] || [],
            },
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
            categories: currentChartData.categories || [],
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
            size:        0,
            strokeWidth: 0,
            hover:       { size: 5 },
        },

        tooltip: {
            shared:    true,
            intersect: false,
            theme:     'light',
            y:         { formatter: (v) => formatUSD(v, 2) },
        },

        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom:    0.55,
                opacityTo:      0.10,
                stops:          [0, 90],
            },
        },

        grid: {
            borderColor:     '#e2e8f0',
            strokeDashArray: 4,
            padding:         { left: 4, right: 12, top: 0, bottom: 0 },
        },

        legend: { show: false },

        colors: [CHART_COLORS[currentChartMetric]],

        noData: {
            text:  'No data for this period',
            style: { color: '#94a3b8', fontSize: '14px' },
        },
    };
}
function initChart() {
    if (columnChartInstance) {
        columnChartInstance.destroy();
        columnChartInstance = null;
    }

    const el = document.querySelector('#chart');
    if (!el) {
        console.warn('[ColumnChart] #chart element not found.');
        return;
    }

    try {
        columnChartInstance = new ApexCharts(el, buildChartOptions());
        columnChartInstance.render();
    } catch (err) {
        console.error('[ColumnChart] Failed to initialize ApexCharts:', err);
        return;
    }

    document.querySelectorAll('.chart-tab').forEach((tab) => {
        tab.addEventListener('click', () => {
            setAreaChartMetric(tab.dataset.chart || 'revenue');
        });
    });
}

function updateColumnChart(data) {
    if (!columnChartInstance) return;

    currentChartData = data || {};

    columnChartInstance.updateOptions(
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

    document.querySelectorAll('.chart-tab').forEach((tab) => {
        tab.classList.toggle('active', tab.dataset.chart === metric);
    });

    updateColumnChart(currentChartData);
}
