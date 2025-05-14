document.addEventListener('DOMContentLoaded', function() {
    console.log('Modern Budget JS loaded');
    
    // Initialize all components
    initializeGauge();
    initializeProgressBars();
    initializeEventListeners();
    initializeFormValidation();
    initializeSearch();
    initializeRecommendations();
    initializeFilters();
    initializeSorting();
    initializeTooltips();
    initializeTipsToggle();
    initializeChartPeriodSelector();
    initializeRangeSlider();
    setupCategoryDetails();
});

// Initialize gauge animation
function initializeGauge() {
    const gauges = document.querySelectorAll('.modern-gauge');
    gauges.forEach(gauge => {
        const percentage = parseFloat(gauge.dataset.percentage) || 0;
        const progress = gauge.querySelector('.gauge-progress');
        
        if (progress) {
            // Animate gauge on load
            setTimeout(() => {
                progress.style.strokeDashoffset = 251.2 * (1 - percentage / 100);
            }, 500);
        }
    });
}

// Initialize progress bars with animation
function initializeProgressBars() {
    // Animate category progress bars
    const progressFills = document.querySelectorAll('.progress-fill');
    progressFills.forEach((fill, index) => {
        const width = fill.style.width;
        fill.style.width = '0%';
        
        setTimeout(() => {
            fill.style.width = width;
        }, 100 + (index * 50));
    });
    
    // Animate budget progress bars in table
    const budgetProgressBars = document.querySelectorAll('.budget-progress .progress-bar');
    budgetProgressBars.forEach((bar, index) => {
        const width = bar.style.width;
        bar.style.width = '0%';
        
        setTimeout(() => {
            bar.style.width = width;
        }, 300 + (index * 30));
    });
}

// Initialize all event listeners
function initializeEventListeners() {
    // Edit budget buttons
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-action.edit')) {
            e.preventDefault();
            const button = e.target.closest('.btn-action.edit');
            const budgetId = button.getAttribute('data-budget-id');
            if (budgetId) {
                loadBudgetForEdit(budgetId);
            }
        }
    });

    // Delete budget buttons
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-action.delete')) {
            e.preventDefault();
            const button = e.target.closest('.btn-action.delete');
            const budgetId = button.getAttribute('data-budget-id');
            if (budgetId) {
                setupDeleteModal(budgetId);
            }
        }
    });

    // Quick edit buttons
    document.addEventListener('click', function(e) {
        if (e.target.closest('.edit-quick')) {
            e.preventDefault();
            const button = e.target.closest('.edit-quick');
            const budgetId = button.getAttribute('data-budget-id');
            if (budgetId) {
                setupQuickEditModal(budgetId);
            }
        }
    });

    // Adopt recommendation buttons
    document.addEventListener('click', function(e) {
        if (e.target.closest('.adopt-recommendation')) {
            e.preventDefault();
            const button = e.target.closest('.adopt-recommendation');
            adoptRecommendation(button);
        }
    });

    // Adopt all recommendations
    const adoptAllBtn = document.getElementById('adoptAllRecommendations');
    if (adoptAllBtn) {
        adoptAllBtn.addEventListener('click', function() {
            const confirmMessage = document.querySelector('meta[name="confirm-adopt-all"]')?.getAttribute('content') || 
                'Are you sure you want to adopt all budget recommendations? This will create budget entries for all recommended categories.';
            
            if (confirm(confirmMessage)) {
                document.getElementById('generateBudgetForm').submit();
            }
        });
    }

    // Budget suggestion button
    const suggestBudgetAmount = document.getElementById('suggestBudgetAmount');
    if (suggestBudgetAmount) {
        suggestBudgetAmount.addEventListener('click', generateBudgetSuggestion);
    }

    // Quick edit adjustment buttons
    setupQuickEditAdjustmentButtons();

    // Budget table row click - show category details
    const budgetTableRows = document.querySelectorAll('.budget-table tbody tr');
    budgetTableRows.forEach(row => {
        row.addEventListener('click', function(e) {
            // Don't trigger if clicked on action buttons
            if (!e.target.closest('.actions-cell')) {
                const budgetId = this.getAttribute('data-budget-id');
                const categoryId = this.getAttribute('data-category-id');
                if (budgetId && categoryId) {
                    showCategoryDetails(budgetId, categoryId, this);
                }
            }
        });
    });

    // View all categories button
    const viewAllBtn = document.getElementById('viewAllCategoriesBtn');
    if (viewAllBtn) {
        viewAllBtn.addEventListener('click', function() {
            window.scrollTo({
                top: document.querySelector('.budget-table-section').offsetTop - 20,
                behavior: 'smooth'
            });
        });
    }

    // Category items in top categories section - click to show details
    const categoryItems = document.querySelectorAll('.category-item');
    categoryItems.forEach(item => {
        item.addEventListener('click', function(e) {
            if (!e.target.closest('.category-actions')) {
                const categoryId = this.getAttribute('data-category-id');
                if (categoryId) {
                    // Find the corresponding row in the table
                    const tableRow = document.querySelector(`.budget-table tbody tr[data-category-id="${categoryId}"]`);
                    if (tableRow) {
                        const budgetId = tableRow.getAttribute('data-budget-id');
                        showCategoryDetails(budgetId, categoryId, tableRow);
                    }
                }
            }
        });
    });

    // Edit category from details modal
    const editCategoryFromDetails = document.getElementById('editCategoryFromDetails');
    if (editCategoryFromDetails) {
        editCategoryFromDetails.addEventListener('click', function() {
            const budgetId = this.getAttribute('data-budget-id');
            if (budgetId) {
                // Close the details modal
                const detailsModal = bootstrap.Modal.getInstance(document.getElementById('categoryDetailsModal'));
                if (detailsModal) {
                    detailsModal.hide();
                }
                // Open the edit modal
                loadBudgetForEdit(budgetId);
            }
        });
    }
}

// Load budget data for editing
function loadBudgetForEdit(budgetId) {
    const basePath = document.querySelector('meta[name="base-path"]')?.getAttribute('content') || '';
    
    fetch(`${basePath}/budget?action=get_budget&budget_id=${budgetId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Populate edit form
            document.getElementById('edit_budget_id').value = data.budget.budget_id;
            document.getElementById('edit_category_id').value = data.budget.category_id;
            document.getElementById('edit_amount').value = data.budget.amount;
            document.getElementById('edit_start_date').value = data.budget.start_date;
            document.getElementById('edit_end_date').value = data.budget.end_date;
            
            // Find the row in the table to get current stats
            const row = document.querySelector(`.budget-table tbody tr[data-budget-id="${budgetId}"]`);
            if (row) {
                const spent = parseFloat(row.getAttribute('data-spent')) || 0;
                const budgetAmount = parseFloat(row.getAttribute('data-budget-amount')) || 0;
                const available = parseFloat(row.getAttribute('data-available')) || 0;
                const percentage = parseFloat(row.getAttribute('data-percentage')) || 0;
                
                // Update the stats section
                const statsContainer = document.getElementById('editBudgetStats');
                if (statsContainer) {
                    statsContainer.innerHTML = `
                        <div class="stats-item">
                            <div class="stats-label">${getTranslation('spent')}</div>
                            <div class="stats-value">${formatCurrency(spent)}</div>
                        </div>
                        <div class="stats-item">
                            <div class="stats-label">${getTranslation('remaining')}</div>
                            <div class="stats-value ${available < 0 ? 'danger' : 'success'}">${formatCurrency(available)}</div>
                        </div>
                        <div class="stats-item">
                            <div class="stats-label">${getTranslation('usage')}</div>
                            <div class="stats-value ${percentage >= 90 ? 'danger' : (percentage >= 70 ? 'warning' : '')}">${percentage.toFixed(0)}%</div>
                        </div>
                        <div class="stats-item">
                            <div class="stats-label">${getTranslation('status')}</div>
                            <div class="stats-value ${getStatusClass(percentage)}">${getStatusText(percentage)}</div>
                        </div>
                    `;
                }
            }
            
            // Show edit modal
            const editModal = new bootstrap.Modal(document.getElementById('editBudgetModal'));
            editModal.show();
        } else {
            const errorMsg = document.querySelector('meta[name="error-load-budget"]')?.getAttribute('content') || 
                'Failed to load budget data';
            showNotification(errorMsg, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        const errorMsg = document.querySelector('meta[name="error-load-budget-general"]')?.getAttribute('content') || 
            'An error occurred while loading budget data';
        showNotification(errorMsg, 'error');
    });
}

// Setup the delete modal with category info
function setupDeleteModal(budgetId) {
    const row = document.querySelector(`.budget-table tbody tr[data-budget-id="${budgetId}"]`);
    if (row) {
        const categoryName = row.querySelector('.category-text').textContent.trim();
        const budgetAmount = row.getAttribute('data-budget-amount');
        
        document.getElementById('delete_budget_id').value = budgetId;
        document.getElementById('deleteCategoryName').textContent = categoryName;
        document.getElementById('deleteBudgetAmount').textContent = formatCurrency(budgetAmount);
        
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteBudgetModal'));
        deleteModal.show();
    }
}

// Setup the quick edit modal
function setupQuickEditModal(budgetId) {
    const row = document.querySelector(`.budget-table tbody tr[data-budget-id="${budgetId}"]`);
    if (row) {
        const categoryName = row.querySelector('.category-text').textContent.trim();
        const budgetAmount = parseFloat(row.getAttribute('data-budget-amount')) || 0;
        const spent = parseFloat(row.getAttribute('data-spent')) || 0;
        const percentage = parseFloat(row.getAttribute('data-percentage')) || 0;
        const categoryId = row.getAttribute('data-category-id');
        const startDate = row.getAttribute('data-start-date') || document.getElementById('start_date').value;
        const endDate = row.getAttribute('data-end-date') || document.getElementById('end_date').value;
        
        // Set hidden fields
        document.getElementById('quick_edit_budget_id').value = budgetId;
        document.getElementById('quick_edit_category_id').value = categoryId;
        document.getElementById('quick_edit_start_date').value = startDate;
        document.getElementById('quick_edit_end_date').value = endDate;
        
        // Set display fields
        document.getElementById('quickEditCategoryName').textContent = categoryName;
        document.getElementById('quickEditCurrentAmount').textContent = formatCurrency(budgetAmount);
        document.getElementById('quickEditSpentAmount').textContent = formatCurrency(spent);
        document.getElementById('quickEditPercentage').textContent = `${percentage.toFixed(0)}%`;
        
        // Set input field
        document.getElementById('quick_edit_amount').value = budgetAmount;
        
        // Set progress fill
        const progressFill = document.getElementById('quickEditProgressFill');
        progressFill.style.width = `${Math.min(100, percentage)}%`;
        progressFill.className = `progress-fill ${percentage >= 90 ? 'danger' : (percentage >= 70 ? 'warning' : 'success')}`;
        
        // Show the modal
        const quickEditModal = new bootstrap.Modal(document.getElementById('quickEditModal'));
        quickEditModal.show();
    }
}

// Setup quick edit adjustment buttons
function setupQuickEditAdjustmentButtons() {
    const decrease10 = document.getElementById('decrease10');
    const decrease5 = document.getElementById('decrease5');
    const increase5 = document.getElementById('increase5');
    const increase10 = document.getElementById('increase10');
    const amountInput = document.getElementById('quick_edit_amount');
    
    if (decrease10 && decrease5 && increase5 && increase10 && amountInput) {
        decrease10.addEventListener('click', function() {
            adjustAmount(amountInput, -10);
        });
        
        decrease5.addEventListener('click', function() {
            adjustAmount(amountInput, -5);
        });
        
        increase5.addEventListener('click', function() {
            adjustAmount(amountInput, 5);
        });
        
        increase10.addEventListener('click', function() {
            adjustAmount(amountInput, 10);
        });
    }
}

// Adjust amount in quick edit modal
function adjustAmount(input, percentChange) {
    const currentValue = parseFloat(input.value) || 0;
    const adjustment = currentValue * (percentChange / 100);
    const newValue = Math.max(0.01, currentValue + adjustment);
    input.value = newValue.toFixed(2);
}

// Generate budget suggestion
function generateBudgetSuggestion() {
    const categorySelect = document.getElementById('category_id');
    const amountInput = document.getElementById('amount');
    const suggestionInfo = document.getElementById('suggestionInfo');
    
    if (categorySelect && amountInput && suggestionInfo) {
        const categoryId = categorySelect.value;
        
        if (!categoryId) {
            suggestionInfo.textContent = getTranslation('please_select_category_first');
            suggestionInfo.classList.add('show');
            return;
        }
        
        // Get the category name
        const categoryName = categorySelect.options[categorySelect.selectedIndex].text;
        
        // Look for existing budgets in the table
        const existingRows = document.querySelectorAll(`.budget-table tbody tr[data-category-id="${categoryId}"]`);
        
        if (existingRows.length > 0) {
            // If there's an existing budget, use that amount
            const existingAmount = parseFloat(existingRows[0].getAttribute('data-budget-amount')) || 0;
            amountInput.value = existingAmount.toFixed(2);
            suggestionInfo.innerHTML = `
                <span><strong>${getTranslation('existing_budget_found')}</strong>: ${formatCurrency(existingAmount)}</span>
            `;
            suggestionInfo.classList.add('show');
        } else {
            // If no existing budget, use typical percentages based on category name
            let suggestedPercentage = 0;
            
            // Check if it's an "Investments" category
            if (categoryName.includes('Investments') || categoryName.includes('การลงทุน')) {
                suggestedPercentage = 15; // 15% for investments
            } else if (categoryName.includes('Housing') || categoryName.includes('ที่อยู่อาศัย')) {
                suggestedPercentage = 30; // 30% for housing
            } else if (categoryName.includes('Food') || categoryName.includes('อาหาร')) {
                suggestedPercentage = 15; // 15% for food
            } else if (categoryName.includes('Transportation') || categoryName.includes('การเดินทาง')) {
                suggestedPercentage = 10; // 10% for transportation
            } else if (categoryName.includes('Utilities') || categoryName.includes('สาธารณูปโภค')) {
                suggestedPercentage = 10; // 10% for utilities
            } else if (categoryName.includes('Entertainment') || categoryName.includes('ความบันเทิง')) {
                suggestedPercentage = 5; // 5% for entertainment
            } else {
                suggestedPercentage = 5; // Default for other categories
            }
            
            // Get total monthly income if available
            const incomeElements = document.querySelectorAll('.income-amount');
            let monthlyIncome = 10000; // Default fallback income
            
            if (incomeElements.length > 0) {
                const incomeText = incomeElements[0].textContent;
                const incomeMatch = incomeText.match(/[\d,.]+/);
                if (incomeMatch) {
                    monthlyIncome = parseFloat(incomeMatch[0].replace(/,/g, ''));
                }
            }
            
            const suggestedAmount = (monthlyIncome * suggestedPercentage / 100);
            amountInput.value = suggestedAmount.toFixed(2);
            
            suggestionInfo.innerHTML = `
                <span><strong>${getTranslation('suggested')}</strong>: ${formatCurrency(suggestedAmount)} (${suggestedPercentage}% ${getTranslation('of_income')})</span>
            `;
            suggestionInfo.classList.add('show');
        }
    }
}

// Adopt recommendation
function adoptRecommendation(button) {
    const categoryId = button.getAttribute('data-category-id');
    const amount = button.getAttribute('data-amount');
    const existingBudgetId = button.getAttribute('data-existing-budget-id');
    
    if (existingBudgetId) {
        // If there's an existing budget, update it
        document.getElementById('edit_budget_id').value = existingBudgetId;
        document.getElementById('edit_category_id').value = categoryId;
        document.getElementById('edit_amount').value = amount;
        
        // Get dates from an existing row
        const row = document.querySelector(`.budget-table tbody tr[data-budget-id="${existingBudgetId}"]`);
        let startDate = document.getElementById('start_date').value;
        let endDate = document.getElementById('end_date').value;
        
        if (row) {
            startDate = row.getAttribute('data-start-date') || startDate;
            endDate = row.getAttribute('data-end-date') || endDate;
        }
        
        document.getElementById('edit_start_date').value = startDate;
        document.getElementById('edit_end_date').value = endDate;
        
        // Show the edit modal
        const editModal = new bootstrap.Modal(document.getElementById('editBudgetModal'));
        editModal.show();
    } else {
        // If it's a new budget, use the add modal
        document.getElementById('category_id').value = categoryId;
        document.getElementById('amount').value = amount;
        
        // Show the add budget modal
        const addModal = new bootstrap.Modal(document.getElementById('addBudgetModal'));
        addModal.show();
    }
}

// Show category details
function showCategoryDetails(budgetId, categoryId, rowElement) {
    // Get category data from the row
    const categoryName = rowElement.querySelector('.category-text').textContent.trim();
    const budgetAmount = parseFloat(rowElement.getAttribute('data-budget-amount')) || 0;
    const spent = parseFloat(rowElement.getAttribute('data-spent')) || 0;
    const available = parseFloat(rowElement.getAttribute('data-available')) || 0;
    const percentage = parseFloat(rowElement.getAttribute('data-percentage')) || 0;
    
    // Set the modal title
    document.getElementById('categoryDetailsTitle').textContent = categoryName;
    
    // Set the summary values
    document.getElementById('detailsBudgetAmount').textContent = formatCurrency(budgetAmount);
    document.getElementById('detailsSpentAmount').textContent = formatCurrency(spent);
    document.getElementById('detailsRemainingAmount').textContent = formatCurrency(available);
    document.getElementById('detailsUsagePercentage').textContent = `${percentage.toFixed(0)}%`;
    
    // Set the progress fill
    const progressFill = document.getElementById('detailsProgressFill');
    progressFill.style.width = `${Math.min(100, percentage)}%`;
    progressFill.className = `progress-fill ${percentage >= 90 ? 'danger' : (percentage >= 70 ? 'warning' : 'success')}`;
    
    // Calculate forecast
    const currentDay = new Date().getDate();
    const daysInMonth = new Date(new Date().getFullYear(), new Date().getMonth() + 1, 0).getDate();
    const forecast = (spent / currentDay) * daysInMonth;
    const estimatedFinal = forecast;
    
    // Set forecast values
    document.getElementById('detailsForecastAmount').textContent = formatCurrency(forecast);
    document.getElementById('detailsEstimatedFinal').textContent = formatCurrency(estimatedFinal);
    
    // Set forecast status
    const forecastStatus = document.getElementById('forecastStatus');
    if (estimatedFinal <= budgetAmount) {
        forecastStatus.className = 'forecast-status good';
        forecastStatus.textContent = getTranslation('forecast_within_budget');
    } else if (estimatedFinal <= budgetAmount * 1.1) {
        forecastStatus.className = 'forecast-status warning';
        forecastStatus.textContent = getTranslation('forecast_slightly_over');
    } else {
        forecastStatus.className = 'forecast-status danger';
        forecastStatus.textContent = getTranslation('forecast_exceeds_budget');
    }
    
    // Reset tabs to the first one
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('active');
    });
    document.querySelectorAll('.tab-pane').forEach(pane => {
        pane.classList.remove('active');
    });
    document.querySelector('.tab-button').classList.add('active');
    document.querySelector('.tab-pane').classList.add('active');
    
    // Set edit button data
    document.getElementById('editCategoryFromDetails').setAttribute('data-budget-id', budgetId);
    
    // Show the modal
    const categoryDetailsModal = new bootstrap.Modal(document.getElementById('categoryDetailsModal'));
    categoryDetailsModal.show();
    
    // Initialize the trend chart
    initializeTrendChart(categoryId);
}

// Initialize category details tabs
function setupCategoryDetails() {
    // Setup tab switching
    const tabButtons = document.querySelectorAll('.tab-button');
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            
            // Remove active class from all buttons and panes
            document.querySelectorAll('.tab-button').forEach(btn => {
                btn.classList.remove('active');
            });
            document.querySelectorAll('.tab-pane').forEach(pane => {
                pane.classList.remove('active');
            });
            
            // Add active class to clicked button and corresponding pane
            this.classList.add('active');
            document.getElementById(`${tabId}Tab`).classList.add('active');
        });
    });
}

// Initialize trend chart in category details
function initializeTrendChart(categoryId) {
    const canvas = document.getElementById('categoryTrendChart');
    if (!canvas) return;
    
    // Sample data - in a real implementation, you would fetch this from the server
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
    const budgetData = [1000, 1000, 1000, 1000, 1000, 1000];
    const spendingData = [800, 950, 1100, 950, 900, 1050];
    
    // Destroy existing chart if it exists
    if (window.categoryChart) {
        window.categoryChart.destroy();
    }
    
    // Create the chart
    window.categoryChart = new Chart(canvas, {
        type: 'line',
        data: {
            labels: months,
            datasets: [
                {
                    label: getTranslation('budget'),
                    data: budgetData,
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    borderWidth: 2,
                    fill: false,
                    tension: 0.1
                },
                {
                    label: getTranslation('spent'),
                    data: spendingData,
                    borderColor: '#ef4444',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    borderWidth: 2,
                    fill: false,
                    tension: 0.1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

// Form validation
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

// Initialize search functionality
function initializeSearch() {
    const categorySearch = document.getElementById('categorySearch');
    const globalSearch = document.getElementById('globalSearch');
    
    if (categorySearch) {
        categorySearch.addEventListener('input', function() {
            filterTable(this.value);
        });
    }
    
    if (globalSearch) {
        globalSearch.addEventListener('input', function() {
            filterTable(this.value);
        });
    }
}

// Filter table based on search term
function filterTable(searchTerm) {
    const searchTermLower = searchTerm.toLowerCase();
    const tableRows = document.querySelectorAll('.budget-table tbody tr');
    const noMatchingMessage = document.getElementById('noMatchingBudgets');
    let visibleRows = 0;
    
    tableRows.forEach(row => {
        const categoryName = row.querySelector('.category-text').textContent.toLowerCase();
        let isVisible = categoryName.includes(searchTermLower);
        
        // Check budget amount, spent amount, remaining amount as well
        if (!isVisible) {
            const values = [
                row.querySelector('td:nth-child(2)')?.textContent,
                row.querySelector('td:nth-child(3)')?.textContent,
                row.querySelector('td:nth-child(4)')?.textContent
            ];
            
            isVisible = values.some(value => value && value.toLowerCase().includes(searchTermLower));
        }
        
        row.style.display = isVisible ? '' : 'none';
        if (isVisible) visibleRows++;
    });
    
    // Show/hide no results message
    if (noMatchingMessage) {
        if (visibleRows === 0 && tableRows.length > 0) {
            document.querySelector('.table-responsive').style.display = 'none';
            noMatchingMessage.style.display = 'block';
        } else {
            document.querySelector('.table-responsive').style.display = '';
            noMatchingMessage.style.display = 'none';
        }
    }
}

// Initialize recommendations animations
function initializeRecommendations() {
    // Animate recommendation progress bars
    const recommendationBars = document.querySelectorAll('.recommendation-progress .progress-bar');
    recommendationBars.forEach((bar, index) => {
        const width = bar.style.width;
        bar.style.width = '0%';
        
        setTimeout(() => {
            bar.style.width = width;
        }, 800 + (index * 100));
    });
}

// Initialize filters
function initializeFilters() {
    const filterToggle = document.getElementById('filterToggle');
    const filterDropdown = document.getElementById('filterDropdown');
    const applyFiltersBtn = document.getElementById('applyFilters');
    const resetFiltersBtn = document.getElementById('resetFilters');
    
    if (filterToggle && filterDropdown) {
        filterToggle.addEventListener('click', function() {
            filterToggle.classList.toggle('active');
            filterDropdown.classList.toggle('show');
        });
        
        // Close the dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!filterToggle.contains(e.target) && !filterDropdown.contains(e.target)) {
                filterToggle.classList.remove('active');
                filterDropdown.classList.remove('show');
            }
        });
    }
    
    if (applyFiltersBtn) {
        applyFiltersBtn.addEventListener('click', applyFilters);
    }
    
    if (resetFiltersBtn) {
        resetFiltersBtn.addEventListener('click', resetFilters);
    }
}

// Apply filters
function applyFilters() {
    const categoryFilter = document.getElementById('filterCategory').value;
    const statusFilter = document.getElementById('filterSpendingStatus').value;
    
    const tableRows = document.querySelectorAll('.budget-table tbody tr');
    let visibleRows = 0;
    
    tableRows.forEach(row => {
        let isVisible = true;
        
        // Apply category filter
        if (categoryFilter !== 'all') {
            isVisible = isVisible && row.getAttribute('data-category-id') === categoryFilter;
        }
        
        // Apply status filter
        if (statusFilter !== 'all') {
            isVisible = isVisible && row.getAttribute('data-status') === statusFilter;
        }
        
        row.style.display = isVisible ? '' : 'none';
        if (isVisible) visibleRows++;
    });
    
    // Show/hide no results message
    const noMatchingMessage = document.getElementById('noMatchingBudgets');
    if (noMatchingMessage) {
        if (visibleRows === 0 && tableRows.length > 0) {
            document.querySelector('.table-responsive').style.display = 'none';
            noMatchingMessage.style.display = 'block';
        } else {
            document.querySelector('.table-responsive').style.display = '';
            noMatchingMessage.style.display = 'none';
        }
    }
    
    // Close the dropdown
    document.getElementById('filterToggle').classList.remove('active');
    document.getElementById('filterDropdown').classList.remove('show');
}

// Reset filters
function resetFilters() {
    document.getElementById('filterCategory').value = 'all';
    document.getElementById('filterSpendingStatus').value = 'all';
    
    const tableRows = document.querySelectorAll('.budget-table tbody tr');
    tableRows.forEach(row => {
        row.style.display = '';
    });
    
    // Hide no results message
    const noMatchingMessage = document.getElementById('noMatchingBudgets');
    if (noMatchingMessage) {
        document.querySelector('.table-responsive').style.display = '';
        noMatchingMessage.style.display = 'none';
    }
}

// Initialize sorting
function initializeSorting() {
    const sortCriteria = document.getElementById('sortCriteria');
    const sortDirection = document.getElementById('sortDirection');
    const tableHeaders = document.querySelectorAll('.budget-table th[data-sort]');
    
    if (sortCriteria) {
        sortCriteria.addEventListener('change', function() {
            const criteria = this.value;
            const direction = sortDirection.getAttribute('data-direction');
            sortTable(criteria, direction);
        });
    }
    
    if (sortDirection) {
        sortDirection.addEventListener('click', function() {
            const currentDirection = this.getAttribute('data-direction');
            const newDirection = currentDirection === 'asc' ? 'desc' : 'asc';
            this.setAttribute('data-direction', newDirection);
            
            // Update icon
            this.innerHTML = `<i class="fas fa-sort-amount-${newDirection === 'asc' ? 'down-alt' : 'up-alt'}"></i>`;
            
            // Sort with new direction
            const criteria = sortCriteria.value;
            sortTable(criteria, newDirection);
        });
    }
    
    if (tableHeaders.length > 0) {
        tableHeaders.forEach(header => {
            header.addEventListener('click', function() {
                const criteria = this.getAttribute('data-sort');
                let direction = this.getAttribute('data-sort-direction') || 'asc';
                
                // Toggle direction
                direction = direction === 'asc' ? 'desc' : 'asc';
                
                // Reset all headers
                tableHeaders.forEach(h => {
                    h.removeAttribute('data-sort-direction');
                    h.querySelector('i').className = 'fas fa-sort';
                });
                
                // Set current header
                this.setAttribute('data-sort-direction', direction);
                this.querySelector('i').className = `fas fa-sort-${direction === 'asc' ? 'up' : 'down'}`;
                
                // Sort table
                sortTable(criteria, direction);
                
                // Update sort controls
                if (sortCriteria) sortCriteria.value = criteria;
                if (sortDirection) {
                    sortDirection.setAttribute('data-direction', direction);
                    sortDirection.innerHTML = `<i class="fas fa-sort-amount-${direction === 'asc' ? 'down-alt' : 'up-alt'}"></i>`;
                }
            });
        });
    }
}

// Sort table
function sortTable(criteria, direction) {
    const table = document.querySelector('.budget-table');
    const rows = Array.from(table.querySelectorAll('tbody tr'));
    const tbody = table.querySelector('tbody');
    
    // Sort rows
    rows.sort((a, b) => {
        let aValue, bValue;
        
        switch (criteria) {
            case 'name':
                aValue = a.querySelector('.category-text').textContent.trim().toLowerCase();
                bValue = b.querySelector('.category-text').textContent.trim().toLowerCase();
                break;
            case 'budget':
            case 'amount':
                aValue = parseFloat(a.getAttribute('data-budget-amount')) || 0;
                bValue = parseFloat(b.getAttribute('data-budget-amount')) || 0;
                break;
            case 'spent':
                aValue = parseFloat(a.getAttribute('data-spent')) || 0;
                bValue = parseFloat(b.getAttribute('data-spent')) || 0;
                break;
            case 'remaining':
                aValue = parseFloat(a.getAttribute('data-available')) || 0;
                bValue = parseFloat(b.getAttribute('data-available')) || 0;
                break;
            case 'progress':
            case 'percentage':
                aValue = parseFloat(a.getAttribute('data-percentage')) || 0;
                bValue = parseFloat(b.getAttribute('data-percentage')) || 0;
                break;
            case 'status':
                aValue = getStatusOrder(a.getAttribute('data-status'));
                bValue = getStatusOrder(b.getAttribute('data-status'));
                break;
            default:
                aValue = a.querySelector('.category-text').textContent.trim().toLowerCase();
                bValue = b.querySelector('.category-text').textContent.trim().toLowerCase();
        }
        
        // Determine the sort order
        if (direction === 'asc') {
            return aValue > bValue ? 1 : -1;
        } else {
            return aValue < bValue ? 1 : -1;
        }
    });
    
    // Reappend rows in sorted order
    rows.forEach(row => tbody.appendChild(row));
}

// Get status order for sorting
function getStatusOrder(status) {
    switch (status) {
        case 'good': return 1;
        case 'warning': return 2;
        case 'critical': return 3;
        default: return 0;
    }
}

// Initialize tooltips
function initializeTooltips() {
    const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltips.forEach(tooltip => {
        new bootstrap.Tooltip(tooltip);
    });
}

// Initialize tips toggle
function initializeTipsToggle() {
    const tipsToggle = document.getElementById('tipsToggle');
    const tipsBody = document.getElementById('tipsBody');
    
    if (tipsToggle && tipsBody) {
        tipsToggle.addEventListener('click', function() {
            tipsToggle.classList.toggle('active');
            tipsBody.classList.toggle('show');
        });
    }
}

// Initialize chart period selector
function initializeChartPeriodSelector() {
    const periodBtns = document.querySelectorAll('.period-btn');
    
    periodBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            // Remove active class from all buttons
            periodBtns.forEach(b => b.classList.remove('active'));
            
            // Add active class to clicked button
            this.classList.add('active');
            
            // In a real implementation, you would fetch and update the chart data
            // For now, we'll just simulate a change with a notification
            const period = this.getAttribute('data-period');
            showNotification(`${getTranslation('switched_to_period')}: ${getTranslation(period)}`, 'info');
        });
    });
}

// Initialize range slider
function initializeRangeSlider() {
    const rangeSlider = document.getElementById('investmentPercentage');
    const rangeValue = document.getElementById('investmentValue');
    
    if (rangeSlider && rangeValue) {
        rangeSlider.addEventListener('input', function() {
            rangeValue.textContent = `${this.value}%`;
        });
    }
}

// Utility function to format currency
function formatCurrency(amount) {
    const currencySymbol = document.querySelector('meta[name="currency-symbol"]')?.getAttribute('content') || '$';
    return `${currencySymbol}${parseFloat(amount).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
}

// Get translation from meta tags or fallback
function getTranslation(key) {
    // Check if there's a meta tag with this translation
    const metaTag = document.querySelector(`meta[name="${key}"]`);
    if (metaTag) {
        return metaTag.getAttribute('content');
    }
    
    // Fallback translations for common terms
    const translations = {
        'spent': 'Spent',
        'remaining': 'Remaining',
        'usage': 'Usage',
        'status': 'Status',
        'good': 'Good',
        'warning': 'Warning',
        'critical': 'Critical',
        'please_select_category_first': 'Please select a category first',
        'existing_budget_found': 'Existing budget found',
        'suggested': 'Suggested',
        'of_income': 'of income',
        'budget': 'Budget',
        'forecast_within_budget': 'You are within budget',
        'forecast_slightly_over': 'You may slightly exceed your budget',
        'forecast_exceeds_budget': 'You are likely to exceed your budget',
        'switched_to_period': 'Switched to period',
        'month': 'Month',
        'quarter': 'Quarter',
        'year': 'Year'
    };
    
    return translations[key] || key;
}

// Get status class
function getStatusClass(percentage) {
    if (percentage >= 90) return 'danger';
    if (percentage >= 70) return 'warning';
    return '';
}

// Get status text
function getStatusText(percentage) {
    if (percentage >= 90) return getTranslation('critical');
    if (percentage >= 70) return getTranslation('warning');
    return getTranslation('good');
}

// Utility function to show notifications
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    
    // Add styles
    notification.style.position = 'fixed';
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.padding = '1rem 1.5rem';
    notification.style.borderRadius = '0.5rem';
    notification.style.backgroundColor = type === 'error' ? '#ef4444' : type === 'warning' ? '#f59e0b' : '#3b82f6';
    notification.style.color = 'white';
    notification.style.zIndex = '9999';
    notification.style.opacity = '0';
    notification.style.transition = 'opacity 0.3s ease';
    notification.style.boxShadow = '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)';
    
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

// Select investment category when clicking add investment budget
function selectInvestmentCategory() {
    const categorySelect = document.getElementById('category_id');
    if (categorySelect) {
        const options = categorySelect.options;
        for (let i = 0; i < options.length; i++) {
            if (options[i].text.includes('Investments') || options[i].text.includes('การลงทุน')) {
                categorySelect.value = options[i].value;
                break;
            }
        }
    }
}

// Make function globally available
window.selectInvestmentCategory = selectInvestmentCategory;