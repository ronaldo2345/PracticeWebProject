<?php
require __DIR__.'/../config/config.php';
require __DIR__.'/../includes/functions.php';

if (is_logged_in()) {
    redirect('dashboard.php');
}

// Initialize login attempts if not set
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['last_attempt'] = 0;
}

// Check if rate limited
if ($_SESSION['login_attempts'] >= 5 && (time() - $_SESSION['last_attempt'] < 300)) {
    die('Too many login attempts. Please try again later.');
}

$errors = [];
$inputs = ['username' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid form submission';
    } else {
        $inputs['username'] = sanitize_input($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($inputs['username'])) {
            $errors[] = 'Username is required';
        }

        if (empty($password)) {
            $errors[] = 'Password is required';
        }

        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
                $stmt->execute([$inputs['username']]);
                $user = $stmt->fetch();

                if ($user && password_verify($password, $user['password'])) {
                    // Check if password needs rehashing
                    if (password_needs_rehash($user['password'], PASSWORD_BCRYPT, ['cost' => PASSWORD_COST])) {
                        $newHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => PASSWORD_COST]);
                        $pdo->prepare("UPDATE users SET password = ? WHERE uid = ?")
                            ->execute([$newHash, $user['uid']]);
                    }

                    // Reset login attempts
                    $_SESSION['login_attempts'] = 0;

                    // Set session variables
                    $_SESSION['user_id'] = $user['uid'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['last_login'] = time();

                    // Regenerate session ID
                    session_regenerate_id(true);

                    // Redirect to intended page or dashboard
                    $redirect = $_SESSION['redirect_url'] ?? 'dashboard.php';
                    unset($_SESSION['redirect_url']);
                    redirect($redirect);
                } else {
                    // Increment failed attempts
                    $_SESSION['login_attempts']++;
                    $_SESSION['last_attempt'] = time();
                    $errors[] = 'Invalid username or password';
                }
            } catch (PDOException $e) {
                $errors[] = 'Login failed. Please try again.';
                error_log('Login error: ' . $e->getMessage());
            }
        }
    }
}

$title = "Login - Virtual Kitchen";
require __DIR__.'/../templates/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h2 class="h4 mb-0">Login to Your Account</h2>
            </div>
            <div class="card-body">
                <?php if ($_SESSION['login_attempts'] >= 3): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> 
                        Multiple failed login attempts detected.
                    </div>
                <?php endif; ?>

                <?php display_errors($errors); ?>

                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" 
                               value="<?= htmlspecialchars($inputs['username']) ?>" 
                               required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <div class="form-text">
                            <a href="forgot-password.php">Forgot your password?</a>
                        </div>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Remember me</label>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </button>
                </form>

                <hr class="my-4">

                <p class="text-center mb-0">
                    Don't have an account? <a href="register.php">Register here</a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__.'/../templates/footer.php'; ?>