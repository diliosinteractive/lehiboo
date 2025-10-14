/**
 * EventList Analytics Charts - Chart.js Initialization
 * Initialize charts for vendor analytics dashboard
 */

(function($) {
    'use strict';

    /**
     * Color Palette - Professional & Sober
     */
    const COLORS = {
        primary: '#2563eb',      // Blue - Views
        secondary: '#64748b',    // Grey-blue - Visitors
        success: '#10b981',      // Green - Bookings
        warning: '#f59e0b',      // Orange - Conversion/Contacts
        info: '#06b6d4',         // Cyan - Wishlists
        purple: '#8b5cf6',       // Purple - Shares
        rose: '#f43f5e'          // Rose - Additional
    };

    /**
     * Chart.js Default Configuration
     */
    const defaultOptions = {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: true,
                position: 'top',
                labels: {
                    padding: 15,
                    font: {
                        size: 12,
                        family: "'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif"
                    },
                    usePointStyle: true,
                    pointStyle: 'circle'
                }
            },
            tooltip: {
                backgroundColor: 'rgba(15, 23, 42, 0.9)',
                titleColor: '#fff',
                bodyColor: '#fff',
                borderColor: '#334155',
                borderWidth: 1,
                padding: 12,
                displayColors: true,
                boxPadding: 6,
                usePointStyle: true,
                callbacks: {
                    title: function(tooltipItems) {
                        return tooltipItems[0].label || '';
                    }
                }
            }
        }
    };

    /**
     * Initialize Main Line Chart - Temporal Analytics
     */
    function initMainChart() {
        const canvas = document.getElementById('el-analytics-main-chart');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');

        // Get data from data attributes
        const labels = JSON.parse(canvas.dataset.labels || '[]');
        const views = JSON.parse(canvas.dataset.views || '[]');
        const bookings = JSON.parse(canvas.dataset.bookings || '[]');
        const wishlists = JSON.parse(canvas.dataset.wishlists || '[]');
        const contacts = JSON.parse(canvas.dataset.contacts || '[]');
        const shares = JSON.parse(canvas.dataset.shares || '[]');

        // Chart configuration
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Vues',
                        data: views,
                        borderColor: COLORS.primary,
                        backgroundColor: COLORS.primary + '15',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointBackgroundColor: COLORS.primary,
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointHoverBackgroundColor: COLORS.primary,
                        pointHoverBorderColor: '#fff',
                        order: 1
                    },
                    {
                        label: 'Clics Réserver',
                        data: bookings,
                        borderColor: COLORS.success,
                        backgroundColor: COLORS.success + '15',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointBackgroundColor: COLORS.success,
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointHoverBackgroundColor: COLORS.success,
                        pointHoverBorderColor: '#fff',
                        order: 2
                    },
                    {
                        label: 'Favoris',
                        data: wishlists,
                        borderColor: COLORS.info,
                        backgroundColor: COLORS.info + '15',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointBackgroundColor: COLORS.info,
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointHoverBackgroundColor: COLORS.info,
                        pointHoverBorderColor: '#fff',
                        order: 3
                    },
                    {
                        label: 'Contacts',
                        data: contacts,
                        borderColor: COLORS.warning,
                        backgroundColor: COLORS.warning + '15',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointBackgroundColor: COLORS.warning,
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointHoverBackgroundColor: COLORS.warning,
                        pointHoverBorderColor: '#fff',
                        order: 4
                    },
                    {
                        label: 'Partages',
                        data: shares,
                        borderColor: COLORS.purple,
                        backgroundColor: COLORS.purple + '15',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointBackgroundColor: COLORS.purple,
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointHoverBackgroundColor: COLORS.purple,
                        pointHoverBorderColor: '#fff',
                        order: 5
                    }
                ]
            },
            options: {
                ...defaultOptions,
                aspectRatio: 2.5,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#e2e8f0',
                            drawBorder: false
                        },
                        ticks: {
                            color: '#64748b',
                            font: {
                                size: 11
                            },
                            precision: 0
                        }
                    },
                    x: {
                        grid: {
                            display: false,
                            drawBorder: false
                        },
                        ticks: {
                            color: '#64748b',
                            font: {
                                size: 11
                            },
                            maxRotation: 45,
                            minRotation: 0
                        }
                    }
                },
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    ...defaultOptions.plugins,
                    legend: {
                        ...defaultOptions.plugins.legend,
                        position: 'bottom'
                    },
                    tooltip: {
                        ...defaultOptions.plugins.tooltip,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += context.parsed.y;
                                return label;
                            }
                        }
                    }
                }
            }
        });
    }

    /**
     * Initialize Devices Donut Chart
     */
    function initDevicesChart() {
        const canvas = document.getElementById('el-analytics-devices-chart');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');

        // Get data from data attributes
        const rawLabels = JSON.parse(canvas.dataset.labels || '[]');
        const values = JSON.parse(canvas.dataset.values || '[]');

        // Translate device labels to French
        const labelTranslations = {
            'mobile': 'Mobile',
            'tablet': 'Tablette',
            'desktop': 'Desktop'
        };
        const labels = rawLabels.map(label => labelTranslations[label] || label);

        // Device-specific colors
        const deviceColors = {
            'Mobile': COLORS.primary,
            'Tablette': COLORS.info,
            'Desktop': COLORS.secondary
        };
        const backgroundColors = labels.map(label => deviceColors[label] || COLORS.primary);

        // Chart configuration
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: backgroundColors,
                    borderColor: '#fff',
                    borderWidth: 2,
                    hoverOffset: 8
                }]
            },
            options: {
                ...defaultOptions,
                aspectRatio: 1.5,
                cutout: '65%',
                plugins: {
                    ...defaultOptions.plugins,
                    legend: {
                        ...defaultOptions.plugins.legend,
                        position: 'bottom'
                    },
                    tooltip: {
                        ...defaultOptions.plugins.tooltip,
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                return label + ': ' + value + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    }

    /**
     * Initialize Browsers Donut Chart
     */
    function initBrowsersChart() {
        const canvas = document.getElementById('el-analytics-browsers-chart');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');

        // Get data from data attributes
        const labels = JSON.parse(canvas.dataset.labels || '[]');
        const values = JSON.parse(canvas.dataset.values || '[]');

        // Browser-specific colors
        const browserColors = [
            COLORS.primary,
            COLORS.success,
            COLORS.warning,
            COLORS.info,
            COLORS.purple,
            COLORS.rose,
            COLORS.secondary
        ];
        const backgroundColors = labels.map((_, index) =>
            browserColors[index % browserColors.length]
        );

        // Chart configuration
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: backgroundColors,
                    borderColor: '#fff',
                    borderWidth: 2,
                    hoverOffset: 8
                }]
            },
            options: {
                ...defaultOptions,
                aspectRatio: 1.5,
                cutout: '65%',
                plugins: {
                    ...defaultOptions.plugins,
                    legend: {
                        ...defaultOptions.plugins.legend,
                        position: 'bottom'
                    },
                    tooltip: {
                        ...defaultOptions.plugins.tooltip,
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                return label + ': ' + value + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    }

    /**
     * Initialize all charts when DOM is ready
     */
    $(document).ready(function() {
        // Wait for Chart.js to be loaded
        if (typeof Chart === 'undefined') {
            console.error('Chart.js is not loaded');
            return;
        }

        // Set Chart.js global defaults
        Chart.defaults.font.family = "'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif";
        Chart.defaults.font.size = 12;
        Chart.defaults.color = '#64748b';

        // Initialize all charts
        initMainChart();
        initDevicesChart();
        initBrowsersChart();
    });

})(jQuery);
