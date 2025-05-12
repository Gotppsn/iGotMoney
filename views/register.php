<?php
// Set page title and current page for menu highlighting
$page_title = __('register') . ' - ' . __('app_name');
$current_page = 'register';

// Include header
require_once 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card auth-card shadow-lg border-0 rounded-lg mt-5">
            <div class="card-header">
                <h3 class="text-center font-weight-bold my-2"><?php echo __('create_account'); ?></h3>
            </div>
            <div class="card-body">
                <form action="<?php echo BASE_PATH; ?>/register" method="post">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-floating mb-3 mb-md-0">
                                <input class="form-control" id="first_name" name="first_name" type="text" placeholder="<?php echo __('first_name'); ?>" />
                                <label for="first_name"><?php echo __('first_name'); ?></label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input class="form-control" id="last_name" name="last_name" type="text" placeholder="<?php echo __('last_name'); ?>" />
                                <label for="last_name"><?php echo __('last_name'); ?></label>
                            </div>
                        </div>
                    </div>
                    <div class="form-floating mb-3">
                        <input class="form-control" id="username" name="username" type="text" placeholder="<?php echo __('username'); ?>" required />
                        <label for="username"><?php echo __('username'); ?></label>
                        <?php if (isset($errors) && in_array(__('username_is_required'), $errors)): ?>
                            <small class="text-danger"><?php echo __('username_is_required'); ?></small>
                        <?php elseif (isset($errors) && in_array(__('username_min_length'), $errors)): ?>
                            <small class="text-danger"><?php echo __('username_min_length'); ?></small>
                        <?php endif; ?>
                    </div>
                    <div class="form-floating mb-3">
                        <input class="form-control" id="email" name="email" type="email" placeholder="name@example.com" required />
                        <label for="email"><?php echo __('email'); ?></label>
                        <?php if (isset($errors) && in_array(__('email_is_required'), $errors)): ?>
                            <small class="text-danger"><?php echo __('email_is_required'); ?></small>
                        <?php elseif (isset($errors) && in_array(__('email_is_invalid'), $errors)): ?>
                            <small class="text-danger"><?php echo __('email_is_invalid'); ?></small>
                        <?php endif; ?>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-floating mb-3 mb-md-0">
                                <input class="form-control" id="password" name="password" type="password" placeholder="<?php echo __('create_a_password'); ?>" required />
                                <label for="password"><?php echo __('password'); ?></label>
                                <?php if (isset($errors) && in_array(__('password_is_required'), $errors)): ?>
                                    <small class="text-danger"><?php echo __('password_is_required'); ?></small>
                                <?php elseif (isset($errors) && in_array(__('password_min_length'), $errors)): ?>
                                    <small class="text-danger"><?php echo __('password_min_length'); ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-3 mb-md-0">
                                <input class="form-control" id="password_confirm" name="password_confirm" type="password" placeholder="<?php echo __('confirm_password'); ?>" required />
                                <label for="password_confirm"><?php echo __('confirm_password'); ?></label>
                                <?php if (isset($errors) && in_array(__('passwords_dont_match'), $errors)): ?>
                                    <small class="text-danger"><?php echo __('passwords_dont_match'); ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 mb-0">
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-block"><?php echo __('create_account'); ?></button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-footer text-center py-3">
                <div class="small">
                    <a href="<?php echo BASE_PATH; ?>/login"><?php echo __('already_have_account'); ?> <?php echo __('login'); ?></a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
require_once 'includes/footer.php';
?>