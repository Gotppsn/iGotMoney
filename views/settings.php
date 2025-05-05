<?php
// Set page title and current page for menu highlighting
$page_title = 'Settings - iGotMoney';
$current_page = 'settings';

// Include header
require_once 'includes/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Settings</h1>
</div>

<div class="row">
    <div class="col-lg-3">
        <!-- Settings Navigation -->
        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="nav flex-column nav-pills" id="settings-tab" role="tablist" aria-orientation="vertical">
                    <button class="nav-link active mb-2" id="profile-tab" data-bs-toggle="pill" data-bs-target="#profile" type="button" role="tab" aria-controls="profile" aria-selected="true">
                        <i class="fas fa-user-circle me-2"></i> Profile
                    </button>
                    <button class="nav-link mb-2" id="security-tab" data-bs-toggle="pill" data-bs-target="#security" type="button" role="tab" aria-controls="security" aria-selected="false">
                        <i class="fas fa-lock me-2"></i> Security
                    </button>
                    <button class="nav-link mb-2" id="preferences-tab" data-bs-toggle="pill" data-bs-target="#preferences" type="button" role="tab" aria-controls="preferences" aria-selected="false">
                        <i class="fas fa-cog me-2"></i> Preferences
                    </button>
                    <button class="nav-link" id="notifications-tab" data-bs-toggle="pill" data-bs-target="#notifications" type="button" role="tab" aria-controls="notifications" aria-selected="false">
                        <i class="fas fa-bell me-2"></i> Notifications
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-9">
        <div class="tab-content" id="settings-tabContent">
            <!-- Profile Settings -->
            <div class="tab-pane fade show active" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Profile Information</h6>
                    </div>
                    <div class="card-body">
                        <form action="/settings" method="post">
                            <input type="hidden" name="action" value="update_profile">
                            
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" value="<?php echo htmlspecialchars($user->username); ?>" readonly>
                                <small class="form-text text-muted">Username cannot be changed.</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user->email); ?>" required>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="first_name" class="form-label">First Name</label>
                                    <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user->first_name); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="last_name" class="form-label">Last Name</label>
                                    <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user->last_name); ?>">
                                </div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Update Profile</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Security Settings -->
            <div class="tab-pane fade" id="security" role="tabpanel" aria-labelledby="security-tab">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Change Password</h6>
                    </div>
                    <div class="card-body">
                        <form action="/settings" method="post">
                            <input type="hidden" name="action" value="change_password">
                            
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Current Password</label>
                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="new_password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" required>
                                <small class="form-text text-muted">Password must be at least 6 characters long.</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Change Password</button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Account Security</h6>
                    </div>
                    <div class="card-body">
                        <p>For security reasons, remember to:</p>
                        <ul>
                            <li>Use a strong, unique password</li>
                            <li>Never share your login information</li>
                            <li>Log out when using shared devices</li>
                            <li>Regularly update your password</li>
                        </ul>
                        
                        <p>Last login: <strong><?php echo date('M j, Y h:i A'); ?></strong></p>
                    </div>
                </div>
            </div>
            
            <!-- Preferences Settings -->
            <div class="tab-pane fade" id="preferences" role="tabpanel" aria-labelledby="preferences-tab">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">App Preferences</h6>
                    </div>
                    <div class="card-body">
                        <form action="/settings" method="post">
                            <input type="hidden" name="action" value="update_settings">
                            
                            <div class="mb-3">
                                <label for="currency" class="form-label">Currency</label>
                                <select class="form-select" id="currency" name="currency">
                                    <?php foreach ($available_currencies as $code => $name): ?>
                                        <option value="<?php echo $code; ?>" <?php echo ($settings->currency === $code) ? 'selected' : ''; ?>>
                                            <?php echo $name; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="form-text text-muted">Select your preferred currency for displaying financial data.</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="theme" class="form-label">Theme</label>
                                <select class="form-select" id="theme" name="theme">
                                    <?php foreach ($available_themes as $code => $name): ?>
                                        <option value="<?php echo $code; ?>" <?php echo ($settings->theme === $code) ? 'selected' : ''; ?>>
                                            <?php echo $name; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="form-text text-muted">Choose the display theme for the application.</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="budget_alert_threshold" class="form-label">Budget Alert Threshold (%)</label>
                                <input type="range" class="form-range" id="budget_alert_threshold" name="budget_alert_threshold" 
                                       min="50" max="100" step="5" value="<?php echo $settings->budget_alert_threshold; ?>">
                                <div class="d-flex justify-content-between">
                                    <small>50%</small>
                                    <small id="threshold_value"><?php echo $settings->budget_alert_threshold; ?>%</small>
                                    <small>100%</small>
                                </div>
                                <small class="form-text text-muted">Receive alerts when your spending reaches this percentage of your budget.</small>
                            </div>
                            
                            <div class="mb-3 d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Save Preferences</button>
                                <button type="button" class="btn btn-outline-secondary" id="resetSettings">Reset to Default</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Notification Settings -->
            <div class="tab-pane fade" id="notifications" role="tabpanel" aria-labelledby="notifications-tab">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Notification Settings</h6>
                    </div>
                    <div class="card-body">
                        <form action="/settings" method="post">
                            <input type="hidden" name="action" value="update_settings">
                            
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="notification_enabled" name="notification_enabled" 
                                       <?php echo $settings->notification_enabled ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="notification_enabled">Enable In-App Notifications</label>
                                <div class="form-text">Receive notifications within the application for important alerts.</div>
                            </div>
                            
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="email_notification_enabled" name="email_notification_enabled" 
                                       <?php echo $settings->email_notification_enabled ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="email_notification_enabled">Enable Email Notifications</label>
                                <div class="form-text">Receive email notifications for important alerts and updates.</div>
                            </div>
                            
                            <h6 class="mt-4 mb-3">Notification Types</h6>
                            
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="budget_alerts" checked disabled>
                                <label class="form-check-label" for="budget_alerts">
                                    Budget Alerts
                                </label>
                                <div class="form-text">Alerts when you reach your budget threshold.</div>
                            </div>
                            
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="bill_reminders" checked disabled>
                                <label class="form-check-label" for="bill_reminders">
                                    Bill Reminders
                                </label>
                                <div class="form-text">Reminders for upcoming bill payments.</div>
                            </div>
                            
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="goal_progress" checked disabled>
                                <label class="form-check-label" for="goal_progress">
                                    Goal Progress
                                </label>
                                <div class="form-text">Updates on your financial goal progress.</div>
                            </div>
                            
                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" id="security_alerts" checked disabled>
                                <label class="form-check-label" for="security_alerts">
                                    Security Alerts
                                </label>
                                <div class="form-text">Alerts about account security issues.</div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Save Notification Settings</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reset Settings Modal -->
<div class="modal fade" id="resetSettingsModal" tabindex="-1" aria-labelledby="resetSettingsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="resetSettingsModalLabel">Reset Settings</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to reset all settings to their default values? This action cannot be undone.</p>
                <form id="resetSettingsForm" action="/settings" method="post">
                    <input type="hidden" name="action" value="reset_settings">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="resetSettingsForm" class="btn btn-danger">Reset Settings</button>
            </div>
        </div>
    </div>
</div>

<?php
// JavaScript for settings page
$page_scripts = "
// Update budget threshold value display
document.getElementById('budget_alert_threshold').addEventListener('input', function() {
    document.getElementById('threshold_value').textContent = this.value + '%';
});

// Reset settings button
document.getElementById('resetSettings').addEventListener('click', function() {
    var modal = new bootstrap.Modal(document.getElementById('resetSettingsModal'));
    modal.show();
});
";

// Include footer
require_once 'includes/footer.php';
?>