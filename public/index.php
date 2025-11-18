<?php
require __DIR__.'/../config/config.php';
require __DIR__.'/../includes/functions.php';

// Get featured recipes (most recent 6)
$stmt = $pdo->query("
    SELECT r.*, u.username 
    FROM recipes r 
    JOIN users u ON r.uid = u.uid 
    ORDER BY r.created_at DESC 
    LIMIT 6
");
$featured_recipes = $stmt->fetchAll();

// Get recipe counts by category
$categories = $pdo->query("
    SELECT type, COUNT(*) as count 
    FROM recipes 
    GROUP BY type
")->fetchAll();

$title = "Discover Delicious Recipes";
require __DIR__.'/../templates/header.php';
?>

<div class="container">
    <!-- Hero Section -->
    <section class="hero bg-light rounded p-5 mb-5 text-center">
        <h1 class="display-4"><i class="fas fa-utensils text-primary"></i> Welcome to Virtual Kitchen</h1>
        <p class="lead">Share and discover amazing recipes from around the world</p>
        <div class="mt-4">
            <a href="search.php" class="btn btn-primary btn-lg me-2">
                <i class="fas fa-search"></i> Browse Recipes
            </a>
            <?php if (!is_logged_in()): ?>
                <a href="register.php" class="btn btn-outline-primary btn-lg">
                    <i class="fas fa-user-plus"></i> Join Our Community
                </a>
            <?php endif; ?>
        </div>
    </section>

    <!-- Featured Recipes -->
    <section class="mb-5">
        <h2 class="mb-4"><i class="fas fa-star text-warning"></i> Featured Recipes</h2>
        <div class="row">
            <?php foreach ($featured_recipes as $recipe): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-img-top bg-secondary" style="height: 180px; background: url('<?= !empty($recipe['image']) ? htmlspecialchars($recipe['image']) : 'assets/images/placeholder.jpg' ?>') center/cover;"></div>
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($recipe['name']) ?></h5>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="badge bg-primary"><?= htmlspecialchars($recipe['type']) ?></span>
                                <span class="text-muted"><?= format_cooking_time($recipe['cookingtime']) ?></span>
                            </div>
                            <p class="card-text"><?= htmlspecialchars(substr($recipe['description'], 0, 100)) ?>...</p>
                        </div>
                        <div class="card-footer bg-white">
                            <a href="recipe.php?id=<?= $recipe['rid'] ?>" class="btn btn-sm btn-outline-primary">
                                View Recipe
                            </a>
                            <small class="text-muted float-end">
                                By <?= htmlspecialchars($recipe['username']) ?>
                            </small>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Categories -->
    <section class="mb-5">
        <h2 class="mb-4"><i class="fas fa-tags text-success"></i> Recipe Categories</h2>
        <div class="row">
            <?php foreach ($categories as $category): ?>
                <div class="col-sm-6 col-md-4 col-lg-3 mb-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($category['type']) ?></h5>
                            <p class="card-text"><?= $category['count'] ?> recipes</p>
                            <a href="search.php?type=<?= urlencode($category['type']) ?>" class="btn btn-sm btn-outline-success">
                                Explore
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</div>

<?php require __DIR__.'/../templates/footer.php'; ?>