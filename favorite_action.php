<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_login();

$return_to = get_post_string('return_to');
if ($return_to === '') {
    $return_to = 'favorites.php';
}
if (preg_match('/^recipe\.php\?id=([1-9][0-9]*)$/', $return_to, $matches)) {
    $return_url = 'recipe.php?id=' . (int)$matches[1];
} elseif (in_array($return_to, ['index.php', 'recipes.php', 'favorites.php', 'dashboard.php', 'my_recipes.php'], true)) {
    $return_url = $return_to;
} else {
    $return_url = 'favorites.php';
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf_token($_POST['csrf_token'] ?? null)) {
    set_flash_message('error', 'Your session expired. Refresh the page and try again.');
    header('Location: ' . $return_url);
    exit();
}

$recipe_id = filter_input(INPUT_POST, 'recipe_id', FILTER_VALIDATE_INT);
$action = get_post_string('action');
$user_id = (int)$_SESSION['user_id'];

if (!$recipe_id || $recipe_id < 1 || !in_array($action, ['add', 'remove'], true)) {
    set_flash_message('error', 'That favorite action was not valid.');
    header('Location: ' . $return_url);
    exit();
}

$check = $conn->prepare('SELECT id FROM recipes WHERE id = ? LIMIT 1');
$check->bind_param('i', $recipe_id);
$check->execute();
$recipe_exists = $check->get_result()->num_rows === 1;
$check->close();

if (!$recipe_exists) {
    set_flash_message('error', 'That recipe could not be found.');
    header('Location: recipes.php');
    exit();
}

if ($action === 'add') {
    $stmt = $conn->prepare('INSERT IGNORE INTO favorites (user_id, recipe_id) VALUES (?, ?)');
    $stmt->bind_param('ii', $user_id, $recipe_id);
    $success = $stmt->execute();
    $stmt->close();
    set_flash_message($success ? 'success' : 'error', $success ? 'Recipe saved to your favorites.' : 'The recipe could not be saved. Please try again.');
} else {
    $stmt = $conn->prepare('DELETE FROM favorites WHERE user_id = ? AND recipe_id = ?');
    $stmt->bind_param('ii', $user_id, $recipe_id);
    $success = $stmt->execute();
    $stmt->close();
    set_flash_message($success ? 'success' : 'error', $success ? 'Recipe removed from your favorites.' : 'The favorite could not be removed. Please try again.');
}

header('Location: ' . $return_url);
exit();
