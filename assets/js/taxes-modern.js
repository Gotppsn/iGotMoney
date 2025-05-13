document.addEventListener('DOMContentLoaded', function() {
    console.log('Modern Taxes JS loaded');
    
    // Initialize all components
    initializeChart();
    initializeEventListeners();
    initializeCalculations();
    initializeAnimations();
});

function initializeChart() {
    if (typeof Chart === 'undefined') {
        console.error('Chart.js is not loaded!');
        return;
    }
    
    const chartCanvas = document.getElementById('taxBreakdownChart');
    if (!chartCanvas) {
        console.error('Chart canvas element not found!');
        return;
    }

    try {
        // Get chart data from meta tags
        const chartLabelsEl = document.querySelector('meta[name="chart-labels"]');
        const chartDataEl = document.querySelector('meta[name="chart-data"]');
        const chartColorsEl = document.querySelector('meta[name="chart-colors"]');
        
        if (!chartLabelsEl || !chartDataEl || !chartColorsEl) {
            console.error('Chart data meta tags not found!');
            return;
        }
        
        const chartLabels = JSON.parse(chartLabelsEl.getAttribute('content') || '[]');
        const chartData = JSON.parse(chartDataEl.getAttribute('content') || '[]');
        const chartColors = JSON.parse(chartColorsEl.getAttribute('content') || '[]');
        
        if (chartLabels.length === 0 || chartData.length === 0) {
            return;
        }

        // Create chart with modern styling
        const ctx = chartCanvas.getContext('2d');
        window.taxBreakdownChart = new Chart(ctx, {
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
                cutout: '60%',
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
                                const currencySymbol = getCurrencySymbol();
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

    } catch (error) {
        console.error('Error initializing chart:', error);
    }
}

// Get currency symbol from the page metadata or use $ as a fallback
function getCurrencySymbol() {
    const metaTag = document.querySelector('meta[name="currency-symbol"]');
    return metaTag ? metaTag.getAttribute('content') : '$';
}

function initializeEventListeners() {
    // Auto-fill buttons
    document.getElementById('autoFillTaxInfo')?.addEventListener('click', function() {
        const modal = new bootstrap.Modal(document.getElementById('autoFillModal'));
        modal.show();
    });

    document.getElementById('autoFillEmpty')?.addEventListener('click', function() {
        const modal = new bootstrap.Modal(document.getElementById('autoFillModal'));
        modal.show();
    });

    // Delete button
    document.getElementById('deleteTaxInfo')?.addEventListener('click', function(e) {
        e.preventDefault();
        const taxId = this.getAttribute('data-tax-id');
        document.getElementById('delete_tax_id').value = taxId;
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteTaxInfoModal'));
        deleteModal.show();
    });

    // Scroll to form button
    document.getElementById('scrollToForm')?.addEventListener('click', function() {
        document.querySelector('.tax-form').scrollIntoView({ behavior: 'smooth' });
    });

    // Tax tips accordion
    const tipHeaders = document.querySelectorAll('.tip-header');
    tipHeaders.forEach(header => {
        header.addEventListener('click', function() {
            const tipIndex = this.getAttribute('data-tip-index');
            const content = document.getElementById(`tipContent${tipIndex}`);
            const allContents = document.querySelectorAll('.tip-content');
            const allHeaders = document.querySelectorAll('.tip-header');
            
            // Close all other accordion items
            allContents.forEach((item, index) => {
                if (item !== content) {
                    item.classList.remove('active');
                    allHeaders[index].setAttribute('aria-expanded', 'false');
                }
            });
            
            // Toggle current accordion item
            content.classList.toggle('active');
            this.setAttribute('aria-expanded', content.classList.contains('active'));
        });
    });

    // Form submission loading states
    const taxForm = document.getElementById('taxInfoForm');
    if (taxForm) {
        taxForm.addEventListener('submit', function() {
            const submitButton = this.querySelector('button[type="submit"]');
            const originalText = submitButton.innerHTML;
            submitButton.disabled = true;
            
            // Get loading text from translation data attribute or use fallback
            const loadingText = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> ' + 
                                (document.documentElement.getAttribute('data-loading-text') || 'Processing...');
            
            submitButton.innerHTML = loadingText;
            
            // Reset button state after 30 seconds (failsafe)
            setTimeout(() => {
                submitButton.disabled = false;
                submitButton.innerHTML = originalText;
            }, 30000);
        });
    }

    // Auto-fill form submission
    const autoFillForm = document.getElementById('autoFillForm');
    if (autoFillForm) {
        autoFillForm.addEventListener('submit', function() {
            const submitButton = document.querySelector('button[form="autoFillForm"]');
            const originalText = submitButton.innerHTML;
            submitButton.disabled = true;
            
            // Get loading text from translation data attribute or use fallback
            const autoFillingText = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> ' + 
                                   (document.documentElement.getAttribute('data-auto-filling-text') || 'Auto-Filling...');
            
            submitButton.innerHTML = autoFillingText;
            
            // Reset button state after 30 seconds (failsafe)
            setTimeout(() => {
                submitButton.disabled = false;
                submitButton.innerHTML = originalText;
            }, 30000);
        });
    }

    // Delete form submission
    const deleteForm = document.getElementById('deleteTaxInfoForm');
    if (deleteForm) {
        deleteForm.addEventListener('submit', function() {
            const submitButton = document.querySelector('button[form="deleteTaxInfoForm"]');
            const originalText = submitButton.innerHTML;
            submitButton.disabled = true;
            
            // Get loading text from translation data attribute or use fallback
            const deletingText = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> ' + 
                                (document.documentElement.getAttribute('data-deleting-text') || 'Deleting...');
            
            submitButton.innerHTML = deletingText;
            
            // Reset button state after 30 seconds (failsafe)
            setTimeout(() => {
                submitButton.disabled = false;
                submitButton.innerHTML = originalText;
            }, 30000);
        });
    }
}

function initializeCalculations() {
    const filingStatusEl = document.getElementById('filing_status');
    const estimatedIncomeEl = document.getElementById('estimated_income');
    const deductionsEl = document.getElementById('deductions');
    const creditsEl = document.getElementById('credits');
    const taxPaidToDateEl = document.getElementById('tax_paid_to_date');
    
    // Add input event listeners to all form fields
    [filingStatusEl, estimatedIncomeEl, deductionsEl, creditsEl, taxPaidToDateEl].forEach(el => {
        if (el) {
            el.addEventListener('change', calculateTaxEstimates);
            el.addEventListener('input', calculateTaxEstimates);
        }
    });
    
    // Initial calculation
    calculateTaxEstimates();
}

function calculateTaxEstimates() {
    const currencySymbol = getCurrencySymbol();
    const filingStatus = document.getElementById('filing_status')?.value || 'single';
    const estimatedIncome = parseFloat(document.getElementById('estimated_income')?.value) || 0;
    const deductions = parseFloat(document.getElementById('deductions')?.value) || 0;
    const credits = parseFloat(document.getElementById('credits')?.value) || 0;
    const taxPaidToDate = parseFloat(document.getElementById('tax_paid_to_date')?.value) || 0;
    
    // Calculate taxable income
    const taxableIncome = Math.max(0, estimatedIncome - deductions);
    
    // Calculate tax based on filing status (simplified version)
    let tax = 0;
    
    if (filingStatus === 'single') {
        // 2024 tax brackets for single filers (simplified)
        if (taxableIncome <= 11000) {
            tax = taxableIncome * 0.10;
        } else if (taxableIncome <= 44725) {
            tax = 1100 + (taxableIncome - 11000) * 0.12;
        } else if (taxableIncome <= 95375) {
            tax = 5147 + (taxableIncome - 44725) * 0.22;
        } else if (taxableIncome <= 182100) {
            tax = 16290 + (taxableIncome - 95375) * 0.24;
        } else if (taxableIncome <= 231250) {
            tax = 37104 + (taxableIncome - 182100) * 0.32;
        } else if (taxableIncome <= 578125) {
            tax = 52832 + (taxableIncome - 231250) * 0.35;
        } else {
            tax = 174238.25 + (taxableIncome - 578125) * 0.37;
        }
    } else if (filingStatus === 'married_joint') {
        // 2024 tax brackets for married filing jointly (simplified)
        if (taxableIncome <= 22000) {
            tax = taxableIncome * 0.10;
        } else if (taxableIncome <= 89450) {
            tax = 2200 + (taxableIncome - 22000) * 0.12;
        } else if (taxableIncome <= 190750) {
            tax = 10294 + (taxableIncome - 89450) * 0.22;
        } else if (taxableIncome <= 364200) {
            tax = 32580 + (taxableIncome - 190750) * 0.24;
        } else if (taxableIncome <= 462500) {
            tax = 74208 + (taxableIncome - 364200) * 0.32;
        } else if (taxableIncome <= 693750) {
            tax = 105664 + (taxableIncome - 462500) * 0.35;
        } else {
            tax = 186601.5 + (taxableIncome - 693750) * 0.37;
        }
    } else if (filingStatus === 'married_separate') {
        // 2024 tax brackets for married filing separately (simplified)
        if (taxableIncome <= 11000) {
            tax = taxableIncome * 0.10;
        } else if (taxableIncome <= 44725) {
            tax = 1100 + (taxableIncome - 11000) * 0.12;
        } else if (taxableIncome <= 95375) {
            tax = 5147 + (taxableIncome - 44725) * 0.22;
        } else if (taxableIncome <= 182100) {
            tax = 16290 + (taxableIncome - 95375) * 0.24;
        } else if (taxableIncome <= 231250) {
            tax = 37104 + (taxableIncome - 182100) * 0.32;
        } else if (taxableIncome <= 346875) {
            tax = 52832 + (taxableIncome - 231250) * 0.35;
        } else {
            tax = 93300.75 + (taxableIncome - 346875) * 0.37;
        }
    } else if (filingStatus === 'head_of_household') {
        // 2024 tax brackets for head of household (simplified)
        if (taxableIncome <= 15700) {
            tax = taxableIncome * 0.10;
        } else if (taxableIncome <= 59850) {
            tax = 1570 + (taxableIncome - 15700) * 0.12;
        } else if (taxableIncome <= 95350) {
            tax = 6868 + (taxableIncome - 59850) * 0.22;
        } else if (taxableIncome <= 182100) {
            tax = 14678 + (taxableIncome - 95350) * 0.24;
        } else if (taxableIncome <= 231250) {
            tax = 35498 + (taxableIncome - 182100) * 0.32;
        } else if (taxableIncome <= 578100) {
            tax = 51226 + (taxableIncome - 231250) * 0.35;
        } else {
            tax = 172623.5 + (taxableIncome - 578100) * 0.37;
        }
    }
    
    // Apply tax credits
    tax = Math.max(0, tax - credits);
    
    // Calculate remaining tax
    const remainingTax = Math.max(0, tax - taxPaidToDate);
    
    // Calculate effective tax rate
    const effectiveTaxRate = estimatedIncome > 0 ? (tax / estimatedIncome) * 100 : 0;
    
    // Update stat cards
    updateStatCards(estimatedIncome, tax, remainingTax, effectiveTaxRate);
    
    // Update chart if it exists
    updateChart(estimatedIncome, tax);
}

function updateStatCards(estimatedIncome, taxLiability, remainingTax, effectiveTaxRate) {
    const cards = document.querySelectorAll('.stat-value');
    const currencySymbol = getCurrencySymbol();
    
    if (cards.length >= 4) {
        // Apply subtle animation to highlight changes
        cards.forEach(card => {
            card.style.transition = 'color 0.3s ease';
            card.style.color = '#6366f1';
            setTimeout(() => {
                card.style.color = '';
            }, 300);
        });
        
        // Update values
        cards[0].textContent = currencySymbol + estimatedIncome.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        cards[1].textContent = currencySymbol + taxLiability.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        cards[2].textContent = currencySymbol + remainingTax.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        cards[3].textContent = effectiveTaxRate.toFixed(2) + '%';
    }
}

function updateChart(estimatedIncome, taxLiability) {
    if (window.taxBreakdownChart) {
        const netIncome = estimatedIncome - taxLiability;
        
        // Update chart data
        window.taxBreakdownChart.data.datasets[0].data = [netIncome, taxLiability];
        
        // Apply smooth animation
        window.taxBreakdownChart.options.animation = {
            duration: 800,
            easing: 'easeOutQuart'
        };
        
        // Update chart
        window.taxBreakdownChart.update();
    }
}

function initializeAnimations() {
    // Check for success message in URL
    const urlParams = new URLSearchParams(window.location.search);
    const successParam = urlParams.get('success');
    const errorParam = urlParams.get('error');
    
    if (successParam && successParam.includes('auto-filled')) {
        // Highlight stat cards with success animation
        document.querySelectorAll('.stat-value').forEach(card => {
            card.style.transition = 'color 0.3s ease';
            card.style.color = '#10b981';
            setTimeout(() => {
                card.style.color = '';
            }, 1500);
        });
        
        // If chart exists, add highlight animation
        if (window.taxBreakdownChart) {
            window.taxBreakdownChart.options.animation = {
                duration: 1200,
                easing: 'easeOutBounce'
            };
            window.taxBreakdownChart.update();
        }
        
        // Show success notification
        // Get translated message or use fallback
        const successMessage = document.documentElement.getAttribute('data-success-auto-fill-message') || 
                              'Tax information auto-filled successfully!';
        showNotification(successMessage, 'success');
        
        // Clean URL
        const url = new URL(window.location.href);
        url.searchParams.delete('success');
        url.searchParams.delete('error');
        window.history.replaceState({}, document.title, url.toString());
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
    notification.style.backgroundColor = type === 'error' ? '#ef4444' : type === 'success' ? '#10b981' : '#3b82f6';
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