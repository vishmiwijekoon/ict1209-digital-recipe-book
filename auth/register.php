<?php
// ============================================================
// User Registration Page
// Project: Diary of Taste - Digital Recipe Book
// ============================================================

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Redirect if already logged in
if (is_logged_in()) {
    redirect('../dashboard.php');
}

$errors = [];
$username = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username         = trim($_POST['username'] ?? '');
    $email            = trim($_POST['email'] ?? '');
    $password         = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($username)) {
        $errors['username'] = 'Username is required.';
    } elseif (strlen($username) < 3 || strlen($username) > 50) {
        $errors['username'] = 'Username must be between 3 and 50 characters.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors['username'] = 'Username can only contain letters, numbers, and underscores.';
    }

    if (empty($email)) {
        $errors['email'] = 'Email address is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }

    if (empty($password)) {
        $errors['password'] = 'Password is required.';
    } elseif (strlen($password) < 6) {
        $errors['password'] = 'Password must be at least 6 characters long.';
    }

    if ($password !== $confirm_password) {
        $errors['confirm_password'] = 'Passwords do not match.';
    }

    // Check unique constraints in database if basic validation passes
    if (empty($errors)) {
        // Check username
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username LIMIT 1");
        $stmt->execute([':username' => $username]);
        if ($stmt->fetch()) {
            $errors['username'] = 'This username is already taken. Please choose another.';
        }

        // Check email
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        if ($stmt->fetch()) {
            $errors['email'] = 'An account with this email already exists. Please log in.';
        }
    }

    // Insert user if valid
    if (empty($errors)) {
        // Explicitly hash with PASSWORD_BCRYPT per assignment specification
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $pdo->prepare("
            INSERT INTO users (username, email, password, role, created_at)
            VALUES (:username, :email, :password, 'user', NOW())
        ");

        try {
            $stmt->execute([
                ':username' => $username,
                ':email'    => $email,
                ':password' => $hashed_password
            ]);

            set_flash('success', 'Registration successful! You can now log in with your credentials.');
            redirect('login.php');
        } catch (PDOException $e) {
            $errors['general'] = 'Registration failed due to a database error. Please try again.';
        }
    }
}

$page_title = 'Register';
$active_page = 'register';
$base_path = '../';

require_once __DIR__ . '/../includes/header.php';
?>

<main class="container my-5">
  <div class="row justify-content-center">
    <div class="col-md-8 col-lg-5">
      <div class="contact-panel shadow-sm">
        <div class="text-center mb-4">
          <p class="eyebrow mb-1">Join Diary of Taste</p>
          <h2>Create an Account</h2>
          <p class="text-muted small">Share your favorite recipes with home cooking enthusiasts worldwide.</p>
        </div>

        <?php if (!empty($errors['general'])): ?>
          <div class="alert alert-danger"><?= htmlspecialchars($errors['general']) ?></div>
        <?php endif; ?>

        <form action="register.php" method="POST" novalidate id="register-form">
          <!-- Username -->
          <div class="mb-3">
            <label class="form-label-custom" for="reg-username">Username</label>
            <input type="text"
                   name="username"
                   id="reg-username"
                   class="form-control-custom <?= isset($errors['username']) ? 'is-invalid' : '' ?>"
                   placeholder="e.g. chef_danuka"
                   value="<?= htmlspecialchars($username) ?>"
                   required>
            <?php if (isset($errors['username'])): ?>
              <div class="form-feedback error d-block"><?= htmlspecialchars($errors['username']) ?></div>
            <?php endif; ?>
          </div>

          <!-- Email -->
          <div class="mb-3">
            <label class="form-label-custom" for="reg-email">Email Address</label>
            <input type="email"
                   name="email"
                   id="reg-email"
                   class="form-control-custom <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                   placeholder="e.g. you@example.com"
                   value="<?= htmlspecialchars($email) ?>"
                   required>
            <?php if (isset($errors['email'])): ?>
              <div class="form-feedback error d-block"><?= htmlspecialchars($errors['email']) ?></div>
            <?php endif; ?>
          </div>

          <!-- Password -->
          <div class="mb-3">
            <label class="form-label-custom" for="reg-password">Password</label>
            <input type="password"
                   name="password"
                   id="reg-password"
                   class="form-control-custom <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                   placeholder="At least 6 characters"
                   required>
            <?php if (isset($errors['password'])): ?>
              <div class="form-feedback error d-block"><?= htmlspecialchars($errors['password']) ?></div>
            <?php endif; ?>
          </div>

          <!-- Confirm Password -->
          <div class="mb-4">
            <label class="form-label-custom" for="reg-confirm">Confirm Password</label>
            <input type="password"
                   name="confirm_password"
                   id="reg-confirm"
                   class="form-control-custom <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>"
                   placeholder="Re-enter your password"
                   required>
            <?php if (isset($errors['confirm_password'])): ?>
              <div class="form-feedback error d-block"><?= htmlspecialchars($errors['confirm_password']) ?></div>
            <?php endif; ?>
          </div>

          <button type="submit" class="btn-send w-100 py-2">
            <i class="bi bi-person-plus me-1"></i> Register Account
          </button>
        </form>

        <div class="text-center mt-4 pt-3 border-top">
          <p class="small text-muted mb-0">
            Already have an account?
            <a href="login.php" class="fw-semibold text-decoration-underline">Sign In here</a>
          </p>
        </div>
      </div>
    </div>
  </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
