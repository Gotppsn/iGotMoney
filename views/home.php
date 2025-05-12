<?php
// Set page title
$page_title = __('home_title');

// Include header
require_once 'includes/header.php';
?>

<!-- Hero Section -->
<div class="hero-section">
    <div class="container py-5">
        <div class="row align-items-center">
            <div class="col-lg-7 hero-content">
                <span class="badge bg-primary-light text-primary mb-3"><?php echo __('hero_badge'); ?></span>
                <h1 class="display-4 fw-bold mb-4"><?php echo __('hero_heading'); ?></h1>
                <p class="lead mb-4"><?php echo __('hero_description'); ?></p>
                <div class="d-flex gap-3 hero-buttons">
                    <a href="<?php echo BASE_PATH; ?>/register" class="btn btn-primary btn-lg"><?php echo __('get_started_button'); ?></a>
                    <a href="#features" class="btn btn-outline-primary btn-lg"><?php echo __('learn_more_button'); ?></a>
                </div>
            </div>
            <div class="col-lg-5 d-none d-lg-block">
                <div class="hero-shape">
                    <div class="hero-shape-1"></div>
                    <div class="hero-shape-2"></div>
                    <div class="hero-shape-3"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Features Section -->
<div id="features" class="container py-5">
    <div class="text-center mb-5">
        <span class="badge bg-primary-light text-primary mb-2"><?php echo __('features_badge'); ?></span>
        <h2 class="fw-bold"><?php echo __('features_heading'); ?></h2>
        <p class="text-muted mx-auto" style="max-width: 700px;"><?php echo __('features_description'); ?></p>
    </div>
    
    <div class="row g-4">
        <div class="col-md-4">
            <div class="feature-card h-100">
                <div class="feature-icon bg-primary-light">
                    <i class="fas fa-wallet"></i>
                </div>
                <div class="feature-content">
                    <h4><?php echo __('income_expense_tracking_title'); ?></h4>
                    <p><?php echo __('income_expense_tracking_description'); ?></p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="feature-card h-100">
                <div class="feature-icon bg-success-light">
                    <i class="fas fa-chart-pie"></i>
                </div>
                <div class="feature-content">
                    <h4><?php echo __('budgeting_title'); ?></h4>
                    <p><?php echo __('budgeting_description'); ?></p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="feature-card h-100">
                <div class="feature-icon bg-info-light">
                    <i class="fas fa-bullseye"></i>
                </div>
                <div class="feature-content">
                    <h4><?php echo __('financial_goals_title'); ?></h4>
                    <p><?php echo __('financial_goals_description'); ?></p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="feature-card h-100">
                <div class="feature-icon bg-warning-light">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="feature-content">
                    <h4><?php echo __('investment_tracking_title'); ?></h4>
                    <p><?php echo __('investment_tracking_description'); ?></p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="feature-card h-100">
                <div class="feature-icon bg-danger-light">
                    <i class="fas fa-exchange-alt"></i>
                </div>
                <div class="feature-content">
                    <h4><?php echo __('stock_analysis_title'); ?></h4>
                    <p><?php echo __('stock_analysis_description'); ?></p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="feature-card h-100">
                <div class="feature-icon bg-secondary-light">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
                <div class="feature-content">
                    <h4><?php echo __('tax_planning_title'); ?></h4>
                    <p><?php echo __('tax_planning_description'); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- How It Works Section -->
<div class="how-it-works-section py-5">
    <div class="container py-3">
        <div class="text-center mb-5">
            <span class="badge bg-primary-light text-primary mb-2"><?php echo __('how_it_works_badge'); ?></span>
            <h2 class="fw-bold"><?php echo __('how_it_works_heading'); ?></h2>
            <p class="text-muted mx-auto" style="max-width: 700px;"><?php echo __('how_it_works_description'); ?></p>
        </div>
        
        <div class="steps-container">
            <div class="steps-line"></div>
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="step-card">
                        <div class="step-number">1</div>
                        <h5 class="step-title"><?php echo __('step_1_title'); ?></h5>
                        <p class="step-description"><?php echo __('step_1_description'); ?></p>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="step-card">
                        <div class="step-number">2</div>
                        <h5 class="step-title"><?php echo __('step_2_title'); ?></h5>
                        <p class="step-description"><?php echo __('step_2_description'); ?></p>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="step-card">
                        <div class="step-number">3</div>
                        <h5 class="step-title"><?php echo __('step_3_title'); ?></h5>
                        <p class="step-description"><?php echo __('step_3_description'); ?></p>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="step-card">
                        <div class="step-number">4</div>
                        <h5 class="step-title"><?php echo __('step_4_title'); ?></h5>
                        <p class="step-description"><?php echo __('step_4_description'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="text-center mt-5">
        <a href="<?php echo BASE_PATH; ?>/register" class="btn btn-primary btn-lg"><?php echo __('get_started_free_button'); ?></a>
    </div>
</div>

<!-- Custom styles for homepage -->
<style>
    /* Hero Section */
    .hero-section {
        background: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%);
        color: white;
        position: relative;
        overflow: hidden;
        padding: 5rem 0;
        margin-bottom: 2rem;
        border-radius: 0 0 2rem 2rem;
    }
    
    .hero-content {
        position: relative;
        z-index: 2;
    }
    
    .badge {
        font-size: 0.85rem;
        font-weight: 500;
        padding: 0.5rem 1rem;
        border-radius: 2rem;
    }
    
    .bg-primary-light {
        background-color: rgba(255, 255, 255, 0.15);
    }
    
    .hero-buttons .btn {
        padding: 0.75rem 1.75rem;
        border-radius: 0.5rem;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .hero-buttons .btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }
    
    .hero-shape {
        position: relative;
        height: 400px;
    }
    
    .hero-shape-1,
    .hero-shape-2,
    .hero-shape-3 {
        position: absolute;
        border-radius: 1rem;
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(5px);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    }
    
    .hero-shape-1 {
        width: 200px;
        height: 200px;
        top: 20%;
        left: 20%;
        transform: rotate(15deg);
        animation: float 6s ease-in-out infinite;
    }
    
    .hero-shape-2 {
        width: 150px;
        height: 150px;
        top: 50%;
        left: 50%;
        transform: rotate(-15deg);
        animation: float 8s ease-in-out infinite;
        animation-delay: 1s;
    }
    
    .hero-shape-3 {
        width: 100px;
        height: 100px;
        top: 30%;
        left: 60%;
        transform: rotate(45deg);
        animation: float 7s ease-in-out infinite;
        animation-delay: 2s;
    }
    
    @keyframes float {
        0% { transform: translateY(0) rotate(0); }
        50% { transform: translateY(-20px) rotate(5deg); }
        100% { transform: translateY(0) rotate(0); }
    }
    
    /* Feature Cards */
    .feature-card {
        background-color: white;
        border-radius: 1rem;
        padding: 1.5rem;
        transition: all 0.3s ease;
        border: none;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    }
    
    .feature-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
    }
    
    .feature-icon {
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        border-radius: 1rem;
        margin-bottom: 1.25rem;
    }
    
    .bg-success-light {
        background-color: rgba(46, 204, 113, 0.1);
        color: #2ecc71;
    }
    
    .bg-info-light {
        background-color: rgba(52, 152, 219, 0.1);
        color: #3498db;
    }
    
    .bg-warning-light {
        background-color: rgba(243, 156, 18, 0.1);
        color: #f39c12;
    }
    
    .bg-danger-light {
        background-color: rgba(231, 76, 60, 0.1);
        color: #e74c3c;
    }
    
    .bg-secondary-light {
        background-color: rgba(108, 117, 125, 0.1);
        color: #6c757d;
    }
    
    .feature-content h4 {
        font-size: 1.25rem;
        font-weight: 600;
        margin-bottom: 0.75rem;
        color: #333;
    }
    
    .feature-content p {
        color: #6c757d;
        margin-bottom: 0;
    }
    
    /* How It Works Section */
    .how-it-works-section {
        background-color: #f8f9fa;
        position: relative;
        border-radius: 2rem;
        margin: 2rem 0;
    }
    
    .steps-container {
        position: relative;
        padding: 1rem 0;
    }
    
    .steps-line {
        position: absolute;
        top: 50%;
        left: 10%;
        right: 10%;
        height: 3px;
        background-color: #e9ecef;
        z-index: 1;
    }
    
    .step-card {
        background-color: white;
        border-radius: 1rem;
        padding: 2rem 1.5rem;
        position: relative;
        z-index: 2;
        text-align: center;
        transition: all 0.3s ease;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        height: 100%;
    }
    
    .step-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
    }
    
    .step-number {
        width: 50px;
        height: 50px;
        background-color: #4361ee;
        color: white;
        font-size: 1.25rem;
        font-weight: 700;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.25rem;
    }
    
    .step-title {
        font-weight: 600;
        margin-bottom: 0.75rem;
        color: #333;
    }
    
    .step-description {
        color: #6c757d;
        margin-bottom: 0;
    }
    
    /* Responsive adjustments */
    @media (max-width: 992px) {
        .hero-section {
            padding: 3rem 0;
        }
        
        .hero-shape {
            display: none;
        }
        
        .steps-line {
            display: none;
        }
    }
    
    @media (max-width: 768px) {
        .step-card {
            margin-bottom: 1.5rem;
        }
    }
</style>

<?php
// Include footer
require_once 'includes/footer.php';
?>