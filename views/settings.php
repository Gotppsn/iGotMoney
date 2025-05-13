<?php
// Set page title and current page for menu highlighting
$page_title = __('settings') . ' - ' . __('app_name');
$current_page = 'settings';

// Additional CSS and JS
$additional_css = ['/assets/css/settings-modern.css', '/assets/css/currency.css'];
$additional_js = ['/assets/js/settings-modern.js'];

// Include formatter helper
require_once 'includes/formatter.php';

// Add page-specific script for tab handling
$page_scripts = '
    document.addEventListener("DOMContentLoaded", function() {
        console.log("Settings page initialized");
    });
';

// Include header
require_once 'includes/header.php';
?>

<div class="settings-page">
    <!-- Page Header -->
    <div class="page-header-section">
        <div class="page-header-content">
            <div class="page-title-group">
                <h1 class="page-title"><?php echo __('settings'); ?></h1>
                <p class="page-subtitle"><?php echo __('manage_your_account_preferences'); ?></p>
            </div>
        </div>
    </div>

    <div class="settings-grid">
        <!-- Settings Navigation -->
        <div class="settings-nav-card">
            <div class="settings-nav">
                <div class="nav-pills" role="tablist">
                    <button class="nav-link active" id="profile-tab" type="button" role="tab">
                        <i class="fas fa-user-circle"></i>
                        <?php echo __('profile'); ?>
                    </button>
                    <button class="nav-link" id="security-tab" type="button" role="tab">
                        <i class="fas fa-lock"></i>
                        <?php echo __('security'); ?>
                    </button>
                    <button class="nav-link" id="preferences-tab" type="button" role="tab">
                        <i class="fas fa-cog"></i>
                        <?php echo __('preferences'); ?>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Settings Content -->
        <div class="settings-content">
            <div class="tab-content">
                <!-- Profile Settings -->
                <div class="tab-pane active" id="profile" role="tabpanel" style="display: block;">
                    <div class="settings-card">
                        <div class="settings-card-header">
                            <div class="header-icon">
                                <i class="fas fa-user-circle"></i>
                            </div>
                            <div class="header-content">
                                <h2 class="card-title"><?php echo __('profile_information'); ?></h2>
                                <p class="card-description"><?php echo __('update_your_account_information'); ?></p>
                            </div>
                        </div>
                        <div class="settings-card-body">
                            <form action="<?php echo BASE_PATH; ?>/settings" method="post" class="settings-form needs-validation" novalidate>
                                <input type="hidden" name="action" value="update_profile">
                                
                                <div class="form-grid">
                                    <div class="form-field">
                                        <label for="username" class="form-label">
                                            <i class="fas fa-user"></i>
                                            <?php echo __('username'); ?>
                                        </label>
                                        <input type="text" class="form-control" id="username" value="<?php echo htmlspecialchars($user->username); ?>" readonly>
                                        <small class="form-text"><?php echo __('username_cannot_be_changed'); ?></small>
                                    </div>
                                    
                                    <div class="form-field">
                                        <label for="email" class="form-label">
                                            <i class="fas fa-envelope"></i>
                                            <?php echo __('email'); ?>
                                        </label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user->email); ?>" required>
                                    </div>
                                    
                                    <div class="form-field">
                                        <label for="first_name" class="form-label">
                                            <i class="fas fa-signature"></i>
                                            <?php echo __('first_name'); ?>
                                        </label>
                                        <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user->first_name); ?>">
                                    </div>
                                    
                                    <div class="form-field">
                                        <label for="last_name" class="form-label">
                                            <i class="fas fa-signature"></i>
                                            <?php echo __('last_name'); ?>
                                        </label>
                                        <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user->last_name); ?>">
                                    </div>
                                </div>
                                
                                <div class="btn-group">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i>
                                        <?php echo __('update_profile'); ?>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Security Settings -->
                <div class="tab-pane" id="security" role="tabpanel" style="display: none;">
                    <div class="settings-card">
                        <div class="settings-card-header">
                            <div class="header-icon">
                                <i class="fas fa-key"></i>
                            </div>
                            <div class="header-content">
                                <h2 class="card-title"><?php echo __('change_password'); ?></h2>
                                <p class="card-description"><?php echo __('update_your_password'); ?></p>
                            </div>
                        </div>
                        <div class="settings-card-body">
                            <form action="<?php echo BASE_PATH; ?>/settings" method="post" class="settings-form needs-validation" novalidate>
                                <input type="hidden" name="action" value="change_password">
                                
                                <div class="form-grid">
                                    <div class="form-field full-width">
                                        <label for="current_password" class="form-label">
                                            <i class="fas fa-lock"></i>
                                            <?php echo __('current_password'); ?>
                                        </label>
                                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                                    </div>
                                    
                                    <div class="form-field">
                                        <label for="new_password" class="form-label">
                                            <i class="fas fa-unlock"></i>
                                            <?php echo __('new_password'); ?>
                                        </label>
                                        <input type="password" class="form-control" id="new_password" name="new_password" required minlength="6">
                                        <small class="form-text"><?php echo __('password_min_length'); ?></small>
                                    </div>
                                    
                                    <div class="form-field">
                                        <label for="confirm_password" class="form-label">
                                            <i class="fas fa-redo"></i>
                                            <?php echo __('confirm_new_password'); ?>
                                        </label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                    </div>
                                </div>
                                
                                <div class="btn-group">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-shield-alt"></i>
                                        <?php echo __('change_password'); ?>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <div class="settings-card">
                        <div class="settings-card-header">
                            <div class="header-icon">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <div class="header-content">
                                <h2 class="card-title"><?php echo __('account_security'); ?></h2>
                                <p class="card-description"><?php echo __('security_best_practices'); ?></p>
                            </div>
                        </div>
                        <div class="settings-card-body">
                            <div class="security-list">
                                <div class="security-item">
                                    <i class="fas fa-check-circle"></i>
                                    <span><?php echo __('use_strong_password'); ?></span>
                                </div>
                                <div class="security-item">
                                    <i class="fas fa-check-circle"></i>
                                    <span><?php echo __('never_share_login'); ?></span>
                                </div>
                                <div class="security-item">
                                    <i class="fas fa-check-circle"></i>
                                    <span><?php echo __('logout_shared_devices'); ?></span>
                                </div>
                                <div class="security-item">
                                    <i class="fas fa-check-circle"></i>
                                    <span><?php echo __('regularly_update_password'); ?></span>
                                </div>
                            </div>
                            
                            <div class="last-login">
                                <i class="fas fa-clock"></i>
                                <p><?php echo __('last_login'); ?>: <strong><?php echo date('M j, Y h:i A'); ?></strong></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Preferences Settings -->
                <div class="tab-pane" id="preferences" role="tabpanel" style="display: none;">
                    <!-- Language Settings Card -->
                    <div class="settings-card">
                        <div class="settings-card-header">
                            <div class="header-icon">
                                <i class="fas fa-globe"></i>
                            </div>
                            <div class="header-content">
                                <h2 class="card-title"><?php echo __('language_settings'); ?></h2>
                                <p class="card-description"><?php echo __('choose_language'); ?></p>
                            </div>
                        </div>
                        <div class="settings-card-body">
                            <form action="<?php echo BASE_PATH; ?>/settings" method="post" class="settings-form needs-validation" novalidate>
                                <input type="hidden" name="action" value="update_settings">
                                
                                <div class="form-grid">
                                    <div class="form-field">
                                        <label for="language" class="form-label">
                                            <i class="fas fa-language"></i>
                                            <?php echo __('language'); ?>
                                        </label>
                                        <select class="form-select" id="language" name="language">
                                            <?php foreach ($available_languages as $code => $name): ?>
                                                <option value="<?php echo $code; ?>" <?php echo ($settings->language === $code) ? 'selected' : ''; ?>>
                                                    <?php echo $name; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <small class="form-text"><?php echo __('language_preference_description'); ?></small>
                                    </div>
                                    
                                    <div class="form-field">
                                        <div class="language-preview">
                                            <h3><?php echo __('language_preview'); ?></h3>
                                            <div class="preview-examples">
                                                <div class="preview-item">
                                                    <span class="preview-label"><?php echo __('dashboard'); ?>:</span>
                                                    <span class="preview-value"><?php echo $settings->language === 'en' ? 'Dashboard' : 'แดชบอร์ด'; ?></span>
                                                </div>
                                                <div class="preview-item">
                                                    <span class="preview-label"><?php echo __('income'); ?>:</span>
                                                    <span class="preview-value"><?php echo $settings->language === 'en' ? 'Income' : 'รายได้'; ?></span>
                                                </div>
                                                <div class="preview-item">
                                                    <span class="preview-label"><?php echo __('expenses'); ?>:</span>
                                                    <span class="preview-value"><?php echo $settings->language === 'en' ? 'Expenses' : 'ค่าใช้จ่าย'; ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="btn-group">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i>
                                        <?php echo __('save_changes'); ?>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Currency Settings Card -->
                    <div class="settings-card">
                        <div class="settings-card-header">
                            <div class="header-icon">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                            <div class="header-content">
                                <h2 class="card-title"><?php echo __('currency_settings'); ?></h2>
                                <p class="card-description"><?php echo __('choose_currency'); ?></p>
                            </div>
                        </div>
                        <div class="settings-card-body">
                            <form action="<?php echo BASE_PATH; ?>/settings" method="post" class="settings-form needs-validation" novalidate>
                                <input type="hidden" name="action" value="update_settings">
                                <input type="hidden" name="language" value="<?php echo htmlspecialchars($settings->language); ?>">
                                
                                <div class="form-grid">
                                    <div class="form-field">
                                        <label for="currency" class="form-label">
                                            <i class="fas fa-money-bill-wave"></i>
                                            <?php echo __('currency'); ?>
                                        </label>
                                        <select class="form-select" id="currency" name="currency">
                                            <?php foreach ($available_currencies as $code => $name): ?>
                                                <option value="<?php echo $code; ?>" <?php echo ($settings->currency === $code) ? 'selected' : ''; ?>>
                                                    <?php echo $name; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <small class="form-text"><?php echo __('currency_preference_description'); ?></small>
                                    </div>
                                    
                                    <div class="form-field">
                                        <div class="currency-preview">
                                            <h3><?php echo __('currency_preview'); ?></h3>
                                            <div class="preview-examples">
                                                <div class="preview-item">
                                                    <span class="preview-label"><?php echo __('income'); ?>:</span>
                                                    <span class="preview-value" id="income-preview" data-value="1000">
                                                        <?php 
                                                        $symbol = $settings->getCurrencySymbol();
                                                        echo $symbol; 
                                                        ?>1,000.00
                                                    </span>
                                                </div>
                                                <div class="preview-item">
                                                    <span class="preview-label"><?php echo __('expenses'); ?>:</span>
                                                    <span class="preview-value" id="expenses-preview" data-value="250.5">
                                                        <?php echo $symbol; ?>250.50
                                                    </span>
                                                </div>
                                                <div class="preview-item">
                                                    <span class="preview-label"><?php echo __('budget'); ?>:</span>
                                                    <span class="preview-value" id="budget-preview" data-value="750">
                                                        <?php echo $symbol; ?>750.00
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="btn-group">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i>
                                        <?php echo __('save_preferences'); ?>
                                    </button>
                                    <button type="button" class="btn btn-secondary" id="resetSettings">
                                        <i class="fas fa-undo"></i>
                                        <?php echo __('reset_to_default'); ?>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reset Settings Modal -->
<div class="modal fade" id="resetSettingsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h5 class="modal-title"><?php echo __('reset_settings'); ?></h5>
                <button type="button" class="modal-close" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <p><?php echo __('confirm_reset_settings_question'); ?></p>
                <p class="text-muted"><?php echo __('action_cannot_be_undone'); ?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo __('cancel'); ?></button>
                <form id="resetSettingsForm" action="<?php echo BASE_PATH; ?>/settings" method="post" style="display: inline;">
                    <input type="hidden" name="action" value="reset_settings">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-undo"></i>
                        <?php echo __('reset_settings'); ?>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>