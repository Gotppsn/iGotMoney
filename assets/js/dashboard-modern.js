document.addEventListener('DOMContentLoaded', function() {
    console.log('Modern Dashboard JS loaded');
    
    // Initialize all components
    initializeChart();
    initializeEventListeners();
    initializeAnimations();
});

function initializeChart() {
    if (typeof Chart === 'undefined') {
        console.error('Chart.js is not loaded!');
        return;
    }
    
    const chartCanvas = document.getElementById('expenseCategoryChart');
    if (!chartCanvas) {
        console.error('Chart canvas element not found!');
        return;
    }

    try {
        // Get chart data from meta tags
        const chartLabelsEl = document.querySelector('meta[name="chart-labels"]');
        const chartDataEl = document.querySelector('meta[name="chart-data"]');
        const chartColorsEl = document.querySelector('meta[name="chart-colors"]');
        
        if (!chartLabelsEl || !chartDataEl || !chartColorsEl) {
            console.error('Chart data meta tags not found!');
            showNoDataMessage();
            return;
        }
        
        const chartLabels = JSON.parse(chartLabelsEl.getAttribute('content') || '[]');
        const chartData = JSON.parse(chartDataEl.getAttribute('content') || '[]');
        const chartColors = JSON.parse(chartColorsEl.getAttribute('content') || '[]');
        
        if (chartLabels.length === 0 || chartData.length === 0) {
            showNoDataMessage();
            return;
        }

        // Create chart with modern styling
        const ctx = chartCanvas.getContext('2d');
        window.expenseCategoryChart = new Chart(ctx, {
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
                                return `${label}: $${value.toLocaleString()} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });

    } catch (error) {
        console.error('Error initializing chart:', error);
        showNoDataMessage();
    }
}

function showNoDataMessage() {
    const chartContainer = document.querySelector('.chart-container');
    if (chartContainer) {
        chartContainer.innerHTML = `
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <h4>No expense data available</h4>
                <p>Start tracking your expenses to see insights</p>
            </div>
        `;
    }
}

function initializeEventListeners() {
    // Refresh dashboard button
    const refreshButton = document.getElementById('refreshDashboard');
    if (refreshButton) {
        refreshButton.addEventListener('click', function() {
            const icon = this.querySelector('i');
            if (icon) {
                icon.classList.add('fa-spin');
            }
            
            showLoadingOverlay();
            
            setTimeout(() => {
                window.location.reload();
            }, 800);
        });
    }
    
    // Print dashboard button
    const printButton = document.getElementById('printDashboard');
    if (printButton) {
        printButton.addEventListener('click', function() {
            window.print();
        });
    }
    
    // Generate advice button
    const generateAdviceBtn = document.getElementById('generateAdvice');
    if (generateAdviceBtn) {
        generateAdviceBtn.addEventListener('click', function() {
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
            
            setTimeout(() => {
                window.location.href = '?generate_advice=true';
            }, 1000);
        });
    }
    
    // Chart period selector
    const chartPeriodSelect = document.getElementById('chartPeriodSelect');
    if (chartPeriodSelect) {
        chartPeriodSelect.addEventListener('change', function() {
            updateChartData(this.value);
        });
    }
}

function updateChartData(period) {
    const basePath = document.querySelector('meta[name="base-path"]').getAttribute('content');
    
    // Show loading state
    const chartContainer = document.querySelector('.chart-container');
    if (chartContainer) {
        chartContainer.style.opacity = '0.5';
    }
    
    // Simulate data update (in a real app, this would fetch from server)
    setTimeout(() => {
        if (window.expenseCategoryChart) {
            // Generate new data based on period
            const currentData = window.expenseCategoryChart.data.datasets[0].data;
            const newData = currentData.map(value => {
                switch(period) {
                    case 'last-month':
                        return value * 0.8;
                    case 'last-3-months':
                        return value * 2.5;
                    case 'current-year':
                        return value * 10;
                    default:
                        return value;
                }
            });
            
            window.expenseCategoryChart.data.datasets[0].data = newData;
            window.expenseCategoryChart.update();
        }
        
        // Restore opacity
        if (chartContainer) {
            chartContainer.style.opacity = '1';
        }
        
        showNotification('Chart updated to show ' + period + ' data', 'info');
    }, 500);
}

function initializeAnimations() {
    // Animate progress bars on load
    const progressBars = document.querySelectorAll('.category-bar-fill, .budget-bar-fill, .goal-bar-fill, .status-bar-fill');
    progressBars.forEach((bar, index) => {
        setTimeout(() => {
            const width = bar.getAttribute('data-percentage') || bar.style.width.replace('%', '');
            bar.style.width = width + '%';
        }, 100 + (index * 50));
    });

    // Animate value counters
    const valueElements = document.querySelectorAll('.card-value');
    valueElements.forEach(element => {
        const finalValue = parseFloat(element.textContent.replace(/[$,]/g, ''));
        animateValue(element, 0, finalValue, 1500);
    });
}

function animateValue(element, start, end, duration) {
    const startTimestamp = Date.now();
    const step = (timestamp) => {
        const progress = Math.min((Date.now() - startTimestamp) / duration, 1);
        const currentValue = progress * (end - start) + start;
        element.textContent = '$' + currentValue.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
        
        if (progress < 1) {
            window.requestAnimationFrame(step);
        }
    };
    window.requestAnimationFrame(step);
}

function showLoadingOverlay() {
    const loadingOverlay = document.createElement('div');
    loadingOverlay.className = 'loading-overlay';
    loadingOverlay.style.position = 'fixed';
    loadingOverlay.style.top = '0';
    loadingOverlay.style.left = '0';
    loadingOverlay.style.width = '100%';
    loadingOverlay.style.height = '100%';
    loadingOverlay.style.backgroundColor = 'rgba(255, 255, 255, 0.7)';
    loadingOverlay.style.zIndex = '9999';
    loadingOverlay.style.display = 'flex';
    loadingOverlay.style.justifyContent = 'center';
    loadingOverlay.style.alignItems = 'center';
    
    const spinner = document.createElement('div');
    spinner.className = 'spinner';
    spinner.innerHTML = '<i class="fas fa-circle-notch fa-spin fa-3x" style="color: var(--primary-color);"></i>';
    
    loadingOverlay.appendChild(spinner);
    document.body.appendChild(loadingOverlay);
    
    // Remove after 1 second
    setTimeout(() => {
        if (document.body.contains(loadingOverlay)) {
            document.body.removeChild(loadingOverlay);
        }
    }, 1000);
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `dashboard-notification ${type}`;
    notification.style.position = 'fixed';
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.padding = '16px 24px';
    notification.style.borderRadius = '12px';
    notification.style.backgroundColor = type === 'error' ? '#ef4444' : type === 'warning' ? '#f59e0b' : '#3b82f6';
    notification.style.color = 'white';
    notification.style.zIndex = '9999';
    notification.style.opacity = '0';
    notification.style.transition = 'opacity 0.3s ease';
    notification.style.boxShadow = '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)';
    
    let icon;
    switch(type) {
        case 'success':
            icon = 'check-circle';
            notification.style.backgroundColor = '#10b981';
            break;
        case 'warning':
            icon = 'exclamation-triangle';
            break;
        case 'error':
            icon = 'exclamation-circle';
            break;
        default:
            icon = 'info-circle';
    }
    
    notification.innerHTML = `<i class="fas fa-${icon}" style="margin-right: 12px;"></i> ${message}`;
    
    document.body.appendChild(notification);
    
    // Fade in
    setTimeout(() => {
        notification.style.opacity = '1';
    }, 10);
    
    // Fade out and remove
    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 3000);
}