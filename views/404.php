<?php
// Set page title
$page_title = '404 Not Found - iGotMoney';

// Include header
require_once 'includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="error-content">
                <h1 class="error-title mb-4">404</h1>
                <h2 class="mb-4">Page Not Found</h2>
                <p class="lead mb-5">The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.</p>
                
                <div class="text-center mb-5">
                    <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
                        <a href="/dashboard" class="btn btn-primary btn-lg">
                            <i class="fas fa-home me-2"></i> Return to Dashboard
                        </a>
                    <?php else: ?>
                        <a href="/" class="btn btn-primary btn-lg">
                            <i class="fas fa-home me-2"></i> Return to Home
                        </a>
                    <?php endif; ?>
                </div>
                
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Looking for something?</h5>
                        <p class="card-text">Here are some helpful links:</p>
                        <div class="row">
                            <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
                                <div class="col-md-4">
                                    <a href="/dashboard" class="text-decoration-none">
                                        <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                                    </a>
                                </div>
                                <div class="col-md-4">
                                    <a href="/income" class="text-decoration-none">
                                        <i class="fas fa-wallet me-2"></i> Income
                                    </a>
                                </div>
                                <div class="col-md-4">
                                    <a href="/expenses" class="text-decoration-none">
                                        <i class="fas fa-credit-card me-2"></i> Expenses
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="col-md-4">
                                    <a href="/" class="text-decoration-none">
                                        <i class="fas fa-home me-2"></i> Home Page
                                    </a>
                                </div>
                                <div class="col-md-4">
                                    <a href="/login" class="text-decoration-none">
                                        <i class="fas fa-sign-in-alt me-2"></i> Login
                                    </a>
                                </div>
                                <div class="col-md-4">
                                    <a href="/register" class="text-decoration-none">
                                        <i class="fas fa-user-plus me-2"></i> Register
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .error-title {
        font-size: 8rem;
        font-weight: 700;
        color: #4e73df;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    .error-content {
        padding: 3rem 1rem;
    }
</style>

<?php
// Include footer
require_once 'includes/footer.php';
?>