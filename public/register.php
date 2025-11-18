<?php
require __DIR__.'/../config/config.php';
require __DIR__.'/../includes/functions.php';

// Redirect logged-in users
if (is_logged_in()) {
    redirect('dashboard.php');
}

$errors = [];
$inputs = [
    'username' => '',
    'email' => '',
    'password' => '',
    'agree_terms' => false
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!validate_csrf_token($_POST['csrf_token'])) {
        $errors[] = 'Invalid form submission';
    } else {
        // Sanitize and validate inputs
        $inputs['username'] = sanitize_input($_POST['username'] ?? '');
        $inputs['email'] = sanitize_input($_POST['email'] ?? '');
        $inputs['password'] = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $inputs['agree_terms'] = isset($_POST['agree_terms']);

        // Username validation
        if (empty($inputs['username'])) {
            $errors[] = 'Username is required';
        } elseif (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $inputs['username'])) {
            $errors[] = 'Username must be 3-20 characters (letters, numbers, _)';
        }

        // Email validation
        if (empty($inputs['email'])) {
            $errors[] = 'Email is required';
        } elseif (!filter_var($inputs['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address';
        }

        // Password validation
        if (empty($inputs['password'])) {
            $errors[] = 'Password is required';
        } elseif (strlen($inputs['password']) < 8) {
            $errors[] = 'Password must be at least 8 characters';
        } elseif (!preg_match('/[A-Z]/', $inputs['password'])) {
            $errors[] = 'Password must contain at least one uppercase letter';
        } elseif (!preg_match('/[0-9]/', $inputs['password'])) {
            $errors[] = 'Password must contain at least one number';
        } elseif ($inputs['password'] !== $confirm_password) {
            $errors[] = 'Passwords do not match';
        }

        // Terms agreement
        if (!$inputs['agree_terms']) {
            $errors[] = 'You must agree to the terms and conditions';
        }

        // Check for existing user
        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("SELECT uid FROM users WHERE username = ? OR email = ?");
                $stmt->execute([$inputs['username'], $inputs['email']]);
                
                if ($stmt->fetch()) {
                    $errors[] = 'Username or email already exists';
                } else {
                    // Create account
                    $hashed_password = password_hash($inputs['password'], PASSWORD_BCRYPT, ['cost' => PASSWORD_COST]);
                    
                    $pdo->beginTransaction();
                    $stmt = $pdo->prepare("
                        INSERT INTO users (username, email, password, created_at) 
                        VALUES (?, ?, ?, NOW())
                    ");
                    $stmt->execute([
                        $inputs['username'],
                        $inputs['email'],
                        $hashed_password
                    ]);
                    $user_id = $pdo->lastInsertId();
                    $pdo->commit();

                    // Auto-login
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['username'] = $inputs['username'];
                    $_SESSION['success'] = 'Registration successful! Welcome to Virtual Kitchen';
                    
                    redirect('dashboard.php');
                }
            } catch (PDOException $e) {
                $pdo->rollBack();
                $errors[] = 'Registration failed. Please try again.';
                error_log('Registration error: ' . $e->getMessage());
            }
        }
    }
}

$title = "Create Your Account";
require __DIR__.'/../templates/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h2 class="h4 mb-0"><i class="fas fa-user-plus"></i> Create Account</h2>
                </div>
                <div class="card-body">
                    <?php display_errors($errors); ?>

                    <form method="POST" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

                        <!-- Username -->
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" 
                                   value="<?= htmlspecialchars($inputs['username']) ?>" 
                                   required
                                   pattern="[a-zA-Z0-9_]{3,20}"
                                   title="3-20 characters (letters, numbers, _)">
                            <div class="form-text">Letters, numbers, and underscores only</div>
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?= htmlspecialchars($inputs['email']) ?>" required>
                        </div>

                        <!-- Password -->
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" 
                                   required minlength="8"
                                   pattern="^(?=.*[A-Z])(?=.*\d).+$"
                                   title="At least 8 characters with 1 uppercase and 1 number">
                            <div class="form-text">Minimum 8 characters with at least 1 uppercase letter and 1 number</div>
                        </div>

                        <!-- Confirm Password -->
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="confirm_password" 
                                   name="confirm_password" required>
                        </div>

                        <!-- Terms Agreement -->
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="agree_terms" 
                                   name="agree_terms" <?= $inputs['agree_terms'] ? 'checked' : '' ?> required>
                            <label class="form-check-label" for="agree_terms">
                                I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Terms and Conditions</a>
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2">
                            <i class="fas fa-user-plus"></i> Register
                        </button>
                    </form>

                    <hr class="my-4">

                    <p class="text-center mb-0">
                        Already have an account? <a href="login.php">Log in here</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Terms Modal -->
<div class="modal fade" id="termsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Terms and Conditions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>By registering an account, you agree to:</p>
                <ul>
                    <li>Use the service responsibly</li>
                    <li>Not share inappropriate content</li>
                    <li>Maintain the security of your account</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__.'/../templates/footer.php'; ?>