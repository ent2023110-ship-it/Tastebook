<?php
/**
 * TasteBook – Digital Recipe Book
 * Recipe Details Page (recipe.php)
 * 
 * ICT 2209 - Web Technologies Mini Project
 * Rajarata University of Sri Lanka
 */

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

// Get and validate recipe ID
$recipe_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;

if ($recipe_id <= 0) {
    set_flash_message('error', 'Invalid recipe ID specified.');
    header("Location: recipes.php");
    exit();
}

// Fetch recipe details with author info using Prepared Statement
$stmt = $conn->prepare("
    SELECT r.*, u.username AS author_name, u.email AS author_email 
    FROM recipes r 
    JOIN users u ON r.user_id = u.id 
    WHERE r.id = ? 
    LIMIT 1
");
$stmt->bind_param("i", $recipe_id);
$stmt->execute();
$result = $stmt->get_result();

if (!$result || $result->num_rows === 0) {
    $stmt->close();
    $page_title = 'Recipe Not Found';
    require_once __DIR__ . '/includes/header.php';
    ?>
    <div class="container py-5 text-center">
        <div class="stat-icon-wrap bg-danger bg-opacity-10 text-danger mx-auto mb-3" style="width: 80px; height: 80px; font-size: 2.5rem;">
            <i class="bi bi-exclamation-triangle"></i>
        </div>
        <h2 class="fw-bold text-dark mb-2">Recipe Not Found</h2>
        <p class="text-muted mb-4">The recipe you requested may have been deleted or the link is invalid.</p>
        <a href="recipes.php" class="btn btn-primary rounded-pill px-4">
            <i class="bi bi-arrow-left me-1"></i> Back to All Recipes
        </a>
    </div>
    <?php
    require_once __DIR__ . '/includes/footer.php';
    exit();
}

$recipe = $result->fetch_assoc();
$stmt->close();

$view_update = $conn->prepare('UPDATE recipes SET views = views + 1 WHERE id = ?');
$view_update->bind_param('i', $recipe_id);
$view_update->execute();
$view_update->close();

$favorite_ids = get_user_favorite_ids($conn);
$is_favorite = isset($favorite_ids[$recipe_id]);
$related_stmt = $conn->prepare('SELECT id, title, description, image FROM recipes WHERE category = ? AND id <> ? ORDER BY created_at DESC LIMIT 3');
$related_stmt->bind_param('si', $recipe['category'], $recipe_id);
$related_stmt->execute();
$related_result = $related_stmt->get_result();
$related_recipes = [];
while ($related = $related_result->fetch_assoc()) {
    $related_recipes[] = $related;
}
$related_stmt->close();

$page_title = $recipe['title'];
require_once __DIR__ . '/includes/header.php';

// Parse ingredients line by line
$raw_ingredients = explode("\n", str_replace("\r", "", $recipe['ingredients']));
$ingredients_list = array_filter(array_map('trim', $raw_ingredients));

// Parse instructions line by line
$raw_instructions = explode("\n", str_replace("\r", "", $recipe['instructions']));
$instructions_list = array_filter(array_map('trim', $raw_instructions));
?>

<!-- Breadcrumb Navigation -->
<div class="bg-white border-bottom py-2">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-muted">Home</a></li>
                <li class="breadcrumb-item"><a href="recipes.php" class="text-decoration-none text-muted">Recipes</a></li>
                <li class="breadcrumb-item active text-truncate" aria-current="page"><?php echo htmlspecialchars($recipe['title']); ?></li>
            </ol>
        </nav>
    </div>
</div>

<!-- Recipe Header & Hero -->
<header class="recipe-details-header">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-primary rounded-pill px-3 py-1"><?php echo htmlspecialchars($recipe['category']); ?></span>
                    <span class="text-muted small"><i class="bi bi-calendar-event me-1"></i> <?php echo format_recipe_date($recipe['created_at']); ?></span>
                </div>
                <h1 class="fw-bold heading-serif display-5 mb-3 text-dark"><?php echo htmlspecialchars($recipe['title']); ?></h1>
                <p class="lead text-muted mb-4"><?php echo htmlspecialchars($recipe['description'] ?? ''); ?></p>

                <!-- Chef info & Action Buttons -->
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 pt-3 border-top">
                    <div class="d-flex align-items-center">
                        <div class="user-avatar-circle me-3" style="width: 44px; height: 44px; font-size: 1.1rem;">
                            <?php echo strtoupper(substr($recipe['author_name'] ?? 'C', 0, 1)); ?>
                        </div>
                        <div>
                            <span class="text-muted small d-block">Recipe by</span>
                            <strong class="text-dark"><?php echo htmlspecialchars($recipe['author_name']); ?></strong>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-3" id="printRecipeBtn" title="Print this Recipe">
                            <i class="bi bi-printer me-1"></i> Print
                        </button>
                        <button type="button" class="btn btn-outline-primary rounded-pill px-3" id="shareRecipeBtn" data-share-title="<?php echo htmlspecialchars($recipe['title'], ENT_QUOTES, 'UTF-8'); ?>" data-share-url="<?php echo htmlspecialchars('recipe.php?id=' . (int)$recipe['id'], ENT_QUOTES, 'UTF-8'); ?>">
                            <i class="bi bi-share me-1"></i> Share
                        </button>
                        <?php render_favorite_button($recipe_id, $is_favorite, 'recipe.php?id=' . $recipe_id, $is_favorite ? 'Remove favorite' : 'Save', true); ?>
                        <?php if (is_logged_in() && (int)$_SESSION['user_id'] === (int)$recipe['user_id']): ?>
                            <a href="edit_recipe.php?id=<?php echo (int)$recipe_id; ?>" class="btn btn-outline-primary rounded-pill px-3">
                                <i class="bi bi-pencil-square me-1"></i> Edit
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="recipe-hero-img-wrap">
                    <img src="<?php echo htmlspecialchars(get_recipe_image_url($recipe['image'], $recipe['title'])); ?>"
                         alt="<?php echo htmlspecialchars($recipe['title']); ?>" 
                         class="recipe-hero-img">
                </div>
            </div>
        </div>
    </div>
</header>

<!-- Recipe Core Details: Ingredients & Instructions -->
<section class="py-5">
    <div class="container">
        <!-- Quick Meta Row -->
        <div class="row g-3 mb-5">
            <div class="col-6 col-md-3">
                <div class="recipe-meta-pill">
                    <div class="text-primary fs-4 mb-1"><i class="bi bi-clock-history"></i></div>
                    <small class="text-muted d-block">Prep & Cook Time</small>
                    <strong class="text-dark"><?php echo (int)$recipe['prep_time'] + (int)$recipe['cook_time']; ?> min total</strong>
                    <span class="text-muted small d-block"><?php echo (int)$recipe['prep_time']; ?> prep · <?php echo (int)$recipe['cook_time']; ?> cook</span>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="recipe-meta-pill">
                    <div class="text-success fs-4 mb-1"><i class="bi bi-people-fill"></i></div>
                    <small class="text-muted d-block">Serving Size</small>
                    <strong class="text-dark"><span id="servingCountDisplay"><?php echo (int)$recipe['servings']; ?></span> Servings</strong>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="recipe-meta-pill">
                    <div class="text-warning fs-4 mb-1"><i class="bi bi-bar-chart-fill"></i></div>
                    <small class="text-muted d-block">Difficulty</small>
                    <strong class="text-dark"><?php echo htmlspecialchars($recipe['difficulty']); ?></strong>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="recipe-meta-pill">
                    <div class="text-info fs-4 mb-1"><i class="bi bi-journal-check"></i></div>
                    <small class="text-muted d-block">Ingredients Count</small>
                    <strong class="text-dark"><?php echo count($ingredients_list); ?> Items</strong>
                </div>
            </div>
        </div>

        <div class="row g-5">
            <!-- Left: Ingredients Checklist & Serving Adjuster -->
            <div class="col-lg-5">
                <div class="recipe-box sticky-top" style="top: 100px;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-basket2-fill text-primary me-2"></i> Ingredients</h4>
                        
                        <!-- JavaScript Portion Multiplier Controls -->
                        <div class="d-flex align-items-center bg-light rounded-pill p-1 border">
                            <button type="button" class="btn btn-sm btn-light rounded-circle p-0" id="servingDecreaseBtn" style="width: 26px; height: 26px;" title="Decrease Servings">
                                <i class="bi bi-dash"></i>
                            </button>
                            <span class="px-2 small fw-bold text-dark">Servings</span>
                            <button type="button" class="btn btn-sm btn-light rounded-circle p-0" id="servingIncreaseBtn" style="width: 26px; height: 26px;" title="Increase Servings">
                                <i class="bi bi-plus"></i>
                            </button>
                        </div>
                    </div>

                    <p class="text-muted small mb-3">Click on any ingredient below to check it off as you cook:</p>

                    <div class="ingredients-checklist" data-base-servings="<?php echo (int)$recipe['servings']; ?>">
                        <?php foreach ($ingredients_list as $index => $item): ?>
                            <label class="ingredient-item">
                                <input type="checkbox" class="form-check-input me-3 mt-0" aria-label="Mark ingredient done">
                                <span class="ingredient-text text-dark">
                                    <?php 
                                    // Parse numeric quantities if possible for portion multiplier
                                    if (preg_match('/^([0-9\/\.\s]+)(.*)$/', $item, $matches)) {
                                        $num_part = trim($matches[1]);
                                        $rest_part = $matches[2];
                                        $float_val = 0;
                                        if (preg_match('/^(\d+)\s+(\d+)\/(\d+)$/', $num_part, $quantity_parts) && (int)$quantity_parts[3] !== 0) {
                                            $float_val = (int)$quantity_parts[1] + ((int)$quantity_parts[2] / (int)$quantity_parts[3]);
                                        } elseif (preg_match('/^(\d+)\/(\d+)$/', $num_part, $quantity_parts) && (int)$quantity_parts[2] !== 0) {
                                            $float_val = (int)$quantity_parts[1] / (int)$quantity_parts[2];
                                        } elseif (strpos($num_part, '/') !== false) {
                                            $parts = explode('/', $num_part);
                                            $float_val = (count($parts) === 2 && $parts[1] != 0) ? ($parts[0] / $parts[1]) : 1;
                                        } else {
                                            $float_val = floatval($num_part);
                                        }
                                        if ($float_val > 0) {
                                            echo '<span class="fw-bold ingredient-qty text-primary" data-base-qty="' . $float_val . '">' . htmlspecialchars($num_part) . '</span>' . htmlspecialchars($rest_part);
                                        } else {
                                            echo htmlspecialchars($item);
                                        }
                                    } else {
                                        echo htmlspecialchars($item);
                                    }
                                    ?>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Right: Step-by-Step Cooking Instructions -->
            <div class="col-lg-7">
                <div class="recipe-box">
                    <h4 class="fw-bold mb-4 text-dark"><i class="bi bi-fire text-primary me-2"></i> Step-by-Step Instructions</h4>

                    <div class="instructions-timeline">
                        <?php 
                        $step_number = 1;
                        foreach ($instructions_list as $step): 
                            // Strip leading numbers if user typed "1. " or "Step 1:"
                            $clean_step = preg_replace('/^(Step\s*\d+:?|\d+[\.\)]\s*)/i', '', $step);
                        ?>
                            <div class="instruction-step">
                                <div class="instruction-step-num"><?php echo $step_number++; ?></div>
                                <div class="instruction-step-body">
                                    <h6 class="fw-bold text-dark mb-1">Step <?php echo ($step_number - 1); ?></h6>
                                    <p class="text-secondary mb-0" style="line-height: 1.7;"><?php echo nl2br(htmlspecialchars($clean_step)); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Cooking Tips Box -->
                    <div class="mt-4 p-3 bg-light rounded-3 border-start border-4 border-warning">
                        <div class="d-flex align-items-center mb-1">
                            <i class="bi bi-lightbulb-fill text-warning me-2 fs-5"></i>
                            <strong class="text-dark">Chef's Pro Tip:</strong>
                        </div>
                        <p class="small text-muted mb-0">
                            For best flavor results, always ensure spices and herbs are freshly ground and let cooked meats rest for 5 minutes before slicing.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if ($related_recipes): ?>
<section class="py-5 bg-white border-top">
    <div class="container">
        <h2 class="heading-serif fw-bold mb-4">More in <?php echo htmlspecialchars($recipe['category']); ?></h2>
        <div class="row g-4">
            <?php foreach ($related_recipes as $related): ?>
                <div class="col-md-4">
                    <article class="recipe-card h-100">
                        <a href="recipe.php?id=<?php echo (int)$related['id']; ?>" class="recipe-card-img-wrap">
                            <img src="<?php echo htmlspecialchars(get_recipe_image_url($related['image'], $related['title'])); ?>" alt="<?php echo htmlspecialchars($related['title']); ?>" class="recipe-card-img" loading="lazy">
                        </a>
                        <div class="recipe-card-body">
                            <h3 class="h5 recipe-card-title"><a href="recipe.php?id=<?php echo (int)$related['id']; ?>"><?php echo htmlspecialchars($related['title']); ?></a></h3>
                            <p class="recipe-card-desc"><?php echo htmlspecialchars($related['description'] ?? ''); ?></p>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
