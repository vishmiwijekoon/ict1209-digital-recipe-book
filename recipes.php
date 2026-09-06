<?php
// ============================================================
// Recipe Library / Catalog Page
// Project: Diary of Taste - Digital Recipe Book
// ============================================================

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$search   = trim($_GET['search'] ?? '');
$category = trim($_GET['category'] ?? 'all');

try {
    $recipes = get_all_recipes($pdo, $category, $search);
} catch (PDOException $e) {
    $recipes = [];
}

$page_title = 'Recipes';
$active_page = 'recipes';
$base_path = '';

require_once __DIR__ . '/includes/header.php';
?>

<!-- PAGE HEADER -->
<header class="page-header">
  <div class="container text-center">
    <h1>Browse Recipes</h1>
    <p>Find delicious home-tested recipes by dish name, ingredient, or category.</p>
  </div>
</header>

<main class="container my-4">

  <!-- SEARCH + FILTERS -->
  <div class="recipe-toolbar mb-4">
    <form class="recipe-search" id="recipe-search-form" action="recipes.php" method="GET" novalidate>
      <span class="search-icon"><i class="bi bi-search"></i></span>
      <label for="recipe-search-input" class="visually-hidden">Search by recipe name</label>
      <input type="text"
             name="search"
             id="recipe-search-input"
             placeholder="Search by recipe name or ingredient..."
             value="<?= htmlspecialchars($search) ?>"
             autocomplete="off">
    </form>
    <button type="submit" form="recipe-search-form" class="btn-search">Search</button>

    <div class="filter-chips mt-3" id="filter-chips" role="group" aria-label="Filter recipes by category">
      <?php
      $categories = ['all' => 'All', 'Breakfast' => 'Breakfast', 'Lunch' => 'Lunch', 'Dinner' => 'Dinner', 'Desserts' => 'Desserts', 'Drinks' => 'Drinks'];
      foreach ($categories as $cat_key => $cat_label):
          $is_active = (strtolower($category) === strtolower($cat_key)) ? 'active' : '';
      ?>
        <a href="recipes.php?category=<?= urlencode($cat_key) ?>"
           class="chip <?= $is_active ?>"
           data-filter="<?= htmlspecialchars($cat_key) ?>"><?= $cat_label ?></a>
      <?php endforeach; ?>
    </div>
  </div>

  <?php if (!empty($search) || ($category !== 'all' && !empty($category))): ?>
    <div class="d-flex align-items-center justify-content-between mb-3 p-2 bg-light rounded border">
      <span class="small text-muted">
        Active filter:
        <strong><?= htmlspecialchars(ucfirst($category)) ?></strong>
        <?php if (!empty($search)): ?> | Query: "<strong><?= htmlspecialchars($search) ?></strong>"<?php endif; ?>
        (<?= count($recipes) ?> result<?= count($recipes) === 1 ? '' : 's' ?>)
      </span>
      <a href="recipes.php" class="small text-danger text-decoration-none"><i class="bi bi-x-circle"></i> Clear Filters</a>
    </div>
  <?php endif; ?>

  <!-- RECIPE GRID -->
  <div class="row g-4" id="recipe-grid">
    <?php if (!empty($recipes)): ?>
      <?php
      $idx = 0;
      foreach ($recipes as $recipe):
          $idx++;
          // First 6 are visible, subsequent can be toggled by load more
          $is_extra = ($idx > 6 && empty($search) && $category === 'all') ? 'true' : 'false';
          $hidden_class = ($is_extra === 'true') ? 'is-hidden' : '';
      ?>
        <div class="col-12 col-md-6 col-lg-4 recipe-item <?= $hidden_class ?>"
             data-category="<?= htmlspecialchars($recipe['category']) ?>"
             data-name="<?= htmlspecialchars($recipe['title']) ?>"
             data-extra="<?= $is_extra ?>">
          <div class="recipe-card">
            <div class="recipe-img" style="background-image:url('<?= htmlspecialchars($recipe['image_url'] ?: 'https://images.unsplash.com/photo-1495521821757-a1efb6729352?q=80&w=800&auto=format&fit=crop') ?>');">
              <span class="badge-tag"><?= htmlspecialchars($recipe['category']) ?></span>
            </div>
            <div class="recipe-body">
              <h3><?= htmlspecialchars($recipe['title']) ?></h3>
              <p><?= htmlspecialchars($recipe['description'] ?: substr($recipe['instructions'], 0, 95) . '...') ?></p>

              <div class="d-flex justify-content-between align-items-center text-muted small mt-2 mb-3">
                <span><i class="bi bi-clock"></i> <?= htmlspecialchars($recipe['prep_time'] ?? '15m') ?> prep</span>
                <span><i class="bi bi-fire"></i> <?= htmlspecialchars($recipe['cook_time'] ?? '20m') ?> cook</span>
                <span><i class="bi bi-person"></i> <?= htmlspecialchars($recipe['author'] ?? 'Chef') ?></span>
              </div>

              <a href="recipe-details.php?id=<?= (int)$recipe['id'] ?>" class="btn-outline-recipe w-100 text-center">View Recipe</a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="col-12 text-center py-5">
        <i class="bi bi-search display-5 text-muted mb-3 d-block"></i>
        <h4>No recipes found</h4>
        <p class="text-muted">No recipes match your filter criteria. Try a different search term or category.</p>
        <a href="recipes.php" class="btn btn-outline-dark mt-2">Reset All Filters</a>
      </div>
    <?php endif; ?>
  </div>

  <p class="no-results" id="no-results">No recipes match your instant search.</p>

  <!-- LOAD MORE (if total recipes > 6 and default view) -->
  <?php if (count($recipes) > 6 && empty($search) && $category === 'all'): ?>
    <div class="load-more-wrap text-center my-4">
      <button type="button" class="btn-load-more" id="load-more-btn">
        <i class="bi bi-arrow-clockwise"></i> Load More Recipes
      </button>
    </div>
  <?php endif; ?>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
