<?php
// Set page title and current page for menu highlighting
$page_title = 'Login - iGotMoney';
$current_page = 'login';

// Include header
require_once 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-5">
        <div class="card auth-card shadow-lg border-0 rounded-lg mt-5">
            <div class="card-header">
                <h3 class="text-center font-weight-bold my-2">Login</h3>
            </div>
            <div class="card-body">
                <form action="/login" method="post">
                    <div class="form-floating mb-3">
                        <input class="form-control" id="username" name="username" type="text" placeholder="Username" required />
                        <label for="username">Username</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input class="form-control" id="password" name="password" type="password" placeholder="Password" required />
                        <label for="password">Password</label>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mt-4 mb-0">
                        <a class="small text-primary" href="#">Forgot Password?</a>
                        <button type="submit" class="btn btn-primary">Login</button>
                    </div>
                </form>
            </div>
            <div class="card-footer text-center py-3">
                <div class="small">
                    <a href="/register">Need an account? Sign up!</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
require_once 'includes/footer.php';
?>