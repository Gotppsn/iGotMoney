/**
 * Enhanced Income Charts JavaScript
 * Handles chart initialization for the income management page
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('Income Charts JS loaded');
    
    // Initialize charts with a slight delay to ensure DOM is fully ready
    setTimeout(function() {
        initializeCharts();
    }, 100);
});

/**
 * Initialize all charts on the page
 */
function initializeCharts() {
    if (typeof Chart === 'undefined') {
        console.error('Chart.js is not loaded!');
        return;
    }
    
    try {
        // Initialize each chart separately with proper cleanup
        initializeFrequencyChart();
        initializeTrendChart();
    } catch (error) {
        console.error('Error initializing charts:', error);
    }
}

/**
 * Force clear any charts attached to a specific canvas
 * @param {string} canvasId - Canvas element ID
 */
function forceDestroyChart(canvasId) {
    try {
        // Get the canvas element
        const canvas = document.getElementById(canvasId);
        if (!canvas) return;
        
        // Clear canvas and remove data attributes
        const ctx = canvas.getContext('2d');
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        
        // Remove and recreate the canvas to ensure clean slate
        const parent = canvas.parentNode;
        const newCanvas = document.createElement('canvas');
        newCanvas.id = canvasId;
        newCanvas.width = canvas.width;
        newCanvas.height = canvas.height;
        newCanvas.className = canvas.className;
        
        // Replace the old canvas with the new one
        parent.removeChild(canvas);
        parent.appendChild(newCanvas);
        
        console.log(`Force cleared canvas #${canvasId}`);
    } catch (error) {
        console.error(`Error clearing canvas ${canvasId}:`, error);
    }
}

/**
 * Initialize the frequency distribution chart
 */
function initializeFrequencyChart() {
    // Force clear any existing charts on this canvas
    forceDestroyChart('frequencyChart');
    
    const chartCanvas = document.getElementById('frequencyChart');
    if (!chartCanvas) {
        console.error('Frequency chart canvas element not found!');
        return;
    }

    try {
        // Get chart data from meta tags
        const chartLabelsEl = document.querySelector('meta[name="chart-labels"]');
        const chartDataEl = document.querySelector('meta[name="chart-data"]');
        const chartColorsEl = document.querySelector('meta[name="chart-colors"]');
        const currencySymbolEl = document.querySelector('meta[name="currency-symbol"]');
        
        if (!chartLabelsEl || !chartDataEl || !chartColorsEl) {
            console.error('Chart data meta tags not found!');
            showNoDataMessage('chartNoData');
            return;
        }
        
        let chartLabels = [];
        let chartData = [];
        let chartColors = [];
        
        try {
            chartLabels = JSON.parse(chartLabelsEl.getAttribute('content') || '[]');
            chartData = JSON.parse(chartDataEl.getAttribute('content') || '[]');
            chartColors = JSON.parse(chartColorsEl.getAttribute('content') || '[]');
        } catch (e) {
            console.error('Error parsing chart data JSON:', e);
            showNoDataMessage('chartNoData');
            return;
        }
        
        const currencySymbol = currencySymbolEl ? currencySymbolEl.getAttribute('content') : '$';
        
        if (chartLabels.length === 0 || chartData.length === 0) {
            showNoDataMessage('chartNoData');
            return;
        }

        // Create chart with modern styling
        const ctx = chartCanvas.getContext('2d');
        window.frequencyChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: chartLabels,
                datasets: [{
                    data: chartData,
                    backgroundColor: chartColors,
                    borderColor: '#ffffff',
                    borderWidth: 3,
                    hoverBorderWidth: 3,
                    hoverBorderColor: '#ffffff',
                    hoverOffset: 20
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                animation: {
                    animateScale: true,
                    animateRotate: true,
                    duration: 1500,
                    easing: 'easeInOutQuart'
                },
                layout: {
                    padding: 20
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        padding: 30,
                        labels: {
                            boxWidth: 16,
                            boxHeight: 16,
                            padding: 15,
                            font: {
                                size: 14,
                                weight: 500
                            },
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#ffffff',
                        bodyColor: '#ffffff',
                        padding: 16,
                        cornerRadius: 12,
                        titleFont: {
                            size: 16,
                            weight: 600
                        },
                        bodyFont: {
                            size: 14
                        },
                        displayColors: true,
                        usePointStyle: true,
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                return `${label}: ${currencySymbol}${value.toLocaleString()} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });

        // Set up chart type toggle
        const chartViewToggle = document.getElementById('chartViewToggle');
        if (chartViewToggle) {
            chartViewToggle.addEventListener('change', function() {
                // Force clear existing chart
                forceDestroyChart('frequencyChart');
                
                // Get fresh canvas context
                const newCanvas = document.getElementById('frequencyChart');
                const ctx = newCanvas.getContext('2d');
                const chartType = this.value;
                
                // Create new chart with selected type
                window.frequencyChart = new Chart(ctx, {
                    type: chartType === 'pie' ? 'doughnut' : 'bar',
                    data: {
                        labels: chartLabels,
                        datasets: [{
                            data: chartData,
                            backgroundColor: chartColors,
                            borderColor: chartType === 'pie' ? '#ffffff' : chartColors,
                            borderWidth: chartType === 'pie' ? 3 : 0,
                            hoverBorderWidth: chartType === 'pie' ? 3 : 0,
                            hoverBorderColor: chartType === 'pie' ? '#ffffff' : chartColors,
                            hoverOffset: chartType === 'pie' ? 20 : 0,
                            barThickness: chartType === 'bar' ? 30 : undefined,
                            borderRadius: chartType === 'bar' ? 6 : undefined
                        }]
                    },
                    options: chartType === 'pie' ? {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '65%',
                        animation: {
                            animateScale: true,
                            animateRotate: true,
                            duration: 1500,
                            easing: 'easeInOutQuart'
                        },
                        layout: {
                            padding: 20
                        },
                        plugins: {
                            legend: {
                                position: 'bottom',
                                padding: 30,
                                labels: {
                                    boxWidth: 16,
                                    boxHeight: 16,
                                    padding: 15,
                                    font: {
                                        size: 14,
                                        weight: 500
                                    },
                                    usePointStyle: true,
                                    pointStyle: 'circle'
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                titleColor: '#ffffff',
                                bodyColor: '#ffffff',
                                padding: 16,
                                cornerRadius: 12,
                                titleFont: {
                                    size: 16,
                                    weight: 600
                                },
                                bodyFont: {
                                    size: 14
                                },
                                displayColors: true,
                                usePointStyle: true,
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label || '';
                                        const value = context.parsed;
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = ((value / total) * 100).toFixed(1);
                                        return `${label}: ${currencySymbol}${value.toLocaleString()} (${percentage}%)`;
                                    }
                                }
                            }
                        }
                    } : {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    drawBorder: false,
                                    color: 'rgba(0, 0, 0, 0.05)'
                                },
                                ticks: {
                                    font: {
                                        size: 12
                                    },
                                    callback: function(value) {
                                        return currencySymbol + value.toLocaleString();
                                    }
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    font: {
                                        size: 12
                                    }
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                titleColor: '#ffffff',
                                bodyColor: '#ffffff',
                                padding: 16,
                                cornerRadius: 12,
                                titleFont: {
                                    size: 16,
                                    weight: 600
                                },
                                bodyFont: {
                                    size: 14
                                },
                                callbacks: {
                                    label: function(context) {
                                        const value = context.parsed.y;
                                        return `${currencySymbol}${value.toLocaleString()}`;
                                    }
                                }
                            }
                        }
                    }
                });
            });
        }

        // Hide no data message
        const noDataMessage = document.getElementById('chartNoData');
        if (noDataMessage) {
            noDataMessage.style.display = 'none';
        }

    } catch (error) {
        console.error('Error initializing frequency chart:', error);
        showNoDataMessage('chartNoData');
    }
}

/**
 * Initialize the income trend chart
 */
function initializeTrendChart() {
    // Force clear any existing charts on this canvas
    forceDestroyChart('trendChart');
    
    const trendCanvas = document.getElementById('trendChart');
    if (!trendCanvas) {
        console.error('Trend chart canvas element not found!');
        return;
    }

    try {
        // Get projection data from meta tags
        const projectionLabelsEl = document.querySelector('meta[name="projection-labels"]');
        const projectionDataEl = document.querySelector('meta[name="projection-data"]');
        const currencySymbolEl = document.querySelector('meta[name="currency-symbol"]');
        
        if (!projectionLabelsEl || !projectionDataEl) {
            console.error('Projection data meta tags not found!');
            showNoDataMessage('trendChartNoData');
            return;
        }
        
        let projectionLabels = [];
        let projectionData = [];
        
        try {
            projectionLabels = JSON.parse(projectionLabelsEl.getAttribute('content') || '[]');
            projectionData = JSON.parse(projectionDataEl.getAttribute('content') || '[]');
        } catch (e) {
            console.error('Error parsing projection data JSON:', e);
            showNoDataMessage('trendChartNoData');
            return;
        }
        
        const currencySymbol = currencySymbolEl ? currencySymbolEl.getAttribute('content') : '$';
        
        if (projectionLabels.length === 0 || projectionData.length === 0) {
            showNoDataMessage('trendChartNoData');
            return;
        }

        // Create trend chart
        const ctx = trendCanvas.getContext('2d');
        window.trendChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: projectionLabels,
                datasets: [{
                    label: 'Monthly Income',
                    data: projectionData,
                    backgroundColor: 'rgba(99, 102, 241, 0.2)',
                    borderColor: 'rgba(99, 102, 241, 1)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 4,
                    pointBackgroundColor: 'rgba(99, 102, 241, 1)',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointHoverRadius: 6,
                    pointHoverBackgroundColor: 'rgba(99, 102, 241, 1)',
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            drawBorder: false,
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            font: {
                                size: 12
                            },
                            callback: function(value) {
                                return currencySymbol + value.toLocaleString();
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 12
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#ffffff',
                        bodyColor: '#ffffff',
                        padding: 16,
                        cornerRadius: 12,
                        titleFont: {
                            size: 16,
                            weight: 600
                        },
                        bodyFont: {
                            size: 14
                        },
                        callbacks: {
                            label: function(context) {
                                const value = context.parsed.y;
                                return `${currencySymbol}${value.toLocaleString()}`;
                            }
                        }
                    }
                }
            }
        });

        // Set up projection months toggle
        const projectionMonths = document.getElementById('projectionMonths');
        if (projectionMonths) {
            projectionMonths.addEventListener('change', function() {
                // Force clear existing chart
                forceDestroyChart('trendChart');
                
                // Get fresh canvas context
                const newCanvas = document.getElementById('trendChart');
                const ctx = newCanvas.getContext('2d');
                const months = parseInt(this.value);
                
                // Create new chart with data subset if needed
                const displayLabels = months && months < projectionLabels.length ? 
                    projectionLabels.slice(0, months) : projectionLabels;
                const displayData = months && months < projectionData.length ? 
                    projectionData.slice(0, months) : projectionData;
                
                window.trendChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: displayLabels,
                        datasets: [{
                            label: 'Monthly Income',
                            data: displayData,
                            backgroundColor: 'rgba(99, 102, 241, 0.2)',
                            borderColor: 'rgba(99, 102, 241, 1)',
                            borderWidth: 3,
                            tension: 0.4,
                            fill: true,
                            pointRadius: 4,
                            pointBackgroundColor: 'rgba(99, 102, 241, 1)',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointHoverRadius: 6,
                            pointHoverBackgroundColor: 'rgba(99, 102, 241, 1)',
                            pointHoverBorderColor: '#fff',
                            pointHoverBorderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    drawBorder: false,
                                    color: 'rgba(0, 0, 0, 0.05)'
                                },
                                ticks: {
                                    font: {
                                        size: 12
                                    },
                                    callback: function(value) {
                                        return currencySymbol + value.toLocaleString();
                                    }
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    font: {
                                        size: 12
                                    }
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                titleColor: '#ffffff',
                                bodyColor: '#ffffff',
                                padding: 16,
                                cornerRadius: 12,
                                titleFont: {
                                    size: 16,
                                    weight: 600
                                },
                                bodyFont: {
                                    size: 14
                                },
                                callbacks: {
                                    label: function(context) {
                                        const value = context.parsed.y;
                                        return `${currencySymbol}${value.toLocaleString()}`;
                                    }
                                }
                            }
                        }
                    }
                });
            });
        }

        // Hide no data message
        const noDataMessage = document.getElementById('trendChartNoData');
        if (noDataMessage) {
            noDataMessage.style.display = 'none';
        }

    } catch (error) {
        console.error('Error initializing trend chart:', error);
        showNoDataMessage('trendChartNoData');
    }
}

/**
 * Show no data message and hide chart container
 * @param {string} elementId - ID of the no data message element
 */
function showNoDataMessage(elementId) {
    const chartContainer = document.querySelector(`.chart-container`);
    const noDataMessage = document.getElementById(elementId);
    
    if (chartContainer) {
        chartContainer.style.display = 'none';
    }
    
    if (noDataMessage) {
        noDataMessage.style.display = 'block';
    }
}