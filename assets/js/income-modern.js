document.addEventListener('DOMContentLoaded', function() {
    console.log('Modern Income JS loaded');
    
    // Initialize all components
    initializeChart();
    initializeEventListeners();
    initializeFormValidation();
    initializeAnimations();
    initializeSearch();
});

function initializeChart() {
    if (typeof Chart === 'undefined') {
        console.error('Chart.js is not loaded!');
        return;
    }
    
    const chartCanvas = document.getElementById('frequencyChart');
    if (!chartCanvas) {
        console.error('Chart canvas element not found!');
        return;
    }

    try {
        // Get chart data from meta tags
        const chartLabelsEl = document.querySelector('meta[name="chart-labels"]');
        const chartDataEl = document.querySelector('meta[name="chart-data"]');
        const chartColorsEl = document.querySelector('meta[name="chart-colors"]');
        const currencySymbolEl = document.querySelector('meta[name="currency-symbol"]');
        
        if (!chartLabelsEl || !chartDataEl || !chartColorsEl) {
            console.error('Chart data meta tags not found!');
            showNoDataMessage();
            return;
        }
        
        const chartLabels = JSON.parse(chartLabelsEl.getAttribute('content') || '[]');
        const chartData = JSON.parse(chartDataEl.getAttribute('content') || '[]');
        const chartColors = JSON.parse(chartColorsEl.getAttribute('content') || '[]');
        const currencySymbol = currencySymbolEl ? currencySymbolEl.getAttribute('content') : '$';
        
        if (chartLabels.length === 0 || chartData.length === 0) {
            showNoDataMessage();
            return;
        }

        // Create chart with modern styling
        const ctx = chartCanvas.getContext('2d');
        window.frequencyChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: chartLabels,
                datasets: [{
                    data: chartData,
                    backgroundColor: chartColors,
                    borderColor: '#ffffff',
                    borderWidth: 3,
                    hoverBorderWidth: 3,
                    hoverBorderColor: '#ffffff',
                    hoverOffset: 20
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                animation: {
                    animateScale: true,
                    animateRotate: true,
                    duration: 1500,
                    easing: 'easeInOutQuart'
                },
                layout: {
                    padding: 20
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        padding: 30,
                        labels: {
                            boxWidth: 16,
                            boxHeight: 16,
                            padding: 15,
                            font: {
                                size: 14,
                                weight: 500
                            },
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#ffffff',
                        bodyColor: '#ffffff',
                        padding: 16,
                        cornerRadius: 12,
                        titleFont: {
                            size: 16,
                            weight: 600
                        },
                        bodyFont: {
                            size: 14
                        },
                        displayColors: true,
                        usePointStyle: true,
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                return `${label}: ${currencySymbol}${value.toLocaleString()} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });

        // Hide no data message
        const noDataMessage = document.getElementById('chartNoData');
        if (noDataMessage) {
            noDataMessage.style.display = 'none';
        }

    } catch (error) {
        console.error('Error initializing chart:', error);
        showNoDataMessage();
    }
}

function showNoDataMessage() {
    const chartContainer = document.querySelector('.chart-container');
    const noDataMessage = document.getElementById('chartNoData');
    
    if (chartContainer) {
        chartContainer.style.display = 'none';
    }
    
    if (noDataMessage) {
        noDataMessage.style.display = 'block';
    }
}

function initializeEventListeners() {
    // Edit income buttons
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-action.edit')) {
            e.preventDefault();
            const button = e.target.closest('.btn-action.edit');
            const incomeId = button.getAttribute('data-income-id');
            if (incomeId) {
                loadIncomeForEdit(incomeId);
            }
        }
    });

    // Delete income buttons
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-action.delete')) {
            e.preventDefault();
            const button = e.target.closest('.btn-action.delete');
            const incomeId = button.getAttribute('data-income-id');
            if (incomeId) {
                document.getElementById('delete_income_id').value = incomeId;
                const deleteModal = new bootstrap.Modal(document.getElementById('deleteIncomeModal'));
                deleteModal.show();
            }
        }
    });

    // Add income modal focus
    const addIncomeModal = document.getElementById('addIncomeModal');
    if (addIncomeModal) {
        addIncomeModal.addEventListener('shown.bs.modal', function() {
            document.getElementById('name').focus();
        });
    }

    // Handle end date inputs
    const endDateInputs = document.querySelectorAll('#end_date, #edit_end_date');
    endDateInputs.forEach(input => {
        input.addEventListener('change', function() {
            // Allow empty values for optional end date
            if (!this.value) {
                return;
            }
            
            // Validate the date
            const selectedDate = new Date(this.value);
            if (isNaN(selectedDate.getTime())) {
                this.value = '';
                return;
            }
            
            // Check if date is reasonable
            if (selectedDate < new Date('1900-01-01')) {
                alert('Please select a valid date after January 1, 1900');
                this.value = '';
                return;
            }
        });
    });

    // Active status toggle
    const isActiveCheckbox = document.getElementById('is_active');
    const editIsActiveCheckbox = document.getElementById('edit_is_active');
    
    if (isActiveCheckbox) {
        isActiveCheckbox.addEventListener('change', function() {
            handleActiveStatusChange(this);
        });
    }

    if (editIsActiveCheckbox) {
        editIsActiveCheckbox.addEventListener('change', function() {
            handleActiveStatusChange(this);
        });
    }
}

function handleActiveStatusChange(checkbox) {
    // Visual feedback when toggling active status
    const toggleSlider = checkbox.nextElementSibling;
    if (toggleSlider && toggleSlider.classList.contains('toggle-slider')) {
        toggleSlider.style.transition = 'background-color 0.3s ease';
    }
}

function loadIncomeForEdit(incomeId) {
    const basePath = document.querySelector('meta[name="base-path"]').getAttribute('content');
    
    fetch(`${basePath}/income?action=get_income&income_id=${incomeId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Populate edit form
            document.getElementById('edit_income_id').value = data.income.income_id;
            document.getElementById('edit_name').value = data.income.name;
            document.getElementById('edit_amount').value = data.income.amount;
            document.getElementById('edit_frequency').value = data.income.frequency;
            document.getElementById('edit_start_date').value = data.income.start_date;
            
            // Handle end date
            const endDateInput = document.getElementById('edit_end_date');
            if (data.income.end_date && data.income.end_date !== '0000-00-00' && data.income.end_date !== null) {
                const parsedDate = new Date(data.income.end_date);
                if (!isNaN(parsedDate.getTime()) && parsedDate > new Date('1900-01-01')) {
                    endDateInput.value = data.income.end_date;
                } else {
                    endDateInput.value = '';
                }
            } else {
                endDateInput.value = '';
            }
            
            document.getElementById('edit_is_active').checked = data.income.is_active == 1;
            
            // Show edit modal
            const editModal = new bootstrap.Modal(document.getElementById('editIncomeModal'));
            editModal.show();
        } else {
            showNotification(getTranslation('failed_to_load_income_data'), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification(getTranslation('an_error_occurred_loading'), 'error');
    });
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
}

function initializeAnimations() {
    // Get currency symbol from meta
    const currencySymbolEl = document.querySelector('meta[name="currency-symbol"]');
    const currencySymbol = currencySymbolEl ? currencySymbolEl.getAttribute('content') : '$';
    
    // Animate source bars on load
    const sourceBars = document.querySelectorAll('.source-bar-fill');
    sourceBars.forEach((bar, index) => {
        setTimeout(() => {
            const percentage = bar.getAttribute('data-percentage') || bar.style.width.replace('%', '');
            bar.style.width = percentage + '%';
        }, 100 + (index * 50));
    });
}

function initializeSearch() {
    const searchInput = document.getElementById('incomeSearch');
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('.income-table tbody tr');
            let visibleRows = 0;
            
            tableRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    row.style.display = '';
                    visibleRows++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            // Show/hide no results message
            const noDataMessage = document.getElementById('tableNoData');
            const tableBody = document.querySelector('.table-responsive');
            
            if (visibleRows === 0 && tableRows.length > 0) {
                if (tableBody) tableBody.style.display = 'none';
                if (noDataMessage) {
                    noDataMessage.style.display = 'block';
                    noDataMessage.querySelector('h4').textContent = getTranslation('no_matching_income_sources');
                    noDataMessage.querySelector('p').textContent = getTranslation('try_adjusting_search');
                    noDataMessage.querySelector('.btn-add-first').style.display = 'none';
                }
            } else {
                if (tableBody) tableBody.style.display = 'block';
                if (noDataMessage && tableRows.length > 0) noDataMessage.style.display = 'none';
            }
        });
    }
}

function showNotification(message, type = 'info') {
    // Create a simple notification system
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    
    // Add styles
    notification.style.position = 'fixed';
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.padding = '1rem 1.5rem';
    notification.style.borderRadius = '0.5rem';
    notification.style.backgroundColor = type === 'error' ? '#ef4444' : type === 'warning' ? '#f59e0b' : '#3b82f6';
    notification.style.color = 'white';
    notification.style.zIndex = '9999';
    notification.style.opacity = '0';
    notification.style.transition = 'opacity 0.3s ease';
    
    document.body.appendChild(notification);
    
    // Fade in
    setTimeout(() => {
        notification.style.opacity = '1';
    }, 10);
    
    // Fade out and remove
    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 3000);
}

// Helper function to get translations from the data attributes
function getTranslation(key, defaultValue = '') {
    // For future implementation: we can add data attributes to the body element with translations
    // For now, we'll use hardcoded fallbacks
    const translations = {
        'failed_to_load_income_data': 'Failed to load income data',
        'an_error_occurred_loading': 'An error occurred while loading income data',
        'no_matching_income_sources': 'No matching income sources found',
        'try_adjusting_search': 'Try adjusting your search term'
    };
    
    return translations[key] || defaultValue || key;
}

// Utility function to format currency with the user's currency symbol
function formatCurrency(value) {
    // Get currency symbol from meta tag
    const currencySymbolEl = document.querySelector('meta[name="currency-symbol"]');
    const currencySymbol = currencySymbolEl ? currencySymbolEl.getAttribute('content') : '$';
    
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD', // This is just for formatting, we'll replace the symbol
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(value).replace(/^\$/, currencySymbol);
}

// Utility function to format date
function formatDate(dateString) {
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('en-US', options);
}