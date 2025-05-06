<?php
// Set page title and current page for menu highlighting
$page_title = 'Investment Management - iGotMoney';
$current_page = 'investments';

// Additional JS
$additional_js = ['/assets/js/investments.js'];

// Include header
require_once 'includes/header.php';
?>
<!-- Add base path meta tag for JavaScript -->
<meta name="base-path" content="<?php echo BASE_PATH; ?>">

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Investment Management</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addInvestmentModal">
            <i class="fas fa-plus"></i> Add Investment
        </button>
    </div>
</div>

<!-- Investment Portfolio Summary -->
<div class="row mb-4">
    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Portfolio Summary</h6>
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
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Portfolio Allocation</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="chart-container">
                            <canvas id="investmentTypeChart"></canvas>
                        </div>
                        <div class="text-center mt-2">
                            <small class="text-muted">By Investment Type</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="chart-container">
                            <canvas id="riskLevelChart"></canvas>
                        </div>
                        <div class="text-center mt-2">
                            <small class="text-muted">By Risk Level</small>
                        </div>
                    </div>
                </div>
            </div>
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
                        <input type="text" class="form-control" id="ticker_symbol" name="ticker_symbol">
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
                        <label for="current_price" class="form-label">Current Price (Optional)</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="current_price" name="current_price" step="0.01" min="0">
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

<?php
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