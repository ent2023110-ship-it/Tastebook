<?php
/**
 * TasteBook – Digital Recipe Book
 * Contact Us Page (contact.php)
 * 
 * ICT 2209 - Web Technologies Mini Project
 * Rajarata University of Sri Lanka
 */

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$page_title = 'Contact Us';
$errors = [];
$name = '';
$email = '';
$message = '';

// Handle POST contact submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $errors['general'] = 'Your session expired. Refresh the page and try again.';
    }

    $name = get_post_string('name');
    $email = get_post_string('email');
    $message = get_post_string('message');

    // 1. Validation: Name
    if (empty($name)) {
        $errors['name'] = 'Please enter your name.';
    } elseif (strlen($name) < 2 || strlen($name) > 100) {
        $errors['name'] = 'Name must be between 2 and 100 characters.';
    }

    // 2. Validation: Email
    if (empty($email)) {
        $errors['email'] = 'Please enter your email address.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
        $errors['email'] = 'Please enter a valid email address.';
    }

    // 3. Validation: Message
    if (empty($message)) {
        $errors['message'] = 'Please enter your message.';
    } elseif (strlen($message) < 10) {
        $errors['message'] = 'Message must be at least 10 characters.';
    }

    // If valid, insert into messages table using Prepared Statement
    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO messages (name, email, message) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $message);

        if ($stmt->execute()) {
            $stmt->close();
            set_flash_message('success', 'Thank you for contacting us. Your message has been received.');
            header('Location: contact.php');
            exit();
        } else {
            $errors['general'] = 'Failed to send your message. Please try again later.';
            $stmt->close();
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- Header Banner -->
<section class="py-5 bg-white border-bottom border-secondary border-opacity-10">
    <div class="container text-center">
        <span class="text-primary fw-semibold text-uppercase tracking-wide small">Get In Touch</span>
        <h1 class="fw-bold heading-serif display-5 mb-2">We'd Love to Hear From You</h1>
        <p class="text-muted max-w-700 mx-auto">
            Have questions about a recipe, suggestions for TasteBook, or want to collaborate? Send us a message and our team will gladly assist you.
        </p>
    </div>
</section>

<!-- Contact Form & Info Section -->
<section class="py-5">
    <div class="container">
        <div class="row g-5">
            <!-- Left Column: Contact Form -->
            <div class="col-lg-7">
                <div class="card border-0 rounded-4 shadow-sm bg-white p-4 p-md-5">
                    <h3 class="fw-bold text-dark mb-4">Send Us a Message</h3>

                    <?php if (!empty($errors['general'])): ?>
                        <div class="alert alert-danger py-2 small mb-4">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i> <?php echo htmlspecialchars($errors['general']); ?>
                        </div>
                    <?php endif; ?>

                    <form action="contact.php" method="POST" id="contactForm" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                        <!-- Full Name Field -->
                        <div class="mb-3">
                            <label for="contactName" class="form-label">Your Name <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-person"></i></span>
                                <input type="text" 
                                       class="form-control border-start-0 <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>" 
                                       id="contactName" 
                                       name="name" 
                                       maxlength="100"
                                       placeholder="e.g. Rachel Silva" 
                                       value="<?php echo htmlspecialchars($name); ?>" 
                                       required>
                                <?php if (isset($errors['name'])): ?>
                                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['name']); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Email Field -->
                        <div class="mb-3">
                            <label for="contactEmail" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-envelope"></i></span>
                                <input type="email" 
                                       class="form-control border-start-0 <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" 
                                       id="contactEmail" 
                                       name="email" 
                                       maxlength="150"
                                       placeholder="name@example.com" 
                                       value="<?php echo htmlspecialchars($email); ?>" 
                                       required>
                                <?php if (isset($errors['email'])): ?>
                                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['email']); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Message Field -->
                        <div class="mb-4">
                            <label for="contactMessage" class="form-label">Your Message <span class="text-danger">*</span></label>
                            <textarea class="form-control <?php echo isset($errors['message']) ? 'is-invalid' : ''; ?>" 
                                      id="contactMessage" 
                                      name="message" 
                                      rows="5" 
                                      placeholder="Write your feedback, question or recipe idea here..." 
                                      required><?php echo htmlspecialchars($message); ?></textarea>
                            <?php if (isset($errors['message'])): ?>
                                <div class="invalid-feedback"><?php echo htmlspecialchars($errors['message']); ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-primary rounded-pill px-5 py-2" id="btnSubmitContact">
                            <i class="bi bi-send-fill me-1"></i> Send Message
                        </button>
                    </form>
                </div>
            </div>

            <!-- Right Column: Contact Details -->
            <div class="col-lg-5">
                <div class="d-flex flex-column gap-4">
                    <div class="card border-0 rounded-4 shadow-sm bg-white p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="stat-icon-wrap bg-primary bg-opacity-10 text-primary me-3" style="width: 46px; height: 46px; font-size: 1.3rem;">
                                <i class="bi bi-chat-heart-fill"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0 text-dark">TasteBook Community</h5>
                                <small class="text-muted">We’re here to help</small>
                            </div>
                        </div>
                        <p class="text-muted small mb-3">
                            Have a question, suggestion, or recipe idea? Send us a note and our team will be happy to hear from you.
                        </p>
                    </div>

                    <!-- Direct Info Items -->
                    <div class="card border-0 rounded-4 shadow-sm bg-white p-4">
                        <h5 class="fw-bold text-dark mb-3">Direct Contact Details</h5>

                        <div class="d-flex align-items-start mb-3">
                            <i class="bi bi-geo-alt-fill text-primary fs-5 me-3"></i>
                            <div>
                                <strong class="d-block text-dark">Location</strong>
                                <small class="text-muted">Mihintale, Anuradhapura, Sri Lanka</small>
                            </div>
                        </div>

                        <div class="d-flex align-items-start mb-3">
                            <i class="bi bi-envelope-at-fill text-primary fs-5 me-3"></i>
                            <div>
                                <strong class="d-block text-dark">Email Inquiries</strong>
                                <small class="text-muted">info@tastebook.local / student@rjt.ac.lk</small>
                            </div>
                        </div>

                        <div class="d-flex align-items-start">
                            <i class="bi bi-clock-fill text-primary fs-5 me-3"></i>
                            <div>
                                <strong class="d-block text-dark">Response Hours</strong>
                                <small class="text-muted">Monday – Friday: 9:00 AM – 5:00 PM</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
