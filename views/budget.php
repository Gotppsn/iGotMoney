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

<!-- Main Content Wrapper -->
<div class="budget-page">
    <!-- Page Header Section -->
    <div class="page-header-section">
        <div class="page-header-content">
            <div class="page-title-group">
                <h1 class="page-title"><?php echo __('budget_management'); ?></h1>
                <p class="page-subtitle"><?php echo __('budget_subtitle'); ?></p>
            </div>
            <div class="page-actions">
                <button type="button" class="btn-generate-budget" data-bs-toggle="modal" data-bs-target="#generateBudgetModal">
                    <i class="fas fa-magic"></i>
                    <span><?php echo __('auto_generate'); ?></span>
                </button>
                <button type="button" class="btn-add-budget" data-bs-toggle="modal" data-bs-target="#addBudgetModal">
                    <i class="fas fa-plus-circle"></i>
                    <span><?php echo __('add_budget'); ?></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="filter-section">
        <div class="filter-container">
            <div class="filter-toggle" id="filterToggle">
                <i class="fas fa-filter"></i>
                <span><?php echo __('filter'); ?></span>
                <i class="fas fa-chevron-down filter-chevron"></i>
            </div>
            <div class="filter-dropdown" id="filterDropdown">
                <div class="filter-group">
                    <label for="filterCategory"><?php echo __('category'); ?></label>
                    <select id="filterCategory" class="filter-select">
                        <option value="all"><?php echo __('all_categories'); ?></option>
                        <?php 
                        if (isset($categories) && $categories->num_rows > 0) {
                            $categories->data_seek(0);
                            while ($category = $categories->fetch_assoc()): 
                                $translated_name = $expense->getTranslatedCategoryName($category['name']);
                        ?>
                            <option value="<?php echo $category['category_id']; ?>">
                                <?php echo htmlspecialchars($translated_name); ?>
                            </option>
                        <?php 
                            endwhile;
                        }
                        ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="filterSpendingStatus"><?php echo __('status'); ?></label>
                    <select id="filterSpendingStatus" class="filter-select">
                        <option value="all"><?php echo __('all'); ?></option>
                        <option value="good"><?php echo __('good'); ?></option>
                        <option value="warning"><?php echo __('warning'); ?></option>
                        <option value="critical"><?php echo __('critical'); ?></option>
                    </select>
                </div>
                <div class="filter-actions">
                    <button id="applyFilters" class="filter-button apply"><?php echo __('apply_filter'); ?></button>
                    <button id="resetFilters" class="filter-button reset"><?php echo __('reset'); ?></button>
                </div>
            </div>
        </div>
        <div class="search-box-container">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="globalSearch" placeholder="<?php echo __('search_categories'); ?>">
            </div>
        </div>
    </div>

    <!-- Quick Stats Section -->
    <div class="quick-stats-section">
        <div class="stats-grid">
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
            
            <div class="stat-card overview">
                <div class="stat-icon">
                    <i class="fas fa-wallet"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-label"><?php echo __('total_budget'); ?></h3>
                    <p class="stat-value"><?php echo formatMoney($total_budget); ?></p>
                    <div class="stat-progress">
                        <div class="progress-track">
                            <div class="progress-fill <?php echo $budget_percentage >= 90 ? 'danger' : ($budget_percentage >= 70 ? 'warning' : 'success'); ?>" 
                                 style="width: <?php echo $budget_percentage; ?>%"></div>
                        </div>
                        <span class="progress-text"><?php echo number_format($budget_percentage, 0); ?>% <?php echo __('used'); ?></span>
                    </div>
                    <div class="stat-info">
                        <i class="fas fa-calendar"></i>
                        <span><?php echo __('this_month'); ?></span>
                    </div>
                </div>
            </div>
            
            <div class="stat-card health">
                <div class="stat-icon">
                    <i class="fas fa-heartbeat"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-label"><?php echo __('budget_health'); ?></h3>
                    <p class="stat-value"><?php echo number_format($budget_health, 0); ?>%</p>
                    <div class="stat-progress">
                        <div class="progress-track">
                            <div class="progress-fill <?php echo $budget_health >= 60 ? 'success' : ($budget_health >= 30 ? 'warning' : 'danger'); ?>" 
                                 style="width: <?php echo $budget_health; ?>%"></div>
                        </div>
                        <span class="progress-text"><?php echo $budget_health >= 60 ? __('healthy') : ($budget_health >= 30 ? __('needs_attention') : __('critical')); ?></span>
                    </div>
                    <div class="stat-trend <?php echo $budget_health >= 60 ? 'positive' : ($budget_health >= 30 ? 'warning' : 'negative'); ?>">
                        <i class="fas fa-<?php echo $budget_health >= 60 ? 'check-circle' : ($budget_health >= 30 ? 'exclamation-triangle' : 'exclamation-circle'); ?>"></i>
                        <span><?php echo formatMoney($total_remaining); ?> <?php echo __('remaining'); ?></span>
                    </div>
                </div>
            </div>
            
            <div class="stat-card investment">
                <div class="stat-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-label"><?php echo __('investment_budget'); ?></h3>
                    <p class="stat-value"><?php echo formatMoney($investment_budget); ?></p>
                    <?php if ($investment_budget > 0): ?>
                        <div class="stat-progress">
                            <div class="progress-track">
                                <div class="progress-fill info" style="width: <?php echo $investment_percentage; ?>%"></div>
                            </div>
                            <span class="progress-text"><?php echo number_format($investment_percentage, 0); ?>% <?php echo __('used'); ?></span>
                        </div>
                        <div class="stat-trend <?php echo $investment_spent >= $investment_budget ? 'positive' : 'warning'; ?>">
                            <i class="fas fa-<?php echo $investment_spent >= $investment_budget ? 'check' : 'arrow-up'; ?>"></i>
                            <span><?php echo $investment_spent >= $investment_budget ? __('goal_reached') : formatMoney($investment_budget - $investment_spent) . ' ' . __('remaining'); ?></span>
                        </div>
                    <?php else: ?>
                        <div class="stat-info add-investment-prompt">
                            <i class="fas fa-info-circle"></i>
                            <span><?php echo __('not_set'); ?></span>
                            <button class="btn-mini" onclick="selectInvestmentCategory()" data-bs-toggle="modal" data-bs-target="#addBudgetModal">
                                <i class="fas fa-plus-circle"></i>
                                <span><?php echo __('add'); ?></span>
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="charts-section">
        <div class="charts-grid">
            <!-- Budget Overview Chart -->
            <div class="chart-card budget-overview">
                <div class="chart-header">
                    <div class="chart-title">
                        <i class="fas fa-chart-pie"></i>
                        <h3><?php echo __('budget_overview'); ?></h3>
                    </div>
                    <div class="chart-actions">
                        <div class="chart-period-selector">
                            <button class="period-btn active" data-period="month"><?php echo __('this_month'); ?></button>
                            <button class="period-btn" data-period="quarter"><?php echo __('quarter'); ?></button>
                            <button class="period-btn" data-period="year"><?php echo __('year'); ?></button>
                        </div>
                    </div>
                </div>
                <div class="chart-body">
                    <div class="budget-gauge-container">
                        <div class="gauge-wrapper">
                            <div class="modern-gauge" data-percentage="<?php echo $budget_health; ?>">
                                <svg viewBox="0 0 200 100" class="gauge-svg">
                                    <path d="M20 100 A80 80 0 0 1 180 100" fill="none" stroke="#e5e7eb" stroke-width="20" stroke-linecap="round"/>
                                    <path d="M20 100 A80 80 0 0 1 180 100" fill="none" stroke="currentColor" stroke-width="20" stroke-linecap="round" 
                                          stroke-dasharray="251.2" stroke-dashoffset="<?php echo 251.2 * (1 - $budget_health / 100); ?>"
                                          class="gauge-progress"/>
                                </svg>
                                <div class="gauge-center">
                                    <span class="gauge-value"><?php echo number_format($budget_health, 0); ?>%</span>
                                    <span class="gauge-label"><?php echo __('remaining'); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="budget-breakdown">
                            <div class="breakdown-item">
                                <span class="breakdown-label"><?php echo __('total_budget'); ?></span>
                                <span class="breakdown-value"><?php echo formatMoney($total_budget); ?></span>
                            </div>
                            <div class="breakdown-item">
                                <span class="breakdown-label"><?php echo __('spent'); ?></span>
                                <span class="breakdown-value text-danger"><?php echo formatMoney($total_spent); ?></span>
                            </div>
                            <div class="breakdown-item">
                                <span class="breakdown-label"><?php echo __('remaining'); ?></span>
                                <span class="breakdown-value text-success"><?php echo formatMoney($total_remaining); ?></span>
                            </div>
                            <div class="breakdown-item forecast">
                                <span class="breakdown-label"><?php echo __('projected_for_month'); ?></span>
                                <?php 
                                // Calculate simple forecast based on current day of month
                                $current_day = date('j');
                                $days_in_month = date('t');
                                $forecast = ($total_spent / $current_day) * $days_in_month;
                                $forecast_status = $forecast <= $total_budget ? 'text-success' : 'text-danger';
                                ?>
                                <span class="breakdown-value <?php echo $forecast_status; ?>">
                                    <?php echo formatMoney($forecast); ?>
                                    <i class="fas fa-<?php echo $forecast <= $total_budget ? 'check-circle' : 'exclamation-circle'; ?> tooltip-icon" 
                                       data-bs-toggle="tooltip" 
                                       title="<?php echo $forecast <= $total_budget ? __('forecast_within_budget') : __('forecast_exceeds_budget'); ?>"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Category Distribution -->
            <div class="chart-card category-distribution">
                <div class="chart-header">
                    <div class="chart-title">
                        <i class="fas fa-list-ol"></i>
                        <h3><?php echo __('top_budget_categories'); ?></h3>
                    </div>
                    <div class="chart-actions">
                        <button class="btn-mini view-all-btn" id="viewAllCategoriesBtn">
                            <i class="fas fa-eye"></i>
                            <span><?php echo __('view_all'); ?></span>
                        </button>
                    </div>
                </div>
                <div class="chart-body">
                    <div class="categories-list-content">
                        <?php if (!empty($budget_status)): ?>
                            <?php 
                            // Sort budgets by amount
                            usort($budget_status, function($a, $b) {
                                return $b['budget_amount'] - $a['budget_amount'];
                            });
                            
                            $rank = 1;
                            foreach (array_slice($budget_status, 0, 5) as $budget): 
                                $percentage = ($budget['spent'] / max(0.01, $budget['budget_amount'])) * 100;
                                // Get translated category name
                                $translated_category_name = $expense->getTranslatedCategoryName($budget['category_name']);
                            ?>
                                <div class="category-item" data-category-id="<?php echo $budget['category_id']; ?>">
                                    <div class="category-rank"><?php echo $rank++; ?></div>
                                    <div class="category-info">
                                        <h4 class="category-name">
                                            <?php echo htmlspecialchars($translated_category_name); ?>
                                            <?php if ($budget['is_investment']): ?>
                                                <i class="fas fa-gem text-info ms-1"></i>
                                            <?php endif; ?>
                                        </h4>
                                        <div class="category-progress">
                                            <div class="progress-track">
                                                <div class="progress-fill <?php echo $percentage >= 90 ? 'danger' : ($percentage >= 70 ? 'warning' : 'success'); ?>" 
                                                     style="width: <?php echo min(100, $percentage); ?>%"></div>
                                            </div>
                                            <span class="progress-text"><?php echo number_format($percentage, 0); ?>%</span>
                                        </div>
                                    </div>
                                    <div class="category-amount">
                                        <span class="amount"><?php echo formatMoney($budget['budget_amount']); ?></span>
                                        <span class="spent"><?php echo formatMoney($budget['spent']); ?> <?php echo __('spent'); ?></span>
                                    </div>
                                    <div class="category-actions">
                                        <button class="btn-action edit-quick" data-budget-id="<?php echo $budget['budget_id']; ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-categories">
                                <div class="empty-icon">
                                    <i class="fas fa-folder-open"></i>
                                </div>
                                <p><?php echo __('no_budgets_created_yet'); ?></p>
                                <button class="btn-add-first" data-bs-toggle="modal" data-bs-target="#addBudgetModal">
                                    <i class="fas fa-plus"></i>
                                    <?php echo __('add_your_first_budget'); ?>
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Budget Categories Table Section -->
    <div class="budget-table-section">
        <div class="table-card">
            <div class="table-header">
                <div class="table-title">
                    <i class="fas fa-tasks"></i>
                    <h3><?php echo __('budget_categories'); ?></h3>
                </div>
                <div class="table-controls">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="categorySearch" placeholder="<?php echo __('search_categories'); ?>" data-table-search="budgetTable">
                    </div>
                    <div class="sort-options">
                        <span><?php echo __('sort_by'); ?>:</span>
                        <select id="sortCriteria" class="sort-select">
                            <option value="name"><?php echo __('name'); ?></option>
                            <option value="amount"><?php echo __('amount'); ?></option>
                            <option value="spent"><?php echo __('spent'); ?></option>
                            <option value="remaining"><?php echo __('remaining'); ?></option>
                            <option value="percentage"><?php echo __('percentage'); ?></option>
                        </select>
                        <button id="sortDirection" class="sort-direction-btn" data-direction="asc">
                            <i class="fas fa-sort-amount-down-alt"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="table-body">
                <?php if (empty($budget_status)): ?>
                    <div class="table-empty">
                        <div class="empty-icon">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                        <h4><?php echo __('no_budgets_defined_yet'); ?></h4>
                        <p><?php echo __('start_by_creating_budget'); ?></p>
                        <div class="empty-actions">
                            <button type="button" class="btn-add-first" data-bs-toggle="modal" data-bs-target="#addBudgetModal">
                                <i class="fas fa-plus"></i>
                                <?php echo __('add_your_first_budget'); ?>
                            </button>
                            <span class="action-separator"><?php echo __('or'); ?></span>
                            <button type="button" class="btn-generate-first" data-bs-toggle="modal" data-bs-target="#generateBudgetModal">
                                <i class="fas fa-magic"></i>
                                <?php echo __('auto_generate_budget'); ?>
                            </button>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="budget-table" id="budgetTable">
                            <thead>
                                <tr>
                                    <th data-sort="name"><?php echo __('category'); ?> <i class="fas fa-sort"></i></th>
                                    <th data-sort="budget"><?php echo __('budget'); ?> <i class="fas fa-sort"></i></th>
                                    <th data-sort="spent"><?php echo __('spent'); ?> <i class="fas fa-sort"></i></th>
                                    <th data-sort="remaining"><?php echo __('remaining'); ?> <i class="fas fa-sort"></i></th>
                                    <th data-sort="progress"><?php echo __('progress'); ?> <i class="fas fa-sort"></i></th>
                                    <th data-sort="status"><?php echo __('status'); ?> <i class="fas fa-sort"></i></th>
                                    <th><?php echo __('actions'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($budget_status as $budget): 
                                    // Get translated category name
                                    $translated_category = $expense->getTranslatedCategoryName($budget['category_name']);
                                ?>
                                    <tr class="<?php echo $budget['is_investment'] ? 'investment-row' : ''; ?>" 
                                        data-category-id="<?php echo $budget['category_id']; ?>"
                                        data-budget-id="<?php echo $budget['budget_id']; ?>"
                                        data-percentage="<?php echo $budget['percentage']; ?>"
                                        data-budget-amount="<?php echo $budget['budget_amount']; ?>"
                                        data-spent="<?php echo $budget['spent']; ?>"
                                        data-available="<?php echo $budget['available']; ?>"
                                        data-status="<?php echo $budget['percentage'] >= 90 ? 'critical' : ($budget['percentage'] >= 70 ? 'warning' : 'good'); ?>">
                                        <td class="category-cell">
                                            <span class="category-text">
                                                <?php if ($budget['is_investment']): ?>
                                                    <i class="fas fa-gem text-info me-2"></i>
                                                <?php endif; ?>
                                                <?php echo htmlspecialchars($translated_category); ?>
                                            </span>
                                        </td>
                                        <td class="amount-cell"><?php echo formatMoney($budget['budget_amount']); ?></td>
                                        <td class="amount-cell"><?php echo formatMoney($budget['spent']); ?></td>
                                        <td class="amount-cell">
                                            <span class="<?php echo $budget['available'] < 0 ? 'text-danger' : 'text-success'; ?>">
                                                <?php echo formatMoney($budget['available']); ?>
                                            </span>
                                        </td>
                                        <td class="progress-cell">
                                            <?php
                                            $progress_class = 'success';
                                            if ($budget['percentage'] >= 90) {
                                                $progress_class = 'danger';
                                            } elseif ($budget['percentage'] >= 70) {
                                                $progress_class = 'warning';
                                            }
                                            
                                            if ($budget['is_investment']) {
                                                $progress_class = 'info';
                                            }
                                            ?>
                                            <div class="budget-progress">
                                                <div class="progress-bar <?php echo $progress_class; ?>" 
                                                     style="width: <?php echo min(100, $budget['percentage']); ?>%"></div>
                                                <span class="progress-label"><?php echo number_format($budget['percentage'], 0); ?>%</span>
                                            </div>
                                        </td>
                                        <td class="status-cell">
                                            <?php
                                            if ($budget['is_investment']) {
                                                if ($budget['percentage'] >= 100) {
                                                    echo '<span class="status-badge success">' . __('on_track') . '</span>';
                                                } else {
                                                    echo '<span class="status-badge warning">' . __('invest_more') . '</span>';
                                                }
                                            } else {
                                                if ($budget['percentage'] >= 90) {
                                                    echo '<span class="status-badge danger">' . __('critical') . '</span>';
                                                } elseif ($budget['percentage'] >= 70) {
                                                    echo '<span class="status-badge warning">' . __('warning') . '</span>';
                                                } else {
                                                    echo '<span class="status-badge success">' . __('good') . '</span>';
                                                }
                                            }
                                            ?>
                                        </td>
                                        <td class="actions-cell">
                                            <div class="action-buttons">
                                                <button class="btn-action edit" data-budget-id="<?php echo $budget['budget_id']; ?>" title="<?php echo __('edit'); ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn-action delete" data-budget-id="<?php echo $budget['budget_id']; ?>" title="<?php echo __('delete'); ?>">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="table-empty" style="display: none;" id="noMatchingBudgets">
                        <div class="empty-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <h4><?php echo __('no_matching_budgets_found'); ?></h4>
                        <p><?php echo __('try_adjusting_your_search'); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Budget Recommendations -->
    <?php if (isset($monthly_income) && $monthly_income > 0 && isset($budget_plan) && !empty($budget_plan)): ?>
    <div class="recommendations-section">
        <div class="recommendations-card">
            <div class="card-header">
                <div class="card-title">
                    <i class="fas fa-lightbulb"></i>
                    <h3><?php echo __('smart_budget_recommendations'); ?></h3>
                </div>
                <button class="btn-adopt-all" id="adoptAllRecommendations" data-bs-toggle="tooltip" title="<?php echo __('adopt_all_recommendations_tooltip'); ?>">
                    <i class="fas fa-check"></i>
                    <?php echo __('adopt_all'); ?>
                </button>
            </div>
            <div class="card-body">
                <div class="recommendation-info">
                    <i class="fas fa-info-circle"></i>
                    <p><?php echo __('based_on_your_income'); ?> <strong><?php echo formatMoney($monthly_income); ?></strong> <?php echo __('per_month'); ?>, <?php echo __('we_recommend'); ?> <strong><?php echo __('investments'); ?></strong> <?php echo __('optimizing_budget'); ?>.</p>
                </div>
                
                <div class="recommendations-grid">
                    <?php foreach ($budget_plan as $recommendation): 
                        // Get translated category name
                        $translated_category = $expense->getTranslatedCategoryName($recommendation['category_name']);
                    ?>
                        <div class="recommendation-item <?php echo $recommendation['is_investment'] ? 'investment' : ''; ?>">
                            <div class="recommendation-header">
                                <h4 class="recommendation-category">
                                    <?php if ($recommendation['is_investment']): ?>
                                        <i class="fas fa-gem text-info me-2"></i>
                                    <?php endif; ?>
                                    <?php echo htmlspecialchars($translated_category); ?>
                                </h4>
                                <span class="recommendation-amount"><?php echo formatMoney($recommendation['allocated_amount']); ?></span>
                            </div>
                            <div class="recommendation-progress">
                                <div class="progress-bar" style="width: <?php echo $recommendation['percentage']; ?>%"></div>
                                <span class="progress-percentage"><?php echo number_format($recommendation['percentage'], 1); ?>%</span>
                            </div>
                            
                            <?php
                            // Check if this category already has a budget
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
                            
                            <?php if ($existing_budget): ?>
                                <div class="recommendation-comparison">
                                    <div class="comparison-item">
                                        <span class="comparison-label"><?php echo __('current'); ?>:</span>
                                        <span class="comparison-value"><?php echo formatMoney($current_budget); ?></span>
                                    </div>
                                    <div class="comparison-item">
                                        <span class="comparison-label"><?php echo __('recommended'); ?>:</span>
                                        <span class="comparison-value"><?php echo formatMoney($recommendation['allocated_amount']); ?></span>
                                    </div>
                                    <div class="comparison-difference <?php echo $recommendation['allocated_amount'] > $current_budget ? 'positive' : ($recommendation['allocated_amount'] < $current_budget ? 'negative' : 'neutral'); ?>">
                                        <?php
                                        $difference = $recommendation['allocated_amount'] - $current_budget;
                                        $icon_class = $difference > 0 ? 'arrow-up' : ($difference < 0 ? 'arrow-down' : 'equals');
                                        ?>
                                        <i class="fas fa-<?php echo $icon_class; ?>"></i>
                                        <span><?php echo formatMoney(abs($difference)); ?></span>
                                    </div>
                                </div>
                                <button class="btn-adopt adopt-recommendation" 
                                        data-category-id="<?php echo $recommendation['category_id']; ?>"
                                        data-amount="<?php echo $recommendation['allocated_amount']; ?>"
                                        data-existing-budget-id="<?php echo $budget_id; ?>">
                                    <i class="fas fa-sync-alt"></i>
                                    <?php echo __('update'); ?>
                                </button>
                            <?php else: ?>
                                <button class="btn-adopt adopt-recommendation" 
                                        data-category-id="<?php echo $recommendation['category_id']; ?>"
                                        data-amount="<?php echo $recommendation['allocated_amount']; ?>">
                                    <i class="fas fa-plus"></i>
                                    <?php echo __('adopt'); ?>
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="recommendations-footer">
                <div class="budget-insights">
                    <div class="insight-item">
                        <i class="fas fa-chart-line"></i>
                        <div class="insight-content">
                            <h4><?php echo __('investment_insight'); ?></h4>
                            <p><?php echo __('investment_recommendation_text'); ?></p>
                        </div>
                    </div>
                    <div class="insight-item">
                        <i class="fas fa-balance-scale"></i>
                        <div class="insight-content">
                            <h4><?php echo __('balance_insight'); ?></h4>
                            <p><?php echo __('balanced_budget_recommendation_text'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Budget Tips Section -->
    <div class="budget-tips-section">
        <div class="tips-card">
            <div class="tips-header">
                <div class="tips-title">
                    <i class="fas fa-lightbulb"></i>
                    <h3><?php echo __('budget_tips_tricks'); ?></h3>
                </div>
                <button class="tips-toggle" id="tipsToggle">
                    <i class="fas fa-chevron-down"></i>
                </button>
            </div>
            <div class="tips-body" id="tipsBody">
                <div class="tips-grid">
                    <div class="tip-item">
                        <div class="tip-icon">
                            <i class="fas fa-piggy-bank"></i>
                        </div>
                        <div class="tip-content">
                            <h4><?php echo __('savings_first_tip'); ?></h4>
                            <p><?php echo __('savings_first_tip_text'); ?></p>
                        </div>
                    </div>
                    <div class="tip-item">
                        <div class="tip-icon">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                        <div class="tip-content">
                            <h4><?php echo __('50_30_20_rule'); ?></h4>
                            <p><?php echo __('50_30_20_rule_text'); ?></p>
                        </div>
                    </div>
                    <div class="tip-item">
                        <div class="tip-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="tip-content">
                            <h4><?php echo __('zero_based_budgeting'); ?></h4>
                            <p><?php echo __('zero_based_budgeting_text'); ?></p>
                        </div>
                    </div>
                    <div class="tip-item">
                        <div class="tip-icon">
                            <i class="fas fa-hand-holding-usd"></i>
                        </div>
                        <div class="tip-content">
                            <h4><?php echo __('emergency_fund_tip'); ?></h4>
                            <p><?php echo __('emergency_fund_tip_text'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
<!-- Add Budget Modal -->
<div class="modal fade modern-modal" id="addBudgetModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-icon">
                    <i class="fas fa-plus-circle"></i>
                </div>
                <h5 class="modal-title"><?php echo __('add_new_budget'); ?></h5>
                <button type="button" class="modal-close" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="<?php echo BASE_PATH; ?>/budget" method="post" id="addBudgetForm" class="needs-validation" novalidate>
                <input type="hidden" name="action" value="add">
                
                <div class="modal-body">
                    <div class="form-grid">
                        <div class="form-field">
                            <label for="category_id"><?php echo __('category'); ?></label>
                            <select class="modern-select" id="category_id" name="category_id" required>
                                <option value=""><?php echo __('please_select_category'); ?></option>
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
                        
                        <div class="form-field">
                            <label for="amount"><?php echo __('budget_amount'); ?></label>
                            <div class="amount-input">
                                <span class="currency-symbol"><?php echo getCurrencySymbol(); ?></span>
                                <input type="number" id="amount" name="amount" step="0.01" min="0.01" required>
                                <div class="invalid-feedback">
                                    <?php echo __('please_enter_valid_amount'); ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-field">
                            <label for="start_date"><?php echo __('start_date'); ?></label>
                            <input type="date" id="start_date" name="start_date" value="<?php echo date('Y-m-d'); ?>" required>
                            <div class="invalid-feedback">
                                <?php echo __('please_select_start_date'); ?>
                            </div>
                        </div>
                        
                        <div class="form-field">
                            <label for="end_date"><?php echo __('end_date'); ?></label>
                            <input type="date" id="end_date" name="end_date" value="<?php echo date('Y-m-d', strtotime('+1 month')); ?>" required>
                            <div class="invalid-feedback">
                                <?php echo __('please_select_end_date'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="budget-suggestion">
                        <button type="button" id="suggestBudgetAmount" class="suggestion-button">
                            <i class="fas fa-magic"></i>
                            <?php echo __('suggest_amount'); ?>
                        </button>
                        <div class="suggestion-info" id="suggestionInfo"></div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal"><?php echo __('cancel'); ?></button>
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-plus"></i>
                        <?php echo __('add_budget'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Budget Modal -->
<div class="modal fade modern-modal" id="editBudgetModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-icon edit">
                    <i class="fas fa-edit"></i>
                </div>
                <h5 class="modal-title"><?php echo __('edit_budget'); ?></h5>
                <button type="button" class="modal-close" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="<?php echo BASE_PATH; ?>/budget" method="post" id="editBudgetForm" class="needs-validation" novalidate>
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="budget_id" id="edit_budget_id">
                
                <div class="modal-body">
                    <div class="form-grid">
                        <div class="form-field">
                            <label for="edit_category_id"><?php echo __('category'); ?></label>
                            <select class="modern-select" id="edit_category_id" name="category_id" required>
                                <option value=""><?php echo __('please_select_category'); ?></option>
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
                        
                        <div class="form-field">
                            <label for="edit_amount"><?php echo __('budget_amount'); ?></label>
                            <div class="amount-input">
                                <span class="currency-symbol"><?php echo getCurrencySymbol(); ?></span>
                                <input type="number" id="edit_amount" name="amount" step="0.01" min="0.01" required>
                                <div class="invalid-feedback">
                                    <?php echo __('please_enter_valid_amount'); ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-field">
                            <label for="edit_start_date"><?php echo __('start_date'); ?></label>
                            <input type="date" id="edit_start_date" name="start_date" required>
                            <div class="invalid-feedback">
                                <?php echo __('please_select_start_date'); ?>
                            </div>
                        </div>
                        
                        <div class="form-field">
                            <label for="edit_end_date"><?php echo __('end_date'); ?></label>
                            <input type="date" id="edit_end_date" name="end_date" required>
                            <div class="invalid-feedback">
                                <?php echo __('please_select_end_date'); ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="budget-statistics">
                        <div class="stats-header"><?php echo __('current_statistics'); ?></div>
                        <div class="stats-grid" id="editBudgetStats">
                            <!-- Statistics will be populated by JS -->
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal"><?php echo __('cancel'); ?></button>
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save"></i>
                        <?php echo __('save_changes'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Budget Modal -->
<div class="modal fade modern-modal" id="deleteBudgetModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-icon delete">
                    <i class="fas fa-trash-alt"></i>
                </div>
                <h5 class="modal-title"><?php echo __('delete_budget'); ?></h5>
                <button type="button" class="modal-close" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body text-center">
                <p><?php echo __('are_you_sure_delete_budget'); ?></p>
                <p class="text-muted"><?php echo __('action_cannot_be_undone'); ?></p>
                <div class="delete-category-name mt-3">
                    <strong><?php echo __('category'); ?>: </strong>
                    <span id="deleteCategoryName"></span>
                </div>
                <div class="delete-budget-amount">
                    <strong><?php echo __('amount'); ?>: </strong>
                    <span id="deleteBudgetAmount"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" data-bs-dismiss="modal"><?php echo __('cancel'); ?></button>
                <form action="<?php echo BASE_PATH; ?>/budget" method="post" id="deleteBudgetForm">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="budget_id" id="delete_budget_id">
                    <button type="submit" class="btn-submit danger">
                        <i class="fas fa-trash-alt"></i>
                        <?php echo __('delete'); ?>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Generate Budget Modal -->
<div class="modal fade modern-modal" id="generateBudgetModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-icon generate">
                    <i class="fas fa-magic"></i>
                </div>
                <h5 class="modal-title"><?php echo __('auto_generate_budget'); ?></h5>
                <button type="button" class="modal-close" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="<?php echo BASE_PATH; ?>/budget" method="post" id="generateBudgetForm">
                <input type="hidden" name="action" value="generate_plan">
                <input type="hidden" name="replace_existing" value="1">
                
                <div class="modal-body">
                    <?php if (isset($monthly_income) && $monthly_income > 0): ?>
                        <div class="alert-info">
                            <i class="fas fa-info-circle"></i>
                            <p><?php echo __('budget_info'); ?></p>
                        </div>
                        
                        <div class="income-display">
                            <span class="income-label"><?php echo __('your_current_monthly_income'); ?></span>
                            <span class="income-amount"><?php echo formatMoney($monthly_income); ?></span>
                        </div>
                        
                        <div class="generation-options">
                            <div class="option-group">
                                <label class="option-label"><?php echo __('investment_priority'); ?></label>
                                <div class="range-slider-container">
                                    <input type="range" min="10" max="30" value="15" class="range-slider" id="investmentPercentage">
                                    <div class="range-value" id="investmentValue">15%</div>
                                </div>
                                <div class="range-labels">
                                    <span>10%</span>
                                    <span>30%</span>
                                </div>
                            </div>
                            <div class="option-group">
                                <label class="checkbox-container">
                                    <input type="checkbox" id="useHistoricalData" name="use_historical" value="1" checked>
                                    <span class="checkmark"></span>
                                    <?php echo __('use_spending_history'); ?>
                                </label>
                                <div class="option-hint"><?php echo __('spending_history_hint'); ?></div>
                            </div>
                        </div>
                        
                        <div class="generation-info">
                            <h6><?php echo __('budget_will_be_created_with'); ?></h6>
                            <ul>
                                <li><strong><?php echo __('minimum_for_investments'); ?></strong></li>
                                <li><?php echo __('smart_allocation'); ?></li>
                                <li><?php echo __('optimal_financial_ratios'); ?></li>
                            </ul>
                        </div>
                        
                        <div class="alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <p><?php echo __('replace_existing_budgets'); ?></p>
                        </div>
                    <?php else: ?>
                        <div class="alert-danger">
                            <i class="fas fa-exclamation-triangle"></i>
                            <p><?php echo __('need_to_add_income'); ?>
                               <a href="<?php echo BASE_PATH; ?>/income" class="alert-link"><?php echo __('go_to_income_management'); ?></a> <?php echo __('to_add_your_income_sources'); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal"><?php echo __('cancel'); ?></button>
                    <button type="submit" class="btn-submit" <?php echo (isset($monthly_income) && $monthly_income > 0) ? '' : 'disabled'; ?>>
                        <i class="fas fa-magic"></i>
                        <?php echo __('generate_budget'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Quick Edit Modal -->
<div class="modal fade modern-modal" id="quickEditModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-icon edit">
                    <i class="fas fa-sliders-h"></i>
                </div>
                <h5 class="modal-title"><?php echo __('quick_adjust'); ?></h5>
                <button type="button" class="modal-close" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="<?php echo BASE_PATH; ?>/budget" method="post" id="quickEditForm">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="budget_id" id="quick_edit_budget_id">
                <input type="hidden" name="category_id" id="quick_edit_category_id">
                <input type="hidden" name="start_date" id="quick_edit_start_date">
                <input type="hidden" name="end_date" id="quick_edit_end_date">
                
                <div class="modal-body">
                    <div class="quick-edit-category">
                        <h4 id="quickEditCategoryName"></h4>
                    </div>
                    
                    <div class="quick-edit-current">
                        <div class="current-amount">
                            <span class="label"><?php echo __('current_budget'); ?>:</span>
                            <span class="value" id="quickEditCurrentAmount"></span>
                        </div>
                        <div class="spent-amount">
                            <span class="label"><?php echo __('spent'); ?>:</span>
                            <span class="value" id="quickEditSpentAmount"></span>
                        </div>
                        <div class="usage-percentage">
                            <div class="progress-track">
                                <div class="progress-fill" id="quickEditProgressFill"></div>
                            </div>
                            <span class="progress-text" id="quickEditPercentage"></span>
                        </div>
                    </div>
                    
                    <div class="quick-edit-amount">
                        <label for="quick_edit_amount"><?php echo __('adjust_budget_amount'); ?></label>
                        <div class="amount-input">
                            <span class="currency-symbol"><?php echo getCurrencySymbol(); ?></span>
                            <input type="number" id="quick_edit_amount" name="amount" step="0.01" min="0.01" required>
                        </div>
                    </div>
                    
                    <div class="quick-actions">
                        <button type="button" class="quick-action-btn decrease-10" id="decrease10">-10%</button>
                        <button type="button" class="quick-action-btn decrease-5" id="decrease5">-5%</button>
                        <button type="button" class="quick-action-btn increase-5" id="increase5">+5%</button>
                        <button type="button" class="quick-action-btn increase-10" id="increase10">+10%</button>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal"><?php echo __('cancel'); ?></button>
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save"></i>
                        <?php echo __('save'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Category Details Modal -->
<div class="modal fade modern-modal" id="categoryDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-icon">
                    <i class="fas fa-folder-open"></i>
                </div>
                <h5 class="modal-title" id="categoryDetailsTitle"></h5>
                <button type="button" class="modal-close" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="category-summary">
                    <div class="summary-stats">
                        <div class="summary-stat">
                            <span class="summary-label"><?php echo __('budget'); ?></span>
                            <span class="summary-value" id="detailsBudgetAmount"></span>
                        </div>
                        <div class="summary-stat">
                            <span class="summary-label"><?php echo __('spent'); ?></span>
                            <span class="summary-value" id="detailsSpentAmount"></span>
                        </div>
                        <div class="summary-stat">
                            <span class="summary-label"><?php echo __('remaining'); ?></span>
                            <span class="summary-value" id="detailsRemainingAmount"></span>
                        </div>
                        <div class="summary-stat">
                            <span class="summary-label"><?php echo __('usage'); ?></span>
                            <span class="summary-value" id="detailsUsagePercentage"></span>
                        </div>
                    </div>
                    <div class="summary-progress">
                        <div class="progress-track">
                            <div class="progress-fill" id="detailsProgressFill"></div>
                        </div>
                    </div>
                </div>
                
                <div class="details-tabs">
                    <div class="tab-buttons">
                        <button class="tab-button active" data-tab="expenses"><?php echo __('expenses'); ?></button>
                        <button class="tab-button" data-tab="trends"><?php echo __('trends'); ?></button>
                        <button class="tab-button" data-tab="forecast"><?php echo __('forecast'); ?></button>
                    </div>
                    <div class="tab-content">
                        <div class="tab-pane active" id="expensesTab">
                            <div class="category-expenses">
                                <div class="expenses-placeholder">
                                    <i class="fas fa-receipt"></i>
                                    <p><?php echo __('expenses_display_here'); ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="trendsTab">
                            <div class="trend-chart-container">
                                <canvas id="categoryTrendChart"></canvas>
                            </div>
                        </div>
                        <div class="tab-pane" id="forecastTab">
                            <div class="forecast-info">
                                <div class="forecast-item">
                                    <span class="forecast-label"><?php echo __('monthly_forecast'); ?></span>
                                    <span class="forecast-value" id="detailsForecastAmount"></span>
                                </div>
                                <div class="forecast-item">
                                    <span class="forecast-label"><?php echo __('estimated_end_month'); ?></span>
                                    <span class="forecast-value" id="detailsEstimatedFinal"></span>
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
                <button type="button" class="btn-edit-category" id="editCategoryFromDetails">
                    <i class="fas fa-edit"></i>
                    <?php echo __('edit_budget'); ?>
                </button>
                <button type="button" class="btn-cancel" data-bs-dismiss="modal"><?php echo __('close'); ?></button>
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
echo '<meta name="try-adjusting-search" content="' . htmlspecialchars(__('try_adjusting_your_search')) . '">';
echo '<meta name="error-load-budget" content="' . htmlspecialchars(__('failed_to_load_budget')) . '">';
echo '<meta name="error-load-budget-general" content="' . htmlspecialchars(__('error_occurred_loading_budget')) . '">';
echo '<meta name="confirm-adopt-all" content="' . htmlspecialchars(__('confirm_adopt_all_budgets')) . '">';
echo '<meta name="forecast-within-budget" content="' . htmlspecialchars(__('forecast_within_budget')) . '">';
echo '<meta name="forecast-exceeds-budget" content="' . htmlspecialchars(__('forecast_exceeds_budget')) . '">';
echo '<meta name="adopt-all-recommendations-tooltip" content="' . htmlspecialchars(__('adopt_all_recommendations_tooltip')) . '">';

// Additional JS
$additional_js = ['/assets/js/budget-modern.js'];

// Include footer
require_once 'includes/footer.php';
?>