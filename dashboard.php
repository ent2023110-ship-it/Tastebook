<?php
/**
 * TasteBook – Digital Recipe Book
 * User Dashboard (dashboard.php)
 * 
 * ICT 2209 - Web Technologies Mini Project
 * Rajarata University of Sri Lanka
 */

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

// Protect dashboard: Redirect if not logged in
require_login();

$user_id = (int)$_SESSION['user_id'];
$user = get_logged_in_user($conn);
if (!$user) {
    $_SESSION = [];
    session_destroy();
    header('Location: auth/login.php');
    exit();
}

// Handle Recipe Deletion Request (with CSRF/Ownership protection)
if (isset($_POST['action']) && $_POST['action'] === 'delete_recipe') {
    $delete_id = filter_input(INPUT_POST, 'recipe_id', FILTER_VALIDATE_INT) ?: 0;
    
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        set_flash_message('error', 'Your session expired. Refresh the page and try again.');
        header("Location: dashboard.php");
        exit();
    }

    if ($delete_id > 0) {
        // Delete only if the recipe belongs to the logged-in user
        $stmt_del = $conn->prepare("DELETE FROM recipes WHERE id = ? AND user_id = ?");
        $stmt_del->bind_param("ii", $delete_id, $user_id);
        
        if ($stmt_del->execute() && $stmt_del->affected_rows > 0) {
            set_flash_message('success', 'Recipe deleted successfully.');
        } else {
            set_flash_message('error', 'Could not delete recipe. Either it does not exist or you do not have permission.');
        }
        $stmt_del->close();
    }
    if ($delete_id <= 0) {
        set_flash_message('error', 'Choose a valid recipe to delete.');
    }
    header("Location: dashboard.php");
    exit();
}

// Fetch all recipes created by this specific user
$stmt_recipes = $conn->prepare("SELECT * FROM recipes WHERE user_id = ? ORDER BY created_at DESC");
$stmt_recipes->bind_param("i", $user_id);
$stmt_recipes->execute();
$user_recipes_result = $stmt_recipes->get_result();

$user_recipes = [];
while ($r = $user_recipes_result->fetch_assoc()) {
    $user_recipes[] = $r;
}
$stmt_recipes->close();

$recipe_count = count($user_recipes);
$favorite_count_stmt = $conn->prepare('SELECT COUNT(*) AS total FROM favorites WHERE user_id = ?');
$favorite_count_stmt->bind_param('i', $user_id);
$favorite_count_stmt->execute();
$favorite_count = (int)$favorite_count_stmt->get_result()->fetch_assoc()['total'];
$favorite_count_stmt->close();

$page_title = 'User Dashboard';
require_once __DIR__ . '/includes/header.php';
?>

<div class="py-5">
    <div class="container">
        <!-- User Welcome Banner -->
        <div class="card border-0 rounded-4 shadow-sm mb-4 overflow-hidden bg-white">
            <div class="card-body p-4 p-md-5">
                <div class="row align-items-center g-4">
                    <div class="col-md-8">
                        <div class="d-flex align-items-center">
                            <div class="user-avatar-circle me-3" style="width: 64px; height: 64px; font-size: 1.75rem;">
                                <?php echo strtoupper(substr($user['username'] ?? 'U', 0, 1)); ?>
                            </div>
                            <div>
                                <span class="badge bg-primary-light text-primary px-3 py-1 rounded-pill mb-1">Cook Profile</span>
                                <h2 class="fw-bold text-dark mb-1">Welcome, <?php echo htmlspecialchars($user['username']); ?>!</h2>
                                <p class="text-muted small mb-0">
                                    <i class="bi bi-envelope me-1"></i> <?php echo htmlspecialchars($user['email']); ?> &bull; 
                                    <i class="bi bi-calendar-check ms-2 me-1"></i> Member since <?php echo format_recipe_date($user['created_at']); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4 text-md-end">
                        <div class="d-flex justify-content-md-end gap-2 flex-wrap">
                            <a href="profile.php" class="btn btn-outline-secondary rounded-pill px-3">
                                <i class="bi bi-person-gear me-1"></i> Profile
                            </a>
                            <a href="favorites.php" class="btn btn-outline-danger rounded-pill px-3">
                                <i class="bi bi-heart me-1"></i> Favorites
                            </a>
                            <a href="my_recipes.php" class="btn btn-outline-primary rounded-pill px-3">
                                <i class="bi bi-journal-text me-1"></i> My Recipes
                            </a>
                            <a href="add_recipe.php" class="btn btn-primary rounded-pill px-4">
                                <i class="bi bi-plus-circle me-1"></i> Add Recipe
                            </a>
                            <form action="auth/logout.php" method="post">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                                <button type="submit" class="btn btn-outline-danger rounded-pill px-3">
                                    <i class="bi bi-box-arrow-right me-1"></i> Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dashboard Statistics Grid -->
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="stat-card d-flex align-items-center">
                    <div class="stat-icon-wrap bg-primary bg-opacity-10 text-primary me-3">
                        <i class="bi bi-journal-check"></i>
                    </div>
                    <div>
                        <span class="text-muted small d-block">Published Recipes</span>
                        <h3 class="fw-bold text-dark mb-0"><?php echo $recipe_count; ?></h3>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="stat-card d-flex align-items-center">
                    <div class="stat-icon-wrap bg-success bg-opacity-10 text-success me-3">
                        <i class="bi bi-heart"></i>
                    </div>
                    <div>
                        <span class="text-muted small d-block">Saved Favorites</span>
                        <h3 class="fw-bold text-dark mb-0"><?php echo $favorite_count; ?></h3>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="stat-card d-flex align-items-center">
                    <div class="stat-icon-wrap bg-warning bg-opacity-10 text-warning me-3">
                        <i class="bi bi-award"></i>
                    </div>
                    <div>
                        <span class="text-muted small d-block">Chef Level</span>
                        <h3 class="fw-bold text-dark mb-0"><?php echo ($recipe_count >= 5) ? 'Master Chef' : 'Home Cook'; ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- User's Recipes List Section -->
        <div class="card border-0 rounded-4 shadow-sm bg-white">
            <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="fw-bold text-dark mb-0">My Recipe Collection</h4>
                    <small class="text-muted">Manage, view, and organize recipes you have shared</small>
                </div>
                <a href="add_recipe.php" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                    <i class="bi bi-plus-lg me-1"></i> New Recipe
                </a>
            </div>

            <div class="card-body p-4">
                <?php if (!empty($user_recipes)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" style="width: 90px;">Dish</th>
                                    <th scope="col">Recipe Title</th>
                                    <th scope="col" class="d-none d-md-table-cell">Created Date</th>
                                    <th scope="col" class="d-none d-lg-table-cell">Cooking Time</th>
                                    <th scope="col" class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($user_recipes as $recipe): ?>
                                    <tr>
                                        <td>
                                            <img src="<?php echo htmlspecialchars(get_recipe_image_url($recipe['image'], $recipe['title'])); ?>"
                                                 alt="<?php echo htmlspecialchars($recipe['title']); ?>" 
                                                 class="rounded-3" 
                                                 style="width: 65px; height: 50px; object-fit: cover;">
                                        </td>
                                        <td>
                                            <a href="recipe.php?id=<?php echo $recipe['id']; ?>" class="fw-bold text-dark text-decoration-none">
                                                <?php echo htmlspecialchars($recipe['title']); ?>
                                            </a>
                                            <div class="text-muted small text-truncate d-block" style="max-width: 350px;">
                                                <?php echo htmlspecialchars($recipe['description'] ?? ''); ?>
                                            </div>
                                        </td>
                                        <td class="d-none d-md-table-cell text-muted small">
                                            <?php echo format_recipe_date($recipe['created_at']); ?>
                                        </td>
                                        <td class="d-none d-lg-table-cell">
                                            <span class="badge bg-light text-dark border">
                                                <?php echo (int)$recipe['prep_time'] + (int)$recipe['cook_time']; ?> min
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group btn-group-sm">
                                                <a href="recipe.php?id=<?php echo $recipe['id']; ?>" class="btn btn-outline-primary" title="View Recipe">
                                                    <i class="bi bi-eye"></i> View
                                                </a>
                                                <a href="edit_recipe.php?id=<?php echo (int)$recipe['id']; ?>" class="btn btn-outline-secondary" title="Edit Recipe">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-outline-danger" 
                                                        title="Delete Recipe"
                                                        onclick="if(confirm('Are you sure you want to delete this recipe? This action cannot be undone.')) { document.getElementById('deleteForm_<?php echo $recipe['id']; ?>').submit(); }">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>

                                            <!-- Hidden Deletion Form -->
                                            <form id="deleteForm_<?php echo $recipe['id']; ?>" action="dashboard.php" method="POST" style="display: none;">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                                                <input type="hidden" name="action" value="delete_recipe">
                                                <input type="hidden" name="recipe_id" value="<?php echo $recipe['id']; ?>">
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <div class="stat-icon-wrap bg-light text-muted mx-auto mb-3" style="width: 80px; height: 80px; font-size: 2.5rem;">
                            <i class="bi bi-journal-plus"></i>
                        </div>
                        <h5 class="fw-bold text-dark">You haven't posted any recipes yet!</h5>
                        <p class="text-muted mb-4">Start by adding your very first delicious kitchen creation.</p>
                        <a href="add_recipe.php" class="btn btn-primary rounded-pill px-4">
                            <i class="bi bi-plus-circle me-1"></i> Add Your First Recipe
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
