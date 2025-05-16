/**
 * Modern Budget Page JavaScript
 * iGotMoney Application
 */

document.addEventListener('DOMContentLoaded', function() {
  initializeAll();
});

/**
 * Initialize all components and features
 */
function initializeAll() {
  // Initialize chart
  initializeDonutChart();
  
  // Initialize event listeners
  setupCategoryItemListeners();
  setupTableRowListeners();
  setupFilterFunctionality();
  setupFormValidation();
  setupButtonListeners();
  setupModalListeners();
  setupTabNavigation();
  setupTipsToggle();
  setupInvestmentSlider();
  setupAdoptRecommendationButtons();
  setupPeriodSelector();
  
  // Apply animations
  animateProgressBars();
  
  // Check if chart and data are visible
  checkEmptyStates();
}

/**
 * Initialize the donut chart
 */
function initializeDonutChart() {
  const chartCanvas = document.getElementById('budgetDonutChart');
  if (!chartCanvas) return;
  
  // Get budget status data from the table
  const budgetRows = document.querySelectorAll('#budgetTable tbody tr');
  if (budgetRows.length === 0) return;
  
  const chartData = {
    labels: [],
    values: [],
    colors: [
      '#6366f1', // Primary
      '#64748b', // Secondary
      '#10b981', // Success
      '#f59e0b', // Warning
      '#3b82f6', // Info
      '#8b5cf6', // Purple
      '#ec4899', // Pink
      '#14b8a6', // Teal
      '#06b6d4', // Cyan
      '#84cc16', // Lime
    ]
  };
  
  // Get category names and spent amounts
  budgetRows.forEach((row, index) => {
    const categoryName = row.querySelector('.category-name span').textContent.trim();
    const spentAmountStr = row.getAttribute('data-spent');
    const spentAmount = parseFloat(spentAmountStr);
    
    if (spentAmount > 0) {
      chartData.labels.push(categoryName);
      chartData.values.push(spentAmount);
    }
  });
  
  // Limit to top 8 categories
  if (chartData.labels.length > 8) {
    // Find the total of the remaining categories
    let otherTotal = 0;
    for (let i = 8; i < chartData.values.length; i++) {
      otherTotal += chartData.values[i];
    }
    
    // Replace with "Other" category
    chartData.labels.splice(8);
    chartData.values.splice(8);
    chartData.labels.push('Other');
    chartData.values.push(otherTotal);
  }
  
  // Configure limited colors
  const chartColors = chartData.colors.slice(0, chartData.labels.length);
  
  // Create donut chart
  window.budgetDonutChart = new Chart(chartCanvas, {
    type: 'doughnut',
    data: {
      labels: chartData.labels,
      datasets: [{
        data: chartData.values,
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
          position: 'right',
          labels: {
            usePointStyle: true,
            padding: 15,
            font: {
              size: 11
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
              const currencySymbol = getCurrencySymbol();
              return `${label}: ${currencySymbol}${value.toLocaleString()} (${percentage}%)`;
            }
          }
        }
      }
    }
  });
}

/**
 * Get currency symbol from meta tag
 */
function getCurrencySymbol() {
  const currencySymbolMeta = document.querySelector('meta[name="currency-symbol"]');
  return currencySymbolMeta ? currencySymbolMeta.getAttribute('content') : '$';
}

/**
 * Get translation from meta tag or default
 */
function getTranslation(key, defaultValue = '') {
  const metaTag = document.querySelector(`meta[name="${key}"]`);
  return metaTag ? metaTag.getAttribute('content') : defaultValue;
}

/**
 * Animate progress bars after page load
 */
function animateProgressBars() {
  // Animate overview card progress bars
  const overviewProgressFills = document.querySelectorAll('.overview-card .progress-fill');
  animateElements(overviewProgressFills, 300);
  
  // Animate category item progress bars
  const categoryProgressFills = document.querySelectorAll('.category-item .progress-fill');
  animateElements(categoryProgressFills, 500);
  
  // Animate table progress bars
  const tableProgressFills = document.querySelectorAll('.budget-table .progress-fill');
  animateElements(tableProgressFills, 800);
}

/**
 * Animate elements with delay
 */
function animateElements(elements, baseDelay = 0) {
  elements.forEach((element, index) => {
    const delay = baseDelay + (index * 50);
    setTimeout(() => {
      element.style.width = element.getAttribute('style').replace('width: 0%', `width: ${element.style.width}`);
    }, delay);
  });
}

/**
 * Check and handle empty states
 */
function checkEmptyStates() {
  // Check if budget table is empty
  const budgetTable = document.getElementById('budgetTable');
  const noMatchingBudgets = document.getElementById('noMatchingBudgets');
  
  if (budgetTable && noMatchingBudgets) {
    const tableRows = budgetTable.querySelectorAll('tbody tr');
    if (tableRows.length === 0) {
      budgetTable.style.display = 'none';
      noMatchingBudgets.style.display = 'block';
    }
  }
}

/**
 * Setup category item click listeners
 */
function setupCategoryItemListeners() {
  const categoryItems = document.querySelectorAll('.category-item');
  categoryItems.forEach(item => {
    // Category click opens details modal
    item.addEventListener('click', function(e) {
      if (!e.target.closest('.category-edit')) {
        const categoryId = this.getAttribute('data-category-id');
        const budgetId = this.getAttribute('data-budget-id');
        if (categoryId && budgetId) {
          openCategoryDetails(budgetId, categoryId);
        }
      }
    });
    
    // Edit button click
    const editBtn = item.querySelector('.category-edit');
    if (editBtn) {
      editBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        const budgetId = this.getAttribute('data-budget-id');
        if (budgetId) {
          openEditBudgetModal(budgetId);
        }
      });
    }
  });
}

/**
 * Setup table row click listeners
 */
function setupTableRowListeners() {
  const tableRows = document.querySelectorAll('#budgetTable tbody tr');
  tableRows.forEach(row => {
    // Row click opens details modal
    row.addEventListener('click', function(e) {
      if (!e.target.closest('.action-btn')) {
        const categoryId = this.getAttribute('data-category-id');
        const budgetId = this.getAttribute('data-budget-id');
        if (categoryId && budgetId) {
          openCategoryDetails(budgetId, categoryId);
        }
      }
    });
    
    // Edit button click
    const editBtn = row.querySelector('.action-btn.edit');
    if (editBtn) {
      editBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        const budgetId = this.getAttribute('data-budget-id');
        if (budgetId) {
          openEditBudgetModal(budgetId);
        }
      });
    }
    
    // Delete button click
    const deleteBtn = row.querySelector('.action-btn.delete');
    if (deleteBtn) {
      deleteBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        const budgetId = this.getAttribute('data-budget-id');
        if (budgetId) {
          openDeleteBudgetModal(budgetId);
        }
      });
    }
  });
}

/**
 * Setup filter functionality
 */
function setupFilterFunctionality() {
  // Toggle filter menu
  const filterToggle = document.getElementById('filterToggle');
  const filterMenu = document.getElementById('filterMenu');
  
  if (filterToggle && filterMenu) {
    filterToggle.addEventListener('click', function() {
      filterMenu.classList.toggle('show');
    });
    
    // Close when clicking outside
    document.addEventListener('click', function(e) {
      if (!filterToggle.contains(e.target) && !filterMenu.contains(e.target)) {
        filterMenu.classList.remove('show');
      }
    });
  }
  
  // Search functionality
  const searchInput = document.getElementById('searchBudgets');
  if (searchInput) {
    searchInput.addEventListener('input', function() {
      filterBudgets();
    });
  }
  
  // Apply filters button
  const applyFiltersBtn = document.getElementById('applyFilters');
  if (applyFiltersBtn) {
    applyFiltersBtn.addEventListener('click', function() {
      filterBudgets();
      if (filterMenu) {
        filterMenu.classList.remove('show');
      }
    });
  }
  
  // Reset filters button
  const resetFiltersBtn = document.getElementById('resetFilters');
  if (resetFiltersBtn) {
    resetFiltersBtn.addEventListener('click', function() {
      resetFilters();
      if (filterMenu) {
        filterMenu.classList.remove('show');
      }
    });
  }
  
  // Clear filters button in no results message
  const clearFiltersBtn = document.getElementById('clearFilters');
  if (clearFiltersBtn) {
    clearFiltersBtn.addEventListener('click', resetFilters);
  }
}

/**
 * Filter budgets based on search and filter criteria
 */
function filterBudgets() {
  const searchTerm = document.getElementById('searchBudgets').value.toLowerCase();
  const statusFilter = document.getElementById('statusFilter').value;
  const sortBy = document.getElementById('sortBy').value;
  
  const table = document.getElementById('budgetTable');
  const noResultsMsg = document.getElementById('noMatchingBudgets');
  
  if (!table || !noResultsMsg) return;
  
  const rows = table.querySelectorAll('tbody tr');
  let visibleRows = 0;
  
  rows.forEach(row => {
    const categoryText = row.querySelector('.category-cell').textContent.toLowerCase();
    const status = row.getAttribute('data-status');
    
    // Check if row matches all filters
    const matchesSearch = searchTerm === '' || categoryText.includes(searchTerm);
    const matchesStatus = statusFilter === 'all' || status === statusFilter;
    
    const isVisible = matchesSearch && matchesStatus;
    row.style.display = isVisible ? '' : 'none';
    
    if (isVisible) {
      visibleRows++;
    }
  });
  
  // Show/hide no results message
  if (visibleRows === 0 && rows.length > 0) {
    table.style.display = 'none';
    noResultsMsg.style.display = 'block';
  } else {
    table.style.display = '';
    noResultsMsg.style.display = 'none';
  }
  
  // Apply sorting
  sortTable(sortBy);
}

/**
 * Reset all filters to default
 */
function resetFilters() {
  const searchInput = document.getElementById('searchBudgets');
  const statusFilter = document.getElementById('statusFilter');
  const sortBy = document.getElementById('sortBy');
  
  if (searchInput) searchInput.value = '';
  if (statusFilter) statusFilter.value = 'all';
  if (sortBy) sortBy.value = 'category';
  
  filterBudgets();
}

/**
 * Sort table based on selected column
 */
function sortTable(sortBy) {
  const table = document.getElementById('budgetTable');
  if (!table) return;
  
  const tbody = table.querySelector('tbody');
  const rows = Array.from(tbody.querySelectorAll('tr'));
  
  // Sort rows
  rows.sort((a, b) => {
    let aValue, bValue;
    
    switch (sortBy) {
      case 'category':
        aValue = a.querySelector('.category-cell').textContent.trim().toLowerCase();
        bValue = b.querySelector('.category-cell').textContent.trim().toLowerCase();
        break;
      case 'amount':
        aValue = parseFloat(a.getAttribute('data-budget-amount')) || 0;
        bValue = parseFloat(b.getAttribute('data-budget-amount')) || 0;
        return bValue - aValue; // Sort budget amount in descending order
      case 'spent':
        aValue = parseFloat(a.getAttribute('data-spent')) || 0;
        bValue = parseFloat(b.getAttribute('data-spent')) || 0;
        return bValue - aValue; // Sort spent amount in descending order
      case 'percentage':
        aValue = parseFloat(a.getAttribute('data-percentage')) || 0;
        bValue = parseFloat(b.getAttribute('data-percentage')) || 0;
        return bValue - aValue; // Sort percentage in descending order
      default:
        aValue = a.querySelector('.category-cell').textContent.trim().toLowerCase();
        bValue = b.querySelector('.category-cell').textContent.trim().toLowerCase();
    }
    
    if (aValue < bValue) return -1;
    if (aValue > bValue) return 1;
    return 0;
  });
  
  // Re-append rows in new order (only the visible ones)
  rows.forEach(row => {
    if (row.style.display !== 'none') {
      tbody.appendChild(row);
    }
  });
}

/**
 * Setup form validation
 */
function setupFormValidation() {
  const forms = document.querySelectorAll('.needs-validation');
  forms.forEach(form => {
    form.addEventListener('submit', function(event) {
      if (!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
      }
      
      form.classList.add('was-validated');
    });
  });
}

/**
 * Setup various button listeners
 */
function setupButtonListeners() {
  // View all categories button
  const viewAllBtn = document.getElementById('viewAllBtn');
  if (viewAllBtn) {
    viewAllBtn.addEventListener('click', function() {
      const budgetListSection = document.querySelector('.budget-list');
      if (budgetListSection) {
        budgetListSection.scrollIntoView({ behavior: 'smooth' });
      }
    });
  }
  
  // Suggest amount button
  const suggestAmountBtn = document.getElementById('suggestAmount');
  if (suggestAmountBtn) {
    suggestAmountBtn.addEventListener('click', suggestBudgetAmount);
  }
  
  // Adopt all recommendations button
  const adoptAllBtn = document.getElementById('adoptAllBtn');
  if (adoptAllBtn) {
    adoptAllBtn.addEventListener('click', function() {
      const generateBudgetForm = document.getElementById('generateBudgetForm');
      if (generateBudgetForm && confirm('Are you sure you want to adopt all budget recommendations?')) {
        generateBudgetForm.submit();
      }
    });
  }
}

/**
 * Suggest budget amount based on category
 */
function suggestBudgetAmount() {
  const categorySelect = document.getElementById('category_id');
  const amountInput = document.getElementById('amount');
  const suggestionResult = document.getElementById('suggestionResult');
  
  if (!categorySelect || !amountInput || !suggestionResult) return;
  
  if (categorySelect.value === '') {
    suggestionResult.textContent = 'Please select a category first';
    suggestionResult.classList.add('show');
    return;
  }
  
  // Get the category name
  const categoryName = categorySelect.options[categorySelect.selectedIndex].text;
  
  // Get the category rows in the table
  const rows = document.querySelectorAll('#budgetTable tbody tr');
  let existingBudget = null;
  
  for (const row of rows) {
    if (row.getAttribute('data-category-id') === categorySelect.value) {
      existingBudget = {
        amount: parseFloat(row.getAttribute('data-budget-amount')),
        spent: parseFloat(row.getAttribute('data-spent')),
        percentage: parseFloat(row.getAttribute('data-percentage'))
      };
      break;
    }
  }
  
  if (existingBudget) {
    // Use existing budget amount
    amountInput.value = existingBudget.amount.toFixed(2);
    suggestionResult.innerHTML = `
      <strong>Existing budget found:</strong> ${formatCurrency(existingBudget.amount)}<br>
      <span class="status ${existingBudget.percentage >= 90 ? 'danger' : (existingBudget.percentage >= 70 ? 'warning' : 'success')}">
        Currently ${existingBudget.percentage.toFixed(0)}% used
      </span>
    `;
    suggestionResult.classList.add('show');
  } else {
    // Suggest based on category name
    let percentageOfIncome = 0;
    
    if (categoryName.includes('Housing') || categoryName.includes('Rent') || categoryName.includes('Mortgage')) {
      percentageOfIncome = 30;
    } else if (categoryName.includes('Food') || categoryName.includes('Groceries')) {
      percentageOfIncome = 15;
    } else if (categoryName.includes('Transportation')) {
      percentageOfIncome = 10;
    } else if (categoryName.includes('Utilities')) {
      percentageOfIncome = 10;
    } else if (categoryName.includes('Insurance')) {
      percentageOfIncome = 10;
    } else if (categoryName.includes('Investment') || categoryName.includes('Savings')) {
      percentageOfIncome = 15;
    } else if (categoryName.includes('Entertainment') || categoryName.includes('Leisure')) {
      percentageOfIncome = 5;
    } else if (categoryName.includes('Personal')) {
      percentageOfIncome = 5;
    } else {
      percentageOfIncome = 5; // Default
    }
    
    // Estimate income from overview card
    const overviewCard = document.querySelector('.overview-card.primary');
    let monthlyIncome = 3000; // Default assumption
    
    if (overviewCard) {
      const budgetText = overviewCard.querySelector('.amount').textContent;
      const budgetMatch = budgetText.match(/[\d,\.]+/);
      if (budgetMatch) {
        // Assuming total budget is about 90% of income
        monthlyIncome = parseFloat(budgetMatch[0].replace(/,/g, '')) / 0.9;
      }
    }
    
    const suggestedAmount = (monthlyIncome * (percentageOfIncome / 100)).toFixed(2);
    amountInput.value = suggestedAmount;
    
    suggestionResult.innerHTML = `
      <strong>Suggested:</strong> ${formatCurrency(suggestedAmount)} (${percentageOfIncome}% of estimated income)<br>
      <span class="text-secondary">Based on typical budgeting guidelines</span>
    `;
    suggestionResult.classList.add('show');
  }
}

/**
 * Format currency with symbol
 */
function formatCurrency(amount) {
  const currencySymbol = getCurrencySymbol();
  return `${currencySymbol}${parseFloat(amount).toLocaleString(undefined, {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  })}`;
}

/**
 * Setup modal listener functions
 */
function setupModalListeners() {
  // Edit from details modal button
  const editFromDetailsBtn = document.getElementById('editFromDetails');
  if (editFromDetailsBtn) {
    editFromDetailsBtn.addEventListener('click', function() {
      const budgetId = this.getAttribute('data-budget-id');
      if (budgetId) {
        // Close details modal
        const detailsModal = bootstrap.Modal.getInstance(document.getElementById('categoryDetailsModal'));
        if (detailsModal) detailsModal.hide();
        
        // Open edit modal
        openEditBudgetModal(budgetId);
      }
    });
  }
}

/**
 * Open category details modal
 */
function openCategoryDetails(budgetId, categoryId) {
  const modal = document.getElementById('categoryDetailsModal');
  if (!modal) return;
  
  // Find the row with this budget
  const row = document.querySelector(`tr[data-budget-id="${budgetId}"]`);
  if (!row) return;
  
  // Get category data
  const categoryName = row.querySelector('.category-name span').textContent.trim();
  const budgetAmount = parseFloat(row.getAttribute('data-budget-amount'));
  const spentAmount = parseFloat(row.getAttribute('data-spent'));
  const availableAmount = parseFloat(row.getAttribute('data-available'));
  const percentage = parseFloat(row.getAttribute('data-percentage'));
  const statusClass = row.getAttribute('data-status');
  
  // Set modal title
  const titleElement = modal.querySelector('#categoryDetailsTitle');
  if (titleElement) {
    titleElement.textContent = categoryName;
  }
  
  // Set summary values
  setElementValue('detailsBudgetAmount', formatCurrency(budgetAmount));
  setElementValue('detailsSpentAmount', formatCurrency(spentAmount));
  setElementValue('detailsRemainingAmount', formatCurrency(availableAmount));
  setElementValue('detailsUsagePercentage', `${percentage.toFixed(0)}%`);
  
  // Set progress bar
  const progressFill = document.getElementById('detailsProgressFill');
  if (progressFill) {
    progressFill.style.width = `${Math.min(100, percentage)}%`;
    progressFill.className = `progress-fill ${statusClass}`;
  }
  
  // Calculate forecast
  const currentDate = new Date();
  const currentDay = currentDate.getDate();
  const daysInMonth = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 0).getDate();
  const dailyRate = spentAmount / currentDay;
  const forecastAmount = dailyRate * daysInMonth;
  
  // Set forecast values
  setElementValue('detailsForecastAmount', formatCurrency(forecastAmount));
  setElementValue('detailsEstimatedFinal', formatCurrency(forecastAmount));
  
  // Set forecast status
  const forecastStatus = document.getElementById('forecastStatus');
  if (forecastStatus) {
    if (forecastAmount <= budgetAmount) {
      forecastStatus.className = 'forecast-status good';
      forecastStatus.textContent = getTranslation('forecast-within-budget', 'You are within budget');
    } else {
      forecastStatus.className = 'forecast-status danger';
      forecastStatus.textContent = getTranslation('forecast-exceeds-budget', 'You are likely to exceed your budget');
    }
  }
  
  // Initialize trend chart
  initializeTrendChart();
  
  // Set edit button data
  const editFromDetailsBtn = document.getElementById('editFromDetails');
  if (editFromDetailsBtn) {
    editFromDetailsBtn.setAttribute('data-budget-id', budgetId);
  }
  
  // Reset active tab to first one
  const tabBtns = modal.querySelectorAll('.tab-btn');
  const tabPanes = modal.querySelectorAll('.tab-pane');
  
  tabBtns.forEach(btn => btn.classList.remove('active'));
  tabPanes.forEach(pane => pane.classList.remove('active'));
  
  if (tabBtns.length > 0) tabBtns[0].classList.add('active');
  if (tabPanes.length > 0) tabPanes[0].classList.add('active');
  
  // Show modal
  const modalInstance = new bootstrap.Modal(modal);
  modalInstance.show();
}

/**
 * Set element value helper
 */
function setElementValue(id, value) {
  const element = document.getElementById(id);
  if (element) {
    element.textContent = value;
  }
}

/**
 * Initialize trend chart
 */
function initializeTrendChart() {
  const canvas = document.getElementById('trendChart');
  if (!canvas) return;
  
  // Clear existing chart
  if (window.trendChart) {
    window.trendChart.destroy();
  }
  
  // Sample data - in a real implementation, you would fetch this from the API
  const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
  const budgetData = [1000, 1000, 1000, 1000, 1000, 1000];
  const spentData = [800, 900, 1100, 950, 1000, 850];
  
  // Create chart
  window.trendChart = new Chart(canvas, {
    type: 'line',
    data: {
      labels: months,
      datasets: [
        {
          label: 'Budget',
          data: budgetData,
          borderColor: '#6366f1',
          backgroundColor: 'rgba(99, 102, 241, 0.1)',
          tension: 0.1,
          fill: false
        },
        {
          label: 'Spent',
          data: spentData,
          borderColor: '#ef4444',
          backgroundColor: 'rgba(239, 68, 68, 0.1)',
          tension: 0.1,
          fill: false
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            callback: function(value) {
              return getCurrencySymbol() + value;
            }
          }
        }
      },
      plugins: {
        tooltip: {
          callbacks: {
            label: function(context) {
              return context.dataset.label + ': ' + formatCurrency(context.parsed.y);
            }
          }
        }
      }
    }
  });
}

/**
 * Open edit budget modal
 */
function openEditBudgetModal(budgetId) {
  // Get the base path
  const basePath = window.BASE_PATH || '';
  
  // Fetch the budget data
  fetch(`${basePath}/budget?action=get_budget&budget_id=${budgetId}`, {
    headers: {
      'X-Requested-With': 'XMLHttpRequest'
    }
  })
  .then(response => response.json())
  .then(data => {
    if (data.success && data.budget) {
      // Set form values
      const form = document.getElementById('editBudgetForm');
      if (!form) return;
      
      document.getElementById('edit_budget_id').value = data.budget.budget_id;
      document.getElementById('edit_category_id').value = data.budget.category_id;
      document.getElementById('edit_amount').value = data.budget.amount;
      document.getElementById('edit_start_date').value = data.budget.start_date;
      document.getElementById('edit_end_date').value = data.budget.end_date;
      
      // Set stats
      updateEditBudgetStats(budgetId);
      
      // Show modal
      const modal = new bootstrap.Modal(document.getElementById('editBudgetModal'));
      modal.show();
    } else {
      alert(getTranslation('error-load-budget', 'Failed to load budget data'));
    }
  })
  .catch(error => {
    console.error('Error loading budget data:', error);
    alert(getTranslation('error-load-budget', 'Failed to load budget data'));
  });
}

/**
 * Update edit budget stats
 */
function updateEditBudgetStats(budgetId) {
  const row = document.querySelector(`tr[data-budget-id="${budgetId}"]`);
  if (!row) return;
  
  const statsContainer = document.getElementById('editBudgetStats');
  if (!statsContainer) return;
  
  const budgetAmount = parseFloat(row.getAttribute('data-budget-amount'));
  const spentAmount = parseFloat(row.getAttribute('data-spent'));
  const availableAmount = parseFloat(row.getAttribute('data-available'));
  const percentage = parseFloat(row.getAttribute('data-percentage'));
  
  let statusClass = 'success';
  let statusText = 'Good';
  
  if (percentage >= 90) {
    statusClass = 'danger';
    statusText = 'Critical';
  } else if (percentage >= 70) {
    statusClass = 'warning';
    statusText = 'Warning';
  }
  
  statsContainer.innerHTML = `
    <div class="stats-item">
      <span class="label">Spent</span>
      <span class="value">${formatCurrency(spentAmount)}</span>
    </div>
    <div class="stats-item">
      <span class="label">Remaining</span>
      <span class="value ${availableAmount < 0 ? 'negative' : 'positive'}">${formatCurrency(availableAmount)}</span>
    </div>
    <div class="stats-item">
      <span class="label">Used</span>
      <span class="value">${percentage.toFixed(0)}%</span>
    </div>
    <div class="stats-item">
      <span class="label">Status</span>
      <span class="value ${statusClass}">${statusText}</span>
    </div>
  `;
}

/**
 * Open delete budget modal
 */
function openDeleteBudgetModal(budgetId) {
  const row = document.querySelector(`tr[data-budget-id="${budgetId}"]`);
  if (!row) return;
  
  const categoryName = row.querySelector('.category-name span').textContent.trim();
  const budgetAmount = formatCurrency(parseFloat(row.getAttribute('data-budget-amount')));
  
  // Set form values
  document.getElementById('delete_budget_id').value = budgetId;
  
  // Set display values
  setElementValue('deleteCategoryName', categoryName);
  setElementValue('deleteBudgetAmount', budgetAmount);
  
  // Show modal
  const modal = new bootstrap.Modal(document.getElementById('deleteBudgetModal'));
  modal.show();
}

/**
 * Setup tab navigation
 */
function setupTabNavigation() {
  const tabButtons = document.querySelectorAll('.tab-btn');
  tabButtons.forEach(button => {
    button.addEventListener('click', function() {
      const tabId = this.getAttribute('data-tab');
      
      // Remove active class from all buttons and panes
      document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
      });
      
      document.querySelectorAll('.tab-pane').forEach(pane => {
        pane.classList.remove('active');
      });
      
      // Add active class to clicked button and corresponding pane
      this.classList.add('active');
      document.getElementById(tabId + 'Tab').classList.add('active');
    });
  });
}

/**
 * Setup tips toggle
 */
function setupTipsToggle() {
  const tipsToggle = document.getElementById('tipsToggle');
  const tipsContent = document.getElementById('tipsContent');
  
  if (tipsToggle && tipsContent) {
    tipsToggle.addEventListener('click', function() {
      tipsToggle.classList.toggle('active');
      tipsContent.classList.toggle('show');
    });
  }
}

/**
 * Setup investment slider
 */
function setupInvestmentSlider() {
  const slider = document.getElementById('investmentPercentage');
  const value = document.getElementById('investmentValue');
  
  if (slider && value) {
    slider.addEventListener('input', function() {
      value.textContent = this.value + '%';
    });
  }
}

/**
 * Setup adopt recommendation buttons
 */
function setupAdoptRecommendationButtons() {
  const adoptButtons = document.querySelectorAll('.adopt-btn');
  adoptButtons.forEach(button => {
    button.addEventListener('click', function() {
      const categoryId = this.getAttribute('data-category-id');
      const amount = this.getAttribute('data-amount');
      const existingBudgetId = this.getAttribute('data-existing-budget-id');
      
      if (existingBudgetId) {
        // Update existing budget
        document.getElementById('edit_budget_id').value = existingBudgetId;
        document.getElementById('edit_category_id').value = categoryId;
        document.getElementById('edit_amount').value = amount;
        
        // Find dates from existing budget
        const row = document.querySelector(`tr[data-budget-id="${existingBudgetId}"]`);
        let startDate = document.getElementById('start_date').value;
        let endDate = document.getElementById('end_date').value;
        
        // Show edit modal
        openEditBudgetModal(existingBudgetId);
      } else {
        // Add new budget
        document.getElementById('category_id').value = categoryId;
        document.getElementById('amount').value = amount;
        
        // Show add modal
        const modal = new bootstrap.Modal(document.getElementById('addBudgetModal'));
        modal.show();
      }
    });
  });
}

/**
 * Setup period selector
 */
function setupPeriodSelector() {
  const periodButtons = document.querySelectorAll('.period-btn');
  periodButtons.forEach(button => {
    button.addEventListener('click', function() {
      // Remove active class from all buttons
      periodButtons.forEach(btn => {
        btn.classList.remove('active');
      });
      
      // Add active class to clicked button
      this.classList.add('active');
      
      // Get period
      const period = this.getAttribute('data-period');
      
      // In a real implementation, you would fetch data for this period
      // and update the chart and categories
      
      // For now, just show a notification
      console.log(`Period changed to: ${period}`);
    });
  });
}

/**
 * Helper function used by auto-generate budget
 */
function selectInvestmentCategory() {
  const categorySelect = document.getElementById('category_id');
  if (!categorySelect) return;
  
  // Find the investment category option
  for (let i = 0; i < categorySelect.options.length; i++) {
    const option = categorySelect.options[i];
    if (option.textContent.includes('Investment') || 
        option.textContent.includes('Invest') || 
        option.classList.contains('investment-option')) {
      categorySelect.value = option.value;
      
      // Trigger suggestion
      const suggestBtn = document.getElementById('suggestAmount');
      if (suggestBtn) {
        suggestBtn.click();
      }
      
      break;
    }
  }
}