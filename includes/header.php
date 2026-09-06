<?php
// ============================================================
// Header & Navbar Template
// Project: Diary of Taste - Digital Recipe Book
// ============================================================

if (!isset($base_path)) {
    $base_path = '';
}
$site_title = isset($page_title) ? $page_title . ' - Diary of Taste' : 'Diary of Taste - Digital Recipe Book';
$active_page = $active_page ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($site_title) ?></title>
  <meta name="description" content="<?= htmlspecialchars($page_description ?? 'A modern digital recipe book to discover, share, and enjoy curated home cooking.') ?>">
  <link rel="icon" type="image/x-icon" href="<?= $base_path ?>images/favicon.png">

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;600;700&family=Roboto+Slab:wght@400;500;600;700&family=Work+Sans:wght@400;500;600&display=swap" rel="stylesheet">

  <!-- Bootstrap 5 CSS -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">

  <!-- Site Stylesheet -->
  <link rel="stylesheet" href="<?= $base_path ?>css/style.css">
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg site-navbar sticky-top">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center" href="<?= $base_path ?>index.php">
      <img src="<?= $base_path ?>images/logo.png" alt="Diary of Taste Logo" class="navbar-logo">
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
      data-bs-target="#mainNav" aria-controls="mainNav"
      aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav mx-auto text-center">
        <li class="nav-item"><a class="nav-link <?= $active_page === 'home' ? 'active' : '' ?>" href="<?= $base_path ?>index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link <?= $active_page === 'recipes' ? 'active' : '' ?>" href="<?= $base_path ?>recipes.php">Recipes</a></li>
        <li class="nav-item"><a class="nav-link <?= $active_page === 'contact' ? 'active' : '' ?>" href="<?= $base_path ?>contact.php">Contact</a></li>
        <?php if (is_logged_in()): ?>
          <li class="nav-item"><a class="nav-link <?= $active_page === 'dashboard' ? 'active' : '' ?>" href="<?= $base_path ?>dashboard.php">Dashboard</a></li>
        <?php endif; ?>
      </ul>

      <div class="d-flex flex-column flex-lg-row align-items-center gap-2 mt-3 mt-lg-0">
        <?php if (is_logged_in()): ?>
          <span class="navbar-text text-muted small me-lg-2">
            <i class="bi bi-person-circle text-warning"></i> <?= htmlspecialchars($_SESSION['username'] ?? 'Chef') ?>
          </span>
          <a href="<?= $base_path ?>dashboard.php" class="btn btn-sm btn-outline-dark" style="border-radius:999px;padding:0.4rem 1rem;font-size:0.85rem;font-weight:600;">
            <i class="bi bi-speedometer2"></i> Dashboard
          </a>
          <a href="<?= $base_path ?>auth/logout.php" class="btn btn-sm btn-outline-danger" style="border-radius:999px;padding:0.4rem 1rem;font-size:0.85rem;">
            <i class="bi bi-box-arrow-right"></i> Logout
          </a>
        <?php else: ?>
          <a href="<?= $base_path ?>auth/login.php" class="btn-login <?= $active_page === 'login' ? 'active' : '' ?> text-center">
            Login
          </a>
          <a href="<?= $base_path ?>auth/register.php" class="btn btn-sm btn-outline-dark text-center <?= $active_page === 'register' ? 'active' : '' ?>" style="border-radius:999px;padding:0.45rem 1.1rem;font-weight:600;font-size:0.88rem;">
            Register
          </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>

<div class="container">
  <?php display_flash(); ?>
</div>
