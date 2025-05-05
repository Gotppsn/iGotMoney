<?php
// Set page title and current page for menu highlighting
$page_title = 'Profile - iGotMoney';
$current_page = 'profile';

// Include header
require_once 'includes/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Financial Profile</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                <i class="fas fa-print"></i> Print
            </button>
        </div>
    </div>
</div>

<!-- Profile Summary -->
<div class="row mb-4">
    <div class="col-lg-8 mb-4">
        <div class="card shadow h-100">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Profile Overview</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- User Information -->
                    <div class="col-md-6">
                        <h4 class="small font-weight-bold">Personal Information</h4>
                        <div class="mb-4">
                            <div class="mb-2">
                                <strong>Name:</strong> <?php echo htmlspecialchars($user->first_name . ' ' . $user->last_name); ?>
                            </div>
                            <div class="mb-2">
                                <strong>Username:</strong> <?php echo htmlspecialchars($user->username); ?>
                            </div>
                            <div class="mb-2">
                                <strong>Email:</strong> <?php echo htmlspecialchars($user->email); ?>
                            </div>
                            <div>
                                <strong>Member Since:</strong> <?php echo date('F j, Y', strtotime($user->created_at)); ?>
                            </div>
                        </div>
                        
                        <h4 class="small font-weight-bold">Financial Summary</h4>
                        <div class="mb-4">
                            <div class="mb-2">
                                <strong>Monthly Income:</strong> $<?php echo number_format($monthly_income, 2); ?>
                            </div>
                            <div class="mb-2">
                                <strong>Monthly Expenses:</strong> $<?php echo number_format($monthly_expenses, 2); ?>
                            </div>
                            <div class="mb-2">
                                <strong>Monthly Net:</strong> 
                                <span class="<?php echo $monthly_net >= 0 ? 'text-success' : 'text-danger'; ?>">
                                    $<?php echo number_format(abs($monthly_net), 2); ?>
                                    <?php echo $monthly_net >= 0 ? '' : ' (deficit)'; ?>
                                </span>
                            </div>
                            <div>
                                <strong>Saving Rate:</strong> 
                                <span class="<?php echo $saving_rate >= 10 ? 'text-success' : ($saving_rate > 0 ? 'text-warning' : 'text-danger'); ?>">
                                    <?php echo number_format($saving_rate, 2); ?>%
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Financial Health Score -->
                    <div class="col-md-6">
                        <h4 class="small font-weight-bold">Financial Health Score</h4>
                        <div class="mb-4">
                            <div class="text-center mb-3">
                                <div class="display-4 font-weight-bold <?php echo getScoreColorClass($financial_health['score']); ?>">
                                    <?php echo $financial_health['score']; ?>/100
                                </div>
                                <div class="h5"><?php echo $financial_health['status']; ?></div>
                            </div>
                            
                            <?php foreach ($financial_health['breakdown'] as $key => $item): ?>
                                <div class="mb-2">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span><?php echo $item['label']; ?></span>
                                        <span><?php echo $item['score']; ?>/<?php echo $item['max']; ?></span>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar <?php echo getScoreProgressBarClass($item['score'], $item['max']); ?>" 
                                             role="progressbar" style="width: <?php echo ($item['score'] / $item['max']) * 100; ?>%" 
                                             aria-valuenow="<?php echo $item['score']; ?>" aria-valuemin="0" aria-valuemax="<?php echo $item['max']; ?>">
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="alert <?php echo getScoreAlertClass($financial_health['score']); ?>" role="alert">
                            <i class="fas fa-info-circle me-2"></i>
                            <?php echo getFinancialHealthAdvice($financial_health['score']); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4 mb-4">
        <div class="card shadow h-100">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Income vs. Expenses</h6>
            </div>
            <div class="card-body">
                <div class="chart-container mb-4">
                    <canvas id="incomeExpensesChart"></canvas>
                </div>
                
                <div class="text-center">
                    <div class="mb-1">
                        <strong>Monthly Surplus/Deficit:</strong>
                        <span class="h5 <?php echo $monthly_net >= 0 ? 'text-success' : 'text-danger'; ?>">
                            <?php echo $monthly_net >= 0 ? '+' : '-'; ?>$<?php echo number_format(abs($monthly_net), 2); ?>
                        </span>
                    </div>
                    <p class="mb-0 small">
                        <?php
                        if ($monthly_net > 0) {
                            echo 'You are saving ' . number_format($saving_rate, 2) . '% of your income each month.';
                        } else {
                            echo 'You are spending more than you earn each month.';
                        }
                        ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Financial Stats -->
<div class="row">
    <!-- Investment Overview -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow h-100">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Investment Overview</h6>
            </div>
            <div class="card-body">
                <?php if ($total_invested > 0): ?>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-4">
                                <div class="mb-2">
                                    <strong>Total Invested:</strong> $<?php echo number_format($total_invested, 2); ?>
                                </div>
                                <div class="mb-2">
                                    <strong>Current Value:</strong> $<?php echo number_format($current_investment_value, 2); ?>
                                </div>
                                <div>
                                    <strong>Total Gain/Loss:</strong> 
                                    <span class="<?php echo $investment_gain_loss >= 0 ? 'text-success' : 'text-danger'; ?>">
                                        <?php echo $investment_gain_loss >= 0 ? '+' : '-'; ?>$<?php echo number_format(abs($investment_gain_loss), 2); ?>
                                        (<?php echo number_format(abs($investment_gain_loss_percent), 2); ?>%)
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="chart-container">
                                <canvas id="investmentPieChart"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (isset($investment_summary['by_type'])): ?>
                        <h6 class="mt-4 mb-3">Investment Breakdown by Type</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Amount</th>
                                        <th>Percentage</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($investment_summary['by_type'] as $type => $data): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($type); ?></td>
                                            <td>$<?php echo number_format($data['current'], 2); ?></td>
                                            <td><?php echo number_format($data['percent'], 2); ?>%</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="text-center py-4">
                        <p>No investment data available.</p>
                        <a href="/investments" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus me-1"></i> Add Investment
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Budget Status -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow h-100">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Budget Status</h6>
            </div>
            <div class="card-body">
                <?php if (!empty($budget_status)): ?>
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span><strong>Overall Budget Utilization</strong></span>
                            <?php 
                            $total_budget = 0;
                            $total_spent = 0;
                            foreach ($budget_status as $budget_item) {
                                $total_budget += $budget_item['budget_amount'];
                                $total_spent += $budget_item['spent'];
                            }
                            $overall_percentage = ($total_budget > 0) ? ($total_spent / $total_budget) * 100 : 0;
                            ?>
                            <span><?php echo number_format($overall_percentage, 2); ?>%</span>
                        </div>
                        <?php 
                        $progress_class = 'bg-success';
                        if ($overall_percentage >= 90) {
                            $progress_class = 'bg-danger';
                        } else if ($overall_percentage >= 75) {
                            $progress_class = 'bg-warning';
                        }
                        ?>
                        <div class="progress mb-4">
                            <div class="progress-bar <?php echo $progress_class; ?>" role="progressbar" 
                                 style="width: <?php echo min(100, $overall_percentage); ?>%" 
                                 aria-valuenow="<?php echo $overall_percentage; ?>" aria-valuemin="0" aria-valuemax="100">
                            </div>
                        </div>
                    </div>
                    
                    <h6 class="mb-3">Budget Categories</h6>
                    <?php foreach ($budget_status as $budget_item): ?>
                        <h4 class="small font-weight-bold">
                            <?php echo htmlspecialchars($budget_item['category_name']); ?>
                            <span class="float-end">
                                <?php echo number_format($budget_item['percentage'], 0); ?>%
                            </span>
                        </h4>
                        <?php 
                        $progress_class = 'bg-success';
                        if ($budget_item['percentage'] >= 90) {
                            $progress_class = 'bg-danger';
                        } else if ($budget_item['percentage'] >= 75) {
                            $progress_class = 'bg-warning';
                        }
                        ?>
                        <div class="progress mb-4">
                            <div class="progress-bar <?php echo $progress_class; ?>" role="progressbar" 
                                 style="width: <?php echo min(100, $budget_item['percentage']); ?>%" 
                                 aria-valuenow="<?php echo $budget_item['percentage']; ?>" aria-valuemin="0" aria-valuemax="100">
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-4">
                        <p>No budget data available.</p>
                        <a href="/budget" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus me-1"></i> Create Budget
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
// Helper function to get score color class
function getScoreColorClass($score) {
    if ($score >= 85) return 'text-success';
    if ($score >= 70) return 'text-info';
    if ($score >= 50) return 'text-primary';
    if ($score >= 35) return 'text-warning';
    return 'text-danger';
}

// Helper function to get score progress bar class
function getScoreProgressBarClass($score, $max) {
    $percentage = ($score / $max) * 100;
    if ($percentage >= 80) return 'bg-success';
    if ($percentage >= 60) return 'bg-info';
    if ($percentage >= 40) return 'bg-primary';
    if ($percentage >= 20) return 'bg-warning';
    return 'bg-danger';
}

// Helper function to get score alert class
function getScoreAlertClass($score) {
    if ($score >= 85) return 'alert-success';
    if ($score >= 70) return 'alert-info';
    if ($score >= 50) return 'alert-primary';
    if ($score >= 35) return 'alert-warning';
    return 'alert-danger';
}

// Helper function to get financial health advice
function getFinancialHealthAdvice($score) {
    if ($score >= 85) {
        return 'Excellent financial health! You\'re saving consistently and have a good foundation. Consider optimizing your investments further.';
    } elseif ($score >= 70) {
        return 'Good financial health. You\'re on the right track. Focus on increasing your savings rate and diversifying investments.';
    } elseif ($score >= 50) {
        return 'Average financial health. Look for ways to reduce expenses and increase your savings rate to improve your financial position.';
    } elseif ($score >= 35) {
        return 'Below average financial health. Work on reducing your expense to income ratio and start building savings.';
    } else {
        return 'Your financial health needs attention. Focus on reducing expenses and debt, while finding ways to increase income.';
    }
}

// JavaScript for profile page
$page_scripts = "
// Income vs Expenses Chart
var incomeExpensesCtx = document.getElementById('incomeExpensesChart').getContext('2d');
var incomeExpensesData = {
    labels: ['Income', 'Expenses'],
    datasets: [{
        data: [$monthly_income, $monthly_expenses],
        backgroundColor: [
            '#1cc88a',
            '#e74a3b'
        ],
        hoverBackgroundColor: [
            '#17a673',
            '#be2617'
        ],
        hoverBorderColor: 'rgba(234, 236, 244, 1)',
    }],
};

var incomeExpensesChart = new Chart(incomeExpensesCtx, {
    type: 'doughnut',
    data: incomeExpensesData,
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

" . ($total_invested > 0 ? "
// Investment Type Chart
var investmentPieCtx = document.getElementById('investmentPieChart').getContext('2d');
var investmentTypeLabels = [];
var investmentTypeData = [];
var investmentTypeColors = [
    '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b',
    '#6f42c1', '#fd7e14', '#20c9a6', '#5a5c69', '#858796'
];
var investmentTypeHoverColors = [
    '#2e59d9', '#17a673', '#2c9faf', '#dda20a', '#be2617',
    '#5a32a3', '#db6a02', '#169b7f', '#3a3b45', '#60616f'
];

" . (isset($investment_summary['by_type']) ? "
// Extract investment type data
" . implode('', array_map(function($type, $data, $index) {
    return "investmentTypeLabels.push('$type');\ninvestmentTypeData.push({$data['current']});\n";
}, array_keys($investment_summary['by_type']), array_values($investment_summary['by_type']), array_keys(array_keys($investment_summary['by_type'])))) . "
" : "") . "

var investmentPieData = {
    labels: investmentTypeLabels,
    datasets: [{
        data: investmentTypeData,
        backgroundColor: investmentTypeColors.slice(0, investmentTypeLabels.length),
        hoverBackgroundColor: investmentTypeHoverColors.slice(0, investmentTypeLabels.length),
        hoverBorderColor: 'rgba(234, 236, 244, 1)',
    }],
};

var investmentPieChart = new Chart(investmentPieCtx, {
    type: 'pie',
    data: investmentPieData,
    options: {
        maintainAspectRatio: false,
        responsive: true,
        plugins: {
            legend: {
                position: 'right',
                align: 'start',
                labels: {
                    boxWidth: 12
                }
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
    },
});
" : "") . "
";

// Include footer
require_once 'includes/footer.php';
?>