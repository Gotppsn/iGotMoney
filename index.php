<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iGotMoney - Your Personal Finance Manager</title>
    
    <!-- Add proper CSP meta tag -->
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://code.jquery.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data:">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="/assets/css/style.css" rel="stylesheet">
    <!-- Favicon -->
    <link rel="icon" href="/assets/img/favicon.ico">
</head>
<body>
    <div class="container">
        <header class="py-3 mb-4 border-bottom">
            <div class="row align-items-center">
                <div class="col-6">
                    <a href="/" class="text-decoration-none">
                        <img src="/assets/img/logo.png" alt="iGotMoney" height="40" class="me-2">
                        <span class="fs-4 fw-bold text-primary">iGotMoney</span>
                    </a>
                </div>
                <div class="col-6 text-end">
                    <a href="/login" class="btn btn-outline-primary me-2">Login</a>
                    <a href="/register" class="btn btn-primary">Register</a>
                </div>
            </div>
        </header>

        <main>
            <div class="p-5 mb-4 bg-light rounded-3">
                <div class="container-fluid py-5">
                    <h1 class="display-5 fw-bold">Welcome to iGotMoney</h1>
                    <p class="col-md-8 fs-4">Your personal finance manager that helps you track expenses, manage budgets, and achieve your financial goals.</p>
                    <button class="btn btn-primary btn-lg" type="button">Get Started</button>
                </div>
            </div>

            <div class="row align-items-md-stretch">
                <div class="col-md-4">
                    <div class="h-100 p-5 text-white bg-primary rounded-3">
                        <h2>Track Expenses</h2>
                        <p>Record and categorize your expenses easily to understand your spending habits.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="h-100 p-5 bg-light border rounded-3">
                        <h2>Set Budgets</h2>
                        <p>Create monthly budgets for different categories and stay within your financial limits.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="h-100 p-5 text-white bg-success rounded-3">
                        <h2>Financial Goals</h2>
                        <p>Set and track your financial goals with progress indicators and reminders.</p>
                    </div>
                </div>
            </div>
        </main>

        <footer class="py-3 my-4">
            <p class="text-center text-muted">© 2025 iGotMoney - Your Personal Finance Manager</p>
        </footer>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Custom JS -->
    <script src="/assets/js/main.js"></script>
</body>
</html>