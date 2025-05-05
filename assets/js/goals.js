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
        // Add milestone markers (25%, 50%, 75%)
        const milestones = [25, 50, 75];
        
        milestones.forEach(milestone => {
            if (milestone < progressValue || isCompleted) {
                const marker = document.createElement('div');
                marker.className = 'milestone-marker milestone-reached';
                marker.style.left = `${milestone}%`;
                marker.setAttribute('data-bs-toggle', 'tooltip');
                marker.setAttribute('title', `${milestone}% Complete`);
                progress.appendChild(marker);
                
                // Initialize tooltip
                new bootstrap.Tooltip(marker);
            } else {
                const marker = document.createElement('div');
                marker.className = 'milestone-marker';
                marker.style.left = `${milestone}%`;
                marker.setAttribute('data-bs-toggle', 'tooltip');
                marker.setAttribute('title', `${milestone}% Milestone`);
                progress.appendChild(marker);
                
                // Initialize tooltip
                new bootstrap.Tooltip(marker);
            }
        });
        
        // Add styles for milestone markers
        const styleEl = document.createElement('style');
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
    if (!startDateInput || !targetDateInput) return;
    
    const startDate = new Date(startDateInput.value);
    const targetDate = new Date(targetDateInput.value);
    
    if (targetDate <= startDate) {
        alert('Target date must be after start date.');
        targetDateInput.value = '';
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
        const progressText = card.querySelector('.progress-bar').textContent.trim();
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
            
            let projectionHTML = '';
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
            
            projectionHTML = `
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
            
            projectionDiv.innerHTML = projectionHTML;
            
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
 * Format date to MMM D, YYYY
 * @param {Date} date - Date to format
 * @returns {string} - Formatted date string
 */
function formatDate(date) {
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    return `${months[date.getMonth()]} ${date.getDate()}, ${date.getFullYear()}`;
}

/**
 * Format number with commas and 2 decimal places
 * @param {number} num - Number to format
 * @returns {string} - Formatted number string
 */
function formatNumber(num) {
    return num.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}

/**
 * Calculate the monthly contribution needed to reach a goal
 * @param {number} targetAmount - Target amount
 * @param {number} currentAmount - Current amount
 * @param {Date} startDate - Start date
 * @param {Date} targetDate - Target date
 * @returns {number} - Monthly contribution amount
 */
function calculateMonthlyContribution(targetAmount, currentAmount, startDate, targetDate) {
    // Calculate months between dates
    const monthsDiff = (targetDate.getFullYear() - startDate.getFullYear()) * 12 + 
                      (targetDate.getMonth() - startDate.getMonth());
    
    // Calculate monthly contribution
    const remainingAmount = targetAmount - currentAmount;
    return monthsDiff > 0 ? remainingAmount / monthsDiff : remainingAmount;
}