<?php
/**
 * TasteBook – Digital Recipe Book
 * Add Recipe Page (add_recipe.php)
 * 
 * ICT 2209 - Web Technologies Mini Project
 * Rajarata University of Sri Lanka
 */

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

// Strictly protect page: only logged-in users can add recipes
require_login();

$page_title = 'Add New Recipe';
$errors = [];
$title = '';
$description = '';
$ingredients = '';
$instructions = '';
$category = 'Dinner';
$prep_time = 15;
$cook_time = 30;
$servings = 4;
$difficulty = 'Easy';

// Handle POST recipe submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $errors['general'] = 'Your session expired. Refresh the page and try again.';
    }

    $title = get_post_string('title');
    $description = get_post_string('description');
    $ingredients = get_post_string('ingredients');
    $instructions = get_post_string('instructions');
    $category = get_post_string('category');
    $prep_time = filter_input(INPUT_POST, 'prep_time', FILTER_VALIDATE_INT);
    $cook_time = filter_input(INPUT_POST, 'cook_time', FILTER_VALIDATE_INT);
    $servings = filter_input(INPUT_POST, 'servings', FILTER_VALIDATE_INT);
    $difficulty = get_post_string('difficulty');
    $image_name = 'default_recipe.jpg'; // default placeholder

    // 1. Validation: Title
    if (empty($title)) {
        $errors['title'] = 'Recipe title is required.';
    } elseif (strlen($title) < 3 || strlen($title) > 200) {
        $errors['title'] = 'Recipe title must be between 3 and 200 characters.';
    }

    // 2. Validation: Ingredients
    if (empty($ingredients)) {
        $errors['ingredients'] = 'Please list at least one ingredient.';
    }

    // 3. Validation: Instructions
    if (empty($instructions)) {
        $errors['instructions'] = 'Please provide cooking instructions.';
    }

    if (!in_array($category, recipe_categories(), true)) {
        $errors['category'] = 'Choose a valid recipe category.';
    }
    if (!is_int($prep_time) || $prep_time < 0 || $prep_time > 1440) {
        $errors['prep_time'] = 'Preparation time must be between 0 and 1440 minutes.';
    }
    if (!is_int($cook_time) || $cook_time < 0 || $cook_time > 1440) {
        $errors['cook_time'] = 'Cooking time must be between 0 and 1440 minutes.';
    }
    if (!is_int($servings) || $servings < 1 || $servings > 100) {
        $errors['servings'] = 'Servings must be between 1 and 100.';
    }
    if (!in_array($difficulty, recipe_difficulties(), true)) {
        $errors['difficulty'] = 'Choose a valid difficulty level.';
    }

    // 4. Handle Recipe Image Upload
    if (empty($errors)) {
        $upload = upload_recipe_image($_FILES['recipe_image'] ?? null);
        if ($upload['error']) {
            $errors['image'] = $upload['error'];
        } elseif ($upload['filename']) {
            $image_name = $upload['filename'];
        }
    }

    // If no validation errors, insert into MySQL database
    if (empty($errors)) {
        $user_id = (int)$_SESSION['user_id'];

        $stmt = $conn->prepare("
            INSERT INTO recipes (title, description, ingredients, instructions, image, category, prep_time, cook_time, servings, difficulty, user_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("ssssssiiisi", $title, $description, $ingredients, $instructions, $image_name, $category, $prep_time, $cook_time, $servings, $difficulty, $user_id);

        if ($stmt->execute()) {
            $new_recipe_id = $stmt->insert_id;
            $stmt->close();
            set_flash_message('success', 'Your recipe has been published successfully!');
            header("Location: recipe.php?id=" . $new_recipe_id);
            exit();
        } else {
            $errors['general'] = 'Failed to save recipe into database. Please try again.';
            $stmt->close();
            if ($image_name !== 'default_recipe.jpg') {
                $uploaded_image_path = __DIR__ . '/images/' . $image_name;
                if (is_file($uploaded_image_path)) {
                    unlink($uploaded_image_path);
                }
            }
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                    <div class="card-header bg-white border-bottom p-4">
                        <div class="d-flex align-items-center">
                            <div class="brand-icon-wrap me-3" style="width: 44px; height: 44px;">
                                <i class="bi bi-journal-plus"></i>
                            </div>
                            <div>
                                <h3 class="fw-bold mb-0 text-dark">Share a New Recipe</h3>
                                <p class="text-muted small mb-0">Contribute your culinary creation to the TasteBook community</p>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-4 p-md-5">
                        <?php if (!empty($errors['general'])): ?>
                            <div class="alert alert-danger py-2 small mb-4">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i> <?php echo htmlspecialchars($errors['general']); ?>
                            </div>
                        <?php endif; ?>

                        <form action="add_recipe.php" method="POST" enctype="multipart/form-data" id="addRecipeForm" novalidate>
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                            <!-- Recipe Title -->
                            <div class="mb-4">
                                <label for="recipeTitle" class="form-label">Recipe Title <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control <?php echo isset($errors['title']) ? 'is-invalid' : ''; ?>" 
                                       id="recipeTitle" 
                                       name="title" 
                                       placeholder="e.g. Crispy Honey Garlic Chicken" 
                                       maxlength="200"
                                       value="<?php echo htmlspecialchars($title); ?>" 
                                       required>
                                <?php if (isset($errors['title'])): ?>
                                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['title']); ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Short Description -->
                            <div class="mb-4">
                                <label for="recipeDescription" class="form-label">Short Description</label>
                                <textarea class="form-control" 
                                          id="recipeDescription" 
                                          name="description" 
                                          rows="2" 
                                          placeholder="Give a brief appetizing summary of the dish..."><?php echo htmlspecialchars($description); ?></textarea>
                                <small class="text-muted">A short 1-2 sentence overview to entice hungry readers.</small>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label for="recipeCategory" class="form-label">Category</label>
                                    <select class="form-select <?php echo isset($errors['category']) ? 'is-invalid' : ''; ?>" id="recipeCategory" name="category" required>
                                        <?php foreach (recipe_categories() as $option): ?>
                                            <option value="<?php echo htmlspecialchars($option); ?>" <?php echo $category === $option ? 'selected' : ''; ?>><?php echo htmlspecialchars($option); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if (isset($errors['category'])): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errors['category']); ?></div><?php endif; ?>
                                </div>
                                <div class="col-md-6">
                                    <label for="recipeDifficulty" class="form-label">Difficulty</label>
                                    <select class="form-select <?php echo isset($errors['difficulty']) ? 'is-invalid' : ''; ?>" id="recipeDifficulty" name="difficulty" required>
                                        <?php foreach (recipe_difficulties() as $option): ?>
                                            <option value="<?php echo htmlspecialchars($option); ?>" <?php echo $difficulty === $option ? 'selected' : ''; ?>><?php echo htmlspecialchars($option); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if (isset($errors['difficulty'])): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errors['difficulty']); ?></div><?php endif; ?>
                                </div>
                                <div class="col-sm-4">
                                    <label for="prepTime" class="form-label">Prep time (minutes)</label>
                                    <input class="form-control <?php echo isset($errors['prep_time']) ? 'is-invalid' : ''; ?>" type="number" id="prepTime" name="prep_time" min="0" max="1440" value="<?php echo htmlspecialchars((string)$prep_time); ?>" required>
                                    <?php if (isset($errors['prep_time'])): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errors['prep_time']); ?></div><?php endif; ?>
                                </div>
                                <div class="col-sm-4">
                                    <label for="cookTime" class="form-label">Cook time (minutes)</label>
                                    <input class="form-control <?php echo isset($errors['cook_time']) ? 'is-invalid' : ''; ?>" type="number" id="cookTime" name="cook_time" min="0" max="1440" value="<?php echo htmlspecialchars((string)$cook_time); ?>" required>
                                    <?php if (isset($errors['cook_time'])): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errors['cook_time']); ?></div><?php endif; ?>
                                </div>
                                <div class="col-sm-4">
                                    <label for="servings" class="form-label">Servings</label>
                                    <input class="form-control <?php echo isset($errors['servings']) ? 'is-invalid' : ''; ?>" type="number" id="servings" name="servings" min="1" max="100" value="<?php echo htmlspecialchars((string)$servings); ?>" required>
                                    <?php if (isset($errors['servings'])): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errors['servings']); ?></div><?php endif; ?>
                                </div>
                            </div>

                            <!-- Ingredients List -->
                            <div class="mb-4">
                                <label for="recipeIngredients" class="form-label">Ingredients <span class="text-danger">*</span></label>
                                <textarea class="form-control font-monospace <?php echo isset($errors['ingredients']) ? 'is-invalid' : ''; ?>" 
                                          id="recipeIngredients" 
                                          name="ingredients" 
                                          rows="6" 
                                          placeholder="Enter each ingredient on a new line. Example:&#10;2 cups all-purpose flour&#10;1 tsp salt&#10;2 tbsp olive oil&#10;200g mozzarella cheese" 
                                          required><?php echo htmlspecialchars($ingredients); ?></textarea>
                                <div class="form-text text-muted">
                                    <i class="bi bi-info-circle me-1"></i> Tip: Write one ingredient per line (e.g. <code>2 cups flour</code>). Our automatic portion scaler will format quantities!
                                </div>
                                <?php if (isset($errors['ingredients'])): ?>
                                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['ingredients']); ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Cooking Instructions -->
                            <div class="mb-4">
                                <label for="recipeInstructions" class="form-label">Step-by-Step Instructions <span class="text-danger">*</span></label>
                                <textarea class="form-control <?php echo isset($errors['instructions']) ? 'is-invalid' : ''; ?>" 
                                          id="recipeInstructions" 
                                          name="instructions" 
                                          rows="7" 
                                          placeholder="Enter each step on a new line. Example:&#10;1. Preheat oven to 200°C (400°F).&#10;2. Mix dry ingredients in a large bowl.&#10;3. Knead the dough for 10 minutes until elastic." 
                                          required><?php echo htmlspecialchars($instructions); ?></textarea>
                                <div class="form-text text-muted">
                                    <i class="bi bi-info-circle me-1"></i> Write each step clearly on a new line.
                                </div>
                                <?php if (isset($errors['instructions'])): ?>
                                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['instructions']); ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Recipe Image Upload with Live JS Preview -->
                            <div class="mb-4">
                                <label class="form-label">Recipe Image (Optional)</label>
                                <div class="image-upload-zone" onclick="document.getElementById('recipeImageInput').click();">
                                    <i class="bi bi-cloud-arrow-up fs-1 text-primary d-block mb-2"></i>
                                    <span class="fw-semibold text-dark d-block">Click to upload recipe photo</span>
                                    <small class="text-muted">Supports JPG, PNG, WEBP (Max 5MB)</small>
                                    <input type="file" 
                                           id="recipeImageInput" 
                                           name="recipe_image" 
                                           accept="image/jpeg,image/png,image/webp" 
                                           class="d-none">
                                </div>
                                <?php if (isset($errors['image'])): ?>
                                    <div class="text-danger small mt-1"><?php echo htmlspecialchars($errors['image']); ?></div>
                                <?php endif; ?>

                                <!-- Live Image Preview Area -->
                                <div id="imagePreviewContainer" class="image-preview-container shadow-sm border mt-3">
                                    <img id="imagePreview" src="#" alt="Recipe Image Preview">
                                </div>
                            </div>

                            <!-- Submit & Cancel Buttons -->
                            <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                                <a href="dashboard.php" class="btn btn-outline-secondary rounded-pill px-4">
                                    Cancel
                                </a>
                                <button type="submit" class="btn btn-primary rounded-pill px-5" id="btnPublishRecipe">
                                    <i class="bi bi-check2-circle me-1"></i> Publish Recipe
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
