document.addEventListener('DOMContentLoaded', function() {
    console.log('Modern Profile JS loaded');
    
    // Initialize all components
    initializeCharts();
    initializeProgressBars();
    initializeAnimations();
    initializeEventListeners();
});

function initializeCharts() {
    initializeIncomeExpensesChart();
    initializeInvestmentChart();
}

function initializeIncomeExpensesChart() {
    const ctx = document.getElementById('incomeExpensesChart');
    if (!ctx) return;
    
    // Get data from meta tags
    const monthlyIncomeEl = document.querySelector('meta[name="monthly-income"]');
    const monthlyExpensesEl = document.querySelector('meta[name="monthly-expenses"]');
    
    if (!monthlyIncomeEl || !monthlyExpensesEl) return;
    
    const monthlyIncome = parseFloat(monthlyIncomeEl.getAttribute('content'));
    const monthlyExpenses = parseFloat(monthlyExpensesEl.getAttribute('content'));
    
    // Get language-specific labels
    const incomeLabel = document.documentElement.lang === 'th' ? 'รายได้' : 'Income';
    const expensesLabel = document.documentElement.lang === 'th' ? 'ค่าใช้จ่าย' : 'Expenses';
    
    const data = {
        labels: [incomeLabel, expensesLabel],
        datasets: [{
            data: [monthlyIncome, monthlyExpenses],
            backgroundColor: [
                '#10b981',
                '#ef4444'
            ],
            borderWidth: 0,
            hoverOffset: 20
        }]
    };
    
    new Chart(ctx, {
        type: 'doughnut',
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '75%',
            plugins: {
                legend: {
                    position: 'bottom',
                    padding: 20,
                    labels: {
                        boxWidth: 16,
                        boxHeight: 16,
                        padding: 15,
                        font: {
                            size: 14,
                            weight: 500,
                            family: document.documentElement.lang === 'th' ? 
                                   "'Noto Sans Thai', sans-serif" : "'Inter', sans-serif"
                        },
                        usePointStyle: true
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
                        weight: 600,
                        family: document.documentElement.lang === 'th' ? 
                               "'Noto Sans Thai', sans-serif" : "'Inter', sans-serif"
                    },
                    bodyFont: {
                        size: 14,
                        family: document.documentElement.lang === 'th' ? 
                               "'Noto Sans Thai', sans-serif" : "'Inter', sans-serif"
                    },
                    displayColors: true,
                    usePointStyle: true,
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${label}: $${value.toLocaleString()} (${percentage}%)`;
                        }
                    }
                }
            },
            animation: {
                animateScale: true,
                animateRotate: true,
                duration: 1500,
                easing: 'easeInOutQuart'
            }
        }
    });
}

function initializeInvestmentChart() {
    const ctx = document.getElementById('investmentPieChart');
    if (!ctx) return;
    
    // Get data from meta tags
    const chartLabelsEl = document.querySelector('meta[name="investment-labels"]');
    const chartDataEl = document.querySelector('meta[name="investment-data"]');
    
    if (!chartLabelsEl || !chartDataEl) return;
    
    const labels = JSON.parse(chartLabelsEl.getAttribute('content'));
    const data = JSON.parse(chartDataEl.getAttribute('content'));
    
    const colors = [
        '#6366f1', '#8b5cf6', '#ec4899', '#ef4444', '#f59e0b',
        '#10b981', '#14b8a6', '#06b6d4', '#3b82f6', '#6366f1'
    ];
    
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: colors.slice(0, data.length),
                borderWidth: 2,
                borderColor: '#ffffff',
                hoverOffset: 20
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    padding: 20,
                    labels: {
                        boxWidth: 16,
                        boxHeight: 16,
                        padding: 12,
                        font: {
                            size: 13,
                            weight: 500,
                            family: document.documentElement.lang === 'th' ? 
                                   "'Noto Sans Thai', sans-serif" : "'Inter', sans-serif"
                        },
                        usePointStyle: true
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
                        weight: 600,
                        family: document.documentElement.lang === 'th' ? 
                               "'Noto Sans Thai', sans-serif" : "'Inter', sans-serif"
                    },
                    bodyFont: {
                        size: 14,
                        family: document.documentElement.lang === 'th' ? 
                               "'Noto Sans Thai', sans-serif" : "'Inter', sans-serif"
                    },
                    displayColors: true,
                    usePointStyle: true,
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${label}: $${value.toLocaleString()} (${percentage}%)`;
                        }
                    }
                }
            },
            animation: {
                animateScale: true,
                animateRotate: true,
                duration: 1500,
                easing: 'easeInOutQuart'
            }
        }
    });
}

function initializeProgressBars() {
    // Animate all progress bars
    const progressBars = document.querySelectorAll('.progress-bar-fill');
    
    progressBars.forEach((bar, index) => {
        const percentage = bar.getAttribute('data-percentage');
        if (percentage) {
            setTimeout(() => {
                bar.style.width = percentage + '%';
            }, 100 + (index * 100));
        }
    });
    
    // Budget progress bars
    const budgetBars = document.querySelectorAll('.progress-bar');
    budgetBars.forEach((bar, index) => {
        const width = bar.style.width;
        if (width) {
            bar.style.width = '0%';
            setTimeout(() => {
                bar.style.width = width;
            }, 100 + (index * 100));
        }
    });
}

function initializeAnimations() {
    // Animate score circle
    const scoreCircle = document.querySelector('.score-circle');
    if (scoreCircle) {
        const scoreValue = scoreCircle.querySelector('.score-value');
        if (scoreValue) {
            const finalValue = parseInt(scoreValue.textContent);
            let currentValue = 0;
            const increment = finalValue / 50;
            
            const animateScore = () => {
                if (currentValue < finalValue) {
                    currentValue += increment;
                    if (currentValue > finalValue) currentValue = finalValue;
                    scoreValue.textContent = Math.round(currentValue);
                    requestAnimationFrame(animateScore);
                }
            };
            
            // Start animation after a short delay
            setTimeout(animateScore, 500);
        }
    }
    
    // Animate stat values
    const statValues = document.querySelectorAll('.stat-value');
    statValues.forEach((stat, index) => {
        const value = stat.textContent;
        stat.style.opacity = '0';
        stat.style.transform = 'translateY(10px)';
        
        setTimeout(() => {
            stat.style.transition = 'all 0.6s ease';
            stat.style.opacity = '1';
            stat.style.transform = 'translateY(0)';
        }, 300 + (index * 100));
    });
}

function initializeEventListeners() {
    // Print functionality
    const printButton = document.querySelector('.btn-print');
    if (printButton) {
        printButton.addEventListener('click', function() {
            window.print();
        });
    }
    
    // Add hover effects to cards
    const cards = document.querySelectorAll('.profile-card, .stat-card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
    
    // Smooth scroll for internal links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const href = this.getAttribute('href');
            
            // Check if href is valid (not just '#')
            if (href && href !== '#' && href.length > 1) {
                try {
                    const target = document.querySelector(href);
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                } catch (error) {
                    console.warn('Invalid selector:', href);
                }
            }
        });
    });
}

// Utility function to format currency
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
}

// Utility function to format percentages
function formatPercentage(value) {
    return new Intl.NumberFormat('en-US', {
        style: 'percent',
        minimumFractionDigits: 1,
        maximumFractionDigits: 1
    }).format(value / 100);
}