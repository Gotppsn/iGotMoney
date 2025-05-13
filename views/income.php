<?php
// Set page title and current page for menu highlighting
$page_title = __('income_management') . ' - ' . __('app_name');
$current_page = 'income';

// Additional CSS and JS
$additional_css = ['/assets/css/income-modern.css'];
$additional_js = ['/assets/js/income-modern.js', '/assets/js/chart.income.js'];

// Include currency helper for currency formatting
require_once 'includes/currency_helper.php';

// Include header
require_once 'includes/header.php';
?>

<!-- Main Content Wrapper -->
<div class="income-page">
    <!-- Page Header Section -->
    <div class="page-header-section">
        <div class="page-header-content">
            <div class="page-title-group">
                <h1 class="page-title"><?php echo __('income_management'); ?></h1>
                <p class="page-subtitle"><?php echo __('track_manage_income'); ?></p>
            </div>
            <div class="header-actions">
                <button type="button" id="refreshData" class="btn-refresh me-2" title="<?php echo __('refresh'); ?>">
                    <i class="fas fa-sync-alt"></i>
                </button>
                <button type="button" class="btn-add-income" data-bs-toggle="modal" data-bs-target="#addIncomeModal">
                    <i class="fas fa-plus-circle"></i>
                    <span><?php echo __('add_income'); ?></span>
                </button>
            </div>
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
                    <h3 class="stat-label"><?php echo __('monthly_income'); ?></h3>
                    <p class="stat-value"><?php echo formatMoney($monthly_income); ?></p>
                    <?php
                    $previous_month = isset($prev_monthly_income) ? $prev_monthly_income : $monthly_income * 0.95;
                    $is_increased = $monthly_income >= $previous_month;
                    $percent_change = abs(($monthly_income - $previous_month) / max(1, $previous_month) * 100);
                    ?>
                    <div class="stat-trend <?php echo $is_increased ? 'positive' : 'negative'; ?>">
                        <i class="fas fa-<?php echo $is_increased ? 'arrow-up' : 'arrow-down'; ?>"></i>
                        <span><?php echo number_format($percent_change, 1); ?>% <?php echo __('from_last_month'); ?></span>
                    </div>
                </div>
            </div>
            
            <div class="stat-card annual">
                <div class="stat-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-label"><?php echo __('annual_income'); ?></h3>
                    <p class="stat-value"><?php echo formatMoney($yearly_income); ?></p>
                    <div class="stat-info">
                        <i class="fas fa-info-circle"></i>
                        <span><?php echo __('projected_for'); ?> <?php echo date('Y'); ?></span>
                    </div>
                </div>
            </div>
            
            <div class="stat-card sources">
                <div class="stat-icon">
                    <i class="fas fa-wallet"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-label"><?php echo __('active_sources'); ?></h3>
                    <?php
                    $active_count = 0;
                    $total_sources = 0;
                    if (isset($income_sources) && $income_sources->num_rows > 0) {
                        $total_sources = $income_sources->num_rows;
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
                        <i class="fas fa-<?php echo $active_count === $total_sources ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                        <span><?php echo $active_count; ?> / <?php echo $total_sources; ?> <?php echo __('currently_active'); ?></span>
                    </div>
                </div>
            </div>
            
            <div class="stat-card future">
                <div class="stat-icon">
                    <i class="fas fa-calendar-week"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-label"><?php echo __('next_income'); ?></h3>
                    <?php
                    $next_income_date = null;
                    $next_income_amount = 0;
                    $next_income_name = '';
                    $today = new DateTime();
                    
                    if (isset($income_sources) && $income_sources->num_rows > 0) {
                        $income_sources->data_seek(0);
                        $next_dates = [];
                        
                        while ($source = $income_sources->fetch_assoc()) {
                            if ($source['is_active'] != 1) continue;
                            
                            $start_date = new DateTime($source['start_date']);
                            if ($start_date > $today) {
                                // Future income
                                $next_dates[] = [
                                    'date' => $start_date,
                                    'amount' => $source['amount'],
                                    'name' => $source['name']
                                ];
                                continue;
                            }
                            
                            // Calculate next occurrence based on frequency
                            $next_date = clone $today;
                            switch($source['frequency']) {
                                case 'daily':
                                    $next_date->modify('+1 day');
                                    break;
                                case 'weekly':
                                    $next_date->modify('+1 week');
                                    break;
                                case 'bi-weekly':
                                    $next_date->modify('+2 weeks');
                                    break;
                                case 'monthly':
                                    $next_date->modify('+1 month');
                                    break;
                                case 'quarterly':
                                    $next_date->modify('+3 months');
                                    break;
                                case 'annually':
                                    $next_date->modify('+1 year');
                                    break;
                                default:
                                    continue 2; // Skip one-time income that's in the past
                            }
                            
                            $next_dates[] = [
                                'date' => $next_date,
                                'amount' => $source['amount'],
                                'name' => $source['name']
                            ];
                        }
                        
                        // Sort by date and get the closest
                        if (!empty($next_dates)) {
                            usort($next_dates, function($a, $b) {
                                return $a['date'] <=> $b['date'];
                            });
                            
                            $next_income_date = $next_dates[0]['date'];
                            $next_income_amount = $next_dates[0]['amount'];
                            $next_income_name = $next_dates[0]['name'];
                        }
                        
                        $income_sources->data_seek(0);
                    }
                    ?>
                    
                    <?php if ($next_income_date): ?>
                        <p class="stat-value"><?php echo formatMoney($next_income_amount); ?></p>
                        <div class="stat-info" title="<?php echo htmlspecialchars($next_income_name); ?>">
                            <i class="fas fa-calendar-day"></i>
                            <span>
                                <?php 
                                $days_until = $today->diff($next_income_date)->days;
                                if ($days_until == 0) {
                                    echo __('today');
                                } elseif ($days_until == 1) {
                                    echo __('tomorrow');
                                } else {
                                    echo sprintf(__('in_days'), $days_until);
                                }
                                ?>
                            </span>
                        </div>
                    <?php else: ?>
                        <p class="stat-value">-</p>
                        <div class="stat-info">
                            <i class="fas fa-calendar-times"></i>
                            <span><?php echo __('no_upcoming_income'); ?></span>
                        </div>
                    <?php endif; ?>
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
                        <h3><?php echo __('income_by_frequency'); ?></h3>
                    </div>
                    <div class="chart-actions">
                        <select id="chartViewToggle" class="form-select form-select-sm">
                            <option value="pie"><?php echo __('pie_chart'); ?></option>
                            <option value="bar"><?php echo __('bar_chart'); ?></option>
                        </select>
                    </div>
                </div>
                <div class="chart-body">
                    <div class="chart-container">
                        <canvas id="frequencyChart"></canvas>
                    </div>
                    <div id="chartNoData" class="no-data-message" style="display: <?php echo ($active_count > 0) ? 'none' : 'block'; ?>">
                        <i class="fas fa-chart-bar"></i>
                        <p><?php echo __('no_active_income_sources'); ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Top Income Sources List -->
            <div class="chart-card sources-list">
                <div class="chart-header">
                    <div class="chart-title">
                        <i class="fas fa-list-ol"></i>
                        <h3><?php echo __('top_income_sources'); ?></h3>
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
                                <div class="source-item" data-income-id="<?php echo $source['income_id']; ?>">
                                    <div class="source-rank"><?php echo $rank++; ?></div>
                                    <div class="source-info">
                                        <h4 class="source-name"><?php echo htmlspecialchars($source['name']); ?></h4>
                                        <span class="source-frequency"><?php echo __($source['frequency']); ?></span>
                                        <div class="source-bar">
                                            <div class="source-bar-fill" 
                                                 style="width: <?php echo $percentage; ?>%"
                                                 data-percentage="<?php echo $percentage; ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="source-amount">
                                        <span class="amount"><?php echo formatMoney($source['monthly_amount']); ?>/mo</span>
                                        <span class="percentage"><?php echo number_format($percentage, 1); ?>%</span>
                                    </div>
                                    <div class="source-actions">
                                        <button class="btn-quick-edit" title="<?php echo __('quick_edit'); ?>">
                                            <i class="fas fa-pen"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <?php if(count($sources_array) > 5): ?>
                                <div class="view-all-sources">
                                    <a href="#incomeTable" class="btn-view-all">
                                        <i class="fas fa-eye"></i> <?php echo __('view_all_sources'); ?> (<?php echo count($sources_array); ?>)
                                    </a>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="empty-sources">
                                <i class="fas fa-folder-open"></i>
                                <p><?php echo __('no_income_sources_recorded'); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Income Trend Chart -->
            <div class="chart-card trend-chart">
                <div class="chart-header">
                    <div class="chart-title">
                        <i class="fas fa-chart-line"></i>
                        <h3><?php echo __('monthly_income_projection'); ?></h3>
                    </div>
                    <div class="chart-actions">
                        <select id="projectionMonths" class="form-select form-select-sm">
                            <option value="3">3 <?php echo __('months'); ?></option>
                            <option value="6" selected>6 <?php echo __('months'); ?></option>
                            <option value="12">12 <?php echo __('months'); ?></option>
                        </select>
                    </div>
                </div>
                <div class="chart-body">
                    <div class="chart-container">
                        <canvas id="trendChart"></canvas>
                    </div>
                    <div id="trendChartNoData" class="no-data-message" style="display: <?php echo ($active_count > 0) ? 'none' : 'block'; ?>">
                        <i class="fas fa-chart-line"></i>
                        <p><?php echo __('no_active_income_sources'); ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Income Calendar Preview -->
            <div class="chart-card calendar-preview">
                <div class="chart-header">
                    <div class="chart-title">
                        <i class="fas fa-calendar-alt"></i>
                        <h3><?php echo __('income_calendar'); ?></h3>
                    </div>
                </div>
                <div class="chart-body">
                    <div class="calendar-container">
                        <div class="current-month">
                            <h4><?php echo date('F Y'); ?></h4>
                        </div>
                        <div class="calendar-grid">
                            <?php
                            // Calendar generation
                            $currentMonth = date('n');
                            $currentYear = date('Y');
                            $daysInMonth = date('t');
                            $firstDayOfMonth = date('N', strtotime($currentYear . '-' . $currentMonth . '-01'));
                            
                            // Build day labels
                            $dayNames = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
                            echo '<div class="calendar-days">';
                            foreach ($dayNames as $day) {
                                echo '<div class="day-name">' . $day . '</div>';
                            }
                            echo '</div>';
                            
                            // Generate calendar grid with income data
                            $incomeData = [];
                            
                            if (isset($income_sources) && $income_sources->num_rows > 0) {
                                $income_sources->data_seek(0);
                                
                                while ($source = $income_sources->fetch_assoc()) {
                                    if ($source['is_active'] != 1) continue;
                                    
                                    // Get income date based on frequency
                                    $startDate = new DateTime($source['start_date']);
                                    
                                    if ($source['frequency'] === 'one-time') {
                                        // One-time income that falls within this month
                                        if ($startDate->format('Y-m') === date('Y-m')) {
                                            $day = $startDate->format('j');
                                            if (!isset($incomeData[$day])) {
                                                $incomeData[$day] = 0;
                                            }
                                            $incomeData[$day] += $source['amount'];
                                        }
                                    } else {
                                        // Calculate recurring incomes for this month
                                        $currentDate = new DateTime($currentYear . '-' . $currentMonth . '-01');
                                        $monthEnd = new DateTime($currentYear . '-' . $currentMonth . '-' . $daysInMonth);
                                        
                                        // Skip if start date is in the future
                                        if ($startDate > $monthEnd) continue;
                                        
                                        // Check if we have income days in this month
                                        switch ($source['frequency']) {
                                            case 'daily':
                                                // Daily income - every day
                                                for ($i = 1; $i <= $daysInMonth; $i++) {
                                                    if (!isset($incomeData[$i])) {
                                                        $incomeData[$i] = 0;
                                                    }
                                                    $incomeData[$i] += $source['amount'];
                                                }
                                                break;
                                                
                                            case 'weekly':
                                                // Weekly income - same day of week
                                                $startDayOfWeek = $startDate->format('N');
                                                for ($i = 1; $i <= $daysInMonth; $i++) {
                                                    $checkDate = new DateTime($currentYear . '-' . $currentMonth . '-' . $i);
                                                    if ($checkDate->format('N') == $startDayOfWeek && $checkDate >= $startDate) {
                                                        if (!isset($incomeData[$i])) {
                                                            $incomeData[$i] = 0;
                                                        }
                                                        $incomeData[$i] += $source['amount'];
                                                    }
                                                }
                                                break;
                                                
                                            case 'bi-weekly':
                                                // Bi-weekly income - every 2 weeks
                                                for ($i = 1; $i <= $daysInMonth; $i++) {
                                                    $checkDate = new DateTime($currentYear . '-' . $currentMonth . '-' . $i);
                                                    if ($checkDate >= $startDate) {
                                                        $interval = $startDate->diff($checkDate);
                                                        $totalDays = $interval->days;
                                                        if ($totalDays % 14 === 0) {
                                                            if (!isset($incomeData[$i])) {
                                                                $incomeData[$i] = 0;
                                                            }
                                                            $incomeData[$i] += $source['amount'];
                                                        }
                                                    }
                                                }
                                                break;
                                                
                                            case 'monthly':
                                                // Monthly income - same day of month
                                                $dayOfMonth = $startDate->format('j');
                                                if ($dayOfMonth <= $daysInMonth) {
                                                    $incomeDate = new DateTime($currentYear . '-' . $currentMonth . '-' . $dayOfMonth);
                                                    if ($incomeDate >= $startDate) {
                                                        if (!isset($incomeData[$dayOfMonth])) {
                                                            $incomeData[$dayOfMonth] = 0;
                                                        }
                                                        $incomeData[$dayOfMonth] += $source['amount'];
                                                    }
                                                }
                                                break;
                                                
                                            case 'quarterly':
                                                // Quarterly income - same day every 3 months
                                                $startMonth = $startDate->format('n');
                                                $monthDiff = (($currentYear - $startDate->format('Y')) * 12) + $currentMonth - $startMonth;
                                                if ($monthDiff % 3 === 0) {
                                                    $dayOfMonth = $startDate->format('j');
                                                    if ($dayOfMonth <= $daysInMonth) {
                                                        $incomeDate = new DateTime($currentYear . '-' . $currentMonth . '-' . $dayOfMonth);
                                                        if ($incomeDate >= $startDate) {
                                                            if (!isset($incomeData[$dayOfMonth])) {
                                                                $incomeData[$dayOfMonth] = 0;
                                                            }
                                                            $incomeData[$dayOfMonth] += $source['amount'];
                                                        }
                                                    }
                                                }
                                                break;
                                                
                                            case 'annually':
                                                // Annual income - same day and month
                                                if ($currentMonth == $startDate->format('n')) {
                                                    $dayOfMonth = $startDate->format('j');
                                                    if ($dayOfMonth <= $daysInMonth) {
                                                        $incomeDate = new DateTime($currentYear . '-' . $currentMonth . '-' . $dayOfMonth);
                                                        if ($incomeDate >= $startDate) {
                                                            if (!isset($incomeData[$dayOfMonth])) {
                                                                $incomeData[$dayOfMonth] = 0;
                                                            }
                                                            $incomeData[$dayOfMonth] += $source['amount'];
                                                        }
                                                    }
                                                }
                                                break;
                                        }
                                    }
                                }
                                
                                $income_sources->data_seek(0);
                            }
                            
                            // Output calendar cells
                            echo '<div class="calendar-dates">';
                            
                            // Add empty cells for days before the first day of month
                            for ($i = 1; $i < $firstDayOfMonth; $i++) {
                                echo '<div class="empty-day"></div>';
                            }
                            
                            // Add cells for each day of the month
                            for ($day = 1; $day <= $daysInMonth; $day++) {
                                $isToday = ($day == date('j'));
                                $hasIncome = isset($incomeData[$day]);
                                
                                $classes = 'calendar-day';
                                if ($isToday) $classes .= ' today';
                                if ($hasIncome) $classes .= ' has-income';
                                
                                echo '<div class="' . $classes . '">';
                                echo '<span class="date">' . $day . '</span>';
                                
                                if ($hasIncome) {
                                    echo '<div class="income-marker" title="' . formatMoney($incomeData[$day]) . '">';
                                    echo '<i class="fas fa-coins"></i>';
                                    echo '</div>';
                                }
                                
                                echo '</div>';
                            }
                            
                            echo '</div>';
                            ?>
                        </div>
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
                    <h3><?php echo __('income_sources'); ?></h3>
                </div>
                <div class="table-controls">
                    <div class="control-group">
                        <select id="statusFilter" class="form-select form-select-sm me-2">
                            <option value="all"><?php echo __('all_statuses'); ?></option>
                            <option value="active"><?php echo __('active_only'); ?></option>
                            <option value="inactive"><?php echo __('inactive_only'); ?></option>
                        </select>
                        <select id="frequencyFilter" class="form-select form-select-sm me-2">
                            <option value="all"><?php echo __('all_frequencies'); ?></option>
                            <option value="monthly"><?php echo __('monthly'); ?></option>
                            <option value="annually"><?php echo __('annually'); ?></option>
                            <option value="one-time"><?php echo __('one-time'); ?></option>
                            <option value="weekly"><?php echo __('weekly'); ?></option>
                            <option value="bi-weekly"><?php echo __('bi-weekly'); ?></option>
                            <option value="quarterly"><?php echo __('quarterly'); ?></option>
                            <option value="daily"><?php echo __('daily'); ?></option>
                        </select>
                    </div>
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="incomeSearch" placeholder="<?php echo __('search_income_sources'); ?>" data-table-search="incomeTable">
                    </div>
                </div>
            </div>
            <div class="table-body">
                <div class="table-responsive">
                    <table class="income-table" id="incomeTable">
                        <thead>
                            <tr>
                                <th class="sortable" data-sort="name"><?php echo __('name'); ?> <i class="fas fa-sort"></i></th>
                                <th class="sortable" data-sort="amount"><?php echo __('amount'); ?> <i class="fas fa-sort"></i></th>
                                <th class="sortable" data-sort="frequency"><?php echo __('frequency'); ?> <i class="fas fa-sort"></i></th>
                                <th class="sortable" data-sort="start_date"><?php echo __('start_date'); ?> <i class="fas fa-sort"></i></th>
                                <th class="sortable" data-sort="end_date"><?php echo __('end_date'); ?> <i class="fas fa-sort"></i></th>
                                <th class="sortable" data-sort="status"><?php echo __('status'); ?> <i class="fas fa-sort"></i></th>
                                <th><?php echo __('actions'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($income_sources) && $income_sources->num_rows > 0): ?>
                                <?php 
                                $income_sources->data_seek(0);
                                while ($income_source = $income_sources->fetch_assoc()): 
                                ?>
                                    <tr data-id="<?php echo $income_source['income_id']; ?>" 
                                        data-status="<?php echo $income_source['is_active'] ? 'active' : 'inactive'; ?>"
                                        data-frequency="<?php echo $income_source['frequency']; ?>">
                                        <td class="source-name-cell"><?php echo htmlspecialchars($income_source['name']); ?></td>
                                        <td class="amount-cell"><?php echo formatMoney($income_source['amount']); ?></td>
                                        <td>
                                            <span class="frequency-badge">
                                                <?php echo __($income_source['frequency']); ?>
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
                                                    echo '<span class="text-muted">' . __('ongoing') . '</span>';
                                                }
                                            } else {
                                                echo '<span class="text-muted">' . __('ongoing') . '</span>';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <span class="status-badge <?php echo $income_source['is_active'] ? 'active' : 'inactive'; ?>">
                                                <?php echo $income_source['is_active'] ? __('active') : __('inactive'); ?>
                                            </span>
                                            <div class="status-toggle">
                                                <label class="switch-toggle">
                                                    <input type="checkbox" class="toggle-status" 
                                                           <?php echo $income_source['is_active'] ? 'checked' : ''; ?>>
                                                    <span class="slider"></span>
                                                </label>
                                            </div>
                                        </td>
                                        <td class="actions-cell">
                                            <button class="btn-action edit" data-income-id="<?php echo $income_source['income_id']; ?>" title="<?php echo __('edit'); ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn-action duplicate" data-income-id="<?php echo $income_source['income_id']; ?>" title="<?php echo __('duplicate'); ?>">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                            <button class="btn-action delete" data-income-id="<?php echo $income_source['income_id']; ?>" title="<?php echo __('delete'); ?>">
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
                    <h4><?php echo __('no_income_sources_recorded_yet'); ?></h4>
                    <p><?php echo __('start_tracking_income'); ?></p>
                    <button type="button" class="btn-add-first" data-bs-toggle="modal" data-bs-target="#addIncomeModal">
                        <i class="fas fa-plus"></i>
                        <?php echo __('add_your_first_income'); ?>
                    </button>
                </div>
                <div id="tableNoResults" class="table-empty" style="display: none;">
                    <div class="empty-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h4><?php echo __('no_matching_income_sources'); ?></h4>
                    <p><?php echo __('try_adjusting_search'); ?></p>
                    <button type="button" class="btn-reset-filters">
                        <i class="fas fa-undo"></i>
                        <?php echo __('reset_filters'); ?>
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
                <h5 class="modal-title"><?php echo __('add_new_income_source'); ?></h5>
                <button type="button" class="modal-close" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="<?php echo BASE_PATH; ?>/income" method="post" class="needs-validation" novalidate>
                <input type="hidden" name="action" value="add">
                
                <div class="modal-body">
                    <div class="form-grid">
                        <div class="form-field full-width">
                            <label for="name"><?php echo __('income_name'); ?></label>
                            <input type="text" id="name" name="name" required>
                            <div class="invalid-feedback">
                                <?php echo __('please_enter_name'); ?>
                            </div>
                        </div>
                        
                        <div class="form-field">
                            <label for="amount"><?php echo __('amount'); ?></label>
                            <div class="amount-input">
                                <span class="currency-symbol"><?php echo getCurrencySymbol(); ?></span>
                                <input type="number" id="amount" name="amount" step="0.01" min="0.01" required>
                                <div class="invalid-feedback">
                                    <?php echo __('please_enter_valid_amount'); ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-field">
                            <label for="frequency"><?php echo __('frequency'); ?></label>
                            <select id="frequency" name="frequency" class="modern-select">
                                <option value="one-time"><?php echo __('one-time'); ?></option>
                                <option value="daily"><?php echo __('daily'); ?></option>
                                <option value="weekly"><?php echo __('weekly'); ?></option>
                                <option value="bi-weekly"><?php echo __('bi-weekly'); ?></option>
                                <option value="monthly" selected><?php echo __('monthly'); ?></option>
                                <option value="quarterly"><?php echo __('quarterly'); ?></option>
                                <option value="annually"><?php echo __('annually'); ?></option>
                            </select>
                        </div>
                        
                        <div class="form-field">
                            <label for="start_date"><?php echo __('start_date'); ?></label>
                            <input type="date" id="start_date" name="start_date" value="<?php echo date('Y-m-d'); ?>" required>
                            <div class="invalid-feedback">
                                <?php echo __('please_select_start_date'); ?>
                            </div>
                        </div>
                        
                        <div class="form-field">
                            <label for="end_date"><?php echo __('end_date_optional'); ?></label>
                            <input type="date" id="end_date" name="end_date">
                            <small class="form-text text-muted"><?php echo __('leave_empty_for_ongoing'); ?></small>
                        </div>
                        
                        <div class="form-field full-width">
                            <label class="toggle-field">
                                <input type="checkbox" id="is_active" name="is_active" checked>
                                <span class="toggle-slider"></span>
                                <span class="toggle-label"><?php echo __('active_income_source'); ?></span>
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal"><?php echo __('cancel'); ?></button>
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-plus"></i>
                        <?php echo __('add_income'); ?>
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
                <h5 class="modal-title"><?php echo __('edit_income_source'); ?></h5>
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
                            <label for="edit_name"><?php echo __('income_name'); ?></label>
                            <input type="text" id="edit_name" name="name" required>
                            <div class="invalid-feedback">
                                <?php echo __('please_enter_name'); ?>
                            </div>
                        </div>
                        
                        <div class="form-field">
                            <label for="edit_amount"><?php echo __('amount'); ?></label>
                            <div class="amount-input">
                                <span class="currency-symbol"><?php echo getCurrencySymbol(); ?></span>
                                <input type="number" id="edit_amount" name="amount" step="0.01" min="0.01" required>
                                <div class="invalid-feedback">
                                    <?php echo __('please_enter_valid_amount'); ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-field">
                            <label for="edit_frequency"><?php echo __('frequency'); ?></label>
                            <select id="edit_frequency" name="frequency" class="modern-select">
                                <option value="one-time"><?php echo __('one-time'); ?></option>
                                <option value="daily"><?php echo __('daily'); ?></option>
                                <option value="weekly"><?php echo __('weekly'); ?></option>
                                <option value="bi-weekly"><?php echo __('bi-weekly'); ?></option>
                                <option value="monthly"><?php echo __('monthly'); ?></option>
                                <option value="quarterly"><?php echo __('quarterly'); ?></option>
                                <option value="annually"><?php echo __('annually'); ?></option>
                            </select>
                        </div>
                        
                        <div class="form-field">
                            <label for="edit_start_date"><?php echo __('start_date'); ?></label>
                            <input type="date" id="edit_start_date" name="start_date" required>
                            <div class="invalid-feedback">
                                <?php echo __('please_select_start_date'); ?>
                            </div>
                        </div>
                        
                        <div class="form-field">
                            <label for="edit_end_date"><?php echo __('end_date_optional'); ?></label>
                            <input type="date" id="edit_end_date" name="end_date">
                            <small class="form-text text-muted"><?php echo __('leave_empty_for_ongoing'); ?></small>
                        </div>
                        
                        <div class="form-field full-width">
                            <label class="toggle-field">
                                <input type="checkbox" id="edit_is_active" name="is_active">
                                <span class="toggle-slider"></span>
                                <span class="toggle-label"><?php echo __('active_income_source'); ?></span>
                            </label>
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

<!-- Delete Income Modal -->
<div class="modal fade modern-modal" id="deleteIncomeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-icon delete">
                    <i class="fas fa-trash-alt"></i>
                </div>
                <h5 class="modal-title"><?php echo __('delete_income_source'); ?></h5>
                <button type="button" class="modal-close" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body text-center">
                <p><?php echo __('are_you_sure_delete_income'); ?></p>
                <p class="text-muted"><?php echo __('action_cannot_be_undone'); ?></p>
                <div class="delete-income-name mb-3 fw-bold"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" data-bs-dismiss="modal"><?php echo __('cancel'); ?></button>
                <form action="<?php echo BASE_PATH; ?>/income" method="post">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="income_id" id="delete_income_id">
                    <button type="submit" class="btn-submit danger">
                        <i class="fas fa-trash-alt"></i>
                        <?php echo __('delete'); ?>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Quick Edit Popover Template -->
<div id="quickEditTemplate" style="display: none;">
    <div class="quick-edit-form">
        <div class="form-group mb-2">
            <label for="quick_edit_amount"><?php echo __('amount'); ?></label>
            <div class="amount-input">
                <span class="currency-symbol"><?php echo getCurrencySymbol(); ?></span>
                <input type="number" id="quick_edit_amount" class="form-control form-control-sm" step="0.01" min="0.01">
            </div>
        </div>
        <div class="form-group mb-2">
            <label for="quick_edit_frequency"><?php echo __('frequency'); ?></label>
            <select id="quick_edit_frequency" class="form-select form-select-sm">
                <option value="one-time"><?php echo __('one-time'); ?></option>
                <option value="daily"><?php echo __('daily'); ?></option>
                <option value="weekly"><?php echo __('weekly'); ?></option>
                <option value="bi-weekly"><?php echo __('bi-weekly'); ?></option>
                <option value="monthly"><?php echo __('monthly'); ?></option>
                <option value="quarterly"><?php echo __('quarterly'); ?></option>
                <option value="annually"><?php echo __('annually'); ?></option>
            </select>
        </div>
        <div class="form-group mb-2">
            <div class="form-check form-switch">
                <input type="checkbox" id="quick_edit_active" class="form-check-input">
                <label for="quick_edit_active" class="form-check-label"><?php echo __('active'); ?></label>
            </div>
        </div>
        <div class="actions mt-3">
            <button type="button" class="btn btn-sm btn-primary quick-edit-save">
                <i class="fas fa-save"></i> <?php echo __('save'); ?>
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary quick-edit-cancel ms-2">
                <?php echo __('cancel'); ?>
            </button>
        </div>
    </div>
</div>

<!-- Duplicate Income Modal -->
<div class="modal fade modern-modal" id="duplicateIncomeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-icon">
                    <i class="fas fa-copy"></i>
                </div>
                <h5 class="modal-title"><?php echo __('duplicate_income_source'); ?></h5>
                <button type="button" class="modal-close" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="<?php echo BASE_PATH; ?>/income" method="post" class="needs-validation" novalidate>
                <input type="hidden" name="action" value="add">
                
                <div class="modal-body">
                    <div class="form-grid">
                        <div class="form-field full-width">
                            <label for="duplicate_name"><?php echo __('income_name'); ?></label>
                            <input type="text" id="duplicate_name" name="name" required>
                            <div class="invalid-feedback">
                                <?php echo __('please_enter_name'); ?>
                            </div>
                        </div>
                        
                        <div class="form-field">
                            <label for="duplicate_amount"><?php echo __('amount'); ?></label>
                            <div class="amount-input">
                                <span class="currency-symbol"><?php echo getCurrencySymbol(); ?></span>
                                <input type="number" id="duplicate_amount" name="amount" step="0.01" min="0.01" required>
                                <div class="invalid-feedback">
                                    <?php echo __('please_enter_valid_amount'); ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-field">
                            <label for="duplicate_frequency"><?php echo __('frequency'); ?></label>
                            <select id="duplicate_frequency" name="frequency" class="modern-select">
                                <option value="one-time"><?php echo __('one-time'); ?></option>
                                <option value="daily"><?php echo __('daily'); ?></option>
                                <option value="weekly"><?php echo __('weekly'); ?></option>
                                <option value="bi-weekly"><?php echo __('bi-weekly'); ?></option>
                                <option value="monthly" selected><?php echo __('monthly'); ?></option>
                                <option value="quarterly"><?php echo __('quarterly'); ?></option>
                                <option value="annually"><?php echo __('annually'); ?></option>
                            </select>
                        </div>
                        
                        <div class="form-field">
                            <label for="duplicate_start_date"><?php echo __('start_date'); ?></label>
                            <input type="date" id="duplicate_start_date" name="start_date" value="<?php echo date('Y-m-d'); ?>" required>
                            <div class="invalid-feedback">
                                <?php echo __('please_select_start_date'); ?>
                            </div>
                        </div>
                        
                        <div class="form-field">
                            <label for="duplicate_end_date"><?php echo __('end_date_optional'); ?></label>
                            <input type="date" id="duplicate_end_date" name="end_date">
                            <small class="form-text text-muted"><?php echo __('leave_empty_for_ongoing'); ?></small>
                        </div>
                        
                        <div class="form-field full-width">
                            <label class="toggle-field">
                                <input type="checkbox" id="duplicate_is_active" name="is_active" checked>
                                <span class="toggle-slider"></span>
                                <span class="toggle-label"><?php echo __('active_income_source'); ?></span>
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal"><?php echo __('cancel'); ?></button>
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-copy"></i>
                        <?php echo __('create_duplicate'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Toast Notification Container -->
<div class="toast-container position-fixed bottom-0 end-0 p-3"></div>

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
            
            // Use translate function for frequency name
            $freq_name = __($freq);
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

// Generate income projection data
$projection_data = [];
$projection_labels = [];
$current_month = date('n');
$current_year = date('Y');

// Get first day of current month
$first_day = new DateTime("$current_year-$current_month-01");

// Project 6 months by default
for ($i = 0; $i < 6; $i++) {
    $month_key = $first_day->format('Y-m');
    $projection_labels[] = $first_day->format('M Y');
    $projection_data[$month_key] = 0;
    $first_day->modify('+1 month');
}

// Calculate projected income for each month
if (isset($income_sources) && $income_sources->num_rows > 0) {
    $income_sources->data_seek(0);
    
    while ($source = $income_sources->fetch_assoc()) {
        if ($source['is_active'] != 1) continue;
        
        $startDate = new DateTime($source['start_date']);
        $endDate = !empty($source['end_date']) && $source['end_date'] !== '0000-00-00' && $source['end_date'] !== null ?
            new DateTime($source['end_date']) : null;
        
        // Only include income sources that are active during the projection period
        $first_day = new DateTime("$current_year-$current_month-01");
        
        for ($i = 0; $i < 6; $i++) {
            $month_key = $first_day->format('Y-m');
            $month_end = clone $first_day;
            $month_end->modify('last day of this month');
            
            // Skip if income starts after this month or ended before this month
            if ($startDate > $month_end || ($endDate !== null && $endDate < $first_day)) {
                $first_day->modify('+1 month');
                continue;
            }
            
            // Add income based on frequency
            switch ($source['frequency']) {
                case 'daily':
                    // Calculate days in the month
                    $days_in_month = $first_day->format('t');
                    $projection_data[$month_key] += $source['amount'] * $days_in_month;
                    break;
                    
                case 'weekly':
                    // Approximately 4.33 weeks per month
                    $projection_data[$month_key] += $source['amount'] * 4.33;
                    break;
                    
                case 'bi-weekly':
                    // Approximately 2.17 bi-weeks per month
                    $projection_data[$month_key] += $source['amount'] * 2.17;
                    break;
                    
                case 'monthly':
                    $projection_data[$month_key] += $source['amount'];
                    break;
                    
                case 'quarterly':
                    // Check if this is a quarter month for this income
                    if (($first_day->format('n') - $startDate->format('n')) % 3 === 0) {
                        $projection_data[$month_key] += $source['amount'];
                    }
                    break;
                    
                case 'annually':
                    // Check if this is the anniversary month
                    if ($first_day->format('m') === $startDate->format('m')) {
                        $projection_data[$month_key] += $source['amount'];
                    }
                    break;
                    
                case 'one-time':
                    // Only count one-time income if it falls in this month
                    $income_month = $startDate->format('Y-m');
                    if ($income_month === $month_key) {
                        $projection_data[$month_key] += $source['amount'];
                    }
                    break;
            }
            
            $first_day->modify('+1 month');
        }
    }
    
    $income_sources->data_seek(0);
}

// Convert projection data to flat array for chart
$projection_values = array_values($projection_data);

// Add meta tags for passing data to JS
echo '<meta name="base-path" content="' . BASE_PATH . '">';
echo '<meta name="chart-labels" content="' . htmlspecialchars(json_encode($chart_labels)) . '">';
echo '<meta name="chart-data" content="' . htmlspecialchars(json_encode($chart_data)) . '">';
echo '<meta name="chart-colors" content="' . htmlspecialchars(json_encode(array_slice($chart_colors, 0, count($chart_data)))) . '">';
echo '<meta name="currency-symbol" content="' . htmlspecialchars(getCurrencySymbol()) . '">';
echo '<meta name="projection-labels" content="' . htmlspecialchars(json_encode($projection_labels)) . '">';
echo '<meta name="projection-data" content="' . htmlspecialchars(json_encode($projection_values)) . '">';
// Add translation strings for JavaScript
echo '<meta name="i18n-empty-results" content="' . htmlspecialchars(__('no_matching_income_sources')) . '">';
echo '<meta name="i18n-try-adjusting" content="' . htmlspecialchars(__('try_adjusting_search')) . '">';
echo '<meta name="i18n-data-updated" content="' . htmlspecialchars(__('data_updated_successfully')) . '">';
echo '<meta name="i18n-saving" content="' . htmlspecialchars(__('saving')) . '">';
echo '<meta name="i18n-save-success" content="' . htmlspecialchars(__('changes_saved_successfully')) . '">';
echo '<meta name="i18n-save-error" content="' . htmlspecialchars(__('error_saving_changes')) . '">';

require_once 'includes/footer.php';
?>