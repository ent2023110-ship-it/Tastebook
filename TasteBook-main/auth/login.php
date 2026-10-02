<?php
/**
 * TasteBook – Digital Recipe Book
 * User Login Page (auth/login.php)
 * 
 * ICT 2209 - Web Technologies Mini Project
 * Rajarata University of Sri Lanka
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

// If user is already logged in, redirect to dashboard
if (is_logged_in()) {
    header("Location: ../dashboard.php");
    exit();
}

$page_title = 'User Login';
$errors = [];
$email = '';

// Handle POST login request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $errors['general'] = 'Your session expired. Refresh the page and try again.';
    }

    $email = get_post_string('email');
    $password = get_post_string('password', false);

    // Validate inputs
    if (empty($email)) {
        $errors['email'] = 'Please enter your registered email address.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email address format.';
    }

    if (empty($password)) {
        $errors['password'] = 'Please enter your password.';
    }

    // If validation passed, query the database
    if (empty($errors)) {
        // Query user with prepared statement
        $stmt = $conn->prepare("SELECT id, username, email, password FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Verify the encrypted password
            if (password_verify($password, $user['password'])) {
                // Session fixation protection
                session_regenerate_id(true);
                unset($_SESSION['csrf_token']);

                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];

                $stmt->close();
                set_flash_message('success', 'Welcome back, ' . $user['username'] . '!');
                header("Location: ../dashboard.php");
                exit();
            } else {
                $errors['general'] = 'Invalid email address or password. Please try again.';
            }
        } else {
            $errors['general'] = 'Invalid email address or password. Please try again.';
        }
        $stmt->close();
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="auth-wrapper">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-7 col-sm-10">
                <div class="auth-card">
                    <div class="text-center mb-4">
                        <div class="brand-icon-wrap mb-2 mx-auto" style="width: 48px; height: 48px; font-size: 1.5rem;">
                            <i class="bi bi-box-arrow-in-right"></i>
                        </div>
                        <h2 class="fw-bold text-dark mb-1">Welcome Back</h2>
                        <p class="text-muted small">Log in to manage your digital recipe collection</p>
                    </div>

                    <?php if (!empty($errors['general'])): ?>
                        <div class="alert alert-danger py-2 small mb-3">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i> <?php echo htmlspecialchars($errors['general']); ?>
                        </div>
                    <?php endif; ?>

                    <form action="login.php" method="POST" id="loginForm" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                        <!-- Email Field -->
                        <div class="mb-3">
                            <label for="loginEmail" class="form-label">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-envelope"></i></span>
                                <input type="email" 
                                       class="form-control border-start-0 <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" 
                                       id="loginEmail" 
                                       name="email" 
                                       placeholder="name@example.com" 
                                       value="<?php echo htmlspecialchars($email); ?>" 
                                       required>
                                <?php if (isset($errors['email'])): ?>
                                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['email']); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Password Field -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label for="loginPassword" class="form-label mb-0">Password</label>
                                <span class="text-muted small">Use your TasteBook password</span>
                            </div>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-shield-lock"></i></span>
                                <input type="password" 
                                       class="form-control border-start-0 <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" 
                                       id="loginPassword" 
                                       name="password" 
                                       placeholder="Enter your password" 
                                       required>
                                <?php if (isset($errors['password'])): ?>
                                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['password']); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-primary w-100 py-2 mb-3" id="btnSubmitLogin">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
                        </button>
                    </form>

                    <!-- Demo Credentials Helper Box for Evaluation -->
                    <div class="p-3 bg-light rounded-3 border border-secondary border-opacity-25 small mb-3">
                        <strong class="d-block text-dark mb-1"><i class="bi bi-info-circle text-primary me-1"></i> Sample Demo Account:</strong>
                        <div class="text-muted">Email: <code>gordon@tastebook.com</code></div>
                        <div class="text-muted">Password: <code>password123</code></div>
                    </div>

                    <div class="text-center pt-3 border-top border-light-subtle">
                        <p class="text-muted small mb-0">
                            Don't have an account? 
                            <a href="register.php" class="text-primary fw-semibold text-decoration-none">Register here</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
