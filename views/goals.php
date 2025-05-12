<?php
// Set page title and current page for menu highlighting
$page_title = __('financial_goals') . ' - ' . __('app_name');
$current_page = 'goals';

// Additional CSS and JS
$additional_css = ['/assets/css/goals-modern.css'];
$additional_js = ['/assets/js/goals-modern.js'];

// Include header
require_once 'includes/header.php';
?>

<!-- Main Content Wrapper -->
<div class="goals-page">
    <!-- Page Header Section -->
    <div class="page-header-section">
        <div class="page-header-content">
            <div class="page-title-group">
                <h1 class="page-title"><?php echo __('financial_goals'); ?></h1>
                <p class="page-subtitle"><?php echo __('track_achieve_objectives'); ?></p>
            </div>
            <div class="page-actions">
                <button type="button" class="btn-success" data-bs-toggle="modal" data-bs-target="#recommendGoalsModal">
                    <i class="fas fa-magic"></i>
                    <span><?php echo __('goal_recommendations'); ?></span>
                </button>
                <button type="button" class="btn-primary" data-bs-toggle="modal" data-bs-target="#addGoalModal">
                    <i class="fas fa-plus-circle"></i>
                    <span><?php echo __('add_goal'); ?></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Quick Stats Section -->
    <div class="stats-section">
        <div class="stats-grid">
            <?php
            // Initialize counters
            $total_goals = 0;
            $completed_goals = 0;
            $on_track_goals = 0;
            $behind_goals = 0;
            
            if (isset($goals_list) && $goals_list->num_rows > 0) {
                $total_goals = $goals_list->num_rows;
                
                // First pass to count goal stats
                while ($goal = $goals_list->fetch_assoc()) {
                    if ($goal['status'] === 'completed') {
                        $completed_goals++;
                    } else {
                        // Calculate progress
                        $progress = ($goal['current_amount'] / $goal['target_amount']) * 100;
                        
                        // Calculate time progress
                        $start_date = new DateTime($goal['start_date']);
                        $target_date = new DateTime($goal['target_date']);
                        $now = new DateTime();
                        
                        $total_interval = $start_date->diff($target_date);
                        $elapsed_interval = $start_date->diff($now);
                        
                        $total_days = max(1, $total_interval->days);
                        $elapsed_days = $elapsed_interval->days;
                        
                        $time_progress = $total_days > 0 ? ($elapsed_days / $total_days) * 100 : 100;
                        
                        if ($progress >= $time_progress) {
                            $on_track_goals++;
                        } else {
                            $behind_goals++;
                        }
                    }
                }
                
                // Reset pointer for later use
                $goals_list->data_seek(0);
            }
            ?>

            <div class="stat-card total">
                <div class="stat-icon">
                    <i class="fas fa-flag"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-label"><?php echo __('total_goals'); ?></h3>
                    <p class="stat-value"><?php echo $total_goals; ?></p>
                </div>
            </div>

            <div class="stat-card completed">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-label"><?php echo __('completed'); ?></h3>
                    <p class="stat-value"><?php echo $completed_goals; ?></p>
                </div>
            </div>

            <div class="stat-card on-track">
                <div class="stat-icon">
                    <i class="fas fa-thumbs-up"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-label"><?php echo __('on_track'); ?></h3>
                    <p class="stat-value"><?php echo $on_track_goals; ?></p>
                </div>
            </div>

            <div class="stat-card behind">
                <div class="stat-icon">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-label"><?php echo __('needs_attention'); ?></h3>
                    <p class="stat-value"><?php echo $behind_goals; ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Goals List Section -->
    <div class="goals-section">
        <div class="section-card">
            <div class="section-header">
                <div class="section-title">
                    <i class="fas fa-bullseye"></i>
                    <h2><?php echo __('your_financial_goals'); ?></h2>
                </div>
                <div class="goals-filter">
                    <button type="button" class="filter-btn active" data-filter="all"><?php echo __('all'); ?></button>
                    <button type="button" class="filter-btn" data-filter="in-progress"><?php echo __('in_progress'); ?></button>
                    <button type="button" class="filter-btn" data-filter="completed"><?php echo __('completed'); ?></button>
                </div>
            </div>
            <div class="section-body">
                <?php if (isset($goals_list) && $goals_list->num_rows > 0): ?>
                    <div class="goals-list">
                        <?php while ($goal = $goals_list->fetch_assoc()): ?>
                            <?php
                            // Calculate progress
                            $progress = ($goal['current_amount'] / $goal['target_amount']) * 100;
                            
                            // Calculate time progress
                            $start_date = new DateTime($goal['start_date']);
                            $target_date = new DateTime($goal['target_date']);
                            $now = new DateTime();
                            
                            $days_remaining = $now->diff($target_date)->format("%r%a");
                            $total_days = max(1, $start_date->diff($target_date)->days);
                            $elapsed_days = $start_date->diff($now)->days;
                            
                            $time_progress = $total_days > 0 ? min(100, ($elapsed_days / $total_days) * 100) : 100;
                            
                            // Determine progress status
                            $progress_class = '';
                            $progress_icon = '';
                            $status_class = '';
                            
                            if ($goal['status'] === 'completed') {
                                $progress_class = 'bg-success';
                                $progress_icon = 'check-circle';
                                $status_class = 'status-success';
                            } elseif ($progress >= $time_progress) {
                                $progress_class = 'bg-info';
                                $progress_icon = 'thumbs-up';
                                $status_class = 'status-success';
                            } elseif ($progress >= $time_progress * 0.7) {
                                $progress_class = 'bg-primary';
                                $progress_icon = 'clipboard-check';
                                $status_class = 'status-info';
                            } else {
                                $progress_class = 'bg-warning';
                                $progress_icon = 'exclamation-triangle';
                                $status_class = 'status-warning';
                            }
                            
                            // Determine due date status
                            $due_class = '';
                            if ($days_remaining < 0) {
                                $due_class = 'text-danger';
                            } elseif ($days_remaining < 30) {
                                $due_class = 'text-warning';
                            } else {
                                $due_class = 'text-info';
                            }
                            
                            // Calculate monthly contribution needed
                            $monthly_contribution = 0;
                            if ($days_remaining > 0 && $goal['status'] !== 'completed') {
                                $remaining_amount = max(0, $goal['target_amount'] - $goal['current_amount']);
                                $months_remaining = $days_remaining / 30.44; // Average days per month
                                $monthly_contribution = $months_remaining > 0 ? $remaining_amount / $months_remaining : $remaining_amount;
                            }
                            ?>
                            
                            <div class="goal-card" data-status="<?php echo $goal['status']; ?>">
                                <div class="goal-header">
                                    <div class="goal-title-wrapper">
                                        <h3 class="goal-title"><?php echo htmlspecialchars($goal['name']); ?></h3>
                                        <?php if ($goal['status'] === 'completed'): ?>
                                            <span class="goal-badge badge-completed"><?php echo __('completed'); ?></span>
                                        <?php elseif ($goal['priority'] === 'high'): ?>
                                            <span class="goal-badge badge-high"><?php echo __('high_priority'); ?></span>
                                        <?php elseif ($goal['priority'] === 'medium'): ?>
                                            <span class="goal-badge badge-medium"><?php echo __('medium_priority'); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="goal-actions">
                                        <?php if ($goal['status'] !== 'completed'): ?>
                                            <button class="btn-action success update-progress" data-goal-id="<?php echo $goal['goal_id']; ?>">
                                                <i class="fas fa-plus"></i>
                                                <span><?php echo __('update_progress'); ?></span>
                                            </button>
                                        <?php endif; ?>
                                        <button class="btn-action info edit-goal" data-goal-id="<?php echo $goal['goal_id']; ?>">
                                            <i class="fas fa-edit"></i>
                                            <span><?php echo __('edit'); ?></span>
                                        </button>
                                        <button class="btn-action danger delete-goal" data-goal-id="<?php echo $goal['goal_id']; ?>">
                                            <i class="fas fa-trash"></i>
                                            <span><?php echo __('delete'); ?></span>
                                        </button>
                                    </div>
                                </div>
                                <div class="goal-body">
                                    <div class="goal-content">
                                        <div class="goal-progress-section">
                                            <?php if (!empty($goal['description'])): ?>
                                                <p class="goal-description"><?php echo htmlspecialchars($goal['description']); ?></p>
                                            <?php endif; ?>
                                            
                                            <div class="progress-wrapper">
                                                <div class="progress">
                                                    <div class="progress-bar <?php echo $progress_class; ?>" 
                                                         role="progressbar" 
                                                         style="width: <?php echo min(100, $progress); ?>%"
                                                         aria-valuenow="<?php echo min(100, $progress); ?>" 
                                                         aria-valuemin="0" 
                                                         aria-valuemax="100">
                                                    </div>
                                                </div>
                                                <div class="goal-progress-info">
                                                    <div class="progress-text">
                                                        <span class="amount">$<?php echo number_format($goal['current_amount'], 2); ?></span> <?php echo __('of'); ?> 
                                                        <span class="amount">$<?php echo number_format($goal['target_amount'], 2); ?></span>
                                                    </div>
                                                    <div class="progress-status <?php echo $status_class; ?>">
                                                        <i class="fas fa-<?php echo $progress_icon; ?>"></i>
                                                        <?php if ($goal['status'] === 'completed'): ?>
                                                            <?php echo __('completed'); ?>
                                                        <?php elseif ($progress >= $time_progress): ?>
                                                            <?php echo __('on_track'); ?>
                                                        <?php else: ?>
                                                            <?php echo __('behind_schedule'); ?>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="goal-meta-section">
                                            <div class="goal-meta-grid">
                                                <div class="meta-item" style="grid-column: 1 / -1;">
                                                    <span class="meta-label"><?php echo __('progress'); ?></span>
                                                    <span class="meta-value"><?php echo number_format($progress, 0); ?>%</span>
                                                </div>
                                                <div class="meta-item">
                                                    <span class="meta-label"><?php echo __('started'); ?></span>
                                                    <span class="meta-value"><?php echo date('M j, Y', strtotime($goal['start_date'])); ?></span>
                                                </div>
                                                <div class="meta-item">
                                                    <span class="meta-label"><?php echo __('target_date'); ?></span>
                                                    <span class="meta-value <?php echo $due_class; ?>"><?php echo date('M j, Y', strtotime($goal['target_date'])); ?></span>
                                                </div>
                                                <?php if ($goal['status'] !== 'completed' && $days_remaining > 0): ?>
                                                    <div class="meta-item">
                                                        <span class="meta-label"><?php echo __('days_remaining'); ?></span>
                                                        <span class="meta-value <?php echo $due_class; ?>"><?php echo $days_remaining; ?></span>
                                                    </div>
                                                    <div class="meta-item">
                                                        <span class="meta-label"><?php echo __('monthly_needed'); ?></span>
                                                        <span class="meta-value text-primary">$<?php echo number_format($monthly_contribution, 2); ?></span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-flag"></i>
                        </div>
                        <h3><?php echo __('no_goals_set_yet'); ?></h3>
                        <p><?php echo __('set_clear_goals_message'); ?></p>
                        <div class="empty-actions">
                            <button type="button" class="btn-primary" data-bs-toggle="modal" data-bs-target="#addGoalModal">
                                <i class="fas fa-plus"></i> <?php echo __('set_your_first_goal'); ?>
                            </button>
                            <button type="button" class="btn-success" data-bs-toggle="modal" data-bs-target="#recommendGoalsModal">
                                <i class="fas fa-magic"></i> <?php echo __('get_recommendations'); ?>
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Goal Planning Tips -->
    <div class="tips-section">
        <div class="tips-header">
            <i class="fas fa-lightbulb"></i>
            <h2><?php echo __('goal_planning_tips'); ?></h2>
        </div>
        <div class="tips-grid">
            <div class="tip-card info">
                <h5><?php echo __('smart_goals'); ?></h5>
                <p><?php echo __('smart_goals_desc'); ?></p>
            </div>
            <div class="tip-card warning">
                <h5><?php echo __('prioritize_your_goals'); ?></h5>
                <p><?php echo __('prioritize_goals_desc'); ?></p>
            </div>
            <div class="tip-card success">
                <h5><?php echo __('regular_contributions'); ?></h5>
                <p><?php echo __('regular_contributions_desc'); ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Add Goal Modal -->
<div class="modal fade" id="addGoalModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-icon">
                    <i class="fas fa-plus-circle"></i>
                </div>
                <h5 class="modal-title"><?php echo __('add_financial_goal'); ?></h5>
                <button type="button" class="modal-close" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="<?php echo BASE_PATH; ?>/goals" method="post" class="needs-validation" novalidate>
                <input type="hidden" name="action" value="add">
                
                <div class="modal-body">
                    <div class="form-grid">
                        <div class="form-field">
                            <label for="name"><?php echo __('goal_name'); ?></label>
                            <input type="text" id="name" name="name" required>
                            <div class="invalid-feedback"><?php echo __('please_provide_goal_name'); ?></div>
                        </div>
                        
                        <div class="form-field">
                            <label for="description"><?php echo __('description_optional'); ?></label>
                            <textarea id="description" name="description" rows="2"></textarea>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-field">
                                <label for="target_amount"><?php echo __('target_amount'); ?></label>
                                <div class="input-group">
                                    <span class="currency-symbol">$</span>
                                    <input type="number" id="target_amount" name="target_amount" step="0.01" min="0.01" required>
                                </div>
                                <div class="invalid-feedback"><?php echo __('please_provide_valid_target_amount'); ?></div>
                            </div>
                            <div class="form-field">
                                <label for="current_amount"><?php echo __('current_amount'); ?></label>
                                <div class="input-group">
                                    <span class="currency-symbol">$</span>
                                    <input type="number" id="current_amount" name="current_amount" step="0.01" min="0" value="0">
                                </div>
                                <div class="invalid-feedback"><?php echo __('current_amount_nonnegative'); ?></div>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-field">
                                <label for="start_date"><?php echo __('start_date'); ?></label>
                                <input type="date" id="start_date" name="start_date" value="<?php echo date('Y-m-d'); ?>" required>
                                <div class="invalid-feedback"><?php echo __('please_provide_start_date'); ?></div>
                            </div>
                            <div class="form-field">
                                <label for="target_date"><?php echo __('target_date'); ?></label>
                                <input type="date" id="target_date" name="target_date" value="<?php echo date('Y-m-d', strtotime('+1 year')); ?>" required>
                                <div class="invalid-feedback"><?php echo __('target_date_after_start_date'); ?></div>
                            </div>
                        </div>
                        
                        <div class="form-field">
                            <label for="priority"><?php echo __('priority'); ?></label>
                            <select id="priority" name="priority">
                                <option value="low"><?php echo __('low'); ?></option>
                                <option value="medium" selected><?php echo __('medium'); ?></option>
                                <option value="high"><?php echo __('high'); ?></option>
                            </select>
                        </div>
                        
                        <div id="goalCalculator" class="calculator-info d-none">
                            <h6><?php echo __('goal_calculator'); ?></h6>
                            <div class="info-row">
                                <span class="info-label"><?php echo __('monthly_contribution_needed'); ?></span>
                                <span class="info-value" id="monthlyContribution">$0.00</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label"><?php echo __('total_time_to_achieve'); ?></span>
                                <span class="info-value" id="timeToGoal">0 <?php echo __('months'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal"><?php echo __('cancel'); ?></button>
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-plus"></i>
                        <?php echo __('add_goal'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Goal Modal -->
<div class="modal fade" id="editGoalModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-icon edit">
                    <i class="fas fa-edit"></i>
                </div>
                <h5 class="modal-title"><?php echo __('edit_financial_goal'); ?></h5>
                <button type="button" class="modal-close" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="<?php echo BASE_PATH; ?>/goals" method="post" class="needs-validation" novalidate>
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="goal_id" id="edit_goal_id">
                
                <div class="modal-body">
                    <div class="form-grid">
                        <div class="form-field">
                            <label for="edit_name"><?php echo __('goal_name'); ?></label>
                            <input type="text" id="edit_name" name="name" required>
                            <div class="invalid-feedback"><?php echo __('please_provide_goal_name'); ?></div>
                        </div>
                        
                        <div class="form-field">
                            <label for="edit_description"><?php echo __('description_optional'); ?></label>
                            <textarea id="edit_description" name="description" rows="2"></textarea>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-field">
                                <label for="edit_target_amount"><?php echo __('target_amount'); ?></label>
                                <div class="input-group">
                                    <span class="currency-symbol">$</span>
                                    <input type="number" id="edit_target_amount" name="target_amount" step="0.01" min="0.01" required>
                                </div>
                                <div class="invalid-feedback"><?php echo __('please_provide_valid_target_amount'); ?></div>
                            </div>
                            <div class="form-field">
                                <label for="edit_current_amount"><?php echo __('current_amount'); ?></label>
                                <div class="input-group">
                                    <span class="currency-symbol">$</span>
                                    <input type="number" id="edit_current_amount" name="current_amount" step="0.01" min="0">
                                </div>
                                <div class="invalid-feedback"><?php echo __('current_amount_nonnegative'); ?></div>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-field">
                                <label for="edit_start_date"><?php echo __('start_date'); ?></label>
                                <input type="date" id="edit_start_date" name="start_date" required>
                                <div class="invalid-feedback"><?php echo __('please_provide_start_date'); ?></div>
                            </div>
                            <div class="form-field">
                                <label for="edit_target_date"><?php echo __('target_date'); ?></label>
                                <input type="date" id="edit_target_date" name="target_date" required>
                                <div class="invalid-feedback"><?php echo __('target_date_after_start_date'); ?></div>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-field">
                                <label for="edit_priority"><?php echo __('priority'); ?></label>
                                <select id="edit_priority" name="priority">
                                    <option value="low"><?php echo __('low'); ?></option>
                                    <option value="medium"><?php echo __('medium'); ?></option>
                                    <option value="high"><?php echo __('high'); ?></option>
                                </select>
                            </div>
                            <div class="form-field">
                                <label for="edit_status"><?php echo __('status'); ?></label>
                                <select id="edit_status" name="status">
                                    <option value="in-progress"><?php echo __('in_progress'); ?></option>
                                    <option value="completed"><?php echo __('completed'); ?></option>
                                    <option value="cancelled"><?php echo __('cancel'); ?></option>
                                </select>
                            </div>
                        </div>
                        
                        <div id="editGoalCalculator" class="calculator-info d-none">
                            <h6><?php echo __('goal_calculator'); ?></h6>
                            <div class="info-row">
                                <span class="info-label"><?php echo __('monthly_contribution_needed'); ?></span>
                                <span class="info-value" id="editMonthlyContribution">$0.00</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label"><?php echo __('total_time_to_achieve'); ?></span>
                                <span class="info-value" id="editTimeToGoal">0 <?php echo __('months'); ?></span>
                            </div>
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

<!-- Update Progress Modal -->
<div class="modal fade" id="updateProgressModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-icon success">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h5 class="modal-title"><?php echo __('update_goal_progress'); ?></h5>
                <button type="button" class="modal-close" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="<?php echo BASE_PATH; ?>/goals" method="post" class="needs-validation" novalidate>
                <input type="hidden" name="action" value="update_progress">
                <input type="hidden" name="goal_id" id="progress_goal_id">
                
                <div class="modal-body">
                    <div class="progress-modal-info">
                        <h6 id="progress_goal_name"></h6>
                        <div class="progress-visualization">
                            <div class="progress-bar" id="progress_bar" role="progressbar"></div>
                        </div>
                        <div class="progress-details">
                            <span><?php echo __('current_amount'); ?>: <strong id="progress_current_amount">$0.00</strong></span>
                            <span><?php echo __('target_amount'); ?>: <strong id="progress_target_amount">$0.00</strong></span>
                        </div>
                    </div>
                    
                    <div class="form-field">
                        <label for="progress_amount"><?php echo __('add_to_current_amount'); ?></label>
                        <div class="input-group">
                            <span class="currency-symbol">$</span>
                            <input type="number" id="progress_amount" name="amount" step="0.01" min="0.01" required>
                        </div>
                        <div class="invalid-feedback"><?php echo __('please_enter_valid_amount'); ?></div>
                        <div class="form-text"><?php echo __('add_to_progress'); ?></div>
                    </div>
                    
                    <div class="completion-alert d-none" id="progress_completion_alert">
                        <i class="fas fa-check-circle"></i>
                        <span><?php echo __('completion_alert'); ?></span>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal"><?php echo __('cancel'); ?></button>
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-arrow-up"></i>
                        <?php echo __('update_progress'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Goal Modal -->
<div class="modal fade" id="deleteGoalModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-icon delete">
                    <i class="fas fa-trash-alt"></i>
                </div>
                <h5 class="modal-title"><?php echo __('delete_goal'); ?></h5>
                <button type="button" class="modal-close" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body text-center">
                <p><?php echo __('are_you_sure_delete_goal'); ?></p>
                <p><strong id="delete_goal_name"></strong></p>
                <p class="text-muted"><?php echo __('action_cannot_be_undone'); ?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" data-bs-dismiss="modal"><?php echo __('cancel'); ?></button>
                <form action="<?php echo BASE_PATH; ?>/goals" method="post" style="display: inline;">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="goal_id" id="delete_goal_id">
                    <button type="submit" class="btn-submit danger">
                        <i class="fas fa-trash-alt"></i>
                        <?php echo __('delete'); ?>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Recommend Goals Modal -->
<div class="modal fade" id="recommendGoalsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-icon recommendation">
                    <i class="fas fa-magic"></i>
                </div>
                <h5 class="modal-title"><?php echo __('goal_recommendations'); ?></h5>
                <button type="button" class="modal-close" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <?php if (isset($show_recommendations) && isset($recommended_goals)): ?>
                    <div class="recommendation-info">
                        <i class="fas fa-info-circle"></i>
                        <p><?php echo __('based_on_monthly_income'); ?> $<?php echo number_format($monthly_income, 2); ?>, <?php echo __('recommended_financial_goals'); ?>.</p>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="recommendations-table">
                            <thead>
                                <tr>
                                    <th><?php echo __('goal'); ?></th>
                                    <th><?php echo __('description'); ?></th>
                                    <th><?php echo __('target_amount'); ?></th>
                                    <th><?php echo __('monthly'); ?></th>
                                    <th><?php echo __('priority'); ?></th>
                                    <th><?php echo __('action'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recommended_goals as $rec_goal): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($rec_goal['name']); ?></td>
                                        <td><?php echo htmlspecialchars($rec_goal['description']); ?></td>
                                        <td>$<?php echo number_format($rec_goal['target_amount'], 2); ?></td>
                                        <td>$<?php echo number_format($rec_goal['monthly_contribution'], 2); ?></td>
                                        <td>
                                            <span class="priority-badge priority-<?php echo $rec_goal['priority']; ?>">
                                                <?php echo ucfirst($rec_goal['priority']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button type="button" class="btn-adopt adopt-goal" 
                                                data-name="<?php echo htmlspecialchars($rec_goal['name']); ?>"
                                                data-description="<?php echo htmlspecialchars($rec_goal['description']); ?>"
                                                data-target="<?php echo $rec_goal['target_amount']; ?>"
                                                data-priority="<?php echo $rec_goal['priority']; ?>"
                                                data-timeline="<?php echo $rec_goal['timeline']; ?>">
                                                <i class="fas fa-plus"></i> <?php echo __('adopt'); ?>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <form action="<?php echo BASE_PATH; ?>/goals" method="post">
                        <input type="hidden" name="action" value="recommend_goals">
                        
                        <div class="recommendation-info">
                            <i class="fas fa-info-circle"></i>
                            <p><?php echo __('budget_info'); ?></p>
                        </div>
                        
                        <div class="mb-4">
                            <p class="mb-1"><?php echo __('your_current_monthly_income'); ?> <strong>$<?php echo number_format($monthly_income, 2); ?></strong></p>
                            
                            <p class="mb-2"><?php echo __('budget_will_be_created_with'); ?></p>
                            <ul>
                                <li><?php echo __('minimum_for_investments'); ?></li>
                                <li><?php echo __('smart_allocation'); ?></li>
                                <li><?php echo __('optimal_financial_ratios'); ?></li>
                            </ul>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn-submit">
                                <i class="fas fa-magic"></i> <?php echo __('generate_advice'); ?>
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" data-bs-dismiss="modal"><?php echo __('close'); ?></button>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
require_once 'includes/footer.php';
?>