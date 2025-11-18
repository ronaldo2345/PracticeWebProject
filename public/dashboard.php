<?php
require __DIR__.'/../config/config.php';
require __DIR__.'/../includes/functions.php';

if (!is_logged_in()) {
    redirect('login.php');
}

// Get user's recipes
$stmt = $pdo->prepare("SELECT * FROM recipes WHERE uid = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$recipes = $stmt->fetchAll();

$title = "My Dashboard";
require __DIR__.'/../templates/header.php';
?>

<div class="container">
    <h1>Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!</h1>
    
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">
            <?php 
            switch($_GET['success']) {
                case 'recipe_added': echo "Recipe added successfully!"; break;
                case 'recipe_updated': echo "Recipe updated successfully!"; break;
            }
            ?>
        </div>
    <?php endif; ?>
    
    <a href="add-recipe.php" class="btn btn-primary mb-3">+ Add New Recipe</a>
    
    <h2>My Recipes</h2>
    <div class="recipe-grid">
        <?php if (empty($recipes)): ?>
            <p>You haven't added any recipes yet.</p>
        <?php else: ?>
            <?php foreach ($recipes as $recipe): ?>
                <div class="recipe-card">
                    <h3><?= htmlspecialchars($recipe['name']) ?></h3>
                    <p><strong>Type:</strong> <?= $recipe['type'] ?></p>
                    <p><?= nl2br(htmlspecialchars(substr($recipe['description'], 0, 100))) ?>...</p>
                    <div class="recipe-actions">
                        <a href="recipe.php?id=<?= $recipe['rid'] ?>" class="btn btn-sm btn-info">View</a>
                        <a href="edit-recipe.php?id=<?= $recipe['rid'] ?>" class="btn btn-sm btn-warning">Edit</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__.'/../templates/footer.php'; ?>