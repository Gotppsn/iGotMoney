<?php
// Set page title and current page for menu highlighting
$page_title = __('login') . ' - ' . __('app_name');
$current_page = 'login';

// Include header
require_once 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-5">
        <div class="card auth-card shadow-lg border-0 rounded-lg mt-5">
            <div class="card-header">
                <h3 class="text-center font-weight-bold my-2"><?php echo __('login'); ?></h3>
            </div>
            <div class="card-body">
                <form action="<?php echo BASE_PATH; ?>/login" method="post">
                    <div class="form-floating mb-3">
                        <input class="form-control" id="username" name="username" type="text" placeholder="<?php echo __('username'); ?>" required />
                        <label for="username"><?php echo __('username'); ?></label>
                    </div>
                    <div class="form-floating mb-3">
                        <input class="form-control" id="password" name="password" type="password" placeholder="<?php echo __('password'); ?>" required />
                        <label for="password"><?php echo __('password'); ?></label>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mt-4 mb-0">
                        <a class="small text-primary" href="#"><?php echo __('forgot_password'); ?></a>
                        <button type="submit" class="btn btn-primary"><?php echo __('login'); ?></button>
                    </div>
                </form>
            </div>
            <div class="card-footer text-center py-3">
                <div class="small">
                    <a href="<?php echo BASE_PATH; ?>/register"><?php echo __('dont_have_account'); ?> <?php echo __('sign_up'); ?>!</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
require_once 'includes/footer.php';
?>