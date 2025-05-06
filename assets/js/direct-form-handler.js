/**
 * Direct Form Handler for iGotMoney
 * This script handles form submissions directly without AJAX to troubleshoot the 404 errors
 */

document.addEventListener('DOMContentLoaded', function() {
    // Add event handler to the Add Expense button in the modal
    const addExpenseButton = document.querySelector('#addExpenseModal .modal-footer button[type="submit"]');
    if (addExpenseButton) {
        addExpenseButton.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Get the form
            const form = document.getElementById('addExpenseForm');
            if (!form) return;
            
            // Force direct form submission
            form.setAttribute('data-ajax', 'false');
            form.submit();
        });
    }
    
    // Add event handler to the edit expense button in the modal
    const editExpenseButton = document.querySelector('#editExpenseModal .modal-footer button[type="submit"]');
    if (editExpenseButton) {
        editExpenseButton.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Get the form
            const form = document.getElementById('editExpenseForm');
            if (!form) return;
            
            // Force direct form submission
            form.setAttribute('data-ajax', 'false');
            form.submit();
        });
    }
    
    // Add event handler to the delete expense button in the modal
    const deleteExpenseButton = document.querySelector('#deleteExpenseModal .modal-footer button[type="submit"]');
    if (deleteExpenseButton) {
        deleteExpenseButton.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Get the form
            const form = document.getElementById('deleteExpenseForm');
            if (!form) return;
            
            // Force direct form submission
            form.setAttribute('data-ajax', 'false');
            form.submit();
        });
    }
});