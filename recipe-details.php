<?php
// ============================================================
// Recipe Detail Page
// Project: Diary of Taste - Digital Recipe Book
// ============================================================

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$recipe = null;

if ($id > 0) {
    $recipe = get_recipe_by_id($pdo, $id);
}

// If no recipe found by ID, default to the first available approved recipe in DB
if (!$recipe) {
    $stmt = $pdo->query("SELECT id FROM recipes WHERE status = 'approved' ORDER BY id ASC LIMIT 1");
    $first_id = $stmt->fetchColumn();
    if ($first_id) {
        redirect("recipe-details.php?id=" . (int)$first_id);
    }
}

// Check permission for non-approved recipes (author or admin can preview)
$is_author_or_admin = is_admin() || (isset($_SESSION['user_id']) && $recipe && (int)$_SESSION['user_id'] === (int)$recipe['user_id']);
$is_publicly_visible = $recipe && ($recipe['status'] === 'approved');

// Fetch related recipes
$related_recipes = [];
if ($recipe && ($is_publicly_visible || $is_author_or_admin)) {
    $related_recipes = get_related_recipes($pdo, $recipe['category'], $recipe['id'], 3);
}

$page_title = $recipe ? $recipe['title'] : 'Recipe Details';
$active_page = 'recipes';
$base_path = '';

require_once __DIR__ . '/includes/header.php';
?>

<main class="container my-4">

  <?php if (!$recipe): ?>
    <div class="text-center py-5">
      <h2>Recipe Not Found</h2>
      <p class="text-muted">The requested recipe could not be found or has been removed.</p>
      <a href="recipes.php" class="btn btn-outline-dark">Return to Recipes</a>
    </div>
  <?php elseif (!$is_publicly_visible && !$is_author_or_admin): ?>
    <div class="text-center py-5">
      <i class="bi bi-clock-history display-4 text-warning mb-3 d-block"></i>
      <h2>Recipe Currently Unavailable</h2>
      <p class="text-muted">This recipe is currently awaiting administrator review or has been temporarily hidden.</p>
      <a href="recipes.php" class="btn btn-outline-dark mt-2">Return to Recipe Library</a>
    </div>
  <?php else: ?>

    <?php if ($recipe['status'] !== 'approved'): ?>
      <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
        <div>
          <strong>Owner / Admin Preview:</strong> This recipe status is currently <strong><?= htmlspecialchars(ucfirst($recipe['status'])) ?></strong> and is not yet visible to the general public.
        </div>
      </div>
    <?php endif; ?>


    <!-- BREADCRUMB -->
    <nav class="breadcrumb-trail" aria-label="breadcrumb">
      <a href="index.php">Home</a>
      <span class="sep">&rsaquo;</span>
      <a href="recipes.php">Recipes</a>
      <span class="sep">&rsaquo;</span>
      <a href="recipes.php?category=<?= urlencode($recipe['category']) ?>"><?= htmlspecialchars($recipe['category']) ?></a>
      <span class="sep">&rsaquo;</span>
      <span class="current"><?= htmlspecialchars($recipe['title']) ?></span>
    </nav>

    <!-- RECIPE HERO -->
    <div class="row g-4 mb-5">
      <div class="col-lg-6">
        <div class="recipe-hero-img" style="background-image:url('<?= htmlspecialchars($recipe['image_url'] ?: 'https://images.unsplash.com/photo-1495521821757-a1efb6729352?q=80&w=1200&auto=format&fit=crop') ?>');"></div>
      </div>
      <div class="col-lg-6">
        <div class="recipe-hero-tags">
          <span class="tag-pill"><?= htmlspecialchars($recipe['category']) ?></span>
          <span class="tag-pill"><i class="bi bi-person"></i> By <?= htmlspecialchars($recipe['author'] ?? 'Community Chef') ?></span>
        </div>
        <h1 class="recipe-hero-title"><?= htmlspecialchars($recipe['title']) ?></h1>
        <p class="recipe-hero-desc">
          <?= htmlspecialchars($recipe['description'] ?: 'A time-tested home recipe crafted with passion and fresh ingredients.') ?>
        </p>

        <div class="recipe-stats">
          <div>
            <span class="stat-label">Prep Time</span>
            <span class="stat-value"><?= htmlspecialchars($recipe['prep_time'] ?? '15 Min') ?></span>
          </div>
          <div>
            <span class="stat-label">Cook Time</span>
            <span class="stat-value"><?= htmlspecialchars($recipe['cook_time'] ?? '20 Min') ?></span>
          </div>
          <div>
            <span class="stat-label">Servings</span>
            <span class="stat-value"><?= htmlspecialchars($recipe['servings'] ?? '4') ?></span>
          </div>
          <div>
            <span class="stat-label">Difficulty</span>
            <span class="stat-value"><?= htmlspecialchars($recipe['difficulty'] ?? 'Easy') ?></span>
          </div>
        </div>

        <div class="recipe-actions">
          <?php if (isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === (int)$recipe['user_id']): ?>
            <a href="edit-recipe.php?id=<?= (int)$recipe['id'] ?>" class="btn btn-warning" style="border-radius:10px;padding:0.65rem 1.2rem;font-weight:600;">
              <i class="bi bi-pencil-square me-1"></i> Edit My Recipe
            </a>
          <?php endif; ?>
          <button type="button" class="btn-print" onclick="window.print();">
            <i class="bi bi-printer"></i> Print Recipe
          </button>
          <a href="recipes.php?category=<?= urlencode($recipe['category']) ?>" class="btn btn-outline-secondary" style="border-radius:10px;padding:0.65rem 1.2rem;font-weight:600;">
            <i class="bi bi-tag"></i> More <?= htmlspecialchars($recipe['category']) ?>
          </a>
        </div>
      </div>
    </div>

    <!-- INGREDIENTS + METADATA -->
    <div class="row g-4 mb-5">
      <div class="col-lg-8">
        <h2 class="section-title-lg">Ingredients</h2>
        <p class="text-muted small">Check off ingredients as you prepare your workspace:</p>
        <ul class="ingredient-list" id="ingredient-list">
          <?php
          $ingredient_lines = preg_split("/\r\n|\n|\r/", trim($recipe['ingredients']));
          $ing_idx = 0;
          foreach ($ingredient_lines as $line):
              $line = trim($line);
              if (empty($line)) continue;
              $ing_idx++;
          ?>
            <li class="ingredient-item">
              <input type="checkbox" id="ing-<?= $ing_idx ?>">
              <label for="ing-<?= $ing_idx ?>"><span><?= htmlspecialchars($line) ?></span></label>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>

      <div class="col-lg-4">
        <div class="metadata-card shadow-sm">
          <h6>Record Information</h6>
          <p class="meta-label">Submitted By</p>
          <p class="meta-value"><?= htmlspecialchars($recipe['author'] ?? 'Admin Chef') ?></p>
          <p class="meta-label">Date Added</p>
          <p class="meta-value"><?= date('F j, Y', strtotime($recipe['created_at'])) ?></p>
          <p class="meta-label">Primary Category</p>
          <p class="meta-value"><?= htmlspecialchars($recipe['category']) ?></p>
          <p class="meta-label">Course Code</p>
          <p class="meta-value">ICT 1209 - Mini Project</p>
        </div>
      </div>
    </div>

    <!-- COOKING INSTRUCTIONS -->
    <section class="mb-5">
      <h2 class="section-title-lg">Cooking Instructions</h2>
      <ol class="instruction-list">
        <?php
        $instruction_lines = preg_split("/\r\n|\n|\r/", trim($recipe['instructions']));
        $step_idx = 0;
        foreach ($instruction_lines as $step):
            $step = trim($step);
            if (empty($step)) continue;
            // Strip leading "1. " or "Step 1: " if present
            $step_text = preg_replace('/^(\d+[\.\)]|step\s*\d+:?)\s*/i', '', $step);
            $step_idx++;
            $formatted_num = str_pad((string)$step_idx, 2, '0', STR_PAD_LEFT);
        ?>
          <li class="instruction-item">
            <span class="step-num"><?= $formatted_num ?></span>
            <p><?= htmlspecialchars($step_text) ?></p>
          </li>
        <?php endforeach; ?>
      </ol>
    </section>

    <!-- RELATED RECIPES -->
    <?php if (!empty($related_recipes)): ?>
      <section class="my-5 pt-4 border-top">
        <div class="section-heading">
          <h2>More in <?= htmlspecialchars($recipe['category']) ?></h2>
          <a href="recipes.php?category=<?= urlencode($recipe['category']) ?>" class="view-all">View All</a>
        </div>
        <div class="row g-4">
          <?php foreach ($related_recipes as $related): ?>
            <div class="col-12 col-md-4">
              <div class="recipe-card">
                <div class="recipe-img" style="background-image:url('<?= htmlspecialchars($related['image_url'] ?: 'https://images.unsplash.com/photo-1495521821757-a1efb6729352?q=80&w=800&auto=format&fit=crop') ?>');">
                  <span class="badge-tag"><?= htmlspecialchars($related['category']) ?></span>
                </div>
                <div class="recipe-body">
                  <h3><?= htmlspecialchars($related['title']) ?></h3>
                  <p><?= htmlspecialchars($related['description'] ?: substr($related['instructions'], 0, 80) . '...') ?></p>
                  <a href="recipe-details.php?id=<?= (int)$related['id'] ?>" class="btn-outline-recipe">View Recipe</a>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>

  <?php endif; ?>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
