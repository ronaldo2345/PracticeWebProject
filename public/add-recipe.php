<?php
require __DIR__.'/../config/config.php';
require __DIR__.'/../includes/functions.php';

if (!is_logged_in()) {
    redirect('login.php');
}

$errors = [];
$recipe_types = ['French', 'Italian', 'Chinese', 'Indian', 'Mexican', 'others'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && validate_csrf_token($_POST['csrf_token'])) {
    $name = sanitize_input($_POST['name']);
    $description = sanitize_input($_POST['description']);
    $type = sanitize_input($_POST['type']);
    $cookingtime = (int)$_POST['cookingtime'];
    $ingredients = sanitize_input($_POST['ingredients']);
    $instructions = sanitize_input($_POST['instructions']);
    
    // Validation
    if (empty($name)) $errors[] = 'Recipe name is required';
    if (!in_array($type, $recipe_types)) $errors[] = 'Invalid recipe type';
    if ($cookingtime <= 0) $errors[] = 'Cooking time must be positive';
    
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO recipes 
                (name, description, type, cookingtime, ingredients, instructions, uid)
                VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $name, $description, $type, $cookingtime, 
                $ingredients, $instructions, $_SESSION['user_id']
            ]);
            redirect('dashboard.php?success=recipe_added');
        } catch (PDOException $e) {
            $errors[] = 'Failed to add recipe: ' . $e->getMessage();
        }
    }
}

$title = "Add New Recipe";
require __DIR__.'/../templates/header.php';
?>

<div class="container">
    <h1>Add New Recipe</h1>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <p><?= $error ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
        
        <div class="form-group">
            <label>Recipe Name*</label>
            <input type="text" name="name" required class="form-control">
        </div>
        
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" class="form-control" rows="3"></textarea>
        </div>
        
        <div class="form-group">
            <label>Cuisine Type*</label>
            <select name="type" required class="form-control">
                <?php foreach ($recipe_types as $t): ?>
                    <option value="<?= $t ?>"><?= $t ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label>Cooking Time (minutes)*</label>
            <input type="number" name="cookingtime" min="1" required class="form-control">
        </div>
        
        <div class="form-group">
            <label>Ingredients* (one per line)</label>
            <textarea name="ingredients" required class="form-control" rows="5"></textarea>
        </div>
        
        <div class="form-group">
            <label>Instructions*</label>
            <textarea name="instructions" required class="form-control" rows="10"></textarea>
        </div>
        
        <button type="submit" class="btn btn-primary">Save Recipe</button>
    </form>
</div>

<?php require __DIR__.'/../templates/footer.php'; ?>