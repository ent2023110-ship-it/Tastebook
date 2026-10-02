<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_login();

$user_id = (int)$_SESSION['user_id'];
$user = get_logged_in_user($conn);
if (!$user) {
    $_SESSION = [];
    session_destroy();
    header('Location: auth/login.php');
    exit();
}
$errors = [];
$username = $user['username'];
$email = $user['email'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = get_post_string('username');
    $email = get_post_string('email');
    $current_password = get_post_string('current_password', false);
    $new_password = get_post_string('new_password', false);
    $confirm_password = get_post_string('confirm_password', false);

    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $errors['general'] = 'Your session expired. Refresh the page and try again.';
    }
    if (strlen($username) < 3 || strlen($username) > 100) {
        $errors['username'] = 'Username must be between 3 and 100 characters.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
        $errors['email'] = 'Enter a valid email address (maximum 150 characters).';
    } else {
        $check = $conn->prepare('SELECT id FROM users WHERE email = ? AND id <> ? LIMIT 1');
        $check->bind_param('si', $email, $user_id);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $errors['email'] = 'That email address is already in use.';
        }
        $check->close();
    }

    $change_password = ($current_password !== '' || $new_password !== '' || $confirm_password !== '');
    if ($change_password) {
        $stored = $conn->prepare('SELECT password FROM users WHERE id = ? LIMIT 1');
        $stored->bind_param('i', $user_id);
        $stored->execute();
        $stored_hash = $stored->get_result()->fetch_assoc()['password'] ?? '';
        $stored->close();

        if ($current_password === '' || !password_verify($current_password, $stored_hash)) {
            $errors['current_password'] = 'Enter your current password to make a password change.';
        }
        if (strlen($new_password) < 8 || !preg_match('/[A-Za-z]/', $new_password) || !preg_match('/[0-9]/', $new_password)) {
            $errors['new_password'] = 'Use at least 8 characters, including a letter and a number.';
        }
        if ($new_password !== $confirm_password) {
            $errors['confirm_password'] = 'The new passwords do not match.';
        }
    }

    if (!$errors) {
        if ($change_password) {
            $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $update = $conn->prepare('UPDATE users SET username = ?, email = ?, password = ? WHERE id = ?');
            $update->bind_param('sssi', $username, $email, $password_hash, $user_id);
        } else {
            $update = $conn->prepare('UPDATE users SET username = ?, email = ? WHERE id = ?');
            $update->bind_param('ssi', $username, $email, $user_id);
        }

        if ($update->execute()) {
            $update->close();
            $_SESSION['username'] = $username;
            $_SESSION['email'] = $email;
            set_flash_message('success', 'Your profile has been updated.');
            header('Location: profile.php');
            exit();
        }
        $update->close();
        $errors['general'] = 'Your profile could not be updated. Please try again.';
    }
}

$page_title = 'My Profile';
require_once __DIR__ . '/includes/header.php';
?>
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 rounded-4 shadow-sm">
                    <div class="card-body p-4 p-md-5">
                        <span class="text-primary fw-semibold text-uppercase small">Account settings</span>
                        <h1 class="heading-serif fw-bold mb-4">My Profile</h1>
                        <?php if ($errors): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php foreach ($errors as $error): ?><div><?php echo htmlspecialchars($error); ?></div><?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <form method="post" id="profileForm" novalidate>
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                            <div class="mb-3">
                                <label for="profileUsername" class="form-label">Username</label>
                                <input class="form-control" id="profileUsername" name="username" value="<?php echo htmlspecialchars($username); ?>" minlength="3" maxlength="100" required>
                            </div>
                            <div class="mb-4">
                                <label for="profileEmail" class="form-label">Email address</label>
                                <input class="form-control" type="email" id="profileEmail" name="email" value="<?php echo htmlspecialchars($email); ?>" maxlength="150" required>
                            </div>
                            <h2 class="h5 fw-bold border-top pt-4">Change password</h2>
                            <p class="text-muted small">Leave these fields blank to keep your current password.</p>
                            <div class="mb-3">
                                <label for="currentPassword" class="form-label">Current password</label>
                                <input class="form-control" type="password" id="currentPassword" name="current_password" autocomplete="current-password">
                            </div>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label for="newPassword" class="form-label">New password</label>
                                    <input class="form-control" type="password" id="newPassword" name="new_password" autocomplete="new-password">
                                </div>
                                <div class="col-md-6">
                                    <label for="confirmPassword" class="form-label">Confirm new password</label>
                                    <input class="form-control" type="password" id="confirmPassword" name="confirm_password" autocomplete="new-password">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary rounded-pill px-4">Save profile</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
