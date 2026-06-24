const DONUT_COLORS = [
    '#a78bfa', // purple
    '#fb7185', // pink-red
    '#34d399', // green
    '#38bdf8', // sky blue
    '#fbbf24', // amber
    '#f472b6', // pink
    '#60a5fa', // blue
];

let donutChartInstance = null;

function buildDonutOptions() {
    return {
        series: [],
        labels: [],

        chart: {
            type:       'donut',
            height:     320,
            fontFamily: 'inherit',
            animations: {
                enabled:          true,
                speed:            600,
                animateGradually: { enabled: true, delay: 120 },
                dynamicAnimation: { enabled: true, speed: 400 },
            },
        },

        colors: DONUT_COLORS,

        // ── Rounded ends (the key to that look) ──
        plotOptions: {
            pie: {
                expandOnClick: false,
                donut: {
                    size: '78%',
                    background: 'transparent',
                    labels: {
                        show: true,
                        name: {
                            show:       true,
                            fontSize:   '13px',
                            fontWeight: 500,
                            color:      '#94a3b8',
                            offsetY:    -4,
                        },
                        value: {
                            show:       true,
                            fontSize:   '28px',
                            fontWeight: 700,
                            color:      '#0f172a',
                            offsetY:    6,
                            formatter:  formatUSD,
                        },
                        total: {
                            show:       true,
                            showAlways: true,
                            label:      'Total Revenue',
                            fontSize:   '13px',
                            fontWeight: 500,
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

        // ── Rounded segment ends ──
        stroke: {
            width:     0,
            lineCap:   'round',
            colors:    ['transparent'],
        },

        // ── No data labels on segments ──
        dataLabels: { enabled: false },

        // ── Clean legend ──
        legend: {
            position:   'bottom',
            fontSize:   '13px',
            fontWeight: 500,
            itemMargin: { horizontal: 10, vertical: 6 },
            markers: {
                width:  8,
                height: 8,
                radius: 8,
            },
            labels: { colors: '#64748b' },
        },

        // ── Subtle hover ──
        states: {
            hover:  { filter: { type: 'darken', value: 0.06 } },
            active: { filter: { type: 'darken', value: 0.10 } },
        },

        tooltip: {
            theme:           'light',
            fillSeriesColor: false,
            y: { formatter: formatUSD },
        },

        noData: {
            text:  'No data for this period',
            style: { color: '#94a3b8', fontSize: '14px' },
        },
    };
}

function initDonutChart() {
    if (donutChartInstance) {
        donutChartInstance.destroy();
        donutChartInstance = null;
    }

    const el = document.querySelector('#donutChart');
    if (!el) {
        console.warn('[DonutChart] #donutChart element not found.');
        return;
    }

    try {
        donutChartInstance = new ApexCharts(el, buildDonutOptions());
        donutChartInstance.render();
    } catch (err) {
        console.error('[DonutChart] Failed to initialize ApexCharts:', err);
    }
}

function updateDonutChart(data) {
    if (!donutChartInstance) return;

    donutChartInstance.updateOptions(
        { labels: data.labels || [] },
        false,
        true
    );

    donutChartInstance.updateSeries(data.series || []);
}
