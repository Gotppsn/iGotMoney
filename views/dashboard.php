<?php
// Set page title and current page for menu highlighting
$page_title = 'Dashboard - iGotMoney';
$current_page = 'dashboard';

// Add dashboard-specific CSS
$additional_css = ['/assets/css/dashboard.css'];

// Include header
require_once 'includes/header.php';
?>

<div class="dashboard-header d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-3 mb-4">
    <div>
        <h1 class="h2 fw-bold">Financial Dashboard</h1>
        <p class="text-muted mb-0">Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
    </div>
    <div class="dashboard-actions">
        <button type="button" class="btn btn-sm btn-outline-primary me-2" id="refreshDashboard">
            <i class="fas fa-sync-alt me-1"></i> Refresh
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary" id="printDashboard">
            <i class="fas fa-print me-1"></i> Print
        </button>
    </div>
</div>

<!-- Financial Summary Cards -->
<div class="row mb-4 g-3">
    <div class="col-xl-3 col-md-6">
        <div class="card h-100 income-card shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="card-icon-wrapper income-icon me-3">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <div>
                        <p class="card-label mb-0">Monthly Income</p>
                    </div>
                </div>
                <h3 class="card-value mb-2">$<?php echo number_format($monthly_income, 2); ?></h3>
                <?php 
                // Calculate income trend (mock data for demonstration)
                $income_trend = 0;
                if (isset($monthly_income) && isset($previous_monthly_income)) {
                    $income_trend = $monthly_income - $previous_monthly_income;
                }
                // Default to positive trend for demonstration
                if (!isset($previous_monthly_income)) {
                    $income_trend = 150;
                }
                ?>
                <p class="card-trend mb-0 <?php echo $income_trend >= 0 ? 'trend-positive' : 'trend-negative'; ?>">
                    <i class="fas fa-<?php echo $income_trend >= 0 ? 'arrow-up' : 'arrow-down'; ?>"></i>
                    <?php echo abs($income_trend) > 0 ? '$' . number_format(abs($income_trend), 2) : '0.00'; ?> 
                    <?php echo $income_trend >= 0 ? 'increase' : 'decrease'; ?> this month
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="card h-100 expenses-card shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="card-icon-wrapper expenses-icon me-3">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <div>
                        <p class="card-label mb-0">Monthly Expenses</p>
                    </div>
                </div>
                <h3 class="card-value mb-2">$<?php echo number_format($monthly_expenses, 2); ?></h3>
                <?php 
                // Calculate expense trend (mock data for demonstration)
                $expense_trend = 0;
                if (isset($monthly_expenses) && isset($previous_monthly_expenses)) {
                    $expense_trend = $monthly_expenses - $previous_monthly_expenses;
                }
                // Default to negative trend (expenses decreasing is positive) for demonstration
                if (!isset($previous_monthly_expenses)) {
                    $expense_trend = -50;
                }
                // For expenses, decreasing is considered positive
                $expense_trend_positive = $expense_trend <= 0;
                ?>
                <p class="card-trend mb-0 <?php echo $expense_trend_positive ? 'trend-positive' : 'trend-negative'; ?>">
                    <i class="fas fa-<?php echo $expense_trend <= 0 ? 'arrow-down' : 'arrow-up'; ?>"></i>
                    <?php echo abs($expense_trend) > 0 ? '$' . number_format(abs($expense_trend), 2) : '0.00'; ?> 
                    <?php echo $expense_trend <= 0 ? 'decrease' : 'increase'; ?> this month
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="card h-100 savings-card shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="card-icon-wrapper savings-icon me-3">
                        <i class="fas fa-piggy-bank"></i>
                    </div>
                    <div>
                        <p class="card-label mb-0">Monthly Net</p>
                    </div>
                </div>
                <h3 class="card-value mb-2">$<?php echo number_format($monthly_net, 2); ?></h3>
                <?php 
                // Calculate savings rate
                $savings_rate = 0;
                if (isset($monthly_income) && $monthly_income > 0) {
                    $savings_rate = ($monthly_net / $monthly_income) * 100;
                }
                ?>
                <p class="card-trend mb-0 <?php echo $savings_rate >= 20 ? 'trend-positive' : ($savings_rate >= 10 ? 'text-warning' : 'trend-negative'); ?>">
                    <i class="fas fa-chart-pie me-1"></i>
                    <?php echo number_format($savings_rate, 1); ?>% savings rate
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="card h-100 projection-card shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="card-icon-wrapper projection-icon me-3">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div>
                        <p class="card-label mb-0">Yearly Projection</p>
                    </div>
                </div>
                <h3 class="card-value mb-2">$<?php echo number_format($yearly_net, 2); ?></h3>
                <p class="card-trend mb-0">
                    <i class="fas fa-calendar-alt me-1"></i>
                    Annual forecast based on current data
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Main Content Cards -->
<div class="row g-3">
    <!-- Expense Categories Chart -->
    <div class="col-lg-6">
        <div class="card shadow-sm dashboard-card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-chart-pie text-primary me-2"></i>
                    Expenses by Category
                </h5>
                <div class="dropdown">
                    <button class="btn btn-sm card-menu-btn dropdown-toggle" type="button" id="dropdownTimeRange" data-bs-toggle="dropdown" aria-expanded="false">
                        This Month
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownTimeRange">
                        <li><a class="dropdown-item active" href="#">This Month</a></li>
                        <li><a class="dropdown-item" href="#">Last Month</a></li>
                        <li><a class="dropdown-item" href="#">Last 3 Months</a></li>
                        <li><a class="dropdown-item" href="#">This Year</a></li>
                    </ul>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-container mb-4" style="position: relative; height: 200px;">
                    <canvas id="expenseCategoryChart"></canvas>
                </div>
                
                <h6 class="fw-bold mt-4 mb-3">Top Expenses</h6>
                
                <?php 
                // Display top expense categories
                if (isset($top_expenses) && $top_expenses->num_rows > 0):
                    $top_expenses->data_seek(0);
                    while ($expense = $top_expenses->fetch_assoc()):
                ?>
                <div class="expense-item mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="expense-category"><?php echo htmlspecialchars($expense['category_name']); ?></span>
                        <span class="expense-amount fw-bold">$<?php echo number_format($expense['total'], 2); ?></span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar category-progress-bar" role="progressbar" 
                            style="width: <?php echo min(100, ($expense['total'] / max(1, $monthly_expenses)) * 100); ?>%" 
                            aria-valuenow="<?php echo ($expense['total'] / max(1, $monthly_expenses)) * 100; ?>" 
                            aria-valuemin="0" aria-valuemax="100">
                        </div>
                    </div>
                </div>
                <?php 
                    endwhile;
                else:
                ?>
                <div class="text-center py-4">
                    <div class="empty-state-icon mb-3">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <p>No expense data available.</p>
                    <a href="<?php echo BASE_PATH; ?>/expenses/add" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus me-1"></i> Add Your First Expense
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Budget Status -->
    <div class="col-lg-6">
        <div class="card shadow-sm dashboard-card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-tasks text-primary me-2"></i>
                    Budget Status
                </h5>
                <a href="<?php echo BASE_PATH; ?>/budget" class="btn btn-sm btn-outline-primary">
                    Manage Budgets
                </a>
            </div>
            <div class="card-body">
                <?php if (empty($budget_status)): ?>
                    <div class="text-center py-4">
                        <div class="empty-state-icon mb-3">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                        <p>No budget data available. Set up your first budget.</p>
                        <a href="<?php echo BASE_PATH; ?>/budget" class="btn btn-primary">Create Budget</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($budget_status as $budget): ?>
                        <div class="budget-item mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="budget-category"><?php echo htmlspecialchars($budget['category_name']); ?></span>
                                <div>
                                    <span class="budget-percentage me-2">
                                        <?php echo number_format($budget['percentage'], 0); ?>%
                                    </span>
                                    <span class="text-muted small">
                                        $<?php echo number_format($budget['spent'], 2); ?> / $<?php echo number_format($budget['budget_amount'], 2); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="progress budget-progress" style="height: 8px;">
                                <?php 
                                $progress_class = 'budget-progress-safe';
                                if ($budget['percentage'] >= 90) {
                                    $progress_class = 'budget-progress-danger';
                                } else if ($budget['percentage'] >= 75) {
                                    $progress_class = 'budget-progress-warning';
                                }
                                ?>
                                <div class="progress-bar <?php echo $progress_class; ?>" role="progressbar" 
                                    style="width: <?php echo min(100, $budget['percentage']); ?>%" 
                                    aria-valuenow="<?php echo $budget['percentage']; ?>" 
                                    aria-valuemin="0" aria-valuemax="100">
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <hr class="my-4">
                    
                    <h6 class="fw-bold mb-3">Income vs Expenses</h6>
                    <div class="mb-3">
                        <?php 
                        $expense_percentage = 0;
                        if (isset($monthly_income) && $monthly_income > 0) {
                            $expense_percentage = ($monthly_expenses / $monthly_income) * 100;
                        }
                        $overall_status = 'text-success';
                        $overall_message = 'Great job! Your expenses are well below your income.';
                        
                        if ($expense_percentage >= 90) {
                            $overall_status = 'text-danger';
                            $overall_message = 'Warning: Your expenses are too high compared to income.';
                            $progress_class = 'budget-progress-danger';
                        } else if ($expense_percentage >= 75) {
                            $overall_status = 'text-warning';
                            $overall_message = 'Watch out: Your expenses are getting close to your income.';
                            $progress_class = 'budget-progress-warning';
                        } else {
                            $progress_class = 'budget-progress-safe';
                        }
                        ?>
                        <div class="progress budget-progress mb-2" style="height: 10px;">
                            <div class="progress-bar <?php echo $progress_class; ?>" role="progressbar" 
                                style="width: <?php echo min(100, $expense_percentage); ?>%" 
                                aria-valuenow="<?php echo $expense_percentage; ?>" 
                                aria-valuemin="0" aria-valuemax="100">
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="overall-status <?php echo $overall_status; ?>">
                                <i class="fas fa-circle me-1"></i>
                                <?php echo number_format($expense_percentage, 0); ?>% of income spent
                            </span>
                            <span class="small text-end text-muted">Target: <85%</span>
                        </div>
                        <p class="small mt-2 mb-0 <?php echo $overall_status; ?>"><?php echo $overall_message; ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Third row - Financial Goals and Advice -->
<div class="row g-3">
    <!-- Financial Goals -->
    <div class="col-lg-6">
        <div class="card shadow-sm dashboard-card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-bullseye text-primary me-2"></i>
                    Financial Goals
                </h5>
                <a href="<?php echo BASE_PATH; ?>/goals" class="btn btn-sm btn-outline-primary">
                    View All Goals
                </a>
            </div>
            <div class="card-body">
                <?php if (!isset($goals) || $goals->num_rows === 0): ?>
                    <div class="text-center py-4">
                        <div class="empty-state-icon mb-3">
                            <i class="fas fa-flag-checkered"></i>
                        </div>
                        <p>No financial goals set. Start planning your future!</p>
                        <a href="<?php echo BASE_PATH; ?>/goals" class="btn btn-primary">Set Goals</a>
                    </div>
                <?php else: ?>
                    <?php while ($goal = $goals->fetch_assoc()): ?>
                        <?php 
                        $progress = 0;
                        if ($goal['target_amount'] > 0) {
                            $progress = ($goal['current_amount'] / $goal['target_amount']) * 100; 
                        }
                        
                        // Determine status class based on progress
                        $status_class = 'bg-info';
                        $status_icon = 'clock';
                        
                        if ($progress >= 100) {
                            $status_class = 'bg-success';
                            $status_icon = 'check-circle';
                        } else if ($progress >= 75) {
                            $status_class = 'bg-primary';
                            $status_icon = 'chart-line';
                        } else if ($progress >= 50) {
                            $status_class = 'bg-warning';
                            $status_icon = 'fire';
                        }
                        
                        // Calculate target date information
                        $target_date = new DateTime($goal['target_date']);
                        $today = new DateTime();
                        $days_remaining = $today->diff($target_date)->days;
                        $is_overdue = $today > $target_date;
                        ?>
                        
                        <div class="financial-goal-item mb-4">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($goal['name']); ?></h6>
                                    <span class="badge <?php echo $status_class; ?> mt-1">
                                        <i class="fas fa-<?php echo $status_icon; ?> me-1"></i>
                                        <?php echo $progress >= 100 ? 'Completed' : number_format($progress, 0) . '%'; ?>
                                    </span>
                                </div>
                                <div class="text-end">
                                    <div class="small <?php echo $is_overdue ? 'text-danger' : 'text-muted'; ?>">
                                        <i class="fas fa-calendar-alt me-1"></i>
                                        <?php echo $is_overdue ? 'Overdue' : $days_remaining . ' days left'; ?>
                                    </div>
                                    <div class="small text-muted">
                                        Target: <?php echo date('M j, Y', strtotime($goal['target_date'])); ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="progress goal-progress mb-2" style="height: 8px;">
                                <!-- Add milestone markers if any -->
                                <?php if ($progress >= 25): ?>
                                <div class="milestone-marker <?php echo $progress >= 25 ? 'milestone-reached' : ''; ?>" 
                                     style="left: 25%;" title="25% Milestone"></div>
                                <?php endif; ?>
                                
                                <?php if ($progress >= 50): ?>
                                <div class="milestone-marker <?php echo $progress >= 50 ? 'milestone-reached' : ''; ?>" 
                                     style="left: 50%;" title="Halfway There!"></div>
                                <?php endif; ?>
                                
                                <?php if ($progress >= 75): ?>
                                <div class="milestone-marker <?php echo $progress >= 75 ? 'milestone-reached' : ''; ?>" 
                                     style="left: 75%;" title="75% Complete"></div>
                                <?php endif; ?>
                                
                                <div class="progress-bar <?php echo $status_class; ?>" role="progressbar" 
                                    style="width: <?php echo min(100, $progress); ?>%" 
                                    aria-valuenow="<?php echo $progress; ?>" 
                                    aria-valuemin="0" aria-valuemax="100">
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="small">
                                    $<?php echo number_format($goal['current_amount'], 2); ?> of 
                                    $<?php echo number_format($goal['target_amount'], 2); ?>
                                </span>
                                
                                <?php 
                                // Calculate remaining amount
                                $remaining = max(0, $goal['target_amount'] - $goal['current_amount']);
                                
                                // Simple contribution calculation
                                $contribution = 0;
                                if ($days_remaining > 0 && !$is_overdue) {
                                    // Monthly contribution
                                    $months_remaining = max(1, ceil($days_remaining / 30));
                                    $contribution = $remaining / $months_remaining;
                                }
                                ?>
                                
                                <?php if ($progress < 100 && !$is_overdue && $contribution > 0): ?>
                                <span class="small text-muted">
                                    $<?php echo number_format($contribution, 2); ?>/month to reach goal
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                    
                    <div class="text-center mt-3">
                        <a href="<?php echo BASE_PATH; ?>/goals" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-plus me-1"></i> Add New Goal
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Financial Advice -->
    <div class="col-lg-6">
        <div class="card shadow-sm dashboard-card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-lightbulb text-primary me-2"></i>
                    Financial Advice
                </h5>
                <button type="button" class="btn btn-sm btn-outline-primary" id="generateAdvice">
                    <i class="fas fa-sync me-1"></i> Get New Advice
                </button>
            </div>
            <div class="card-body">
                <?php if (!isset($financial_advice) || $financial_advice->num_rows === 0): ?>
                    <div class="text-center py-4">
                        <div class="empty-state-icon mb-3">
                            <i class="fas fa-comment-dollar"></i>
                        </div>
                        <p>No financial advice available at this time.</p>
                    </div>
                <?php else: ?>
                    <div class="advice-list">
                        <?php while ($advice = $financial_advice->fetch_assoc()): ?>
                            <?php 
                            $advice_class = 'bg-soft-info';
                            $advice_text_class = 'advice-info';
                            $advice_icon = 'info-circle';
                            
                            if ($advice['importance_level'] === 'high') {
                                $advice_class = 'bg-soft-danger';
                                $advice_text_class = 'advice-danger';
                                $advice_icon = 'exclamation-circle';
                            } else if ($advice['importance_level'] === 'medium') {
                                $advice_class = 'bg-soft-warning';
                                $advice_text_class = 'advice-warning';
                                $advice_icon = 'exclamation-triangle';
                            }
                            ?>
                            <div class="advice-item mb-3 p-3 rounded">
                                <div class="d-flex">
                                    <div class="advice-icon-wrapper <?php echo $advice_class; ?> me-3">
                                        <i class="fas fa-<?php echo $advice_icon; ?> <?php echo $advice_text_class; ?>"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <h6 class="advice-title <?php echo $advice_text_class; ?> mb-1">
                                                <?php echo htmlspecialchars($advice['title']); ?>
                                            </h6>
                                            <small class="text-muted"><?php echo date('M j', strtotime($advice['generated_at'])); ?></small>
                                        </div>
                                        <p class="advice-content mb-0">
                                            <?php echo htmlspecialchars($advice['content']); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
// JavaScript for charts and dashboard functionality
$page_scripts = "
// Initialize dashboard components
document.addEventListener('DOMContentLoaded', function() {
    // Initialize charts
    initializeCharts();
    
    // Add animation for progress bars
    animateProgressBars();
    
    // Set up event listeners
    initializeEventListeners();
});

// Initialize expense categories chart
function initializeCharts() {
    var expenseCategoryCtx = document.getElementById('expenseCategoryChart').getContext('2d');
    var expenseCategoryData = {
        labels: [";
    
    // Add chart labels safely
    if (isset($top_expenses) && $top_expenses->num_rows > 0) {
        $top_expenses->data_seek(0);
        $labels = [];
        while ($row = $top_expenses->fetch_assoc()) {
            $labels[] = "'" . addslashes($row['category_name']) . "'";
        }
        $page_scripts .= implode(',', $labels);
        // Reset for next use
        $top_expenses->data_seek(0);
    }
    
    $page_scripts .= "],
        datasets: [{
            data: [";
        
    // Add chart data safely
    if (isset($top_expenses) && $top_expenses->num_rows > 0) {
        $values = [];
        while ($row = $top_expenses->fetch_assoc()) {
            $values[] = $row['total'];
        }
        $page_scripts .= implode(',', $values);
    }
        
    $page_scripts .= "],
            backgroundColor: [
                '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b',
                '#6f42c1', '#fd7e14', '#20c9a6', '#5a5c69', '#858796'
            ],
            hoverBackgroundColor: [
                '#2e59d9', '#17a673', '#2c9faf', '#dda20a', '#be2617',
                '#5a32a3', '#db6a02', '#169b7f', '#3a3b45', '#60616f'
            ],
            borderWidth: 1,
            hoverBorderColor: 'rgba(234, 236, 244, 1)',
        }],
    };

    window.expenseCategoryChart = new Chart(expenseCategoryCtx, {
        type: 'doughnut',
        data: expenseCategoryData,
        options: {
            maintainAspectRatio: false,
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 12,
                        padding: 15,
                        font: {
                            size: 11,
                            family: 'Inter, sans-serif'
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 10,
                    bodyFont: {
                        size: 12,
                        family: 'Inter, sans-serif'
                    },
                    titleFont: {
                        size: 13,
                        family: 'Inter, sans-serif'
                    },
                    callbacks: {
                        label: function(context) {
                            var label = context.label || '';
                            var value = context.raw || 0;
                            var total = context.dataset.data.reduce((a, b) => a + b, 0);
                            var percentage = Math.round((value / total) * 100);
                            return label + ': $' + value.toFixed(2) + ' (' + percentage + '%)';
                        }
                    }
                }
            },
            cutout: '70%',
            animation: {
                animateScale: true,
                animateRotate: true,
                duration: 1000
            }
        },
    });
}

// Animate progress bars
function animateProgressBars() {
    const progressBars = document.querySelectorAll('.progress-bar');
    
    progressBars.forEach((bar, index) => {
        setTimeout(() => {
            const targetWidth = bar.getAttribute('aria-valuenow') + '%';
            bar.style.width = targetWidth;
        }, 100 + (index * 50)); // Staggered animation
    });
}

// Initialize event listeners
function initializeEventListeners() {
    // Print dashboard button
    const printButton = document.getElementById('printDashboard');
    if (printButton) {
        printButton.addEventListener('click', function() {
            window.print();
        });
    }
    
    // Refresh dashboard button
    const refreshButton = document.getElementById('refreshDashboard');
    if (refreshButton) {
        refreshButton.addEventListener('click', function() {
            location.reload();
        });
    }
    
    // Generate advice button
    const generateAdviceBtn = document.getElementById('generateAdvice');
    if (generateAdviceBtn) {
        generateAdviceBtn.addEventListener('click', function() {
            // Show loading state
            this.disabled = true;
            this.innerHTML = '<i class=\"fas fa-spinner fa-spin me-1\"></i> Generating...';
            
            // In a real app, this would make an AJAX call
            // For now, just reload the page after a short delay
            setTimeout(() => {
                window.location.href = '?generate_advice=true';
            }, 1000);
        });
    }
    
    // Time range dropdown for chart
    const timeRangeOptions = document.querySelectorAll('.dropdown-menu a.dropdown-item');
    timeRangeOptions.forEach(option => {
        option.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Update dropdown button text
            const dropdownButton = document.getElementById('dropdownTimeRange');
            if (dropdownButton) {
                dropdownButton.textContent = this.textContent;
            }
            
            // Remove active class from all options
            timeRangeOptions.forEach(opt => opt.classList.remove('active'));
            
            // Add active class to selected option
            this.classList.add('active');
            
            // In a real app, this would update the chart with new data
            // For this example, we'll simulate the update with random data
            simulateChartUpdate(this.textContent.trim());
        });
    });
}

// Simulate chart update with different time ranges
function simulateChartUpdate(timeRange) {
    // Only proceed if the chart exists
    if (!window.expenseCategoryChart) return;
    
    // Show loading indicator
    const chartContainer = document.querySelector('.chart-container');
    if (chartContainer) {
        chartContainer.style.opacity = 0.5;
        chartContainer.style.position = 'relative';
        
        // Create and append loading spinner
        const spinner = document.createElement('div');
        spinner.className = 'spinner-overlay';
        spinner.innerHTML = '<i class=\"fas fa-spinner fa-spin\"></i>';
        spinner.style.position = 'absolute';
        spinner.style.top = '50%';
        spinner.style.left = '50%';
        spinner.style.transform = 'translate(-50%, -50%)';
        spinner.style.fontSize = '1.5rem';
        spinner.style.color = '#4e73df';
        
        chartContainer.appendChild(spinner);
    }
    
    // Simulate API delay
    setTimeout(() => {
        // Generate new random data based on time range
        let newData;
        
        switch(timeRange) {
            case 'Last Month':
                newData = generateRandomData(5, 100, 500);
                break;
            case 'Last 3 Months':
                newData = generateRandomData(5, 300, 1500);
                break;
            case 'This Year':
                newData = generateRandomData(5, 1000, 5000);
                break;
            default: // 'This Month'
                // Keep existing data
                if (chartContainer) {
                    chartContainer.style.opacity = 1;
                    chartContainer.removeChild(spinner);
                }
                return;
        }
        
        // Update chart data
        window.expenseCategoryChart.data.datasets[0].data = newData;
        window.expenseCategoryChart.update();
        
        // Update top expenses list with new data
        updateTopExpensesList(newData);
        
        // Remove loading indicator
        if (chartContainer) {
            chartContainer.style.opacity = 1;
            chartContainer.removeChild(spinner);
        }
    }, 800);
}

// Generate random data for chart simulation
function generateRandomData(count, min, max) {
    const data = [];
    for (let i = 0; i < count; i++) {
        data.push(Math.floor(Math.random() * (max - min + 1)) + min);
    }
    return data;
}

// Update top expenses list with new data
function updateTopExpensesList(newData) {
    const expenseItems = document.querySelectorAll('.expense-item');
    const totalExpenses = newData.reduce((sum, value) => sum + value, 0);
    
    expenseItems.forEach((item, index) => {
        if (index < newData.length) {
            const amountElement = item.querySelector('.expense-amount');
            const progressBar = item.querySelector('.progress-bar');
            
            if (amountElement) {
                amountElement.textContent = '$' + newData[index].toFixed(2);
            }
            
            if (progressBar) {
                const percentage = (newData[index] / totalExpenses) * 100;
                progressBar.style.width = percentage + '%';
                progressBar.setAttribute('aria-valuenow', percentage);
            }
        }
    });
}
";

// Additional CSS for dashboard
$additional_css = isset($additional_css) ? $additional_css : [];
$additional_css[] = '/assets/css/dashboard.css';

// Additional JS for dashboard
$additional_js = isset($additional_js) ? $additional_js : [];
$additional_js[] = '/assets/js/dashboard.js';

// Include footer
require_once 'includes/footer.php';
?>