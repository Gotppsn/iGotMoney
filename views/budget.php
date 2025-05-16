<?php
// Set page title and current page for menu highlighting
$page_title = __('budget_management') . ' - ' . __('app_name');
$current_page = 'budget';

// Additional CSS for modern design
$additional_css = ['/assets/css/budget-modern.css'];

// Include currency helper for currency formatting
require_once 'includes/currency_helper.php';

// Include header
require_once 'includes/header.php';
?>

<div class="budget-container">
    <!-- Page Header -->
    <div class="page-header">
        <div class="header-content">
            <div class="header-title">
                <h1><?php echo __('budget_management'); ?></h1>
                <p class="subtitle"><?php echo __('plan_your_spending'); ?></p>
            </div>
            <div class="header-actions">
                <button type="button" class="btn btn-outline" data-bs-toggle="modal" data-bs-target="#generateBudgetModal">
                    <i class="fas fa-magic"></i>
                    <span><?php echo __('auto_generate'); ?></span>
                </button>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBudgetModal">
                    <i class="fas fa-plus"></i>
                    <span><?php echo __('add_budget'); ?></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Overview Cards -->
    <div class="overview-section">
        <?php
        $total_budget = 0;
        $total_spent = 0;
        $total_remaining = 0;
        $investment_budget = 0;
        $investment_spent = 0;
        $budget_percentage = 0;
        
        if (!empty($budget_status)) {
            foreach ($budget_status as $budget) {
                $total_budget += $budget['budget_amount'];
                $total_spent += $budget['spent'];
                
                if ($budget['is_investment']) {
                    $investment_budget = $budget['budget_amount'];
                    $investment_spent = $budget['spent'];
                }
            }
            $total_remaining = $total_budget - $total_spent;
            $budget_percentage = $total_budget > 0 ? min(100, ($total_spent / $total_budget) * 100) : 0;
        }
        
        $budget_health = $total_budget > 0 ? (($total_budget - $total_spent) / $total_budget) * 100 : 0;
        $investment_percentage = $investment_budget > 0 ? min(100, ($investment_spent / $investment_budget) * 100) : 0;
        ?>
        
        <div class="overview-card primary">
            <div class="card-icon">
                <i class="fas fa-wallet"></i>
            </div>
            <div class="card-content">
                <h3><?php echo __('total_budget'); ?></h3>
                <div class="amount"><?php echo formatMoney($total_budget); ?></div>
                <div class="progress-container">
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo $budget_percentage; ?>%"></div>
                    </div>
                    <div class="progress-text"><?php echo number_format($budget_percentage, 0); ?>% <?php echo __('used'); ?></div>
                </div>
            </div>
        </div>
        
        <div class="overview-card secondary">
            <div class="card-icon">
                <i class="fas fa-chart-pie"></i>
            </div>
            <div class="card-content">
                <h3><?php echo __('spent_vs_remaining'); ?></h3>
                <div class="split-values">
                    <div class="split-item">
                        <span class="label"><?php echo __('spent'); ?></span>
                        <span class="value"><?php echo formatMoney($total_spent); ?></span>
                    </div>
                    <div class="divider"></div>
                    <div class="split-item">
                        <span class="label"><?php echo __('remaining'); ?></span>
                        <span class="value <?php echo $total_remaining < 0 ? 'negative' : 'positive'; ?>"><?php echo formatMoney($total_remaining); ?></span>
                    </div>
                </div>
                <div class="status-indicator">
                    <?php if ($budget_health >= 50): ?>
                        <span class="status good"><i class="fas fa-check-circle"></i> <?php echo __('on_track'); ?></span>
                    <?php elseif ($budget_health >= 20): ?>
                        <span class="status warning"><i class="fas fa-exclamation-triangle"></i> <?php echo __('caution'); ?></span>
                    <?php else: ?>
                        <span class="status danger"><i class="fas fa-exclamation-circle"></i> <?php echo __('over_budget'); ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="overview-card tertiary">
            <div class="card-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="card-content">
                <h3><?php echo __('investments'); ?></h3>
                <?php if ($investment_budget > 0): ?>
                    <div class="amount"><?php echo formatMoney($investment_budget); ?></div>
                    <div class="progress-container">
                        <div class="progress-bar">
                            <div class="progress-fill investment" style="width: <?php echo $investment_percentage; ?>%"></div>
                        </div>
                        <div class="progress-text"><?php echo number_format($investment_percentage, 0); ?>% <?php echo __('used'); ?></div>
                    </div>
                <?php else: ?>
                    <div class="no-data">
                        <p><?php echo __('no_investment_budget'); ?></p>
                        <button class="btn btn-sm" onclick="selectInvestmentCategory()" data-bs-toggle="modal" data-bs-target="#addBudgetModal">
                            <i class="fas fa-plus"></i> <?php echo __('add_now'); ?>
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="content-section">
        <!-- Budget Visualization Section -->
        <div class="budget-visualization">
            <div class="section-header">
                <h2><?php echo __('budget_overview'); ?></h2>
                <div class="period-selector">
                    <button class="period-btn active" data-period="month"><?php echo __('month'); ?></button>
                    <button class="period-btn" data-period="quarter"><?php echo __('quarter'); ?></button>
                    <button class="period-btn" data-period="year"><?php echo __('year'); ?></button>
                </div>
            </div>
            
            <div class="visualization-content">
                <div class="budget-chart">
                    <div class="donut-chart-container">
                        <canvas id="budgetDonutChart"></canvas>
                        <div class="donut-center">
                            <div class="donut-value"><?php echo formatMoney($total_spent); ?></div>
                            <div class="donut-label"><?php echo __('total_spent'); ?></div>
                        </div>
                    </div>
                </div>
                
                <div class="budget-categories">
                    <h3><?php echo __('top_categories'); ?></h3>
                    
                    <?php if (!empty($budget_status)): ?>
                        <?php 
                        // Sort budgets by percentage
                        usort($budget_status, function($a, $b) {
                            return $b['percentage'] - $a['percentage'];
                        });
                        
                        // Display top 5 categories
                        $count = 0;
                        foreach ($budget_status as $budget): 
                            if ($count >= 5) break;
                            
                            // Skip categories with no spending
                            if ($budget['spent'] <= 0) continue;
                            
                            $count++;
                            
                            // Get translated category name
                            $translated_category = $expense->getTranslatedCategoryName($budget['category_name']);
                            
                            // Determine status color
                            $status_class = 'success';
                            if ($budget['percentage'] >= 90) {
                                $status_class = 'danger';
                            } elseif ($budget['percentage'] >= 70) {
                                $status_class = 'warning';
                            }
                        ?>
                            <div class="category-item" data-category-id="<?php echo $budget['category_id']; ?>" data-budget-id="<?php echo $budget['budget_id']; ?>">
                                <div class="category-info">
                                    <div class="category-name"><?php echo htmlspecialchars($translated_category); ?></div>
                                    <div class="category-amount">
                                        <span class="spent"><?php echo formatMoney($budget['spent']); ?></span>
                                        <span class="separator">/</span>
                                        <span class="total"><?php echo formatMoney($budget['budget_amount']); ?></span>
                                    </div>
                                </div>
                                <div class="category-progress">
                                    <div class="progress-bar">
                                        <div class="progress-fill <?php echo $status_class; ?>" style="width: <?php echo min(100, $budget['percentage']); ?>%"></div>
                                    </div>
                                    <div class="percentage"><?php echo number_format($budget['percentage'], 0); ?>%</div>
                                </div>
                                <button class="category-edit" data-budget-id="<?php echo $budget['budget_id']; ?>">
                                    <i class="fas fa-pencil-alt"></i>
                                </button>
                            </div>
                        <?php endforeach; ?>
                        
                        <?php if ($count == 0): ?>
                            <div class="no-data-message">
                                <i class="fas fa-chart-pie"></i>
                                <p><?php echo __('no_spending_recorded'); ?></p>
                            </div>
                        <?php endif; ?>
                        
                    <?php else: ?>
                        <div class="no-data-message">
                            <i class="fas fa-chart-pie"></i>
                            <p><?php echo __('no_budgets_created'); ?></p>
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addBudgetModal">
                                <?php echo __('create_budget'); ?>
                            </button>
                        </div>
                    <?php endif; ?>
                    
                    <div class="view-all">
                        <button class="btn btn-link" id="viewAllBtn">
                            <span><?php echo __('view_all_categories'); ?></span>
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Budget List Section -->
        <div class="budget-list">
            <div class="section-header">
                <h2><?php echo __('budget_categories'); ?></h2>
                <div class="header-actions">
                    <div class="search-container">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchBudgets" placeholder="<?php echo __('search_budgets'); ?>">
                    </div>
                    <div class="filter-dropdown">
                        <button class="filter-btn" id="filterToggle">
                            <i class="fas fa-filter"></i>
                            <span><?php echo __('filter'); ?></span>
                        </button>
                        <div class="filter-menu" id="filterMenu">
                            <div class="filter-option">
                                <label for="statusFilter"><?php echo __('status'); ?></label>
                                <select id="statusFilter">
                                    <option value="all"><?php echo __('all'); ?></option>
                                    <option value="good"><?php echo __('good'); ?></option>
                                    <option value="warning"><?php echo __('warning'); ?></option>
                                    <option value="danger"><?php echo __('over_budget'); ?></option>
                                </select>
                            </div>
                            <div class="filter-option">
                                <label for="sortBy"><?php echo __('sort_by'); ?></label>
                                <select id="sortBy">
                                    <option value="category"><?php echo __('category_name'); ?></option>
                                    <option value="amount"><?php echo __('budget_amount'); ?></option>
                                    <option value="spent"><?php echo __('spent_amount'); ?></option>
                                    <option value="percentage"><?php echo __('percentage'); ?></option>
                                </select>
                            </div>
                            <div class="filter-buttons">
                                <button class="btn btn-sm" id="applyFilters"><?php echo __('apply'); ?></button>
                                <button class="btn btn-sm btn-outline" id="resetFilters"><?php echo __('reset'); ?></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="budget-table-container">
                <?php if (!empty($budget_status)): ?>
                    <table class="budget-table" id="budgetTable">
                        <thead>
                            <tr>
                                <th data-sort="category"><?php echo __('category'); ?></th>
                                <th data-sort="amount"><?php echo __('budget'); ?></th>
                                <th data-sort="spent"><?php echo __('spent'); ?></th>
                                <th data-sort="remaining"><?php echo __('remaining'); ?></th>
                                <th data-sort="percentage"><?php echo __('progress'); ?></th>
                                <th data-sort="status"><?php echo __('status'); ?></th>
                                <th><?php echo __('actions'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($budget_status as $budget): 
                                // Get translated category name
                                $translated_category = $expense->getTranslatedCategoryName($budget['category_name']);
                                
                                // Determine status
                                $status_class = 'success';
                                $status_text = __('good');
                                
                                if ($budget['percentage'] >= 90) {
                                    $status_class = 'danger';
                                    $status_text = __('over_budget');
                                } elseif ($budget['percentage'] >= 70) {
                                    $status_class = 'warning';
                                    $status_text = __('warning');
                                }
                                
                                // For investment categories, use different status
                                if ($budget['is_investment']) {
                                    $status_class = 'info';
                                    $status_text = $budget['percentage'] >= 100 ? __('on_track') : __('invest_more');
                                }
                            ?>
                                <tr class="<?php echo $budget['is_investment'] ? 'investment-row' : ''; ?>" 
                                    data-category-id="<?php echo $budget['category_id']; ?>"
                                    data-budget-id="<?php echo $budget['budget_id']; ?>"
                                    data-percentage="<?php echo $budget['percentage']; ?>"
                                    data-budget-amount="<?php echo $budget['budget_amount']; ?>"
                                    data-spent="<?php echo $budget['spent']; ?>"
                                    data-available="<?php echo $budget['available']; ?>"
                                    data-status="<?php echo $status_class; ?>">
                                    <td class="category-cell">
                                        <div class="category-name">
                                            <?php if ($budget['is_investment']): ?>
                                                <i class="fas fa-gem investment-icon"></i>
                                            <?php endif; ?>
                                            <span><?php echo htmlspecialchars($translated_category); ?></span>
                                        </div>
                                    </td>
                                    <td class="amount-cell"><?php echo formatMoney($budget['budget_amount']); ?></td>
                                    <td class="amount-cell"><?php echo formatMoney($budget['spent']); ?></td>
                                    <td class="amount-cell">
                                        <span class="<?php echo $budget['available'] < 0 ? 'negative' : 'positive'; ?>">
                                            <?php echo formatMoney($budget['available']); ?>
                                        </span>
                                    </td>
                                    <td class="progress-cell">
                                        <div class="progress-wrapper">
                                            <div class="progress-bar">
                                                <div class="progress-fill <?php echo $status_class; ?>" style="width: <?php echo min(100, $budget['percentage']); ?>%"></div>
                                            </div>
                                            <span class="progress-text"><?php echo number_format($budget['percentage'], 0); ?>%</span>
                                        </div>
                                    </td>
                                    <td class="status-cell">
                                        <span class="status-badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                    </td>
                                    <td class="actions-cell">
                                        <button class="action-btn edit" data-budget-id="<?php echo $budget['budget_id']; ?>" title="<?php echo __('edit'); ?>">
                                            <i class="fas fa-pencil-alt"></i>
                                        </button>
                                        <button class="action-btn delete" data-budget-id="<?php echo $budget['budget_id']; ?>" title="<?php echo __('delete'); ?>">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <div id="noMatchingBudgets" class="no-data-message" style="display: none;">
                        <i class="fas fa-filter"></i>
                        <p><?php echo __('no_matching_budgets'); ?></p>
                        <button class="btn btn-sm btn-outline" id="clearFilters"><?php echo __('clear_filters'); ?></button>
                    </div>
                    
                <?php else: ?>
                    <div class="no-data-message">
                        <i class="fas fa-list"></i>
                        <p><?php echo __('no_budgets_created_yet'); ?></p>
                        <div class="action-options">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBudgetModal">
                                <i class="fas fa-plus"></i> <?php echo __('add_budget'); ?>
                            </button>
                            <span class="or-separator"><?php echo __('or'); ?></span>
                            <button class="btn btn-outline" data-bs-toggle="modal" data-bs-target="#generateBudgetModal">
                                <i class="fas fa-magic"></i> <?php echo __('auto_generate'); ?>
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if (isset($budget_plan) && !empty($budget_plan)): ?>
        <!-- Budget Recommendations -->
        <div class="budget-recommendations">
            <div class="section-header">
                <h2><?php echo __('recommendations'); ?></h2>
                <button class="btn btn-success btn-sm" id="adoptAllBtn">
                    <i class="fas fa-check"></i>
                    <span><?php echo __('adopt_all'); ?></span>
                </button>
            </div>
            
            <div class="recommendations-intro">
                <i class="fas fa-info-circle"></i>
                <p><?php echo __('recommendations_intro'); ?></p>
            </div>
            
            <div class="recommendations-list">
                <?php foreach ($budget_plan as $recommendation): 
                    // Get translated category name
                    $translated_category = $expense->getTranslatedCategoryName($recommendation['category_name']);
                    
                    // Check if there's an existing budget
                    $existing_budget = false;
                    $budget_id = 0;
                    $current_budget = 0;
                    
                    if (!empty($budget_status)) {
                        foreach ($budget_status as $budget) {
                            if ($budget['category_id'] == $recommendation['category_id']) {
                                $existing_budget = true;
                                $budget_id = $budget['budget_id'];
                                $current_budget = $budget['budget_amount'];
                                break;
                            }
                        }
                    }
                ?>
                    <div class="recommendation-card <?php echo $recommendation['is_investment'] ? 'investment' : ''; ?>">
                        <div class="recommendation-header">
                            <div class="recommendation-category">
                                <?php if ($recommendation['is_investment']): ?>
                                    <i class="fas fa-gem investment-icon"></i>
                                <?php endif; ?>
                                <span><?php echo htmlspecialchars($translated_category); ?></span>
                            </div>
                            <div class="recommendation-amount"><?php echo formatMoney($recommendation['allocated_amount']); ?></div>
                        </div>
                        
                        <div class="recommendation-details">
                            <div class="recommendation-percentage">
                                <span class="label"><?php echo __('of_income'); ?>:</span>
                                <span class="value"><?php echo number_format($recommendation['percentage'], 1); ?>%</span>
                            </div>
                            
                            <?php if ($existing_budget): ?>
                                <div class="recommendation-change">
                                    <span class="label"><?php echo __('current'); ?>:</span>
                                    <span class="value"><?php echo formatMoney($current_budget); ?></span>
                                </div>
                                <div class="recommendation-difference <?php echo $recommendation['allocated_amount'] > $current_budget ? 'positive' : ($recommendation['allocated_amount'] < $current_budget ? 'negative' : ''); ?>">
                                    <?php
                                    $difference = $recommendation['allocated_amount'] - $current_budget;
                                    $icon = $difference > 0 ? 'arrow-up' : ($difference < 0 ? 'arrow-down' : 'equals');
                                    ?>
                                    <i class="fas fa-<?php echo $icon; ?>"></i>
                                    <span><?php echo formatMoney(abs($difference)); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <button class="btn btn-sm adopt-btn" 
                                data-category-id="<?php echo $recommendation['category_id']; ?>"
                                data-amount="<?php echo $recommendation['allocated_amount']; ?>"
                                <?php if ($existing_budget): ?>
                                data-existing-budget-id="<?php echo $budget_id; ?>"
                                <?php endif; ?>>
                            <i class="fas fa-<?php echo $existing_budget ? 'sync' : 'plus'; ?>"></i>
                            <span><?php echo $existing_budget ? __('update') : __('adopt'); ?></span>
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Budget Tips -->
        <div class="budget-tips">
            <div class="tips-header">
                <h2>
                    <i class="fas fa-lightbulb"></i>
                    <span><?php echo __('budget_tips'); ?></span>
                </h2>
                <button class="tips-toggle" id="tipsToggle">
                    <i class="fas fa-chevron-down"></i>
                </button>
            </div>
            
            <div class="tips-content" id="tipsContent">
                <div class="tips-grid">
                    <div class="tip-card">
                        <div class="tip-icon">
                            <i class="fas fa-piggy-bank"></i>
                        </div>
                        <div class="tip-content">
                            <h3><?php echo __('tip_50_30_20_rule'); ?></h3>
                            <p><?php echo __('tip_50_30_20_description'); ?></p>
                        </div>
                    </div>
                    
                    <div class="tip-card">
                        <div class="tip-icon">
                            <i class="fas fa-hand-holding-usd"></i>
                        </div>
                        <div class="tip-content">
                            <h3><?php echo __('tip_pay_yourself'); ?></h3>
                            <p><?php echo __('tip_pay_yourself_description'); ?></p>
                        </div>
                    </div>
                    
                    <div class="tip-card">
                        <div class="tip-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="tip-content">
                            <h3><?php echo __('tip_investment'); ?></h3>
                            <p><?php echo __('tip_investment_description'); ?></p>
                        </div>
                    </div>
                    
                    <div class="tip-card">
                        <div class="tip-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="tip-content">
                            <h3><?php echo __('tip_review_regularly'); ?></h3>
                            <p><?php echo __('tip_review_regularly_description'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Budget Modal -->
<div class="modal fade" id="addBudgetModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus"></i>
                    <span><?php echo __('add_budget'); ?></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?php echo BASE_PATH; ?>/budget" method="post" id="addBudgetForm" class="needs-validation" novalidate>
                <input type="hidden" name="action" value="add">
                
                <div class="modal-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="category_id"><?php echo __('category'); ?></label>
                            <select class="form-control" id="category_id" name="category_id" required>
                                <option value=""><?php echo __('select_category'); ?></option>
                                <?php 
                                if (isset($categories) && $categories->num_rows > 0) {
                                    $categories->data_seek(0);
                                    while ($category = $categories->fetch_assoc()): 
                                        // Get translated category name
                                        $translated_name = $expense->getTranslatedCategoryName($category['name']);
                                ?>
                                    <option value="<?php echo $category['category_id']; ?>" 
                                            <?php echo ($category['name'] === 'Investments') ? 'class="investment-option"' : ''; ?>>
                                        <?php echo htmlspecialchars($translated_name); ?>
                                        <?php echo ($category['name'] === 'Investments') ? ' 💎' : ''; ?>
                                    </option>
                                <?php 
                                    endwhile;
                                }
                                ?>
                            </select>
                            <div class="invalid-feedback">
                                <?php echo __('please_select_category'); ?>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="amount"><?php echo __('amount'); ?></label>
                            <div class="amount-input">
                                <span class="currency-symbol"><?php echo getCurrencySymbol(); ?></span>
                                <input type="number" class="form-control" id="amount" name="amount" step="0.01" min="0.01" required>
                                <div class="invalid-feedback">
                                    <?php echo __('please_enter_valid_amount'); ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="start_date"><?php echo __('start_date'); ?></label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo date('Y-m-d'); ?>" required>
                            <div class="invalid-feedback">
                                <?php echo __('please_select_start_date'); ?>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="end_date"><?php echo __('end_date'); ?></label>
                            <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo date('Y-m-d', strtotime('+1 month')); ?>" required>
                            <div class="invalid-feedback">
                                <?php echo __('please_select_end_date'); ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="suggestion-section">
                        <button type="button" id="suggestAmount" class="btn btn-outline btn-sm">
                            <i class="fas fa-magic"></i>
                            <span><?php echo __('suggest_amount'); ?></span>
                        </button>
                        <div id="suggestionResult" class="suggestion-result"></div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" data-bs-dismiss="modal"><?php echo __('cancel'); ?></button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        <span><?php echo __('add_budget'); ?></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Budget Modal -->
<div class="modal fade" id="editBudgetModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-pencil-alt"></i>
                    <span><?php echo __('edit_budget'); ?></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?php echo BASE_PATH; ?>/budget" method="post" id="editBudgetForm" class="needs-validation" novalidate>
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="budget_id" id="edit_budget_id">
                
                <div class="modal-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="edit_category_id"><?php echo __('category'); ?></label>
                            <select class="form-control" id="edit_category_id" name="category_id" required>
                                <option value=""><?php echo __('select_category'); ?></option>
                                <?php 
                                if (isset($categories) && $categories->num_rows > 0) {
                                    $categories->data_seek(0);
                                    while ($category = $categories->fetch_assoc()): 
                                        // Get translated category name
                                        $translated_name = $expense->getTranslatedCategoryName($category['name']);
                                ?>
                                    <option value="<?php echo $category['category_id']; ?>" 
                                            <?php echo ($category['name'] === 'Investments') ? 'class="investment-option"' : ''; ?>>
                                        <?php echo htmlspecialchars($translated_name); ?>
                                        <?php echo ($category['name'] === 'Investments') ? ' 💎' : ''; ?>
                                    </option>
                                <?php 
                                    endwhile;
                                }
                                ?>
                            </select>
                            <div class="invalid-feedback">
                                <?php echo __('please_select_category'); ?>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_amount"><?php echo __('amount'); ?></label>
                            <div class="amount-input">
                                <span class="currency-symbol"><?php echo getCurrencySymbol(); ?></span>
                                <input type="number" class="form-control" id="edit_amount" name="amount" step="0.01" min="0.01" required>
                                <div class="invalid-feedback">
                                    <?php echo __('please_enter_valid_amount'); ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_start_date"><?php echo __('start_date'); ?></label>
                            <input type="date" class="form-control" id="edit_start_date" name="start_date" required>
                            <div class="invalid-feedback">
                                <?php echo __('please_select_start_date'); ?>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_end_date"><?php echo __('end_date'); ?></label>
                            <input type="date" class="form-control" id="edit_end_date" name="end_date" required>
                            <div class="invalid-feedback">
                                <?php echo __('please_select_end_date'); ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="budget-stats" id="editBudgetStats">
                        <!-- Will be populated via JS -->
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" data-bs-dismiss="modal"><?php echo __('cancel'); ?></button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        <span><?php echo __('save_changes'); ?></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Budget Modal -->
<div class="modal fade" id="deleteBudgetModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-trash-alt"></i>
                    <span><?php echo __('delete_budget'); ?></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="confirm-message"><?php echo __('confirm_delete_budget'); ?></p>
                <div class="delete-details">
                    <div class="detail-item">
                        <span class="label"><?php echo __('category'); ?>:</span>
                        <span class="value" id="deleteCategoryName"></span>
                    </div>
                    <div class="detail-item">
                        <span class="label"><?php echo __('amount'); ?>:</span>
                        <span class="value" id="deleteBudgetAmount"></span>
                    </div>
                </div>
                <div class="warning-message">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span><?php echo __('action_cannot_be_undone'); ?></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" data-bs-dismiss="modal"><?php echo __('cancel'); ?></button>
                <form action="<?php echo BASE_PATH; ?>/budget" method="post" id="deleteBudgetForm">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="budget_id" id="delete_budget_id">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash-alt"></i>
                        <span><?php echo __('delete'); ?></span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Auto Generate Budget Modal -->
<div class="modal fade" id="generateBudgetModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-magic"></i>
                    <span><?php echo __('auto_generate_budget'); ?></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?php echo BASE_PATH; ?>/budget" method="post" id="generateBudgetForm">
                <input type="hidden" name="action" value="generate_plan">
                <input type="hidden" name="replace_existing" value="1">
                
                <div class="modal-body">
                    <?php if (isset($monthly_income) && $monthly_income > 0): ?>
                        <div class="info-box">
                            <i class="fas fa-info-circle"></i>
                            <p><?php echo __('auto_generate_info'); ?></p>
                        </div>
                        
                        <div class="income-display">
                            <span class="label"><?php echo __('monthly_income'); ?>:</span>
                            <span class="value"><?php echo formatMoney($monthly_income); ?></span>
                        </div>
                        
                        <div class="generation-options">
                            <div class="option-group">
                                <label><?php echo __('investment_priority'); ?></label>
                                <div class="slider-container">
                                    <input type="range" min="10" max="30" value="15" class="slider" id="investmentPercentage" name="investment_percentage">
                                    <div class="slider-value" id="investmentValue">15%</div>
                                </div>
                                <div class="slider-labels">
                                    <span>10%</span>
                                    <span>30%</span>
                                </div>
                            </div>
                            
                            <div class="option-group checkbox-group">
                                <label class="checkbox-container">
                                    <input type="checkbox" id="useHistorical" name="use_historical" checked>
                                    <span class="checkmark"></span>
                                    <span class="label-text"><?php echo __('use_spending_history'); ?></span>
                                </label>
                                <p class="option-description"><?php echo __('spending_history_description'); ?></p>
                            </div>
                        </div>
                        
                        <div class="generation-features">
                            <h4><?php echo __('generated_budget_includes'); ?>:</h4>
                            <ul class="feature-list">
                                <li><?php echo __('investment_allocation'); ?></li>
                                <li><?php echo __('essential_expenses'); ?></li>
                                <li><?php echo __('balanced_discretionary'); ?></li>
                                <li><?php echo __('financial_goals_alignment'); ?></li>
                            </ul>
                        </div>
                        
                        <div class="warning-box">
                            <i class="fas fa-exclamation-triangle"></i>
                            <p><?php echo __('replace_existing_budgets_warning'); ?></p>
                        </div>
                    <?php else: ?>
                        <div class="error-box">
                            <i class="fas fa-exclamation-circle"></i>
                            <p><?php echo __('no_income_error'); ?></p>
                            <a href="<?php echo BASE_PATH; ?>/income" class="btn btn-outline btn-sm">
                                <?php echo __('add_income'); ?> <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" data-bs-dismiss="modal"><?php echo __('cancel'); ?></button>
                    <button type="submit" class="btn btn-primary" <?php echo (isset($monthly_income) && $monthly_income > 0) ? '' : 'disabled'; ?>>
                        <i class="fas fa-magic"></i>
                        <span><?php echo __('generate_budget'); ?></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Category Details Modal -->
<div class="modal fade" id="categoryDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="categoryDetailsTitle"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="category-summary">
                    <div class="summary-items">
                        <div class="summary-item">
                            <span class="label"><?php echo __('budget'); ?></span>
                            <span class="value" id="detailsBudgetAmount"></span>
                        </div>
                        <div class="summary-item">
                            <span class="label"><?php echo __('spent'); ?></span>
                            <span class="value" id="detailsSpentAmount"></span>
                        </div>
                        <div class="summary-item">
                            <span class="label"><?php echo __('remaining'); ?></span>
                            <span class="value" id="detailsRemainingAmount"></span>
                        </div>
                        <div class="summary-item">
                            <span class="label"><?php echo __('used'); ?></span>
                            <span class="value" id="detailsUsagePercentage"></span>
                        </div>
                    </div>
                    
                    <div class="progress-bar">
                        <div class="progress-fill" id="detailsProgressFill"></div>
                    </div>
                </div>
                
                <div class="details-tabs">
                    <div class="tab-navigation">
                        <button class="tab-btn active" data-tab="transactions"><?php echo __('transactions'); ?></button>
                        <button class="tab-btn" data-tab="trend"><?php echo __('trend'); ?></button>
                        <button class="tab-btn" data-tab="forecast"><?php echo __('forecast'); ?></button>
                    </div>
                    
                    <div class="tab-content">
                        <div class="tab-pane active" id="transactionsTab">
                            <div class="transactions-placeholder">
                                <i class="fas fa-receipt"></i>
                                <p><?php echo __('loading_transactions'); ?></p>
                            </div>
                        </div>
                        
                        <div class="tab-pane" id="trendTab">
                            <div class="trend-chart-container">
                                <canvas id="trendChart"></canvas>
                            </div>
                        </div>
                        
                        <div class="tab-pane" id="forecastTab">
                            <div class="forecast-content">
                                <div class="forecast-items">
                                    <div class="forecast-item">
                                        <span class="label"><?php echo __('monthly_forecast'); ?></span>
                                        <span class="value" id="detailsForecastAmount"></span>
                                    </div>
                                    <div class="forecast-item">
                                        <span class="label"><?php echo __('estimated_month_end'); ?></span>
                                        <span class="value" id="detailsEstimatedFinal"></span>
                                    </div>
                                </div>
                                
                                <div class="forecast-status" id="forecastStatus">
                                    <!-- Will be filled by JS -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-edit" id="editFromDetails">
                    <i class="fas fa-pencil-alt"></i>
                    <span><?php echo __('edit_budget'); ?></span>
                </button>
                <button type="button" class="btn btn-outline" data-bs-dismiss="modal"><?php echo __('close'); ?></button>
            </div>
        </div>
    </div>
</div>

<?php
// Add BASE_PATH as a global JavaScript variable to use in AJAX requests
echo '<script>const BASE_PATH = "' . BASE_PATH . '";</script>';

// Add currency symbol meta tag for JavaScript
echo '<meta name="currency-symbol" content="' . htmlspecialchars(getCurrencySymbol()) . '">';

// Add translation meta tags for JavaScript
echo '<meta name="no-matching-budgets" content="' . htmlspecialchars(__('no_matching_budgets_found')) . '">';
echo '<meta name="error-load-budget" content="' . htmlspecialchars(__('failed_to_load_budget')) . '">';
echo '<meta name="forecast-within-budget" content="' . htmlspecialchars(__('forecast_within_budget')) . '">';
echo '<meta name="forecast-exceeds-budget" content="' . htmlspecialchars(__('forecast_exceeds_budget')) . '">';

// Additional JS
$additional_js = ['/assets/js/budget-modern.js'];

// Include footer
require_once 'includes/footer.php';
?>