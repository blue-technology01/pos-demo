var currentPeriod = 'today';

var dashboardState = {
    cache: {},
    loading: {},
    pending: {},
};

var dashboardPeriods = ['today', 'yesterday', 'week', 'month', 'year'];

function renderDashboard(data) {

    updateStats(data.stats || {});

    updateColumnChart(data.chart || {});

    updateDonutChart(data.donut || {});

    setDashboardLoading(false);

}

function setDashboardLoading(isLoading) {

    document.body.classList.toggle('dashboard-loading', isLoading);

}

function updateStats(stats) {

    document.getElementById('stat-revenue').textContent =
        formatUSD(stats.revenue ?? 0);

    document.getElementById('stat-orders').textContent =
        (stats.orders ?? 0).toLocaleString();

    document.getElementById('stat-customers').textContent =
        (stats.customers ?? 0).toLocaleString();

    document.getElementById('stat-products').textContent =
        (stats.products ?? 0).toLocaleString();

}

function loadDashboard(period, showLoading) {

    period = period || 'today';
    showLoading = showLoading === true;

    if (dashboardState.cache[period]) {
        if (currentPeriod === period) {
            renderDashboard(dashboardState.cache[period]);
        }
        return;
    }

    if (dashboardState.loading[period]) {
        if (showLoading && currentPeriod === period) {
            setDashboardLoading(true);
        }
        dashboardState.pending[period] = true;
        return;
    }

    dashboardState.loading[period] = true;

    if (showLoading && currentPeriod === period) {
        setDashboardLoading(true);
    }

    var dashboardDataUrl = window.DASHBOARD_DATA_URL || '/admin/dashboard/data';
    var url = dashboardDataUrl + '?period=' + encodeURIComponent(period);

    fetch(url, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        }
    })
    .then(function (res) {

        if (!res.ok) throw new Error('HTTP ' + res.status);

        return res.json();

    })
    .then(function (data) {

        dashboardState.cache[period] = data;

        if (currentPeriod === period) {
            renderDashboard(data);
            preloadDashboardPeriods();
        }

    })
    .catch(function (err) {

        console.error('[Dashboard] Failed to load data:', err);

        if (showLoading && currentPeriod === period) {
            setDashboardLoading(false);
        }

    })
    .finally(function () {

        dashboardState.loading[period] = false;

        if (dashboardState.pending[period]) {
            dashboardState.pending[period] = false;
            loadDashboard(period, showLoading);
        }

    });

}

function preloadDashboardPeriods() {

    dashboardPeriods.forEach(function (period) {
        if (period !== currentPeriod && !dashboardState.cache[period]) {
            loadDashboard(period, false);
        }
    });

}

$(document).on('click', '.period-btn', function () {

    $('.period-btn').removeClass('active');

    $(this).addClass('active');

    currentPeriod = $(this).data('period');

    loadDashboard(currentPeriod, false);

});

$(function () {

    initChart();

    initDonutChart();

    var initialPeriod = window.INITIAL_DASHBOARD_PERIOD || 'today';
    var initialData = window.INITIAL_DASHBOARD_DATA || null;

    currentPeriod = initialPeriod;

    if (initialData) {
        dashboardState.cache[initialPeriod] = initialData;
        renderDashboard(initialData);
        preloadDashboardPeriods();
        return;
    }

    loadDashboard(initialPeriod, true);

});
