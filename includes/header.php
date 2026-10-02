<?php
/**
 * TasteBook – Digital Recipe Book
 * Shared Header Component (includes/header.php)
 */
if (!isset($conn)) {
    require_once __DIR__ . '/db.php';
}
require_once __DIR__ . '/functions.php';

// Active page identification
$current_page = basename($_SERVER['PHP_SELF']);
$base_path = get_base_url();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="TasteBook is a modern recipe book and sharing community. Discover, cook, save, and share recipes.">
    <meta name="author" content="TasteBook">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' | TasteBook' : 'TasteBook – Digital Recipe Book'; ?></title>
    
    <!-- Google Fonts: Outfit & Playfair Display -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;1,600&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5.3.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo $base_path; ?>css/style.css">
</head>
<body>

    <!-- Sticky Modern Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top modern-navbar" id="mainNavbar">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="<?php echo $base_path; ?>index.php" id="navLogo">
                <span class="brand-icon-wrap me-2">
                    <i class="bi bi-fire"></i>
                </span>
                <span class="brand-text">Taste<span class="brand-accent">Book</span></span>
            </a>
            
            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarTastebook" aria-controls="navbarTastebook" aria-expanded="false" aria-label="Toggle navigation" id="navbarToggleBtn">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarTastebook">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0 nav-links-wrap">
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page === 'index.php') ? 'active' : ''; ?>" href="<?php echo $base_path; ?>index.php" id="navHome">
                            <i class="bi bi-house-door me-1 d-lg-none"></i> Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page === 'recipes.php') ? 'active' : ''; ?>" href="<?php echo $base_path; ?>recipes.php" id="navRecipes">
                            <i class="bi bi-journal-bookmark me-1 d-lg-none"></i> Recipes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page === 'about.php') ? 'active' : ''; ?>" href="<?php echo $base_path; ?>about.php" id="navAbout">
                            <i class="bi bi-info-circle me-1 d-lg-none"></i> About
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page === 'contact.php') ? 'active' : ''; ?>" href="<?php echo $base_path; ?>contact.php" id="navContact">
                            <i class="bi bi-envelope me-1 d-lg-none"></i> Contact
                        </a>
                    </li>
                </ul>
                
                <div class="d-flex align-items-center gap-2 nav-auth-wrap">
                    <?php if (is_logged_in()): ?>
                        <a href="<?php echo $base_path; ?>add_recipe.php" class="btn btn-sm btn-outline-primary rounded-pill px-3 d-none d-md-inline-flex align-items-center" id="navAddRecipeBtn">
                            <i class="bi bi-plus-circle me-1"></i> Add Recipe
                        </a>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-user-dropdown dropdown-toggle d-flex align-items-center rounded-pill" type="button" id="userMenuDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="user-avatar-circle me-2">
                                    <?php echo strtoupper(substr($_SESSION['username'] ?? 'U', 0, 1)); ?>
                                </span>
                                <span class="username-label d-none d-sm-inline"><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-4 mt-2" aria-labelledby="userMenuDropdown">
                                <li>
                                    <div class="dropdown-header text-muted small pb-1">Signed in as</div>
                                    <div class="px-3 pb-2 fw-semibold text-dark"><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></div>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item py-2 d-flex align-items-center" href="<?php echo $base_path; ?>dashboard.php" id="navDashboardLink">
                                        <i class="bi bi-speedometer2 me-2 text-primary"></i> Dashboard
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item py-2 d-flex align-items-center" href="<?php echo $base_path; ?>add_recipe.php" id="navAddRecipeDropdown">
                                        <i class="bi bi-plus-square me-2 text-success"></i> Add New Recipe
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item py-2 d-flex align-items-center" href="<?php echo $base_path; ?>my_recipes.php">
                                        <i class="bi bi-journal-text me-2 text-primary"></i> My Recipes
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item py-2 d-flex align-items-center" href="<?php echo $base_path; ?>favorites.php">
                                        <i class="bi bi-heart me-2 text-danger"></i> Favorites
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item py-2 d-flex align-items-center" href="<?php echo $base_path; ?>profile.php">
                                        <i class="bi bi-person-gear me-2 text-secondary"></i> Profile
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="<?php echo $base_path; ?>auth/logout.php" method="post">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                                        <button class="dropdown-item py-2 text-danger d-flex align-items-center" type="submit" id="navLogoutLink">
                                            <i class="bi bi-box-arrow-right me-2"></i> Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="<?php echo $base_path; ?>auth/login.php" class="btn btn-sm btn-login rounded-pill px-3" id="navLoginBtn">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Login
                        </a>
                        <a href="<?php echo $base_path; ?>auth/register.php" class="btn btn-sm btn-register rounded-pill px-3" id="navRegisterBtn">
                            <i class="bi bi-person-plus me-1"></i> Register
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content Wrapper with Top Padding for Fixed Navbar -->
    <main class="page-wrapper">
        <div class="container flash-container mt-3">
            <?php display_flash_message(); ?>
        </div>
