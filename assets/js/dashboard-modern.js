document.addEventListener('DOMContentLoaded', function() {
    console.log('Enhanced Dashboard JS loaded');
    
    // Initialize all components
    initializeChart();
    initializeEventListeners();
    initializeAnimations();
    
    // Show initial loading animation
    showPageLoadAnimation();
});

/**
 * Show a subtle page load animation
 */
function showPageLoadAnimation() {
    // Add a class to trigger animations
    document.body.classList.add('dashboard-loaded');
    
    // Animate summary cards with staggered delay
    const summaryCards = document.querySelectorAll('.summary-card');
    summaryCards.forEach((card, index) => {
        setTimeout(() => {
            card.classList.add('animated');
        }, 100 + (index * 100));
    });
}

/**
 * Initialize the expense category chart
 */
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
        
        // Parse the data from meta tags
        let chartLabels, chartData, chartColors;
        try {
            chartLabels = JSON.parse(chartLabelsEl.getAttribute('content') || '[]');
            chartData = JSON.parse(chartDataEl.getAttribute('content') || '[]');
            chartColors = JSON.parse(chartColorsEl.getAttribute('content') || '[]');
        } catch (e) {
            console.error('Error parsing chart data:', e);
            showNoDataMessage();
            return;
        }
        
        const currencySymbol = currencySymbolEl ? currencySymbolEl.getAttribute('content') : '$';
        
        if (chartLabels.length === 0 || chartData.length === 0) {
            showNoDataMessage();
            return;
        }

        // Create chart with enhanced styling
        const ctx = chartCanvas.getContext('2d');
        
        // Set Chart.js defaults for better appearance
        Chart.defaults.font.family = "'Inter', 'Noto Sans Thai', system-ui, sans-serif";
        Chart.defaults.color = '#64748b';
        
        window.expenseCategoryChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: chartLabels,
                datasets: [{
                    data: chartData,
                    backgroundColor: chartColors,
                    borderColor: '#ffffff',
                    borderWidth: 4,
                    hoverBorderWidth: 5,
                    hoverBorderColor: '#ffffff',
                    hoverOffset: 15,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                animation: {
                    animateScale: true,
                    animateRotate: true,
                    duration: 2000,
                    easing: 'easeOutQuart'
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
                            padding: 20,
                            font: {
                                size: 14,
                                weight: 500
                            },
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.8)',
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
                        boxPadding: 6,
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
        
        // Add center text plugin (optional)
        appendChartCenterText(chartCanvas, calculateTotal(chartData), currencySymbol);

    } catch (error) {
        console.error('Error initializing chart:', error);
        showNoDataMessage();
    }
}

/**
 * Calculate the total value of a data array
 */
function calculateTotal(dataArray) {
    return dataArray.reduce((sum, value) => sum + value, 0);
}

/**
 * Append center text to doughnut chart
 */
function appendChartCenterText(canvas, totalValue, currencySymbol) {
    const centerContainer = document.createElement('div');
    centerContainer.classList.add('chart-center-text');
    centerContainer.innerHTML = `
        <div class="chart-center-value">${currencySymbol}${totalValue.toLocaleString()}</div>
        <div class="chart-center-label">Total Expenses</div>
    `;
    
    // Position the element
    centerContainer.style.position = 'absolute';
    centerContainer.style.top = '50%';
    centerContainer.style.left = '50%';
    centerContainer.style.transform = 'translate(-50%, -50%)';
    centerContainer.style.textAlign = 'center';
    
    // Style the content
    centerContainer.style.pointerEvents = 'none';
    centerContainer.querySelector('.chart-center-value').style.fontSize = '1.5rem';
    centerContainer.querySelector('.chart-center-value').style.fontWeight = '700';
    centerContainer.querySelector('.chart-center-value').style.color = '#1e293b';
    centerContainer.querySelector('.chart-center-label').style.fontSize = '0.875rem';
    centerContainer.querySelector('.chart-center-label').style.color = '#64748b';
    
    // Add to chart container
    canvas.parentNode.style.position = 'relative';
    canvas.parentNode.appendChild(centerContainer);
}

/**
 * Show no data message for chart
 */
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
                <a href="expenses/add" class="btn-primary">
                    <i class="fas fa-plus"></i> Add First Expense
                </a>
            </div>
        `;
    }
}

/**
 * Initialize all event listeners
 */
function initializeEventListeners() {
    // Refresh dashboard button
    const refreshButton = document.getElementById('refreshDashboard');
    if (refreshButton) {
        refreshButton.addEventListener('click', function() {
            const icon = this.querySelector('i');
            if (icon) {
                icon.classList.add('fa-spin');
            }
            
            showLoadingOverlay('Refreshing dashboard...');
            
            setTimeout(() => {
                window.location.reload();
            }, 800);
        });
    }
    
    // Print dashboard button
    const printButton = document.getElementById('printDashboard');
    if (printButton) {
        printButton.addEventListener('click', function() {
            showNotification('Preparing to print...', 'info');
            setTimeout(() => {
                window.print();
            }, 300);
        });
    }
    
    // Generate advice button
    const generateAdviceBtn = document.getElementById('generateAdvice');
    if (generateAdviceBtn) {
        generateAdviceBtn.addEventListener('click', function() {
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
            
            showNotification('Generating personalized advice...', 'info');
            
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
            
            showNotification('Generating personalized advice...', 'info');
            
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
    
    // Add hover effects to category items
    const categoryItems = document.querySelectorAll('.category-item');
    categoryItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            const categoryName = this.querySelector('.category-info h4').textContent.trim();
            highlightChartSegment(categoryName);
        });
        
        item.addEventListener('mouseleave', function() {
            resetChartHighlight();
        });
    });
}

/**
 * Highlight a segment in the chart based on label
 */
function highlightChartSegment(labelToHighlight) {
    if (!window.expenseCategoryChart) return;
    
    const chart = window.expenseCategoryChart;
    const activeElements = [];
    
    // Find the index of the segment to highlight
    chart.data.labels.forEach((label, index) => {
        if (label === labelToHighlight) {
            activeElements.push({
                datasetIndex: 0,
                index: index
            });
        }
    });
    
    // Set active elements to highlight the segment
    chart.setActiveElements(activeElements);
    chart.update();
}

/**
 * Reset chart highlighting
 */
function resetChartHighlight() {
    if (!window.expenseCategoryChart) return;
    
    window.expenseCategoryChart.setActiveElements([]);
    window.expenseCategoryChart.update();
}

/**
 * Update chart data based on period selection
 */
function updateChartData(period) {
    const basePath = document.querySelector('meta[name="base-path"]').getAttribute('content');
    const currencySymbolEl = document.querySelector('meta[name="currency-symbol"]');
    const currencySymbol = currencySymbolEl ? currencySymbolEl.getAttribute('content') : '$';
    
    // Show loading state
    const chartContainer = document.querySelector('.chart-container');
    if (chartContainer) {
        chartContainer.style.opacity = '0.5';
    }
    
    showNotification(`Loading ${period} data...`, 'info');
    
    // Fetch new data from server
    fetch(`${basePath}/dashboard?period=${period}&ajax=true`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (window.expenseCategoryChart && data.labels && data.data) {
            // Update chart with new data
            window.expenseCategoryChart.data.labels = data.labels;
            window.expenseCategoryChart.data.datasets[0].data = data.data;
            
            // Update center text if it exists
            const centerTextValue = document.querySelector('.chart-center-value');
            if (centerTextValue) {
                const total = calculateTotal(data.data);
                centerTextValue.textContent = `${currencySymbol}${total.toLocaleString()}`;
            }
            
            // Apply smooth transition
            window.expenseCategoryChart.update('active');
            
            // Update categories list
            if (data.categories) {
                updateCategoriesList(data.categories);
            }
        }
        
        // Restore opacity with transition
        if (chartContainer) {
            chartContainer.style.transition = 'opacity 0.5s ease';
            chartContainer.style.opacity = '1';
        }
        
        showNotification(`Chart updated to show ${getPeriodLabel(period)} data`, 'success');
    })
    .catch(error => {
        console.error('Error fetching chart data:', error);
        
        // Fallback to client-side simulation for demo purposes
        if (window.expenseCategoryChart) {
            simulateChartUpdate(period);
        }
        
        // Restore opacity
        if (chartContainer) {
            chartContainer.style.opacity = '1';
        }
        
        showNotification(`Using simulated data for ${getPeriodLabel(period)}`, 'warning');
    });
}

/**
 * Get readable period label
 */
function getPeriodLabel(period) {
    const periodMap = {
        'current-month': 'this month\'s',
        'last-month': 'last month\'s',
        'last-3-months': 'last 3 months\'',
        'current-year': 'this year\'s',
        'all-time': 'all-time'
    };
    
    return periodMap[period] || period;
}

/**
 * Simulate chart data update for demo purposes
 */
function simulateChartUpdate(period) {
    const currentData = window.expenseCategoryChart.data.datasets[0].data;
    let newData = [];
    
    switch(period) {
        case 'last-month':
            newData = currentData.map(value => value * (0.7 + Math.random() * 0.4));
            break;
        case 'last-3-months':
            newData = currentData.map(value => value * (2.2 + Math.random() * 0.6));
            break;
        case 'current-year':
            newData = currentData.map(value => value * (8 + Math.random() * 4));
            break;
        case 'all-time':
            newData = currentData.map(value => value * (12 + Math.random() * 6));
            break;
        default:
            newData = currentData;
    }
    
    // Round numbers for more natural appearance
    newData = newData.map(value => Math.round(value));
    
    window.expenseCategoryChart.data.datasets[0].data = newData;
    window.expenseCategoryChart.update('active');
    
    // Update center text if it exists
    const centerTextValue = document.querySelector('.chart-center-value');
    const currencySymbolEl = document.querySelector('meta[name="currency-symbol"]');
    const currencySymbol = currencySymbolEl ? currencySymbolEl.getAttribute('content') : '$';
    
    if (centerTextValue) {
        const total = calculateTotal(newData);
        centerTextValue.textContent = `${currencySymbol}${total.toLocaleString()}`;
    }
    
    // Also simulate updating the categories list
    simulateUpdateCategoriesList(newData);
}

/**
 * Update categories list with new data
 */
function updateCategoriesList(categories) {
    const categoriesList = document.querySelector('.categories-list');
    if (!categoriesList) return;
    
    // Get all existing category items
    const categoryItems = categoriesList.querySelectorAll('.category-item');
    
    // If the number matches, just update values
    if (categoryItems.length === categories.length) {
        categories.forEach((category, index) => {
            const item = categoryItems[index];
            if (item) {
                const amountElement = item.querySelector('.amount');
                const barFill = item.querySelector('.category-bar-fill');
                
                if (amountElement) {
                    amountElement.textContent = category.formatted_amount;
                }
                
                if (barFill) {
                    barFill.style.width = category.percentage + '%';
                }
            }
        });
    } else {
        // Otherwise, rebuild the entire list (structure changed)
        // This would require server to send full HTML or structured data
        // For simplicity, we're not implementing this now
    }
}

/**
 * Simulate updating categories list for demo purposes
 */
function simulateUpdateCategoriesList(newData) {
    const categoriesList = document.querySelector('.categories-list');
    if (!categoriesList) return;
    
    const categoryItems = categoriesList.querySelectorAll('.category-item');
    const total = newData.reduce((sum, value) => sum + value, 0);
    const currencySymbolEl = document.querySelector('meta[name="currency-symbol"]');
    const currencySymbol = currencySymbolEl ? currencySymbolEl.getAttribute('content') : '$';
    
    categoryItems.forEach((item, index) => {
        if (index < newData.length) {
            const value = newData[index];
            const percentage = (value / total) * 100;
            const amountElement = item.querySelector('.amount');
            const barFill = item.querySelector('.category-bar-fill');
            
            if (amountElement) {
                amountElement.textContent = currencySymbol + value.toLocaleString();
            }
            
            if (barFill) {
                barFill.style.width = percentage + '%';
            }
        }
    });
}

/**
 * Initialize animations
 */
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
    valueElements.forEach((element, index) => {
        // Extract numeric value from text
        const rawText = element.textContent;
        const rawValue = rawText.replace(/[^\d.-]/g, '');
        const finalValue = parseFloat(rawValue);
        
        if (!isNaN(finalValue)) {
            // Reset to zero initially
            element.textContent = currencySymbol + '0.00';
            
            // Start animation with slight staggered delay
            setTimeout(() => {
                animateValue(element, 0, finalValue, 1500, currencySymbol);
            }, 300 + (index * 150));
        }
    });
}

/**
 * Animate a numeric value
 */
function animateValue(element, start, end, duration, currencySymbol = '$') {
    const startTimestamp = Date.now();
    const formatter = new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
    
    // Use cubic-bezier for more natural animation
    const easeOutQuart = (t) => 1 - Math.pow(1 - t, 4);
    
    const step = () => {
        const progress = Math.min((Date.now() - startTimestamp) / duration, 1);
        const easedProgress = easeOutQuart(progress);
        const currentValue = easedProgress * (end - start) + start;
        
        element.textContent = currencySymbol + formatter.format(currentValue);
        
        if (progress < 1) {
            window.requestAnimationFrame(step);
        }
    };
    
    window.requestAnimationFrame(step);
}

/**
 * Show loading overlay with message
 */
function showLoadingOverlay(message = 'Loading...') {
    // Remove any existing overlay
    const existingOverlay = document.querySelector('.loading-overlay');
    if (existingOverlay) {
        existingOverlay.remove();
    }
    
    const loadingOverlay = document.createElement('div');
    loadingOverlay.className = 'loading-overlay';
    
    const spinner = document.createElement('div');
    spinner.className = 'spinner';
    spinner.innerHTML = `
        <i class="fas fa-circle-notch fa-spin fa-3x"></i>
        <div class="spinner-text">${message}</div>
    `;
    
    loadingOverlay.appendChild(spinner);
    document.body.appendChild(loadingOverlay);
    
    // Show with animation
    setTimeout(() => {
        loadingOverlay.style.opacity = '1';
    }, 10);
    
    // Remove after 1.5 seconds or call removeLoadingOverlay()
    setTimeout(() => {
        removeLoadingOverlay();
    }, 1500);
    
    return loadingOverlay;
}

/**
 * Remove loading overlay with animation
 */
function removeLoadingOverlay() {
    const loadingOverlay = document.querySelector('.loading-overlay');
    if (loadingOverlay) {
        loadingOverlay.style.opacity = '0';
        
        setTimeout(() => {
            if (document.body.contains(loadingOverlay)) {
                document.body.removeChild(loadingOverlay);
            }
        }, 300);
    }
}

/**
 * Show notification message
 */
function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existingNotifications = document.querySelectorAll('.dashboard-notification');
    existingNotifications.forEach(notification => {
        notification.classList.remove('visible');
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    });
    
    // Create new notification
    const notification = document.createElement('div');
    notification.className = `dashboard-notification ${type}`;
    
    // Choose icon based on notification type
    let icon;
    switch(type) {
        case 'success':
            icon = 'check-circle';
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
    
    notification.innerHTML = `<i class="fas fa-${icon}"></i> ${message}`;
    
    // Add to document
    document.body.appendChild(notification);
    
    // Trigger animation after a small delay
    setTimeout(() => {
        notification.classList.add('visible');
    }, 10);
    
    // Remove notification after delay
    setTimeout(() => {
        notification.classList.remove('visible');
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 3500);
    
    return notification;
}