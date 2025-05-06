<?php
// Set page title and current page for menu highlighting
$page_title = 'Investment Management - iGotMoney';
$current_page = 'investments';

// Additional JS
$additional_js = [
    '/assets/js/investments.js',
    '/assets/js/investment-performance.js'
];

// Include header
require_once 'includes/header.php';
?>
<!-- Add base path meta tag for JavaScript -->
<meta name="base-path" content="<?php echo BASE_PATH; ?>">

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Investment Management</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" id="refreshInvestmentData">
                <i class="fas fa-sync-alt me-1"></i> Refresh
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="exportInvestmentData">
                <i class="fas fa-file-export me-1"></i> Export
            </button>
        </div>
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addInvestmentModal">
            <i class="fas fa-plus"></i> Add Investment
        </button>
    </div>
</div>

<!-- Investment Portfolio Summary -->
<div class="row mb-4">
    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Portfolio Summary</h6>
                <div class="dropdown no-arrow">
                    <a class="dropdown-toggle" href="#" role="button" id="portfolioDropdown" 
                       data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end shadow animated--fade-in" 
                         aria-labelledby="portfolioDropdown">
                        <a class="dropdown-item" href="#" id="analyzePortfolioBtn">
                            <i class="fas fa-chart-pie fa-sm fa-fw me-2 text-gray-400"></i>
                            Analyze Portfolio
                        </a>
                        <a class="dropdown-item" href="#" id="setInvestmentGoalsBtn">
                            <i class="fas fa-bullseye fa-sm fa-fw me-2 text-gray-400"></i>
                            Set Investment Goals
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="#" id="portfolioSettingsBtn">
                            <i class="fas fa-cogs fa-sm fa-fw me-2 text-gray-400"></i>
                            Portfolio Settings
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <?php
                $total_invested = $investment_summary['total_invested'] ?? 0;
                $current_value = $investment_summary['current_value'] ?? 0;
                $total_gain_loss = $investment_summary['total_gain_loss'] ?? 0;
                $percent_gain_loss = $investment_summary['percent_gain_loss'] ?? 0;
                
                $gain_loss_class = $total_gain_loss >= 0 ? 'text-success' : 'text-danger';
                $gain_loss_icon = $total_gain_loss >= 0 ? 'arrow-up' : 'arrow-down';
                ?>
                
                <div class="text-center mb-4">
                    <h2 class="<?php echo $gain_loss_class; ?>">
                        <i class="fas fa-<?php echo $gain_loss_icon; ?> me-2"></i>
                        <?php echo number_format($percent_gain_loss, 2); ?>%
                    </h2>
                    <p class="text-muted mb-0">Total Return</p>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-6 text-center">
                        <h5>Invested</h5>
                        <h3 class="text-primary">$<?php echo number_format($total_invested, 2); ?></h3>
                    </div>
                    <div class="col-md-6 text-center">
                        <h5>Current Value</h5>
                        <h3 class="text-info">$<?php echo number_format($current_value, 2); ?></h3>
                    </div>
                </div>
                
                <div class="text-center mb-3">
                    <h5>Total Gain/Loss</h5>
                    <h3 class="<?php echo $gain_loss_class; ?>">
                        $<?php echo number_format(abs($total_gain_loss), 2); ?>
                        <small>(<?php echo $total_gain_loss >= 0 ? '+' : '-'; ?>)</small>
                    </h3>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Portfolio Performance</h6>
                <div class="dropdown no-arrow">
                    <a class="dropdown-toggle" href="#" role="button" id="performanceDropdown" 
                       data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end shadow animated--fade-in" 
                         aria-labelledby="performanceDropdown">
                        <a class="dropdown-item" href="#" id="compareToMarketBtn">
                            <i class="fas fa-exchange-alt fa-sm fa-fw me-2 text-gray-400"></i>
                            Compare to Market
                        </a>
                        <a class="dropdown-item" href="#" id="showProjectionsBtn">
                            <i class="fas fa-chart-line fa-sm fa-fw me-2 text-gray-400"></i>
                            Show Projections
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div id="performanceChartContainer" style="height: 300px;">
                    <!-- Performance chart will be rendered here -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ROI Analysis -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">ROI Analysis</h6>
            </div>
            <div class="card-body" id="roiCalculatorContainer">
                <!-- ROI calculator will be rendered here -->
            </div>
        </div>
    </div>
</div>

<!-- Portfolio Allocation -->
<div class="row mb-4">
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Allocation by Type</h6>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="investmentTypeChart"></canvas>
                </div>
                <div class="text-center mt-2">
                    <small class="text-muted">Portfolio Distribution by Investment Type</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Allocation by Risk</h6>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="riskLevelChart"></canvas>
                </div>
                <div class="text-center mt-2">
                    <small class="text-muted">Portfolio Distribution by Risk Level</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stock Buy/Sell Analysis -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Stock Buy/Sell Analysis</h6>
            </div>
            <div class="card-body" id="stockAnalysisContainer">
                <!-- Stock analysis will be rendered here -->
            </div>
        </div>
    </div>
</div>

<!-- Investment Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Your Investments</h6>
        <div class="dropdown no-arrow">
            <a class="dropdown-toggle" href="#" role="button" id="investmentTableDropdown" 
               data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-end shadow animated--fade-in" 
                 aria-labelledby="investmentTableDropdown">
                <a class="dropdown-item" href="#" id="filterInvestmentsBtn">
                    <i class="fas fa-filter fa-sm fa-fw me-2 text-gray-400"></i>
                    Filter
                </a>
                <a class="dropdown-item" href="#" id="sortInvestmentsBtn">
                    <i class="fas fa-sort fa-sm fa-fw me-2 text-gray-400"></i>
                    Sort
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#" id="updateAllPricesBtn">
                    <i class="fas fa-sync fa-sm fa-fw me-2 text-gray-400"></i>
                    Update All Prices
                </a>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <div class="input-group">
                <span class="input-group-text">
                    <i class="fas fa-search"></i>
                </span>
                <input type="text" class="form-control" id="investmentSearch" placeholder="Search investments...">
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered" id="investmentTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Purchase Date</th>
                        <th>Purchase Price</th>
                        <th>Quantity</th>
                        <th>Current Price</th>
                        <th>Current Value</th>
                        <th>Gain/Loss</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($investments && $investments->num_rows > 0): 
                        while ($investment = $investments->fetch_assoc()): 
                            $purchase_value = $investment['purchase_price'] * $investment['quantity'];
                            $current_value = $investment['current_price'] * $investment['quantity'];
                            $gain_loss = $current_value - $purchase_value;
                            $percent_change = $purchase_value > 0 ? ($gain_loss / $purchase_value) * 100 : 0;
                            
                            $gain_loss_class = $gain_loss >= 0 ? 'text-success' : 'text-danger';
                            $gain_loss_arrow = $gain_loss >= 0 ? 'up' : 'down';
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($investment['name']); ?></td>
                            <td>
                                <?php echo htmlspecialchars($investment['type_name']); ?>
                                <span class="badge bg-<?php echo getRiskBadgeClass($investment['risk_level']); ?>">
                                    <?php echo ucfirst($investment['risk_level']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($investment['purchase_date']); ?></td>
                            <td>$<?php echo number_format($investment['purchase_price'], 2); ?></td>
                            <td><?php echo number_format($investment['quantity'], 6); ?></td>
                            <td>$<?php echo number_format($investment['current_price'], 2); ?></td>
                            <td>$<?php echo number_format($current_value, 2); ?></td>
                            <td class="<?php echo $gain_loss_class; ?>">
                                <i class="fas fa-arrow-<?php echo $gain_loss_arrow; ?> me-1"></i>
                                $<?php echo number_format(abs($gain_loss), 2); ?> 
                                (<?php echo number_format(abs($percent_change), 2); ?>%)
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-primary update-price" 
                                        data-investment-id="<?php echo $investment['investment_id']; ?>"
                                        data-current-price="<?php echo $investment['current_price']; ?>">
                                    <i class="fas fa-dollar-sign"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-info edit-investment" 
                                        data-investment-id="<?php echo $investment['investment_id']; ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-danger delete-investment" 
                                        data-investment-id="<?php echo $investment['investment_id']; ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php 
                        endwhile; 
                    else: 
                    ?>
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <p class="mb-0">No investments found. Add your first investment to get started.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Investment Modal -->
<div class="modal fade" id="addInvestmentModal" tabindex="-1" aria-labelledby="addInvestmentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addInvestmentModalLabel">Add Investment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?php echo BASE_PATH; ?>/investments" method="post">
                <input type="hidden" name="action" value="add">
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="type_id" class="form-label">Investment Type</label>
                        <select class="form-select" id="type_id" name="type_id" required>
                            <?php 
                            // Reset the pointer to the beginning of the result set
                            $investment_types->data_seek(0);
                            
                            while ($type = $investment_types->fetch_assoc()): 
                            ?>
                                <option value="<?php echo $type['type_id']; ?>" data-risk="<?php echo $type['risk_level']; ?>">
                                    <?php echo htmlspecialchars($type['name']); ?> (<?php echo ucfirst($type['risk_level']); ?> Risk)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Investment Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="ticker_symbol" class="form-label">Ticker Symbol (Optional)</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="ticker_symbol" name="ticker_symbol">
                            <button class="btn btn-outline-secondary" type="button" id="lookupTickerBtn">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                        <div class="form-text">For stocks and ETFs (e.g., AAPL, VOO)</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="purchase_date" class="form-label">Purchase Date</label>
                        <input type="date" class="form-control" id="purchase_date" name="purchase_date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="purchase_price" class="form-label">Purchase Price</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="purchase_price" name="purchase_price" step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="quantity" class="form-label">Quantity</label>
                            <input type="number" class="form-control" id="quantity" name="quantity" step="0.000001" min="0" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="current_price" class="form-label">Current Price</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="current_price" name="current_price" step="0.01" min="0">
                            <button class="btn btn-outline-secondary" type="button" id="fetchPriceBtn" disabled>
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                        <div class="form-text">Leave blank to use purchase price as current price</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Investment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Investment Modal -->
<div class="modal fade" id="editInvestmentModal" tabindex="-1" aria-labelledby="editInvestmentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editInvestmentModalLabel">Edit Investment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?php echo BASE_PATH; ?>/investments" method="post">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="investment_id" id="edit_investment_id">
                
                <div class="modal-body">
                    <!-- Content will be dynamically loaded -->
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-3">Loading investment data...</p>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Investment Modal -->
<div class="modal fade" id="deleteInvestmentModal" tabindex="-1" aria-labelledby="deleteInvestmentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteInvestmentModalLabel">Delete Investment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this investment? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="<?php echo BASE_PATH; ?>/investments" method="post">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="investment_id" id="delete_investment_id">
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Update Price Modal -->
<div class="modal fade" id="updatePriceModal" tabindex="-1" aria-labelledby="updatePriceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updatePriceModalLabel">Update Current Price</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?php echo BASE_PATH; ?>/investments" method="post">
                <input type="hidden" name="action" value="update_price">
                <input type="hidden" name="investment_id" id="update_investment_id">
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="update_current_price" class="form-label">Current Price</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="update_current_price" name="current_price" step="0.01" min="0" required>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Price</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Stock Lookup Modal -->
<div class="modal fade" id="stockLookupModal" tabindex="-1" aria-labelledby="stockLookupModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="stockLookupModalLabel">Stock Symbol Lookup</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="companySearchInput" class="form-label">Search for a company</label>
                    <input type="text" class="form-control" id="companySearchInput" placeholder="Enter company name...">
                </div>
                <div id="lookupResults" class="mt-3">
                    <div class="alert alert-info">
                        Enter a company name to search for its stock symbol.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Portfolio Analysis Modal -->
<div class="modal fade" id="portfolioAnalysisModal" tabindex="-1" aria-labelledby="portfolioAnalysisModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="portfolioAnalysisModalLabel">Portfolio Analysis</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3">Analyzing your portfolio...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Helper function to get risk badge class
function getRiskBadgeClass($risk_level) {
    switch ($risk_level) {
        case 'very low':
            return 'success';
        case 'low':
            return 'info';
        case 'moderate':
            return 'primary';
        case 'high':
            return 'warning';
        case 'very high':
            return 'danger';
        default:
            return 'secondary';
    }
}

// Chart data
$typeChartData = [];
$typeChartLabels = [];
$typeChartColors = [
    '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b',
    '#6f42c1', '#fd7e14', '#20c9a6', '#5a5c69', '#858796'
];

$riskChartData = [];
$riskChartLabels = [];
$riskChartColors = [
    'very low' => '#1cc88a', // Green
    'low' => '#36b9cc',      // Cyan
    'moderate' => '#f6c23e', // Yellow
    'high' => '#e74a3b',     // Red
    'very high' => '#e74a3b' // Red
];

// Prepare chart data
if (isset($investment_summary['by_type']) && !empty($investment_summary['by_type'])) {
    foreach ($investment_summary['by_type'] as $type => $data) {
        $typeChartLabels[] = $type;
        $typeChartData[] = $data['current'];
    }
}

if (isset($investment_summary['by_risk']) && !empty($investment_summary['by_risk'])) {
    foreach ($investment_summary['by_risk'] as $risk => $data) {
        $riskChartLabels[] = ucfirst($risk);
        $riskChartData[] = $data['current'];
    }
}

// JavaScript for investments page
$page_scripts = "
// Initialize the type chart
var typeChartCtx = document.getElementById('investmentTypeChart').getContext('2d');
var investmentTypeChart = new Chart(typeChartCtx, {
    type: 'doughnut',
    data: {
        labels: " . json_encode($typeChartLabels) . ",
        datasets: [{
            data: " . json_encode($typeChartData) . ",
            backgroundColor: " . json_encode(array_slice($typeChartColors, 0, count($typeChartData))) . ",
            hoverBackgroundColor: " . json_encode(array_map(function($color) {
                return adjustColor($color, -20);
            }, array_slice($typeChartColors, 0, count($typeChartData)))) . ",
            hoverBorderColor: 'rgba(234, 236, 244, 1)',
        }]
    },
    options: {
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
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
        cutout: '60%',
    }
});

// Initialize the risk chart
var riskChartCtx = document.getElementById('riskLevelChart').getContext('2d');
var riskLevelChart = new Chart(riskChartCtx, {
    type: 'doughnut',
    data: {
        labels: " . json_encode($riskChartLabels) . ",
        datasets: [{
            data: " . json_encode($riskChartData) . ",
            backgroundColor: " . json_encode(array_map(function($risk) use ($riskChartColors) {
                return $riskChartColors[strtolower($risk)] ?? '#858796';
            }, $riskChartLabels)) . ",
            hoverBackgroundColor: " . json_encode(array_map(function($risk) use ($riskChartColors) {
                $color = $riskChartColors[strtolower($risk)] ?? '#858796';
                return adjustColor($color, -20);
            }, $riskChartLabels)) . ",
            hoverBorderColor: 'rgba(234, 236, 244, 1)',
        }]
    },
    options: {
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
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
                        return label + ' Risk: $' + value.toFixed(2) + ' (' + percentage + '%)';
                    }
                }
            }
        },
        cutout: '60%',
    }
});

// Add event listeners for investment management

// Export data button
document.getElementById('exportInvestmentData').addEventListener('click', function() {
    // Simulate export functionality
    alert('Exporting investment data...');
    // In a real implementation, this would generate a CSV or Excel file
});

// Refresh data button
document.getElementById('refreshInvestmentData').addEventListener('click', function() {
    // Show loading indicator
    this.innerHTML = '<i class=\"fas fa-spinner fa-spin me-1\"></i> Refreshing...';
    this.disabled = true;
    
    // Simulate data refresh
    setTimeout(() => {
        window.location.reload();
    }, 1000);
});

// Stock lookup button
document.getElementById('lookupTickerBtn').addEventListener('click', function() {
    // Show stock lookup modal
    var modal = new bootstrap.Modal(document.getElementById('stockLookupModal'));
    modal.show();
});

// Company search input
const companySearchInput = document.getElementById('companySearchInput');
if (companySearchInput) {
    companySearchInput.addEventListener('input', function() {
        const searchTerm = this.value.trim();
        
        if (searchTerm.length < 2) {
            document.getElementById('lookupResults').innerHTML = `
                <div class=\"alert alert-info\">
                    Enter a company name to search for its stock symbol.
                </div>
            `;
            return;
        }
        
        // Show loading indicator
        document.getElementById('lookupResults').innerHTML = `
            <div class=\"text-center py-3\">
                <div class=\"spinner-border text-primary\" role=\"status\">
                    <span class=\"visually-hidden\">Loading...</span>
                </div>
                <p class=\"mt-2\">Searching...</p>
            </div>
        `;
        
        // Simulate API call to search for company
        setTimeout(() => {
            // Mock results (in a real app, this would come from an API)
            const results = [
                { symbol: 'AAPL', name: 'Apple Inc.', exchange: 'NASDAQ' },
                { symbol: 'MSFT', name: 'Microsoft Corporation', exchange: 'NASDAQ' },
                { symbol: 'GOOGL', name: 'Alphabet Inc.', exchange: 'NASDAQ' },
                { symbol: 'AMZN', name: 'Amazon.com Inc.', exchange: 'NASDAQ' },
                { symbol: 'META', name: 'Meta Platforms Inc.', exchange: 'NASDAQ' }
            ].filter(company => 
                company.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                company.symbol.toLowerCase().includes(searchTerm.toLowerCase())
            );
            
            if (results.length === 0) {
                document.getElementById('lookupResults').innerHTML = `
                    <div class=\"alert alert-warning\">
                        No companies found matching your search.
                    </div>
                `;
                return;
            }
            
            let resultsHTML = `
                <div class=\"table-responsive\">
                    <table class=\"table table-hover\">
                        <thead>
                            <tr>
                                <th>Symbol</th>
                                <th>Company Name</th>
                                <th>Exchange</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            results.forEach(company => {
                resultsHTML += `
                    <tr>
                        <td><strong>${company.symbol}</strong></td>
                        <td>${company.name}</td>
                        <td>${company.exchange}</td>
                        <td>
                            <button type=\"button\" class=\"btn btn-sm btn-primary select-symbol\" 
                                data-symbol=\"${company.symbol}\" 
                                data-company=\"${company.name}\">
                                Select
                            </button>
                        </td>
                    </tr>
                `;
            });
            
            resultsHTML += `
                        </tbody>
                    </table>
                </div>
            `;
            
            document.getElementById('lookupResults').innerHTML = resultsHTML;
            
            // Add event listeners to select buttons
            document.querySelectorAll('.select-symbol').forEach(button => {
                button.addEventListener('click', function() {
                    const symbol = this.getAttribute('data-symbol');
                    const company = this.getAttribute('data-company');
                    
                    // Fill ticker symbol input
                    document.getElementById('ticker_symbol').value = symbol;
                    
                    // If name field is empty, fill it too
                    if (!document.getElementById('name').value) {
                        document.getElementById('name').value = company;
                    }
                    
                    // Enable fetch price button
                    document.getElementById('fetchPriceBtn').disabled = false;
                    
                    // Close modal
                    bootstrap.Modal.getInstance(document.getElementById('stockLookupModal')).hide();
                });
            });
        }, 800);
    });
}

// Initialize fetch price button
const fetchPriceBtn = document.getElementById('fetchPriceBtn');
if (fetchPriceBtn) {
    fetchPriceBtn.addEventListener('click', function() {
        const ticker = document.getElementById('ticker_symbol').value;
        
        if (!ticker) {
            alert('Please enter a ticker symbol first.');
            return;
        }
        
        // Show loading state
        this.disabled = true;
        this.innerHTML = '<i class=\"fas fa-spinner fa-spin\"></i>';
        
        // Simulate fetching current price
        setTimeout(() => {
            // Generate a random price for demo
            const randomPrice = (Math.random() * 100 + 50).toFixed(2);
            document.getElementById('current_price').value = randomPrice;
            
            // Reset button
            this.disabled = false;
            this.innerHTML = '<i class=\"fas fa-sync-alt\"></i>';
        }, 1000);
    });
}

// Initialize search functionality
const investmentSearch = document.getElementById('investmentSearch');
if (investmentSearch) {
    investmentSearch.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const table = document.getElementById('investmentTable');
        const rows = table.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const match = text.includes(searchTerm);
            row.style.display = match ? '' : 'none';
        });
    });
}

// Initialize portfolio analysis button
document.getElementById('analyzePortfolioBtn').addEventListener('click', function() {
    const modal = new bootstrap.Modal(document.getElementById('portfolioAnalysisModal'));
    modal.show();
    
    // Load analysis content after delay (simulating calculation)
    setTimeout(() => {
        document.querySelector('#portfolioAnalysisModal .modal-body').innerHTML = `
            <div class=\"card border-primary mb-3\">
                <div class=\"card-header bg-primary text-white\">
                    <h5 class=\"mb-0\">Portfolio Analysis Results</h5>
                </div>
                <div class=\"card-body\">
                    <div class=\"row mb-3\">
                        <div class=\"col-md-6\">
                            <div class=\"card border-0 shadow-sm\">
                                <div class=\"card-body text-center\">
                                    <h6 class=\"card-title\">Diversification Score</h6>
                                    <div class=\"display-4 text-primary\">7/10</div>
                                    <p class=\"text-muted\">Well diversified portfolio</p>
                                </div>
                            </div>
                        </div>
                        <div class=\"col-md-6\">
                            <div class=\"card border-0 shadow-sm\">
                                <div class=\"card-body text-center\">
                                    <h6 class=\"card-title\">Risk Assessment</h6>
                                    <div class=\"display-4 text-warning\">Moderate</div>
                                    <p class=\"text-muted\">Balanced risk profile</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <h6 class=\"mb-3\">Recommendations</h6>
                    <ul class=\"list-group mb-3\">
                        <li class=\"list-group-item\">
                            <i class=\"fas fa-lightbulb text-warning me-2\"></i>
                            Consider adding more fixed income investments to better balance your portfolio.
                        </li>
                        <li class=\"list-group-item\">
                            <i class=\"fas fa-lightbulb text-warning me-2\"></i>
                            Your portfolio is heavily concentrated in technology stocks. Consider diversifying into other sectors.
                        </li>
                        <li class=\"list-group-item\">
                            <i class=\"fas fa-lightbulb text-warning me-2\"></i>
                            Regular rebalancing can help maintain your target asset allocation.
                        </li>
                    </ul>
                    
                    <div class=\"alert alert-info\">
                        <i class=\"fas fa-info-circle me-2\"></i>
                        <small>This analysis is based on current portfolio data and general investment principles. 
                        For personalized financial advice, consult with a qualified financial advisor.</small>
                    </div>
                </div>
            </div>
        `;
    }, 1500);
});

// Initialize investment goals button
document.getElementById('setInvestmentGoalsBtn').addEventListener('click', function() {
    alert('Setting investment goals functionality will be implemented in the next update.');
});

// Initialize portfolio settings button
document.getElementById('portfolioSettingsBtn').addEventListener('click', function() {
    alert('Portfolio settings functionality will be implemented in the next update.');
});

// Initialize compare to market button
document.getElementById('compareToMarketBtn').addEventListener('click', function() {
    alert('Market comparison functionality will be implemented in the next update.');
});

// Initialize show projections button
document.getElementById('showProjectionsBtn').addEventListener('click', function() {
    alert('Investment projections functionality will be implemented in the next update.');
});

// Initialize filter investments button
document.getElementById('filterInvestmentsBtn').addEventListener('click', function() {
    alert('Investment filtering functionality will be implemented in the next update.');
});

// Initialize sort investments button
document.getElementById('sortInvestmentsBtn').addEventListener('click', function() {
    alert('Investment sorting functionality will be implemented in the next update.');
});

// Initialize update all prices button
document.getElementById('updateAllPricesBtn').addEventListener('click', function() {
    if (confirm('Do you want to update prices for all investments with ticker symbols?')) {
        this.innerHTML = '<i class=\"fas fa-spinner fa-spin me-1\"></i> Updating...';
        this.disabled = true;
        
        // Simulate updating prices
        setTimeout(() => {
            this.innerHTML = '<i class=\"fas fa-sync fa-sm fa-fw me-2 text-gray-400\"></i> Update All Prices';
            this.disabled = false;
            alert('All prices have been updated successfully.');
            window.location.reload();
        }, 2000);
    }
});

// Log chart initialization
console.log('Charts initialized');
";

// Function to adjust color brightness
function adjustColor($hex, $steps) {
    // Extract RGB values
    $hex = str_replace('#', '', $hex);
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    
    // Adjust brightness
    $r = max(0, min(255, $r + $steps));
    $g = max(0, min(255, $g + $steps));
    $b = max(0, min(255, $b + $steps));
    
    // Convert back to hex
    return sprintf('#%02x%02x%02x', $r, $g, $b);
}

// Include footer
require_once 'includes/footer.php';
?>