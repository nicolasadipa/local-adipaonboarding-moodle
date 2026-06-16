// local_adipaonboarding/admin/telemetry — dashboard: drop-off bar charts por tour.
// Chart.js v4 cargado desde CDN al primer init.
define([
    'core/notification',
    'core/log'
], function(Notification, Log) {
    'use strict';

    // Cache buster con jsrev: el bundle Chart.js esta patched para no llamar
    // define.amd (contamina RequireJS de Moodle). Sin ?v= el browser sirve la
    // version cacheada sin patch. Ver runner.js para contexto.
    var CHARTJS_URL = M.cfg.wwwroot + '/local/adipaonboarding/lib/chart.umd.min.js?v=' + (M.cfg.jsrev || '0');
    var chartPromise = null;

    function loadChartJs() {
        if (chartPromise) {
            return chartPromise;
        }
        chartPromise = new Promise(function(resolve, reject) {
            if (window.Chart) {
                resolve(window.Chart);
                return;
            }
            var script = document.createElement('script');
            script.src = CHARTJS_URL;
            script.onload = function() {
                if (window.Chart) {
                    resolve(window.Chart);
                } else {
                    reject(new Error('Chart.js cargo pero no expone window.Chart'));
                }
            };
            script.onerror = function() {
                reject(new Error('No se pudo cargar Chart.js desde CDN'));
            };
            document.head.appendChild(script);
        });
        return chartPromise;
    }

    function renderDropOff(Chart, tour, container) {
        if (!tour.values || tour.values.length === 0) {
            return;
        }
        var card = document.createElement('div');
        card.className = 'card mb-3';
        var body = document.createElement('div');
        body.className = 'card-body';
        var header = document.createElement('h5');
        header.innerHTML = '<code>' + tour.tourid + '</code>';
        body.appendChild(header);
        var canvas = document.createElement('canvas');
        canvas.height = 80;
        body.appendChild(canvas);
        card.appendChild(body);
        container.appendChild(card);

        new Chart(canvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: tour.labels,
                datasets: [{
                    label: 'Step views',
                    data: tour.values,
                    backgroundColor: '#704EFD',
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {display: false}
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {precision: 0}
                    }
                }
            }
        });
    }

    return {
        init: function(dropoffdata) {
            if (!Array.isArray(dropoffdata) || dropoffdata.length === 0) {
                return;
            }
            var container = document.getElementById('adipa-dropoff-container');
            if (!container) {
                return;
            }
            loadChartJs().then(function(Chart) {
                dropoffdata.forEach(function(tour) {
                    renderDropOff(Chart, tour, container);
                });
                return null;
            }).catch(function(err) {
                Log.debug('local_adipaonboarding: ' + err.message);
                Notification.exception(err);
            });
        }
    };
});
