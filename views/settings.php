<?php
// Set page title and current page for menu highlighting
$page_title = 'Settings - iGotMoney';
$current_page = 'settings';

// Additional CSS and JS
$additional_css = ['/assets/css/settings-modern.css'];
$additional_js = ['/assets/js/settings-modern.js'];

// Include header
require_once 'includes/header.php';
?>

<div class="settings-page">
    <!-- Page Header -->
    <div class="page-header-section">
        <div class="page-header-content">
            <div class="page-title-group">
                <h1 class="page-title">Settings</h1>
                <p class="page-subtitle">Manage your account preferences and security settings</p>
            </div>
        </div>
    </div>

    <div class="settings-grid">
        <!-- Settings Navigation -->
        <div class="settings-nav-card">
            <div class="settings-nav">
                <div class="nav-pills" role="tablist">
                    <button class="nav-link active" data-bs-target="#profile" type="button" role="tab">
                        <i class="fas fa-user-circle"></i>
                        Profile
                    </button>
                    <button class="nav-link" data-bs-target="#security" type="button" role="tab">
                        <i class="fas fa-lock"></i>
                        Security
                    </button>
                    <button class="nav-link" data-bs-target="#preferences" type="button" role="tab">
                        <i class="fas fa-cog"></i>
                        Preferences
                    </button>
                    <button class="nav-link" data-bs-target="#notifications" type="button" role="tab">
                        <i class="fas fa-bell"></i>
                        Notifications
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Settings Content -->
        <div class="settings-content">
            <div class="tab-content">
                <!-- Profile Settings -->
                <div class="tab-pane active" id="profile" role="tabpanel">
                    <div class="settings-card">
                        <div class="settings-card-header">
                            <div class="header-icon">
                                <i class="fas fa-user-circle"></i>
                            </div>
                            <div class="header-content">
                                <h2 class="card-title">Profile Information</h2>
                                <p class="card-description">Update your account information and profile details</p>
                            </div>
                        </div>
                        <div class="settings-card-body">
                            <form action="/settings" method="post" class="settings-form needs-validation" novalidate>
                                <input type="hidden" name="action" value="update_profile">
                                
                                <div class="form-grid">
                                    <div class="form-field">
                                        <label for="username" class="form-label">
                                            <i class="fas fa-user"></i>
                                            Username
                                        </label>
                                        <input type="text" class="form-control" id="username" value="<?php echo htmlspecialchars($user->username); ?>" readonly>
                                        <small class="form-text">Username cannot be changed.</small>
                                    </div>
                                    
                                    <div class="form-field">
                                        <label for="email" class="form-label">
                                            <i class="fas fa-envelope"></i>
                                            Email Address
                                        </label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user->email); ?>" required>
                                    </div>
                                    
                                    <div class="form-field">
                                        <label for="first_name" class="form-label">
                                            <i class="fas fa-signature"></i>
                                            First Name
                                        </label>
                                        <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user->first_name); ?>">
                                    </div>
                                    
                                    <div class="form-field">
                                        <label for="last_name" class="form-label">
                                            <i class="fas fa-signature"></i>
                                            Last Name
                                        </label>
                                        <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user->last_name); ?>">
                                    </div>
                                </div>
                                
                                <div class="btn-group">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i>
                                        Update Profile
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Security Settings -->
                <div class="tab-pane" id="security" role="tabpanel">
                    <div class="settings-card">
                        <div class="settings-card-header">
                            <div class="header-icon">
                                <i class="fas fa-key"></i>
                            </div>
                            <div class="header-content">
                                <h2 class="card-title">Change Password</h2>
                                <p class="card-description">Update your password to keep your account secure</p>
                            </div>
                        </div>
                        <div class="settings-card-body">
                            <form action="/settings" method="post" class="settings-form needs-validation" novalidate>
                                <input type="hidden" name="action" value="change_password">
                                
                                <div class="form-grid">
                                    <div class="form-field full-width">
                                        <label for="current_password" class="form-label">
                                            <i class="fas fa-lock"></i>
                                            Current Password
                                        </label>
                                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                                    </div>
                                    
                                    <div class="form-field">
                                        <label for="new_password" class="form-label">
                                            <i class="fas fa-unlock"></i>
                                            New Password
                                        </label>
                                        <input type="password" class="form-control" id="new_password" name="new_password" required minlength="6">
                                        <small class="form-text">Password must be at least 6 characters long.</small>
                                    </div>
                                    
                                    <div class="form-field">
                                        <label for="confirm_password" class="form-label">
                                            <i class="fas fa-redo"></i>
                                            Confirm New Password
                                        </label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                    </div>
                                </div>
                                
                                <div class="btn-group">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-shield-alt"></i>
                                        Change Password
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
                                <h2 class="card-title">Account Security</h2>
                                <p class="card-description">Keep your account safe with these security best practices</p>
                            </div>
                        </div>
                        <div class="settings-card-body">
                            <div class="security-list">
                                <div class="security-item">
                                    <i class="fas fa-check-circle"></i>
                                    <span>Use a strong, unique password</span>
                                </div>
                                <div class="security-item">
                                    <i class="fas fa-check-circle"></i>
                                    <span>Never share your login information</span>
                                </div>
                                <div class="security-item">
                                    <i class="fas fa-check-circle"></i>
                                    <span>Log out when using shared devices</span>
                                </div>
                                <div class="security-item">
                                    <i class="fas fa-check-circle"></i>
                                    <span>Regularly update your password</span>
                                </div>
                            </div>
                            
                            <div class="last-login">
                                <i class="fas fa-clock"></i>
                                <p>Last login: <strong><?php echo date('M j, Y h:i A'); ?></strong></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Preferences Settings -->
                <div class="tab-pane" id="preferences" role="tabpanel">
                    <div class="settings-card">
                        <div class="settings-card-header">
                            <div class="header-icon">
                                <i class="fas fa-palette"></i>
                            </div>
                            <div class="header-content">
                                <h2 class="card-title">App Preferences</h2>
                                <p class="card-description">Customize your app experience and display preferences</p>
                            </div>
                        </div>
                        <div class="settings-card-body">
                            <form action="/settings" method="post" class="settings-form needs-validation" novalidate>
                                <input type="hidden" name="action" value="update_settings">
                                
                                <div class="form-grid">
                                    <div class="form-field">
                                        <label for="currency" class="form-label">
                                            <i class="fas fa-money-bill-wave"></i>
                                            Currency
                                        </label>
                                        <select class="form-select" id="currency" name="currency">
                                            <?php foreach ($available_currencies as $code => $name): ?>
                                                <option value="<?php echo $code; ?>" <?php echo ($settings->currency === $code) ? 'selected' : ''; ?>>
                                                    <?php echo $name; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <small class="form-text">Select your preferred currency for displaying financial data.</small>
                                    </div>
                                    
                                    <div class="form-field">
                                        <label for="theme" class="form-label">
                                            <i class="fas fa-palette"></i>
                                            Theme
                                        </label>
                                        <select class="form-select" id="theme" name="theme">
                                            <?php foreach ($available_themes as $code => $name): ?>
                                                <option value="<?php echo $code; ?>" <?php echo ($settings->theme === $code) ? 'selected' : ''; ?>>
                                                    <?php echo $name; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <small class="form-text">Choose the display theme for the application.</small>
                                    </div>
                                    
                                    <div class="form-field full-width">
                                        <label for="budget_alert_threshold" class="form-label">
                                            <i class="fas fa-chart-line"></i>
                                            Budget Alert Threshold
                                        </label>
                                        <div class="range-field">
                                            <input type="range" class="range-input" id="budget_alert_threshold" name="budget_alert_threshold" 
                                                   min="50" max="100" step="5" value="<?php echo $settings->budget_alert_threshold; ?>">
                                            <div class="range-labels">
                                                <span>50%</span>
                                                <span class="range-value" id="threshold_value"><?php echo $settings->budget_alert_threshold; ?>%</span>
                                                <span>100%</span>
                                            </div>
                                        </div>
                                        <small class="form-text">Receive alerts when your spending reaches this percentage of your budget.</small>
                                    </div>
                                </div>
                                
                                <div class="btn-group">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i>
                                        Save Preferences
                                    </button>
                                    <button type="button" class="btn btn-secondary" id="resetSettings">
                                        <i class="fas fa-undo"></i>
                                        Reset to Default
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Notification Settings -->
                <div class="tab-pane" id="notifications" role="tabpanel">
                    <div class="settings-card">
                        <div class="settings-card-header">
                            <div class="header-icon">
                                <i class="fas fa-bell"></i>
                            </div>
                            <div class="header-content">
                                <h2 class="card-title">Notification Settings</h2>
                                <p class="card-description">Configure how you receive notifications and alerts</p>
                            </div>
                        </div>
                        <div class="settings-card-body">
                            <form action="/settings" method="post" class="settings-form">
                                <input type="hidden" name="action" value="update_settings">
                                
                                <div class="notification-group">
                                    <div class="notification-item">
                                        <label class="toggle-field notification-toggle">
                                            <input type="checkbox" id="notification_enabled" name="notification_enabled" 
                                                   <?php echo $settings->notification_enabled ? 'checked' : ''; ?>>
                                            <span class="toggle-slider"></span>
                                        </label>
                                        <div class="notification-content">
                                            <h3 class="notification-title">In-App Notifications</h3>
                                            <p class="notification-description">Receive notifications within the application for important alerts.</p>
                                        </div>
                                    </div>
                                    
                                    <div class="notification-item">
                                        <label class="toggle-field notification-toggle">
                                            <input type="checkbox" id="email_notification_enabled" name="email_notification_enabled" 
                                                   <?php echo $settings->email_notification_enabled ? 'checked' : ''; ?>>
                                            <span class="toggle-slider"></span>
                                        </label>
                                        <div class="notification-content">
                                            <h3 class="notification-title">Email Notifications</h3>
                                            <p class="notification-description">Receive email notifications for important alerts and updates.</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="settings-card-header" style="border-bottom: none; padding: 1.5rem 0;">
                                    <div class="header-icon" style="background-color: var(--info-light);">
                                        <i class="fas fa-list-ul" style="color: var(--info-color);"></i>
                                    </div>
                                    <div class="header-content">
                                        <h3 class="card-title">Notification Types</h3>
                                        <p class="card-description">These notifications are always enabled for your security</p>
                                    </div>
                                </div>
                                
                                <div class="notification-group">
                                    <div class="notification-item disabled-checkbox">
                                        <label class="toggle-field notification-toggle">
                                            <input type="checkbox" checked disabled>
                                            <span class="toggle-slider"></span>
                                        </label>
                                        <div class="notification-content">
                                            <h3 class="notification-title">Budget Alerts</h3>
                                            <p class="notification-description">Alerts when you reach your budget threshold.</p>
                                        </div>
                                    </div>
                                    
                                    <div class="notification-item disabled-checkbox">
                                        <label class="toggle-field notification-toggle">
                                            <input type="checkbox" checked disabled>
                                            <span class="toggle-slider"></span>
                                        </label>
                                        <div class="notification-content">
                                            <h3 class="notification-title">Bill Reminders</h3>
                                            <p class="notification-description">Reminders for upcoming bill payments.</p>
                                        </div>
                                    </div>
                                    
                                    <div class="notification-item disabled-checkbox">
                                        <label class="toggle-field notification-toggle">
                                            <input type="checkbox" checked disabled>
                                            <span class="toggle-slider"></span>
                                        </label>
                                        <div class="notification-content">
                                            <h3 class="notification-title">Goal Progress</h3>
                                            <p class="notification-description">Updates on your financial goal progress.</p>
                                        </div>
                                    </div>
                                    
                                    <div class="notification-item disabled-checkbox">
                                        <label class="toggle-field notification-toggle">
                                            <input type="checkbox" checked disabled>
                                            <span class="toggle-slider"></span>
                                        </label>
                                        <div class="notification-content">
                                            <h3 class="notification-title">Security Alerts</h3>
                                            <p class="notification-description">Alerts about account security issues.</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="btn-group">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i>
                                        Save Notification Settings
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
<div class="modal fade modern-modal" id="resetSettingsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h5 class="modal-title">Reset Settings</h5>
                <button type="button" class="modal-close" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to reset all settings to their default values?</p>
                <p class="text-muted">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="resetSettingsForm" action="/settings" method="post" style="display: inline;">
                    <input type="hidden" name="action" value="reset_settings">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-undo"></i>
                        Reset Settings
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>