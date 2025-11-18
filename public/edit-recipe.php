<?php
require __DIR__.'/../config/config.php';
require __DIR__.'/../includes/functions.php';

if (!is_logged_in() || !isset($_GET['id'])) {
    redirect('login.php');
}

// Fetch recipe
$stmt = $pdo->prepare("SELECT * FROM recipes WHERE rid = ? AND uid = ?");
$stmt->execute([$_GET['id'], $_SESSION['user_id']]);
$recipe = $stmt->fetch();

if (!$recipe) redirect('dashboard.php');

$errors = [];
$recipe_types = ['French', 'Italian', 'Chinese', 'Indian', 'Mexican', 'others'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && validate_csrf_token($_POST['csrf_token'])) {
    $name = sanitize_input($_POST['name']);
    $description = sanitize_input($_POST['description']);
    $type = sanitize_input($_POST['type']);
    $cookingtime = (int)$_POST['cookingtime'];
    $ingredients = sanitize_input($_POST['ingredients']);
    $instructions = sanitize_input($_POST['instructions']);
    
    // Validation (same as add-recipe.php)
    
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("UPDATE recipes SET 
                name = ?, description = ?, type = ?, cookingtime = ?, 
                ingredients = ?, instructions = ?
                WHERE rid = ? AND uid = ?");
            $stmt->execute([
                $name, $description, $type, $cookingtime,
                $ingredients, $instructions, 
                $_GET['id'], $_SESSION['user_id']
            ]);
            redirect('dashboard.php?success=recipe_updated');
        } catch (PDOException $e) {
            $errors[] = 'Failed to update recipe: ' . $e->getMessage();
        }
    }
}

$title = "Edit Recipe";
require __DIR__.'/../templates/header.php';
?>

<!-- Similar form to add-recipe.php but pre-filled -->
<form method="POST">
    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
    
    <!-- Pre-fill all fields with $recipe data -->
    <input type="text" name="name" value="<?= htmlspecialchars($recipe['name']) ?>" required>
    <!-- Other fields... -->
    
    <button type="submit">Update Recipe</button>
</form>

<?php require __DIR__.'/../templates/footer.php'; ?>