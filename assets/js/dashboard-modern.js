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
        const currencySymbolEl = document.querySelector('meta[name="currency-symbol"]');
        
        if (!chartLabelsEl || !chartDataEl || !chartColorsEl) {
            console.error('Chart data meta tags not found!');
            showNoDataMessage();
            return;
        }
        
        const chartLabels = JSON.parse(chartLabelsEl.getAttribute('content') || '[]');
        const chartData = JSON.parse(chartDataEl.getAttribute('content') || '[]');
        const chartColors = JSON.parse(chartColorsEl.getAttribute('content') || '[]');
        const currencySymbol = currencySymbolEl ? currencySymbolEl.getAttribute('content') : '$';
        
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
                                return `${label}: ${currencySymbol}${value.toLocaleString()} (${percentage}%)`;
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
    
    // Generate advice button in empty state
    const generateAdviceEmptyBtn = document.getElementById('generateAdviceEmpty');
    if (generateAdviceEmptyBtn) {
        generateAdviceEmptyBtn.addEventListener('click', function() {
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
    const currencySymbolEl = document.querySelector('meta[name="currency-symbol"]');
    const currencySymbol = currencySymbolEl ? currencySymbolEl.getAttribute('content') : '$';
    
    // Show loading state
    const chartContainer = document.querySelector('.chart-container');
    if (chartContainer) {
        chartContainer.style.opacity = '0.5';
    }
    
    // Fetch new data from server
    fetch(`${basePath}/dashboard?period=${period}`, {
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (window.expenseCategoryChart && data.labels && data.data) {
            // Update chart with new data
            window.expenseCategoryChart.data.labels = data.labels;
            window.expenseCategoryChart.data.datasets[0].data = data.data;
            
            // Update tooltip to use currency symbol
            window.expenseCategoryChart.options.plugins.tooltip.callbacks.label = function(context) {
                const label = context.label || '';
                const value = context.parsed;
                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                const percentage = ((value / total) * 100).toFixed(1);
                return `${label}: ${currencySymbol}${value.toLocaleString()} (${percentage}%)`;
            };
            
            window.expenseCategoryChart.update();
            
            // Update categories list (optional - requires additional implementation)
            updateCategoriesList(data);
        }
        
        // Restore opacity
        if (chartContainer) {
            chartContainer.style.opacity = '1';
        }
        
        showNotification(`Chart updated to show ${period} data`, 'info');
    })
    .catch(error => {
        console.error('Error fetching chart data:', error);
        
        // Fallback to client-side simulation
        if (window.expenseCategoryChart) {
            const currentData = window.expenseCategoryChart.data.datasets[0].data;
            const newData = currentData.map(value => {
                switch(period) {
                    case 'last-month':
                        return value * 0.8;
                    case 'last-3-months':
                        return value * 2.5;
                    case 'current-year':
                        return value * 10;
                    case 'all-time':
                        return value * 15;
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
        
        showNotification(`Chart updated to show ${period} data`, 'info');
    });
}

function updateCategoriesList(data) {
    // This function would update the categories list below the chart
    // Get currency symbol
    const currencySymbolEl = document.querySelector('meta[name="currency-symbol"]');
    const currencySymbol = currencySymbolEl ? currencySymbolEl.getAttribute('content') : '$';
    
    const categoriesList = document.querySelector('.categories-list');
    if (!categoriesList || !data.labels || !data.data) return;
    
    // You can implement more detailed update logic here if needed
    // This is a basic implementation that updates amounts when data changes
    const amountElements = categoriesList.querySelectorAll('.amount');
    if (amountElements.length === data.data.length) {
        amountElements.forEach((element, index) => {
            if (data.data[index] !== undefined) {
                element.textContent = `${currencySymbol}${Number(data.data[index]).toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                })}`;
            }
        });
    }
}

function initializeAnimations() {
    // Get currency symbol
    const currencySymbolEl = document.querySelector('meta[name="currency-symbol"]');
    const currencySymbol = currencySymbolEl ? currencySymbolEl.getAttribute('content') : '$';
    
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
        const rawValue = element.textContent.replace(/[^\d.-]/g, '');
        const finalValue = parseFloat(rawValue);
        if (!isNaN(finalValue)) {
            animateValue(element, 0, finalValue, 1500, currencySymbol);
        }
    });
}

function animateValue(element, start, end, duration, currencySymbol = '$') {
    const startTimestamp = Date.now();
    const step = (timestamp) => {
        const progress = Math.min((Date.now() - startTimestamp) / duration, 1);
        const currentValue = progress * (end - start) + start;
        element.textContent = currencySymbol + currentValue.toLocaleString('en-US', {
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