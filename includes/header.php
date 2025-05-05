<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'iGotMoney - Financial Management'; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/assets/css/style.css">
    
    <?php if (isset($additional_css)): ?>
        <?php foreach ($additional_css as $css): ?>
            <link rel="stylesheet" href="<?php echo $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
        <!-- Top Navigation -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
            <div class="container">
                <a class="navbar-brand" href="/dashboard">
                    <i class="fas fa-money-bill-wave me-2"></i>iGotMoney
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user-circle me-1"></i> <?php echo htmlspecialchars($_SESSION['username']); ?>
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="userDropdown">
                                <li><a class="dropdown-item" href="/profile"><i class="fas fa-id-card me-1"></i> Profile</a></li>
                                <li><a class="dropdown-item" href="/settings"><i class="fas fa-cog me-1"></i> Settings</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="/logout"><i class="fas fa-sign-out-alt me-1"></i> Logout</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        
        <!-- Sidebar and Main Content Container -->
        <div class="container-fluid">
            <div class="row">
                <!-- Sidebar -->
                <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-light sidebar py-3">
                    <div class="position-sticky">
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link <?php echo ($current_page ?? '') === 'dashboard' ? 'active' : ''; ?>" href="/dashboard">
                                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo ($current_page ?? '') === 'income' ? 'active' : ''; ?>" href="/income">
                                    <i class="fas fa-wallet me-2"></i> Income
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo ($current_page ?? '') === 'expenses' ? 'active' : ''; ?>" href="/expenses">
                                    <i class="fas fa-credit-card me-2"></i> Expenses
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo ($current_page ?? '') === 'budget' ? 'active' : ''; ?>" href="/budget">
                                    <i class="fas fa-chart-pie me-2"></i> Budget
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo ($current_page ?? '') === 'investments' ? 'active' : ''; ?>" href="/investments">
                                    <i class="fas fa-chart-line me-2"></i> Investments
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo ($current_page ?? '') === 'stocks' ? 'active' : ''; ?>" href="/stocks">
                                    <i class="fas fa-exchange-alt me-2"></i> Stock Analysis
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo ($current_page ?? '') === 'goals' ? 'active' : ''; ?>" href="/goals">
                                    <i class="fas fa-bullseye me-2"></i> Financial Goals
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo ($current_page ?? '') === 'taxes' ? 'active' : ''; ?>" href="/taxes">
                                    <i class="fas fa-file-invoice-dollar me-2"></i> Tax Planning
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo ($current_page ?? '') === 'reports' ? 'active' : ''; ?>" href="/reports">
                                    <i class="fas fa-chart-bar me-2"></i> Reports
                                </a>
                            </li>
                        </ul>
                    </div>
                </nav>
                
                <!-- Main Content -->
                <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
    <?php else: ?>
        <!-- Simple navbar for non-logged in users -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
            <div class="container">
                <a class="navbar-brand" href="/">
                    <i class="fas fa-money-bill-wave me-2"></i>iGotMoney
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($current_page ?? '') === 'login' ? 'active' : ''; ?>" href="/login">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($current_page ?? '') === 'register' ? 'active' : ''; ?>" href="/register">Register</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        
        <!-- Main Content Container -->
        <div class="container mt-4">
    <?php endif; ?>

    <?php if (isset($success)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $success; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>