<?php
// ============================================================
// Homepage (Dynamic)
// Project: Diary of Taste - Digital Recipe Book
// ============================================================

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

// Fetch featured recipes from MySQL
$featured_recipes = [];
try {
    $featured_recipes = get_featured_recipes($pdo, 3);
} catch (PDOException $e) {
    // Graceful fallback
}

$page_title = 'Home';
$active_page = 'home';
$base_path = '';

require_once __DIR__ . '/includes/header.php';
?>

<!-- HERO SECTION -->
<header class="hero-section">
  <div class="container">
    <div class="row g-4 align-items-stretch">
      <div class="col-lg-6">
        <div class="hero-copy">
          <p class="eyebrow mb-2">Home cooking, curated</p>
          <h1>Discover Delicious Recipes</h1>
          <p class="lede">Find your next favourite meal from our curated culinary collection.</p>
          <form class="search-form" id="search-form" action="recipes.php" method="GET" novalidate>
            <label for="search-input" class="visually-hidden">Search recipes</label>
            <input type="text" name="search" id="search-input" placeholder="Search recipes by name or ingredient" autocomplete="off">
            <button type="submit" aria-label="Search">
              <i class="bi bi-search"></i>
            </button>
          </form>
          <div id="search-feedback" class="form-feedback" role="status" aria-live="polite"></div>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="hero-image">
          <span class="hero-tag"><i class="bi bi-stars me-1 text-warning"></i> Freshly Curated Recipes</span>
        </div>
      </div>
    </div>
  </div>
</header>

<!-- CATEGORIES -->
<section class="container my-5" id="categories">
  <div class="section-heading">
    <h2>Recipe Categories</h2>
    <a href="recipes.php" class="view-all">View All</a>
  </div>
  <div class="row g-3 row-cols-2 row-cols-md-5">
    <div class="col">
      <a href="recipes.php?category=Breakfast" class="category-card" data-category="Breakfast">
        <span class="cat-icon"><i class="bi bi-egg-fried"></i></span>
        <span class="cat-label">Breakfast</span>
      </a>
    </div>
    <div class="col">
      <a href="recipes.php?category=Lunch" class="category-card" data-category="Lunch">
        <span class="cat-icon"><i class="bi bi-basket"></i></span>
        <span class="cat-label">Lunch</span>
      </a>
    </div>
    <div class="col">
      <a href="recipes.php?category=Dinner" class="category-card" data-category="Dinner">
        <span class="cat-icon"><i class="bi bi-fire"></i></span>
        <span class="cat-label">Dinner</span>
      </a>
    </div>
    <div class="col">
      <a href="recipes.php?category=Desserts" class="category-card" data-category="Desserts">
        <span class="cat-icon"><i class="bi bi-cake2"></i></span>
        <span class="cat-label">Desserts</span>
      </a>
    </div>
    <div class="col">
      <a href="recipes.php?category=Drinks" class="category-card" data-category="Drinks">
        <span class="cat-icon"><i class="bi bi-cup-straw"></i></span>
        <span class="cat-label">Drinks</span>
      </a>
    </div>
  </div>
  <p id="filter-notice" class="text-muted mt-3 mb-0" style="font-size:0.9rem;" aria-live="polite"></p>
</section>

<!-- FEATURED RECIPES -->
<section class="container my-5" id="featured">
  <div class="section-heading">
    <h2>Featured Recipes</h2>
    <a href="recipes.php" class="view-all">Browse All Recipes <i class="bi bi-arrow-right"></i></a>
  </div>
  <div class="row g-4">
    <?php if (!empty($featured_recipes)): ?>
      <?php foreach ($featured_recipes as $recipe): ?>
        <div class="col-12 col-md-6 col-lg-4">
          <div class="recipe-card" data-category="<?= htmlspecialchars($recipe['category']) ?>">
            <div class="recipe-img" style="background-image:url('<?= htmlspecialchars($recipe['image_url'] ?: 'https://images.unsplash.com/photo-1495521821757-a1efb6729352?q=80&w=800&auto=format&fit=crop') ?>');">
              <span class="badge-tag"><?= htmlspecialchars($recipe['category']) ?></span>
            </div>
            <div class="recipe-body">
              <h3><?= htmlspecialchars($recipe['title']) ?></h3>
              <p><?= htmlspecialchars($recipe['description'] ?: substr($recipe['instructions'], 0, 90) . '...') ?></p>
              <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                <span class="small text-muted"><i class="bi bi-clock me-1"></i> <?= htmlspecialchars($recipe['cook_time'] ?? '20 Min') ?></span>
                <a href="recipe-details.php?id=<?= (int)$recipe['id'] ?>" class="btn-outline-recipe">View Recipe</a>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="col-12 text-center py-5">
        <p class="text-muted">No recipes found in the database yet. Import <code>database.sql</code> or add new recipes!</p>
        <a href="recipes.php" class="btn btn-outline-dark">Browse Recipe Catalog</a>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- ABOUT / STORY -->
<section class="container my-5">
  <div class="about">
    <div class="floating-shape shape-1"></div>
    <div class="floating-shape shape-2"></div>
    <div class="about-content" style="position: relative; z-index: 1;">
      <p class="eyebrow">Our story</p>
      <h2>About Diary of Taste</h2>
      <p class="mb-4">We're a kitchen-table community dedicated to collecting and sharing home-cooked recipes worth repeating — tested, tasted, and passed on the way good food has always been: from one home cook to another.</p>
      <a href="recipes.php" class="btn-outline-recipe">Browse All Recipes</a>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
