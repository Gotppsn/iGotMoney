<?php
/**
 * Profile Controller
 * 
 * Handles user profile functionality
 */

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: /login');
    exit();
}

// Include required models
require_once 'models/User.php';
require_once 'models/Income.php';
require_once 'models/Expense.php';
require_once 'models/Budget.php';
require_once 'models/Investment.php';

// Get user ID
$user_id = $_SESSION['user_id'];

// Initialize objects
$user = new User();
$income = new Income();
$expense = new Expense();
$budget = new Budget();
$investment = new Investment();

// Get user data
$user->getUserById($user_id);

// Get financial summary
$monthly_income = $income->getMonthlyTotal($user_id);
$yearly_income = $income->getYearlyTotal($user_id);
$monthly_expenses = $expense->getMonthlyTotal($user_id);
$yearly_expenses = $expense->getYearlyTotal($user_id);
$monthly_net = $monthly_income - $monthly_expenses;
$yearly_net = $yearly_income - $yearly_expenses;
$saving_rate = $monthly_income > 0 ? ($monthly_net / $monthly_income) * 100 : 0;

// Get budget status
$budget_status = $budget->getCurrentStatus($user_id);

// Get investment summary
$investment_summary = $investment->getSummary($user_id);

// Calculate statistics
$total_invested = isset($investment_summary['total_invested']) ? $investment_summary['total_invested'] : 0;
$current_investment_value = isset($investment_summary['current_value']) ? $investment_summary['current_value'] : 0;
$investment_gain_loss = $current_investment_value - $total_invested;
$investment_gain_loss_percent = $total_invested > 0 ? ($investment_gain_loss / $total_invested) * 100 : 0;

// Calculate financial health score (simplified)
$financial_health = calculateFinancialHealthScore($monthly_income, $monthly_expenses, $saving_rate, $total_invested);

// Include view
require_once 'views/profile.php';

/**
 * Calculate financial health score
 * 
 * @param float $monthly_income Monthly income
 * @param float $monthly_expenses Monthly expenses
 * @param float $saving_rate Saving rate percentage
 * @param float $total_invested Total invested amount
 * @return array Financial health score and breakdown
 */
function calculateFinancialHealthScore($monthly_income, $monthly_expenses, $saving_rate, $total_invested) {
    // Initialize scores
    $expense_to_income_score = 0;
    $saving_rate_score = 0;
    $investment_score = 0;
    
    // Calculate expense to income ratio score (0-30 points)
    $expense_ratio = $monthly_income > 0 ? $monthly_expenses / $monthly_income : 1;
    if ($expense_ratio <= 0.5) {
        $expense_to_income_score = 30;
    } elseif ($expense_ratio <= 0.7) {
        $expense_to_income_score = 25;
    } elseif ($expense_ratio <= 0.8) {
        $expense_to_income_score = 20;
    } elseif ($expense_ratio <= 0.9) {
        $expense_to_income_score = 15;
    } elseif ($expense_ratio < 1) {
        $expense_to_income_score = 10;
    } else {
        $expense_to_income_score = 5;
    }
    
    // Calculate saving rate score (0-40 points)
    if ($saving_rate >= 30) {
        $saving_rate_score = 40;
    } elseif ($saving_rate >= 20) {
        $saving_rate_score = 35;
    } elseif ($saving_rate >= 15) {
        $saving_rate_score = 30;
    } elseif ($saving_rate >= 10) {
        $saving_rate_score = 25;
    } elseif ($saving_rate >= 5) {
        $saving_rate_score = 20;
    } elseif ($saving_rate > 0) {
        $saving_rate_score = 15;
    } else {
        $saving_rate_score = 0;
    }
    
    // Calculate investment score (0-30 points)
    $months_of_expenses = $monthly_expenses > 0 ? $total_invested / $monthly_expenses : 0;
    if ($months_of_expenses >= 12) {
        $investment_score = 30;
    } elseif ($months_of_expenses >= 9) {
        $investment_score = 25;
    } elseif ($months_of_expenses >= 6) {
        $investment_score = 20;
    } elseif ($months_of_expenses >= 3) {
        $investment_score = 15;
    } elseif ($months_of_expenses > 0) {
        $investment_score = 10;
    } else {
        $investment_score = 0;
    }
    
    // Calculate total score
    $total_score = $expense_to_income_score + $saving_rate_score + $investment_score;
    
    // Determine health status
    $status = '';
    if ($total_score >= 85) {
        $status = 'Excellent';
    } elseif ($total_score >= 70) {
        $status = 'Good';
    } elseif ($total_score >= 50) {
        $status = 'Average';
    } elseif ($total_score >= 35) {
        $status = 'Below Average';
    } else {
        $status = 'Poor';
    }
    
    // Return financial health information
    return array(
        'score' => $total_score,
        'status' => $status,
        'breakdown' => array(
            'expense_to_income' => array(
                'score' => $expense_to_income_score,
                'max' => 30,
                'label' => 'Expense to Income Ratio'
            ),
            'saving_rate' => array(
                'score' => $saving_rate_score,
                'max' => 40,
                'label' => 'Saving Rate'
            ),
            'investments' => array(
                'score' => $investment_score,
                'max' => 30,
                'label' => 'Investments'
            )
        )
    );
}
?>