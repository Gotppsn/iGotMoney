<?php
// Set page title and current page for menu highlighting
$page_title = 'Investment Management - iGotMoney';
$current_page = 'investments';

// Additional JS
$additional_js = ['/assets/js/investments.js'];

// Additional CSS
$additional_css = ['/assets/css/investments-modern.css'];

// Include header
require_once 'includes/header.php';
?>

<div class="page-header d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
    <div>
        <h1 class="page-title">Investment Portfolio</h1>
        <p class="page-subtitle text-muted">Manage and track your investment performance</p>
    </div>
    <div class="page-actions">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addInvestmentModal">
            <i class="fas fa-plus-circle me-2"></i> Add Investment
        </button>
    </div>
</div>

<!-- Overall Performance Summary -->
<div class="row mb-4">
    <div class="col-lg-4">
        <div class="card performance-summary-card h-100">
            <div class="card-body">
                <?php
                $total_invested = $investment_summary['total_invested'] ?? 0;
                $current_value = $investment_summary['current_value'] ?? 0;
                $total_gain_loss = $investment_summary['total_gain_loss'] ?? 0;
                $percent_gain_loss = $investment_summary['percent_gain_loss'] ?? 0;
                
                $gain_loss_class = $total_gain_loss >= 0 ? 'text-success' : 'text-danger';
                $gain_loss_icon = $total_gain_loss >= 0 ? 'arrow-up' : 'arrow-down';
                ?>
                
                <h6 class="card-subtitle mb-4">Portfolio Performance</h6>
                
                <div class="text-center performance-highlight mb-4">
                    <div class="performance-circle <?php echo $total_gain_loss >= 0 ? 'bg-soft-success' : 'bg-soft-danger'; ?>">
                        <h2 class="<?php echo $gain_loss_class; ?>">
                            <i class="fas fa-<?php echo $gain_loss_icon; ?>"></i>
                            <?php echo number_format(abs($percent_gain_loss), 2); ?>%
                        </h2>
                    </div>
                    <p class="text-muted mb-0">Total Return</p>
                </div>
                
                <div class="row stats-row g-0">
                    <div class="col-6 text-center">
                        <div class="stats-item">
                            <h6 class="stats-label">Invested</h6>
                            <h3 class="stats-value">$<?php echo number_format($total_invested, 2); ?></h3>
                        </div>
                    </div>
                    <div class="col-6 text-center">
                        <div class="stats-item">
                            <h6 class="stats-label">Current Value</h6>
                            <h3 class="stats-value text-primary">$<?php echo number_format($current_value, 2); ?></h3>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-3">
                    <div class="gain-loss-indicator">
                        <h6 class="stats-label">Total Gain/Loss</h6>
                        <h3 class="stats-value <?php echo $gain_loss_class; ?>">
                            $<?php echo number_format(abs($total_gain_loss), 2); ?>
                            <small>(<?php echo $total_gain_loss >= 0 ? '+' : '-'; ?>)</small>
                        </h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-8">
        <div class="card allocation-card h-100">
            <div class="card-body">
                <h6 class="card-subtitle mb-4">Portfolio Allocation</h6>
                <div class="row">
                    <div class="col-md-6">
                        <div class="chart-container">
                            <canvas id="investmentTypeChart"></canvas>
                        </div>
                        <div class="text-center mt-2">
                            <p class="text-muted chart-label">By Investment Type</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="chart-container">
                            <canvas id="riskLevelChart"></canvas>
                        </div>
                        <div class="text-center mt-2">
                            <p class="text-muted chart-label">By Risk Level</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Top & Bottom Performers -->
<div class="row mb-4">
    <div class="col-lg-6">
        <div class="card performers-card h-100">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-chart-line text-success me-2"></i>Top Performers
                </h6>
            </div>
            <div class="card-body">
                <?php if (isset($investment_summary['top_performers']) && !empty($investment_summary['top_performers'])): ?>
                    <div class="table-responsive">
                        <table class="table table-borderless performers-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Return</th>
                                    <th>Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($investment_summary['top_performers'] as $investment): ?>
                                    <tr>
                                        <td class="investment-name">
                                            <div class="d-flex align-items-center">
                                                <div class="investment-icon bg-soft-primary me-2">
                                                    <i class="fas fa-chart-pie"></i>
                                                </div>
                                                <div>
                                                    <?php echo htmlspecialchars($investment['name']); ?>
                                                    <?php if (!empty($investment['ticker'])): ?>
                                                        <span class="ticker-symbol"><?php echo htmlspecialchars($investment['ticker']); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($investment['type']); ?></td>
                                        <td class="<?php echo $investment['percent_gain_loss'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                                            <i class="fas fa-<?php echo $investment['percent_gain_loss'] >= 0 ? 'arrow-up' : 'arrow-down'; ?> me-1"></i>
                                            <?php echo number_format($investment['percent_gain_loss'], 2); ?>%
                                        </td>
                                        <td class="text-end">$<?php echo number_format($investment['current'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <p>No investment data available yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="card performers-card h-100">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-chart-line text-danger me-2"></i>Worst Performers
                </h6>
            </div>
            <div class="card-body">
                <?php if (isset($investment_summary['worst_performers']) && !empty($investment_summary['worst_performers'])): ?>
                    <div class="table-responsive">
                        <table class="table table-borderless performers-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Return</th>
                                    <th>Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($investment_summary['worst_performers'] as $investment): ?>
                                    <tr>
                                        <td class="investment-name">
                                            <div class="d-flex align-items-center">
                                                <div class="investment-icon bg-soft-secondary me-2">
                                                    <i class="fas fa-chart-pie"></i>
                                                </div>
                                                <div>
                                                    <?php echo htmlspecialchars($investment['name']); ?>
                                                    <?php if (!empty($investment['ticker'])): ?>
                                                        <span class="ticker-symbol"><?php echo htmlspecialchars($investment['ticker']); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($investment['type']); ?></td>
                                        <td class="<?php echo $investment['percent_gain_loss'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                                            <i class="fas fa-<?php echo $investment['percent_gain_loss'] >= 0 ? 'arrow-up' : 'arrow-down'; ?> me-1"></i>
                                            <?php echo number_format($investment['percent_gain_loss'], 2); ?>%
                                        </td>
                                        <td class="text-end">$<?php echo number_format($investment['current'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <p>No investment data available yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Investment List -->
<div class="card investments-list-card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="card-title mb-0">
            <i class="fas fa-list-ul me-2"></i>Your Investments
        </h6>
        <div class="d-flex">
            <div class="search-container">
                <span class="input-group-text">
                    <i class="fas fa-search"></i>
                </span>
                <input type="text" class="form-control" placeholder="Search investments..." id="investmentSearch" data-table-search="investmentTable">
            </div>
        </div>
    </div>
    <div class="card-body">
        <?php if (isset($investments) && $investments && $investments->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table investments-table" id="investmentTable">
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
                        <?php while ($investment_item = $investments->fetch_assoc()): ?>
                            <?php
                            $purchase_value = $investment_item['purchase_price'] * $investment_item['quantity'];
                            $current_value = $investment_item['current_price'] * $investment_item['quantity'];
                            $gain_loss = $current_value - $purchase_value;
                            $percent_gain_loss = $purchase_value > 0 ? ($gain_loss / $purchase_value) * 100 : 0;
                            
                            $gain_loss_class = $gain_loss >= 0 ? 'text-success' : 'text-danger';
                            $gain_loss_icon = $gain_loss >= 0 ? 'arrow-up' : 'arrow-down';
                            ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="investment-icon bg-soft-primary me-2">
                                            <i class="fas fa-<?php echo !empty($investment_item['ticker_symbol']) ? 'chart-line' : 'landmark'; ?>"></i>
                                        </div>
                                        <div>
                                            <?php echo htmlspecialchars($investment_item['name']); ?>
                                            <?php if (!empty($investment_item['ticker_symbol'])): ?>
                                                <span class="ticker-symbol"><?php echo htmlspecialchars($investment_item['ticker_symbol']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge
                                        <?php
                                        switch ($investment_item['risk_level']) {
                                            case 'very low':
                                                echo 'bg-success';
                                                break;
                                            case 'low':
                                                echo 'bg-info';
                                                break;
                                            case 'moderate':
                                                echo 'bg-primary';
                                                break;
                                            case 'high':
                                                echo 'bg-warning';
                                                break;
                                            case 'very high':
                                                echo 'bg-danger';
                                                break;
                                            default:
                                                echo 'bg-secondary';
                                        }
                                        ?>">
                                        <?php echo htmlspecialchars($investment_item['type_name']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($investment_item['purchase_date'])); ?></td>
                                <td>$<?php echo number_format($investment_item['purchase_price'], 2); ?></td>
                                <td><?php echo number_format($investment_item['quantity'], 6); ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span>$<?php echo number_format($investment_item['current_price'], 2); ?></span>
                                        <button class="btn btn-sm btn-icon btn-outline-primary ms-2 update-price" 
                                               data-investment-id="<?php echo $investment_item['investment_id']; ?>"
                                               data-current-price="<?php echo $investment_item['current_price']; ?>">
                                            <i class="fas fa-sync-alt"></i>
                                        </button>
                                    </div>
                                </td>
                                <td class="fw-semibold">$<?php echo number_format($current_value, 2); ?></td>
                                <td class="<?php echo $gain_loss_class; ?>">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-<?php echo $gain_loss_icon; ?> me-1"></i>
                                        <div>
                                            <div>$<?php echo number_format(abs($gain_loss), 2); ?></div>
                                            <div class="small">(<?php echo number_format($percent_gain_loss, 2); ?>%)</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button type="button" class="btn btn-sm btn-icon btn-outline-info edit-investment" data-investment-id="<?php echo $investment_item['investment_id']; ?>" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-icon btn-outline-danger delete-investment" data-investment-id="<?php echo $investment_item['investment_id']; ?>" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fas fa-chart-pie"></i>
                </div>
                <h4>No investments recorded yet</h4>
                <p class="text-muted">Start by adding your first investment to track its performance.</p>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addInvestmentModal">
                    <i class="fas fa-plus"></i> Add Your First Investment
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Investment Tips -->
<div class="card tips-card mb-4">
    <div class="card-header">
        <h6 class="card-title mb-0">
            <i class="fas fa-lightbulb me-2 text-warning"></i>Investment Tips
        </h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4 mb-3 mb-md-0">
                <div class="tip-card">
                    <div class="tip-icon bg-soft-info">
                        <i class="fas fa-sliders-h"></i>
                    </div>
                    <h5>Diversification</h5>
                    <p>Spread your investments across different asset classes to reduce risk and increase potential returns.</p>
                </div>
            </div>
            <div class="col-md-4 mb-3 mb-md-0">
                <div class="tip-card">
                    <div class="tip-icon bg-soft-warning">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h5>Risk Assessment</h5>
                    <p>Assess your risk tolerance based on your age, financial goals, and time horizon before making investment decisions.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="tip-card">
                    <div class="tip-icon bg-soft-success">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <h5>Regular Contributions</h5>
                    <p>Consistent investing, even in small amounts, can lead to significant growth over time due to compound interest.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Investment Modal -->
<div class="modal fade" id="addInvestmentModal" tabindex="-1" aria-labelledby="addInvestmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addInvestmentModalLabel">
                    <i class="fas fa-plus-circle me-2"></i>Add New Investment
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?php echo BASE_PATH; ?>/investments" method="post" class="investment-form">
                <input type="hidden" name="action" value="add">
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="type_id" class="form-label">Investment Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="type_id" name="type_id" required>
                            <option value="" selected disabled>Select investment type</option>
                            <?php if ($investment_types && $investment_types->num_rows > 0): ?>
                                <?php while ($type = $investment_types->fetch_assoc()): ?>
                                    <option value="<?php echo $type['type_id']; ?>" data-risk="<?php echo $type['risk_level']; ?>">
                                        <?php echo htmlspecialchars($type['name']); ?> 
                                        <span class="risk-level">(<?php echo ucfirst($type['risk_level']); ?> Risk)</span>
                                    </option>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Investment Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="Enter investment name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="ticker_symbol" class="form-label">Ticker Symbol</label>
                        <input type="text" class="form-control" id="ticker_symbol" name="ticker_symbol" placeholder="E.g., AAPL, VOO">
                        <div class="form-text">For stocks and ETFs (e.g., AAPL, VOO)</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="purchase_date" class="form-label">Purchase Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="purchase_date" name="purchase_date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="purchase_price" class="form-label">Purchase Price <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="purchase_price" name="purchase_price" step="0.01" min="0" placeholder="0.00" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="quantity" name="quantity" step="0.000001" min="0" placeholder="0" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="current_price" class="form-label">Current Price</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="current_price" name="current_price" step="0.01" min="0" placeholder="0.00">
                        </div>
                        <div class="form-text">Leave blank to use purchase price as current price</div>
                    </div>
                    
                    <div id="investment-calculator" class="mb-3 d-none">
                        <div class="card bg-light">
                            <div class="card-body p-3">
                                <h6 class="card-title">Investment Summary</h6>
                                <div class="row">
                                    <div class="col-6">
                                        <p class="mb-1">Initial Investment:</p>
                                        <h5 id="initial-investment">$0.00</h5>
                                    </div>
                                    <div class="col-6">
                                        <p class="mb-1">Current Value:</p>
                                        <h5 id="current-value">$0.00</h5>
                                    </div>
                                </div>
                                <div id="gain-loss-container" class="d-none mt-2">
                                    <p class="mb-1">Gain/Loss:</p>
                                    <h5 id="gain-loss">$0.00 (0.00%)</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Add any additional notes here..."></textarea>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Investment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Investment Modal -->
<div class="modal fade" id="editInvestmentModal" tabindex="-1" aria-labelledby="editInvestmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editInvestmentModalLabel">
                    <i class="fas fa-edit me-2"></i>Edit Investment
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading investment data...</p>
                </div>
            </div>
            <form action="<?php echo BASE_PATH; ?>/investments" method="post" class="investment-form">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="investment_id" id="edit_investment_id">
                
                <div class="modal-body d-none" id="editInvestmentForm">
                    <div class="mb-3">
                        <label for="edit_type_id" class="form-label">Investment Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="edit_type_id" name="type_id" required>
                            <?php if ($investment_types && $investment_types->num_rows > 0): ?>
                                <?php 
                                // Reset the investment types result pointer
                                $investment_types->data_seek(0);
                                while ($type = $investment_types->fetch_assoc()): 
                                ?>
                                    <option value="<?php echo $type['type_id']; ?>" data-risk="<?php echo $type['risk_level']; ?>">
                                        <?php echo htmlspecialchars($type['name']); ?> 
                                        <span class="risk-level">(<?php echo ucfirst($type['risk_level']); ?> Risk)</span>
                                    </option>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Investment Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_name" name="name" placeholder="Enter investment name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_ticker_symbol" class="form-label">Ticker Symbol</label>
                        <input type="text" class="form-control" id="edit_ticker_symbol" name="ticker_symbol" placeholder="E.g., AAPL, VOO">
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_purchase_date" class="form-label">Purchase Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="edit_purchase_date" name="purchase_date" required>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="edit_purchase_price" class="form-label">Purchase Price <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="edit_purchase_price" name="purchase_price" step="0.01" min="0" placeholder="0.00" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="edit_quantity" name="quantity" step="0.000001" min="0" placeholder="0" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_current_price" class="form-label">Current Price <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="edit_current_price" name="current_price" step="0.01" min="0" placeholder="0.00" required>
                        </div>
                    </div>
                    
                    <div id="edit-investment-calculator" class="mb-3 d-none">
                        <div class="card bg-light">
                            <div class="card-body p-3">
                                <h6 class="card-title">Investment Summary</h6>
                                <div class="row">
                                    <div class="col-6">
                                        <p class="mb-1">Initial Investment:</p>
                                        <h5 id="edit-initial-investment">$0.00</h5>
                                    </div>
                                    <div class="col-6">
                                        <p class="mb-1">Current Value:</p>
                                        <h5 id="edit-current-value">$0.00</h5>
                                    </div>
                                </div>
                                <div id="edit-gain-loss-container" class="mt-2">
                                    <p class="mb-1">Gain/Loss:</p>
                                    <h5 id="edit-gain-loss">$0.00 (0.00%)</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="edit_notes" name="notes" rows="3" placeholder="Add any additional notes here..."></textarea>
                    </div>
                </div>
                
                <div class="modal-footer d-none" id="editInvestmentFooter">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Investment Modal -->
<div class="modal fade" id="deleteInvestmentModal" tabindex="-1" aria-labelledby="deleteInvestmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteInvestmentModalLabel">
                    <i class="fas fa-trash-alt me-2 text-danger"></i>Delete Investment
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div class="delete-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h4 class="mt-4">Are you sure?</h4>
                <p class="text-muted">This action cannot be undone. This will permanently delete this investment from your portfolio.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="<?php echo BASE_PATH; ?>/investments" method="post">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="investment_id" id="delete_investment_id">
                    <button type="submit" class="btn btn-danger">Delete Investment</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Update Price Modal -->
<div class="modal fade" id="updatePriceModal" tabindex="-1" aria-labelledby="updatePriceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updatePriceModalLabel">
                    <i class="fas fa-sync-alt me-2 text-primary"></i>Update Current Price
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?php echo BASE_PATH; ?>/investments" method="post">
                <input type="hidden" name="action" value="update_price">
                <input type="hidden" name="investment_id" id="update_investment_id">
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="update_current_price" class="form-label">Current Price <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="update_current_price" name="current_price" step="0.01" min="0" placeholder="0.00" required>
                        </div>
                    </div>
                    
                    <div class="form-text">
                        <i class="fas fa-info-circle me-1"></i>
                        Enter the current market price for this investment.
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
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
// Initialize the investment calculator
function initInvestmentCalculator() {
    const purchasePrice = document.getElementById('purchase_price');
    const quantity = document.getElementById('quantity');
    const currentPrice = document.getElementById('current_price');
    const calculator = document.getElementById('investment-calculator');
    
    const initialInvestment = document.getElementById('initial-investment');
    const currentValue = document.getElementById('current-value');
    const gainLoss = document.getElementById('gain-loss');
    const gainLossContainer = document.getElementById('gain-loss-container');
    
    function updateCalculator() {
        const price = parseFloat(purchasePrice.value) || 0;
        const qty = parseFloat(quantity.value) || 0;
        const current = parseFloat(currentPrice.value) || price;
        
        const invested = price * qty;
        const currentVal = current * qty;
        const gainLossVal = currentVal - invested;
        const gainLossPercent = invested > 0 ? (gainLossVal / invested) * 100 : 0;
        
        initialInvestment.textContent = '$' + invested.toFixed(2);
        currentValue.textContent = '$' + currentVal.toFixed(2);
        
        if (current !== price) {
            gainLossContainer.classList.remove('d-none');
            gainLoss.textContent = '$' + Math.abs(gainLossVal).toFixed(2) + ' (' + gainLossPercent.toFixed(2) + '%)';
            gainLoss.className = gainLossVal >= 0 ? 'text-success' : 'text-danger';
        } else {
            gainLossContainer.classList.add('d-none');
        }
        
        if (invested > 0) {
            calculator.classList.remove('d-none');
        } else {
            calculator.classList.add('d-none');
        }
    }
    
    if (purchasePrice && quantity && currentPrice) {
        purchasePrice.addEventListener('input', updateCalculator);
        quantity.addEventListener('input', updateCalculator);
        currentPrice.addEventListener('input', updateCalculator);
    }
}

// Initialize the edit investment calculator
function initEditInvestmentCalculator() {
    const purchasePrice = document.getElementById('edit_purchase_price');
    const quantity = document.getElementById('edit_quantity');
    const currentPrice = document.getElementById('edit_current_price');
    const calculator = document.getElementById('edit-investment-calculator');
    
    const initialInvestment = document.getElementById('edit-initial-investment');
    const currentValue = document.getElementById('edit-current-value');
    const gainLoss = document.getElementById('edit-gain-loss');
    const gainLossContainer = document.getElementById('edit-gain-loss-container');
    
    function updateCalculator() {
        const price = parseFloat(purchasePrice.value) || 0;
        const qty = parseFloat(quantity.value) || 0;
        const current = parseFloat(currentPrice.value) || price;
        
        const invested = price * qty;
        const currentVal = current * qty;
        const gainLossVal = currentVal - invested;
        const gainLossPercent = invested > 0 ? (gainLossVal / invested) * 100 : 0;
        
        initialInvestment.textContent = '$' + invested.toFixed(2);
        currentValue.textContent = '$' + currentVal.toFixed(2);
        
        gainLoss.textContent = '$' + Math.abs(gainLossVal).toFixed(2) + ' (' + gainLossPercent.toFixed(2) + '%)';
        gainLoss.className = gainLossVal >= 0 ? 'text-success' : 'text-danger';
        
        if (invested > 0) {
            calculator.classList.remove('d-none');
        } else {
            calculator.classList.add('d-none');
        }
    }
    
    if (purchasePrice && quantity && currentPrice) {
        purchasePrice.addEventListener('input', updateCalculator);
        quantity.addEventListener('input', updateCalculator);
        currentPrice.addEventListener('input', updateCalculator);
        
        // Initial calculation
        updateCalculator();
    }
}

// Initialize the type chart
var typeChartCtx = document.getElementById('investmentTypeChart');
if (typeChartCtx) {
    typeChartCtx = typeChartCtx.getContext('2d');
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
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            maintainAspectRatio: false,
            layout: {
                padding: 15
            },
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 12,
                        padding: 15,
                        font: {
                            family: 'Inter, sans-serif',
                            size: 11
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleFont: {
                        family: 'Inter, sans-serif',
                        size: 13,
                        weight: 'bold'
                    },
                    bodyFont: {
                        family: 'Inter, sans-serif',
                        size: 12
                    },
                    padding: 12,
                    cornerRadius: 8,
                    caretSize: 6,
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
                duration: 1000,
                easing: 'easeOutQuart'
            }
        }
    });
}

// Initialize the risk chart
var riskChartCtx = document.getElementById('riskLevelChart');
if (riskChartCtx) {
    riskChartCtx = riskChartCtx.getContext('2d');
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
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            maintainAspectRatio: false,
            layout: {
                padding: 15
            },
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 12,
                        padding: 15,
                        font: {
                            family: 'Inter, sans-serif',
                            size: 11
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleFont: {
                        family: 'Inter, sans-serif',
                        size: 13,
                        weight: 'bold'
                    },
                    bodyFont: {
                        family: 'Inter, sans-serif',
                        size: 12
                    },
                    padding: 12,
                    cornerRadius: 8,
                    caretSize: 6,
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
            cutout: '70%',
            animation: {
                animateScale: true,
                animateRotate: true,
                duration: 1000,
                easing: 'easeOutQuart'
            }
        }
    });
}

// Initialize investment calculator
document.addEventListener('DOMContentLoaded', function() {
    initInvestmentCalculator();
    
    // Initialize search functionality
    const searchInput = document.getElementById('investmentSearch');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const searchText = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('#investmentTable tbody tr');
            
            tableRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchText)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
});

// Add animation to cards
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.card');
    
    cards.forEach((card, index) => {
        setTimeout(() => {
            card.classList.add('fade-in');
        }, index * 100);
    });
});
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