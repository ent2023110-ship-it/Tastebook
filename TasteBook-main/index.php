<?php
/**
 * TasteBook – Digital Recipe Book
 * Home Page (index.php)
 * 
 * ICT 2209 - Web Technologies Mini Project
 * Rajarata University of Sri Lanka
 */

$page_title = 'Discover & Share Delicious Recipes';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/header.php';

// Fetch 6 recent/featured recipes from MySQL database
$featured_recipes = [];
$sql = "SELECT r.*, u.username AS author_name 
        FROM recipes r 
        JOIN users u ON r.user_id = u.id 
        ORDER BY r.created_at DESC 
        LIMIT 6";

$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $featured_recipes[] = $row;
    }
    $favorite_ids = get_user_favorite_ids($conn);
}

// Total counts for stats counter
$total_recipes_count = 0;
$count_res = $conn->query("SELECT COUNT(*) AS total FROM recipes");
if ($count_res) {
    $total_recipes_count = $count_res->fetch_assoc()['total'];
}
?>

<!-- 1. Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center g-5">
            <!-- Left Column: Copy & Search -->
            <div class="col-lg-6">
                <div class="hero-badge">
                    <i class="bi bi-stars me-2"></i> A community for home cooks
                </div>
                <h1 class="hero-title heading-serif">
                    Discover, Cook & Share <span class="brand-accent">Unforgettable</span> Recipes
                </h1>
                <p class="lead text-muted mb-4">
                    Your personal digital recipe companion. Explore chef-crafted culinary creations, scale ingredients instantly, and share your favorite homemade meals with food lovers worldwide.
                </p>

                <!-- Hero Search Form -->
                <form action="recipes.php" method="GET" class="hero-search-box mb-4">
                    <i class="bi bi-search text-muted fs-5 ms-3"></i>
                    <input type="text" 
                           name="search" 
                           class="hero-search-input" 
                           placeholder="Search chicken pizza, pasta, cakes, fried rice..." 
                           required>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">
                        Search
                    </button>
                </form>

                <!-- Quick Action Buttons & Stats -->
                <div class="d-flex flex-wrap align-items-center gap-3 mb-4">
                    <a href="recipes.php" class="btn btn-primary rounded-pill px-4 py-2">
                        <i class="bi bi-journal-text me-1"></i> Browse All Recipes
                    </a>
                    <?php if (is_logged_in()): ?>
                        <a href="add_recipe.php" class="btn btn-outline-dark rounded-pill px-4 py-2">
                            <i class="bi bi-plus-circle me-1"></i> Share a Recipe
                        </a>
                    <?php else: ?>
                        <a href="auth/register.php" class="btn btn-outline-dark rounded-pill px-4 py-2">
                            <i class="bi bi-person-plus me-1"></i> Join for Free
                        </a>
                    <?php endif; ?>
                </div>

                <div class="row pt-2 g-3 border-top border-secondary border-opacity-10 text-muted small">
                    <div class="col-auto">
                        <strong class="text-dark fs-5 d-block"><?php echo (int)$total_recipes_count; ?></strong>
                        <span>Community Recipes</span>
                    </div>
                    <div class="col-auto border-start ps-3">
                        <strong class="text-dark fs-5 d-block"><?php echo count(recipe_categories()); ?></strong>
                        <span>Recipe Categories</span>
                    </div>
                    <div class="col-auto border-start ps-3">
                        <strong class="text-dark fs-5 d-block">Made for</strong>
                        <span>Everyday cooking</span>
                    </div>
                </div>
            </div>

            <!-- Right Column: Interactive Recipe Carousel Slider -->
            <div class="col-lg-6">
                <div class="hero-image-container">
                    <div id="heroRecipeCarousel" class="carousel slide hero-main-carousel" data-bs-ride="carousel">
                        <div class="carousel-indicators">
                            <button type="button" data-bs-target="#heroRecipeCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                            <button type="button" data-bs-target="#heroRecipeCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
                            <button type="button" data-bs-target="#heroRecipeCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
                        </div>
                        <div class="carousel-inner">
                            <div class="carousel-item active">
                                <img src="<?php echo htmlspecialchars(get_recipe_image_url('chicken_pizza.jpg', 'Chicken Pizza')); ?>" class="d-block w-100" style="height: 420px; object-fit: cover;" alt="BBQ Chicken Pizza">
                                <div class="carousel-caption d-none d-md-block bg-dark bg-opacity-75 rounded-3 p-2 mb-3">
                                    <h5 class="fw-bold mb-0">Artisan BBQ Chicken Pizza</h5>
                                    <small class="text-white-50">Baked fresh with savory mozzarella & caramelized onions</small>
                                </div>
                            </div>
                            <div class="carousel-item">
                                <img src="<?php echo htmlspecialchars(get_recipe_image_url('chicken_pasta.jpg', 'Chicken Pasta')); ?>" class="d-block w-100" style="height: 420px; object-fit: cover;" alt="Garlic Alfredo Pasta">
                                <div class="carousel-caption d-none d-md-block bg-dark bg-opacity-75 rounded-3 p-2 mb-3">
                                    <h5 class="fw-bold mb-0">Creamy Garlic Chicken Alfredo</h5>
                                    <small class="text-white-50">Silky fettuccine with golden seared chicken strips</small>
                                </div>
                            </div>
                            <div class="carousel-item">
                                <img src="<?php echo htmlspecialchars(get_recipe_image_url('chocolate_cake.jpg', 'Chocolate Cake')); ?>" class="d-block w-100" style="height: 420px; object-fit: cover;" alt="Decadent Chocolate Cake">
                                <div class="carousel-caption d-none d-md-block bg-dark bg-opacity-75 rounded-3 p-2 mb-3">
                                    <h5 class="fw-bold mb-0">Decadent Molten Fudge Cake</h5>
                                    <small class="text-white-50">Rich dark cocoa with luscious warm chocolate ganache</small>
                                </div>
                            </div>
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#heroRecipeCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-label="Previous"></span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#heroRecipeCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-label="Next"></span>
                        </button>
                    </div>

                    <!-- Floating decorative UI cards -->
                    <div class="hero-floating-card d-none d-sm-flex align-items-center gap-3">
                        <div class="stat-icon-wrap bg-success bg-opacity-10 text-success">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-dark mb-0">Ready in 25 mins</div>
                            <small class="text-muted">Easy step-by-step cooking</small>
                        </div>
                    </div>

                    <div class="hero-rating-badge d-none d-sm-flex align-items-center text-warning">
                        <i class="bi bi-star-fill me-1"></i>
                        <span class="text-dark">4.9 / 5.0</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 2. Recipe Categories Section -->
<section class="py-5 bg-white border-bottom border-secondary border-opacity-10" id="categories">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <span class="text-primary fw-semibold text-uppercase tracking-wide small">Popular Categories</span>
                <h2 class="fw-bold mb-0">What Are You Craving Today?</h2>
            </div>
            <a href="recipes.php" class="text-primary text-decoration-none fw-semibold d-none d-md-inline">
                All Categories <i class="bi bi-arrow-right"></i>
            </a>
        </div>

        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-6 g-3">
            <div class="col">
                <a href="recipes.php?search=pizza" class="category-tile">
                    <div class="category-icon-bubble">
                        <i class="bi bi-pie-chart-fill"></i>
                    </div>
                    <h6 class="fw-bold mb-1">Pizza</h6>
                    <small class="text-muted">Crispy Crusts</small>
                </a>
            </div>
            <div class="col">
                <a href="recipes.php?search=pasta" class="category-tile">
                    <div class="category-icon-bubble">
                        <i class="bi bi-egg-fried"></i>
                    </div>
                    <h6 class="fw-bold mb-1">Pasta</h6>
                    <small class="text-muted">Italian Classics</small>
                </a>
            </div>
            <div class="col">
                <a href="recipes.php?search=burger" class="category-tile">
                    <div class="category-icon-bubble">
                        <i class="bi bi-circle-fill"></i>
                    </div>
                    <h6 class="fw-bold mb-1">Burgers</h6>
                    <small class="text-muted">Juicy Patties</small>
                </a>
            </div>
            <div class="col">
                <a href="recipes.php?search=rice" class="category-tile">
                    <div class="category-icon-bubble">
                        <i class="bi bi-bowl"></i>
                    </div>
                    <h6 class="fw-bold mb-1">Fried Rice</h6>
                    <small class="text-muted">Asian Flavors</small>
                </a>
            </div>
            <div class="col">
                <a href="recipes.php?search=cake" class="category-tile">
                    <div class="category-icon-bubble">
                        <i class="bi bi-cake2-fill"></i>
                    </div>
                    <h6 class="fw-bold mb-1">Desserts</h6>
                    <small class="text-muted">Sweet Delights</small>
                </a>
            </div>
            <div class="col">
                <a href="recipes.php?search=pancake" class="category-tile">
                    <div class="category-icon-bubble">
                        <i class="bi bi-cup-hot-fill"></i>
                    </div>
                    <h6 class="fw-bold mb-1">Breakfast</h6>
                    <small class="text-muted">Morning Treats</small>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- 3. Popular & Featured Recipes Section -->
<section class="py-5" id="featured-recipes">
    <div class="container">
        <div class="text-center max-w-700 mx-auto mb-5">
            <span class="text-primary fw-semibold text-uppercase tracking-wide small">Fresh From The Kitchen</span>
            <h2 class="fw-bold heading-serif fs-1 mb-2">Featured Chef Recipes</h2>
            <p class="text-muted">Hand-picked culinary masterpieces crafted by our vibrant community of home cooks and chefs.</p>
        </div>

        <div class="row g-4">
            <?php if (!empty($featured_recipes)): ?>
                <?php foreach ($featured_recipes as $recipe): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="recipe-card reveal-on-scroll">
                            <div class="recipe-card-img-wrap">
                                <img src="<?php echo htmlspecialchars(get_recipe_image_url($recipe['image'], $recipe['title'])); ?>"
                                     alt="<?php echo htmlspecialchars($recipe['title']); ?>" 
                                     class="recipe-card-img"
                                     loading="lazy">
                                <div class="recipe-badge-time">
                                    <i class="bi bi-clock me-1"></i> <?php echo (int)$recipe['prep_time'] + (int)$recipe['cook_time']; ?> min
                                </div>
                                <?php render_favorite_button((int)$recipe['id'], isset($favorite_ids[(int)$recipe['id']]), 'index.php'); ?>
                            </div>

                            <div class="recipe-card-body">
                                <h5 class="recipe-card-title">
                                    <a href="recipe.php?id=<?php echo $recipe['id']; ?>">
                                        <?php echo htmlspecialchars($recipe['title']); ?>
                                    </a>
                                </h5>
                                <span class="text-primary small fw-semibold mb-2"><?php echo htmlspecialchars($recipe['category']); ?></span>
                                <p class="recipe-card-desc">
                                    <?php echo htmlspecialchars($recipe['description'] ?? ''); ?>
                                </p>
                                
                                <div class="recipe-card-footer">
                                    <div class="d-flex align-items-center">
                                        <div class="user-avatar-circle me-2" style="width: 26px; height: 26px; font-size: 0.75rem;">
                                            <?php echo strtoupper(substr($recipe['author_name'] ?? 'A', 0, 1)); ?>
                                        </div>
                                        <small class="text-muted"><?php echo htmlspecialchars($recipe['author_name'] ?? 'Chef'); ?></small>
                                    </div>
                                    <a href="recipe.php?id=<?php echo $recipe['id']; ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                        View Recipe <i class="bi bi-chevron-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <p class="text-muted">No recipes found in the database. Please import sample data via <code>database.sql</code>.</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="text-center mt-5">
            <a href="recipes.php" class="btn btn-primary rounded-pill px-5 py-3 fs-6">
                <i class="bi bi-grid-fill me-2"></i> Explore All Digital Recipes
            </a>
        </div>
    </div>
</section>

<!-- 4. Interactive Features Highlight Banner -->
<section class="py-5 bg-white border-top border-bottom border-secondary border-opacity-10">
    <div class="container">
        <div class="row g-4 align-items-center">
            <div class="col-lg-4 col-md-6">
                <div class="d-flex align-items-start p-3 rounded-4 bg-light">
                    <div class="stat-icon-wrap bg-primary bg-opacity-10 text-primary me-3 flex-shrink-0">
                        <i class="bi bi-sliders"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1">Dynamic Portion Scaler</h5>
                        <p class="text-muted small mb-0">Scale ingredient quantities up or down with real-time JavaScript portion calculators.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="d-flex align-items-start p-3 rounded-4 bg-light">
                    <div class="stat-icon-wrap bg-success bg-opacity-10 text-success me-3 flex-shrink-0">
                        <i class="bi bi-search"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1">Instant Live Filtering</h5>
                        <p class="text-muted small mb-0">Filter dishes instantaneously by ingredients, keywords, or dietary categories without reloading.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mx-auto">
                <div class="d-flex align-items-start p-3 rounded-4 bg-light">
                    <div class="stat-icon-wrap bg-warning bg-opacity-10 text-dark me-3 flex-shrink-0">
                        <i class="bi bi-check2-square"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1">Interactive Checklist</h5>
                        <p class="text-muted small mb-0">Check off ingredients as you prepare and track your cooking progress seamlessly.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 5. About TasteBook Section -->
<section class="py-5" id="about">
    <div class="container py-4">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <div class="position-relative">
                    <img src="<?php echo htmlspecialchars(get_recipe_image_url('fried_rice.jpg', 'Cooking Experience')); ?>" alt="About TasteBook" class="img-fluid rounded-4 shadow-lg w-100" style="max-height: 420px; object-fit: cover;">
                    <div class="position-absolute bottom-0 start-0 m-4 p-3 bg-white rounded-3 shadow border border-light d-none d-sm-block">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-stars text-warning fs-3"></i>
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">Good food brings us together</h6>
                                <small class="text-muted">Discover something delicious</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <span class="text-primary fw-semibold text-uppercase tracking-wide small">About TasteBook</span>
                <h2 class="fw-bold heading-serif fs-1 mb-3">Elevating Your Home Cooking Experience</h2>
                <p class="text-muted mb-3">
                    TasteBook brings recipes and home cooks together in one welcoming place to discover, save, and share delicious ideas.
                </p>
                <p class="text-muted mb-4">
                    Find inspiration for your next meal, keep your favorite recipes close, and share the dishes you love with the community.
                </p>

                <div class="row g-3 mb-4">
                    <div class="col-sm-6">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-check-circle-fill text-success fs-5 me-2"></i>
                            <span class="fw-semibold text-dark">No External Heavy Frameworks</span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-check-circle-fill text-success fs-5 me-2"></i>
                            <span class="fw-semibold text-dark">Secure Session & Password Hashing</span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-check-circle-fill text-success fs-5 me-2"></i>
                            <span class="fw-semibold text-dark">Dynamic DOM Search & Filtering</span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-check-circle-fill text-success fs-5 me-2"></i>
                            <span class="fw-semibold text-dark">Prepared MySQL Statements</span>
                        </div>
                    </div>
                </div>

                <a href="contact.php" class="btn btn-outline-primary rounded-pill px-4">
                    <i class="bi bi-envelope me-1"></i> Send Us Feedback
                </a>
            </div>
        </div>
    </div>
</section>

<!-- 6. Call-to-Action Section -->
<section class="py-5 bg-dark text-white text-center position-relative overflow-hidden" style="background: radial-gradient(circle at center, #2c333d 0%, #17191d 100%);">
    <div class="container py-4">
        <div class="max-w-700 mx-auto">
            <span class="badge bg-primary px-3 py-2 rounded-pill mb-3">Join The Community</span>
            <h2 class="display-6 fw-bold heading-serif mb-3 text-white">Have a Secret Family Recipe to Share?</h2>
            <p class="text-white-50 lead mb-4">
                Sign up today to create your personalized digital culinary book and inspire cooking enthusiasts everywhere.
            </p>
            <div class="d-flex justify-content-center gap-3 flex-wrap">
                <?php if (is_logged_in()): ?>
                    <a href="add_recipe.php" class="btn btn-primary btn-lg rounded-pill px-5">
                        <i class="bi bi-plus-circle me-1"></i> Add Your Recipe Now
                    </a>
                <?php else: ?>
                    <a href="auth/register.php" class="btn btn-primary btn-lg rounded-pill px-5">
                        <i class="bi bi-person-plus me-1"></i> Create Free Account
                    </a>
                    <a href="auth/login.php" class="btn btn-outline-light btn-lg rounded-pill px-4">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
