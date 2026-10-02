<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_login();

$user_id = (int)$_SESSION['user_id'];
$recipe_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$recipe_id || $recipe_id < 1) {
    set_flash_message('error', 'Choose a valid recipe to edit.');
    header('Location: my_recipes.php');
    exit();
}

$stmt = $conn->prepare('SELECT * FROM recipes WHERE id = ? AND user_id = ? LIMIT 1');
$stmt->bind_param('ii', $recipe_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$recipe = $result->fetch_assoc();
$stmt->close();

if (!$recipe) {
    http_response_code(404);
    $page_title = 'Recipe Not Found';
    require_once __DIR__ . '/includes/header.php';
    ?>
    <div class="container py-5 text-center">
        <h1 class="heading-serif">Recipe not found</h1>
        <p class="text-muted">This recipe may have been removed or you may not have permission to edit it.</p>
        <a href="my_recipes.php" class="btn btn-primary rounded-pill">Back to My Recipes</a>
    </div>
    <?php
    require_once __DIR__ . '/includes/footer.php';
    exit();
}

$page_title = 'Edit Recipe';
$errors = [];
$values = $recipe;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values['title'] = get_post_string('title');
    $values['description'] = get_post_string('description');
    $values['ingredients'] = get_post_string('ingredients');
    $values['instructions'] = get_post_string('instructions');
    $values['category'] = get_post_string('category');
    $values['difficulty'] = get_post_string('difficulty');
    $values['prep_time'] = filter_input(INPUT_POST, 'prep_time', FILTER_VALIDATE_INT);
    $values['cook_time'] = filter_input(INPUT_POST, 'cook_time', FILTER_VALIDATE_INT);
    $values['servings'] = filter_input(INPUT_POST, 'servings', FILTER_VALIDATE_INT);

    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $errors['general'] = 'Your session expired. Refresh the page and try again.';
    }
    if (strlen($values['title']) < 3 || strlen($values['title']) > 200) {
        $errors['title'] = 'Enter a recipe title between 3 and 200 characters.';
    }
    if ($values['ingredients'] === '') {
        $errors['ingredients'] = 'Please list at least one ingredient.';
    }
    if ($values['instructions'] === '') {
        $errors['instructions'] = 'Please provide cooking instructions.';
    }
    if (!in_array($values['category'], recipe_categories(), true)) {
        $errors['category'] = 'Choose a valid recipe category.';
    }
    if (!in_array($values['difficulty'], recipe_difficulties(), true)) {
        $errors['difficulty'] = 'Choose a valid difficulty level.';
    }
    if (!is_int($values['prep_time']) || $values['prep_time'] < 0 || $values['prep_time'] > 1440) {
        $errors['prep_time'] = 'Preparation time must be between 0 and 1440 minutes.';
    }
    if (!is_int($values['cook_time']) || $values['cook_time'] < 0 || $values['cook_time'] > 1440) {
        $errors['cook_time'] = 'Cooking time must be between 0 and 1440 minutes.';
    }
    if (!is_int($values['servings']) || $values['servings'] < 1 || $values['servings'] > 100) {
        $errors['servings'] = 'Servings must be between 1 and 100.';
    }

    $new_image = null;
    if (!$errors) {
        $upload = upload_recipe_image($_FILES['recipe_image'] ?? null);
        if ($upload['error']) {
            $errors['image'] = $upload['error'];
        } else {
            $new_image = $upload['filename'];
        }
    }

    if (!$errors) {
        $image_name = $new_image ?: $recipe['image'];
        $update = $conn->prepare(
            'UPDATE recipes SET title = ?, description = ?, ingredients = ?, instructions = ?, image = ?, category = ?, prep_time = ?, cook_time = ?, servings = ?, difficulty = ? WHERE id = ? AND user_id = ?'
        );
        $update->bind_param(
            'ssssssiiisii',
            $values['title'],
            $values['description'],
            $values['ingredients'],
            $values['instructions'],
            $image_name,
            $values['category'],
            $values['prep_time'],
            $values['cook_time'],
            $values['servings'],
            $values['difficulty'],
            $recipe_id,
            $user_id
        );

        if ($update->execute() && $update->affected_rows >= 0) {
            $update->close();
            if ($new_image && preg_match('/^recipe_[a-f0-9]+\\.(jpg|png|webp)$/', $recipe['image'])) {
                $old_image_path = __DIR__ . '/images/' . $recipe['image'];
                if (is_file($old_image_path)) {
                    unlink($old_image_path);
                }
            }
            set_flash_message('success', 'Your recipe has been updated.');
            header('Location: recipe.php?id=' . $recipe_id);
            exit();
        }

        $update->close();
        if ($new_image) {
            $new_image_path = __DIR__ . '/images/' . $new_image;
            if (is_file($new_image_path)) {
                unlink($new_image_path);
            }
        }
        $errors['general'] = 'The recipe could not be updated. Please try again.';
    }
}

require_once __DIR__ . '/includes/header.php';
?>
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="card border-0 rounded-4 shadow-sm">
                    <div class="card-body p-4 p-md-5">
                        <h1 class="heading-serif fw-bold mb-1">Edit your recipe</h1>
                        <p class="text-muted mb-4">Update the recipe details and save your changes.</p>

                        <?php if ($errors): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php foreach ($errors as $error): ?><div><?php echo htmlspecialchars($error); ?></div><?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <form method="post" enctype="multipart/form-data" id="addRecipeForm" novalidate>
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                            <div class="mb-3">
                                <label for="recipeTitle" class="form-label">Recipe title</label>
                                <input class="form-control" id="recipeTitle" name="title" maxlength="200" value="<?php echo htmlspecialchars($values['title']); ?>" required>
                                <?php if (isset($errors['title'])): ?><div class="text-danger small"><?php echo htmlspecialchars($errors['title']); ?></div><?php endif; ?>
                            </div>
                            <div class="mb-3">
                                <label for="recipeDescription" class="form-label">Description</label>
                                <textarea class="form-control" id="recipeDescription" name="description" rows="2"><?php echo htmlspecialchars($values['description'] ?? ''); ?></textarea>
                            </div>
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label for="recipeCategory" class="form-label">Category</label>
                                    <select class="form-select" id="recipeCategory" name="category" required>
                                        <?php foreach (recipe_categories() as $option): ?>
                                            <option value="<?php echo htmlspecialchars($option); ?>" <?php echo $values['category'] === $option ? 'selected' : ''; ?>><?php echo htmlspecialchars($option); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="recipeDifficulty" class="form-label">Difficulty</label>
                                    <select class="form-select" id="recipeDifficulty" name="difficulty" required>
                                        <?php foreach (recipe_difficulties() as $option): ?>
                                            <option value="<?php echo htmlspecialchars($option); ?>" <?php echo $values['difficulty'] === $option ? 'selected' : ''; ?>><?php echo htmlspecialchars($option); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-sm-4">
                                    <label for="prepTime" class="form-label">Prep time (minutes)</label>
                                    <input class="form-control" type="number" id="prepTime" name="prep_time" min="0" max="1440" value="<?php echo htmlspecialchars((string)$values['prep_time']); ?>" required>
                                </div>
                                <div class="col-sm-4">
                                    <label for="cookTime" class="form-label">Cook time (minutes)</label>
                                    <input class="form-control" type="number" id="cookTime" name="cook_time" min="0" max="1440" value="<?php echo htmlspecialchars((string)$values['cook_time']); ?>" required>
                                </div>
                                <div class="col-sm-4">
                                    <label for="servings" class="form-label">Servings</label>
                                    <input class="form-control" type="number" id="servings" name="servings" min="1" max="100" value="<?php echo htmlspecialchars((string)$values['servings']); ?>" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="recipeIngredients" class="form-label">Ingredients (one per line)</label>
                                <textarea class="form-control font-monospace" id="recipeIngredients" name="ingredients" rows="7" required><?php echo htmlspecialchars($values['ingredients']); ?></textarea>
                                <?php if (isset($errors['ingredients'])): ?><div class="text-danger small"><?php echo htmlspecialchars($errors['ingredients']); ?></div><?php endif; ?>
                            </div>
                            <div class="mb-3">
                                <label for="recipeInstructions" class="form-label">Instructions (one step per line)</label>
                                <textarea class="form-control" id="recipeInstructions" name="instructions" rows="7" required><?php echo htmlspecialchars($values['instructions']); ?></textarea>
                                <?php if (isset($errors['instructions'])): ?><div class="text-danger small"><?php echo htmlspecialchars($errors['instructions']); ?></div><?php endif; ?>
                            </div>
                            <div class="mb-4">
                                <label for="recipeImageInput" class="form-label">Replace image (optional)</label>
                                <div class="mb-2">
                                    <img src="<?php echo htmlspecialchars(get_recipe_image_url($recipe['image'], $recipe['title'])); ?>" alt="Current image for <?php echo htmlspecialchars($recipe['title']); ?>" class="rounded-3" style="max-width: 220px; max-height: 150px; object-fit: cover;">
                                </div>
                                <input class="form-control" type="file" id="recipeImageInput" name="recipe_image" accept="image/jpeg,image/png,image/webp">
                                <div class="form-text">JPG, PNG, or WEBP; maximum 5 MB. Leave blank to keep the current image.</div>
                                <?php if (isset($errors['image'])): ?><div class="text-danger small"><?php echo htmlspecialchars($errors['image']); ?></div><?php endif; ?>
                                <div id="imagePreviewContainer" class="image-preview-container shadow-sm border mt-3">
                                    <img id="imagePreview" src="#" alt="New recipe image preview">
                                </div>
                            </div>
                            <div class="d-flex justify-content-between gap-2">
                                <a href="my_recipes.php" class="btn btn-outline-secondary rounded-pill px-4">Cancel</a>
                                <button type="submit" class="btn btn-primary rounded-pill px-4">Save changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
