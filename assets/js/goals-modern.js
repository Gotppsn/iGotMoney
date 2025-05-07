/**
 * iGotMoney - Modern Goals Page JavaScript
 * Enhanced goals functionality with modern interactions
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize components
    initializeGoalsPage();
    
    // Set up event listeners
    setupEventListeners();
    
    // Initialize form validation
    initializeFormValidation();
    
    // Animate progress bars
    animateProgressBars();
});

/**
 * Initialize goals page components
 */
function initializeGoalsPage() {
    // Set initial filter to "All"
    document.getElementById('filterAll').classList.add('active');
    
    // Initialize tooltips
    const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    if (tooltips.length) {
        tooltips.forEach(tooltip => {
            new bootstrap.Tooltip(tooltip);
        });
    }
    
    // Initialize goal calculator
    initializeGoalCalculator();
    
    // Apply animation to cards
    animateElements();
}

/**
 * Set up event listeners for interactivity
 */
function setupEventListeners() {
    // Goal filtering
    setupFilterButtons();
    
    // Edit goal modal
    setupEditGoalButtons();
    
    // Update progress buttons
    setupUpdateProgressButtons();
    
    // Delete goal confirmation
    setupDeleteButtons();
    
    // Adopt recommended goal
    setupAdoptGoalButtons();
}

/**
 * Set up filter buttons
 */
function setupFilterButtons() {
    const filterButtons = document.querySelectorAll('.goals-filter button');
    
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all buttons
            filterButtons.forEach(btn => btn.classList.remove('active'));
            
            // Add active class to clicked button
            this.classList.add('active');
            
            // Get filter value
            const filter = this.getAttribute('data-filter');
            
            // Show/hide goals based on filter
            filterGoals(filter);
        });
    });
}

/**
 * Filter goals based on status
 * @param {string} filter - The filter to apply (all, in-progress, completed)
 */
function filterGoals(filter) {
    const goals = document.querySelectorAll('.goal-item');
    
    goals.forEach(goal => {
        if (filter === 'all') {
            goal.style.display = 'block';
        } else {
            if (goal.classList.contains(filter + '-goal')) {
                goal.style.display = 'block';
            } else {
                goal.style.display = 'none';
            }
        }
    });
    
    // Animate newly visible goals
    setTimeout(() => {
        animateElements();
    }, 100);
}

/**
 * Set up edit goal buttons
 */
function setupEditGoalButtons() {
    const editButtons = document.querySelectorAll('.edit-goal');
    const editModal = document.getElementById('editGoalModal');
    
    if (!editButtons.length || !editModal) return;
    
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const goalId = this.getAttribute('data-goal-id');
            
            // Fetch goal data via AJAX
            fetchGoalData(goalId)
                .then(goal => {
                    // Populate modal with goal data
                    populateEditModal(goal);
                    
                    // Show modal
                    const modal = new bootstrap.Modal(editModal);
                    modal.show();
                })
                .catch(error => {
                    console.error('Error fetching goal data:', error);
                    showNotification('Error fetching goal data', 'danger');
                });
        });
    });
}

/**
 * Fetch goal data via AJAX
 * @param {string|number} goalId - The ID of the goal to fetch
 * @returns {Promise} - Promise that resolves with goal data
 */
function fetchGoalData(goalId) {
    return new Promise((resolve, reject) => {
        const basePath = document.querySelector('meta[name="base-path"]').getAttribute('content');
        const url = `${basePath}/goals?action=get_goal&goal_id=${goalId}`;
        
        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    resolve(data.goal);
                } else {
                    reject(new Error(data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                reject(error);
            });
    });
}

/**
 * Populate edit modal with goal data
 * @param {Object} goal - The goal data to populate
 */
function populateEditModal(goal) {
    document.getElementById('edit_goal_id').value = goal.goal_id;
    document.getElementById('edit_name').value = goal.name;
    document.getElementById('edit_description').value = goal.description || '';
    document.getElementById('edit_target_amount').value = goal.target_amount;
    document.getElementById('edit_current_amount').value = goal.current_amount;
    document.getElementById('edit_start_date').value = goal.start_date;
    document.getElementById('edit_target_date').value = goal.target_date;
    document.getElementById('edit_priority').value = goal.priority;
    document.getElementById('edit_status').value = goal.status;
    
    // Calculate and show monthly contribution
    calculateEditGoalMetrics();
    
    // Set up event listeners for recalculation
    const recalculationFields = ['edit_target_amount', 'edit_current_amount', 'edit_start_date', 'edit_target_date'];
    recalculationFields.forEach(field => {
        document.getElementById(field).addEventListener('change', calculateEditGoalMetrics);
    });
}

/**
 * Calculate and update edit goal metrics
 */
function calculateEditGoalMetrics() {
    const targetAmount = parseFloat(document.getElementById('edit_target_amount').value) || 0;
    const currentAmount = parseFloat(document.getElementById('edit_current_amount').value) || 0;
    const startDate = new Date(document.getElementById('edit_start_date').value);
    const targetDate = new Date(document.getElementById('edit_target_date').value);
    
    if (targetAmount <= 0 || isNaN(startDate.getTime()) || isNaN(targetDate.getTime())) {
        document.getElementById('editGoalCalculator').classList.add('d-none');
        return;
    }
    
    // Calculate remaining amount
    const remainingAmount = Math.max(0, targetAmount - currentAmount);
    
    // Calculate months between dates
    const now = new Date();
    const startPoint = startDate > now ? startDate : now;
    const totalMonths = (targetDate.getFullYear() - startPoint.getFullYear()) * 12 + 
                       (targetDate.getMonth() - startPoint.getMonth());
    
    // Calculate monthly contribution
    let monthlyContribution = 0;
    if (totalMonths > 0) {
        monthlyContribution = remainingAmount / totalMonths;
    } else {
        monthlyContribution = remainingAmount;
    }
    
    // Update UI
    document.getElementById('editMonthlyContribution').textContent = '$' + monthlyContribution.toFixed(2);
    document.getElementById('editTimeToGoal').textContent = totalMonths + ' months';
    document.getElementById('editGoalCalculator').classList.remove('d-none');
}

/**
 * Set up update progress buttons
 */
function setupUpdateProgressButtons() {
    const updateButtons = document.querySelectorAll('.update-progress');
    const progressModal = document.getElementById('updateProgressModal');
    
    if (!updateButtons.length || !progressModal) return;
    
    updateButtons.forEach(button => {
        button.addEventListener('click', function() {
            const goalId = this.getAttribute('data-goal-id');
            
            // Fetch goal data via AJAX
            fetchGoalData(goalId)
                .then(goal => {
                    // Populate progress modal
                    populateProgressModal(goal);
                    
                    // Show modal
                    const modal = new bootstrap.Modal(progressModal);
                    modal.show();
                })
                .catch(error => {
                    console.error('Error fetching goal data:', error);
                    showNotification('Error fetching goal data', 'danger');
                });
        });
    });
    
    // Set up progress amount input to check for completion
    const progressAmountInput = document.getElementById('progress_amount');
    if (progressAmountInput) {
        progressAmountInput.addEventListener('input', checkProgressCompletion);
    }
}

/**
 * Populate progress update modal
 * @param {Object} goal - The goal data to populate
 */
function populateProgressModal(goal) {
    document.getElementById('progress_goal_id').value = goal.goal_id;
    document.getElementById('progress_goal_name').textContent = goal.name;
    document.getElementById('progress_current_amount').textContent = '$' + parseFloat(goal.current_amount).toFixed(2);
    document.getElementById('progress_target_amount').textContent = '$' + parseFloat(goal.target_amount).toFixed(2);
    
    // Update progress bar
    const progressBar = document.getElementById('progress_bar');
    const progressPercentage = goal.progress_percentage;
    progressBar.style.width = progressPercentage + '%';
    progressBar.setAttribute('aria-valuenow', progressPercentage);
    progressBar.textContent = progressPercentage.toFixed(0) + '%';
    
    // Set progress classes based on percentage
    progressBar.className = 'progress-bar';
    if (progressPercentage >= 100) {
        progressBar.classList.add('bg-success');
    } else if (progressPercentage >= 70) {
        progressBar.classList.add('bg-info');
    } else if (progressPercentage >= 40) {
        progressBar.classList.add('bg-primary');
    } else {
        progressBar.classList.add('bg-warning');
    }
    
    // Reset progress amount input
    const progressAmountInput = document.getElementById('progress_amount');
    if (progressAmountInput) {
        progressAmountInput.value = '';
    }
    
    // Hide completion alert
    document.getElementById('progress_completion_alert').classList.add('d-none');
}

/**
 * Check if progress update will complete the goal
 */
function checkProgressCompletion() {
    const progressAmount = parseFloat(this.value) || 0;
    const currentAmount = parseFloat(document.getElementById('progress_current_amount').textContent.replace('$', '')) || 0;
    const targetAmount = parseFloat(document.getElementById('progress_target_amount').textContent.replace('$', '')) || 0;
    
    const newTotal = currentAmount + progressAmount;
    const completionAlert = document.getElementById('progress_completion_alert');
    
    if (newTotal >= targetAmount) {
        completionAlert.classList.remove('d-none');
    } else {
        completionAlert.classList.add('d-none');
    }
}

/**
 * Set up delete goal buttons
 */
function setupDeleteButtons() {
    const deleteButtons = document.querySelectorAll('.delete-goal');
    const deleteModal = document.getElementById('deleteGoalModal');
    
    if (!deleteButtons.length || !deleteModal) return;
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const goalId = this.getAttribute('data-goal-id');
            const goalName = this.closest('.card-header').querySelector('h6').textContent.trim();
            
            // Set goal ID in delete form
            document.getElementById('delete_goal_id').value = goalId;
            
            // Set goal name in confirmation message
            document.getElementById('delete_goal_name').textContent = goalName;
            
            // Show modal
            const modal = new bootstrap.Modal(deleteModal);
            modal.show();
        });
    });
}

/**
 * Set up adopt recommended goal buttons
 */
function setupAdoptGoalButtons() {
    const adoptButtons = document.querySelectorAll('.adopt-goal');
    const addModal = document.getElementById('addGoalModal');
    
    if (!adoptButtons.length || !addModal) return;
    
    adoptButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Get goal data from button attributes
            const name = this.getAttribute('data-name');
            const description = this.getAttribute('data-description');
            const targetAmount = this.getAttribute('data-target');
            const priority = this.getAttribute('data-priority');
            
            // Calculate target date based on timeline attribute
            const timeline = this.getAttribute('data-timeline');
            let targetDate = new Date();
            
            if (timeline === 'long-term') {
                targetDate.setFullYear(targetDate.getFullYear() + 30); // 30 years for retirement
            } else if (timeline.includes('months')) {
                const months = parseInt(timeline);
                targetDate.setMonth(targetDate.getMonth() + months);
            } else {
                // Default to 1 year
                targetDate.setFullYear(targetDate.getFullYear() + 1);
            }
            
            // Format date for input
            const formattedDate = targetDate.toISOString().split('T')[0];
            
            // Populate add goal form
            document.getElementById('name').value = name;
            document.getElementById('description').value = description;
            document.getElementById('target_amount').value = targetAmount;
            document.getElementById('current_amount').value = '0';
            document.getElementById('target_date').value = formattedDate;
            document.getElementById('priority').value = priority;
            
            // Calculate monthly contribution
            calculateGoalMetrics();
            
            // Close recommend modal if open
            const recommendModal = document.getElementById('recommendGoalsModal');
            if (recommendModal) {
                const bsModal = bootstrap.Modal.getInstance(recommendModal);
                if (bsModal) {
                    bsModal.hide();
                }
            }
            
            // Show add goal modal
            const modal = new bootstrap.Modal(addModal);
            modal.show();
        });
    });
}

/**
 * Initialize goal calculator
 */
function initializeGoalCalculator() {
    // Add event listeners to recalculate metrics when inputs change
    const recalculationFields = ['target_amount', 'current_amount', 'start_date', 'target_date'];
    
    recalculationFields.forEach(field => {
        const element = document.getElementById(field);
        if (element) {
            element.addEventListener('change', calculateGoalMetrics);
        }
    });
}

/**
 * Calculate and update goal metrics
 */
function calculateGoalMetrics() {
    const targetAmount = parseFloat(document.getElementById('target_amount').value) || 0;
    const currentAmount = parseFloat(document.getElementById('current_amount').value) || 0;
    const startDate = new Date(document.getElementById('start_date').value);
    const targetDate = new Date(document.getElementById('target_date').value);
    
    if (targetAmount <= 0 || isNaN(startDate.getTime()) || isNaN(targetDate.getTime())) {
        document.getElementById('goalCalculator').classList.add('d-none');
        return;
    }
    
    // Calculate remaining amount
    const remainingAmount = Math.max(0, targetAmount - currentAmount);
    
    // Calculate months between dates
    const totalMonths = (targetDate.getFullYear() - startDate.getFullYear()) * 12 + 
                       (targetDate.getMonth() - startDate.getMonth());
    
    // Calculate monthly contribution
    let monthlyContribution = 0;
    if (totalMonths > 0) {
        monthlyContribution = remainingAmount / totalMonths;
    } else {
        monthlyContribution = remainingAmount;
    }
    
    // Update UI
    document.getElementById('monthlyContribution').textContent = '$' + monthlyContribution.toFixed(2);
    document.getElementById('timeToGoal').textContent = totalMonths + ' months';
    document.getElementById('goalCalculator').classList.remove('d-none');
}

/**
 * Initialize form validation
 */
function initializeFormValidation() {
    // Get all forms we want to apply custom validation styles to
    const forms = document.querySelectorAll('.needs-validation');
    
    // Loop over them and prevent submission
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            form.classList.add('was-validated');
        }, false);
        
        // Add custom validation for target date
        const startDateInput = form.querySelector('[name="start_date"]');
        const targetDateInput = form.querySelector('[name="target_date"]');
        
        if (startDateInput && targetDateInput) {
            targetDateInput.addEventListener('change', function() {
                validateDates(startDateInput, targetDateInput);
            });
            
            startDateInput.addEventListener('change', function() {
                validateDates(startDateInput, targetDateInput);
            });
        }
    });
}

/**
 * Validate that target date is after start date
 * @param {HTMLElement} startDateInput - Start date input element
 * @param {HTMLElement} targetDateInput - Target date input element
 */
function validateDates(startDateInput, targetDateInput) {
    const startDate = new Date(startDateInput.value);
    const targetDate = new Date(targetDateInput.value);
    
    if (targetDate <= startDate) {
        targetDateInput.setCustomValidity('Target date must be after start date');
    } else {
        targetDateInput.setCustomValidity('');
    }
}

/**
 * Animate progress bars
 */
function animateProgressBars() {
    const progressBars = document.querySelectorAll('.progress-bar');
    
    progressBars.forEach((bar, index) => {
        setTimeout(() => {
            const targetWidth = bar.getAttribute('aria-valuenow') + '%';
            bar.style.width = targetWidth;
        }, 100 + (index * 50));
    });
}

/**
 * Animate elements with fadeIn effect
 */
function animateElements() {
    const cards = document.querySelectorAll('.summary-card, .goal-card, .tip-card');
    
    cards.forEach((card, index) => {
        card.style.setProperty('--index', index);
        card.classList.add('fade-in');
    });
}

/**
 * Show notification
 * @param {string} message - Notification message
 * @param {string} type - Notification type (success, info, warning, danger)
 */
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show notification-toast`;
    notification.innerHTML = `
        <div>${message}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    // Style the notification
    notification.style.position = 'fixed';
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.maxWidth = '350px';
    notification.style.zIndex = '9999';
    notification.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
    notification.style.borderRadius = '8px';
    notification.style.transform = 'translateX(400px)';
    notification.style.transition = 'transform 0.3s ease';
    
    // Add to document
    document.body.appendChild(notification);
    
    // Show notification with animation
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 10);
    
    // Hide after 5 seconds
    setTimeout(() => {
        notification.style.transform = 'translateX(400px)';
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 5000);
}