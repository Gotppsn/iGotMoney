/**
 * Modern Expenses Page JavaScript
 * iGotMoney Application
 */

document.addEventListener('DOMContentLoaded', function() {
  initializeElements();
  initializeEventListeners();
  initializeChart();
  initializeTopCategories();
  initializeValidation();
  animateElements();
});

/**
 * Initialize and prepare all elements on the page
 */
function initializeElements() {
  // Hide the filters panel by default
  const filtersPanel = document.getElementById('filtersPanel');
  if (filtersPanel) {
    filtersPanel.classList.remove('active');
  }
  
  // Hide bulk actions panel
  const bulkActions = document.getElementById('bulkActions');
  if (bulkActions) {
    bulkActions.classList.remove('active');
  }

  // Generate quick filter pills
  generateQuickFilters();
}

/**
 * Add all event listeners to interactive elements
 */
function initializeEventListeners() {
  // Toggle filters panel
  const filterToggle = document.getElementById('filterToggle');
  if (filterToggle) {
    filterToggle.addEventListener('click', function() {
      const filtersPanel = document.getElementById('filtersPanel');
      if (filtersPanel) {
        filtersPanel.classList.toggle('active');
        this.classList.toggle('active');
      }
    });
  }
  
  // Search functionality
  const searchInput = document.getElementById('expenseSearch');
  if (searchInput) {
    searchInput.addEventListener('input', function() {
      filterTable(this.value);
    });
  }
  
  // Clear search button
  const clearSearchBtn = document.getElementById('clearSearch');
  if (clearSearchBtn) {
    clearSearchBtn.addEventListener('click', function() {
      const searchInput = document.getElementById('expenseSearch');
      if (searchInput) {
        searchInput.value = '';
        filterTable('');
      }
    });
  }
  
  // Select all checkboxes
  const selectAllCheckbox = document.getElementById('selectAll');
  if (selectAllCheckbox) {
    selectAllCheckbox.addEventListener('change', function() {
      const checkboxes = document.querySelectorAll('.expense-checkbox');
      checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
      });
      updateBulkActions();
    });
  }
  
  // Individual checkboxes
  const checkboxes = document.querySelectorAll('.expense-checkbox');
  checkboxes.forEach(checkbox => {
    checkbox.addEventListener('change', updateBulkActions);
  });
  
  // Reset filters button
  const resetFilterBtn = document.getElementById('resetFilter');
  if (resetFilterBtn) {
    resetFilterBtn.addEventListener('click', function(e) {
      e.preventDefault();
      const basePath = document.querySelector('meta[name="base-path"]').getAttribute('content');
      window.location.href = `${basePath}/expenses`;
    });
  }
  
  // Chart period selection
  const chartPeriodSelect = document.getElementById('chartPeriodSelect');
  if (chartPeriodSelect) {
    chartPeriodSelect.addEventListener('change', function() {
      updateChartData(this.value);
    });
  }
  
  // Bulk delete button
  const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
  if (bulkDeleteBtn) {
    bulkDeleteBtn.addEventListener('click', function() {
      const selectedIds = getSelectedExpenseIds();
      if (selectedIds.length > 0) {
        if (confirm(`Are you sure you want to delete ${selectedIds.length} expense(s)?`)) {
          // Here you would normally implement the AJAX call to delete the selected expenses
          // For now, we'll just show a notification
          showNotification('Success', `${selectedIds.length} expenses have been deleted.`, 'success');
          
          // Refresh the page after deletion
          setTimeout(() => {
            window.location.reload();
          }, 1500);
        }
      }
    });
  }
  
  // Bulk category change button
  const bulkCategoryBtn = document.getElementById('bulkCategoryBtn');
  if (bulkCategoryBtn) {
    bulkCategoryBtn.addEventListener('click', function() {
      const selectedIds = getSelectedExpenseIds();
      if (selectedIds.length > 0) {
        // Here you would normally open a modal to select the new category
        // For now, we'll just show a notification
        showNotification('Info', `Select a new category for ${selectedIds.length} expenses.`, 'info');
      }
    });
  }
  
  // Add expense form - toggle recurring options
  const recurringCheckbox = document.getElementById('is_recurring');
  if (recurringCheckbox) {
    recurringCheckbox.addEventListener('change', function() {
      const frequencyField = document.getElementById('frequency');
      if (frequencyField) {
        frequencyField.disabled = !this.checked;
        
        if (!this.checked) {
          frequencyField.value = 'one-time';
        } else {
          frequencyField.value = 'monthly'; // Default to monthly for recurring
        }
      }
    });
  }
  
  // Edit expense form - toggle recurring options
  const editRecurringCheckbox = document.getElementById('edit_is_recurring');
  if (editRecurringCheckbox) {
    editRecurringCheckbox.addEventListener('change', function() {
      const frequencyField = document.getElementById('edit_frequency');
      if (frequencyField) {
        frequencyField.disabled = !this.checked;
        
        if (!this.checked) {
          frequencyField.value = 'one-time';
        } else if (frequencyField.value === 'one-time') {
          frequencyField.value = 'monthly'; // Default to monthly for recurring
        }
      }
    });
  }
  
  // Action buttons (edit, delete, duplicate)
  document.addEventListener('click', function(e) {
    // Edit button
    if (e.target.closest('.btn-action.edit')) {
      e.preventDefault();
      const button = e.target.closest('.btn-action.edit');
      const expenseId = button.getAttribute('data-expense-id');
      if (expenseId) {
        loadExpenseForEdit(expenseId);
      }
    }
    
    // Delete button
    if (e.target.closest('.btn-action.delete')) {
      e.preventDefault();
      const button = e.target.closest('.btn-action.delete');
      const expenseId = button.getAttribute('data-expense-id');
      if (expenseId) {
        const row = button.closest('tr');
        document.getElementById('delete_expense_id').value = expenseId;
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteExpenseModal'));
        deleteModal.show();
      }
    }
    
    // Duplicate button
    if (e.target.closest('.btn-action.duplicate')) {
      e.preventDefault();
      const button = e.target.closest('.btn-action.duplicate');
      const expenseId = button.getAttribute('data-expense-id');
      if (expenseId) {
        duplicateExpense(expenseId);
      }
    }
  });
  
  // Filter pills
  document.addEventListener('click', function(e) {
    if (e.target.closest('.filter-pill')) {
      const pill = e.target.closest('.filter-pill');
      const period = pill.getAttribute('data-period');
      
      // Remove active class from all pills
      document.querySelectorAll('.filter-pill').forEach(p => {
        p.classList.remove('active');
      });
      
      // Add active class to clicked pill
      pill.classList.add('active');
      
      // Update chart for the selected period
      updateChartData(period);
      
      // Update filter form if needed
      updateFilterFormFromPill(period);
    }
  });
}

/**
 * Generate quick filter pills
 */
function generateQuickFilters() {
  const container = document.getElementById('quickFilters');
  if (!container) return;
  
  const filters = [
    { label: 'This Month', period: 'current-month', icon: 'calendar-day' },
    { label: 'Last Month', period: 'last-month', icon: 'calendar-week' },
    { label: 'Last 3 Months', period: 'last-3-months', icon: 'calendar-alt' },
    { label: 'This Year', period: 'current-year', icon: 'calendar' },
    { label: 'All Time', period: 'all', icon: 'infinity' }
  ];
  
  let html = '';
  filters.forEach((filter, index) => {
    const isActive = index === 0 ? 'active' : '';
    html += `
      <div class="filter-pill ${isActive}" data-period="${filter.period}">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          ${getSvgPath(filter.icon)}
        </svg>
        <span>${filter.label}</span>
      </div>
    `;
  });
  
  container.innerHTML = html;
}

/**
 * Helper function to get SVG path for icons
 */
function getSvgPath(icon) {
  switch (icon) {
    case 'calendar-day':
      return '<rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line><circle cx="12" cy="16" r="2"></circle>';
    case 'calendar-week':
      return '<rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line><line x1="8" y1="14" x2="16" y2="14"></line>';
    case 'calendar-alt':
      return '<rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line>';
    case 'calendar':
      return '<rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line>';
    case 'infinity':
      return '<path d="M18.178 8c5.096 0 5.096 8 0 8-5.095 0-7.133-8-12.739-8-4.585 0-4.585 8 0 8 5.606 0 7.644-8 12.74-8z"></path>';
    default:
      return '';
  }
}

/**
 * Initialize Chart.js chart
 */
function initializeChart() {
  const chartCanvas = document.getElementById('expenseChart');
  if (!chartCanvas) return;
  
  const chartLabelsEl = document.querySelector('meta[name="chart-labels"]');
  const chartDataEl = document.querySelector('meta[name="chart-data"]');
  const chartColorsEl = document.querySelector('meta[name="chart-colors"]');
  const currencySymbolEl = document.querySelector('meta[name="currency-symbol"]');
  
  if (!chartLabelsEl || !chartDataEl || !chartColorsEl) {
    console.warn('Chart data meta tags not found');
    return;
  }
  
  try {
    const chartLabels = JSON.parse(chartLabelsEl.getAttribute('content') || '[]');
    const chartData = JSON.parse(chartDataEl.getAttribute('content') || '[]');
    const chartColors = JSON.parse(chartColorsEl.getAttribute('content') || '[]');
    const currencySymbol = currencySymbolEl ? currencySymbolEl.getAttribute('content') : '$';
    
    if (chartLabels.length === 0 || chartData.length === 0) {
      console.warn('No chart data available');
      return;
    }
    
    // Set up Chart.js
    Chart.defaults.font.family = "'Inter', 'Noto Sans Thai', system-ui, sans-serif";
    Chart.defaults.font.size = 14;
    
    window.expenseChart = new Chart(chartCanvas, {
      type: 'doughnut',
      data: {
        labels: chartLabels,
        datasets: [{
          data: chartData,
          backgroundColor: chartColors,
          borderColor: 'white',
          borderWidth: 2,
          hoverOffset: 15,
          borderRadius: 3
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '70%',
        plugins: {
          legend: {
            position: 'bottom',
            labels: {
              usePointStyle: true,
              padding: 20,
              font: {
                size: 12
              }
            }
          },
          tooltip: {
            callbacks: {
              label: function(context) {
                const label = context.label || '';
                const value = context.parsed;
                const total = context.dataset.data.reduce((sum, val) => sum + val, 0);
                const percentage = ((value / total) * 100).toFixed(1);
                return `${label}: ${currencySymbol}${value.toLocaleString()} (${percentage}%)`;
              }
            }
          }
        }
      },
      plugins: [{
        id: 'centerText',
        beforeDraw: function(chart) {
          const width = chart.width;
          const height = chart.height;
          const ctx = chart.ctx;
          
          ctx.restore();
          
          // Total value
          const total = chart.data.datasets[0].data.reduce((sum, val) => sum + val, 0);
          
          // Draw total
          const fontSize = 24;
          ctx.font = `bold ${fontSize}px ${Chart.defaults.font.family}`;
          ctx.textAlign = 'center';
          ctx.fillStyle = '#111827';
          ctx.fillText(currencySymbol + total.toLocaleString(), width / 2, height / 2 - 5);
          
          // Draw label
          const labelFontSize = 14;
          ctx.font = `${labelFontSize}px ${Chart.defaults.font.family}`;
          ctx.fillStyle = '#6b7280';
          ctx.fillText('Total Expenses', width / 2, height / 2 + 15);
          
          ctx.save();
        }
      }]
    });
    
    // Hide no data message if we have data
    const noDataMessage = document.getElementById('chartNoData');
    if (noDataMessage) {
      noDataMessage.style.display = 'none';
    }
    
  } catch (error) {
    console.error('Error initializing chart:', error);
  }
}

/**
 * Initialize top categories
 */
function initializeTopCategories() {
  const topCategoriesContainer = document.querySelector('.top-categories');
  if (!topCategoriesContainer) return;
  
  const chartLabelsEl = document.querySelector('meta[name="chart-labels"]');
  const chartDataEl = document.querySelector('meta[name="chart-data"]');
  const currencySymbolEl = document.querySelector('meta[name="currency-symbol"]');
  
  if (!chartLabelsEl || !chartDataEl) {
    console.warn('Chart data meta tags not found for top categories');
    return;
  }
  
  try {
    const chartLabels = JSON.parse(chartLabelsEl.getAttribute('content') || '[]');
    const chartData = JSON.parse(chartDataEl.getAttribute('content') || '[]');
    const currencySymbol = currencySymbolEl ? currencySymbolEl.getAttribute('content') : '$';
    
    if (chartLabels.length === 0 || chartData.length === 0) {
      // Empty state already handled in PHP
      return;
    }
    
    // Calculate total
    const total = chartData.reduce((sum, val) => sum + val, 0);
    
    // Create items array to sort
    const items = chartLabels.map((label, i) => ({
      label,
      value: chartData[i]
    }));
    
    // Sort by value (highest first)
    items.sort((a, b) => b.value - a.value);
    
    // Generate HTML for top 5 (or fewer if we have less)
    let html = '';
    const maxItems = Math.min(5, items.length);
    
    for (let i = 0; i < maxItems; i++) {
      const item = items[i];
      const percentage = ((item.value / total) * 100).toFixed(1);
      
      html += `
        <div class="category-item">
          <div class="category-rank">${i + 1}</div>
          <div class="category-info">
            <div class="category-name">${item.label}</div>
            <div class="category-progress">
              <div class="progress-bar" data-percentage="${percentage}"></div>
            </div>
          </div>
          <div class="category-amount">
            <div class="amount-value">${currencySymbol}${item.value.toLocaleString()}</div>
            <div class="amount-percentage">${percentage}%</div>
          </div>
        </div>
      `;
    }
    
    topCategoriesContainer.innerHTML = html;
    
    // Animate progress bars after a delay
    setTimeout(() => {
      const progressBars = topCategoriesContainer.querySelectorAll('.progress-bar');
      progressBars.forEach(bar => {
        const percentage = bar.getAttribute('data-percentage');
        bar.style.width = `${percentage}%`;
      });
    }, 300);
    
  } catch (error) {
    console.error('Error initializing top categories:', error);
  }
}

/**
 * Initialize form validation
 */
function initializeValidation() {
  // Add expense form validation
  const addExpenseForm = document.getElementById('addExpenseForm');
  if (addExpenseForm) {
    addExpenseForm.addEventListener('submit', function(event) {
      if (!this.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
      }
      
      this.classList.add('was-validated');
    });
  }
  
  // Edit expense form validation
  const editExpenseForm = document.getElementById('editExpenseForm');
  if (editExpenseForm) {
    editExpenseForm.addEventListener('submit', function(event) {
      if (!this.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
      }
      
      this.classList.add('was-validated');
    });
  }
}

/**
 * Animate elements on page load
 */
function animateElements() {
  // Add fade-in classes with slight delays
  const statCards = document.querySelectorAll('.stat-card');
  statCards.forEach((card, index) => {
    card.classList.add(`fade-in-delay-${index % 3}`);
  });
  
  // Add fade-in to main content
  const cards = document.querySelectorAll('.card');
  cards.forEach((card, index) => {
    card.classList.add(`fade-in-delay-${1 + (index % 2)}`);
  });
}

/**
 * Filter table based on search input
 */
function filterTable(searchTerm) {
  const tableRows = document.querySelectorAll('#expensesTable tbody tr');
  const noDataMessage = document.getElementById('tableNoData');
  const noResultsMessage = document.getElementById('searchNoResults');
  const tableContainer = document.querySelector('.table-container');
  
  searchTerm = searchTerm.toLowerCase();
  let visibleRows = 0;
  
  tableRows.forEach(row => {
    const text = row.textContent.toLowerCase();
    const isVisible = text.includes(searchTerm);
    
    row.style.display = isVisible ? '' : 'none';
    
    if (isVisible) {
      visibleRows++;
    }
  });
  
  // Show/hide appropriate messages
  if (tableRows.length === 0) {
    // No data at all
    if (noDataMessage) noDataMessage.style.display = 'block';
    if (noResultsMessage) noResultsMessage.style.display = 'none';
    if (tableContainer) tableContainer.style.display = 'none';
    
  } else if (visibleRows === 0) {
    // No matching results
    if (noDataMessage) noDataMessage.style.display = 'none';
    if (noResultsMessage) noResultsMessage.style.display = 'block';
    if (tableContainer) tableContainer.style.display = 'none';
    
  } else {
    // Has matching results
    if (noDataMessage) noDataMessage.style.display = 'none';
    if (noResultsMessage) noResultsMessage.style.display = 'none';
    if (tableContainer) tableContainer.style.display = 'block';
  }
}

/**
 * Update the chart data for a specific period
 */
function updateChartData(period) {
  const chartContainer = document.querySelector('.chart-container');
  if (chartContainer) {
    chartContainer.style.opacity = '0.5';
  }
  
  // Update chart period title
  const chartPeriodTitle = document.getElementById('chartPeriodTitle');
  if (chartPeriodTitle) {
    let title;
    switch (period) {
      case 'current-month': title = 'This Month'; break;
      case 'last-month': title = 'Last Month'; break;
      case 'last-3-months': title = 'Last 3 Months'; break;
      case 'current-year': title = 'This Year'; break;
      case 'all': title = 'All Time'; break;
      default: title = 'Expenses';
    }
    chartPeriodTitle.textContent = title;
  }
  
  // Set chart period select to match
  const chartPeriodSelect = document.getElementById('chartPeriodSelect');
  if (chartPeriodSelect) {
    chartPeriodSelect.value = period;
  }
  
  // Get the base path from meta tag
  const basePath = document.querySelector('meta[name="base-path"]').getAttribute('content');
  
  // Make AJAX request to get new data
  fetch(`${basePath}/expenses?action=get_expense_analytics&period=${period}`, {
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
      'Accept': 'application/json'
    }
  })
  .then(response => {
    if (!response.ok) {
      throw new Error('Network response was not ok');
    }
    return response.json();
  })
  .then(data => {
    if (data.success && data.analytics && data.analytics.category_totals) {
      updateChartWithNewData(data.analytics);
    } else {
      console.warn('No chart data available for the selected period');
      simulateChartUpdate(period);
    }
    
    if (chartContainer) {
      chartContainer.style.opacity = '1';
    }
  })
  .catch(error => {
    console.error('Error fetching chart data:', error);
    // Fallback to simulation
    simulateChartUpdate(period);
    
    if (chartContainer) {
      chartContainer.style.opacity = '1';
    }
  });
}

/**
 * Update chart with new data
 */
function updateChartWithNewData(analytics) {
  if (!window.expenseChart) return;
  
  const currencySymbolEl = document.querySelector('meta[name="currency-symbol"]');
  const currencySymbol = currencySymbolEl ? currencySymbolEl.getAttribute('content') : '$';
  
  const categoryTotals = analytics.category_totals;
  const totalExpenses = analytics.total_amount || Object.values(categoryTotals).reduce((sum, val) => sum + val, 0);
  
  // Create sorted array of categories
  const sortedCategories = Object.entries(categoryTotals)
    .sort((a, b) => b[1] - a[1])
    .slice(0, 10); // Limit to top 10
  
  const labels = sortedCategories.map(cat => cat[0]);
  const values = sortedCategories.map(cat => cat[1]);
  
  // Default colors
  const colors = [
    '#6366f1', '#8b5cf6', '#ec4899', '#ef4444', '#f59e0b',
    '#10b981', '#14b8a6', '#06b6d4', '#3b82f6', '#4f46e5'
  ].slice(0, values.length);
  
  // Update chart data
  window.expenseChart.data.labels = labels;
  window.expenseChart.data.datasets[0].data = values;
  window.expenseChart.data.datasets[0].backgroundColor = colors;
  window.expenseChart.update();
  
  // Also update top categories
  updateTopCategories(sortedCategories, totalExpenses, currencySymbol);
}

/**
 * Update top categories list with new data
 */
function updateTopCategories(categories, total, currencySymbol) {
  const topCategoriesContainer = document.querySelector('.top-categories');
  if (!topCategoriesContainer) return;
  
  // Generate HTML for top 5 (or fewer if we have less)
  let html = '';
  const maxItems = Math.min(5, categories.length);
  
  for (let i = 0; i < maxItems; i++) {
    const category = categories[i];
    const percentage = ((category[1] / total) * 100).toFixed(1);
    
    html += `
      <div class="category-item">
        <div class="category-rank">${i + 1}</div>
        <div class="category-info">
          <div class="category-name">${category[0]}</div>
          <div class="category-progress">
            <div class="progress-bar" data-percentage="${percentage}"></div>
          </div>
        </div>
        <div class="category-amount">
          <div class="amount-value">${currencySymbol}${category[1].toLocaleString()}</div>
          <div class="amount-percentage">${percentage}%</div>
        </div>
      </div>
    `;
  }
  
  topCategoriesContainer.innerHTML = html;
  
  // Animate progress bars after a delay
  setTimeout(() => {
    const progressBars = topCategoriesContainer.querySelectorAll('.progress-bar');
    progressBars.forEach(bar => {
      const percentage = bar.getAttribute('data-percentage');
      bar.style.width = `${percentage}%`;
    });
  }, 100);
}

/**
 * Simulate chart update if AJAX fails (for demo purposes)
 */
function simulateChartUpdate(period) {
  if (!window.expenseChart) return;
  
  const currencySymbolEl = document.querySelector('meta[name="currency-symbol"]');
  const currencySymbol = currencySymbolEl ? currencySymbolEl.getAttribute('content') : '$';
  
  // Get current data from chart
  const labels = [...window.expenseChart.data.labels];
  let data = [...window.expenseChart.data.datasets[0].data];
  
  // Adjust data based on period
  let factor = 1;
  switch (period) {
    case 'current-month': factor = 1; break;
    case 'last-month': factor = 0.9; break;
    case 'last-3-months': factor = 2.7; break;
    case 'current-year': factor = 11.5; break;
    case 'all': factor = 24; break;
  }
  
  // Add some randomness
  data = data.map(value => {
    const randomFactor = 0.8 + (Math.random() * 0.4); // Random between 0.8 and 1.2
    return Math.round(value * factor * randomFactor);
  });
  
  // Update chart
  window.expenseChart.data.datasets[0].data = data;
  window.expenseChart.update();
  
  // Calculate total for percentage calculations
  const total = data.reduce((sum, val) => sum + val, 0);
  
  // Create categories array for top categories update
  const categories = labels.map((label, i) => [label, data[i]]);
  categories.sort((a, b) => b[1] - a[1]);
  
  // Update top categories
  updateTopCategories(categories, total, currencySymbol);
}

/**
 * Update filter form based on selected pill
 */
function updateFilterFormFromPill(period) {
  const monthSelect = document.getElementById('monthSelect');
  const yearSelect = document.getElementById('yearSelect');
  
  if (!monthSelect || !yearSelect) return;
  
  const currentDate = new Date();
  const currentMonth = currentDate.getMonth() + 1; // JavaScript months are 0-indexed
  const currentYear = currentDate.getFullYear();
  
  switch (period) {
    case 'current-month':
      monthSelect.value = currentMonth;
      yearSelect.value = currentYear;
      break;
      
    case 'last-month':
      // Calculate last month (handle December to January transition)
      if (currentMonth === 1) {
        monthSelect.value = 12;
        yearSelect.value = currentYear - 1;
      } else {
        monthSelect.value = currentMonth - 1;
        yearSelect.value = currentYear;
      }
      break;
      
    case 'current-year':
      monthSelect.value = 0; // All months
      yearSelect.value = currentYear;
      break;
      
    case 'all':
      monthSelect.value = 0; // All months
      yearSelect.value = 0; // All years
      break;
      
    default:
      // Don't change the form
      break;
  }
}

/**
 * Load expense for editing
 */
function loadExpenseForEdit(expenseId) {
  const basePath = document.querySelector('meta[name="base-path"]').getAttribute('content');
  
  fetch(`${basePath}/expenses?action=get_expense&expense_id=${expenseId}`, {
    headers: {
      'X-Requested-With': 'XMLHttpRequest'
    }
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      // Fill the edit form with data
      document.getElementById('edit_expense_id').value = data.expense.expense_id;
      document.getElementById('edit_description').value = data.expense.description;
      document.getElementById('edit_category_id').value = data.expense.category_id;
      document.getElementById('edit_amount').value = data.expense.amount;
      document.getElementById('edit_expense_date').value = data.expense.expense_date;
      
      const isRecurring = data.expense.is_recurring === '1' || data.expense.is_recurring === 1;
      document.getElementById('edit_is_recurring').checked = isRecurring;
      
      const frequencyField = document.getElementById('edit_frequency');
      if (frequencyField) {
        frequencyField.value = data.expense.frequency;
        frequencyField.disabled = !isRecurring;
      }
      
      // Show modal
      const editModal = new bootstrap.Modal(document.getElementById('editExpenseModal'));
      editModal.show();
    } else {
      showNotification('Error', 'Failed to load expense data', 'error');
    }
  })
  .catch(error => {
    console.error('Error:', error);
    showNotification('Error', 'An error occurred while loading expense data', 'error');
  });
}

/**
 * Duplicate an expense
 */
function duplicateExpense(expenseId) {
  const basePath = document.querySelector('meta[name="base-path"]').getAttribute('content');
  
  fetch(`${basePath}/expenses?action=get_expense&expense_id=${expenseId}`, {
    headers: {
      'X-Requested-With': 'XMLHttpRequest'
    }
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      // Fill the add form with data
      document.getElementById('description').value = data.expense.description + ' (Copy)';
      document.getElementById('category_id').value = data.expense.category_id;
      document.getElementById('amount').value = data.expense.amount;
      document.getElementById('expense_date').value = new Date().toISOString().split('T')[0]; // Today's date
      
      const isRecurring = data.expense.is_recurring === '1' || data.expense.is_recurring === 1;
      document.getElementById('is_recurring').checked = isRecurring;
      
      const frequencyField = document.getElementById('frequency');
      if (frequencyField) {
        frequencyField.value = data.expense.frequency;
        frequencyField.disabled = !isRecurring;
      }
      
      // Show modal
      const addModal = new bootstrap.Modal(document.getElementById('addExpenseModal'));
      addModal.show();
    } else {
      showNotification('Error', 'Failed to duplicate expense', 'error');
    }
  })
  .catch(error => {
    console.error('Error:', error);
    showNotification('Error', 'An error occurred while duplicating expense', 'error');
  });
}

/**
 * Get selected expense IDs
 */
function getSelectedExpenseIds() {
  const checkboxes = document.querySelectorAll('.expense-checkbox:checked');
  return Array.from(checkboxes).map(checkbox => checkbox.value);
}

/**
 * Update bulk actions visibility
 */
function updateBulkActions() {
  const selectedIds = getSelectedExpenseIds();
  const bulkActions = document.getElementById('bulkActions');
  const countElement = document.querySelector('.selected-count .count');
  
  if (bulkActions && countElement) {
    if (selectedIds.length > 0) {
      bulkActions.classList.add('active');
      countElement.textContent = selectedIds.length;
    } else {
      bulkActions.classList.remove('active');
      countElement.textContent = '0';
    }
  }
  
  // Update select all checkbox
  const selectAllCheckbox = document.getElementById('selectAll');
  const allCheckboxes = document.querySelectorAll('.expense-checkbox');
  
  if (selectAllCheckbox && allCheckboxes.length > 0) {
    selectAllCheckbox.checked = selectedIds.length > 0 && selectedIds.length === allCheckboxes.length;
    selectAllCheckbox.indeterminate = selectedIds.length > 0 && selectedIds.length < allCheckboxes.length;
  }
}

/**
 * Show notification
 */
function showNotification(title, message, type = 'info') {
  const container = document.getElementById('notificationContainer');
  if (!container) return;
  
  const notification = document.createElement('div');
  notification.className = `notification ${type}`;
  
  // Get icon based on type
  let iconPath = '';
  switch (type) {
    case 'success':
      iconPath = '<polyline points="20 6 9 17 4 12"></polyline>';
      break;
    case 'error':
      iconPath = '<line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line>';
      break;
    case 'warning':
      iconPath = '<path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line>';
      break;
    default: // info
      iconPath = '<circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line>';
  }
  
  notification.innerHTML = `
    <div class="notification-icon">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">${iconPath}</svg>
    </div>
    <div class="notification-content">
      <div class="notification-title">${title}</div>
      <div class="notification-message">${message}</div>
    </div>
  `;
  
  container.appendChild(notification);
  
  // Show with animation
  setTimeout(() => {
    notification.classList.add('show');
  }, 10);
  
  // Auto-hide after delay
  setTimeout(() => {
    notification.classList.remove('show');
    setTimeout(() => {
      notification.remove();
    }, 300);
  }, 5000);
}