<?php
/**
 * TasteBook – Digital Recipe Book
 * Helper Functions (includes/functions.php)
 * 
 * Provides utility methods for authentication, session handling,
 * input sanitization, security, and flash messaging.
 */

// Start session if not already active
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');
    session_set_cookie_params([
        'httponly' => true,
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
        'path' => '/',
    ]);
    session_start();
}

/**
 * Check if the user is currently logged in
 * @return bool
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

function get_post_string($key, $trim = true) {
    $value = $_POST[$key] ?? '';
    if (!is_string($value)) {
        return '';
    }

    return $trim ? trim($value) : $value;
}

/**
 * Require user login to view a page, otherwise redirect to login
 */
function require_login() {
    if (!is_logged_in()) {
        set_flash_message('error', 'Please log in to access this page.');
        // Store intended destination if helpful
        header("Location: " . get_base_url() . "auth/login.php");
        exit();
    }
}

/**
 * Get current logged in user information array
 * @param mysqli $conn
 * @return array|null
 */
function get_logged_in_user($conn) {
    if (!is_logged_in()) {
        return null;
    }
    
    $user_id = (int)$_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT id, username, email, created_at FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    return $user;
}

/**
 * Sanitize user input for safe HTML display
 * @param string $data
 * @return string
 */
function sanitize_input($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Set a session flash message
 * @param string $type ('success', 'error', 'warning', 'info')
 * @param string $message
 */
function set_flash_message($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get and clear the flash message
 * @return array|null
 */
function get_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $msg = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $msg;
    }
    return null;
}

/**
 * Display the flash message as a Bootstrap Alert
 */
function display_flash_message() {
    $flash = get_flash_message();
    if ($flash) {
        $alert_type = ($flash['type'] === 'error') ? 'danger' : $flash['type'];
        $icon = match($flash['type']) {
            'success' => '<i class="bi bi-check-circle-fill me-2"></i>',
            'error' => '<i class="bi bi-exclamation-triangle-fill me-2"></i>',
            'warning' => '<i class="bi bi-exclamation-circle-fill me-2"></i>',
            default => '<i class="bi bi-info-circle-fill me-2"></i>',
        };
        echo "
        <div class='alert alert-{$alert_type} alert-dismissible fade show shadow-sm border-0 d-flex align-items-center mb-4' role='alert'>
            {$icon}
            <div>" . htmlspecialchars($flash['message']) . "</div>
            <button type='button' class='btn-close ms-auto' data-bs-dismiss='alert' aria-label='Close'></button>
        </div>";
    }
}

/**
 * Helper to dynamically compute base URL path
 * Handles both root installation and subfolder like /tastebook/
 * @return string
 */
function get_base_url() {
    // Determine relative path based on file location
    $script_dir = dirname($_SERVER['SCRIPT_NAME']);
    // Normalize backslashes to forward slashes for Windows
    $script_dir = str_replace('\\', '/', $script_dir);
    
    // Check if script is inside auth/ or includes/
    if (preg_match('/\/auth$|\/includes$/', $script_dir)) {
        return '../';
    }
    return './';
}

/**
 * Format date nicely (e.g., Oct 24, 2025)
 * @param string $timestamp
 * @return string
 */
function format_recipe_date($timestamp) {
    return date('M d, Y', strtotime($timestamp));
}

/**
 * Get reading/prep time estimation based on instructions length
 * @param string $instructions
 * @return string
 */
function estimate_cooking_time($instructions) {
    $word_count = str_word_count($instructions);
    if ($word_count > 100) return '45-60 mins';
    if ($word_count > 50) return '25-35 mins';
    return '15-20 mins';
}

/**
 * Get recipe image URL with local file check and curated food photo fallback
 * @param string|null $image_name
 * @param string $title
 * @return string
 */
function get_recipe_image_url($image_name, $title = '') {
    $base_path = get_base_url();
    $safe_image_name = basename((string)$image_name);
    $sample_images = [
        'chicken_pizza.jpg' => 'https://images.unsplash.com/photo-1513104890138-7c749659a591?auto=format&fit=crop&w=960&q=85',
        'chicken_pasta.jpg' => 'https://images.unsplash.com/photo-1473093295043-cdd812d0e601?auto=format&fit=crop&w=960&q=85',
        'chocolate_cake.jpg' => 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?auto=format&fit=crop&w=960&q=85',
        'fried_rice.jpg' => 'https://images.unsplash.com/photo-1603133872878-684f208fb84b?auto=format&fit=crop&w=960&q=85',
        'burger.jpg' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?auto=format&fit=crop&w=960&q=85',
        'pancakes.jpg' => 'https://images.unsplash.com/photo-1528207776546-365bb710ee93?auto=format&fit=crop&w=960&q=85',
    ];

    if (isset($sample_images[$safe_image_name])) {
        return $sample_images[$safe_image_name];
    }
    
    // Uploaded photos stay local; only the bundled sample images use curated photography.
    if (!empty($image_name)) {
        $local_path = __DIR__ . '/../images/' . $safe_image_name;
        if (file_exists($local_path)) {
            return $base_path . 'images/' . rawurlencode($safe_image_name);
        }
    }
    
    // Curated high quality food photographs by keyword
    $title_lower = strtolower($title . ' ' . $image_name);
    if (str_contains($title_lower, 'pizza')) {
        return 'https://images.unsplash.com/photo-1513104890138-7c749659a591?auto=format&fit=crop&w=800&q=80';
    } elseif (str_contains($title_lower, 'pasta') || str_contains($title_lower, 'alfredo')) {
        return 'https://images.unsplash.com/photo-1621996346565-e3d5d6281290?auto=format&fit=crop&w=800&q=80';
    } elseif (str_contains($title_lower, 'cake') || str_contains($title_lower, 'chocolate') || str_contains($title_lower, 'dessert')) {
        return 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?auto=format&fit=crop&w=800&q=80';
    } elseif (str_contains($title_lower, 'rice') || str_contains($title_lower, 'biryani')) {
        return 'https://images.unsplash.com/photo-1603133872878-684f208fb84b?auto=format&fit=crop&w=800&q=80';
    } elseif (str_contains($title_lower, 'burger')) {
        return 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?auto=format&fit=crop&w=800&q=80';
    } elseif (str_contains($title_lower, 'pancake') || str_contains($title_lower, 'breakfast')) {
        return 'https://images.unsplash.com/photo-1528207776546-365bb710ee93?auto=format&fit=crop&w=800&q=80';
    } elseif (str_contains($title_lower, 'salad')) {
        return 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?auto=format&fit=crop&w=800&q=80';
    } elseif (str_contains($title_lower, 'soup') || str_contains($title_lower, 'curry')) {
        return 'https://images.unsplash.com/photo-1547592166-23ac45744acd?auto=format&fit=crop&w=800&q=80';
    }
    
    // Default fallback culinary image
    return 'https://images.unsplash.com/photo-1495521821757-a1efb6729352?auto=format&fit=crop&w=800&q=80';
}

function recipe_categories() {
    return ['Breakfast', 'Lunch', 'Dinner', 'Desserts', 'Vegetarian', 'Healthy', 'Snacks'];
}

function recipe_difficulties() {
    return ['Easy', 'Medium', 'Hard'];
}

function upload_recipe_image($file) {
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return ['filename' => null, 'error' => null];
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['filename' => null, 'error' => 'The image upload failed. Please try again.'];
    }
    if ($file['size'] > 5 * 1024 * 1024) {
        return ['filename' => null, 'error' => 'Image file size must be less than 5MB.'];
    }

    $image_info = @getimagesize($file['tmp_name']);
    $mime_extensions = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];
    if (!$image_info || !isset($mime_extensions[$image_info['mime']])) {
        return ['filename' => null, 'error' => 'Only valid JPG, PNG, and WEBP images are allowed.'];
    }

    $filename = 'recipe_' . bin2hex(random_bytes(12)) . '.' . $mime_extensions[$image_info['mime']];
    $upload_directory = dirname(__DIR__) . '/images/';
    if (!is_dir($upload_directory) && !@mkdir($upload_directory, 0755, true) && !is_dir($upload_directory)) {
        error_log('TasteBook recipe image directory could not be created.');
        return ['filename' => null, 'error' => 'The image could not be saved. Please try again.'];
    }
    if (!@move_uploaded_file($file['tmp_name'], $upload_directory . $filename)) {
        error_log('TasteBook recipe image upload failed.');
        return ['filename' => null, 'error' => 'The image could not be saved. Please try again.'];
    }

    return ['filename' => $filename, 'error' => null];
}

function get_user_favorite_ids($conn) {
    if (!is_logged_in()) {
        return [];
    }

    $favorite_ids = [];
    $user_id = (int)$_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT recipe_id FROM favorites WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $favorite_ids[(int)$row['recipe_id']] = true;
    }
    $stmt->close();

    return $favorite_ids;
}

function render_favorite_button($recipe_id, $is_favorite, $return_to, $label = 'Save to favorites', $inline = false) {
    $action = $is_favorite ? 'remove' : 'add';
    $icon = $is_favorite ? 'bi-heart-fill' : 'bi-heart';
    $button_label = $is_favorite ? 'Remove from favorites' : $label;
    $button_class = ($inline ? 'btn btn-outline-danger rounded-pill px-3 ' : '') . ($is_favorite ? 'recipe-fav-btn active' : 'recipe-fav-btn');
    $form_class = $inline ? 'favorite-form favorite-form-inline' : 'favorite-form';

    echo '<form action="' . htmlspecialchars(get_base_url() . 'favorite_action.php', ENT_QUOTES, 'UTF-8') . '" method="post" class="' . $form_class . '">';
    echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
    echo '<input type="hidden" name="recipe_id" value="' . (int)$recipe_id . '">';
    echo '<input type="hidden" name="action" value="' . $action . '">';
    echo '<input type="hidden" name="return_to" value="' . htmlspecialchars($return_to, ENT_QUOTES, 'UTF-8') . '">';
    echo '<button type="submit" class="' . $button_class . '" aria-label="' . htmlspecialchars($button_label, ENT_QUOTES, 'UTF-8') . '" aria-pressed="' . ($is_favorite ? 'true' : 'false') . '" title="' . htmlspecialchars($button_label, ENT_QUOTES, 'UTF-8') . '">';
    echo '<i class="bi ' . $icon . ($is_favorite ? ' text-danger' : '') . '" aria-hidden="true"></i>' . ($inline ? ' ' . htmlspecialchars($button_label, ENT_QUOTES, 'UTF-8') : '');
    echo '</button></form>';
}
?>
