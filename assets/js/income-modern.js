/**
 * Enhanced Income Page JavaScript
 * Handles all interactive functionality for the income management page
 */

document.addEventListener('DOMContentLoaded', function() {
  // Initialize all components
  initializeCharts();
  initializeEventListeners();
  initializeFormValidation();
  initializeAnimations();
  initializeSearch();
  initializeTableSorting();
  initializeFilters();
  initializeQuickEdit();
  initializeStatusToggle();
});

/**
 * Initialize all charts on the page
 */
function initializeCharts() {
  if (typeof Chart === 'undefined') {
    console.error('Chart.js is not loaded!');
    return;
  }
  
  // Initialize the frequency distribution chart
  initializeFrequencyChart();
  
  // Initialize the income trend chart
  initializeTrendChart();
}

/**
 * Initialize the frequency distribution chart
 */
function initializeFrequencyChart() {
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
    
    const chartLabels = JSON.parse(chartLabelsEl.getAttribute('content') || '[]');
    const chartData = JSON.parse(chartDataEl.getAttribute('content') || '[]');
    const chartColors = JSON.parse(chartColorsEl.getAttribute('content') || '[]');
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
        const chartType = this.value;
        
        if (window.frequencyChart) {
          // Destroy the current chart
          window.frequencyChart.destroy();
          
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
        }
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
    
    const projectionLabels = JSON.parse(projectionLabelsEl.getAttribute('content') || '[]');
    const projectionData = JSON.parse(projectionDataEl.getAttribute('content') || '[]');
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
        // We can't actually change the projection here without server-side calculation
        // But we could hide/show a subset of the data we already have
        const months = parseInt(this.value);
        if (months && months < projectionLabels.length) {
          // Update the chart with a subset of the data
          window.trendChart.data.labels = projectionLabels.slice(0, months);
          window.trendChart.data.datasets[0].data = projectionData.slice(0, months);
          window.trendChart.update();
        } else {
          // Show all data
          window.trendChart.data.labels = projectionLabels;
          window.trendChart.data.datasets[0].data = projectionData;
          window.trendChart.update();
        }
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

/**
 * Initialize all event listeners for interactive elements
 */
function initializeEventListeners() {
  // Refresh data button
  const refreshButton = document.getElementById('refreshData');
  if (refreshButton) {
    refreshButton.addEventListener('click', refreshData);
  }

  // Edit income buttons
  document.addEventListener('click', function(e) {
    const editButton = e.target.closest('.btn-action.edit');
    if (editButton) {
      e.preventDefault();
      const incomeId = editButton.getAttribute('data-income-id');
      if (incomeId) {
        loadIncomeForEdit(incomeId);
      }
    }
  });

  // Duplicate income buttons
  document.addEventListener('click', function(e) {
    const duplicateButton = e.target.closest('.btn-action.duplicate');
    if (duplicateButton) {
      e.preventDefault();
      const incomeId = duplicateButton.getAttribute('data-income-id');
      if (incomeId) {
        loadIncomeForDuplicate(incomeId);
      }
    }
  });

  // Delete income buttons
  document.addEventListener('click', function(e) {
    const deleteButton = e.target.closest('.btn-action.delete');
    if (deleteButton) {
      e.preventDefault();
      const incomeId = deleteButton.getAttribute('data-income-id');
      if (incomeId) {
        const row = deleteButton.closest('tr');
        const incomeName = row.querySelector('.source-name-cell').textContent;
        
        document.getElementById('delete_income_id').value = incomeId;
        const deleteNameDisplay = document.querySelector('.delete-income-name');
        if (deleteNameDisplay) {
          deleteNameDisplay.textContent = incomeName;
        }
        
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteIncomeModal'));
        deleteModal.show();
      }
    }
  });

  // Reset filters button
  const resetFiltersButton = document.querySelector('.btn-reset-filters');
  if (resetFiltersButton) {
    resetFiltersButton.addEventListener('click', resetFilters);
  }

  // Add income modal focus
  const addIncomeModal = document.getElementById('addIncomeModal');
  if (addIncomeModal) {
    addIncomeModal.addEventListener('shown.bs.modal', function() {
      document.getElementById('name').focus();
    });
  }

  // Handle end date inputs
  const endDateInputs = document.querySelectorAll('#end_date, #edit_end_date, #duplicate_end_date');
  endDateInputs.forEach(input => {
    input.addEventListener('change', function() {
      // Allow empty values for optional end date
      if (!this.value) {
        return;
      }
      
      // Validate the date
      const selectedDate = new Date(this.value);
      if (isNaN(selectedDate.getTime())) {
        this.value = '';
        return;
      }
      
      // Check if date is reasonable
      if (selectedDate < new Date('1900-01-01')) {
        showToast('Error', 'Please select a valid date after January 1, 1900', 'error');
        this.value = '';
        return;
      }
    });
  });

  // Top income sources item click
  document.querySelectorAll('.source-item').forEach(item => {
    item.addEventListener('click', function(e) {
      // Don't trigger if clicking on the quick edit button
      if (e.target.closest('.btn-quick-edit')) {
        return;
      }
      
      const incomeId = this.getAttribute('data-income-id');
      if (incomeId) {
        // Scroll to and highlight the corresponding row in the table
        const tableRow = document.querySelector(`#incomeTable tr[data-id="${incomeId}"]`);
        if (tableRow) {
          tableRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
          tableRow.classList.add('highlight-row');
          setTimeout(() => {
            tableRow.classList.remove('highlight-row');
          }, 2000);
        }
      }
    });
  });
}

/**
 * Refresh data by reloading the page
 */
function refreshData() {
  const refreshBtn = document.getElementById('refreshData');
  if (refreshBtn) {
    refreshBtn.classList.add('loading');
    refreshBtn.setAttribute('disabled', 'disabled');
  }
  
  // Show toast notification
  showToast('Refreshing', 'Updating your financial data...', 'info');
  
  // Reload the page after a short delay
  setTimeout(() => {
    window.location.reload();
  }, 1000);
}

/**
 * Load income data for editing
 * @param {string} incomeId - The income ID to edit
 */
function loadIncomeForEdit(incomeId) {
  const basePath = document.querySelector('meta[name="base-path"]').getAttribute('content');
  
  fetch(`${basePath}/income?action=get_income&income_id=${incomeId}`, {
    headers: {
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
    if (data.success) {
      // Populate edit form
      document.getElementById('edit_income_id').value = data.income.income_id;
      document.getElementById('edit_name').value = data.income.name;
      document.getElementById('edit_amount').value = data.income.amount;
      document.getElementById('edit_frequency').value = data.income.frequency;
      document.getElementById('edit_start_date').value = data.income.start_date;
      
      // Handle end date
      const endDateInput = document.getElementById('edit_end_date');
      if (data.income.end_date && data.income.end_date !== '0000-00-00' && data.income.end_date !== null) {
        const parsedDate = new Date(data.income.end_date);
        if (!isNaN(parsedDate.getTime()) && parsedDate > new Date('1900-01-01')) {
          endDateInput.value = data.income.end_date;
        } else {
          endDateInput.value = '';
        }
      } else {
        endDateInput.value = '';
      }
      
      document.getElementById('edit_is_active').checked = data.income.is_active == 1;
      
      // Show edit modal
      const editModal = new bootstrap.Modal(document.getElementById('editIncomeModal'));
      editModal.show();
    } else {
      showToast('Error', 'Failed to load income data', 'error');
    }
  })
  .catch(error => {
    console.error('Error:', error);
    showToast('Error', 'An error occurred while loading income data', 'error');
  });
}

/**
 * Load income data for duplication
 * @param {string} incomeId - The income ID to duplicate
 */
function loadIncomeForDuplicate(incomeId) {
  const basePath = document.querySelector('meta[name="base-path"]').getAttribute('content');
  
  fetch(`${basePath}/income?action=get_income&income_id=${incomeId}`, {
    headers: {
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
    if (data.success) {
      // Populate duplicate form
      document.getElementById('duplicate_name').value = `Copy of ${data.income.name}`;
      document.getElementById('duplicate_amount').value = data.income.amount;
      document.getElementById('duplicate_frequency').value = data.income.frequency;
      document.getElementById('duplicate_start_date').value = new Date().toISOString().split('T')[0]; // Today's date
      
      // Handle end date
      const endDateInput = document.getElementById('duplicate_end_date');
      if (data.income.end_date && data.income.end_date !== '0000-00-00' && data.income.end_date !== null) {
        const parsedDate = new Date(data.income.end_date);
        if (!isNaN(parsedDate.getTime()) && parsedDate > new Date('1900-01-01')) {
          endDateInput.value = data.income.end_date;
        } else {
          endDateInput.value = '';
        }
      } else {
        endDateInput.value = '';
      }
      
      document.getElementById('duplicate_is_active').checked = data.income.is_active == 1;
      
      // Show duplicate modal
      const duplicateModal = new bootstrap.Modal(document.getElementById('duplicateIncomeModal'));
      duplicateModal.show();
    } else {
      showToast('Error', 'Failed to load income data for duplication', 'error');
    }
  })
  .catch(error => {
    console.error('Error:', error);
    showToast('Error', 'An error occurred while loading income data for duplication', 'error');
  });
}

/**
 * Initialize form validation for all forms
 */
function initializeFormValidation() {
  const forms = document.querySelectorAll('.needs-validation');
  
  forms.forEach(form => {
    form.addEventListener('submit', function(event) {
      if (!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
      }
      
      form.classList.add('was-validated');
    }, false);
  });
}

/**
 * Initialize animations for visual elements
 */
function initializeAnimations() {
  // Animate source bars on load
  const sourceBars = document.querySelectorAll('.source-bar-fill');
  sourceBars.forEach((bar, index) => {
    setTimeout(() => {
      const percentage = bar.getAttribute('data-percentage') || bar.style.width.replace('%', '');
      bar.style.width = percentage + '%';
    }, 100 + (index * 50));
  });
}

/**
 * Initialize search functionality for the income table
 */
function initializeSearch() {
  const searchInput = document.getElementById('incomeSearch');
  
  if (searchInput) {
    searchInput.addEventListener('input', function() {
      filterTable();
    });
  }
}

/**
 * Initialize table sorting functionality
 */
function initializeTableSorting() {
  const sortableHeaders = document.querySelectorAll('th.sortable');
  
  sortableHeaders.forEach(header => {
    header.addEventListener('click', function() {
      const sortBy = this.getAttribute('data-sort');
      if (!sortBy) return;
      
      // If already sorted, toggle direction; otherwise, sort ascending
      const currentDirection = this.classList.contains('sorted-asc') ? 'desc' : 
                             this.classList.contains('sorted-desc') ? 'none' : 'asc';
      
      // Remove sorting classes from all headers
      document.querySelectorAll('th.sortable').forEach(h => {
        h.classList.remove('sorted-asc', 'sorted-desc');
      });
      
      // Apply sorting class to current header if not 'none'
      if (currentDirection !== 'none') {
        this.classList.add(currentDirection === 'asc' ? 'sorted-asc' : 'sorted-desc');
        sortTable(sortBy, currentDirection);
      }
    });
  });
}

/**
 * Sort the income table by the specified column and direction
 * @param {string} column - The column to sort by
 * @param {string} direction - The sort direction ('asc' or 'desc')
 */
function sortTable(column, direction) {
  const table = document.getElementById('incomeTable');
  const tbody = table.querySelector('tbody');
  const rows = Array.from(tbody.querySelectorAll('tr'));
  
  // Sort rows based on column and direction
  rows.sort((a, b) => {
    let aValue, bValue;
    
    switch (column) {
      case 'name':
        aValue = a.querySelector('.source-name-cell').textContent.trim().toLowerCase();
        bValue = b.querySelector('.source-name-cell').textContent.trim().toLowerCase();
        break;
      case 'amount':
        // Extract numeric value from formatted amount
        aValue = parseFloat(a.querySelector('.amount-cell').textContent.replace(/[^0-9.-]+/g, ''));
        bValue = parseFloat(b.querySelector('.amount-cell').textContent.replace(/[^0-9.-]+/g, ''));
        break;
      case 'frequency':
        // Get the frequency text
        aValue = a.querySelector('.frequency-badge').textContent.trim().toLowerCase();
        bValue = b.querySelector('.frequency-badge').textContent.trim().toLowerCase();
        
        // Custom sorting order for frequencies
        const order = {
          'daily': 1,
          'weekly': 2,
          'bi-weekly': 3,
          'monthly': 4,
          'quarterly': 5,
          'annually': 6,
          'one-time': 7
        };
        
        // Map to custom order values if found
        if (order[aValue] !== undefined) aValue = order[aValue];
        if (order[bValue] !== undefined) bValue = order[bValue];
        break;
      case 'start_date':
      case 'end_date':
        // Get the date column
        const aDate = column === 'start_date' ? 
          a.cells[3].textContent : a.cells[4].textContent;
        const bDate = column === 'start_date' ? 
          b.cells[3].textContent : b.cells[4].textContent;
        
        // Handle 'ongoing' text for end dates
        if (column === 'end_date') {
          if (aDate.includes('ongoing')) {
            aValue = new Date(9999, 11, 31); // Far future date
          } else {
            aValue = new Date(aDate);
          }
          
          if (bDate.includes('ongoing')) {
            bValue = new Date(9999, 11, 31); // Far future date
          } else {
            bValue = new Date(bDate);
          }
        } else {
          aValue = new Date(aDate);
          bValue = new Date(bDate);
        }
        break;
      case 'status':
        aValue = a.getAttribute('data-status');
        bValue = b.getAttribute('data-status');
        break;
      default:
        return 0;
    }
    
    // Handle different data types
    if (aValue === bValue) return 0;
    
    // Determine sort direction
    const sortVal = direction === 'asc' ? 
      (aValue > bValue ? 1 : -1) : 
      (aValue < bValue ? 1 : -1);
      
    return sortVal;
  });
  
  // Reappend rows in sorted order
  rows.forEach(row => tbody.appendChild(row));
}

/**
 * Initialize table filtering functionality
 */
function initializeFilters() {
  const statusFilter = document.getElementById('statusFilter');
  const frequencyFilter = document.getElementById('frequencyFilter');
  
  if (statusFilter) {
    statusFilter.addEventListener('change', function() {
      filterTable();
    });
  }
  
  if (frequencyFilter) {
    frequencyFilter.addEventListener('change', function() {
      filterTable();
    });
  }
}

/**
 * Filter the income table based on search input and filters
 */
function filterTable() {
  const searchTerm = document.getElementById('incomeSearch').value.toLowerCase();
  const statusFilter = document.getElementById('statusFilter').value;
  const frequencyFilter = document.getElementById('frequencyFilter').value;
  
  const tableRows = document.querySelectorAll('#incomeTable tbody tr');
  let visibleRows = 0;
  
  tableRows.forEach(row => {
    const text = row.textContent.toLowerCase();
    const status = row.getAttribute('data-status');
    const frequency = row.getAttribute('data-frequency');
    
    // Check if row matches all filters
    const matchesSearch = text.includes(searchTerm);
    const matchesStatus = statusFilter === 'all' || status === statusFilter;
    const matchesFrequency = frequencyFilter === 'all' || frequency === frequencyFilter;
    
    if (matchesSearch && matchesStatus && matchesFrequency) {
      row.style.display = '';
      visibleRows++;
    } else {
      row.style.display = 'none';
    }
  });
  
  // Show/hide no results message
  const tableResponseive = document.querySelector('.table-responsive');
  const tableNoData = document.getElementById('tableNoData');
  const tableNoResults = document.getElementById('tableNoResults');
  
  if (visibleRows === 0 && tableRows.length > 0) {
    if (tableResponseive) tableResponseive.style.display = 'none';
    if (tableNoData) tableNoData.style.display = 'none';
    if (tableNoResults) tableNoResults.style.display = 'block';
  } else {
    if (tableResponseive) tableResponseive.style.display = 'block';
    if (tableNoData && tableRows.length === 0) tableNoData.style.display = 'block';
    if (tableNoResults) tableNoResults.style.display = 'none';
  }
}

/**
 * Reset all table filters
 */
function resetFilters() {
  const searchInput = document.getElementById('incomeSearch');
  const statusFilter = document.getElementById('statusFilter');
  const frequencyFilter = document.getElementById('frequencyFilter');
  
  if (searchInput) searchInput.value = '';
  if (statusFilter) statusFilter.value = 'all';
  if (frequencyFilter) frequencyFilter.value = 'all';
  
  filterTable();
}

/**
 * Initialize quick edit functionality
 */
function initializeQuickEdit() {
  // Quick edit buttons
  document.addEventListener('click', function(e) {
    const quickEditButton = e.target.closest('.btn-quick-edit');
    if (quickEditButton) {
      e.preventDefault();
      e.stopPropagation();
      
      const sourceItem = quickEditButton.closest('.source-item');
      if (sourceItem) {
        const incomeId = sourceItem.getAttribute('data-income-id');
        if (incomeId) {
          showQuickEditPopover(quickEditButton, incomeId);
        }
      }
    }
  });
}

/**
 * Show quick edit popover for an income item
 * @param {HTMLElement} button - The button that triggered the popover
 * @param {string} incomeId - The income ID to edit
 */
function showQuickEditPopover(button, incomeId) {
  // Check if we already have an active popover
  const existingPopovers = document.querySelectorAll('.popover');
  existingPopovers.forEach(p => p.remove());
  
  const basePath = document.querySelector('meta[name="base-path"]').getAttribute('content');
  
  // First, load the income data
  fetch(`${basePath}/income?action=get_income&income_id=${incomeId}`, {
    headers: {
      'X-Requested-With': 'XMLHttpRequest'
    }
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      // Create popover
      const popover = document.createElement('div');
      popover.className = 'popover fade show';
      popover.style.position = 'absolute';
      
      // Get template content
      const template = document.getElementById('quickEditTemplate');
      const content = template.innerHTML;
      
      // Create popover structure
      popover.innerHTML = `
        <div class="popover-header">Quick Edit</div>
        <div class="popover-body">${content}</div>
        <div class="popover-arrow"></div>
      `;
      
      // Position popover
      const buttonRect = button.getBoundingClientRect();
      popover.style.top = `${buttonRect.top + window.scrollY - 10}px`;
      popover.style.left = `${buttonRect.left + window.scrollX - 260}px`; // Position to the left
      
      // Add to DOM
      document.body.appendChild(popover);
      
      // Set input values
      popover.querySelector('#quick_edit_amount').value = data.income.amount;
      popover.querySelector('#quick_edit_frequency').value = data.income.frequency;
      popover.querySelector('#quick_edit_active').checked = data.income.is_active == 1;
      
      // Add event listeners
      popover.querySelector('.quick-edit-save').addEventListener('click', function() {
        saveQuickEdit(incomeId, popover);
      });
      
      popover.querySelector('.quick-edit-cancel').addEventListener('click', function() {
        popover.remove();
      });
      
      // Close when clicking outside
      document.addEventListener('click', function closePopover(e) {
        if (!popover.contains(e.target) && e.target !== button) {
          popover.remove();
          document.removeEventListener('click', closePopover);
        }
      });
    } else {
      showToast('Error', 'Failed to load income data', 'error');
    }
  })
  .catch(error => {
    console.error('Error:', error);
    showToast('Error', 'An error occurred while loading income data', 'error');
  });
}

/**
 * Save quick edit changes via AJAX
 * @param {string} incomeId - The income ID being edited
 * @param {HTMLElement} popover - The popover element containing the form
 */
function saveQuickEdit(incomeId, popover) {
  const basePath = document.querySelector('meta[name="base-path"]').getAttribute('content');
  const amount = popover.querySelector('#quick_edit_amount').value;
  const frequency = popover.querySelector('#quick_edit_frequency').value;
  const isActive = popover.querySelector('#quick_edit_active').checked ? 1 : 0;
  
  // Get i18n strings
  const savingText = document.querySelector('meta[name="i18n-saving"]')?.getAttribute('content') || 'Saving...';
  const successText = document.querySelector('meta[name="i18n-save-success"]')?.getAttribute('content') || 'Changes saved successfully';
  const errorText = document.querySelector('meta[name="i18n-save-error"]')?.getAttribute('content') || 'Error saving changes';
  
  // Show saving indicator
  const saveButton = popover.querySelector('.quick-edit-save');
  const originalText = saveButton.innerHTML;
  saveButton.innerHTML = `<i class="fas fa-spinner fa-spin"></i> ${savingText}`;
  saveButton.disabled = true;
  
  // Prepare form data
  const formData = new FormData();
  formData.append('action', 'edit');
  formData.append('income_id', incomeId);
  formData.append('amount', amount);
  formData.append('frequency', frequency);
  if (isActive) formData.append('is_active', 'on');
  
  // Need to include other required fields that we're not changing
  // First, get the current data
  fetch(`${basePath}/income?action=get_income&income_id=${incomeId}`, {
    headers: {
      'X-Requested-With': 'XMLHttpRequest'
    }
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      // Add required fields
      formData.append('name', data.income.name);
      formData.append('start_date', data.income.start_date);
      if (data.income.end_date) formData.append('end_date', data.income.end_date);
      
      // Now submit the update
      return fetch(`${basePath}/income`, {
        method: 'POST',
        body: formData
      });
    } else {
      throw new Error('Failed to load income data');
    }
  })
  .then(response => {
    if (!response.ok) {
      throw new Error('Network response was not ok');
    }
    
    // Show success message
    showToast('Success', successText, 'success');
    
    // Remove popover
    popover.remove();
    
    // Refresh the page to show updated data
    setTimeout(() => {
      window.location.reload();
    }, 1000);
  })
  .catch(error => {
    console.error('Error:', error);
    
    // Reset button
    saveButton.innerHTML = originalText;
    saveButton.disabled = false;
    
    // Show error message
    showToast('Error', errorText, 'error');
  });
}

/**
 * Initialize status toggle functionality
 */
function initializeStatusToggle() {
  document.querySelectorAll('.toggle-status').forEach(toggle => {
    toggle.addEventListener('change', function() {
      const row = this.closest('tr');
      const incomeId = row.getAttribute('data-id');
      const isActive = this.checked;
      
      updateIncomeStatus(incomeId, isActive);
    });
  });
}

/**
 * Update income status via AJAX
 * @param {string} incomeId - The income ID to update
 * @param {boolean} isActive - The new status
 */
function updateIncomeStatus(incomeId, isActive) {
  const basePath = document.querySelector('meta[name="base-path"]').getAttribute('content');
  
  // Get i18n strings
  const savingText = document.querySelector('meta[name="i18n-saving"]')?.getAttribute('content') || 'Saving...';
  const successText = document.querySelector('meta[name="i18n-save-success"]')?.getAttribute('content') || 'Changes saved successfully';
  const errorText = document.querySelector('meta[name="i18n-save-error"]')?.getAttribute('content') || 'Error saving changes';
  
  // Show saving indicator
  showToast('Updating', savingText, 'info');
  
  // First, get the current data
  fetch(`${basePath}/income?action=get_income&income_id=${incomeId}`, {
    headers: {
      'X-Requested-With': 'XMLHttpRequest'
    }
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      // Prepare form data
      const formData = new FormData();
      formData.append('action', 'edit');
      formData.append('income_id', incomeId);
      formData.append('name', data.income.name);
      formData.append('amount', data.income.amount);
      formData.append('frequency', data.income.frequency);
      formData.append('start_date', data.income.start_date);
      if (data.income.end_date) formData.append('end_date', data.income.end_date);
      if (isActive) formData.append('is_active', 'on');
      
      // Submit the update
      return fetch(`${basePath}/income`, {
        method: 'POST',
        body: formData
      });
    } else {
      throw new Error('Failed to load income data');
    }
  })
  .then(response => {
    if (!response.ok) {
      throw new Error('Network response was not ok');
    }
    
    // Show success message
    showToast('Success', successText, 'success');
    
    // Update the UI to reflect the change
    const row = document.querySelector(`tr[data-id="${incomeId}"]`);
    if (row) {
      // Update data attribute
      row.setAttribute('data-status', isActive ? 'active' : 'inactive');
      
      // Update status badge
      const statusBadge = row.querySelector('.status-badge');
      if (statusBadge) {
        statusBadge.className = `status-badge ${isActive ? 'active' : 'inactive'}`;
        statusBadge.textContent = isActive ? 'Active' : 'Inactive';
      }
    }
  })
  .catch(error => {
    console.error('Error:', error);
    
    // Show error message
    showToast('Error', errorText, 'error');
    
    // Revert the toggle to its previous state
    const toggle = document.querySelector(`tr[data-id="${incomeId}"] .toggle-status`);
    if (toggle) {
      toggle.checked = !isActive;
    }
  });
}

/**
 * Show a toast notification
 * @param {string} title - The toast title
 * @param {string} message - The toast message
 * @param {string} type - The toast type ('success', 'error', 'info')
 */
function showToast(title, message, type = 'info') {
  const toastContainer = document.querySelector('.toast-container');
  if (!toastContainer) return;
  
  // Create toast element
  const toastEl = document.createElement('div');
  toastEl.className = `toast ${type}`;
  toastEl.setAttribute('role', 'alert');
  toastEl.setAttribute('aria-live', 'assertive');
  toastEl.setAttribute('aria-atomic', 'true');
  
  // Icon based on type
  let icon = 'info-circle';
  if (type === 'success') icon = 'check-circle';
  if (type === 'error') icon = 'exclamation-circle';
  
  // Toast content
  toastEl.innerHTML = `
    <div class="toast-header">
      <i class="fas fa-${icon} me-2"></i>
      <strong class="me-auto">${title}</strong>
      <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
    <div class="toast-body">
      ${message}
    </div>
  `;
  
  // Add to container
  toastContainer.appendChild(toastEl);
  
  // Initialize and show toast
  const toast = new bootstrap.Toast(toastEl, {
    autohide: true,
    delay: 3000
  });
  toast.show();
  
  // Remove toast from DOM after it's hidden
  toastEl.addEventListener('hidden.bs.toast', function() {
    toastEl.remove();
  });
}