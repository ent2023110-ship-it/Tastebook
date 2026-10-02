<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_login();

$user_id = (int)$_SESSION['user_id'];
$stmt = $conn->prepare(
    'SELECT r.*, u.username AS author_name
     FROM favorites f
     JOIN recipes r ON r.id = f.recipe_id
     JOIN users u ON u.id = r.user_id
     WHERE f.user_id = ?
     ORDER BY f.created_at DESC'
);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$recipes = [];
while ($recipe = $result->fetch_assoc()) {
    $recipes[] = $recipe;
}
$stmt->close();

$page_title = 'Favorite Recipes';
require_once __DIR__ . '/includes/header.php';
?>
<section class="py-5">
    <div class="container">
        <div class="mb-4">
            <span class="text-primary fw-semibold text-uppercase small">Saved for later</span>
            <h1 class="heading-serif fw-bold mb-1">Your Favorites</h1>
            <p class="text-muted mb-0">A personal shortlist of recipes you want to cook.</p>
        </div>
        <?php if ($recipes): ?>
            <div class="row g-4">
                <?php foreach ($recipes as $recipe): ?>
                    <div class="col-lg-4 col-md-6">
                        <article class="recipe-card h-100">
                            <div class="recipe-card-img-wrap">
                                <img src="<?php echo htmlspecialchars(get_recipe_image_url($recipe['image'], $recipe['title'])); ?>" alt="<?php echo htmlspecialchars($recipe['title']); ?>" class="recipe-card-img" loading="lazy">
                                <?php render_favorite_button((int)$recipe['id'], true, 'favorites.php'); ?>
                                <span class="recipe-badge-time"><i class="bi bi-clock me-1"></i><?php echo (int)$recipe['prep_time'] + (int)$recipe['cook_time']; ?> min</span>
                            </div>
                            <div class="recipe-card-body">
                                <span class="text-primary small fw-semibold"><?php echo htmlspecialchars($recipe['category']); ?></span>
                                <h2 class="h5 recipe-card-title"><a href="recipe.php?id=<?php echo (int)$recipe['id']; ?>"><?php echo htmlspecialchars($recipe['title']); ?></a></h2>
                                <p class="recipe-card-desc"><?php echo htmlspecialchars($recipe['description'] ?? ''); ?></p>
                                <div class="recipe-card-footer">
                                    <small class="text-muted">By <?php echo htmlspecialchars($recipe['author_name']); ?></small>
                                    <a href="recipe.php?id=<?php echo (int)$recipe['id']; ?>" class="btn btn-sm btn-outline-primary rounded-pill">View recipe</a>
                                </div>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center bg-white rounded-4 p-5 shadow-sm">
                <i class="bi bi-heart display-4 text-danger"></i>
                <h2 class="h4 fw-bold mt-3">No favorites saved yet</h2>
                <p class="text-muted">Explore the collection and save recipes you would like to try.</p>
                <a href="recipes.php" class="btn btn-primary rounded-pill px-4">Explore recipes</a>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
