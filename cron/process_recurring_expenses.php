<?php
/**
 * Cron job to process recurring expenses
 * 
 * This script should be run daily to generate new entries for recurring expenses
 * Add to crontab: 0 0 * * * /usr/bin/php /path/to/your/cron/process_recurring_expenses.php
 */

// Include configuration and models
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Expense.php';

try {
    // Initialize expense model
    $expense = new Expense();
    
    // Process all due recurring expenses for all users
    $result = $expense->processDueRecurringExpenses();
    
    if ($result) {
        echo "Successfully processed recurring expenses at " . date('Y-m-d H:i:s') . "\n";
    } else {
        echo "Failed to process recurring expenses at " . date('Y-m-d H:i:s') . "\n";
    }
    
} catch (Exception $e) {
    echo "Error processing recurring expenses: " . $e->getMessage() . "\n";
    error_log("Cron job error: " . $e->getMessage());
}
?>