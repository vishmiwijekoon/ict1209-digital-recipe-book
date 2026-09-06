<?php
// ============================================================
// Edit Recipe Page (Author-Only Access)
// Project: Diary of Taste - Digital Recipe Book
// ============================================================

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

// 1. Enforce login
require_login('auth/login.php');

$current_user_id = (int)$_SESSION['user_id'];
$recipe_id = (int)($_GET['id'] ?? 0);
$errors = [];

// 2. Fetch the recipe to edit
$stmt = $pdo->prepare("SELECT * FROM recipes WHERE id = ?");
$stmt->execute([$recipe_id]);
$recipe = $stmt->fetch();

if (!$recipe) {
    set_flash('error', 'Recipe not found or has been removed.');
    redirect('dashboard.php');
}

// 3. Strict Author Check: only the author who wrote it can edit it
if ((int)$recipe['user_id'] !== $current_user_id) {
    set_flash('error', 'Permission denied: You can only edit recipes that you created.');
    redirect('dashboard.php');
}

// 4. Handle Form Submission (Update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title        = trim($_POST['title'] ?? '');
    $category     = trim($_POST['category'] ?? 'Dinner');
    $description  = trim($_POST['description'] ?? '');
    $prep_time    = trim($_POST['prep_time'] ?? '15 Min');
    $cook_time    = trim($_POST['cook_time'] ?? '20 Min');
    $servings     = trim($_POST['servings'] ?? '4');
    $difficulty   = trim($_POST['difficulty'] ?? 'Easy');
    $image_url    = trim($_POST['image_url'] ?? '');
    $ingredients  = trim($_POST['ingredients'] ?? '');
    $instructions = trim($_POST['instructions'] ?? '');

    // Validation
    if (empty($title)) {
        $errors['title'] = 'Recipe title is required.';
    }
    if (empty($ingredients)) {
        $errors['ingredients'] = 'Please provide at least one ingredient.';
    }
    if (empty($instructions)) {
        $errors['instructions'] = 'Cooking instructions are required.';
    }

    if (empty($image_url)) {
        $image_url = $recipe['image_url'] ?: 'https://images.unsplash.com/photo-1495521821757-a1efb6729352?q=80&w=800&auto=format&fit=crop';
    }

    if (empty($errors)) {
        try {
            $update_stmt = $pdo->prepare("
                UPDATE recipes SET
                    title = :title,
                    description = :description,
                    ingredients = :ingredients,
                    instructions = :instructions,
                    category = :category,
                    image_url = :image_url,
                    prep_time = :prep_time,
                    cook_time = :cook_time,
                    servings = :servings,
                    difficulty = :difficulty
                WHERE id = :id AND user_id = :user_id
            ");

            $update_stmt->execute([
                ':title'        => $title,
                ':description'  => $description,
                ':ingredients'  => $ingredients,
                ':instructions' => $instructions,
                ':category'     => $category,
                ':image_url'    => $image_url,
                ':prep_time'    => $prep_time,
                ':cook_time'    => $cook_time,
                ':servings'     => $servings,
                ':difficulty'   => $difficulty,
                ':id'           => $recipe_id,
                ':user_id'      => $current_user_id
            ]);

            set_flash('success', 'Recipe "' . htmlspecialchars($title) . '" updated successfully!');
            redirect('recipe-details.php?id=' . $recipe_id);
        } catch (PDOException $e) {
            $errors['general'] = 'Failed to update recipe: ' . $e->getMessage();
        }
    }
} else {
    // Populate form with existing recipe values
    $title        = $recipe['title'];
    $category     = $recipe['category'];
    $description  = $recipe['description'];
    $prep_time    = $recipe['prep_time'];
    $cook_time    = $recipe['cook_time'];
    $servings     = $recipe['servings'];
    $difficulty   = $recipe['difficulty'];
    $image_url    = $recipe['image_url'];
    $ingredients  = $recipe['ingredients'];
    $instructions = $recipe['instructions'];
}

$page_title = 'Edit Recipe - ' . $recipe['title'];
$active_page = 'dashboard';
$base_path = '';

require_once __DIR__ . '/includes/header.php';
?>

<main class="container my-5">

  <!-- BREADCRUMB -->
  <nav class="breadcrumb-trail mb-4" aria-label="breadcrumb">
    <a href="index.php">Home</a>
    <span class="sep">&rsaquo;</span>
    <a href="dashboard.php">Dashboard</a>
    <span class="sep">&rsaquo;</span>
    <span class="current">Edit: <?= htmlspecialchars($recipe['title']) ?></span>
  </nav>

  <div class="card shadow-sm border-0 p-4 p-md-5" style="border-radius:14px; max-width: 900px; margin: 0 auto;">
    <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
      <div>
        <p class="eyebrow mb-1 text-warning">Update Recipe</p>
        <h2 class="mb-0">Edit Recipe</h2>
      </div>
      <a href="dashboard.php" class="btn btn-outline-secondary btn-sm" style="border-radius:8px;">
        <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
      </a>
    </div>

    <?php if (!empty($errors['general'])): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($errors['general']) ?></div>
    <?php endif; ?>

    <form action="edit-recipe.php?id=<?= $recipe_id ?>" method="POST" novalidate>
      <div class="row g-3">

        <!-- Title -->
        <div class="col-md-8">
          <label class="form-label-custom" for="rec-title">Recipe Title *</label>
          <input type="text"
                 name="title"
                 id="rec-title"
                 class="form-control-custom <?= isset($errors['title']) ? 'is-invalid' : '' ?>"
                 value="<?= htmlspecialchars($title ?? '') ?>"
                 required>
          <?php if (isset($errors['title'])): ?>
            <div class="form-feedback error d-block"><?= htmlspecialchars($errors['title']) ?></div>
          <?php endif; ?>
        </div>

        <!-- Category -->
        <div class="col-md-4">
          <label class="form-label-custom" for="rec-category">Category *</label>
          <select name="category" id="rec-category" class="form-control-custom">
            <?php foreach (['Breakfast', 'Lunch', 'Dinner', 'Desserts', 'Drinks'] as $cat): ?>
              <option value="<?= $cat ?>" <?= ($category === $cat) ? 'selected' : '' ?>><?= $cat ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Short Description -->
        <div class="col-12">
          <label class="form-label-custom" for="rec-desc">Short Summary / Excerpt</label>
          <input type="text"
                 name="description"
                 id="rec-desc"
                 class="form-control-custom"
                 placeholder="A brief summary of your recipe"
                 value="<?= htmlspecialchars($description ?? '') ?>">
        </div>

        <!-- Image URL -->
        <div class="col-12">
          <label class="form-label-custom" for="rec-image">Image URL</label>
          <input type="url"
                 name="image_url"
                 id="rec-image"
                 class="form-control-custom"
                 placeholder="https://images.unsplash.com/photo-..."
                 value="<?= htmlspecialchars($image_url ?? '') ?>">
        </div>

        <!-- Prep Time, Cook Time, Servings, Difficulty -->
        <div class="col-md-3">
          <label class="form-label-custom" for="rec-prep">Prep Time</label>
          <input type="text" name="prep_time" id="rec-prep" class="form-control-custom" value="<?= htmlspecialchars($prep_time ?? '15 Min') ?>">
        </div>
        <div class="col-md-3">
          <label class="form-label-custom" for="rec-cook">Cook Time</label>
          <input type="text" name="cook_time" id="rec-cook" class="form-control-custom" value="<?= htmlspecialchars($cook_time ?? '25 Min') ?>">
        </div>
        <div class="col-md-3">
          <label class="form-label-custom" for="rec-servings">Servings</label>
          <input type="text" name="servings" id="rec-servings" class="form-control-custom" value="<?= htmlspecialchars($servings ?? '4') ?>">
        </div>
        <div class="col-md-3">
          <label class="form-label-custom" for="rec-diff">Difficulty</label>
          <select name="difficulty" id="rec-diff" class="form-control-custom">
            <option value="Easy" <?= ($difficulty === 'Easy') ? 'selected' : '' ?>>Easy</option>
            <option value="Medium" <?= ($difficulty === 'Medium') ? 'selected' : '' ?>>Medium</option>
            <option value="Hard" <?= ($difficulty === 'Hard') ? 'selected' : '' ?>>Hard</option>
          </select>
        </div>

        <!-- Ingredients -->
        <div class="col-md-6">
          <label class="form-label-custom" for="rec-ingredients">Ingredients * (One per line)</label>
          <textarea name="ingredients"
                    id="rec-ingredients"
                    rows="8"
                    class="form-control-custom <?= isset($errors['ingredients']) ? 'is-invalid' : '' ?>"
                    required><?= htmlspecialchars($ingredients ?? '') ?></textarea>
          <?php if (isset($errors['ingredients'])): ?>
            <div class="form-feedback error d-block"><?= htmlspecialchars($errors['ingredients']) ?></div>
          <?php endif; ?>
        </div>

        <!-- Instructions -->
        <div class="col-md-6">
          <label class="form-label-custom" for="rec-instructions">Instructions * (Step-by-step)</label>
          <textarea name="instructions"
                    id="rec-instructions"
                    rows="8"
                    class="form-control-custom <?= isset($errors['instructions']) ? 'is-invalid' : '' ?>"
                    required><?= htmlspecialchars($instructions ?? '') ?></textarea>
          <?php if (isset($errors['instructions'])): ?>
            <div class="form-feedback error d-block"><?= htmlspecialchars($errors['instructions']) ?></div>
          <?php endif; ?>
        </div>

        <!-- Action Buttons -->
        <div class="col-12 mt-4 pt-2 border-top d-flex gap-2">
          <button type="submit" class="btn-send px-4 py-2">
            <i class="bi bi-check2-circle me-1"></i> Save Changes
          </button>
          <a href="dashboard.php" class="btn btn-outline-secondary px-3 py-2" style="border-radius:10px;">
            Cancel
          </a>
        </div>

      </div>
    </form>
  </div>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
