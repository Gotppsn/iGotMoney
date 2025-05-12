/**
 * iGotMoney - Modern Goals Page JavaScript
 * Enhanced goals functionality with modern interactions and multilingual support
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('Modern Goals JS loaded');
    
    // Initialize all components
    initializeGoalsPage();
    setupEventListeners();
    initializeFormValidation();
    initializeAnimations();
    initializeSearch();
    initializeGoalMetrics();
});

// Translations object - will be populated from data attributes
const translations = {};

function initializeGoalsPage() {
    // Load translations from data attributes on the page
    loadTranslations();
    
    // Initialize tooltips
    const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltips.forEach(tooltip => {
        new bootstrap.Tooltip(tooltip);
    });
    
    // Set up initial filter state
    const filterAll = document.querySelector('.filter-btn[data-filter="all"]');
    if (filterAll) {
        filterAll.classList.add('active');
    }
    
    // Animate stat cards
    animateStatCards();
    
    // Initialize progress bars
    animateProgressBars();
}

function loadTranslations() {
    // Get the html tag to determine current language
    const html = document.querySelector('html');
    const currentLang = html ? html.getAttribute('lang') : 'en';
    
    // Try to get translations from the page
    const translationElements = document.querySelectorAll('[data-translation-key]');
    translationElements.forEach(element => {
        const key = element.getAttribute('data-translation-key');
        const value = element.getAttribute('data-translation-value');
        if (key && value) {
            translations[key] = value;
        }
    });
}

function getTranslation(key, defaultText) {
    return translations[key] || defaultText;
}

function setupEventListeners() {
    // Filter buttons
    setupFilterButtons();
    
    // Edit goal modal
    setupEditGoalButtons();
    
    // Update progress buttons
    setupUpdateProgressButtons();
    
    // Delete goal buttons
    setupDeleteButtons();
    
    // Add goal button
    setupAddGoalButton();
    
    // Recommend goals button
    setupRecommendGoalsButton();
    
    // Adopt recommended goals
    setupAdoptGoalButtons();
    
    // Goal calculator
    setupGoalCalculator();
    
    // Form submissions
    setupFormSubmissions();
}

function setupFilterButtons() {
    const filterButtons = document.querySelectorAll('.filter-btn');
    
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all buttons
            filterButtons.forEach(btn => btn.classList.remove('active'));
            
            // Add active class to clicked button
            this.classList.add('active');
            
            // Get filter value
            const filter = this.getAttribute('data-filter');
            
            // Filter goals
            filterGoals(filter);
        });
    });
}

function filterGoals(filter) {
    const goalCards = document.querySelectorAll('.goal-card');
    let visibleCount = 0;
    
    goalCards.forEach(card => {
        const status = card.getAttribute('data-status');
        
        if (filter === 'all') {
            card.style.display = 'block';
            visibleCount++;
        } else if (filter === 'in-progress' && status === 'in-progress') {
            card.style.display = 'block';
            visibleCount++;
        } else if (filter === 'completed' && status === 'completed') {
            card.style.display = 'block';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });
    
    // Show/hide empty state
    const emptyState = document.querySelector('.empty-state');
    const goalsList = document.querySelector('.goals-list');
    
    if (visibleCount === 0 && goalsList) {
        if (emptyState) {
            emptyState.style.display = 'block';
        }
        goalsList.style.display = 'none';
    } else if (goalsList) {
        if (emptyState) {
            emptyState.style.display = 'none';
        }
        goalsList.style.display = 'block';
    }
    
    // Animate visible goals
    animateVisibleGoals();
}

function setupEditGoalButtons() {
    document.addEventListener('click', function(e) {
        if (e.target.closest('.edit-goal')) {
            e.preventDefault();
            const button = e.target.closest('.edit-goal');
            const goalId = button.getAttribute('data-goal-id');
            if (goalId) {
                loadGoalForEdit(goalId);
            }
        }
    });
}

function setupUpdateProgressButtons() {
    document.addEventListener('click', function(e) {
        if (e.target.closest('.update-progress')) {
            e.preventDefault();
            const button = e.target.closest('.update-progress');
            const goalId = button.getAttribute('data-goal-id');
            if (goalId) {
                loadGoalProgress(goalId);
            }
        }
    });
}

function setupDeleteButtons() {
    document.addEventListener('click', function(e) {
        if (e.target.closest('.delete-goal')) {
            e.preventDefault();
            const button = e.target.closest('.delete-goal');
            const goalId = button.getAttribute('data-goal-id');
            const goalName = button.closest('.goal-card').querySelector('.goal-title').textContent.trim();
            
            if (goalId) {
                document.getElementById('delete_goal_id').value = goalId;
                document.getElementById('delete_goal_name').textContent = goalName;
                const deleteModal = new bootstrap.Modal(document.getElementById('deleteGoalModal'));
                deleteModal.show();
            }
        }
    });
}

function setupAddGoalButton() {
    const addGoalBtn = document.querySelector('[data-bs-target="#addGoalModal"]');
    if (addGoalBtn) {
        addGoalBtn.addEventListener('click', function() {
            resetAddGoalForm();
        });
    }
}

function setupRecommendGoalsButton() {
    const recommendBtn = document.querySelector('[data-bs-target="#recommendGoalsModal"]');
    if (recommendBtn) {
        recommendBtn.addEventListener('click', function() {
            loadRecommendations();
        });
    }
}

function setupAdoptGoalButtons() {
    document.addEventListener('click', function(e) {
        if (e.target.closest('.adopt-goal')) {
            e.preventDefault();
            const button = e.target.closest('.adopt-goal');
            
            // Get goal data from button attributes
            const name = button.getAttribute('data-name');
            const description = button.getAttribute('data-description');
            const targetAmount = button.getAttribute('data-target');
            const priority = button.getAttribute('data-priority');
            const timeline = button.getAttribute('data-timeline');
            
            // Calculate target date
            let targetDate = new Date();
            if (timeline === 'long-term') {
                targetDate.setFullYear(targetDate.getFullYear() + 30);
            } else if (timeline.includes('months')) {
                const months = parseInt(timeline);
                targetDate.setMonth(targetDate.getMonth() + months);
            } else {
                targetDate.setFullYear(targetDate.getFullYear() + 1);
            }
            
            // Populate add goal form
            document.getElementById('name').value = name;
            document.getElementById('description').value = description;
            document.getElementById('target_amount').value = targetAmount;
            document.getElementById('current_amount').value = '0';
            document.getElementById('target_date').value = targetDate.toISOString().split('T')[0];
            document.getElementById('priority').value = priority;
            
            // Calculate metrics
            calculateGoalMetrics();
            
            // Close recommendation modal
            const recommendModal = bootstrap.Modal.getInstance(document.getElementById('recommendGoalsModal'));
            if (recommendModal) {
                recommendModal.hide();
            }
            
            // Show add goal modal
            const addModal = new bootstrap.Modal(document.getElementById('addGoalModal'));
            addModal.show();
        }
    });
}

function setupGoalCalculator() {
    const fields = ['target_amount', 'current_amount', 'start_date', 'target_date'];
    
    fields.forEach(field => {
        const element = document.getElementById(field);
        if (element) {
            element.addEventListener('change', calculateGoalMetrics);
            element.addEventListener('input', calculateGoalMetrics);
        }
    });
    
    // Also setup for edit modal
    const editFields = ['edit_target_amount', 'edit_current_amount', 'edit_start_date', 'edit_target_date'];
    
    editFields.forEach(field => {
        const element = document.getElementById(field);
        if (element) {
            element.addEventListener('change', calculateEditGoalMetrics);
            element.addEventListener('input', calculateEditGoalMetrics);
        }
    });
}

function setupFormSubmissions() {
    // Add goal form
    const addGoalForm = document.querySelector('#addGoalModal form');
    if (addGoalForm) {
        addGoalForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitAddGoal(this);
        });
    }
    
    // Edit goal form
    const editGoalForm = document.querySelector('#editGoalModal form');
    if (editGoalForm) {
        editGoalForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitEditGoal(this);
        });
    }
    
    // Update progress form
    const updateProgressForm = document.querySelector('#updateProgressModal form');
    if (updateProgressForm) {
        updateProgressForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitUpdateProgress(this);
        });
    }
    
    // Delete goal form
    const deleteGoalForm = document.querySelector('#deleteGoalModal form');
    if (deleteGoalForm) {
        deleteGoalForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitDeleteGoal(this);
        });
    }
}

function loadGoalForEdit(goalId) {
    const basePath = document.querySelector('meta[name="base-path"]').getAttribute('content');
    
    fetch(`${basePath}/goals?action=get_goal&goal_id=${goalId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Populate edit form
            const goal = data.goal;
            document.getElementById('edit_goal_id').value = goal.goal_id;
            document.getElementById('edit_name').value = goal.name;
            document.getElementById('edit_description').value = goal.description || '';
            document.getElementById('edit_target_amount').value = goal.target_amount;
            document.getElementById('edit_current_amount').value = goal.current_amount;
            document.getElementById('edit_start_date').value = goal.start_date;
            document.getElementById('edit_target_date').value = goal.target_date;
            document.getElementById('edit_priority').value = goal.priority;
            document.getElementById('edit_status').value = goal.status;
            
            // Calculate metrics
            calculateEditGoalMetrics();
            
            // Show modal
            const editModal = new bootstrap.Modal(document.getElementById('editGoalModal'));
            editModal.show();
        } else {
            showNotification(data.message || getTranslation('failed_to_load_goal_data', 'Failed to load goal data'), 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification(getTranslation('error_loading_goal_data', 'An error occurred while loading goal data'), 'danger');
    });
}

function loadGoalProgress(goalId) {
    const basePath = document.querySelector('meta[name="base-path"]').getAttribute('content');
    
    fetch(`${basePath}/goals?action=get_goal&goal_id=${goalId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Populate progress modal
            const goal = data.goal;
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
            
            // Set progress bar color
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
            
            // Reset amount input
            document.getElementById('progress_amount').value = '';
            
            // Hide completion alert
            document.getElementById('progress_completion_alert').classList.add('d-none');
            
            // Setup completion check
            setupProgressCompletionCheck(goal);
            
            // Show modal
            const progressModal = new bootstrap.Modal(document.getElementById('updateProgressModal'));
            progressModal.show();
        } else {
            showNotification(data.message || getTranslation('failed_to_load_goal_data', 'Failed to load goal data'), 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification(getTranslation('error_loading_goal_data', 'An error occurred while loading goal data'), 'danger');
    });
}

function setupProgressCompletionCheck(goal) {
    const progressAmountInput = document.getElementById('progress_amount');
    const completionAlert = document.getElementById('progress_completion_alert');
    
    progressAmountInput.addEventListener('input', function() {
        const progressAmount = parseFloat(this.value) || 0;
        const currentAmount = parseFloat(goal.current_amount) || 0;
        const targetAmount = parseFloat(goal.target_amount) || 0;
        
        const newTotal = currentAmount + progressAmount;
        
        if (newTotal >= targetAmount) {
            completionAlert.classList.remove('d-none');
        } else {
            completionAlert.classList.add('d-none');
        }
    });
}

function submitAddGoal(form) {
    if (!form.checkValidity()) {
        form.classList.add('was-validated');
        return;
    }
    
    const formData = new FormData(form);
    const basePath = document.querySelector('meta[name="base-path"]').getAttribute('content');
    
    fetch(`${basePath}/goals`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            
            // Close modal
            const addModal = bootstrap.Modal.getInstance(document.getElementById('addGoalModal'));
            if (addModal) {
                addModal.hide();
            }
            
            // Reload page to show new goal
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showNotification(data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification(getTranslation('error_adding_goal', 'An error occurred while adding the goal'), 'danger');
    });
}

function submitEditGoal(form) {
    if (!form.checkValidity()) {
        form.classList.add('was-validated');
        return;
    }
    
    const formData = new FormData(form);
    const basePath = document.querySelector('meta[name="base-path"]').getAttribute('content');
    
    fetch(`${basePath}/goals`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            
            // Close modal
            const editModal = bootstrap.Modal.getInstance(document.getElementById('editGoalModal'));
            if (editModal) {
                editModal.hide();
            }
            
            // Reload page to show updated goal
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showNotification(data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification(getTranslation('error_updating_goal', 'An error occurred while updating the goal'), 'danger');
    });
}

function submitUpdateProgress(form) {
    if (!form.checkValidity()) {
        form.classList.add('was-validated');
        return;
    }
    
    const formData = new FormData(form);
    const basePath = document.querySelector('meta[name="base-path"]').getAttribute('content');
    
    fetch(`${basePath}/goals`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            
            // Close modal
            const progressModal = bootstrap.Modal.getInstance(document.getElementById('updateProgressModal'));
            if (progressModal) {
                progressModal.hide();
            }
            
            // Reload page to show updated progress
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showNotification(data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification(getTranslation('error_updating_progress', 'An error occurred while updating the progress'), 'danger');
    });
}

function submitDeleteGoal(form) {
    const formData = new FormData(form);
    const basePath = document.querySelector('meta[name="base-path"]').getAttribute('content');
    
    fetch(`${basePath}/goals`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            
            // Close modal
            const deleteModal = bootstrap.Modal.getInstance(document.getElementById('deleteGoalModal'));
            if (deleteModal) {
                deleteModal.hide();
            }
            
            // Reload page to show updated list
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showNotification(data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification(getTranslation('error_deleting_goal', 'An error occurred while deleting the goal'), 'danger');
    });
}

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
    
    // Get the translation of "months" from the UI
    const monthsText = document.getElementById('timeToGoal').getAttribute('data-months-text') || 'months';
    
    // Update UI
    document.getElementById('monthlyContribution').textContent = '$' + monthlyContribution.toFixed(2);
    document.getElementById('timeToGoal').textContent = totalMonths + ' ' + monthsText;
    document.getElementById('goalCalculator').classList.remove('d-none');
}

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
    
    // Get the translation of "months" from the UI
    const monthsText = document.getElementById('editTimeToGoal').getAttribute('data-months-text') || 'months';
    
    // Update UI
    document.getElementById('editMonthlyContribution').textContent = '$' + monthlyContribution.toFixed(2);
    document.getElementById('editTimeToGoal').textContent = totalMonths + ' ' + monthsText;
    document.getElementById('editGoalCalculator').classList.remove('d-none');
}

function resetAddGoalForm() {
    const form = document.querySelector('#addGoalModal form');
    if (form) {
        form.reset();
        form.classList.remove('was-validated');
        document.getElementById('goalCalculator').classList.add('d-none');
    }
}

function loadRecommendations() {
    const basePath = document.querySelector('meta[name="base-path"]').getAttribute('content');
    
    fetch(`${basePath}/goals`, {
        method: 'POST',
        body: new URLSearchParams({
            action: 'recommend_goals'
        }),
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/x-www-form-urlencoded'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.recommended_goals) {
            displayRecommendations(data.recommended_goals);
        } else {
            showNotification(data.message || getTranslation('failed_to_load_recommendations', 'Failed to load recommendations'), 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification(getTranslation('error_loading_recommendations', 'An error occurred while loading recommendations'), 'danger');
    });
}

function displayRecommendations(recommendations) {
    const tableBody = document.querySelector('.recommendations-table tbody');
    if (!tableBody) return;
    
    let html = '';
    recommendations.forEach(goal => {
        // Get translated "Adopt" text
        const adoptText = document.querySelector('.btn-adopt') ? 
            document.querySelector('.btn-adopt').textContent.trim() : 'Adopt';
        
        html += `
            <tr>
                <td>${escapeHtml(goal.name)}</td>
                <td>${escapeHtml(goal.description)}</td>
                <td>$${parseFloat(goal.target_amount).toFixed(2)}</td>
                <td>$${parseFloat(goal.monthly_contribution).toFixed(2)}</td>
                <td>
                    <span class="priority-badge priority-${goal.priority}">
                        ${capitalizeFirst(goal.priority)}
                    </span>
                </td>
                <td>
                    <button type="button" class="btn-adopt adopt-goal" 
                        data-name="${escapeHtml(goal.name)}"
                        data-description="${escapeHtml(goal.description)}"
                        data-target="${goal.target_amount}"
                        data-priority="${goal.priority}"
                        data-timeline="${goal.timeline}">
                        <i class="fas fa-plus"></i> ${adoptText}
                    </button>
                </td>
            </tr>
        `;
    });
    
    tableBody.innerHTML = html;
}

function initializeFormValidation() {
    const forms = document.querySelectorAll('.needs-validation');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            form.classList.add('was-validated');
        }, false);
    });
    
    // Custom validation for dates
    const startDateInputs = document.querySelectorAll('input[name="start_date"]');
    const targetDateInputs = document.querySelectorAll('input[name="target_date"]');
    
    startDateInputs.forEach((startInput, index) => {
        const targetInput = targetDateInputs[index];
        
        if (startInput && targetInput) {
            targetInput.addEventListener('change', function() {
                validateDates(startInput, targetInput);
            });
            
            startInput.addEventListener('change', function() {
                validateDates(startInput, targetInput);
            });
        }
    });
}

function validateDates(startDateInput, targetDateInput) {
    const startDate = new Date(startDateInput.value);
    const targetDate = new Date(targetDateInput.value);
    
    if (targetDate <= startDate) {
        targetDateInput.setCustomValidity(getTranslation('target_date_after_start_date', 'Target date must be after start date'));
    } else {
        targetDateInput.setCustomValidity('');
    }
}

function initializeAnimations() {
    // Initialize progress bars animation
    animateProgressBars();
    
    // Animate stat cards
    animateStatCards();
    
    // Animate goal cards
    animateVisibleGoals();
}

function animateProgressBars() {
    const progressBars = document.querySelectorAll('.progress-bar');
    
    progressBars.forEach((bar, index) => {
        setTimeout(() => {
            const targetWidth = bar.getAttribute('aria-valuenow') + '%';
            bar.style.width = targetWidth;
        }, 100 + (index * 50));
    });
}

function animateStatCards() {
    const statCards = document.querySelectorAll('.stat-card');
    
    statCards.forEach((card, index) => {
        setTimeout(() => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            
            setTimeout(() => {
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, 50);
        }, index * 100);
    });
}

function animateVisibleGoals() {
    const goalCards = document.querySelectorAll('.goal-card:not([style*="display: none"])');
    
    goalCards.forEach((card, index) => {
        setTimeout(() => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            
            setTimeout(() => {
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, 50);
        }, index * 100);
    });
}

function initializeSearch() {
    const searchInput = document.getElementById('goalSearch');
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const goalCards = document.querySelectorAll('.goal-card');
            let visibleCount = 0;
            
            goalCards.forEach(card => {
                const text = card.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    card.style.display = 'block';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });
            
            // Show/hide no results message
            const emptyState = document.querySelector('.empty-state');
            const goalsList = document.querySelector('.goals-list');
            
            if (visibleCount === 0 && goalCards.length > 0) {
                if (goalsList) goalsList.style.display = 'none';
                if (emptyState) {
                    emptyState.style.display = 'block';
                    // Try to get translated "No matching goals" text
                    const noMatchingGoalsText = getTranslation('no_matching_goals', 'No matching goals found');
                    const tryAdjustingText = getTranslation('try_adjusting_search', 'Try adjusting your search term');
                    
                    emptyState.querySelector('h3').textContent = noMatchingGoalsText;
                    emptyState.querySelector('p').textContent = tryAdjustingText;
                }
            } else {
                if (goalsList) goalsList.style.display = 'block';
                if (emptyState && goalCards.length > 0) emptyState.style.display = 'none';
            }
        });
    }
}

function initializeGoalMetrics() {
    // Initialize goal metric calculations for existing goals
    const goalCards = document.querySelectorAll('.goal-card');
    
    goalCards.forEach(card => {
        const progress = card.querySelector('.progress-bar');
        if (progress) {
            const value = parseFloat(progress.getAttribute('aria-valuenow'));
            updateProgressIndicator(progress, value);
        }
    });
}

function updateProgressIndicator(progressBar, value) {
    // Update progress bar color based on value
    progressBar.className = 'progress-bar';
    
    if (value >= 100) {
        progressBar.classList.add('bg-success');
    } else if (value >= 70) {
        progressBar.classList.add('bg-info');
    } else if (value >= 40) {
        progressBar.classList.add('bg-primary');
    } else {
        progressBar.classList.add('bg-warning');
    }
}

function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    
    // Set styles
    notification.style.position = 'fixed';
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.padding = '1rem 1.5rem';
    notification.style.borderRadius = '0.5rem';
    notification.style.color = 'white';
    notification.style.zIndex = '9999';
    notification.style.minWidth = '300px';
    notification.style.maxWidth = '500px';
    notification.style.boxShadow = '0 4px 6px rgba(0, 0, 0, 0.1)';
    notification.style.display = 'flex';
    notification.style.alignItems = 'center';
    notification.style.gap = '0.5rem';
    notification.style.animation = 'slideIn 0.3s ease-out';
    
    // Set background color based on type
    switch(type) {
        case 'success':
            notification.style.backgroundColor = '#10b981';
            break;
        case 'danger':
            notification.style.backgroundColor = '#ef4444';
            break;
        case 'warning':
            notification.style.backgroundColor = '#f59e0b';
            break;
        default:
            notification.style.backgroundColor = '#3b82f6';
    }
    
    // Add icon
    const icon = document.createElement('i');
    icon.className = 'fas ';
    switch(type) {
        case 'success':
            icon.className += 'fa-check-circle';
            break;
        case 'danger':
            icon.className += 'fa-exclamation-circle';
            break;
        case 'warning':
            icon.className += 'fa-exclamation-triangle';
            break;
        default:
            icon.className += 'fa-info-circle';
    }
    
    // Add message
    const messageSpan = document.createElement('span');
    messageSpan.textContent = message;
    
    // Add close button
    const closeButton = document.createElement('button');
    closeButton.innerHTML = '&times;';
    closeButton.style.background = 'none';
    closeButton.style.border = 'none';
    closeButton.style.color = 'white';
    closeButton.style.fontSize = '1.5rem';
    closeButton.style.cursor = 'pointer';
    closeButton.style.marginLeft = 'auto';
    closeButton.style.opacity = '0.7';
    closeButton.addEventListener('click', () => {
        notification.style.animation = 'slideOut 0.3s ease-out';
        setTimeout(() => notification.remove(), 300);
    });
    
    notification.appendChild(icon);
    notification.appendChild(messageSpan);
    notification.appendChild(closeButton);
    
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentElement) {
            notification.style.animation = 'slideOut 0.3s ease-out';
            setTimeout(() => notification.remove(), 300);
        }
    }, 5000);
}

// Helper functions
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function capitalizeFirst(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}

// Add animation keyframes
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);