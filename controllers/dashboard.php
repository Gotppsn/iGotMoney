<?php
/**
 * Dashboard Controller
 * 
 * Main dashboard page after user login
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
require_once 'models/FinancialGoal.php';
require_once 'models/FinancialAdvice.php';

// Get user data
$user = new User();
$user->getUserById($_SESSION['user_id']);

// Get income summary
$income = new Income();
$monthly_income = $income->getMonthlyTotal($_SESSION['user_id']);
$yearly_income = $income->getYearlyTotal($_SESSION['user_id']);

// Get expense summary
$expense = new Expense();
$monthly_expenses = $expense->getMonthlyTotal($_SESSION['user_id']);
$yearly_expenses = $expense->getYearlyTotal($_SESSION['user_id']);

// Calculate net income
$monthly_net = $monthly_income - $monthly_expenses;
$yearly_net = $yearly_income - $yearly_expenses;

// Get budget status
$budget = new Budget();
$budget_status = $budget->getCurrentStatus($_SESSION['user_id']);

// Get top expense categories
$top_expenses = $expense->getTopCategories($_SESSION['user_id'], 5);

// Get investment summary
$investment = new Investment();
$investment_summary = $investment->getSummary($_SESSION['user_id']);

// Get financial goals
$goal = new FinancialGoal();
$goals = $goal->getActiveGoals($_SESSION['user_id']);

// Get financial advice
$advice = new FinancialAdvice();
$financial_advice = $advice->getLatestAdvice($_SESSION['user_id'], 3);

// Include view
require_once 'views/dashboard.php';
?>