/**
 * iGotMoney Dashboard JavaScript
 * Modern, clean, minimal design implementation
 */

document.addEventListener('DOMContentLoaded', function() {
  // Initialize all components
  initializeSummaryCards();
  initializeProgressBars();
  initializeAnimations();
  initializeChart();
  initializeEventListeners();
});

/**
 * Initialize animated summary cards
 */
function initializeSummaryCards() {
  const summaryCards = document.querySelectorAll('.summary-card');
  
  summaryCards.forEach((card, index) => {
    setTimeout(() => {
      card.classList.add('aos-animate');
    }, 100 + (index * 100));
    
    // Animate the card value with counting effect
    const valueElement = card.querySelector('.card-value');
    if (valueElement) {
      const finalValue = parseFloat(valueElement.getAttribute('data-value') || '0');
      animateCounter(valueElement, finalValue);
    }
  });
}

/**
 * Animate counter effect for numbers
 * @param {HTMLElement} element - The element to animate
 * @param {number} finalValue - Target value
 */
function animateCounter(element, finalValue) {
  const currency = currencySymbol || '$';
  const duration = 1500;
  const startTime = performance.now();
  const formatter = new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  });

  function updateValue(currentTime) {
    const elapsedTime = currentTime - startTime;
    let progress = Math.min(elapsedTime / duration, 1);
    
    // Easing function for smoother animation
    progress = 1 - Math.pow(1 - progress, 3);
    
    const currentValue = progress * finalValue;
    element.textContent = currency + formatter.format(currentValue);
    
    if (progress < 1) {
      requestAnimationFrame(updateValue);
    }
  }
  
  requestAnimationFrame(updateValue);
}

/**
 * Initialize and animate progress bars
 */
function initializeProgressBars() {
  const progressBars = document.querySelectorAll('.progress-bar-fill');
  
  progressBars.forEach((bar, index) => {
    setTimeout(() => {
      const percentage = parseFloat(bar.getAttribute('data-percentage') || '0');
      bar.style.width = percentage + '%';
    }, 300 + (index * 50));
  });
}

/**
 * Initialize animations for dashboard elements
 */
function initializeAnimations() {
  const animatedElements = document.querySelectorAll('[data-aos]');
  
  animatedElements.forEach((element, index) => {
    const delay = element.getAttribute('data-aos-delay') || 0;
    
    setTimeout(() => {
      element.classList.add('aos-animate');
    }, parseInt(delay));
  });
}

/**
 * Initialize expense category chart
 */
function initializeChart() {
  const chartCanvas = document.getElementById('expenseCategoryChart');
  const chartLegendEl = document.getElementById('chartLegend');
  
  if (!chartCanvas) return;
  
  // Default configuration for Chart.js
  Chart.defaults.font.family = "'Inter', 'Noto Sans Thai', system-ui, sans-serif";
  Chart.defaults.color = '#4b5563';
  
  // Create the chart
  const chart = new Chart(chartCanvas, {
    type: 'doughnut',
    data: {
      labels: chartLabels || [],
      datasets: [{
        data: chartData || [],
        backgroundColor: chartColors || [],
        borderColor: '#ffffff',
        borderWidth: 3,
        hoverBorderWidth: 4,
        hoverOffset: 10,
        borderRadius: 3
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: '70%',
      animation: {
        animateScale: true,
        animateRotate: true,
        duration: 1000,
        easing: 'easeOutQuart'
      },
      plugins: {
        legend: {
          display: false // We'll create custom legend
        },
        tooltip: {
          backgroundColor: 'rgba(15, 23, 42, 0.8)',
          titleColor: '#ffffff',
          bodyColor: '#ffffff',
          padding: 12,
          cornerRadius: 8,
          boxPadding: 4,
          usePointStyle: true,
          callbacks: {
            label: function(context) {
              const label = context.label || '';
              const value = context.parsed;
              const total = context.dataset.data.reduce((a, b) => a + b, 0);
              const percentage = Math.round((value / total) * 100);
              return `${label}: ${currencySymbol}${value.toLocaleString()} (${percentage}%)`;
            }
          }
        }
      }
    }
  });
  
  // Create custom legend
  if (chartLegendEl && chartLabels && chartColors) {
    createCustomLegend(chartLegendEl, chart);
  }
  
  // Create center text
  createChartCenterText(chartCanvas);
  
  // Store chart in window for later reference
  window.expenseChart = chart;
}

/**
 * Create a custom legend for the chart
 * @param {HTMLElement} container - Legend container element
 * @param {Object} chart - Chart.js instance 
 */
function createCustomLegend(container, chart) {
  const legendItems = chart.data.labels.map((label, index) => {
    const color = chart.data.datasets[0].backgroundColor[index];
    const value = chart.data.datasets[0].data[index];
    return { label, color, value };
  });
  
  // Clear container
  container.innerHTML = '';
  
  // Create legend items
  legendItems.forEach((item) => {
    const legendItem = document.createElement('div');
    legendItem.className = 'legend-item';
    
    const colorBox = document.createElement('span');
    colorBox.className = 'legend-color';
    colorBox.style.backgroundColor = item.color;
    
    const text = document.createElement('span');
    text.textContent = truncateText(item.label, 15);
    
    legendItem.appendChild(colorBox);
    legendItem.appendChild(text);
    container.appendChild(legendItem);
    
    // Add hover interaction
    legendItem.addEventListener('mouseover', () => {
      highlightChartSegment(chart, item.label);
    });
    
    legendItem.addEventListener('mouseout', () => {
      resetChartHighlight(chart);
    });
  });
}

/**
 * Create center text display for the doughnut chart
 * @param {HTMLCanvasElement} canvas - Chart canvas element
 */
function createChartCenterText(canvas) {
  const totalValue = chartData.reduce((sum, value) => sum + value, 0);
  
  const centerContainer = document.createElement('div');
  centerContainer.style.position = 'absolute';
  centerContainer.style.top = '50%';
  centerContainer.style.left = '50%';
  centerContainer.style.transform = 'translate(-50%, -50%)';
  centerContainer.style.textAlign = 'center';
  centerContainer.style.pointerEvents = 'none';
  
  const valueText = document.createElement('div');
  valueText.textContent = currencySymbol + totalValue.toLocaleString();
  valueText.style.fontSize = '1.25rem';
  valueText.style.fontWeight = '700';
  valueText.style.color = '#1f2937';
  
  const labelText = document.createElement('div');
  labelText.textContent = 'Total';
  labelText.style.fontSize = '0.75rem';
  labelText.style.color = '#6b7280';
  
  centerContainer.appendChild(valueText);
  centerContainer.appendChild(labelText);
  
  // Add to parent container
  const parent = canvas.parentNode;
  if (parent) {
    parent.style.position = 'relative';
    parent.appendChild(centerContainer);
  }
}

/**
 * Highlight a specific segment in the chart
 * @param {Object} chart - Chart.js instance
 * @param {string} label - Segment label to highlight
 */
function highlightChartSegment(chart, label) {
  const index = chart.data.labels.indexOf(label);
  if (index === -1) return;
  
  chart.setActiveElements([{
    datasetIndex: 0,
    index: index
  }]);
  chart.update();
}

/**
 * Reset chart highlighting
 * @param {Object} chart - Chart.js instance
 */
function resetChartHighlight(chart) {
  chart.setActiveElements([]);
  chart.update();
}

/**
 * Initialize all event listeners
 */
function initializeEventListeners() {
  // Refresh dashboard button
  const refreshButton = document.getElementById('refreshDashboard');
  if (refreshButton) {
    refreshButton.addEventListener('click', () => {
      showLoading('Refreshing dashboard...');
      showToast('Refreshing dashboard data...', 'info');
      
      setTimeout(() => {
        hideLoading();
        location.reload();
      }, 1000);
    });
  }
  
  // Print dashboard button
  const printButton = document.getElementById('printDashboard');
  if (printButton) {
    printButton.addEventListener('click', () => {
      showToast('Preparing to print...', 'info');
      setTimeout(() => {
        window.print();
      }, 300);
    });
  }
  
  // Period selection for chart
  const periodSelect = document.getElementById('chartPeriodSelect');
  if (periodSelect) {
    periodSelect.addEventListener('change', () => {
      updateChartPeriod(periodSelect.value);
    });
  }
  
  // Generate advice buttons
  const generateAdviceBtn = document.getElementById('generateAdvice');
  const generateAdviceEmptyBtn = document.getElementById('generateAdviceEmpty');
  
  const handleGenerateAdvice = (button) => {
    button.disabled = true;
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
    
    showToast('Generating financial advice...', 'info');
    
    setTimeout(() => {
      window.location.href = `${basePath}/dashboard?generate_advice=true`;
    }, 800);
  };
  
  if (generateAdviceBtn) {
    generateAdviceBtn.addEventListener('click', () => handleGenerateAdvice(generateAdviceBtn));
  }
  
  if (generateAdviceEmptyBtn) {
    generateAdviceEmptyBtn.addEventListener('click', () => handleGenerateAdvice(generateAdviceEmptyBtn));
  }
  
  // Category item hover effect for chart highlighting
  const categoryItems = document.querySelectorAll('.category-item');
  categoryItems.forEach(item => {
    const categoryName = item.querySelector('h4').textContent.trim();
    
    item.addEventListener('mouseenter', () => {
      if (window.expenseChart) {
        highlightChartSegment(window.expenseChart, categoryName);
      }
    });
    
    item.addEventListener('mouseleave', () => {
      if (window.expenseChart) {
        resetChartHighlight(window.expenseChart);
      }
    });
  });
}

/**
 * Update chart data based on selected period
 * @param {string} period - Selected time period
 */
function updateChartPeriod(period) {
  showLoading(`Loading ${getPeriodLabel(period)} data...`);
  
  // Fetch data from server
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
    if (window.expenseChart && data.labels && data.data) {
      // Update chart with new data
      window.expenseChart.data.labels = data.labels;
      window.expenseChart.data.datasets[0].data = data.data;
      window.expenseChart.update();
      
      // Update center text
      updateChartCenterText(data.data);
      
      // Update legend
      const chartLegendEl = document.getElementById('chartLegend');
      if (chartLegendEl) {
        createCustomLegend(chartLegendEl, window.expenseChart);
      }
      
      showToast(`Displaying ${getPeriodLabel(period)} expense data`, 'success');
    }
    hideLoading();
  })
  .catch(error => {
    console.error('Error fetching chart data:', error);
    
    // Fallback to client-side simulation
    simulateChartUpdate(period);
    
    showToast(`Could not load data. Using simulated values instead.`, 'warning');
    hideLoading();
  });
}

/**
 * Update the center text of the chart
 * @param {Array} data - New chart data array
 */
function updateChartCenterText(data) {
  const chartCanvas = document.getElementById('expenseCategoryChart');
  if (!chartCanvas) return;
  
  const parent = chartCanvas.parentNode;
  const centerText = parent.querySelector('div');
  if (centerText) {
    const valueText = centerText.querySelector('div');
    if (valueText) {
      const total = data.reduce((sum, value) => sum + value, 0);
      valueText.textContent = currencySymbol + total.toLocaleString();
    }
  }
}

/**
 * Simulate chart data update for demo purposes
 * @param {string} period - Selected time period
 */
function simulateChartUpdate(period) {
  if (!window.expenseChart) return;
  
  const currentData = window.expenseChart.data.datasets[0].data;
  let newData = [];
  
  switch(period) {
    case 'last-month':
      newData = currentData.map(val => Math.round(val * (0.8 + Math.random() * 0.4)));
      break;
    case 'last-3-months':
      newData = currentData.map(val => Math.round(val * (2.2 + Math.random() * 0.6)));
      break;
    case 'current-year':
      newData = currentData.map(val => Math.round(val * (7 + Math.random() * 3)));
      break;
    case 'all-time':
      newData = currentData.map(val => Math.round(val * (10 + Math.random() * 5)));
      break;
    default:
      newData = currentData;
  }
  
  window.expenseChart.data.datasets[0].data = newData;
  window.expenseChart.update();
  
  // Update center text
  updateChartCenterText(newData);
  
  // Update category progress bars
  updateCategoryBars(newData);
}

/**
 * Update category progress bars with new data
 * @param {Array} data - New chart data array
 */
function updateCategoryBars(data) {
  const totalValue = data.reduce((sum, value) => sum + value, 0);
  const categoryItems = document.querySelectorAll('.category-item');
  
  categoryItems.forEach((item, index) => {
    if (index < data.length) {
      const percentage = (data[index] / totalValue) * 100;
      const progressBar = item.querySelector('.progress-bar-fill');
      const amount = item.querySelector('.amount');
      
      if (progressBar) {
        progressBar.style.width = percentage + '%';
      }
      
      if (amount) {
        amount.textContent = currencySymbol + data[index].toLocaleString();
      }
    }
  });
}

/**
 * Show loading overlay
 * @param {string} message - Loading message
 */
function showLoading(message = 'Loading...') {
  const overlay = document.getElementById('loading-overlay');
  if (overlay) {
    const messageEl = overlay.querySelector('.spinner-text');
    if (messageEl) {
      messageEl.textContent = message;
    }
    
    overlay.classList.remove('hidden');
  }
}

/**
 * Hide loading overlay
 */
function hideLoading() {
  const overlay = document.getElementById('loading-overlay');
  if (overlay) {
    overlay.classList.add('hidden');
  }
}

/**
 * Show toast notification
 * @param {string} message - Notification message
 * @param {string} type - Notification type (info, success, warning, error)
 * @param {number} duration - Duration in milliseconds
 */
function showToast(message, type = 'info', duration = 3000) {
  const container = document.getElementById('toast-container');
  if (!container) return;
  
  const toast = document.createElement('div');
  toast.className = `toast ${type}`;
  
  let iconClass = 'info-circle';
  if (type === 'success') iconClass = 'check-circle';
  if (type === 'warning') iconClass = 'exclamation-triangle';
  if (type === 'error') iconClass = 'exclamation-circle';
  
  toast.innerHTML = `<i class="fas fa-${iconClass}"></i><span>${message}</span>`;
  container.appendChild(toast);
  
  // Show the toast
  setTimeout(() => {
    toast.classList.add('show');
  }, 10);
  
  // Remove after duration
  setTimeout(() => {
    toast.classList.remove('show');
    
    // Remove from DOM after animation
    setTimeout(() => {
      if (container.contains(toast)) {
        container.removeChild(toast);
      }
    }, 300);
  }, duration);
}

/**
 * Get readable period label
 * @param {string} period - Period identifier
 * @return {string} Readable period label
 */
function getPeriodLabel(period) {
  const labels = {
    'current-month': 'current month',
    'last-month': 'last month',
    'last-3-months': 'last 3 months',
    'current-year': 'this year',
    'all-time': 'all-time'
  };
  
  return labels[period] || period;
}

/**
 * Truncate text with ellipsis if too long
 * @param {string} text - Text to truncate
 * @param {number} maxLength - Maximum length
 * @return {string} Truncated text
 */
function truncateText(text, maxLength) {
  if (text.length <= maxLength) return text;
  return text.substring(0, maxLength) + '...';
}