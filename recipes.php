<?php
/**
 * TasteBook – Digital Recipe Book
 * All Recipes Page (recipes.php)
 * 
 * ICT 2209 - Web Technologies Mini Project
 * Rajarata University of Sri Lanka
 */

$page_title = 'Explore All Recipes';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/header.php';

// Check if search query was passed via GET from Home page
$initial_search = isset($_GET['search']) && is_string($_GET['search']) ? trim($_GET['search']) : '';
$initial_category = isset($_GET['category']) && is_string($_GET['category']) ? trim($_GET['category']) : 'all';
if (!in_array($initial_category, array_merge(['all'], array_map('strtolower', recipe_categories())), true)) {
    $initial_category = 'all';
}
$initial_sort = isset($_GET['sort']) && is_string($_GET['sort']) ? trim($_GET['sort']) : 'newest';
if (!in_array($initial_sort, ['newest', 'oldest', 'popular', 'alphabetical'], true)) {
    $initial_sort = 'newest';
}
$favorite_ids = get_user_favorite_ids($conn);

// Fetch all recipes from MySQL database
$recipes = [];
$sql = "SELECT r.*, u.username AS author_name,
               (SELECT COUNT(*) FROM favorites f WHERE f.recipe_id = r.id) AS favorite_count
        FROM recipes r 
        JOIN users u ON r.user_id = u.id 
        ORDER BY r.created_at DESC";

$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $recipes[] = $row;
    }
}
?>

<!-- Page Header Banner -->
<section class="py-5 bg-white border-bottom border-secondary border-opacity-10">
    <div class="container text-center">
        <span class="text-primary fw-semibold text-uppercase tracking-wide small">Digital Recipe Collection</span>
        <h1 class="fw-bold heading-serif display-5 mb-2">Explore Delicious Dishes</h1>
        <p class="text-muted max-w-700 mx-auto">
            Browse our ever-growing catalog of mouthwatering recipes. Use the search bar and category filters below to instantly find your next meal.
        </p>

        <!-- Search & Filter Controls -->
        <div class="row justify-content-center mt-4">
            <div class="col-lg-7 col-md-9">
                <div class="input-group input-group-lg shadow-sm rounded-pill overflow-hidden border">
                    <span class="input-group-text bg-white border-0 ps-4 text-muted"><i class="bi bi-search fs-5"></i></span>
                    <input type="text" 
                           id="recipeSearchInput" 
                           class="form-control border-0 px-3 fs-6" 
                           placeholder="Type to filter recipes instantly by title or ingredients..." 
                           value="<?php echo htmlspecialchars($initial_search); ?>"
                           aria-label="Search recipes">
                    <button class="btn btn-light border-0 text-muted px-3" type="button" id="clearSearchBtn" title="Clear Search">
                        <i class="bi bi-x-circle"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Category Filter Pills -->
        <div class="d-flex flex-wrap justify-content-center gap-2 mt-4" id="categoryFilterContainer">
            <button type="button" class="category-pill-btn category-filter-btn <?php echo $initial_category === 'all' ? 'active' : ''; ?>" data-category="all">
                <i class="bi bi-grid-fill me-1"></i> All
            </button>
            <button type="button" class="category-pill-btn category-filter-btn <?php echo $initial_category === 'breakfast' ? 'active' : ''; ?>" data-category="breakfast">
                <i class="bi bi-cup-hot-fill me-1"></i> Breakfast
            </button>
            <button type="button" class="category-pill-btn category-filter-btn <?php echo $initial_category === 'lunch' ? 'active' : ''; ?>" data-category="lunch">
                <i class="bi bi-sun me-1"></i> Lunch
            </button>
            <button type="button" class="category-pill-btn category-filter-btn <?php echo $initial_category === 'dinner' ? 'active' : ''; ?>" data-category="dinner">
                <i class="bi bi-moon-stars me-1"></i> Dinner
            </button>
            <button type="button" class="category-pill-btn category-filter-btn <?php echo $initial_category === 'desserts' ? 'active' : ''; ?>" data-category="desserts">
                <i class="bi bi-cake2-fill me-1"></i> Desserts
            </button>
            <button type="button" class="category-pill-btn category-filter-btn <?php echo $initial_category === 'vegetarian' ? 'active' : ''; ?>" data-category="vegetarian">
                <i class="bi bi-leaf me-1"></i> Vegetarian
            </button>
            <button type="button" class="category-pill-btn category-filter-btn <?php echo $initial_category === 'healthy' ? 'active' : ''; ?>" data-category="healthy">
                <i class="bi bi-heart-pulse me-1"></i> Healthy
            </button>
            <button type="button" class="category-pill-btn category-filter-btn <?php echo $initial_category === 'snacks' ? 'active' : ''; ?>" data-category="snacks">
                <i class="bi bi-cookie me-1"></i> Snacks
            </button>
        </div>
        <div class="row justify-content-center g-2 mt-3">
            <div class="col-sm-6 col-lg-3">
                <label class="visually-hidden" for="difficultyFilter">Difficulty</label>
                <select class="form-select" id="difficultyFilter">
                    <option value="all">Any difficulty</option>
                    <option value="easy">Easy</option>
                    <option value="medium">Medium</option>
                    <option value="hard">Hard</option>
                </select>
            </div>
            <div class="col-sm-6 col-lg-3">
                <label class="visually-hidden" for="prepTimeFilter">Maximum prep time</label>
                <select class="form-select" id="prepTimeFilter">
                    <option value="all">Any prep time</option>
                    <option value="15">Up to 15 min prep</option>
                    <option value="30">Up to 30 min prep</option>
                    <option value="60">Up to 60 min prep</option>
                </select>
            </div>
            <div class="col-sm-6 col-lg-3">
                <label class="visually-hidden" for="cookTimeFilter">Maximum cook time</label>
                <select class="form-select" id="cookTimeFilter">
                    <option value="all">Any cook time</option>
                    <option value="15">Up to 15 min cook</option>
                    <option value="30">Up to 30 min cook</option>
                    <option value="60">Up to 60 min cook</option>
                </select>
            </div>
            <div class="col-sm-6 col-lg-3">
                <label class="visually-hidden" for="recipeSort">Sort recipes</label>
                <select class="form-select" id="recipeSort">
                    <option value="newest" <?php echo $initial_sort === 'newest' ? 'selected' : ''; ?>>Newest</option>
                    <option value="oldest" <?php echo $initial_sort === 'oldest' ? 'selected' : ''; ?>>Oldest</option>
                    <option value="popular" <?php echo $initial_sort === 'popular' ? 'selected' : ''; ?>>Most favorited</option>
                    <option value="alphabetical" <?php echo $initial_sort === 'alphabetical' ? 'selected' : ''; ?>>Alphabetical</option>
                </select>
            </div>
        </div>
    </div>
</section>

<!-- Recipes Grid Section -->
<section class="py-5">
    <div class="container">
        <!-- Results Counter Bar -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <span class="fw-semibold text-muted" id="recipeCountDisplay">
                <?php echo count($recipes); ?> Recipes Available
            </span>
            
            <?php if (is_logged_in()): ?>
                <a href="add_recipe.php" class="btn btn-sm btn-primary rounded-pill px-3">
                    <i class="bi bi-plus-circle me-1"></i> Share Your Recipe
                </a>
            <?php endif; ?>
        </div>

        <!-- Recipe Cards Grid -->
        <div class="row g-4" id="recipeCardsGrid">
            <?php if (!empty($recipes)): ?>
                <?php foreach ($recipes as $recipe): ?>
                    <div class="col-lg-4 col-md-6 recipe-item-col" 
                         data-title="<?php echo htmlspecialchars(strtolower($recipe['title']), ENT_QUOTES, 'UTF-8'); ?>"
                         data-description="<?php echo htmlspecialchars(strtolower($recipe['description'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                         data-ingredients="<?php echo htmlspecialchars(strtolower($recipe['ingredients']), ENT_QUOTES, 'UTF-8'); ?>"
                         data-category="<?php echo htmlspecialchars(strtolower($recipe['category']), ENT_QUOTES, 'UTF-8'); ?>"
                         data-difficulty="<?php echo htmlspecialchars(strtolower($recipe['difficulty']), ENT_QUOTES, 'UTF-8'); ?>"
                         data-prep-time="<?php echo (int)$recipe['prep_time']; ?>"
                         data-cook-time="<?php echo (int)$recipe['cook_time']; ?>"
                         data-favorites="<?php echo (int)$recipe['favorite_count']; ?>"
                         data-created="<?php echo (int)strtotime($recipe['created_at']); ?>">
                        
                        <div class="recipe-card">
                            <div class="recipe-card-img-wrap">
                                <img src="<?php echo htmlspecialchars(get_recipe_image_url($recipe['image'], $recipe['title'])); ?>"
                                     alt="<?php echo htmlspecialchars($recipe['title']); ?>" 
                                     class="recipe-card-img"
                                     loading="lazy">
                                <div class="recipe-badge-time">
                                    <i class="bi bi-clock me-1"></i> <?php echo (int)$recipe['prep_time'] + (int)$recipe['cook_time']; ?> min
                                </div>
                                <?php render_favorite_button((int)$recipe['id'], isset($favorite_ids[(int)$recipe['id']]), 'recipes.php'); ?>
                            </div>

                            <div class="recipe-card-body">
                                <h5 class="recipe-card-title">
                                    <a href="recipe.php?id=<?php echo $recipe['id']; ?>">
                                        <?php echo htmlspecialchars($recipe['title']); ?>
                                    </a>
                                </h5>
                                <span class="text-primary small fw-semibold mb-2"><?php echo htmlspecialchars($recipe['category']); ?> · <?php echo htmlspecialchars($recipe['difficulty']); ?></span>
                                <p class="recipe-card-desc">
                                    <?php echo htmlspecialchars($recipe['description'] ?? ''); ?>
                                </p>
                                
                                <div class="recipe-card-footer">
                                    <div class="d-flex align-items-center">
                                        <div class="user-avatar-circle me-2" style="width: 26px; height: 26px; font-size: 0.75rem;">
                                            <?php echo strtoupper(substr($recipe['author_name'] ?? 'A', 0, 1)); ?>
                                        </div>
                                        <div>
                                            <small class="text-dark fw-semibold d-block line-height-1"><?php echo htmlspecialchars($recipe['author_name'] ?? 'Chef'); ?></small>
                                            <small class="text-muted" style="font-size: 0.75rem;"><?php echo format_recipe_date($recipe['created_at']); ?></small>
                                        </div>
                                    </div>
                                    <a href="recipe.php?id=<?php echo $recipe['id']; ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                        View Recipe <i class="bi bi-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- No Results Fallback Placeholder -->
        <div id="noResultsMessage" class="text-center py-5 d-none">
            <div class="stat-icon-wrap bg-light text-muted mx-auto mb-3" style="width: 80px; height: 80px; font-size: 2.5rem;">
                <i class="bi bi-search"></i>
            </div>
            <h4 class="fw-bold text-dark">No Matching Recipes Found</h4>
            <p class="text-muted mb-4">Try adjusting your search terms or clearing the category filters.</p>
            <button type="button" class="btn btn-outline-primary rounded-pill px-4" id="resetRecipeFilters">
                Reset All Filters
            </button>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
