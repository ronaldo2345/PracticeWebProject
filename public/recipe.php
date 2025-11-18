<?php
require __DIR__.'/../config/config.php';
require __DIR__.'/../includes/functions.php';

if (!isset($_GET['id'])) redirect('index.php');

$stmt = $pdo->prepare("SELECT r.*, u.username 
                       FROM recipes r JOIN users u ON r.uid = u.uid 
                       WHERE r.rid = ?");
$stmt->execute([$_GET['id']]);
$recipe = $stmt->fetch();

if (!$recipe) redirect('index.php');

$title = $recipe['name'];
require __DIR__.'/../templates/header.php';
?>

<div class="container">
    <h1><?= htmlspecialchars($recipe['name']) ?></h1>
    <p class="text-muted">By <?= htmlspecialchars($recipe['username']) ?></p>
    
    <div class="recipe-meta">
        <span class="badge bg-primary"><?= $recipe['type'] ?></span>
        <span class="badge bg-secondary"><?= $recipe['cookingtime'] ?> mins</span>
    </div>
    
    <div class="recipe-section">
        <h3>Description</h3>
        <p><?= nl2br(htmlspecialchars($recipe['description'])) ?></p>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <h3>Ingredients</h3>
            <ul>
                <?php foreach (explode("\n", $recipe['ingredients']) as $ing): ?>
                    <?php if (trim($ing)): ?>
                        <li><?= htmlspecialchars($ing) ?></li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="col-md-6">
            <h3>Instructions</h3>
            <ol>
                <?php foreach (explode("\n", $recipe['instructions']) as $step): ?>
                    <?php if (trim($step)): ?>
                        <li><?= htmlspecialchars($step) ?></li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ol>
        </div>
    </div>
    
    <?php if (is_logged_in() && $_SESSION['user_id'] == $recipe['uid']): ?>
        <div class="mt-4">
            <a href="edit-recipe.php?id=<?= $recipe['rid'] ?>" class="btn btn-warning">Edit Recipe</a>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__.'/../templates/footer.php'; ?>