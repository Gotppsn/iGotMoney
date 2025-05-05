<?php
// Set page title
$page_title = 'iGotMoney - Your Personal Finance Manager';

// Include header
require_once 'includes/header.php';
?>

<!-- Hero Section -->
<div class="container-fluid bg-primary text-white py-5">
    <div class="container py-5">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-4">Take Control of Your Finances</h1>
                <p class="lead mb-4">iGotMoney helps you manage your finances, track expenses, set budgets, and achieve your financial goals - all in one place.</p>
                <div class="d-flex gap-3">
                    <a href="<?php echo BASE_PATH; ?>/register" class="btn btn-light btn-lg px-4">Get Started</a>
                    <a href="#features" class="btn btn-outline-light btn-lg px-4">Learn More</a>
                </div>
            </div>
            <div class="col-lg-6 d-none d-lg-block">
                <img src="<?php echo BASE_PATH; ?>/assets/images/hero-image.svg" alt="Financial Management" class="img-fluid">
            </div>
        </div>
    </div>
</div>

<!-- Features Section -->
<div id="features" class="container py-5">
    <div class="text-center mb-5">
        <h2 class="fw-bold">Powerful Financial Tools</h2>
        <p class="text-muted">Everything you need to manage your financial life</p>
    </div>
    
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center p-4">
                    <div class="rounded-circle bg-primary text-white d-inline-flex justify-content-center align-items-center mb-3" style="width: 60px; height: 60px;">
                        <i class="fas fa-wallet fa-2x"></i>
                    </div>
                    <h4 class="card-title">Income & Expense Tracking</h4>
                    <p class="card-text">Easily track all your income sources and expenses in one place. Categorize and analyze your spending habits.</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center p-4">
                    <div class="rounded-circle bg-success text-white d-inline-flex justify-content-center align-items-center mb-3" style="width: 60px; height: 60px;">
                        <i class="fas fa-chart-pie fa-2x"></i>
                    </div>
                    <h4 class="card-title">Budgeting</h4>
                    <p class="card-text">Create and manage budgets for different categories. Get alerts when you're approaching your limits.</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center p-4">
                    <div class="rounded-circle bg-info text-white d-inline-flex justify-content-center align-items-center mb-3" style="width: 60px; height: 60px;">
                        <i class="fas fa-bullseye fa-2x"></i>
                    </div>
                    <h4 class="card-title">Financial Goals</h4>
                    <p class="card-text">Set and track progress toward your financial goals. Whether it's saving for a home, a vacation, or retirement.</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center p-4">
                    <div class="rounded-circle bg-warning text-white d-inline-flex justify-content-center align-items-center mb-3" style="width: 60px; height: 60px;">
                        <i class="fas fa-chart-line fa-2x"></i>
                    </div>
                    <h4 class="card-title">Investment Tracking</h4>
                    <p class="card-text">Monitor your investments and analyze their performance over time. Make informed decisions about your portfolio.</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center p-4">
                    <div class="rounded-circle bg-danger text-white d-inline-flex justify-content-center align-items-center mb-3" style="width: 60px; height: 60px;">
                        <i class="fas fa-exchange-alt fa-2x"></i>
                    </div>
                    <h4 class="card-title">Stock Analysis</h4>
                    <p class="card-text">Analyze stocks and identify optimal buying and selling points. Keep track of your stock watchlist.</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center p-4">
                    <div class="rounded-circle bg-secondary text-white d-inline-flex justify-content-center align-items-center mb-3" style="width: 60px; height: 60px;">
                        <i class="fas fa-file-invoice-dollar fa-2x"></i>
                    </div>
                    <h4 class="card-title">Tax Planning</h4>
                    <p class="card-text">Get assistance with tax planning and estimation. Maximize your deductions and minimize your tax burden.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- How It Works Section -->
<div class="bg-light py-5">
    <div class="container py-3">
        <div class="text-center mb-5">
            <h2 class="fw-bold">How It Works</h2>
            <p class="text-muted">Simple steps to financial freedom</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center p-4">
                        <div class="bg-primary text-white rounded-circle d-flex justify-content-center align-items-center mx-auto mb-3" style="width: 50px; height: 50px;">
                            <h4 class="mb-0">1</h4>
                        </div>
                        <h5 class="card-title">Sign Up</h5>
                        <p class="card-text">Create your free account and set up your profile in minutes.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center p-4">
                        <div class="bg-primary text-white rounded-circle d-flex justify-content-center align-items-center mx-auto mb-3" style="width: 50px; height: 50px;">
                            <h4 class="mb-0">2</h4>
                        </div>
                        <h5 class="card-title">Add Income & Expenses</h5>
                        <p class="card-text">Add your income sources and begin tracking your expenses.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center p-4">
                        <div class="bg-primary text-white rounded-circle d-flex justify-content-center align-items-center mx-auto mb-3" style="width: 50px; height: 50px;">
                            <h4 class="mb-0">3</h4>
                        </div>
                        <h5 class="card-title">Set Budgets & Goals</h5>
                        <p class="card-text">Create budgets for different categories and set your financial goals.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center p-4">
                        <div class="bg-primary text-white rounded-circle d-flex justify-content-center align-items-center mx-auto mb-3" style="width: 50px; height: 50px;">
                            <h4 class="mb-0">4</h4>
                        </div>
                        <h5 class="card-title">Track & Improve</h5>
                        <p class="card-text">Monitor your progress and use insights to improve your financial health.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Testimonials Section -->
<div class="container py-5">
    <div class="text-center mb-5">
        <h2 class="fw-bold">What Our Users Say</h2>
        <p class="text-muted">Join thousands of satisfied users managing their finances with iGotMoney</p>
    </div>
    
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex mb-3">
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                    </div>
                    <p class="card-text mb-4">"iGotMoney has completely transformed how I manage my finances. I've paid off my debt and started saving for my first home!"</p>
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-primary text-white d-flex justify-content-center align-items-center me-3" style="width: 40px; height: 40px;">
                            <span class="fw-bold">JD</span>
                        </div>
                        <div>
                            <h6 class="mb-0">John Doe</h6>
                            <small class="text-muted">Small Business Owner</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex mb-3">
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                    </div>
                    <p class="card-text mb-4">"The budgeting and goal-setting features are fantastic. I've finally been able to stick to a budget and save consistently!"</p>
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-success text-white d-flex justify-content-center align-items-center me-3" style="width: 40px; height: 40px;">
                            <span class="fw-bold">JS</span>
                        </div>
                        <div>
                            <h6 class="mb-0">Jane Smith</h6>
                            <small class="text-muted">Marketing Specialist</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex mb-3">
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                    </div>
                    <p class="card-text mb-4">"The investment tracking and stock analysis tools have helped me make better investment decisions and grow my portfolio."</p>
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-info text-white d-flex justify-content-center align-items-center me-3" style="width: 40px; height: 40px;">
                            <span class="fw-bold">RJ</span>
                        </div>
                        <div>
                            <h6 class="mb-0">Robert Johnson</h6>
                            <small class="text-muted">Software Engineer</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CTA Section -->
<div class="bg-primary text-white py-5">
    <div class="container py-3">
        <div class="row align-items-center">
            <div class="col-lg-8 mb-4 mb-lg-0">
                <h2 class="fw-bold">Ready to Take Control of Your Finances?</h2>
                <p class="lead mb-0">Join thousands of users who have improved their financial health with iGotMoney.</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="<?php echo BASE_PATH; ?>/register" class="btn btn-light btn-lg px-4">Get Started for Free</a>
            </div>
        </div>
    </div>
</div>

<!-- Custom styles for homepage -->
<style>
    .hero-section {
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
    }
    
    .rounded-circle {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    body {
        overflow-x: hidden;
    }
</style>

<?php
// Include footer
require_once 'includes/footer.php';
?>