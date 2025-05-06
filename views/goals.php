<?php
// Set page title and current page for menu highlighting
$page_title = 'Financial Goals - iGotMoney';
$current_page = 'goals';

// Additional JS
$additional_js = ['/assets/js/goals.js'];

// Include header
require_once 'includes/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Financial Goals</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#recommendGoalsModal">
                <i class="fas fa-magic"></i> Goal Recommendations
            </button>
        </div>
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addGoalModal">
            <i class="fas fa-plus"></i> Add Goal
        </button>
    </div>
</div>

<!-- Financial Goals Summary -->
<div class="row mb-4">
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
                
                $total_days = $total_interval->days;
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

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Goals</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_goals; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-flag fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Completed</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $completed_goals; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            On Track</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $on_track_goals; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-thumbs-up fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Needs Attention</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $behind_goals; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-exclamation-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Financial Goals List -->
<div class="row mb-4">
    <div class="col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Your Financial Goals</h6>
                <div class="btn-group">
                    <button class="btn btn-sm btn-outline-secondary active" id="viewAll">All</button>
                    <button class="btn btn-sm btn-outline-secondary" id="viewInProgress">In Progress</button>
                    <button class="btn btn-sm btn-outline-secondary" id="viewCompleted">Completed</button>
                </div>
            </div>
            <div class="card-body">
                <?php if (isset($goals_list) && $goals_list->num_rows > 0): ?>
                    <?php while ($goal = $goals_list->fetch_assoc()): ?>
                        <?php
                        // Calculate progress
                        $progress = ($goal['current_amount'] / $goal['target_amount']) * 100;
                        
                        // Calculate time progress
                        $start_date = new DateTime($goal['start_date']);
                        $target_date = new DateTime($goal['target_date']);
                        $now = new DateTime();
                        
                        $days_remaining = $now->diff($target_date)->format("%r%a");
                        $total_days = $start_date->diff($target_date)->days;
                        $elapsed_days = $start_date->diff($now)->days;
                        
                        $time_progress = $total_days > 0 ? min(100, ($elapsed_days / $total_days) * 100) : 100;
                        
                        // Determine progress status
                        $progress_class = '';
                        $progress_icon = '';
                        
                        if ($goal['status'] === 'completed') {
                            $progress_class = 'bg-success';
                            $progress_icon = 'check-circle';
                        } elseif ($progress >= $time_progress) {
                            $progress_class = 'bg-info';
                            $progress_icon = 'thumbs-up';
                        } elseif ($progress >= $time_progress * 0.7) {
                            $progress_class = 'bg-primary';
                            $progress_icon = 'clipboard-check';
                        } else {
                            $progress_class = 'bg-warning';
                            $progress_icon = 'exclamation-triangle';
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
                            $remaining_amount = $goal['target_amount'] - $goal['current_amount'];
                            $months_remaining = $days_remaining / 30.44; // Average days per month
                            $monthly_contribution = $months_remaining > 0 ? $remaining_amount / $months_remaining : $remaining_amount;
                        }
                        ?>
                        
                        <div class="goal-item mb-4 <?php echo $goal['status']; ?>-goal">
                            <div class="card">
                                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <?php echo htmlspecialchars($goal['name']); ?>
                                        
                                        <?php if ($goal['status'] === 'completed'): ?>
                                            <span class="badge bg-success ms-2">Completed</span>
                                        <?php elseif ($goal['priority'] === 'high'): ?>
                                            <span class="badge bg-danger ms-2">High Priority</span>
                                        <?php elseif ($goal['priority'] === 'medium'): ?>
                                            <span class="badge bg-warning ms-2">Medium Priority</span>
                                        <?php endif; ?>
                                    </h6>
                                    <div>
                                        <?php if ($goal['status'] !== 'completed'): ?>
                                            <button class="btn btn-sm btn-success update-progress" data-goal-id="<?php echo $goal['goal_id']; ?>">
                                                <i class="fas fa-plus"></i> Update Progress
                                            </button>
                                        <?php endif; ?>
                                        <button class="btn btn-sm btn-info edit-goal" data-goal-id="<?php echo $goal['goal_id']; ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger delete-goal" data-goal-id="<?php echo $goal['goal_id']; ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <?php if (!empty($goal['description'])): ?>
                                                <p><?php echo htmlspecialchars($goal['description']); ?></p>
                                            <?php endif; ?>
                                            
                                            <div class="progress mb-2">
                                                <div class="progress-bar <?php echo $progress_class; ?>" role="progressbar" 
                                                    style="width: <?php echo min(100, $progress); ?>%" 
                                                    aria-valuenow="<?php echo $progress; ?>" 
                                                    aria-valuemin="0" aria-valuemax="100">
                                                    <?php echo number_format($progress, 0); ?>%
                                                </div>
                                            </div>
                                            
                                            <div class="d-flex justify-content-between">
                                                <span>
                                                    <strong>$<?php echo number_format($goal['current_amount'], 2); ?></strong> of 
                                                    <strong>$<?php echo number_format($goal['target_amount'], 2); ?></strong>
                                                </span>
                                                <span>
                                                    <i class="fas fa-<?php echo $progress_icon; ?> me-1 <?php echo $progress_class === 'bg-success' ? 'text-success' : ($progress_class === 'bg-warning' ? 'text-warning' : 'text-info'); ?>"></i>
                                                    <?php if ($goal['status'] === 'completed'): ?>
                                                        Completed
                                                    <?php elseif ($progress >= $time_progress): ?>
                                                        On Track
                                                    <?php else: ?>
                                                        Behind Schedule
                                                    <?php endif; ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card h-100">
                                                <div class="card-body">
                                                    <div class="mb-2">
                                                        <strong>Started:</strong> <?php echo date('M j, Y', strtotime($goal['start_date'])); ?>
                                                    </div>
                                                    <div class="mb-2">
                                                        <strong>Target Date:</strong> 
                                                        <span class="<?php echo $due_class; ?>">
                                                            <?php echo date('M j, Y', strtotime($goal['target_date'])); ?>
                                                        </span>
                                                    </div>
                                                    <?php if ($goal['status'] !== 'completed' && $days_remaining > 0): ?>
                                                        <div class="mb-2">
                                                            <strong>Days Remaining:</strong> 
                                                            <span class="<?php echo $due_class; ?>">
                                                                <?php echo $days_remaining; ?>
                                                            </span>
                                                        </div>
                                                        <div>
                                                            <strong>Monthly Needed:</strong> 
                                                            <span class="text-primary">$<?php echo number_format($monthly_contribution, 2); ?></span>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="text-center py-4">
                        <p>You haven't set any financial goals yet.</p>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addGoalModal">
                            <i class="fas fa-plus"></i> Set Your First Goal
                        </button>
                        <span class="mx-2">or</span>
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#recommendGoalsModal">
                            <i class="fas fa-magic"></i> Get Goal Recommendations
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Goal Planning Tips -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Goal Planning Tips</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <div class="card border-left-info h-100 py-2">
                    <div class="card-body">
                        <h5 class="card-title">SMART Goals</h5>
                        <p class="card-text">Set goals that are Specific, Measurable, Achievable, Relevant, and Time-bound to increase your chances of success.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-left-warning h-100 py-2">
                    <div class="card-body">
                        <h5 class="card-title">Prioritize Your Goals</h5>
                        <p class="card-text">Focus on high-priority goals first. It's better to fully achieve a few important goals than partially complete many.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-left-success h-100 py-2">
                    <div class="card-body">
                        <h5 class="card-title">Regular Contributions</h5>
                        <p class="card-text">Make regular contributions toward your goals. Even small, consistent amounts add up significantly over time.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Goal Modal -->
<div class="modal fade" id="addGoalModal" tabindex="-1" aria-labelledby="addGoalModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addGoalModalLabel">Add Financial Goal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?php echo BASE_PATH; ?>/goals" method="post">
                <input type="hidden" name="action" value="add">
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Goal Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description (Optional)</label>
                        <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="target_amount" class="form-label">Target Amount</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="target_amount" name="target_amount" step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="current_amount" class="form-label">Current Amount</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="current_amount" name="current_amount" step="0.01" min="0" value="0">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="target_date" class="form-label">Target Date</label>
                            <input type="date" class="form-control" id="target_date" name="target_date" value="<?php echo date('Y-m-d', strtotime('+1 year')); ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="priority" class="form-label">Priority</label>
                        <select class="form-select" id="priority" name="priority">
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>
                    
                    <div id="goalCalculator" class="alert alert-info d-none">
                        <h6 class="alert-heading">Goal Calculator</h6>
                        <div class="mb-2">
                            <span>Monthly contribution needed: </span>
                            <strong id="monthlyContribution">$0.00</strong>
                        </div>
                        <div>
                            <span>Total time to achieve goal: </span>
                            <strong id="timeToGoal">0 months</strong>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Goal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Goal Modal -->
<div class="modal fade" id="editGoalModal" tabindex="-1" aria-labelledby="editGoalModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editGoalModalLabel">Edit Financial Goal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?php echo BASE_PATH; ?>/goals" method="post">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="goal_id" id="edit_goal_id">
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Goal Name</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description (Optional)</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="2"></textarea>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="edit_target_amount" class="form-label">Target Amount</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="edit_target_amount" name="target_amount" step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_current_amount" class="form-label">Current Amount</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="edit_current_amount" name="current_amount" step="0.01" min="0">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="edit_start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="edit_start_date" name="start_date" required>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_target_date" class="form-label">Target Date</label>
                            <input type="date" class="form-control" id="edit_target_date" name="target_date" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="edit_priority" class="form-label">Priority</label>
                            <select class="form-select" id="edit_priority" name="priority">
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_status" class="form-label">Status</label>
                            <select class="form-select" id="edit_status" name="status">
                                <option value="in-progress">In Progress</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>
                    
                    <div id="editGoalCalculator" class="alert alert-info d-none">
                        <h6 class="alert-heading">Goal Calculator</h6>
                        <div class="mb-2">
                            <span>Monthly contribution needed: </span>
                            <strong id="editMonthlyContribution">$0.00</strong>
                        </div>
                        <div>
                            <span>Total time to achieve goal: </span>
                            <strong id="editTimeToGoal">0 months</strong>
                        </div>
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

<!-- Update Progress Modal -->
<div class="modal fade" id="updateProgressModal" tabindex="-1" aria-labelledby="updateProgressModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateProgressModalLabel">Update Goal Progress</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?php echo BASE_PATH; ?>/goals" method="post">
                <input type="hidden" name="action" value="update_progress">
                <input type="hidden" name="goal_id" id="progress_goal_id">
                
                <div class="modal-body">
                    <div class="mb-3">
                        <h6 id="progress_goal_name" class="fw-bold"></h6>
                        <div class="progress mb-2">
                            <div class="progress-bar bg-info" id="progress_bar" role="progressbar" style="width: 0%"></div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>
                                Current: <strong id="progress_current_amount">$0.00</strong>
                            </span>
                            <span>
                                Target: <strong id="progress_target_amount">$0.00</strong>
                            </span>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="progress_amount" class="form-label">Add to Current Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="progress_amount" name="amount" step="0.01" min="0" required>
                        </div>
                        <div class="form-text">Enter the amount you want to add to your current progress.</div>
                    </div>
                    
                    <div class="alert alert-success d-none" id="progress_completion_alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <span>This contribution will complete your goal!</span>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Update Progress</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Goal Modal -->
<div class="modal fade" id="deleteGoalModal" tabindex="-1" aria-labelledby="deleteGoalModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteGoalModalLabel">Delete Financial Goal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this financial goal? This action cannot be undone.</p>
                <p class="fw-bold" id="delete_goal_name"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="<?php echo BASE_PATH; ?>/goals" method="post">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="goal_id" id="delete_goal_id">
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Recommend Goals Modal -->
<div class="modal fade" id="recommendGoalsModal" tabindex="-1" aria-labelledby="recommendGoalsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="recommendGoalsModalLabel">Goal Recommendations</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php if (isset($show_recommendations) && isset($recommended_goals)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Based on your monthly income of $<?php echo number_format($monthly_income, 2); ?>, here are some recommended financial goals.
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Goal</th>
                                    <th>Description</th>
                                    <th>Target Amount</th>
                                    <th>Monthly Contribution</th>
                                    <th>Priority</th>
                                    <th>Action</th>
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
                                            <span class="badge bg-<?php echo $rec_goal['priority'] === 'high' ? 'danger' : ($rec_goal['priority'] === 'medium' ? 'warning' : 'info'); ?>">
                                                <?php echo ucfirst($rec_goal['priority']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-success adopt-goal" 
                                                data-name="<?php echo htmlspecialchars($rec_goal['name']); ?>"
                                                data-description="<?php echo htmlspecialchars($rec_goal['description']); ?>"
                                                data-target="<?php echo $rec_goal['target_amount']; ?>"
                                                data-priority="<?php echo $rec_goal['priority']; ?>"
                                                data-timeline="<?php echo $rec_goal['timeline']; ?>">
                                                <i class="fas fa-plus"></i> Adopt
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
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            The system will suggest financial goals based on your income level and common financial planning principles.
                        </div>
                        
                        <p>Current monthly income: <strong>$<?php echo number_format($monthly_income, 2); ?></strong></p>
                        
                        <p>Recommendations will include:</p>
                        <ul>
                            <li>Emergency fund target</li>
                            <li>Retirement savings goal</li>
                            <li>Debt reduction plan (if applicable)</li>
                            <li>Major purchase savings (home, education, etc.)</li>
                            <li>And more based on your financial situation</li>
                        </ul>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-magic"></i> Generate Recommendations
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php
// JavaScript for goals page
$page_scripts = "
// Handle filter buttons
document.getElementById('viewAll').addEventListener('click', function() {
    toggleGoalVisibility('all');
    setActiveButton(this);
});

document.getElementById('viewInProgress').addEventListener('click', function() {
    toggleGoalVisibility('in-progress');
    setActiveButton(this);
});

document.getElementById('viewCompleted').addEventListener('click', function() {
    toggleGoalVisibility('completed');
    setActiveButton(this);
});

function toggleGoalVisibility(status) {
    const allGoals = document.querySelectorAll('.goal-item');
    
    allGoals.forEach(goal => {
        if (status === 'all') {
            goal.style.display = '';
        } else if (status === 'in-progress' && !goal.classList.contains('completed-goal')) {
            goal.style.display = '';
        } else if (status === 'completed' && goal.classList.contains('completed-goal')) {
            goal.style.display = '';
        } else {
            goal.style.display = 'none';
        }
    });
}

function setActiveButton(button) {
    document.querySelectorAll('.btn-group .btn').forEach(btn => {
        btn.classList.remove('active');
    });
    button.classList.add('active');
}

// Handle edit goal button
document.querySelectorAll('.edit-goal').forEach(button => {
    button.addEventListener('click', function() {
        const goalId = this.getAttribute('data-goal-id');
        
        // Show loading spinner
        showSpinner(document.querySelector('#editGoalModal .modal-body'));
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('editGoalModal'));
        modal.show();
        
        // Fetch goal data
        fetch('/goals?action=get_goal&goal_id=' + goalId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Populate form fields
                    document.getElementById('edit_goal_id').value = data.goal.goal_id;
                    document.getElementById('edit_name').value = data.goal.name;
                    document.getElementById('edit_description').value = data.goal.description || '';
                    document.getElementById('edit_target_amount').value = data.goal.target_amount;
                    document.getElementById('edit_current_amount').value = data.goal.current_amount;
                    document.getElementById('edit_start_date').value = data.goal.start_date;
                    document.getElementById('edit_target_date').value = data.goal.target_date;
                    document.getElementById('edit_priority').value = data.goal.priority;
                    document.getElementById('edit_status').value = data.goal.status;
                    
                    // Update calculator
                    updateEditGoalCalculator();
                    
                    // Remove spinner
                    hideSpinner(document.querySelector('#editGoalModal .modal-body'));
                } else {
                    // Show error
                    alert('Failed to load goal data: ' + data.message);
                    modal.hide();
                }
            })
            .catch(error => {
                console.error('Error fetching goal data:', error);
                alert('An error occurred while loading goal data.');
                modal.hide();
            });
    });
});

// Handle update progress button
document.querySelectorAll('.update-progress').forEach(button => {
    button.addEventListener('click', function() {
        const goalId = this.getAttribute('data-goal-id');
        
        // Show loading spinner
        showSpinner(document.querySelector('#updateProgressModal .modal-body'));
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('updateProgressModal'));
        modal.show();
        
        // Fetch goal data
        fetch('/goals?action=get_goal&goal_id=' + goalId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Populate form fields
                    document.getElementById('progress_goal_id').value = data.goal.goal_id;
                    document.getElementById('progress_goal_name').textContent = data.goal.name;
                    document.getElementById('progress_current_amount').textContent = '$' + formatNumber(data.goal.current_amount);
                    document.getElementById('progress_target_amount').textContent = '$' + formatNumber(data.goal.target_amount);
                    
                    // Update progress bar
                    const progressBar = document.getElementById('progress_bar');
                    const progress = (data.goal.current_amount / data.goal.target_amount) * 100;
                    progressBar.style.width = Math.min(100, progress) + '%';
                    progressBar.textContent = progress.toFixed(0) + '%';
                    
                    // Clear amount input
                    document.getElementById('progress_amount').value = '';
                    
                    // Hide completion alert
                    document.getElementById('progress_completion_alert').classList.add('d-none');
                    
                    // Add event listener to amount input
                    const amountInput = document.getElementById('progress_amount');
                    amountInput.addEventListener('input', function() {
                        const amount = parseFloat(this.value) || 0;
                        const currentAmount = parseFloat(data.goal.current_amount);
                        const targetAmount = parseFloat(data.goal.target_amount);
                        
                        // Check if this contribution would complete the goal
                        if (currentAmount + amount >= targetAmount) {
                            document.getElementById('progress_completion_alert').classList.remove('d-none');
                        } else {
                            document.getElementById('progress_completion_alert').classList.add('d-none');
                        }
                    });
                    
                    // Remove spinner
                    hideSpinner(document.querySelector('#updateProgressModal .modal-body'));
                } else {
                    // Show error
                    alert('Failed to load goal data: ' + data.message);
                    modal.hide();
                }
            })
            .catch(error => {
                console.error('Error fetching goal data:', error);
                alert('An error occurred while loading goal data.');
                modal.hide();
            });
    });
});

// Handle delete goal button
document.querySelectorAll('.delete-goal').forEach(button => {
    button.addEventListener('click', function() {
        const goalId = this.getAttribute('data-goal-id');
        const goalName = this.closest('.card-header').querySelector('h6').textContent.trim();
        
        document.getElementById('delete_goal_id').value = goalId;
        document.getElementById('delete_goal_name').textContent = goalName;
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('deleteGoalModal'));
        modal.show();
    });
});

// Handle adopt recommended goal
document.querySelectorAll('.adopt-goal').forEach(button => {
    button.addEventListener('click', function() {
        const name = this.getAttribute('data-name');
        const description = this.getAttribute('data-description');
        const targetAmount = this.getAttribute('data-target');
        const priority = this.getAttribute('data-priority');
        const timeline = this.getAttribute('data-timeline');
        
        // Calculate target date based on timeline
        const targetDate = new Date();
        if (timeline.includes('month')) {
            const months = parseInt(timeline);
            targetDate.setMonth(targetDate.getMonth() + months);
        } else if (timeline.includes('year')) {
            const years = parseInt(timeline);
            targetDate.setFullYear(targetDate.getFullYear() + years);
        } else {
            targetDate.setFullYear(targetDate.getFullYear() + 1); // Default to 1 year
        }
        
        // Populate add goal form
        document.getElementById('name').value = name;
        document.getElementById('description').value = description;
        document.getElementById('target_amount').value = targetAmount;
        document.getElementById('current_amount').value = 0;
        document.getElementById('start_date').value = new Date().toISOString().split('T')[0];
        document.getElementById('target_date').value = targetDate.toISOString().split('T')[0];
        document.getElementById('priority').value = priority;
        
        // Close recommend modal and open add goal modal
        bootstrap.Modal.getInstance(document.getElementById('recommendGoalsModal')).hide();
        const addModal = new bootstrap.Modal(document.getElementById('addGoalModal'));
        addModal.show();
        
        // Update calculator
        updateGoalCalculator();
    });
});

// Goal calculator for add form
const targetAmountInput = document.getElementById('target_amount');
const currentAmountInput = document.getElementById('current_amount');
const startDateInput = document.getElementById('start_date');
const targetDateInput = document.getElementById('target_date');
const calculator = document.getElementById('goalCalculator');
const monthlyContribution = document.getElementById('monthlyContribution');
const timeToGoal = document.getElementById('timeToGoal');

function updateGoalCalculator() {
    const targetAmount = parseFloat(targetAmountInput.value) || 0;
    const currentAmount = parseFloat(currentAmountInput.value) || 0;
    const startDate = new Date(startDateInput.value);
    const targetDate = new Date(targetDateInput.value);
    
    // Check if we have valid values
    if (targetAmount <= 0 || isNaN(startDate.getTime()) || isNaN(targetDate.getTime())) {
        calculator.classList.add('d-none');
        return;
    }
    
    // Show calculator
    calculator.classList.remove('d-none');
    
    // Calculate months between dates
    const monthsDiff = (targetDate.getFullYear() - startDate.getFullYear()) * 12 + 
                      (targetDate.getMonth() - startDate.getMonth());
    
    // Calculate monthly contribution
    const remainingAmount = targetAmount - currentAmount;
    const monthly = monthsDiff > 0 ? remainingAmount / monthsDiff : remainingAmount;
    
    // Calculate time to goal at a fixed monthly contribution
    const fixedMonthly = 100; // Example fixed monthly contribution
    const timeMonths = remainingAmount > 0 ? Math.ceil(remainingAmount / fixedMonthly) : 0;
    
    // Update calculator display
    monthlyContribution.textContent = '$' + formatNumber(monthly);
    timeToGoal.textContent = monthsDiff + ' months';
}

// Add event listeners to calculator inputs
if (targetAmountInput && currentAmountInput && startDateInput && targetDateInput) {
    targetAmountInput.addEventListener('input', updateGoalCalculator);
    currentAmountInput.addEventListener('input', updateGoalCalculator);
    startDateInput.addEventListener('input', updateGoalCalculator);
    targetDateInput.addEventListener('input', updateGoalCalculator);
}

// Goal calculator for edit form
const editTargetAmountInput = document.getElementById('edit_target_amount');
const editCurrentAmountInput = document.getElementById('edit_current_amount');
const editStartDateInput = document.getElementById('edit_start_date');
const editTargetDateInput = document.getElementById('edit_target_date');
const editCalculator = document.getElementById('editGoalCalculator');
const editMonthlyContribution = document.getElementById('editMonthlyContribution');
const editTimeToGoal = document.getElementById('editTimeToGoal');

function updateEditGoalCalculator() {
    const targetAmount = parseFloat(editTargetAmountInput.value) || 0;
    const currentAmount = parseFloat(editCurrentAmountInput.value) || 0;
    const startDate = new Date(editStartDateInput.value);
    const targetDate = new Date(editTargetDateInput.value);
    
    // Check if we have valid values
    if (targetAmount <= 0 || isNaN(startDate.getTime()) || isNaN(targetDate.getTime())) {
        editCalculator.classList.add('d-none');
        return;
    }
    
    // Show calculator
    editCalculator.classList.remove('d-none');
    
    // Calculate months between dates
    const monthsDiff = (targetDate.getFullYear() - startDate.getFullYear()) * 12 + 
                      (targetDate.getMonth() - startDate.getMonth());
    
    // Calculate monthly contribution
    const remainingAmount = targetAmount - currentAmount;
    const monthly = monthsDiff > 0 ? remainingAmount / monthsDiff : remainingAmount;
    
    // Update calculator display
    editMonthlyContribution.textContent = '$' + formatNumber(monthly);
    editTimeToGoal.textContent = monthsDiff + ' months';
}

// Add event listeners to edit calculator inputs
if (editTargetAmountInput && editCurrentAmountInput && editStartDateInput && editTargetDateInput) {
    editTargetAmountInput.addEventListener('input', updateEditGoalCalculator);
    editCurrentAmountInput.addEventListener('input', updateEditGoalCalculator);
    editStartDateInput.addEventListener('input', updateEditGoalCalculator);
    editTargetDateInput.addEventListener('input', updateEditGoalCalculator);
}

// Format number with commas and 2 decimal places
function formatNumber(num) {
    return Number(num).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}
";

// Include footer
require_once 'includes/footer.php';
?>