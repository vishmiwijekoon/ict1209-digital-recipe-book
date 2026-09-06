<?php
// ============================================================
// Footer Template
// Project: Diary of Taste - Digital Recipe Book
// ============================================================

if (!isset($base_path)) {
    $base_path = '';
}
?>
<!-- FOOTER -->
<footer class="site-footer mt-5">
  <div class="container">
    <div class="row g-4">
      <div class="col-6 col-lg-3">
        <a class="navbar-brand mb-2 d-inline-flex align-items-center" href="<?= $base_path ?>index.php">
          <img src="<?= $base_path ?>images/logo.png" alt="Diary of Taste Logo" class="navbar-logo">
        </a>
        <p class="text-muted small mb-0">Discover Delicious Recipes, one home kitchen at a time.</p>
      </div>
      <div class="col-6 col-lg-3">
        <h6>Quick Links</h6>
        <ul>
          <li><a href="<?= $base_path ?>index.php">Home</a></li>
          <li><a href="<?= $base_path ?>recipes.php">Recipes</a></li>
          <li><a href="<?= $base_path ?>contact.php">Contact</a></li>
          <?php if (is_logged_in()): ?>
            <li><a href="<?= $base_path ?>dashboard.php">Dashboard</a></li>
          <?php else: ?>
            <li><a href="<?= $base_path ?>auth/login.php">Login</a></li>
            <li><a href="<?= $base_path ?>auth/register.php">Register</a></li>
          <?php endif; ?>
        </ul>
      </div>
      <div class="col-6 col-lg-3">
        <h6>Follow Us</h6>
        <div class="social-icons">
          <a href="#" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
          <a href="#" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
          <a href="#" aria-label="Twitter"><i class="bi bi-twitter-x"></i></a>
          <a href="#" aria-label="YouTube"><i class="bi bi-youtube"></i></a>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <h6>Subscribe</h6>
        <form id="subscribe-form" class="footer-subscribe d-flex" novalidate>
          <label for="subscribe-input" class="visually-hidden">Enter your email</label>
          <input type="email" id="subscribe-input" placeholder="Enter your email">
          <button type="submit">Subscribe</button>
        </form>
        <div id="subscribe-feedback" class="form-feedback" role="status" aria-live="polite"></div>
      </div>
    </div>
    <div class="footer-bottom text-center">
      &copy; <?= date('Y') ?> Diary of Taste (ICT 1209 Mini Project). All rights reserved.
    </div>
  </div>
</footer>

<!-- Bootstrap JS bundle -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
<script src="<?= $base_path ?>js/script.js"></script>
</body>
</html>
