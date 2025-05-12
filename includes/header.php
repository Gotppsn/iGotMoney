<!DOCTYPE html>
<html lang="<?php echo isset($language) ? $language->getCurrentLanguage() : 'en'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="base-path" content="<?php echo BASE_PATH; ?>">
    <title><?php echo $page_title ?? (__('app_name') . ' - ' . __('financial_management')); ?></title>
    
    <!-- Favicon -->
    <link rel="icon" href="<?php echo BASE_PATH; ?>/assets/images/favicon.ico" type="image/x-icon">
    <link rel="shortcut icon" href="<?php echo BASE_PATH; ?>/assets/images/favicon.ico" type="image/x-icon">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Fonts and Typography -->
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>/assets/css/fonts.css">
    
    <!-- Chart.js - Load early for budget page -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>/assets/css/style.css">
    
    <?php if (isset($additional_css)): ?>
        <?php foreach ($additional_css as $css): ?>
            <link rel="stylesheet" href="<?php echo BASE_PATH . $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
        <!-- Top Navigation -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
            <div class="container">
                <a class="navbar-brand" href="<?php echo BASE_PATH; ?>/dashboard">
                    <i class="fa fa-money-bill-wave me-2"></i><?php echo __('app_name'); ?>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <!-- Language Selector Dropdown -->
                        <li class="nav-item dropdown me-2">
                            <a class="nav-link dropdown-toggle" href="#" id="languageDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-globe me-1"></i> <?php echo __('language'); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="languageDropdown">
                                <?php foreach ($language->getSupportedLanguages() as $code => $name): ?>
                                <li>
                                    <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
                                        <a class="dropdown-item <?php echo $language->getCurrentLanguage() === $code ? 'active' : ''; ?>" 
                                        href="<?php echo BASE_PATH; ?>/?quick_lang=<?php echo $code; ?>">
                                            <?php echo $name; ?>
                                        </a>
                                    <?php else: ?>
                                        <a class="dropdown-item <?php echo $language->getCurrentLanguage() === $code ? 'active' : ''; ?>" 
                                        href="<?php echo BASE_PATH . $current_url . $separator; ?>lang=<?php echo $code; ?>">
                                            <?php echo $name; ?>
                                        </a>
                                    <?php endif; ?>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </li>
                        
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa fa-user-circle me-1"></i> <?php echo htmlspecialchars($_SESSION['username']); ?>
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="userDropdown">
                                <li><a class="dropdown-item" href="<?php echo BASE_PATH; ?>/profile"><i class="fa fa-id-card me-1"></i> <?php echo __('profile'); ?></a></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_PATH; ?>/settings"><i class="fa fa-cog me-1"></i> <?php echo __('settings'); ?></a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_PATH; ?>/logout"><i class="fa fa-sign-out-alt me-1"></i> <?php echo __('logout'); ?></a></li>
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
                                <a class="nav-link <?php echo ($current_page ?? '') === 'dashboard' ? 'active' : ''; ?>" href="<?php echo BASE_PATH; ?>/dashboard">
                                    <i class="fa fa-tachometer-alt me-2"></i> <?php echo __('dashboard'); ?>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo ($current_page ?? '') === 'income' ? 'active' : ''; ?>" href="<?php echo BASE_PATH; ?>/income">
                                    <i class="fa fa-wallet me-2"></i> <?php echo __('income'); ?>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo ($current_page ?? '') === 'expenses' ? 'active' : ''; ?>" href="<?php echo BASE_PATH; ?>/expenses">
                                    <i class="fa fa-credit-card me-2"></i> <?php echo __('expenses'); ?>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo ($current_page ?? '') === 'budget' ? 'active' : ''; ?>" href="<?php echo BASE_PATH; ?>/budget">
                                    <i class="fa fa-chart-pie me-2"></i> <?php echo __('budget'); ?>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo ($current_page ?? '') === 'investments' ? 'active' : ''; ?>" href="<?php echo BASE_PATH; ?>/investments">
                                    <i class="fa fa-chart-line me-2"></i> <?php echo __('investments'); ?>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo ($current_page ?? '') === 'stocks' ? 'active' : ''; ?>" href="<?php echo BASE_PATH; ?>/stocks">
                                    <i class="fa fa-exchange-alt me-2"></i> <?php echo __('stock_analysis'); ?>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo ($current_page ?? '') === 'goals' ? 'active' : ''; ?>" href="<?php echo BASE_PATH; ?>/goals">
                                    <i class="fa fa-bullseye me-2"></i> <?php echo __('financial_goals'); ?>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo ($current_page ?? '') === 'taxes' ? 'active' : ''; ?>" href="<?php echo BASE_PATH; ?>/taxes">
                                    <i class="fa fa-file-invoice-dollar me-2"></i> <?php echo __('tax_planning'); ?>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo ($current_page ?? '') === 'reports' ? 'active' : ''; ?>" href="<?php echo BASE_PATH; ?>/reports">
                                    <i class="fa fa-chart-bar me-2"></i> <?php echo __('reports'); ?>
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
                <a class="navbar-brand" href="<?php echo BASE_PATH; ?>/">
                    <i class="fa fa-money-bill-wave me-2"></i><?php echo __('app_name'); ?>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <!-- Language Selector for Non-Logged Users -->
                        <li class="nav-item dropdown me-2">
                            <a class="nav-link dropdown-toggle" href="#" id="languageDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-globe me-1"></i> <?php echo __('language'); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="languageDropdown">
                                <?php foreach ($language->getSupportedLanguages() as $code => $name): ?>
                                <li>
                                    <a class="dropdown-item <?php echo $language->getCurrentLanguage() === $code ? 'active' : ''; ?>" 
                                       href="<?php echo BASE_PATH; ?>/?lang=<?php echo $code; ?>">
                                        <?php echo $name; ?>
                                    </a>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($current_page ?? '') === 'login' ? 'active' : ''; ?>" href="<?php echo BASE_PATH; ?>/login"><?php echo __('login'); ?></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($current_page ?? '') === 'register' ? 'active' : ''; ?>" href="<?php echo BASE_PATH; ?>/register"><?php echo __('register'); ?></a>
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