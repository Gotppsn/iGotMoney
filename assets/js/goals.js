/**
 * iGotMoney - Goals JavaScript
 * Handles functionality for the financial goals page
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize goal visualization
    initializeGoalVisualization();
    
    // Initialize goal date validation
    initializeDateValidation();
    
    // Initialize progress tracking
    initializeProgressTracking();
    
    // Initialize form validation
    initializeFormValidation();
});

/**
 * Initialize goal visualization
 * Enhances the visual representation of financial goals
 */
function initializeGoalVisualization() {
    // Add progress visualizations to goal cards
    const goalItems = document.querySelectorAll('.goal-item');
    
    goalItems.forEach(goalItem => {
        enhanceGoalVisualization(goalItem);
    });
}

/**
 * Enhance goal visualization
 * @param {HTMLElement} goalItem - The goal item element
 */
function enhanceGoalVisualization(goalItem) {
    // Find progress elements
    const progressBar = goalItem.querySelector('.progress-bar');
    if (!progressBar) return;
    
    const progressValue = parseFloat(progressBar.getAttribute('aria-valuenow')) || 0;
    const isCompleted = goalItem.classList.contains('completed-goal');
    
    // Add milestone markers to progress bar
    const progress = goalItem.querySelector('.progress');
    
    if (progress) {
        // Remove any existing milestone markers
        progress.querySelectorAll('.milestone-marker').forEach(marker => marker.remove());
        
        // Add milestone markers (25%, 50%, 75%)
        const milestones = [25, 50, 75];
        
        milestones.forEach(milestone => {
            const marker = document.createElement('div');
            marker.className = 'milestone-marker ' + (milestone < progressValue || isCompleted ? 'milestone-reached' : '');
            marker.style.left = `${milestone}%`;
            marker.setAttribute('data-bs-toggle', 'tooltip');
            marker.setAttribute('title', `${milestone}% ${milestone < progressValue || isCompleted ? 'Complete' : 'Milestone'}`);
            progress.appendChild(marker);
            
            // Initialize tooltip
            try {
                new bootstrap.Tooltip(marker);
            } catch (e) {
                console.error('Error initializing tooltip:', e);
            }
        });
        
        // Add styles for milestone markers
        if (!document.getElementById('milestone-styles')) {
            const styleEl = document.createElement('style');
            styleEl.id = 'milestone-styles';
            styleEl.textContent = `
                .progress {
                    position: relative;
                }
                .milestone-marker {
                    position: absolute;
                    top: 0;
                    width: 2px;
                    height: 100%;
                    background-color: rgba(0, 0, 0, 0.2);
                    z-index: 1;
                }
                .milestone-marker.milestone-reached {
                    background-color: rgba(255, 255, 255, 0.7);
                }
            `;
            document.head.appendChild(styleEl);
        }
    }
}

/**
 * Initialize date validation
 * Ensures target date is after start date
 */
function initializeDateValidation() {
    const startDateInputs = document.querySelectorAll('#start_date, #edit_start_date');
    const targetDateInputs = document.querySelectorAll('#target_date, #edit_target_date');
    
    startDateInputs.forEach((input, index) => {
        input.addEventListener('change', function() {
            validateDates(this, targetDateInputs[index]);
        });
    });
    
    targetDateInputs.forEach((input, index) => {
        input.addEventListener('change', function() {
            validateDates(startDateInputs[index], this);
        });
    });
}

/**
 * Validate start and target dates
 * @param {HTMLElement} startDateInput - The start date input element
 * @param {HTMLElement} targetDateInput - The target date input element
 */
function validateDates(startDateInput, targetDateInput) {
    if (!startDateInput || !targetDateInput || !targetDateInput.value) return;
    
    const startDate = new Date(startDateInput.value);
    const targetDate = new Date(targetDateInput.value);
    
    if (targetDate <= startDate) {
        // Show validation message
        targetDateInput.setCustomValidity('Target date must be after start date.');
        targetDateInput.reportValidity();
        
        // Clear the input
        setTimeout(() => {
            targetDateInput.value = '';
            targetDateInput.setCustomValidity('');
        }, 1500);
    } else {
        targetDateInput.setCustomValidity('');
    }
}

/**
 * Initialize progress tracking
 * Adds functionality for tracking progress toward goals
 */
function initializeProgressTracking() {
    // Add progress visuals to cards
    addProgressVisuals();
    
    // Add progress projection to cards
    addProgressProjection();
    
    // Handle update progress button clicks
    document.querySelectorAll('.update-progress').forEach(button => {
        button.addEventListener('click', function() {
            const goalId = this.getAttribute('data-goal-id');
            document.getElementById('progress_goal_id').value = goalId;
            
            // Fetch goal data with error handling
            fetch(`/goals?action=get_goal&goal_id=${goalId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Populate form fields
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
                        const completionAlert = document.getElementById('progress_completion_alert');
                        if (completionAlert) {
                            completionAlert.classList.add('d-none');
                        }
                        
                        // Add event listener to amount input
                        const amountInput = document.getElementById('progress_amount');
                        amountInput.addEventListener('input', function() {
                            const amount = parseFloat(this.value) || 0;
                            const currentAmount = parseFloat(data.goal.current_amount);
                            const targetAmount = parseFloat(data.goal.target_amount);
                            
                            // Check if this contribution would complete the goal
                            if (completionAlert) {
                                if (currentAmount + amount >= targetAmount) {
                                    completionAlert.classList.remove('d-none');
                                } else {
                                    completionAlert.classList.add('d-none');
                                }
                            }
                        });
                    } else {
                        showNotification('Failed to load goal data: ' + (data.message || 'Unknown error'), 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error fetching goal data:', error);
                    showNotification('An error occurred while loading goal data.', 'danger');
                });
        });
    });
    
    // Handle progress form submission
    const progressForm = document.querySelector('#updateProgressModal form');
    if (progressForm) {
        progressForm.addEventListener('submit', function(e) {
            const amountInput = document.getElementById('progress_amount');
            const amount = parseFloat(amountInput.value);
            
            if (isNaN(amount) || amount <= 0) {
                e.preventDefault();
                amountInput.classList.add('is-invalid');
                showNotification('Please enter a valid positive amount', 'warning');
                return false;
            }
            
            amountInput.classList.remove('is-invalid');
            return true;
        });
    }
}

/**
 * Add progress visuals to goal cards
 */
function addProgressVisuals() {
    // Find goal cards where progress is tracked
    const goalCards = document.querySelectorAll('.goal-item:not(.completed-goal)');
    
    goalCards.forEach(card => {
        // Get progress elements
        const progressBar = card.querySelector('.progress-bar');
        if (!progressBar) return;
        
        const progressText = progressBar.textContent.trim();
        const progressValue = parseFloat(progressText) || 0;
        
        // Get target elements
        const amountText = card.querySelector('.d-flex.justify-content-between span:first-child');
        if (!amountText) return;
        
        // Extract current and target amounts
        const amountMatch = amountText.textContent.match(/\$([0-9,]+\.[0-9]+) of \$([0-9,]+\.[0-9]+)/);
        if (!amountMatch) return;
        
        const currentAmount = parseFloat(amountMatch[1].replace(/,/g, ''));
        const targetAmount = parseFloat(amountMatch[2].replace(/,/g, ''));
        
        // Find time information
        const timeInfo = card.querySelector('.col-md-4 .card-body');
        if (!timeInfo) return;
        
        // Add trend indicator if not already present
        if (!card.querySelector('.trend-indicator')) {
            // Extract start and target dates
            const startDateText = timeInfo.querySelector('div:nth-child(1)').textContent;
            const targetDateText = timeInfo.querySelector('div:nth-child(2)').textContent;
            
            const startDateMatch = startDateText.match(/Started: ([A-Za-z]+ [0-9]+, [0-9]+)/);
            const targetDateMatch = targetDateText.match(/Target Date: ([A-Za-z]+ [0-9]+, [0-9]+)/);
            
            if (!startDateMatch || !targetDateMatch) return;
            
            const startDate = new Date(startDateMatch[1]);
            const targetDate = new Date(targetDateMatch[1]);
            const today = new Date();
            
            // Calculate time progress
            const totalTime = targetDate - startDate;
            const elapsedTime = today - startDate;
            const timeProgress = totalTime > 0 ? (elapsedTime / totalTime) * 100 : 100;
            
            // Compare money progress to time progress
            const trendsDiv = document.createElement('div');
            trendsDiv.className = 'trend-indicator mt-3';
            
            let trendHTML = '';
            
            if (progressValue >= timeProgress) {
                // Ahead or on track
                trendHTML = `
                    <div class="alert alert-success mb-0">
                        <h6 class="alert-heading mb-1">
                            <i class="fas fa-chart-line me-1"></i> On Track
                        </h6>
                        <div class="d-flex align-items-center small">
                            <div class="me-2 text-nowrap">Time: ${Math.round(timeProgress)}%</div>
                            <div class="progress flex-grow-1" style="height: 4px;">
                                <div class="progress-bar" style="width: ${Math.min(100, timeProgress)}%"></div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center small">
                            <div class="me-2 text-nowrap">Goal: ${Math.round(progressValue)}%</div>
                            <div class="progress flex-grow-1" style="height: 4px;">
                                <div class="progress-bar bg-success" style="width: ${Math.min(100, progressValue)}%"></div>
                            </div>
                        </div>
                    </div>
                `;
            } else if (progressValue >= timeProgress * 0.7) {
                // Slightly behind
                trendHTML = `
                    <div class="alert alert-warning mb-0">
                        <h6 class="alert-heading mb-1">
                            <i class="fas fa-chart-line me-1"></i> Slightly Behind
                        </h6>
                        <div class="d-flex align-items-center small">
                            <div class="me-2 text-nowrap">Time: ${Math.round(timeProgress)}%</div>
                            <div class="progress flex-grow-1" style="height: 4px;">
                                <div class="progress-bar" style="width: ${Math.min(100, timeProgress)}%"></div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center small">
                            <div class="me-2 text-nowrap">Goal: ${Math.round(progressValue)}%</div>
                            <div class="progress flex-grow-1" style="height: 4px;">
                                <div class="progress-bar bg-warning" style="width: ${Math.min(100, progressValue)}%"></div>
                            </div>
                        </div>
                    </div>
                `;
            } else {
                // Significantly behind
                trendHTML = `
                    <div class="alert alert-danger mb-0">
                        <h6 class="alert-heading mb-1">
                            <i class="fas fa-chart-line me-1"></i> Needs Attention
                        </h6>
                        <div class="d-flex align-items-center small">
                            <div class="me-2 text-nowrap">Time: ${Math.round(timeProgress)}%</div>
                            <div class="progress flex-grow-1" style="height: 4px;">
                                <div class="progress-bar" style="width: ${Math.min(100, timeProgress)}%"></div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center small">
                            <div class="me-2 text-nowrap">Goal: ${Math.round(progressValue)}%</div>
                            <div class="progress flex-grow-1" style="height: 4px;">
                                <div class="progress-bar bg-danger" style="width: ${Math.min(100, progressValue)}%"></div>
                            </div>
                        </div>
                    </div>
                `;
            }
            
            trendsDiv.innerHTML = trendHTML;
            
            // Add to the card
            const cardBody = timeInfo.parentNode;
            cardBody.appendChild(trendsDiv);
        }
    });
}

/**
 * Add progress projection to goal cards
 */
function addProgressProjection() {
    // Find goal cards where progress is being tracked
    const goalCards = document.querySelectorAll('.goal-item:not(.completed-goal)');
    
    goalCards.forEach(card => {
        // Get amount elements
        const amountText = card.querySelector('.d-flex.justify-content-between span:first-child');
        if (!amountText) return;
        
        // Extract current and target amounts
        const amountMatch = amountText.textContent.match(/\$([0-9,]+\.[0-9]+) of \$([0-9,]+\.[0-9]+)/);
        if (!amountMatch) return;
        
        const currentAmount = parseFloat(amountMatch[1].replace(/,/g, ''));
        const targetAmount = parseFloat(amountMatch[2].replace(/,/g, ''));
        
        // Find time information
        const timeInfo = card.querySelector('.col-md-4 .card-body');
        if (!timeInfo) return;
        
        // Add projection if not already present
        if (!card.querySelector('.projection-info')) {
            // Extract start and target dates
            const startDateText = timeInfo.querySelector('div:nth-child(1)').textContent;
            const targetDateText = timeInfo.querySelector('div:nth-child(2)').textContent;
            
            const startDateMatch = startDateText.match(/Started: ([A-Za-z]+ [0-9]+, [0-9]+)/);
            const targetDateMatch = targetDateText.match(/Target Date: ([A-Za-z]+ [0-9]+, [0-9]+)/);
            
            if (!startDateMatch || !targetDateMatch) return;
            
            const startDate = new Date(startDateMatch[1]);
            const targetDate = new Date(targetDateMatch[1]);
            const today = new Date();
            
            // Calculate current rate of progress
            const daysSinceStart = Math.max(1, Math.round((today - startDate) / (1000 * 60 * 60 * 24)));
            const dailyProgress = currentAmount / daysSinceStart;
            
            // Calculate days to target date
            const daysUntilTarget = Math.max(0, Math.round((targetDate - today) / (1000 * 60 * 60 * 24)));
            
            // Calculate projected final amount
            const projectedAmount = currentAmount + (dailyProgress * daysUntilTarget);
            const projectedPercentage = (projectedAmount / targetAmount) * 100;
            
            // Calculate estimated completion date
            const remainingAmount = targetAmount - currentAmount;
            const daysToComplete = dailyProgress > 0 ? Math.ceil(remainingAmount / dailyProgress) : Infinity;
            const completionDate = new Date(today);
            completionDate.setDate(completionDate.getDate() + daysToComplete);
            
            // Create projection info
            const projectionDiv = document.createElement('div');
            projectionDiv.className = 'projection-info mt-3';
            
            let projectionClass = '';
            
            if (projectedPercentage >= 100) {
                projectionClass = 'success';
            } else if (projectedPercentage >= 90) {
                projectionClass = 'info';
            } else if (projectedPercentage >= 75) {
                projectionClass = 'warning';
            } else {
                projectionClass = 'danger';
            }
            
            projectionDiv.innerHTML = `
                <div class="alert alert-${projectionClass} mb-0">
                    <h6 class="alert-heading mb-1">
                        <i class="fas fa-chart-pie me-1"></i> Projection
                    </h6>
                    <div class="small">
                        <div>
                            <strong>At current rate:</strong> 
                            $${formatNumber(projectedAmount)} (${Math.min(100, Math.round(projectedPercentage))}%)
                        </div>
                        <div>
                            <strong>Estimated completion:</strong> 
                            ${isFinite(daysToComplete) ? formatDate(completionDate) : 'Never'}
                        </div>
                    </div>
                </div>
            `;
            
            // Add to the card
            const cardBody = timeInfo.parentNode;
            
            // Add after trend indicator if it exists
            const trendIndicator = cardBody.querySelector('.trend-indicator');
            if (trendIndicator) {
                trendIndicator.insertAdjacentElement('afterend', projectionDiv);
            } else {
                cardBody.appendChild(projectionDiv);
            }
        }
    });
}

/**
 * Initialize form validation
 */
function initializeFormValidation() {
    // Add form validation
    const addForm = document.getElementById('addBudgetForm');
    if (addForm) {
        addForm.addEventListener('submit', function(event) {
            if (!this.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            this.classList.add('was-validated');
        });
    }
    
    // Initialize goal calculator for add form
    const targetAmountInput = document.getElementById('target_amount');
    const currentAmountInput = document.getElementById('current_amount');
    const startDateInput = document.getElementById('start_date');
    const targetDateInput = document.getElementById('target_date');
    const calculator = document.getElementById('goalCalculator');
    const monthlyContribution = document.getElementById('monthlyContribution');
    const timeToGoal = document.getElementById('timeToGoal');
    
    if (targetAmountInput && currentAmountInput && startDateInput && targetDateInput && calculator && monthlyContribution && timeToGoal) {
        const updateCalculator = function() {
            const targetAmount = parseFloat(targetAmountInput.value) || 0;
            const currentAmount = parseFloat(currentAmountInput.value) || 0;
            const startDate = new Date(startDateInput.value);
            const targetDate = new Date(targetDateInput.value);
            
            // Check if we have valid values
            if (targetAmount <= 0 || isNaN(startDate.getTime()) || isNaN(targetDate.getTime()) || startDate >= targetDate) {
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
            
            // Update calculator display
            monthlyContribution.textContent = '$' + formatNumber(monthly);
            timeToGoal.textContent = monthsDiff + ' months';
        };
        
        targetAmountInput.addEventListener('input', updateCalculator);
        currentAmountInput.addEventListener('input', updateCalculator);
        startDateInput.addEventListener('change', updateCalculator);
        targetDateInput.addEventListener('change', updateCalculator);
    }
    
    // Initialize goal calculator for edit form
    const editTargetAmountInput = document.getElementById('edit_target_amount');
    const editCurrentAmountInput = document.getElementById('edit_current_amount');
    const editStartDateInput = document.getElementById('edit_start_date');
    const editTargetDateInput = document.getElementById('edit_target_date');
    const editCalculator = document.getElementById('editGoalCalculator');
    const editMonthlyContribution = document.getElementById('editMonthlyContribution');
    const editTimeToGoal = document.getElementById('editTimeToGoal');
    
    if (editTargetAmountInput && editCurrentAmountInput && editStartDateInput && editTargetDateInput && editCalculator && editMonthlyContribution && editTimeToGoal) {
        const updateEditCalculator = function() {
            const targetAmount = parseFloat(editTargetAmountInput.value) || 0;
            const currentAmount = parseFloat(editCurrentAmountInput.value) || 0;
            const startDate = new Date(editStartDateInput.value);
            const targetDate = new Date(editTargetDateInput.value);
            
            // Check if we have valid values
            if (targetAmount <= 0 || isNaN(startDate.getTime()) || isNaN(targetDate.getTime()) || startDate >= targetDate) {
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
        };
        
        editTargetAmountInput.addEventListener('input', updateEditCalculator);
        editCurrentAmountInput.addEventListener('input', updateEditCalculator);
        editStartDateInput.addEventListener('change', updateEditCalculator);
        editTargetDateInput.addEventListener('change', updateEditCalculator);
    }
    
    // Handle adopt recommended goal
    document.querySelectorAll('.adopt-recommendation').forEach(button => {
        button.addEventListener('click', function() {
            const name = this.getAttribute('data-name');
            const description = this.getAttribute('data-description');
            const targetAmount = this.getAttribute('data-amount');
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
            if (typeof updateCalculator === 'function') {
                updateCalculator();
            }
        });
    });
}

/**
 * Format number with commas and 2 decimal places
 * @param {number} num - Number to format
 * @returns {string} - Formatted number string
 */
function formatNumber(num) {
    return num.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

/**
 * Format date to MMM D, YYYY
 * @param {Date} date - Date to format
 * @returns {string} - Formatted date string
 */
function formatDate(date) {
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    return `${months[date.getMonth()]} ${date.getDate()}, ${date.getFullYear()}`;
}

/**
 * Show notification
 * @param {string} message - Message to display
 * @param {string} type - Message type (success, info, warning, danger)
 * @param {number} duration - Duration in milliseconds
 */
function showNotification(message, type = 'info', duration = 3000) {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show notification-toast`;
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    // Style the notification
    notification.style.position = 'fixed';
    notification.style.top = '1rem';
    notification.style.right = '1rem';
    notification.style.zIndex = '1050';
    notification.style.minWidth = '250px';
    notification.style.boxShadow = '0 0.5rem 1rem rgba(0, 0, 0, 0.15)';
    
    // Add to document
    document.body.appendChild(notification);
    
    // Auto-dismiss after duration
    setTimeout(function() {
        notification.classList.remove('show');
        setTimeout(function() {
            notification.remove();
        }, 150);
    }, duration);
}