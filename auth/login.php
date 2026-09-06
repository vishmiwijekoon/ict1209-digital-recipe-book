<?php
// ============================================================
// User Login Page
// Project: Diary of Taste - Digital Recipe Book
// ============================================================

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

// If already logged in, redirect to dashboard
if (is_logged_in()) {
    redirect('../dashboard.php');
}

$error = '';
$login_identifier = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_identifier = trim($_POST['login_identifier'] ?? '');
    $password         = $_POST['password'] ?? '';

    if (empty($login_identifier) || empty($password)) {
        $error = 'Please enter both your username/email and password.';
    } else {
        // Query user by either username or email
        $stmt = $pdo->prepare("
            SELECT id, username, email, password, role
            FROM users
            WHERE username = ? OR email = ?
            LIMIT 1
        ");
        $stmt->execute([$login_identifier, $login_identifier]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Requirement: Call session_regenerate_id() after successful login
            session_regenerate_id(true);

            // Store session parameters
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email']    = $user['email'];
            $_SESSION['role']     = $user['role'] ?? 'user';

            set_flash('success', 'Welcome back, ' . htmlspecialchars($user['username']) . '!');
            redirect('../dashboard.php');
        } else {
            $error = 'Invalid username/email or password. Please try again.';
        }
    }
}

$page_title = 'Login';
$active_page = 'login';
$base_path = '../';

require_once __DIR__ . '/../includes/header.php';
?>

<main class="container my-5">
  <div class="row justify-content-center">
    <div class="col-md-8 col-lg-5">
      <div class="contact-panel shadow-sm">
        <div class="text-center mb-4">
          <p class="eyebrow mb-1">Welcome Back</p>
          <h2>Sign In</h2>
          <p class="text-muted small">Log in to manage your submitted recipes and favorites.</p>
        </div>

        <?php if (!empty($error)): ?>
          <div class="alert alert-danger" role="alert">
            <i class="bi bi-exclamation-triangle me-1"></i> <?= htmlspecialchars($error) ?>
          </div>
        <?php endif; ?>

        <form action="login.php" method="POST" novalidate id="login-form">
          <!-- Username / Email -->
          <div class="mb-3">
            <label class="form-label-custom" for="login-id">Username or Email</label>
            <input type="text"
                   name="login_identifier"
                   id="login-id"
                   class="form-control-custom"
                   placeholder="Enter username or email"
                   value="<?= htmlspecialchars($login_identifier) ?>"
                   required>
          </div>

          <!-- Password -->
          <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center">
              <label class="form-label-custom mb-0" for="login-password">Password</label>
            </div>
            <input type="password"
                   name="password"
                   id="login-password"
                   class="form-control-custom"
                   placeholder="Enter your password"
                   required>
          </div>

          <button type="submit" class="btn-send w-100 py-2">
            <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
          </button>
        </form>

        <div class="text-center mt-4 pt-3 border-top">
          <p class="small text-muted mb-0">
            Don't have an account yet?
            <a href="register.php" class="fw-semibold text-decoration-underline">Register now</a>
          </p>
        </div>
      </div>
    </div>
  </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
