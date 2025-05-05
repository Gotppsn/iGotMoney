/**
 * iGotMoney - Dashboard JavaScript
 * Dashboard-specific functionality
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize dashboard refresh timer
    initializeRefreshTimer();
    
    // Set up quick actions
    initializeQuickActions();
});

/**
 * Initialize dashboard refresh timer
 */
function initializeRefreshTimer() {
    // Refresh dashboard data every 30 minutes
    const refreshInterval = 30 * 60 * 1000; // 30 minutes
    
    setInterval(function() {
        // Show refresh indicator
        const refreshButton = document.getElementById('refreshDashboard');
        if (refreshButton) {
            const icon = refreshButton.querySelector('i');
            if (icon) {
                icon.classList.add('fa-spin');
            }
            
            // Fetch fresh data
            fetchDashboardData(function() {
                // Stop spinner when done
                if (icon) {
                    icon.classList.remove('fa-spin');
                }
                
                // Show notification
                showNotification('Dashboard data updated', 'info', 2000);
            });
        }
    }, refreshInterval);
}

/**
 * Fetch dashboard data
 * @param {function} callback - Function to call when fetch is complete
 */
function fetchDashboardData(callback) {
    // This would normally make an AJAX request to get fresh data
    // For demonstration, we're just reloading the page
    
    // In a real implementation, we would do something like:
    /*
    ajaxRequest('/api/dashboard-data', 'GET', {}, function(response) {
        // Update UI with new data
        updateFinancialSummary(response.summary);
        updateBudgetStatus(response.budgets);
        updateExpenseChart(response.expenses);
        updateGoals(response.goals);
        updateAdvice(response.advice);
        
        if (callback) callback();
    }, function(error) {
        console.error('Failed to fetch dashboard data:', error);
        if (callback) callback();
    });
    */
    
    // For the demo, just wait a bit then reload
    setTimeout(function() {
        window.location.reload();
    }, 1000);
}

/**
 * Initialize quick actions
 */
function initializeQuickActions() {
    // Quick add expense button
    const quickAddExpenseBtn = document.getElementById('quickAddExpense');
    if (quickAddExpenseBtn) {
        quickAddExpenseBtn.addEventListener('click', function() {
            window.location.href = '/expenses?action=add';
        });
    }
    
    // Quick add income button
    const quickAddIncomeBtn = document.getElementById('quickAddIncome');
    if (quickAddIncomeBtn) {
        quickAddIncomeBtn.addEventListener('click', function() {
            window.location.href = '/income?action=add';
        });
    }
}

/**
 * Update financial summary cards
 * @param {object} summary - The summary data
 */
function updateFinancialSummary(summary) {
    // Update monthly income
    const monthlyIncomeElement = document.querySelector('.dashboard-card.income .h5');
    if (monthlyIncomeElement && summary.monthly_income !== undefined) {
        monthlyIncomeElement.textContent = formatCurrency(summary.monthly_income);
    }
    
    // Update monthly expenses
    const monthlyExpensesElement = document.querySelector('.dashboard-card.expenses .h5');
    if (monthlyExpensesElement && summary.monthly_expenses !== undefined) {
        monthlyExpensesElement.textContent = formatCurrency(summary.monthly_expenses);
    }
    
    // Update monthly net
    const monthlyNetElement = document.querySelector('.dashboard-card.savings .h5');
    if (monthlyNetElement && summary.monthly_net !== undefined) {
        monthlyNetElement.textContent = formatCurrency(summary.monthly_net);
    }
    
    // Update yearly projection
    const yearlyNetElement = document.querySelector('.dashboard-card.investments .h5');
    if (yearlyNetElement && summary.yearly_net !== undefined) {
        yearlyNetElement.textContent = formatCurrency(summary.yearly_net);
    }
}

/**
 * Update budget status
 * @param {array} budgets - The budget status data
 */
function updateBudgetStatus(budgets) {
    const budgetContainer = document.querySelector('.card-body > .mt-4');
    if (!budgetContainer) return;
    
    let budgetHTML = '<h4 class="small font-weight-bold">Budget Status</h4>';
    
    if (budgets.length === 0) {
        budgetHTML += `
            <div class="text-center mb-4">
                <p>No budget data available. Set up your first budget.</p>
                <a href="/budget" class="btn btn-primary">Create Budget</a>
            </div>
        `;
    } else {
        budgets.forEach(budget => {
            let progressClass = 'progress-bar-budget-safe';
            if (budget.percentage >= 90) {
                progressClass = 'progress-bar-budget-danger';
            } else if (budget.percentage >= 75) {
                progressClass = 'progress-bar-budget-warning';
            }
            
            budgetHTML += `
                <h4 class="small font-weight-bold">
                    ${budget.category_name}
                    <span class="float-end">
                        ${Math.round(budget.percentage)}%
                    </span>
                </h4>
                <div class="progress mb-4">
                    <div class="progress-bar ${progressClass}" role="progressbar" 
                        style="width: ${Math.min(100, budget.percentage)}%" 
                        aria-valuenow="${budget.percentage}" 
                        aria-valuemin="0" aria-valuemax="100">
                    </div>
                </div>
            `;
        });
    }
    
    budgetContainer.innerHTML = budgetHTML;
}

/**
 * Update expense category chart
 * @param {array} expenses - The expense data
 */
function updateExpenseChart(expenses) {
    if (!window.expenseCategoryChart) return;
    
    const labels = expenses.map(expense => expense.category_name);
    const data = expenses.map(expense => expense.total);
    
    window.expenseCategoryChart.data.labels = labels;
    window.expenseCategoryChart.data.datasets[0].data = data;
    window.expenseCategoryChart.update();
    
    // Update top expenses list
    const topExpensesContainer = document.querySelector('.mt-4');
    if (!topExpensesContainer) return;
    
    let topExpensesHTML = '<h4 class="small font-weight-bold">Top Expenses<span class="float-end">Categories</span></h4>';
    
    if (expenses.length === 0) {
        topExpensesHTML += '<p>No expense data available.</p>';
    } else {
        // Calculate total expenses
        const totalExpenses = data.reduce((sum, value) => sum + value, 0);
        
        expenses.forEach(expense => {
            const percentage = calculatePercentage(expense.total, totalExpenses);
            
            topExpensesHTML += `
                <div class="mb-2">
                    <div class="d-flex justify-content-between">
                        <span>${expense.category_name}</span>
                        <span>${formatCurrency(expense.total)}</span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" 
                            style="width: ${percentage}%" 
                            aria-valuenow="${percentage}" 
                            aria-valuemin="0" aria-valuemax="100">
                        </div>
                    </div>
                </div>
            `;
        });
    }
    
    topExpensesContainer.innerHTML = topExpensesHTML;
}

/**
 * Update financial goals
 * @param {array} goals - The financial goals data
 */
function updateGoals(goals) {
    const goalsContainer = document.querySelector('.card-body');
    if (!goalsContainer) return;
    
    let goalsHTML = '';
    
    if (goals.length === 0) {
        goalsHTML += `
            <div class="text-center">
                <p>No financial goals set. Start planning your future!</p>
                <a href="/goals" class="btn btn-primary">Set Goals</a>
            </div>
        `;
    } else {
        goals.forEach(goal => {
            const progress = calculatePercentage(goal.current_amount, goal.target_amount);
            let progressClass = 'bg-info';
            
            if (progress >= 100) {
                progressClass = 'bg-success';
            } else if (progress >= 75) {
                progressClass = 'bg-warning';
            }
            
            goalsHTML += `
                <div class="d-flex align-items-center mb-3">
                    <div class="flex-grow-1">
                        <h4 class="small font-weight-bold">${goal.name} 
                            <span class="float-end">${Math.round(progress)}%</span>
                        </h4>
                        <div class="progress">
                            <div class="progress-bar ${progressClass}" role="progressbar" 
                                style="width: ${Math.min(100, progress)}%" 
                                aria-valuenow="${progress}" 
                                aria-valuemin="0" aria-valuemax="100">
                            </div>
                        </div>
                        <small>
                            ${formatCurrency(goal.current_amount)} of 
                            ${formatCurrency(goal.target_amount)}
                        </small>
                    </div>
                </div>
            `;
        });
        
        goalsHTML += `
            <div class="text-center mt-3">
                <a href="/goals" class="btn btn-sm btn-primary">View All Goals</a>
            </div>
        `;
    }
    
    goalsContainer.innerHTML = goalsHTML;
}

/**
 * Update financial advice
 * @param {array} advice - The financial advice data
 */
function updateAdvice(advice) {
    const adviceContainer = document.querySelector('.card-body');
    if (!adviceContainer) return;
    
    let adviceHTML = '';
    
    if (advice.length === 0) {
        adviceHTML += '<p>No financial advice available at this time.</p>';
    } else {
        adviceHTML += '<div class="list-group">';
        
        advice.forEach(item => {
            let adviceClass = 'list-group-item-info';
            let adviceIcon = 'info-circle';
            
            if (item.importance_level === 'high') {
                adviceClass = 'list-group-item-danger';
                adviceIcon = 'exclamation-circle';
            } else if (item.importance_level === 'medium') {
                adviceClass = 'list-group-item-warning';
                adviceIcon = 'exclamation-triangle';
            }
            
            const date = new Date(item.generated_at);
            const formattedDate = `${date.toLocaleString('default', { month: 'short' })} ${date.getDate()}`;
            
            adviceHTML += `
                <div class="list-group-item ${adviceClass} mb-2">
                    <div class="d-flex w-100 justify-content-between">
                        <h5 class="mb-1">
                            <i class="fas fa-${adviceIcon} me-2"></i>
                            ${item.title}
                        </h5>
                        <small>${formattedDate}</small>
                    </div>
                    <p class="mb-1">${item.content}</p>
                </div>
            `;
        });
        
        adviceHTML += '</div>';
    }
    
    adviceContainer.innerHTML = adviceHTML;
}