<?php
// Set page title and current page for menu highlighting
$page_title = 'Register - iGotMoney';
$current_page = 'register';

// Include header
require_once 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card auth-card shadow-lg border-0 rounded-lg mt-5">
            <div class="card-header">
                <h3 class="text-center font-weight-bold my-2">Create Account</h3>
            </div>
            <div class="card-body">
                <form action="<?php echo BASE_PATH; ?>/register" method="post">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-floating mb-3 mb-md-0">
                                <input class="form-control" id="first_name" name="first_name" type="text" placeholder="First Name" />
                                <label for="first_name">First Name</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input class="form-control" id="last_name" name="last_name" type="text" placeholder="Last Name" />
                                <label for="last_name">Last Name</label>
                            </div>
                        </div>
                    </div>
                    <div class="form-floating mb-3">
                        <input class="form-control" id="username" name="username" type="text" placeholder="Username" required />
                        <label for="username">Username</label>
                        <?php if (isset($errors) && in_array('Username is required', $errors)): ?>
                            <small class="text-danger">Username is required</small>
                        <?php elseif (isset($errors) && in_array('Username must be at least 3 characters', $errors)): ?>
                            <small class="text-danger">Username must be at least 3 characters</small>
                        <?php endif; ?>
                    </div>
                    <div class="form-floating mb-3">
                        <input class="form-control" id="email" name="email" type="email" placeholder="name@example.com" required />
                        <label for="email">Email address</label>
                        <?php if (isset($errors) && in_array('Email is required', $errors)): ?>
                            <small class="text-danger">Email is required</small>
                        <?php elseif (isset($errors) && in_array('Email is invalid', $errors)): ?>
                            <small class="text-danger">Email is invalid</small>
                        <?php endif; ?>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-floating mb-3 mb-md-0">
                                <input class="form-control" id="password" name="password" type="password" placeholder="Create a password" required />
                                <label for="password">Password</label>
                                <?php if (isset($errors) && in_array('Password is required', $errors)): ?>
                                    <small class="text-danger">Password is required</small>
                                <?php elseif (isset($errors) && in_array('Password must be at least 6 characters', $errors)): ?>
                                    <small class="text-danger">Password must be at least 6 characters</small>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-3 mb-md-0">
                                <input class="form-control" id="password_confirm" name="password_confirm" type="password" placeholder="Confirm password" required />
                                <label for="password_confirm">Confirm Password</label>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 mb-0">
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-block">Create Account</button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-footer text-center py-3">
                <div class="small">
                    <a href="<?php echo BASE_PATH; ?>/login">Have an account? Go to login</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
require_once 'includes/footer.php';
?>