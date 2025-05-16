<?php
$page_title = __('expense_management') . ' - ' . __('app_name');
$current_page = 'expenses';

$additional_css = ['/assets/css/expenses-modern.css'];
$additional_js = ['/assets/js/expenses-modern.js'];

// Include currency helper for currency formatting
require_once 'includes/currency_helper.php';

require_once 'includes/header.php';

$current_month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
$current_year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$filter_category = isset($_GET['category']) ? intval($_GET['category']) : 0;
?>

<div class="expense-container">
    <!-- Header Section -->
    <div class="expense-header">
        <div class="header-content">
            <div>
                <h1 class="page-title"><?php echo __('expense_management'); ?></h1>
                <p class="page-subtitle"><?php echo __('track_manage_expenses'); ?></p>
            </div>
            <button type="button" class="btn-primary" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <span><?php echo __('add_expense'); ?></span>
            </button>
        </div>
        
        <!-- Filter Pills -->
        <div class="filter-pills" id="quickFilters">
            <!-- Will be populated by JavaScript -->
        </div>
    </div>
    
    <!-- Stats Section -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
            </div>
            <div class="stat-content">
                <div class="stat-label"><?php echo __('monthly_expenses'); ?></div>
                <div class="stat-value"><?php echo formatMoney($monthly_expenses); ?></div>
                <?php 
                $previous_month = isset($prev_monthly_expenses) ? $prev_monthly_expenses : $monthly_expenses * 1.05;
                $is_decreased = $monthly_expenses <= $previous_month;
                $percent_change = abs(($monthly_expenses - $previous_month) / max(1, $previous_month) * 100);
                ?>
                <div class="stat-trend <?php echo $is_decreased ? 'positive' : 'negative'; ?>">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <?php if ($is_decreased): ?>
                        <polyline points="7 13 12 8 17 13"></polyline>
                        <?php else: ?>
                        <polyline points="7 10 12 15 17 10"></polyline>
                        <?php endif; ?>
                    </svg>
                    <span><?php echo number_format($percent_change, 1); ?>% <?php echo $is_decreased ? __('decrease') : __('increase'); ?></span>
                </div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                </svg>
            </div>
            <div class="stat-content">
                <div class="stat-label"><?php echo __('annual_expenses'); ?></div>
                <div class="stat-value"><?php echo formatMoney($yearly_expenses); ?></div>
                <div class="stat-info">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="16" x2="12" y2="12"></line>
                        <line x1="12" y1="8" x2="12.01" y2="8"></line>
                    </svg>
                    <span><?php echo __('projected_for'); ?> <?php echo date('Y'); ?></span>
                </div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 8c-2.2 0-4 1.8-4 4s1.8 4 4 4 4-1.8 4-4-1.8-4-4-4z"></path>
                    <path d="M12 2v2"></path>
                    <path d="M12 20v2"></path>
                    <path d="M20 12h2"></path>
                    <path d="M2 12h2"></path>
                </svg>
            </div>
            <div class="stat-content">
                <div class="stat-label"><?php echo __('daily_average'); ?></div>
                <div class="stat-value"><?php echo formatMoney($monthly_expenses / 30); ?></div>
                <div class="stat-info">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    <span><?php echo __('based_on_current_month'); ?></span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Main Content Area -->
    <div class="main-content">
        <!-- Chart Section -->
        <div class="chart-section">
            <div class="card">
                <div class="card-header">
                    <h2>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21.21 15.89A10 10 0 1 1 8 2.83"></path>
                            <path d="M22 12A10 10 0 0 0 12 2v10z"></path>
                        </svg>
                        <span id="chartPeriodTitle"><?php echo __('expense_categories'); ?></span>
                    </h2>
                    <div class="card-actions">
                        <select id="chartPeriodSelect" class="select-minimal">
                            <option value="current-month"><?php echo __('this_month_option'); ?></option>
                            <option value="last-month"><?php echo __('last_month_option'); ?></option>
                            <option value="last-3-months"><?php echo __('last_3_months_option'); ?></option>
                            <option value="current-year"><?php echo __('this_year_option'); ?></option>
                            <option value="all"><?php echo __('all_time_option'); ?></option>
                        </select>
                    </div>
                </div>
                <div class="card-content">
                    <div class="chart-container">
                        <canvas id="expenseChart"></canvas>
                    </div>
                    <div id="chartNoData" class="no-data-message" style="display: <?php echo (isset($top_expenses) && $top_expenses->num_rows > 0) ? 'none' : 'block'; ?>">
                        <div class="empty-state">
                            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21.21 15.89A10 10 0 1 1 8 2.83"></path>
                                <path d="M22 12A10 10 0 0 0 12 2v10z"></path>
                            </svg>
                            <h3><?php echo __('no_expense_data_available'); ?></h3>
                            <p><?php echo __('add_expenses_to_see_chart'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Top Categories -->
            <div class="card top-categories-card">
                <div class="card-header">
                    <h2>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                        </svg>
                        <?php echo __('top_categories'); ?>
                    </h2>
                </div>
                <div class="card-content">
                    <div class="top-categories">
                        <!-- Will be populated by JavaScript -->
                        <?php if (!isset($top_expenses) || $top_expenses->num_rows === 0): ?>
                        <div class="empty-state">
                            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                            </svg>
                            <h3><?php echo __('no_expenses_recorded_yet'); ?></h3>
                            <p><?php echo __('add_expenses_to_see_categories'); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Expenses Table Section -->
        <div class="expenses-table-section">
            <div class="card">
                <div class="card-header">
                    <h2>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="8" y1="6" x2="21" y2="6"></line>
                            <line x1="8" y1="12" x2="21" y2="12"></line>
                            <line x1="8" y1="18" x2="21" y2="18"></line>
                            <line x1="3" y1="6" x2="3.01" y2="6"></line>
                            <line x1="3" y1="12" x2="3.01" y2="12"></line>
                            <line x1="3" y1="18" x2="3.01" y2="18"></line>
                        </svg>
                        <?php echo __('expenses'); ?>
                    </h2>
                    <div class="card-actions">
                        <div class="search-box">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            </svg>
                            <input type="text" id="expenseSearch" placeholder="<?php echo __('search_expenses'); ?>">
                        </div>
                        <button type="button" class="btn-filter" id="filterToggle">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                            </svg>
                            <span><?php echo __('filter'); ?></span>
                        </button>
                    </div>
                </div>
                
                <!-- Filters Panel -->
                <div class="filters-panel" id="filtersPanel">
                    <form id="filterForm" action="<?php echo BASE_PATH; ?>/expenses" method="GET">
                        <div class="filter-grid">
                            <div class="filter-group">
                                <label for="monthSelect"><?php echo __('month'); ?></label>
                                <select id="monthSelect" name="month" class="select-minimal">
                                    <option value="0"><?php echo __('all_months'); ?></option>
                                    <?php 
                                    $months = [
                                        1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                                        5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                                        9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
                                    ];
                                    
                                    foreach ($months as $month_num => $month_name): 
                                        $is_selected = ($month_num == $current_month) ? 'selected' : '';
                                    ?>
                                        <option value="<?php echo $month_num; ?>" <?php echo $is_selected; ?>><?php echo $month_name; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="filter-group">
                                <label for="yearSelect"><?php echo __('year'); ?></label>
                                <select id="yearSelect" name="year" class="select-minimal">
                                    <option value="0"><?php echo __('all_years'); ?></option>
                                    <?php 
                                    $current_year_actual = date('Y');
                                    for ($year = $current_year_actual; $year >= $current_year_actual - 5; $year--): 
                                        $is_selected = ($year == $current_year) ? 'selected' : '';
                                    ?>
                                        <option value="<?php echo $year; ?>" <?php echo $is_selected; ?>><?php echo $year; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            
                            <div class="filter-group">
                                <label for="categorySelect"><?php echo __('category'); ?></label>
                                <select id="categorySelect" name="category" class="select-minimal">
                                    <option value="0"><?php echo __('all_categories'); ?></option>
                                    <?php 
                                    $categories->data_seek(0);
                                    while($category = $categories->fetch_assoc()): 
                                        $is_selected = ($category['category_id'] == $filter_category) ? 'selected' : '';
                                        // Get translated category name
                                        $translated_name = $expense->getTranslatedCategoryName($category['name']);
                                    ?>
                                        <option value="<?php echo $category['category_id']; ?>" <?php echo $is_selected; ?>>
                                            <?php echo htmlspecialchars($translated_name); ?>
                                        </option>
                                    <?php endwhile; ?>
                                    <?php $categories->data_seek(0); ?>
                                </select>
                            </div>
                            
                            <div class="filter-group toggle-group">
                                <label class="toggle-label">
                                    <input type="checkbox" id="recurringFilter" name="recurring">
                                    <span class="toggle-control"></span>
                                    <span><?php echo __('recurring_only'); ?></span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="filter-actions">
                            <button type="submit" class="btn-apply" id="applyFilter">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="9 11 12 14 22 4"></polyline>
                                    <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                                </svg>
                                <?php echo __('apply_filter'); ?>
                            </button>
                            <button type="button" class="btn-reset" id="resetFilter">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 12a9 9 0 0 1-9 9"></path>
                                    <path d="M3 12a9 9 0 0 1 9-9"></path>
                                    <path d="M12 21C7.029 21 3 16.971 3 12S7.029 3 12 3"></path>
                                    <path d="M12 8l4 4-4 4"></path>
                                </svg>
                                <?php echo __('reset'); ?>
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Bulk Actions -->
                <div class="bulk-actions" id="bulkActions">
                    <span class="selected-count"><span class="count">0</span> <?php echo __('items_selected'); ?></span>
                    <div class="bulk-buttons">
                        <button type="button" class="btn-category" id="bulkCategoryBtn">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                                <line x1="7" y1="7" x2="7.01" y2="7"></line>
                            </svg>
                            <?php echo __('change_category'); ?>
                        </button>
                        <button type="button" class="btn-delete" id="bulkDeleteBtn">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="3 6 5 6 21 6"></polyline>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                            </svg>
                            <?php echo __('delete'); ?>
                        </button>
                    </div>
                </div>
                
                <div class="card-content">
                    <div class="table-container">
                        <table class="expenses-table" id="expensesTable">
                            <thead>
                                <tr>
                                    <th class="checkbox-cell">
                                        <label class="checkbox-container">
                                            <input type="checkbox" id="selectAll">
                                            <span class="checkmark"></span>
                                        </label>
                                    </th>
                                    <th><?php echo __('description'); ?></th>
                                    <th><?php echo __('category'); ?></th>
                                    <th><?php echo __('amount'); ?></th>
                                    <th><?php echo __('date'); ?></th>
                                    <th><?php echo __('type'); ?></th>
                                    <th><?php echo __('actions'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (isset($expenses) && $expenses->num_rows > 0): ?>
                                    <?php while ($expense_item = $expenses->fetch_assoc()): 
                                        // Get translated category name
                                        $translated_category = $expense->getTranslatedCategoryName($expense_item['category_name']);
                                        $is_recurring = $expense_item['is_recurring'];
                                    ?>
                                        <tr data-id="<?php echo $expense_item['expense_id']; ?>">
                                            <td class="checkbox-cell">
                                                <label class="checkbox-container">
                                                    <input type="checkbox" class="expense-checkbox" value="<?php echo $expense_item['expense_id']; ?>">
                                                    <span class="checkmark"></span>
                                                </label>
                                            </td>
                                            <td class="description-cell">
                                                <?php if ($is_recurring): ?>
                                                <span class="recurring-indicator" title="<?php echo __('recurring'); ?>">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <path d="M21 12a9 9 0 0 1-9 9"></path>
                                                        <path d="M3 12a9 9 0 0 1 9-9"></path>
                                                        <path d="M12 21C7.029 21 3 16.971 3 12S7.029 3 12 3"></path>
                                                    </svg>
                                                </span>
                                                <?php endif; ?>
                                                <?php echo htmlspecialchars($expense_item['description']); ?>
                                            </td>
                                            <td>
                                                <span class="category-tag"><?php echo htmlspecialchars($translated_category); ?></span>
                                            </td>
                                            <td class="amount-cell"><?php echo formatMoney($expense_item['amount']); ?></td>
                                            <td><?php echo date('M j, Y', strtotime($expense_item['expense_date'])); ?></td>
                                            <td>
                                                <?php if ($expense_item['is_recurring']): ?>
                                                    <span class="type-tag recurring">
                                                        <?php echo ucfirst(str_replace('-', ' ', $expense_item['frequency'])); ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="type-tag one-time">
                                                        <?php echo __('one_time'); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="actions-cell">
                                                <button type="button" class="btn-action duplicate" data-expense-id="<?php echo $expense_item['expense_id']; ?>" title="<?php echo __('duplicate'); ?>">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                                                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                                                    </svg>
                                                </button>
                                                <button type="button" class="btn-action edit" data-expense-id="<?php echo $expense_item['expense_id']; ?>" title="<?php echo __('edit'); ?>">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                                    </svg>
                                                </button>
                                                <button type="button" class="btn-action delete" data-expense-id="<?php echo $expense_item['expense_id']; ?>" title="<?php echo __('delete'); ?>">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <polyline points="3 6 5 6 21 6"></polyline>
                                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                    </svg>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Empty States -->
                    <div id="tableNoData" class="empty-state table-empty" style="display: <?php echo (isset($expenses) && $expenses->num_rows > 0) ? 'none' : 'block'; ?>">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="2" width="20" height="8" rx="2" ry="2"></rect>
                            <rect x="2" y="14" width="20" height="8" rx="2" ry="2"></rect>
                            <line x1="6" y1="6" x2="6.01" y2="6"></line>
                            <line x1="6" y1="18" x2="6.01" y2="18"></line>
                        </svg>
                        <h3><?php echo __('no_expenses_recorded_yet'); ?></h3>
                        <p><?php echo __('start_tracking_expenses'); ?></p>
                        <button type="button" class="btn-primary" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                            <?php echo __('add_your_first_expense'); ?>
                        </button>
                    </div>
                    
                    <div id="searchNoResults" class="empty-state search-empty" style="display: none;">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                        <h3><?php echo __('no_matching_expenses'); ?></h3>
                        <p><?php echo __('try_adjusting_search'); ?></p>
                        <button type="button" class="btn-secondary" id="clearSearch">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 12a9 9 0 0 1-9 9"></path>
                                <path d="M3 12a9 9 0 0 1 9-9"></path>
                                <path d="M12 21C7.029 21 3 16.971 3 12S7.029 3 12 3"></path>
                                <path d="M12 8l4 4-4 4"></path>
                            </svg>
                            <?php echo __('clear_filters'); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Expense Modal -->
<div class="modal fade" id="addExpenseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    <?php echo __('add_new_expense'); ?>
                </h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addExpenseForm" method="POST" action="<?php echo BASE_PATH; ?>/expenses" class="direct-form needs-validation" novalidate>
                <input type="hidden" name="action" value="add">
                
                <div class="modal-body">
                    <div class="form-group">
                        <label for="description"><?php echo __('description'); ?></label>
                        <input type="text" class="form-control" id="description" name="description" required>
                        <div class="invalid-feedback">
                            <?php echo __('please_enter_description'); ?>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="category_id"><?php echo __('category'); ?></label>
                            <select class="form-control" id="category_id" name="category_id" required>
                                <option value=""><?php echo __('select_category'); ?></option>
                                <?php 
                                $categories->data_seek(0);
                                while($category = $categories->fetch_assoc()): 
                                    // Get translated category name
                                    $translated_name = $expense->getTranslatedCategoryName($category['name']);
                                ?>
                                    <option value="<?php echo $category['category_id']; ?>">
                                        <?php echo htmlspecialchars($translated_name); ?>
                                    </option>
                                <?php endwhile; ?>
                                <?php $categories->data_seek(0); ?>
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
                            </div>
                            <div class="invalid-feedback">
                                <?php echo __('please_enter_valid_amount'); ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="expense_date"><?php echo __('date'); ?></label>
                            <input type="date" class="form-control" id="expense_date" name="expense_date" value="<?php echo date('Y-m-d'); ?>" required>
                            <div class="invalid-feedback">
                                <?php echo __('please_select_date'); ?>
                            </div>
                        </div>
                        
                        <div class="form-group" id="frequency_group">
                            <label for="frequency"><?php echo __('frequency'); ?></label>
                            <select class="form-control" id="frequency" name="frequency" disabled>
                                <option value="one-time"><?php echo __('one-time'); ?></option>
                                <option value="daily"><?php echo __('daily'); ?></option>
                                <option value="weekly"><?php echo __('weekly'); ?></option>
                                <option value="bi-weekly"><?php echo __('bi-weekly'); ?></option>
                                <option value="monthly" selected><?php echo __('monthly'); ?></option>
                                <option value="quarterly"><?php echo __('quarterly'); ?></option>
                                <option value="annually"><?php echo __('annually'); ?></option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group toggle-group">
                        <label class="toggle-label">
                            <input type="checkbox" id="is_recurring" name="is_recurring">
                            <span class="toggle-control"></span>
                            <span><?php echo __('recurring_expense'); ?></span>
                        </label>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" data-bs-dismiss="modal">
                        <?php echo __('cancel'); ?>
                    </button>
                    <button type="submit" class="btn-primary">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                            <polyline points="17 21 17 13 7 13 7 21"></polyline>
                            <polyline points="7 3 7 8 15 8"></polyline>
                        </svg>
                        <?php echo __('save'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Expense Modal -->
<div class="modal fade" id="editExpenseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                    </svg>
                    <?php echo __('edit_expense'); ?>
                </h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editExpenseForm" method="POST" action="<?php echo BASE_PATH; ?>/expenses" class="direct-form needs-validation" novalidate>
                <input type="hidden" name="action" value="edit">
                <input type="hidden" id="edit_expense_id" name="expense_id">
                
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_description"><?php echo __('description'); ?></label>
                        <input type="text" class="form-control" id="edit_description" name="description" required>
                        <div class="invalid-feedback">
                            <?php echo __('please_enter_description'); ?>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_category_id"><?php echo __('category'); ?></label>
                            <select class="form-control" id="edit_category_id" name="category_id" required>
                                <option value=""><?php echo __('select_category'); ?></option>
                                <?php 
                                $categories->data_seek(0);
                                while($category = $categories->fetch_assoc()): 
                                    // Get translated category name
                                    $translated_name = $expense->getTranslatedCategoryName($category['name']);
                                ?>
                                    <option value="<?php echo $category['category_id']; ?>">
                                        <?php echo htmlspecialchars($translated_name); ?>
                                    </option>
                                <?php endwhile; ?>
                                <?php $categories->data_seek(0); ?>
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
                            </div>
                            <div class="invalid-feedback">
                                <?php echo __('please_enter_valid_amount'); ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_expense_date"><?php echo __('date'); ?></label>
                            <input type="date" class="form-control" id="edit_expense_date" name="expense_date" required>
                            <div class="invalid-feedback">
                                <?php echo __('please_select_date'); ?>
                            </div>
                        </div>
                        
                        <div class="form-group" id="edit_frequency_group">
                            <label for="edit_frequency"><?php echo __('frequency'); ?></label>
                            <select class="form-control" id="edit_frequency" name="frequency">
                                <option value="one-time"><?php echo __('one-time'); ?></option>
                                <option value="daily"><?php echo __('daily'); ?></option>
                                <option value="weekly"><?php echo __('weekly'); ?></option>
                                <option value="bi-weekly"><?php echo __('bi-weekly'); ?></option>
                                <option value="monthly"><?php echo __('monthly'); ?></option>
                                <option value="quarterly"><?php echo __('quarterly'); ?></option>
                                <option value="annually"><?php echo __('annually'); ?></option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group toggle-group">
                        <label class="toggle-label">
                            <input type="checkbox" id="edit_is_recurring" name="is_recurring">
                            <span class="toggle-control"></span>
                            <span><?php echo __('recurring_expense'); ?></span>
                        </label>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" data-bs-dismiss="modal">
                        <?php echo __('cancel'); ?>
                    </button>
                    <button type="submit" class="btn-primary">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                            <polyline points="17 21 17 13 7 13 7 21"></polyline>
                            <polyline points="7 3 7 8 15 8"></polyline>
                        </svg>
                        <?php echo __('update'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Expense Modal -->
<div class="modal fade" id="deleteExpenseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                    </svg>
                    <?php echo __('delete_expense'); ?>
                </h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="deleteExpenseForm" method="POST" action="<?php echo BASE_PATH; ?>/expenses" class="direct-form">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" id="delete_expense_id" name="expense_id">
                
                <div class="modal-body">
                    <p class="confirm-message"><?php echo __('are_you_sure_delete_expense'); ?></p>
                    <p class="text-muted"><?php echo __('action_cannot_be_undone'); ?></p>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" data-bs-dismiss="modal">
                        <?php echo __('cancel'); ?>
                    </button>
                    <button type="submit" class="btn-danger">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        </svg>
                        <?php echo __('delete'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Notification Container -->
<div class="notification-container" id="notificationContainer"></div>

<?php
// Prepare chart data for JavaScript
$chart_labels = [];
$chart_data = [];
$chart_colors = [
    '#6366f1', '#8b5cf6', '#ec4899', '#ef4444', '#f59e0b',
    '#10b981', '#14b8a6', '#06b6d4', '#3b82f6', '#4f46e5',
    '#a855f7', '#d946ef', '#f43f5e', '#fb7185', '#fbbf24'
];

if (isset($top_expenses) && $top_expenses->num_rows > 0) {
    $top_expenses->data_seek(0);
    while ($category = $top_expenses->fetch_assoc()) {
        // Get translated category name for the chart
        $translated_name = $expense->getTranslatedCategoryName($category['category_name']);
        $chart_labels[] = $translated_name;
        $chart_data[] = floatval($category['total']);
    }
}

// Add meta tags for JavaScript
echo '<meta name="base-path" content="' . BASE_PATH . '">';
echo '<meta name="chart-labels" content="' . htmlspecialchars(json_encode($chart_labels)) . '">';
echo '<meta name="chart-data" content="' . htmlspecialchars(json_encode($chart_data)) . '">';
echo '<meta name="chart-colors" content="' . htmlspecialchars(json_encode(array_slice($chart_colors, 0, count($chart_data)))) . '">';
// Add currency symbol meta tag for JavaScript
echo '<meta name="currency-symbol" content="' . htmlspecialchars(getCurrencySymbol()) . '">';

require_once 'includes/footer.php';
?>