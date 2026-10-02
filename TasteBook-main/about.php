<?php
$page_title = 'About TasteBook';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/header.php';
?>
<section class="py-5 bg-white border-bottom">
    <div class="container py-3">
        <div class="row align-items-center g-5">
            <div class="col-lg-7">
                <span class="text-primary fw-semibold text-uppercase small">About the platform</span>
                <h1 class="display-5 heading-serif fw-bold">A home for recipes worth sharing</h1>
                <p class="lead text-muted">TasteBook brings everyday cooks together to discover, save, and share recipes in one practical digital cookbook.</p>
                <p class="text-muted">TasteBook is designed to make everyday cooking more inspiring, with practical tools to organize recipes and share new ideas.</p>
                <a href="recipes.php" class="btn btn-primary rounded-pill px-4">Explore recipes</a>
            </div>
            <div class="col-lg-5">
                <img src="<?php echo htmlspecialchars(get_recipe_image_url('fried_rice.jpg', 'Fresh cooking ingredients')); ?>" alt="Fresh ingredients ready for cooking" class="img-fluid rounded-4 shadow">
            </div>
        </div>
    </div>
</section>
<section class="py-5">
    <div class="container">
        <div class="text-center mb-4">
            <span class="text-primary fw-semibold text-uppercase small">Features</span>
            <h2 class="heading-serif fw-bold">Made for real home cooking</h2>
        </div>
        <div class="row g-4">
            <?php
            $features = [
                ['bi-search', 'Discover', 'Search and filter recipes by title, ingredients, category, time, and difficulty.'],
                ['bi-journal-plus', 'Share', 'Publish your own recipes with preparation details and a photo.'],
                ['bi-heart', 'Save', 'Keep your favorites in a personal collection tied to your TasteBook account.'],
                ['bi-sliders', 'Cook your way', 'Scale servings, check off ingredients, and print a clear recipe view.'],
            ];
            foreach ($features as [$icon, $title, $description]):
            ?>
                <div class="col-sm-6 col-lg-3">
                    <div class="stat-card h-100">
                        <i class="bi <?php echo htmlspecialchars($icon); ?> fs-2 text-primary"></i>
                        <h3 class="h5 fw-bold mt-3"><?php echo htmlspecialchars($title); ?></h3>
                        <p class="text-muted mb-0"><?php echo htmlspecialchars($description); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
