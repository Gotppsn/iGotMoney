<?php
// Set page title and current page for menu highlighting
$page_title = 'Income Management - iGotMoney';
$current_page = 'income';

// Additional CSS and JS
$additional_css = ['/assets/css/income-modern.css'];
$additional_js = ['/assets/js/income-modern.js'];

// Include header
require_once 'includes/header.php';
?>

<!-- Main Content Wrapper -->
<div class="income-page">
    <!-- Page Header Section -->
    <div class="page-header-section">
        <div class="page-header-content">
            <div class="page-title-group">
                <h1 class="page-title">Income Management</h1>
                <p class="page-subtitle">Track and manage all your income sources effectively</p>
            </div>
            <button type="button" class="btn-add-income" data-bs-toggle="modal" data-bs-target="#addIncomeModal">
                <i class="fas fa-plus-circle"></i>
                <span>Add Income</span>
            </button>
        </div>
    </div>

    <!-- Quick Stats Section -->
    <div class="quick-stats-section">
        <div class="stats-grid">
            <div class="stat-card monthly">
                <div class="stat-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-label">Monthly Income</h3>
                    <p class="stat-value">$<?php echo number_format($monthly_income, 2); ?></p>
                    <?php
                    $previous_month = isset($prev_monthly_income) ? $prev_monthly_income : $monthly_income * 0.95;
                    $is_increased = $monthly_income >= $previous_month;
                    $percent_change = abs(($monthly_income - $previous_month) / max(1, $previous_month) * 100);
                    ?>
                    <div class="stat-trend <?php echo $is_increased ? 'positive' : 'negative'; ?>">
                        <i class="fas fa-<?php echo $is_increased ? 'arrow-up' : 'arrow-down'; ?>"></i>
                        <span><?php echo number_format($percent_change, 1); ?>% from last month</span>
                    </div>
                </div>
            </div>
            
            <div class="stat-card annual">
                <div class="stat-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-label">Annual Income</h3>
                    <p class="stat-value">$<?php echo number_format($yearly_income, 2); ?></p>
                    <div class="stat-info">
                        <i class="fas fa-info-circle"></i>
                        <span>Projected for <?php echo date('Y'); ?></span>
                    </div>
                </div>
            </div>
            
            <div class="stat-card sources">
                <div class="stat-icon">
                    <i class="fas fa-wallet"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-label">Active Sources</h3>
                    <?php
                    $active_count = 0;
                    if (isset($income_sources) && $income_sources->num_rows > 0) {
                        $income_sources->data_seek(0);
                        while ($row = $income_sources->fetch_assoc()) {
                            if ($row['is_active'] == 1) {
                                $active_count++;
                            }
                        }
                        $income_sources->data_seek(0);
                    }
                    ?>
                    <p class="stat-value"><?php echo $active_count; ?></p>
                    <div class="stat-info">
                        <i class="fas fa-check-circle"></i>
                        <span>Currently active</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="charts-section">
        <div class="charts-grid">
            <!-- Income Frequency Distribution Chart -->
            <div class="chart-card frequency-chart">
                <div class="chart-header">
                    <div class="chart-title">
                        <i class="fas fa-chart-pie"></i>
                        <h3>Income by Frequency</h3>
                    </div>
                </div>
                <div class="chart-body">
                    <div class="chart-container">
                        <canvas id="frequencyChart"></canvas>
                    </div>
                    <div id="chartNoData" class="no-data-message" style="display: <?php echo ($active_count > 0) ? 'none' : 'block'; ?>">
                        <i class="fas fa-chart-bar"></i>
                        <p>No active income sources to display</p>
                    </div>
                </div>
            </div>
            
            <!-- Top Income Sources List -->
            <div class="chart-card sources-list">
                <div class="chart-header">
                    <div class="chart-title">
                        <i class="fas fa-list-ol"></i>
                        <h3>Top Income Sources</h3>
                    </div>
                </div>
                <div class="chart-body">
                    <div class="sources-list-content">
                        <?php if (isset($income_sources) && $income_sources->num_rows > 0): ?>
                            <?php 
                            $income_sources->data_seek(0);
                            $sources_array = [];
                            while ($source = $income_sources->fetch_assoc()) {
                                if ($source['is_active'] == 1) {
                                    // Calculate monthly equivalent
                                    $monthly_amount = $source['amount'];
                                    switch ($source['frequency']) {
                                        case 'annually':
                                            $monthly_amount = $source['amount'] / 12;
                                            break;
                                        case 'quarterly':
                                            $monthly_amount = $source['amount'] / 3;
                                            break;
                                        case 'bi-weekly':
                                            $monthly_amount = $source['amount'] * 2.17;
                                            break;
                                        case 'weekly':
                                            $monthly_amount = $source['amount'] * 4.33;
                                            break;
                                        case 'daily':
                                            $monthly_amount = $source['amount'] * 30;
                                            break;
                                    }
                                    $source['monthly_amount'] = $monthly_amount;
                                    $sources_array[] = $source;
                                }
                            }
                            $income_sources->data_seek(0);
                            
                            // Sort by monthly amount
                            usort($sources_array, function($a, $b) {
                                return $b['monthly_amount'] - $a['monthly_amount'];
                            });
                            
                            $total_monthly = array_sum(array_column($sources_array, 'monthly_amount'));
                            $rank = 1;
                            foreach (array_slice($sources_array, 0, 5) as $source):
                                $percentage = $total_monthly > 0 ? ($source['monthly_amount'] / $total_monthly) * 100 : 0;
                            ?>
                                <div class="source-item">
                                    <div class="source-rank"><?php echo $rank++; ?></div>
                                    <div class="source-info">
                                        <h4 class="source-name"><?php echo htmlspecialchars($source['name']); ?></h4>
                                        <span class="source-frequency"><?php echo ucfirst(str_replace('-', ' ', $source['frequency'])); ?></span>
                                        <div class="source-bar">
                                            <div class="source-bar-fill" 
                                                 style="width: <?php echo $percentage; ?>%"
                                                 data-percentage="<?php echo $percentage; ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="source-amount">
                                        <span class="amount">$<?php echo number_format($source['monthly_amount'], 2); ?>/mo</span>
                                        <span class="percentage"><?php echo number_format($percentage, 1); ?>%</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-sources">
                                <i class="fas fa-folder-open"></i>
                                <p>No income sources recorded yet</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Income Table Section -->
    <div class="income-table-section">
        <div class="table-card">
            <div class="table-header">
                <div class="table-title">
                    <i class="fas fa-list"></i>
                    <h3>Income Sources</h3>
                </div>
                <div class="table-controls">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="incomeSearch" placeholder="Search income sources..." data-table-search="incomeTable">
                    </div>
                </div>
            </div>
            <div class="table-body">
                <div class="table-responsive">
                    <table class="income-table" id="incomeTable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Amount</th>
                                <th>Frequency</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($income_sources) && $income_sources->num_rows > 0): ?>
                                <?php 
                                $income_sources->data_seek(0);
                                while ($income_source = $income_sources->fetch_assoc()): 
                                ?>
                                    <tr>
                                        <td class="source-name-cell"><?php echo htmlspecialchars($income_source['name']); ?></td>
                                        <td class="amount-cell">$<?php echo number_format($income_source['amount'], 2); ?></td>
                                        <td>
                                            <span class="frequency-badge">
                                                <?php echo ucfirst(str_replace('-', ' ', $income_source['frequency'])); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M j, Y', strtotime($income_source['start_date'])); ?></td>
                                        <td>
                                            <?php 
                                            if (!empty($income_source['end_date']) && $income_source['end_date'] !== null && $income_source['end_date'] !== '0000-00-00') {
                                                $timestamp = strtotime($income_source['end_date']);
                                                if ($timestamp > 0) {
                                                    echo date('M j, Y', $timestamp);
                                                } else {
                                                    echo '<span class="text-muted">Ongoing</span>';
                                                }
                                            } else {
                                                echo '<span class="text-muted">Ongoing</span>';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <span class="status-badge <?php echo $income_source['is_active'] ? 'active' : 'inactive'; ?>">
                                                <?php echo $income_source['is_active'] ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td class="actions-cell">
                                            <button class="btn-action edit" data-income-id="<?php echo $income_source['income_id']; ?>" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn-action delete" data-income-id="<?php echo $income_source['income_id']; ?>" title="Delete">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div id="tableNoData" class="table-empty" style="display: <?php echo (isset($income_sources) && $income_sources->num_rows > 0) ? 'none' : 'block'; ?>">
                    <div class="empty-icon">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <h4>No income sources recorded yet</h4>
                    <p>Start tracking your income by adding your first income source</p>
                    <button type="button" class="btn-add-first" data-bs-toggle="modal" data-bs-target="#addIncomeModal">
                        <i class="fas fa-plus"></i>
                        Add Your First Income
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Income Modal (Redesigned) -->
<div class="modal fade modern-modal" id="addIncomeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-icon">
                    <i class="fas fa-plus-circle"></i>
                </div>
                <h5 class="modal-title">Add New Income Source</h5>
                <button type="button" class="modal-close" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="<?php echo BASE_PATH; ?>/income" method="post" class="needs-validation" novalidate>
                <input type="hidden" name="action" value="add">
                
                <div class="modal-body">
                    <div class="form-grid">
                        <div class="form-field full-width">
                            <label for="name">Income Name</label>
                            <input type="text" id="name" name="name" required>
                        </div>
                        
                        <div class="form-field">
                            <label for="amount">Amount</label>
                            <div class="amount-input">
                                <span class="currency-symbol">$</span>
                                <input type="number" id="amount" name="amount" step="0.01" min="0.01" required>
                            </div>
                        </div>
                        
                        <div class="form-field">
                            <label for="frequency">Frequency</label>
                            <select id="frequency" name="frequency" class="modern-select">
                                <option value="one-time">One-time</option>
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="bi-weekly">Bi-Weekly</option>
                                <option value="monthly" selected>Monthly</option>
                                <option value="quarterly">Quarterly</option>
                                <option value="annually">Annually</option>
                            </select>
                        </div>
                        
                        <div class="form-field">
                            <label for="start_date">Start Date</label>
                            <input type="date" id="start_date" name="start_date" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        
                        <div class="form-field">
                            <label for="end_date">End Date (Optional)</label>
                            <input type="date" id="end_date" name="end_date">
                        </div>
                        
                        <div class="form-field full-width">
                            <label class="toggle-field">
                                <input type="checkbox" id="is_active" name="is_active" checked>
                                <span class="toggle-slider"></span>
                                <span class="toggle-label">Active Income Source</span>
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-plus"></i>
                        Add Income
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Income Modal (Similar structure) -->
<div class="modal fade modern-modal" id="editIncomeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-icon edit">
                    <i class="fas fa-edit"></i>
                </div>
                <h5 class="modal-title">Edit Income Source</h5>
                <button type="button" class="modal-close" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="<?php echo BASE_PATH; ?>/income" method="post" class="needs-validation" novalidate>
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="income_id" id="edit_income_id">
                
                <div class="modal-body">
                    <div class="form-grid">
                        <div class="form-field full-width">
                            <label for="edit_name">Income Name</label>
                            <input type="text" id="edit_name" name="name" required>
                        </div>
                        
                        <div class="form-field">
                            <label for="edit_amount">Amount</label>
                            <div class="amount-input">
                                <span class="currency-symbol">$</span>
                                <input type="number" id="edit_amount" name="amount" step="0.01" min="0.01" required>
                            </div>
                        </div>
                        
                        <div class="form-field">
                            <label for="edit_frequency">Frequency</label>
                            <select id="edit_frequency" name="frequency" class="modern-select">
                                <option value="one-time">One-time</option>
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="bi-weekly">Bi-Weekly</option>
                                <option value="monthly">Monthly</option>
                                <option value="quarterly">Quarterly</option>
                                <option value="annually">Annually</option>
                            </select>
                        </div>
                        
                        <div class="form-field">
                            <label for="edit_start_date">Start Date</label>
                            <input type="date" id="edit_start_date" name="start_date" required>
                        </div>
                        
                        <div class="form-field">
                            <label for="edit_end_date">End Date (Optional)</label>
                            <input type="date" id="edit_end_date" name="end_date">
                        </div>
                        
                        <div class="form-field full-width">
                            <label class="toggle-field">
                                <input type="checkbox" id="edit_is_active" name="is_active">
                                <span class="toggle-slider"></span>
                                <span class="toggle-label">Active Income Source</span>
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save"></i>
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Income Modal -->
<div class="modal fade modern-modal" id="deleteIncomeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-icon delete">
                    <i class="fas fa-trash-alt"></i>
                </div>
                <h5 class="modal-title">Delete Income Source</h5>
                <button type="button" class="modal-close" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body text-center">
                <p>Are you sure you want to delete this income source?</p>
                <p class="text-muted">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" data-bs-dismiss="modal">Cancel</button>
                <form action="<?php echo BASE_PATH; ?>/income" method="post">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="income_id" id="delete_income_id">
                    <button type="submit" class="btn-submit danger">
                        <i class="fas fa-trash-alt"></i>
                        Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
// Chart data
$chart_labels = [];
$chart_data = [];
$chart_colors = [
    '#6366f1', '#8b5cf6', '#ec4899', '#ef4444', '#f59e0b',
    '#10b981', '#14b8a6', '#06b6d4', '#3b82f6', '#6366f1'
];

if (isset($income_sources) && $income_sources->num_rows > 0) {
    $income_sources->data_seek(0);
    
    $frequency_totals = [];
    while ($source = $income_sources->fetch_assoc()) {
        if ($source['is_active'] == 1) {
            $freq = $source['frequency'];
            $monthly_amount = $source['amount'];
            
            // Convert to monthly equivalent
            switch ($freq) {
                case 'annually':
                    $monthly_amount = $source['amount'] / 12;
                    break;
                case 'quarterly':
                    $monthly_amount = $source['amount'] / 3;
                    break;
                case 'bi-weekly':
                    $monthly_amount = $source['amount'] * 2.17;
                    break;
                case 'weekly':
                    $monthly_amount = $source['amount'] * 4.33;
                    break;
                case 'daily':
                    $monthly_amount = $source['amount'] * 30;
                    break;
            }
            
            $freq_name = ucfirst(str_replace('-', ' ', $freq));
            if (!isset($frequency_totals[$freq_name])) {
                $frequency_totals[$freq_name] = 0;
            }
            $frequency_totals[$freq_name] += $monthly_amount;
        }
    }
    
    // Sort by value
    arsort($frequency_totals);
    
    foreach ($frequency_totals as $label => $value) {
        $chart_labels[] = $label;
        $chart_data[] = round($value, 2);
    }
}

// Add meta tags for passing data to JS
echo '<meta name="base-path" content="' . BASE_PATH . '">';
echo '<meta name="chart-labels" content="' . htmlspecialchars(json_encode($chart_labels)) . '">';
echo '<meta name="chart-data" content="' . htmlspecialchars(json_encode($chart_data)) . '">';
echo '<meta name="chart-colors" content="' . htmlspecialchars(json_encode(array_slice($chart_colors, 0, count($chart_data)))) . '">';

require_once 'includes/footer.php';
?>