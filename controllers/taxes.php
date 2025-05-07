<?php
/**
 * Taxes Controller
 * 
 * Handles tax planning and management functionality
 */

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ' . BASE_PATH . '/login');
    exit();
}

// Include required models
require_once 'models/TaxInformation.php';
require_once 'models/Income.php';
require_once 'models/Expense.php';

// Get user ID
$user_id = $_SESSION['user_id'];

// Initialize objects
$tax = new TaxInformation();
$income = new Income();
$expense = new Expense();

// Get current tax year
$current_year = date('Y');
$selected_year = isset($_GET['year']) ? (int)$_GET['year'] : $current_year;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_update') {
        // Check if tax information already exists for this year
        $tax_year = $_POST['tax_year'] ?? $current_year;
        $tax_exists = $tax->getByYear($tax_year, $user_id);
        
        // Set tax information properties
        $tax->user_id = $user_id;
        $tax->tax_year = $tax_year;
        $tax->filing_status = $_POST['filing_status'] ?? 'single';
        $tax->estimated_income = $_POST['estimated_income'] ?? 0;
        $tax->tax_paid_to_date = $_POST['tax_paid_to_date'] ?? 0;
        $tax->deductions = $_POST['deductions'] ?? 0;
        $tax->credits = $_POST['credits'] ?? 0;
        
        if ($tax_exists) {
            // Update existing tax information
            if ($tax->update()) {
                $success = 'Tax information updated successfully!';
            } else {
                $error = 'Failed to update tax information.';
            }
        } else {
            // Create new tax information
            if ($tax->create()) {
                $success = 'Tax information added successfully!';
            } else {
                $error = 'Failed to add tax information.';
            }
        }
        
        // Update selected year to the one just updated
        $selected_year = $tax_year;
    } elseif ($action === 'delete') {
        // Get tax ID
        $tax_id = $_POST['tax_id'] ?? 0;
        
        // Delete tax information
        if ($tax->delete($tax_id, $user_id)) {
            $success = 'Tax information deleted successfully!';
        } else {
            $error = 'Failed to delete tax information.';
        }
    } elseif ($action === 'auto_fill') {
        // Get tax year
        $tax_year = $_POST['tax_year'] ?? $current_year;
        
        // Auto-fill tax information
        if ($tax->autoFillFromIncome($user_id, $tax_year)) {
            $success = 'Tax information auto-filled successfully!';
            // Update selected year to the one just updated
            $selected_year = $tax_year;
        } else {
            $error = 'Failed to auto-fill tax information. Please make sure you have income sources defined.';
        }
        
        // Redirect to avoid form resubmission
        header('Location: ' . BASE_PATH . '/taxes?year=' . $selected_year . '&success=' . urlencode($success ?? '') . '&error=' . urlencode($error ?? ''));
        exit();
    }
}

// Handle AJAX requests
if (isset($_GET['action']) && $_GET['action'] === 'get_tax_info') {
    header('Content-Type: application/json');
    
    $tax_year = $_GET['tax_year'] ?? $current_year;
    
    if ($tax->getByYear($tax_year, $user_id)) {
        // Calculate additional tax information
        $tax_liability = $tax->calculateTaxLiability();
        $remaining_tax = $tax->getRemainingTax();
        $effective_tax_rate = $tax->getEffectiveTaxRate();
        
        echo json_encode([
            'success' => true,
            'tax_info' => [
                'tax_id' => $tax->tax_id,
                'tax_year' => $tax->tax_year,
                'filing_status' => $tax->filing_status,
                'estimated_income' => $tax->estimated_income,
                'tax_paid_to_date' => $tax->tax_paid_to_date,
                'deductions' => $tax->deductions,
                'credits' => $tax->credits,
                'tax_liability' => $tax_liability,
                'remaining_tax' => $remaining_tax,
                'effective_tax_rate' => $effective_tax_rate
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No tax information found for the selected year.'
        ]);
    }
    
    exit();
}

// Check for success/error messages from redirects
if (isset($_GET['success']) && !empty($_GET['success'])) {
    $success = urldecode($_GET['success']);
}

if (isset($_GET['error']) && !empty($_GET['error'])) {
    $error = urldecode($_GET['error']);
}

// Get all tax information for the user
$tax_years = $tax->getAll($user_id);

// Get tax information for the selected year
$has_tax_info = $tax->getByYear($selected_year, $user_id);

// If tax information exists for the selected year, calculate tax liability
if ($has_tax_info) {
    $tax_liability = $tax->calculateTaxLiability();
    $remaining_tax = $tax->getRemainingTax();
    $effective_tax_rate = $tax->getEffectiveTaxRate();
    $tax_saving_tips = $tax->generateTaxSavingTips();
} else {
    // No tax information for selected year, set defaults
    $tax_liability = 0;
    $remaining_tax = 0;
    $effective_tax_rate = 0;
    $tax_saving_tips = [];
}

// Get yearly income and expense totals for comparison
$yearly_income = $income->getYearlyTotal($user_id);
$yearly_expenses = $expense->getYearlyTotal($user_id);

// Include view
require_once 'views/taxes.php';
?>