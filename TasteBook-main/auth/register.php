<?php
/**
 * TasteBook – Digital Recipe Book
 * User Registration Page (auth/register.php)
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

$page_title = 'Create Account';
$errors = [];
$username = '';
$email = '';

// Handle POST form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $errors['general'] = 'Your session expired. Refresh the page and try again.';
    }

    $username = get_post_string('username');
    $email = get_post_string('email');
    $password = get_post_string('password', false);
    $confirm_password = get_post_string('confirm_password', false);

    // 1. Validation: Username
    if (empty($username)) {
        $errors['username'] = 'Username is required.';
    } elseif (strlen($username) < 3 || strlen($username) > 100) {
        $errors['username'] = 'Username must be between 3 and 100 characters long.';
    }

    // 2. Validation: Email
    if (empty($email)) {
        $errors['email'] = 'Email address is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
        $errors['email'] = 'Provide a valid email address (maximum 150 characters).';
    } else {
        // Check if email is already registered (Duplicate check with Prepared Statement)
        $stmt_check = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        $stmt_check->store_result();
        
        if ($stmt_check->num_rows > 0) {
            $errors['email'] = 'This email address is already registered. Please log in.';
        }
        $stmt_check->close();
    }

    // 3. Validation: Password & Confirmation
    if (empty($password)) {
        $errors['password'] = 'Password is required.';
    } elseif (strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $errors['password'] = 'Use at least 8 characters, including a letter and a number.';
    }

    if ($password !== $confirm_password) {
        $errors['confirm_password'] = 'Passwords do not match.';
    }

    // If there are no errors, proceed with account creation
    if (empty($errors)) {
        // Securely hash the password
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Insert new user into database using prepared statement
        $stmt_insert = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt_insert->bind_param("sss", $username, $email, $hashed_password);

        if ($stmt_insert->execute()) {
            $stmt_insert->close();
            set_flash_message('success', 'Registration successful! You can now log in to TasteBook.');
            header("Location: login.php");
            exit();
        } else {
            $errors['general'] = 'Registration failed due to a database error. Please try again later.';
            $stmt_insert->close();
        }
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
                            <i class="bi bi-person-plus-fill"></i>
                        </div>
                        <h2 class="fw-bold text-dark mb-1">Create an Account</h2>
                        <p class="text-muted small">Join TasteBook to share and discover amazing recipes</p>
                    </div>

                    <?php if (!empty($errors['general'])): ?>
                        <div class="alert alert-danger py-2 small mb-3">
                            <i class="bi bi-exclamation-circle-fill me-1"></i> <?php echo htmlspecialchars($errors['general']); ?>
                        </div>
                    <?php endif; ?>

                    <form action="register.php" method="POST" id="registerForm" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                        <!-- Username Field -->
                        <div class="mb-3">
                            <label for="regUsername" class="form-label">Full Name / Username</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-person"></i></span>
                                <input type="text" 
                                       class="form-control border-start-0 <?php echo isset($errors['username']) ? 'is-invalid' : ''; ?>" 
                                       id="regUsername" 
                                       name="username" 
                                       placeholder="e.g. John Doe" 
                                       maxlength="100"
                                       value="<?php echo htmlspecialchars($username); ?>" 
                                       required>
                                <?php if (isset($errors['username'])): ?>
                                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['username']); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Email Field -->
                        <div class="mb-3">
                            <label for="regEmail" class="form-label">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-envelope"></i></span>
                                <input type="email" 
                                       class="form-control border-start-0 <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" 
                                       id="regEmail" 
                                       name="email" 
                                       placeholder="name@example.com" 
                                       maxlength="150"
                                       value="<?php echo htmlspecialchars($email); ?>" 
                                       required>
                                <?php if (isset($errors['email'])): ?>
                                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['email']); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Password Field -->
                        <div class="mb-3">
                            <label for="regPassword" class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-shield-lock"></i></span>
                                <input type="password" 
                                       class="form-control border-start-0 <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" 
                                       id="regPassword" 
                                       name="password" 
                                       placeholder="At least 8 characters, with a letter and a number"
                                       required>
                                <?php if (isset($errors['password'])): ?>
                                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['password']); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Confirm Password Field -->
                        <div class="mb-4">
                            <label for="regConfirmPassword" class="form-label">Confirm Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-check2-circle"></i></span>
                                <input type="password" 
                                       class="form-control border-start-0 <?php echo isset($errors['confirm_password']) ? 'is-invalid' : ''; ?>" 
                                       id="regConfirmPassword" 
                                       name="confirm_password" 
                                       placeholder="Re-enter password" 
                                       required>
                                <?php if (isset($errors['confirm_password'])): ?>
                                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['confirm_password']); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-primary w-100 py-2 mb-3" id="btnSubmitRegister">
                            <i class="bi bi-person-check-fill me-1"></i> Register Account
                        </button>
                    </form>

                    <div class="text-center pt-3 border-top border-light-subtle">
                        <p class="text-muted small mb-0">
                            Already have an account? 
                            <a href="login.php" class="text-primary fw-semibold text-decoration-none">Sign In here</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
