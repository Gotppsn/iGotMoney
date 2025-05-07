<?php
// Set page title and current page for menu highlighting
$page_title = 'Dashboard - iGotMoney';
$current_page = 'dashboard';

// Include header
require_once 'includes/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Financial Dashboard</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" id="refreshDashboard">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="printDashboard">
                <i class="fas fa-print"></i> Print
            </button>
        </div>
    </div>
</div>

<!-- Financial Summary Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card shadow h-100 py-2 border-left-primary">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Monthly Income</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">$<?php echo number_format($monthly_income, 2); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-calendar fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card shadow h-100 py-2 border-left-danger">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                            Monthly Expenses</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">$<?php echo number_format($monthly_expenses, 2); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card shadow h-100 py-2 border-left-success">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Monthly Net</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">$<?php echo number_format($monthly_net, 2); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-piggy-bank fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card shadow h-100 py-2 border-left-info">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Yearly Projection</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">$<?php echo number_format($yearly_net, 2); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Content Row -->
<div class="row">
    <!-- Expense Categories Chart -->
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Expenses by Category</h6>
            </div>
            <div class="card-body">
                <div class="chart-container mb-4">
                    <canvas id="expenseCategoryChart"></canvas>
                </div>
                
                <div class="mt-4">
                    <h4 class="small font-weight-bold">Top Expenses<span class="float-end">Categories</span></h4>
                    
                    <?php 
                    // Display top expense categories
                    if (isset($top_expenses) && $top_expenses->num_rows > 0):
                        $top_expenses->data_seek(0);
                        while ($expense = $top_expenses->fetch_assoc()):
                    ?>
                    <div class="mb-2">
                        <div class="d-flex justify-content-between">
                            <span><?php echo htmlspecialchars($expense['category_name']); ?></span>
                            <span>$<?php echo number_format($expense['total'], 2); ?></span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar" role="progressbar" 
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
                    <p>No expense data available.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Budget Status -->
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Budget Status</h6>
            </div>
            <div class="card-body">
                <?php if (empty($budget_status)): ?>
                    <div class="text-center mb-4">
                        <p>No budget data available. Set up your first budget.</p>
                        <a href="<?php echo BASE_PATH; ?>/budget" class="btn btn-primary">Create Budget</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($budget_status as $budget): ?>
                        <h4 class="small font-weight-bold">
                            <?php echo htmlspecialchars($budget['category_name']); ?>
                            <span class="float-end">
                                <?php echo number_format($budget['percentage'], 0); ?>%
                            </span>
                        </h4>
                        <div class="progress mb-4">
                            <?php 
                            $progress_class = 'progress-bar-budget-safe';
                            if ($budget['percentage'] >= 90) {
                                $progress_class = 'progress-bar-budget-danger';
                            } else if ($budget['percentage'] >= 75) {
                                $progress_class = 'progress-bar-budget-warning';
                            }
                            ?>
                            <div class="progress-bar <?php echo $progress_class; ?>" role="progressbar" 
                                style="width: <?php echo min(100, $budget['percentage']); ?>%" 
                                aria-valuenow="<?php echo $budget['percentage']; ?>" 
                                aria-valuemin="0" aria-valuemax="100">
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <div class="mt-4">
                    <h4 class="small font-weight-bold">Income vs Expenses</h4>
                    <div class="progress mb-4">
                        <?php 
                        $expense_percentage = 0;
                        if (isset($monthly_income) && $monthly_income > 0) {
                            $expense_percentage = ($monthly_expenses / $monthly_income) * 100;
                        }
                        $progress_class = 'progress-bar-budget-safe';
                        if ($expense_percentage >= 90) {
                            $progress_class = 'progress-bar-budget-danger';
                        } else if ($expense_percentage >= 75) {
                            $progress_class = 'progress-bar-budget-warning';
                        }
                        ?>
                        <div class="progress-bar <?php echo $progress_class; ?>" role="progressbar" 
                            style="width: <?php echo min(100, $expense_percentage); ?>%" 
                            aria-valuenow="<?php echo $expense_percentage; ?>" 
                            aria-valuemin="0" aria-valuemax="100">
                            <?php echo number_format($expense_percentage, 0); ?>%
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Content Row -->
<div class="row">
    <!-- Financial Goals -->
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Financial Goals</h6>
            </div>
            <div class="card-body">
                <?php if (!isset($goals) || $goals->num_rows === 0): ?>
                    <div class="text-center">
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
                        $progress_class = 'bg-info';
                        if ($progress >= 100) {
                            $progress_class = 'bg-success';
                        } else if ($progress >= 75) {
                            $progress_class = 'bg-warning';
                        }
                        ?>
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-grow-1">
                                <h4 class="small font-weight-bold"><?php echo htmlspecialchars($goal['name']); ?> 
                                    <span class="float-end"><?php echo number_format($progress, 0); ?>%</span>
                                </h4>
                                <div class="progress">
                                    <div class="progress-bar <?php echo $progress_class; ?>" role="progressbar" 
                                        style="width: <?php echo min(100, $progress); ?>%" 
                                        aria-valuenow="<?php echo $progress; ?>" 
                                        aria-valuemin="0" aria-valuemax="100">
                                    </div>
                                </div>
                                <small>
                                    $<?php echo number_format($goal['current_amount'], 2); ?> of 
                                    $<?php echo number_format($goal['target_amount'], 2); ?>
                                </small>
                            </div>
                        </div>
                    <?php endwhile; ?>
                    <div class="text-center mt-3">
                        <a href="<?php echo BASE_PATH; ?>/goals" class="btn btn-sm btn-primary">View All Goals</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Financial Advice -->
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Financial Advice</h6>
            </div>
            <div class="card-body">
                <?php if (!isset($financial_advice) || $financial_advice->num_rows === 0): ?>
                    <p>No financial advice available at this time.</p>
                <?php else: ?>
                    <div class="list-group">
                        <?php while ($advice = $financial_advice->fetch_assoc()): ?>
                            <?php 
                            $advice_class = 'list-group-item-info';
                            $advice_icon = 'info-circle';
                            
                            if ($advice['importance_level'] === 'high') {
                                $advice_class = 'list-group-item-danger';
                                $advice_icon = 'exclamation-circle';
                            } else if ($advice['importance_level'] === 'medium') {
                                $advice_class = 'list-group-item-warning';
                                $advice_icon = 'exclamation-triangle';
                            }
                            ?>
                            <div class="list-group-item <?php echo $advice_class; ?> mb-2">
                                <div class="d-flex w-100 justify-content-between">
                                    <h5 class="mb-1">
                                        <i class="fas fa-<?php echo $advice_icon; ?> me-2"></i>
                                        <?php echo htmlspecialchars($advice['title']); ?>
                                    </h5>
                                    <small><?php echo date('M j', strtotime($advice['generated_at'])); ?></small>
                                </div>
                                <p class="mb-1"><?php echo htmlspecialchars($advice['content']); ?></p>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
// JavaScript for charts (simplified to avoid errors)
$page_scripts = "
// Expense Categories Chart
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
        hoverBorderColor: 'rgba(234, 236, 244, 1)',
    }],
};

var expenseCategoryChart = new Chart(expenseCategoryCtx, {
    type: 'doughnut',
    data: expenseCategoryData,
    options: {
        maintainAspectRatio: false,
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom',
            },
            tooltip: {
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
    },
});

// Print dashboard button
document.getElementById('printDashboard').addEventListener('click', function() {
    window.print();
});

// Refresh dashboard button
document.getElementById('refreshDashboard').addEventListener('click', function() {
    location.reload();
});
";

// Include footer
require_once 'includes/footer.php';
?>