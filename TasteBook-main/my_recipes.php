<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_login();

$user_id = (int)$_SESSION['user_id'];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_recipe') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        set_flash_message('error', 'Your session expired. Refresh the page and try again.');
    } else {
        $recipe_id = filter_input(INPUT_POST, 'recipe_id', FILTER_VALIDATE_INT);
        if ($recipe_id && $recipe_id > 0) {
            $delete = $conn->prepare('DELETE FROM recipes WHERE id = ? AND user_id = ?');
            $delete->bind_param('ii', $recipe_id, $user_id);
            $deleted = $delete->execute() && $delete->affected_rows === 1;
            $delete->close();
            set_flash_message($deleted ? 'success' : 'error', $deleted ? 'Recipe deleted successfully.' : 'The recipe could not be deleted.');
        } else {
            set_flash_message('error', 'Choose a valid recipe to delete.');
        }
    }
    header('Location: my_recipes.php');
    exit();
}

$stmt = $conn->prepare('SELECT * FROM recipes WHERE user_id = ? ORDER BY created_at DESC');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$recipes = [];
while ($recipe = $result->fetch_assoc()) {
    $recipes[] = $recipe;
}
$stmt->close();

$page_title = 'My Recipes';
require_once __DIR__ . '/includes/header.php';
?>
<section class="py-5">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
            <div>
                <span class="text-primary fw-semibold text-uppercase small">Your collection</span>
                <h1 class="heading-serif fw-bold mb-1">My Recipes</h1>
                <p class="text-muted mb-0">Edit and organize the recipes you have shared.</p>
            </div>
            <a href="add_recipe.php" class="btn btn-primary rounded-pill px-4"><i class="bi bi-plus-circle me-1"></i> Add a recipe</a>
        </div>

        <?php if ($recipes): ?>
            <div class="row g-4">
                <?php foreach ($recipes as $recipe): ?>
                    <div class="col-md-6 col-xl-4">
                        <article class="recipe-card h-100">
                            <div class="recipe-card-img-wrap">
                                <img src="<?php echo htmlspecialchars(get_recipe_image_url($recipe['image'], $recipe['title'])); ?>" alt="<?php echo htmlspecialchars($recipe['title']); ?>" class="recipe-card-img" loading="lazy">
                                <span class="badge bg-white text-dark position-absolute top-0 start-0 m-3"><?php echo htmlspecialchars($recipe['category']); ?></span>
                            </div>
                            <div class="recipe-card-body">
                                <h2 class="h5 recipe-card-title"><a href="recipe.php?id=<?php echo (int)$recipe['id']; ?>"><?php echo htmlspecialchars($recipe['title']); ?></a></h2>
                                <p class="recipe-card-desc"><?php echo htmlspecialchars($recipe['description'] ?? ''); ?></p>
                                <div class="d-flex gap-2 mt-auto flex-wrap">
                                    <a href="recipe.php?id=<?php echo (int)$recipe['id']; ?>" class="btn btn-outline-primary btn-sm rounded-pill">View</a>
                                    <a href="edit_recipe.php?id=<?php echo (int)$recipe['id']; ?>" class="btn btn-outline-secondary btn-sm rounded-pill">Edit</a>
                                    <form method="post" onsubmit="return confirm('Delete this recipe permanently?');">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                                        <input type="hidden" name="action" value="delete_recipe">
                                        <input type="hidden" name="recipe_id" value="<?php echo (int)$recipe['id']; ?>">
                                        <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill">Delete</button>
                                    </form>
                                </div>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center bg-white rounded-4 p-5 shadow-sm">
                <i class="bi bi-journal-plus display-4 text-primary"></i>
                <h2 class="h4 fw-bold mt-3">Your recipe book is ready</h2>
                <p class="text-muted">Share your first recipe and keep it organized here.</p>
                <a href="add_recipe.php" class="btn btn-primary rounded-pill px-4">Create your first recipe</a>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
