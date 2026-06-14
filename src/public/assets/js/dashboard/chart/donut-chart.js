var DONUT_COLORS = [
    '#3b82f6',
    '#22c55e',
    '#f59e0b',
    '#a855f7',
    '#ef4444',
    '#06b6d4',
    '#84cc16',
];

var donutChart = null;

function buildDonutOptions() {

    return {

        series: [],
        labels: [],

        chart: {
            type:       'donut',
            height:     320,
            fontFamily: 'inherit',
            animations: { enabled: true, speed: 400 },
        },

        colors: DONUT_COLORS,

        legend: {
            position:   'bottom',
            fontSize:   '13px',
            itemMargin: { horizontal: 8, vertical: 4 },
            labels:     { colors: '#64748b' },
        },

        dataLabels: { enabled: false },

        stroke: {
            width:  2,
            colors: ['#ffffff'],
        },

        plotOptions: {
            pie: {
                donut: {
                    size:   '65%',
                    labels: {
                        show:  true,
                        value: {
                            fontSize:   '18px',
                            fontWeight: 600,
                            color:      '#1e293b',
                            formatter:  formatUSD,
                        },
                        total: {
                            show:       true,
                            showAlways: true,
                            label:      'Total Revenue',
                            fontSize:   '13px',
                            color:      '#94a3b8',
                            formatter:  function (w) {
                                return formatUSD(
                                    w.globals.seriesTotals.reduce(function (a, b) {
                                        return a + b;
                                    }, 0)
                                );
                            },
                        },
                    },
                },
            },
        },

        tooltip: {
            y: { formatter: formatUSD },
        },

        noData: {
            text:  'No data for this period',
            style: { color: '#94a3b8', fontSize: '14px' },
        },

    };

}

function initDonutChart() {

    var el = document.querySelector('#donutChart');

    if (!el) {
        console.warn('[DonutChart] #donutChart element not found.');
        return;
    }

    donutChart = new ApexCharts(el, buildDonutOptions());
    donutChart.render();

}

function updateDonutChart(data) {

    if (!donutChart) return;

    donutChart.updateOptions(
        { labels: data.labels || [] },
        false,
        true
    );

    donutChart.updateSeries(data.series || []);

}