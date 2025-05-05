/**
 * iGotMoney - Investments JavaScript
 * Handles functionality for the investment management page
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize investment value calculator
    initializeValueCalculator();
    
    // Initialize risk level visualization
    initializeRiskVisualization();
    
    // Initialize portfolio analyzer
    initializePortfolioAnalyzer();
});

/**
 * Initialize investment value calculator
 * Dynamically calculates values as user enters data
 */
function initializeValueCalculator() {
    // Add calculator to add investment form
    const formGroups = document.querySelectorAll('.modal-body');
    
    formGroups.forEach(formGroup => {
        // Find relevant inputs
        const purchasePriceInput = formGroup.querySelector('[id$="purchase_price"]');
        const quantityInput = formGroup.querySelector('[id$="quantity"]');
        const currentPriceInput = formGroup.querySelector('[id$="current_price"]');
        
        if (!purchasePriceInput || !quantityInput) return;
        
        // Create calculator container if not exists
        let calculatorDiv = formGroup.querySelector('.investment-calculator');
        if (!calculatorDiv) {
            calculatorDiv = document.createElement('div');
            calculatorDiv.className = 'investment-calculator mt-3';
            
            // Add after the current price input group
            if (currentPriceInput) {
                const inputGroup = currentPriceInput.closest('.mb-3');
                if (inputGroup) {
                    inputGroup.insertAdjacentElement('afterend', calculatorDiv);
                }
            } else if (quantityInput) {
                const inputGroup = quantityInput.closest('.mb-3');
                if (inputGroup) {
                    inputGroup.insertAdjacentElement('afterend', calculatorDiv);
                }
            }
        }
        
        // Add input event listeners
        const updateCalculator = () => {
            const purchasePrice = parseFloat(purchasePriceInput.value) || 0;
            const quantity = parseFloat(quantityInput.value) || 0;
            const currentPrice = currentPriceInput ? (parseFloat(currentPriceInput.value) || purchasePrice) : purchasePrice;
            
            // Calculate values
            const purchaseValue = purchasePrice * quantity;
            const currentValue = currentPrice * quantity;
            const gainLoss = currentValue - purchaseValue;
            const percentGainLoss = purchaseValue > 0 ? (gainLoss / purchaseValue) * 100 : 0;
            
            // Format and display
            if (purchaseValue > 0) {
                calculatorDiv.innerHTML = `
                    <div class="alert alert-info">
                        <h6 class="alert-heading">Investment Summary</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Initial Investment:</strong><br>
                                $${formatNumber(purchaseValue)}
                            </div>
                            <div class="col-md-6">
                                <strong>Current Value:</strong><br>
                                $${formatNumber(currentValue)}
                            </div>
                        </div>
                        <hr>
                        <div class="${gainLoss >= 0 ? 'text-success' : 'text-danger'}">
                            <strong>Gain/Loss:</strong> 
                            $${formatNumber(Math.abs(gainLoss))} 
                            (${gainLoss >= 0 ? '+' : '-'}${Math.abs(percentGainLoss).toFixed(2)}%)
                        </div>
                    </div>
                `;
            } else {
                calculatorDiv.innerHTML = '';
            }
        };
        
        if (purchasePriceInput) purchasePriceInput.addEventListener('input', updateCalculator);
        if (quantityInput) quantityInput.addEventListener('input', updateCalculator);
        if (currentPriceInput) currentPriceInput.addEventListener('input', updateCalculator);
        
        // Initialize with current values
        updateCalculator();
    });
}

/**
 * Initialize risk level visualization
 * Provides visual cues based on investment risk level
 */
function initializeRiskVisualization() {
    const typeSelects = document.querySelectorAll('#type_id, #edit_type_id');
    
    typeSelects.forEach(select => {
        select.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const riskLevel = selectedOption.getAttribute('data-risk');
            
            // Find parent form
            const form = this.closest('form');
            if (!form) return;
            
            // Add risk level indicator
            let riskIndicator = form.querySelector('.risk-level-indicator');
            if (!riskIndicator) {
                riskIndicator = document.createElement('div');
                riskIndicator.className = 'risk-level-indicator mt-2';
                this.parentNode.appendChild(riskIndicator);
            }
            
            // Set risk level color and message
            let riskColor, riskMessage;
            
            switch (riskLevel) {
                case 'very low':
                    riskColor = 'success';
                    riskMessage = 'Very low risk investments typically have lower returns but high stability.';
                    break;
                case 'low':
                    riskColor = 'info';
                    riskMessage = 'Low risk investments generally offer modest returns with good stability.';
                    break;
                case 'moderate':
                    riskColor = 'primary';
                    riskMessage = 'Moderate risk investments balance potential returns with acceptable risk levels.';
                    break;
                case 'high':
                    riskColor = 'warning';
                    riskMessage = 'High risk investments may offer greater returns but come with increased volatility.';
                    break;
                case 'very high':
                    riskColor = 'danger';
                    riskMessage = 'Very high risk investments can yield substantial returns but also significant losses.';
                    break;
                default:
                    riskColor = 'secondary';
                    riskMessage = 'Risk level not specified.';
            }
            
            riskIndicator.innerHTML = `
                <div class="alert alert-${riskColor} py-2">
                    <small><i class="fas fa-info-circle me-1"></i> ${riskMessage}</small>
                </div>
            `;
        });
        
        // Trigger change to show initial risk level
        select.dispatchEvent(new Event('change'));
    });
}

/**
 * Initialize portfolio analyzer
 * Provides portfolio analysis and recommendations
 */
function initializePortfolioAnalyzer() {
    // Create analyzer button in summary card
    const summaryCard = document.querySelector('.card-body .text-center.mb-3');
    if (!summaryCard) return;
    
    const analyzerButton = document.createElement('button');
    analyzerButton.className = 'btn btn-sm btn-outline-primary mt-2';
    analyzerButton.innerHTML = '<i class="fas fa-chart-pie me-1"></i> Analyze Portfolio';
    analyzerButton.id = 'portfolioAnalyzerBtn';
    
    summaryCard.appendChild(analyzerButton);
    
    // Create analyzer container
    const analyzerContainer = document.createElement('div');
    analyzerContainer.id = 'portfolioAnalysis';
    analyzerContainer.className = 'mt-3 d-none';
    summaryCard.insertAdjacentElement('afterend', analyzerContainer);
    
    // Add click event to analyzer button
    analyzerButton.addEventListener('click', function() {
        const analysisDiv = document.getElementById('portfolioAnalysis');
        
        if (analysisDiv.classList.contains('d-none')) {
            // Show loading indicator
            analysisDiv.classList.remove('d-none');
            analysisDiv.innerHTML = `
                <div class="text-center py-3">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Analyzing your portfolio...</p>
                </div>
            `;
            
            // Simulate analysis (would connect to backend in real app)
            setTimeout(() => {
                analyzePortfolio(analysisDiv);
            }, 1000);
            
            // Update button text
            this.innerHTML = '<i class="fas fa-times me-1"></i> Close Analysis';
        } else {
            // Hide analysis
            analysisDiv.classList.add('d-none');
            
            // Update button text
            this.innerHTML = '<i class="fas fa-chart-pie me-1"></i> Analyze Portfolio';
        }
    });
}

/**
 * Analyze portfolio and provide recommendations
 * @param {HTMLElement} container - Container to display results
 */
function analyzePortfolio(container) {
    // Get portfolio data from the page
    const portfolioData = getPortfolioDataFromPage();
    
    // Calculate diversification score
    const diversificationScore = calculateDiversificationScore(portfolioData);
    
    // Calculate risk score
    const riskScore = calculateRiskScore(portfolioData);
    
    // Generate recommendations
    const recommendations = generateRecommendations(portfolioData, diversificationScore, riskScore);
    
    // Display analysis results
    container.innerHTML = `
        <div class="card border-primary">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Portfolio Analysis Results</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center">
                                <h6 class="card-title">Diversification Score</h6>
                                <div class="display-4 ${getScoreColorClass(diversificationScore)}">${diversificationScore}/10</div>
                                <p class="text-muted">${getDiversificationMessage(diversificationScore)}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center">
                                <h6 class="card-title">Risk Assessment</h6>
                                <div class="display-4 ${getRiskColorClass(riskScore)}">${getRiskLabel(riskScore)}</div>
                                <p class="text-muted">${getRiskMessage(riskScore)}</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <h6 class="mb-3">Recommendations</h6>
                <ul class="list-group mb-3">
                    ${recommendations.map(rec => `
                        <li class="list-group-item">
                            <i class="fas fa-lightbulb text-warning me-2"></i>
                            ${rec}
                        </li>
                    `).join('')}
                </ul>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <small>This analysis is based on current portfolio data and general investment principles. 
                    For personalized financial advice, consult with a qualified financial advisor.</small>
                </div>
            </div>
        </div>
    `;
}

/**
 * Extract portfolio data from the page
 * @returns {Object} Portfolio data object
 */
function getPortfolioDataFromPage() {
    const data = {
        totalInvested: 0,
        totalValue: 0,
        byType: {},
        byRisk: {},
        investments: []
    };
    
    // Extract from summary section
    const totalInvestedEl = document.querySelector('.row.mb-4 .text-primary');
    const totalValueEl = document.querySelector('.row.mb-4 .text-info');
    
    if (totalInvestedEl) {
        data.totalInvested = parseFloat(totalInvestedEl.textContent.replace('$', '').replace(/,/g, '')) || 0;
    }
    
    if (totalValueEl) {
        data.totalValue = parseFloat(totalValueEl.textContent.replace('$', '').replace(/,/g, '')) || 0;
    }
    
    // Extract from investment table
    const table = document.getElementById('investmentTable');
    if (table) {
        const rows = table.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            const name = row.querySelector('td:nth-child(1)').textContent.trim();
            const typeCell = row.querySelector('td:nth-child(2)');
            const type = typeCell.textContent.split('(')[0].trim();
            const riskBadge = typeCell.querySelector('.badge');
            const risk = riskBadge ? riskBadge.textContent.trim().toLowerCase() : 'unknown';
            const currentValue = parseFloat(row.querySelector('td:nth-child(7)').textContent.replace('$', '').replace(/,/g, '')) || 0;
            
            // Add to investments array
            data.investments.push({
                name,
                type,
                risk,
                value: currentValue
            });
            
            // Aggregate by type
            if (!data.byType[type]) {
                data.byType[type] = 0;
            }
            data.byType[type] += currentValue;
            
            // Aggregate by risk
            if (!data.byRisk[risk]) {
                data.byRisk[risk] = 0;
            }
            data.byRisk[risk] += currentValue;
        });
    }
    
    return data;
}

/**
 * Calculate diversification score based on portfolio data
 * @param {Object} portfolioData - Portfolio data
 * @returns {number} Diversification score (0-10)
 */
function calculateDiversificationScore(portfolioData) {
    let score = 0;
    
    // Check number of investment types
    const typeCount = Object.keys(portfolioData.byType).length;
    if (typeCount >= 5) {
        score += 3;
    } else if (typeCount >= 3) {
        score += 2;
    } else if (typeCount >= 2) {
        score += 1;
    }
    
    // Check distribution across types
    const typeValues = Object.values(portfolioData.byType);
    if (typeValues.length > 0) {
        const totalValue = typeValues.reduce((sum, val) => sum + val, 0);
        
        // Check if any one type is too dominant
        const maxPercentage = Math.max(...typeValues.map(val => (val / totalValue) * 100));
        
        if (maxPercentage < 50) {
            score += 4;
        } else if (maxPercentage < 70) {
            score += 2;
        } else {
            score += 1;
        }
    }
    
    // Check risk diversity
    const riskCount = Object.keys(portfolioData.byRisk).length;
    if (riskCount >= 3) {
        score += 3;
    } else if (riskCount >= 2) {
        score += 2;
    } else {
        score += 1;
    }
    
    return score;
}

/**
 * Calculate risk score based on portfolio data
 * @param {Object} portfolioData - Portfolio data
 * @returns {number} Risk score (0-100)
 */
function calculateRiskScore(portfolioData) {
    // Risk weights
    const riskWeights = {
        'very low': 10,
        'low': 30,
        'moderate': 50,
        'high': 70,
        'very high': 90,
        'unknown': 50
    };
    
    // Calculate weighted average risk
    let totalValue = 0;
    let weightedRiskSum = 0;
    
    for (const [risk, value] of Object.entries(portfolioData.byRisk)) {
        const weight = riskWeights[risk] || 50;
        weightedRiskSum += weight * value;
        totalValue += value;
    }
    
    return totalValue > 0 ? Math.round(weightedRiskSum / totalValue) : 50;
}

/**
 * Generate portfolio recommendations
 * @param {Object} portfolioData - Portfolio data
 * @param {number} diversificationScore - Diversification score
 * @param {number} riskScore - Risk score
 * @returns {Array} Array of recommendation strings
 */
function generateRecommendations(portfolioData, diversificationScore, riskScore) {
    const recommendations = [];
    
    // Diversification recommendations
    if (diversificationScore < 5) {
        recommendations.push('Consider diversifying your portfolio across more investment types to reduce risk.');
        
        // Check specific types that are missing
        const commonTypes = ['Stocks', 'Bonds', 'ETFs', 'Mutual Funds', 'Real Estate'];
        const missingTypes = commonTypes.filter(type => !Object.keys(portfolioData.byType).some(t => t.includes(type)));
        
        if (missingTypes.length > 0) {
            recommendations.push(`Consider adding these investment types to your portfolio: ${missingTypes.join(', ')}.`);
        }
    }
    
    // Risk recommendations
    if (riskScore > 70) {
        recommendations.push('Your portfolio has a high risk level. Consider adding more conservative investments to balance risk.');
    } else if (riskScore < 30) {
        recommendations.push('Your portfolio is very conservative. Consider adding some growth investments for potentially higher returns.');
    }
    
    // Check for concentration
    const typeValues = Object.entries(portfolioData.byType).map(([type, value]) => ({
        type,
        value,
        percentage: (value / portfolioData.totalValue) * 100
    }));
    
    // Sort by percentage descending
    typeValues.sort((a, b) => b.percentage - a.percentage);
    
    // Check if top investment type is too concentrated
    if (typeValues.length > 0 && typeValues[0].percentage > 70) {
        recommendations.push(`Your portfolio is heavily concentrated in ${typeValues[0].type} (${typeValues[0].percentage.toFixed(1)}%). Consider reducing this concentration.`);
    }
    
    // Add general recommendation if others are few
    if (recommendations.length < 2) {
        recommendations.push('Regularly review your investments and rebalance your portfolio periodically to maintain your desired asset allocation.');
    }
    
    return recommendations;
}

/**
 * Get color class based on diversification score
 * @param {number} score - Diversification score
 * @returns {string} Color class
 */
function getScoreColorClass(score) {
    if (score >= 8) return 'text-success';
    if (score >= 5) return 'text-primary';
    if (score >= 3) return 'text-warning';
    return 'text-danger';
}

/**
 * Get diversification message based on score
 * @param {number} score - Diversification score
 * @returns {string} Descriptive message
 */
function getDiversificationMessage(score) {
    if (score >= 8) return 'Well diversified';
    if (score >= 5) return 'Moderately diversified';
    if (score >= 3) return 'Somewhat diversified';
    return 'Poorly diversified';
}

/**
 * Get color class based on risk score
 * @param {number} score - Risk score
 * @returns {string} Color class
 */
function getRiskColorClass(score) {
    if (score >= 70) return 'text-danger';
    if (score >= 50) return 'text-warning';
    if (score >= 30) return 'text-info';
    return 'text-success';
}

/**
 * Get risk label based on risk score
 * @param {number} score - Risk score
 * @returns {string} Risk label
 */
function getRiskLabel(score) {
    if (score >= 70) return 'High';
    if (score >= 50) return 'Moderate';
    if (score >= 30) return 'Low';
    return 'Very Low';
}

/**
 * Get risk message based on risk score
 * @param {number} score - Risk score
 * @returns {string} Descriptive message
 */
function getRiskMessage(score) {
    if (score >= 70) return 'Higher potential returns but with increased volatility';
    if (score >= 50) return 'Balanced approach with moderate risk and returns';
    if (score >= 30) return 'Conservative with lower risk and moderate returns';
    return 'Very conservative with lower returns but high stability';
}

/**
 * Format number with commas
 * @param {number} num - Number to format
 * @returns {string} Formatted number
 */
function formatNumber(num) {
    return num.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}