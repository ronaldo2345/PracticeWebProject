<?php
require __DIR__.'/../config/config.php';
require_once __DIR__.'/../includes/functions.php'; 

// Get recipes based on search filter (optional)
$type = isset($_GET['type']) ? $_GET['type'] : '';
$search_query = $pdo->prepare("
    SELECT r.*, u.username 
    FROM recipes r 
    JOIN users u ON r.uid = u.uid 
    WHERE r.type LIKE :type
    ORDER BY r.created_at DESC
");
$search_query->execute(['type' => "%$type%"]);
$recipes = $search_query->fetchAll();

$title = "Search Recipes";
require __DIR__.'/../templates/header.php';
?>

<div class="container">
    <h2>Search Results</h2>
    <div class="row">
        <?php if ($recipes): ?>
            <?php foreach ($recipes as $recipe): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-img-top" style="background: url('<?= htmlspecialchars($recipe['image'] ?: 'assets/images/placeholder.jpg') ?>') center/cover; height: 180px;"></div>
                        <div class="card-body">
                            <h5><?= htmlspecialchars($recipe['name']) ?></h5>
                            <span class="badge bg-primary"><?= htmlspecialchars($recipe['type']) ?></span>
                            <p><?= htmlspecialchars(substr($recipe['description'], 0, 100)) ?>...</p>
                        </div>
                        <div class="card-footer">
                            <a href="recipe.php?id=<?= $recipe['rid'] ?>" class="btn btn-sm btn-outline-primary">View Recipe</a>
                            <small>By <?= htmlspecialchars($recipe['username']) ?></small>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No recipes found.</p>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__.'/../templates/footer.php'; ?>
