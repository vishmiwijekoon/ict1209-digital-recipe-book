<?php
// ============================================================
// User & Admin Dashboard
// Project: Diary of Taste - Digital Recipe Book
// Features: Recipe CRUD, Status Badges, Admin Approvals,
//           Admin Hide/Show Management, Contact Messages
// ============================================================

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

// Enforce login requirement
require_login('auth/login.php');

$current_user_id = (int)$_SESSION['user_id'];
$errors = [];

// ------------------------------------------------------------
// POST Handlers with Strict Role Security
// ------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Security Gate: Ensure non-admins cannot invoke admin actions via request tampering
    $admin_actions = ['approve_recipe', 'reject_recipe', 'hide_recipe', 'show_recipe', 'delete_message'];
    if (in_array($action, $admin_actions, true) && !is_admin()) {
        set_flash('error', 'Unauthorized action. Admin privileges are required.');
        redirect('dashboard.php');
    }

    // 1. Admin Action: Approve Recipe (Publish live)
    if ($action === 'approve_recipe') {
        $recipe_id = (int)($_POST['recipe_id'] ?? 0);
        $redirect_tab = $_POST['redirect_tab'] ?? 'pending';
        if ($recipe_id > 0) {
            $stmt = $pdo->prepare("UPDATE recipes SET status = 'approved' WHERE id = ?");
            $stmt->execute([$recipe_id]);
            set_flash('success', 'Recipe approved and published live!');
        }
        redirect("dashboard.php?tab=$redirect_tab");
    }

    // 2. Admin Action: Reject Recipe
    if ($action === 'reject_recipe') {
        $recipe_id = (int)($_POST['recipe_id'] ?? 0);
        if ($recipe_id > 0) {
            $stmt = $pdo->prepare("UPDATE recipes SET status = 'rejected' WHERE id = ?");
            $stmt->execute([$recipe_id]);
            set_flash('info', 'Recipe submission has been rejected.');
        }
        redirect('dashboard.php?tab=pending');
    }

    // 3. Admin Action: Hide Recipe (Remove from public view)
    if ($action === 'hide_recipe') {
        $recipe_id = (int)($_POST['recipe_id'] ?? 0);
        if ($recipe_id > 0) {
            $stmt = $pdo->prepare("UPDATE recipes SET status = 'hidden' WHERE id = ?");
            $stmt->execute([$recipe_id]);
            set_flash('info', 'Recipe has been hidden from public browsing.');
        }
        redirect('dashboard.php?tab=all');
    }

    // 4. Admin Action: Show Recipe (Restore to public view)
    if ($action === 'show_recipe') {
        $recipe_id = (int)($_POST['recipe_id'] ?? 0);
        if ($recipe_id > 0) {
            $stmt = $pdo->prepare("UPDATE recipes SET status = 'approved' WHERE id = ?");
            $stmt->execute([$recipe_id]);
            set_flash('success', 'Recipe is now visible on the public site!');
        }
        redirect('dashboard.php?tab=all');
    }

    // 5. Admin Action: Delete Contact Message
    if ($action === 'delete_message') {
        $message_id = (int)($_POST['message_id'] ?? 0);
        if ($message_id > 0) {
            $stmt = $pdo->prepare("DELETE FROM messages WHERE id = ?");
            $stmt->execute([$message_id]);
            set_flash('success', 'Contact message deleted successfully.');
        }
        redirect('dashboard.php?tab=messages');
    }

    // 6. Delete Recipe (Admin can delete any recipe; regular user can only delete own)
    if ($action === 'delete') {
        $recipe_id = (int)($_POST['recipe_id'] ?? 0);
        $redirect_tab = $_POST['redirect_tab'] ?? 'list';

        if ($recipe_id > 0) {
            if (is_admin()) {
                $stmt = $pdo->prepare("DELETE FROM recipes WHERE id = ?");
                $stmt->execute([$recipe_id]);
            } else {
                $stmt = $pdo->prepare("DELETE FROM recipes WHERE id = ? AND user_id = ?");
                $stmt->execute([$recipe_id, $current_user_id]);
            }

            if ($stmt->rowCount() > 0) {
                set_flash('success', 'Recipe deleted successfully.');
            } else {
                set_flash('error', 'Could not delete recipe: Permission denied or recipe not found.');
            }
        }
        redirect("dashboard.php?tab=$redirect_tab");
    }

    // 7. Add Recipe Action (Create)
    if ($action === 'create') {
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
            $image_url = 'https://images.unsplash.com/photo-1495521821757-a1efb6729352?q=80&w=800&auto=format&fit=crop';
        }

        if (empty($errors)) {
            // Admin recipes are auto-approved; regular user submissions enter 'pending' review state
            $status = is_admin() ? 'approved' : 'pending';

            try {
                $stmt = $pdo->prepare("
                    INSERT INTO recipes (
                        title, description, ingredients, instructions,
                        category, image_url, prep_time, cook_time, servings, difficulty,
                        status, user_id, created_at
                    ) VALUES (
                        :title, :description, :ingredients, :instructions,
                        :category, :image_url, :prep_time, :cook_time, :servings, :difficulty,
                        :status, :user_id, NOW()
                    )
                ");

                $stmt->execute([
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
                    ':status'       => $status,
                    ':user_id'      => $current_user_id
                ]);

                if (is_admin()) {
                    set_flash('success', 'Recipe "' . htmlspecialchars($title) . '" published live successfully!');
                    redirect('dashboard.php?tab=all');
                } else {
                    set_flash('success', 'Recipe "' . htmlspecialchars($title) . '" submitted successfully! It is currently pending admin review before appearing on the public site.');
                    redirect('dashboard.php?tab=list');
                }
            } catch (PDOException $e) {
                $errors['general'] = 'Failed to save recipe: ' . $e->getMessage();
            }
        }
    }
}

// ------------------------------------------------------------
// Data Preparation for Views
// ------------------------------------------------------------
$my_recipes = get_user_recipes($pdo, $current_user_id);
$approved_count = count_approved_recipes($pdo);

if (is_admin()) {
    $all_system_recipes = get_all_system_recipes($pdo);
    $pending_recipes    = get_pending_recipes($pdo);
    $pending_count      = count_pending_recipes($pdo);
    $all_messages       = get_all_messages($pdo);
    $messages_count     = count_messages($pdo);
    $total_recipes      = count_all_recipes($pdo);
    $default_tab        = 'all';
} else {
    $all_system_recipes = [];
    $pending_recipes    = [];
    $pending_count      = 0;
    $all_messages       = [];
    $messages_count     = 0;
    $total_recipes      = $approved_count;
    $default_tab        = 'list';
}

$active_tab = $_GET['tab'] ?? $default_tab;

$page_title = 'Dashboard';
$active_page = 'dashboard';
$base_path = '';

require_once __DIR__ . '/includes/header.php';
?>

<main class="container my-5">

  <!-- DASHBOARD HERO BANNER -->
  <section class="contact-hero mb-4 text-start p-4 p-md-5">
    <div class="row align-items-center">
      <div class="col-md-8">
        <p class="eyebrow mb-1 text-warning">
          <?= is_admin() ? 'Administrator Control Panel' : 'Chef Portal' ?>
        </p>
        <h1 class="mb-2">Welcome back, <?= htmlspecialchars($_SESSION['username'] ?? 'Chef') ?>!</h1>
        <p class="mb-0 text-muted">
          <?= is_admin()
            ? 'Manage all platform recipes, review pending submissions, toggle recipe visibility (Show/Hide), and respond to contact messages.'
            : 'Track your submitted recipes, check approval statuses, and share new home-tested meals with the community.' ?>
        </p>
      </div>
      <div class="col-md-4 text-md-end mt-3 mt-md-0">
        <a href="#add-recipe-section" class="btn btn-dark px-4 py-2" style="border-radius:10px;" onclick="document.getElementById('pills-add-tab').click();">
          <i class="bi bi-plus-circle me-1"></i> Add New Recipe
        </a>
      </div>
    </div>
  </section>

  <!-- METRIC STAT CARDS -->
  <div class="row g-4 mb-4">
    <?php if (is_admin()): ?>
      <!-- Admin Stat 1: All Recipes & Live Status -->
      <div class="col-md-4">
        <div class="card shadow-sm border-0 p-3 h-100" style="background:var(--cream-deep);border-radius:12px;">
          <div class="d-flex align-items-center">
            <div class="p-3 bg-white rounded-circle me-3 border">
              <i class="bi bi-collection fs-3 text-primary"></i>
            </div>
            <div>
              <div class="text-muted small">Total Platform Recipes</div>
              <div class="fs-3 fw-bold"><?= $total_recipes ?> <span class="fs-6 fw-normal text-muted">(<?= $approved_count ?> Live)</span></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Admin Stat 2: Pending Approvals -->
      <div class="col-md-4">
        <div class="card shadow-sm border-0 p-3 h-100" style="background:var(--cream-deep);border-radius:12px;">
          <div class="d-flex align-items-center">
            <div class="p-3 bg-white rounded-circle me-3 border">
              <i class="bi bi-hourglass-split fs-3 text-warning"></i>
            </div>
            <div>
              <div class="text-muted small">Pending Approvals</div>
              <div class="fs-3 fw-bold text-warning"><?= $pending_count ?></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Admin Stat 3: Contact Messages -->
      <div class="col-md-4">
        <div class="card shadow-sm border-0 p-3 h-100" style="background:var(--cream-deep);border-radius:12px;">
          <div class="d-flex align-items-center">
            <div class="p-3 bg-white rounded-circle me-3 border">
              <i class="bi bi-chat-left-text fs-3 text-success"></i>
            </div>
            <div>
              <div class="text-muted small">Contact Inquiries</div>
              <div class="fs-3 fw-bold text-success"><?= $messages_count ?></div>
            </div>
          </div>
        </div>
      </div>
    <?php else: ?>
      <!-- User Stat 1: My Submissions -->
      <div class="col-md-4">
        <div class="card shadow-sm border-0 p-3 h-100" style="background:var(--cream-deep);border-radius:12px;">
          <div class="d-flex align-items-center">
            <div class="p-3 bg-white rounded-circle me-3 border">
              <i class="bi bi-journal-bookmark fs-3 text-warning"></i>
            </div>
            <div>
              <div class="text-muted small">My Submissions</div>
              <div class="fs-3 fw-bold"><?= count($my_recipes) ?></div>
            </div>
          </div>
        </div>
      </div>

      <!-- User Stat 2: Live Recipes -->
      <div class="col-md-4">
        <div class="card shadow-sm border-0 p-3 h-100" style="background:var(--cream-deep);border-radius:12px;">
          <div class="d-flex align-items-center">
            <div class="p-3 bg-white rounded-circle me-3 border">
              <i class="bi bi-check2-circle fs-3 text-success"></i>
            </div>
            <div>
              <div class="text-muted small">Live Platform Recipes</div>
              <div class="fs-3 fw-bold"><?= $approved_count ?></div>
            </div>
          </div>
        </div>
      </div>

      <!-- User Stat 3: Account Role -->
      <div class="col-md-4">
        <div class="card shadow-sm border-0 p-3 h-100" style="background:var(--cream-deep);border-radius:12px;">
          <div class="d-flex align-items-center">
            <div class="p-3 bg-white rounded-circle me-3 border">
              <i class="bi bi-person-check fs-3 text-primary"></i>
            </div>
            <div>
              <div class="text-muted small">Account Type</div>
              <div class="fs-5 fw-bold text-dark">Recipe Contributor</div>
            </div>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <!-- TABS CONTAINER -->
  <div class="card shadow-sm border-0 p-4" style="border-radius:12px;" id="add-recipe-section">

    <ul class="nav nav-pills mb-4" id="pills-tab" role="tablist">
      <?php if (is_admin()): ?>
        <!-- TAB: ALL RECIPES (ADMIN) -->
        <li class="nav-item" role="presentation">
          <button class="nav-link <?= $active_tab === 'all' ? 'active' : '' ?>" id="pills-all-tab" data-bs-toggle="pill" data-bs-target="#pills-all" type="button" role="tab">
            <i class="bi bi-collection me-1"></i> All Recipes (<?= count($all_system_recipes) ?>)
          </button>
        </li>

        <!-- TAB: PENDING APPROVALS (ADMIN) -->
        <li class="nav-item" role="presentation">
          <button class="nav-link <?= $active_tab === 'pending' ? 'active' : '' ?>" id="pills-pending-tab" data-bs-toggle="pill" data-bs-target="#pills-pending" type="button" role="tab">
            <i class="bi bi-hourglass-split me-1"></i> Pending Approvals
            <?php if ($pending_count > 0): ?>
              <span class="badge bg-warning text-dark rounded-pill ms-1"><?= $pending_count ?></span>
            <?php endif; ?>
          </button>
        </li>

        <!-- TAB: CONTACT MESSAGES (ADMIN) -->
        <li class="nav-item" role="presentation">
          <button class="nav-link <?= $active_tab === 'messages' ? 'active' : '' ?>" id="pills-messages-tab" data-bs-toggle="pill" data-bs-target="#pills-messages" type="button" role="tab">
            <i class="bi bi-envelope me-1"></i> Contact Messages
            <span class="badge bg-secondary rounded-pill ms-1"><?= $messages_count ?></span>
          </button>
        </li>
      <?php endif; ?>

      <!-- TAB: MY RECIPES (BOTH USER & ADMIN) -->
      <li class="nav-item" role="presentation">
        <button class="nav-link <?= $active_tab === 'list' ? 'active' : '' ?>" id="pills-list-tab" data-bs-toggle="pill" data-bs-target="#pills-list" type="button" role="tab">
          <i class="bi bi-journal-text me-1"></i> My Recipes (<?= count($my_recipes) ?>)
        </button>
      </li>

      <!-- TAB: ADD NEW RECIPE -->
      <li class="nav-item" role="presentation">
        <button class="nav-link <?= $active_tab === 'add' ? 'active' : '' ?>" id="pills-add-tab" data-bs-toggle="pill" data-bs-target="#pills-add" type="button" role="tab">
          <i class="bi bi-plus-lg me-1"></i> Add New Recipe
        </button>
      </li>
    </ul>

    <div class="tab-content" id="pills-tabContent">

      <?php if (is_admin()): ?>
        <!-- ============================================================ -->
        <!-- TAB 1: ALL RECIPES MANAGEMENT (SHOW / HIDE TOGGLE)           -->
        <!-- ============================================================ -->
        <div class="tab-pane fade <?= $active_tab === 'all' ? 'show active' : '' ?>" id="pills-all" role="tabpanel">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
              <h4 class="mb-0">All System Recipes</h4>
              <small class="text-muted">Manage visibility: click <strong>Hide</strong> to remove from public, or <strong>Show</strong> to publish.</small>
            </div>
            <span class="badge bg-primary fs-6"><?= count($all_system_recipes) ?> Total</span>
          </div>

          <?php if (!empty($all_system_recipes)): ?>
            <div class="table-responsive">
              <table class="table table-hover align-middle">
                <thead class="table-light">
                  <tr>
                    <th style="width: 70px;">Image</th>
                    <th>Recipe Title</th>
                    <th>Author</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Date Added</th>
                    <th class="text-end" style="min-width: 250px;">Visibility & Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($all_system_recipes as $rec): ?>
                    <?php $st = $rec['status'] ?? 'pending'; ?>
                    <tr>
                      <td>
                        <img src="<?= htmlspecialchars($rec['image_url'] ?: 'images/favicon.png') ?>"
                             alt="Thumbnail"
                             class="rounded"
                             style="width: 55px; height: 45px; object-fit: cover;">
                      </td>
                      <td>
                        <a href="recipe-details.php?id=<?= (int)$rec['id'] ?>" class="fw-semibold text-dark text-decoration-none">
                          <?= htmlspecialchars($rec['title']) ?>
                        </a>
                      </td>
                      <td>
                        <span class="badge bg-secondary"><?= htmlspecialchars($rec['author'] ?? 'User #' . $rec['user_id']) ?></span>
                      </td>
                      <td>
                        <span class="badge bg-light text-dark border"><?= htmlspecialchars($rec['category']) ?></span>
                      </td>
                      <td>
                        <?php if ($st === 'approved'): ?>
                          <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> Live</span>
                        <?php elseif ($st === 'hidden'): ?>
                          <span class="badge bg-secondary"><i class="bi bi-eye-slash me-1"></i> Hidden</span>
                        <?php elseif ($st === 'rejected'): ?>
                          <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i> Rejected</span>
                        <?php else: ?>
                          <span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i> Pending</span>
                        <?php endif; ?>
                      </td>
                      <td class="small text-muted">
                        <?= date('M d, Y', strtotime($rec['created_at'])) ?>
                      </td>
                      <td class="text-end">
                        <!-- TOGGLE HIDE / SHOW -->
                        <?php if ($st === 'approved'): ?>
                          <form action="dashboard.php" method="POST" class="d-inline">
                            <input type="hidden" name="action" value="hide_recipe">
                            <input type="hidden" name="recipe_id" value="<?= (int)$rec['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-secondary me-1" title="Hide from public site">
                              <i class="bi bi-eye-slash"></i> Hide
                            </button>
                          </form>
                        <?php else: ?>
                          <form action="dashboard.php" method="POST" class="d-inline">
                            <input type="hidden" name="action" value="show_recipe">
                            <input type="hidden" name="recipe_id" value="<?= (int)$rec['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-success me-1" title="Publish live to public site">
                              <i class="bi bi-eye"></i> Show
                            </button>
                          </form>
                        <?php endif; ?>

                        <!-- VIEW -->
                        <a href="recipe-details.php?id=<?= (int)$rec['id'] ?>" class="btn btn-sm btn-outline-dark me-1" title="View Recipe">
                          <i class="bi bi-box-arrow-up-right"></i>
                        </a>

                        <!-- EDIT (Author can edit or Admin can edit if needed) -->
                        <a href="edit-recipe.php?id=<?= (int)$rec['id'] ?>" class="btn btn-sm btn-outline-primary me-1" title="Edit Recipe">
                          <i class="bi bi-pencil"></i>
                        </a>

                        <!-- DELETE -->
                        <form action="dashboard.php" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to permanently delete this recipe?');">
                          <input type="hidden" name="action" value="delete">
                          <input type="hidden" name="recipe_id" value="<?= (int)$rec['id'] ?>">
                          <input type="hidden" name="redirect_tab" value="all">
                          <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Recipe">
                            <i class="bi bi-trash"></i>
                          </button>
                        </form>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php else: ?>
            <div class="text-center py-5">
              <i class="bi bi-journal display-4 text-muted mb-3 d-block"></i>
              <h5>No recipes in the database yet</h5>
            </div>
          <?php endif; ?>
        </div>

        <!-- ============================================================ -->
        <!-- TAB 2: PENDING APPROVALS QUEUE (ADMIN)                       -->
        <!-- ============================================================ -->
        <div class="tab-pane fade <?= $active_tab === 'pending' ? 'show active' : '' ?>" id="pills-pending" role="tabpanel">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
              <h4 class="mb-0">Recipes Awaiting Approval</h4>
              <small class="text-muted">Review submitted recipes before they are published to the public catalog.</small>
            </div>
            <span class="badge bg-warning text-dark fs-6"><?= count($pending_recipes) ?> Pending</span>
          </div>

          <?php if (!empty($pending_recipes)): ?>
            <div class="table-responsive">
              <table class="table table-hover align-middle">
                <thead class="table-light">
                  <tr>
                    <th style="width: 70px;">Image</th>
                    <th>Recipe Title</th>
                    <th>Author</th>
                    <th>Category</th>
                    <th>Date Submitted</th>
                    <th class="text-end" style="min-width: 230px;">Review Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($pending_recipes as $pending): ?>
                    <tr>
                      <td>
                        <img src="<?= htmlspecialchars($pending['image_url'] ?: 'images/favicon.png') ?>"
                             alt="Thumbnail"
                             class="rounded"
                             style="width: 55px; height: 45px; object-fit: cover;">
                      </td>
                      <td>
                        <div class="fw-semibold"><?= htmlspecialchars($pending['title']) ?></div>
                        <small class="text-muted"><?= htmlspecialchars($pending['description'] ?: substr($pending['instructions'], 0, 60) . '...') ?></small>
                      </td>
                      <td>
                        <span class="badge bg-secondary"><?= htmlspecialchars($pending['author'] ?? 'User #' . $pending['user_id']) ?></span>
                      </td>
                      <td>
                        <span class="badge bg-light text-dark border"><?= htmlspecialchars($pending['category']) ?></span>
                      </td>
                      <td class="small text-muted">
                        <?= date('M d, Y', strtotime($pending['created_at'])) ?>
                      </td>
                      <td class="text-end">
                        <a href="recipe-details.php?id=<?= (int)$pending['id'] ?>" class="btn btn-sm btn-outline-dark me-1" target="_blank" title="Preview Recipe">
                          <i class="bi bi-box-arrow-up-right"></i> Preview
                        </a>
                        <form action="dashboard.php" method="POST" class="d-inline">
                          <input type="hidden" name="action" value="approve_recipe">
                          <input type="hidden" name="recipe_id" value="<?= (int)$pending['id'] ?>">
                          <input type="hidden" name="redirect_tab" value="pending">
                          <button type="submit" class="btn btn-sm btn-success me-1">
                            <i class="bi bi-check-lg"></i> Approve
                          </button>
                        </form>
                        <form action="dashboard.php" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to reject this recipe?');">
                          <input type="hidden" name="action" value="reject_recipe">
                          <input type="hidden" name="recipe_id" value="<?= (int)$pending['id'] ?>">
                          <button type="submit" class="btn btn-sm btn-outline-danger">
                            <i class="bi bi-x-lg"></i> Reject
                          </button>
                        </form>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php else: ?>
            <div class="text-center py-5">
              <i class="bi bi-check2-circle display-4 text-success mb-3 d-block"></i>
              <h5>All Caught Up!</h5>
              <p class="text-muted small">There are currently no recipes pending review.</p>
            </div>
          <?php endif; ?>
        </div>

        <!-- ============================================================ -->
        <!-- TAB 3: CONTACT MESSAGES PANEL (ADMIN)                        -->
        <!-- ============================================================ -->
        <div class="tab-pane fade <?= $active_tab === 'messages' ? 'show active' : '' ?>" id="pills-messages" role="tabpanel">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
              <h4 class="mb-0">Contact Page Inquiries</h4>
              <small class="text-muted">Messages received from visitors through the Contact Us form (contact.php).</small>
            </div>
            <span class="badge bg-secondary fs-6"><?= count($all_messages) ?> Messages</span>
          </div>

          <?php if (!empty($all_messages)): ?>
            <div class="table-responsive">
              <table class="table table-hover align-middle">
                <thead class="table-light">
                  <tr>
                    <th style="width: 60px;">#ID</th>
                    <th>Sender Name</th>
                    <th>Email Address</th>
                    <th>Message</th>
                    <th>Date Received</th>
                    <th class="text-end" style="width: 100px;">Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($all_messages as $msg): ?>
                    <tr>
                      <td class="text-muted small">#<?= (int)$msg['id'] ?></td>
                      <td class="fw-semibold"><?= htmlspecialchars($msg['name']) ?></td>
                      <td>
                        <a href="mailto:<?= htmlspecialchars($msg['email']) ?>" class="text-decoration-none">
                          <i class="bi bi-envelope-at me-1"></i><?= htmlspecialchars($msg['email']) ?>
                        </a>
                      </td>
                      <td>
                        <div class="p-2 rounded bg-light border text-break small" style="max-height: 120px; overflow-y: auto;">
                          <?= nl2br(htmlspecialchars($msg['message'])) ?>
                        </div>
                      </td>
                      <td class="small text-muted">
                        <?= date('M d, Y H:i', strtotime($msg['created_at'])) ?>
                      </td>
                      <td class="text-end">
                        <form action="dashboard.php" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this message?');">
                          <input type="hidden" name="action" value="delete_message">
                          <input type="hidden" name="message_id" value="<?= (int)$msg['id'] ?>">
                          <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete message">
                            <i class="bi bi-trash"></i>
                          </button>
                        </form>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php else: ?>
            <div class="text-center py-5">
              <i class="bi bi-inbox display-4 text-muted mb-3 d-block"></i>
              <h5>No Messages Yet</h5>
              <p class="text-muted small">Inquiries submitted via the Contact Us form will appear here.</p>
            </div>
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <!-- ============================================================ -->
      <!-- TAB 4: MY RECIPES (WITH STATUS BADGES & EDIT BUTTON)         -->
      <!-- ============================================================ -->
      <div class="tab-pane fade <?= $active_tab === 'list' ? 'show active' : '' ?>" id="pills-list" role="tabpanel">
        <?php if (!empty($my_recipes)): ?>
          <div class="table-responsive">
            <table class="table table-hover align-middle">
              <thead class="table-light">
                <tr>
                  <th scope="col" style="width: 70px;">Image</th>
                  <th scope="col">Recipe Title</th>
                  <th scope="col">Category</th>
                  <th scope="col">Status</th>
                  <th scope="col">Cooking Info</th>
                  <th scope="col">Date Added</th>
                  <th scope="col" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($my_recipes as $recipe): ?>
                  <?php $rec_status = $recipe['status'] ?? 'pending'; ?>
                  <tr>
                    <td>
                      <img src="<?= htmlspecialchars($recipe['image_url'] ?: 'images/favicon.png') ?>"
                           alt="Thumbnail"
                           class="rounded"
                           style="width: 55px; height: 45px; object-fit: cover;">
                    </td>
                    <td>
                      <a href="recipe-details.php?id=<?= (int)$recipe['id'] ?>" class="fw-semibold text-dark text-decoration-none">
                        <?= htmlspecialchars($recipe['title']) ?>
                      </a>
                    </td>
                    <td>
                      <span class="badge bg-light text-dark border"><?= htmlspecialchars($recipe['category']) ?></span>
                    </td>
                    <td>
                      <?php if ($rec_status === 'approved'): ?>
                        <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> Live</span>
                      <?php elseif ($rec_status === 'hidden'): ?>
                        <span class="badge bg-secondary"><i class="bi bi-eye-slash me-1"></i> Hidden</span>
                      <?php elseif ($rec_status === 'rejected'): ?>
                        <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i> Rejected</span>
                      <?php else: ?>
                        <span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i> Pending Approval</span>
                      <?php endif; ?>
                    </td>
                    <td class="small text-muted">
                      <?= htmlspecialchars($recipe['prep_time'] ?? '15m') ?> prep &bull; <?= htmlspecialchars($recipe['cook_time'] ?? '20m') ?> cook
                    </td>
                    <td class="small text-muted">
                      <?= date('M d, Y', strtotime($recipe['created_at'])) ?>
                    </td>
                    <td class="text-end">
                      <a href="recipe-details.php?id=<?= (int)$recipe['id'] ?>" class="btn btn-sm btn-outline-dark me-1" title="View Recipe">
                        <i class="bi bi-eye"></i> View
                      </a>
                      <a href="edit-recipe.php?id=<?= (int)$recipe['id'] ?>" class="btn btn-sm btn-outline-primary me-1" title="Edit Recipe">
                        <i class="bi bi-pencil"></i> Edit
                      </a>
                      <form action="dashboard.php" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this recipe?');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="recipe_id" value="<?= (int)$recipe['id'] ?>">
                        <input type="hidden" name="redirect_tab" value="list">
                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Recipe">
                          <i class="bi bi-trash"></i> Delete
                        </button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div class="text-center py-5">
            <i class="bi bi-journal-plus display-4 text-muted mb-3 d-block"></i>
            <h5>You haven't submitted any recipes yet</h5>
            <p class="text-muted small">Share your cooking creativity by adding your first recipe!</p>
            <button class="btn btn-dark mt-2" onclick="document.getElementById('pills-add-tab').click();">
              <i class="bi bi-plus-circle me-1"></i> Add Your First Recipe
            </button>
          </div>
        <?php endif; ?>
      </div>

      <!-- ============================================================ -->
      <!-- TAB 5: ADD NEW RECIPE FORM                                   -->
      <!-- ============================================================ -->
      <div class="tab-pane fade <?= $active_tab === 'add' ? 'show active' : '' ?>" id="pills-add" role="tabpanel">
        <h4 class="mb-3">Submit a New Recipe</h4>

        <?php if (!is_admin()): ?>
          <div class="alert alert-info py-2 small mb-3">
            <i class="bi bi-info-circle me-1"></i>
            <strong>Notice:</strong> Your recipe will be submitted to the site administrator for approval before it appears in public searches.
          </div>
        <?php endif; ?>

        <?php if (!empty($errors['general'])): ?>
          <div class="alert alert-danger"><?= htmlspecialchars($errors['general']) ?></div>
        <?php endif; ?>

        <form action="dashboard.php" method="POST" novalidate>
          <input type="hidden" name="action" value="create">

          <div class="row g-3">
            <!-- Title -->
            <div class="col-md-8">
              <label class="form-label-custom" for="rec-title">Recipe Title *</label>
              <input type="text"
                     name="title"
                     id="rec-title"
                     class="form-control-custom <?= isset($errors['title']) ? 'is-invalid' : '' ?>"
                     placeholder="e.g. Creamy Tuscan Garlic Chicken"
                     value="<?= htmlspecialchars($_POST['title'] ?? '') ?>"
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
                  <option value="<?= $cat ?>" <?= (isset($_POST['category']) && $_POST['category'] === $cat) ? 'selected' : '' ?>><?= $cat ?></option>
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
                     placeholder="A brief one-line description to display on recipe cards"
                     value="<?= htmlspecialchars($_POST['description'] ?? '') ?>">
            </div>

            <!-- Image URL -->
            <div class="col-12">
              <label class="form-label-custom" for="rec-image">Image URL (Unsplash or web photo link)</label>
              <input type="url"
                     name="image_url"
                     id="rec-image"
                     class="form-control-custom"
                     placeholder="https://images.unsplash.com/photo-..."
                     value="<?= htmlspecialchars($_POST['image_url'] ?? '') ?>">
            </div>

            <!-- Prep Time, Cook Time, Servings, Difficulty -->
            <div class="col-md-3">
              <label class="form-label-custom" for="rec-prep">Prep Time</label>
              <input type="text" name="prep_time" id="rec-prep" class="form-control-custom" placeholder="e.g. 15 Min" value="<?= htmlspecialchars($_POST['prep_time'] ?? '15 Min') ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label-custom" for="rec-cook">Cook Time</label>
              <input type="text" name="cook_time" id="rec-cook" class="form-control-custom" placeholder="e.g. 25 Min" value="<?= htmlspecialchars($_POST['cook_time'] ?? '25 Min') ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label-custom" for="rec-servings">Servings</label>
              <input type="text" name="servings" id="rec-servings" class="form-control-custom" placeholder="e.g. 4" value="<?= htmlspecialchars($_POST['servings'] ?? '4') ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label-custom" for="rec-diff">Difficulty</label>
              <select name="difficulty" id="rec-diff" class="form-control-custom">
                <option value="Easy" <?= (isset($_POST['difficulty']) && $_POST['difficulty'] === 'Easy') ? 'selected' : '' ?>>Easy</option>
                <option value="Medium" <?= (isset($_POST['difficulty']) && $_POST['difficulty'] === 'Medium') ? 'selected' : '' ?>>Medium</option>
                <option value="Hard" <?= (isset($_POST['difficulty']) && $_POST['difficulty'] === 'Hard') ? 'selected' : '' ?>>Hard</option>
              </select>
            </div>

            <!-- Ingredients -->
            <div class="col-md-6">
              <label class="form-label-custom" for="rec-ingredients">Ingredients * (One per line)</label>
              <textarea name="ingredients"
                        id="rec-ingredients"
                        rows="7"
                        class="form-control-custom <?= isset($errors['ingredients']) ? 'is-invalid' : '' ?>"
                        placeholder="2 cups all-purpose flour&#10;1 tsp baking powder&#10;2 large eggs&#10;1 cup whole milk"
                        required><?= htmlspecialchars($_POST['ingredients'] ?? '') ?></textarea>
              <?php if (isset($errors['ingredients'])): ?>
                <div class="form-feedback error d-block"><?= htmlspecialchars($errors['ingredients']) ?></div>
              <?php endif; ?>
            </div>

            <!-- Instructions -->
            <div class="col-md-6">
              <label class="form-label-custom" for="rec-instructions">Instructions * (Step-by-step)</label>
              <textarea name="instructions"
                        id="rec-instructions"
                        rows="7"
                        class="form-control-custom <?= isset($errors['instructions']) ? 'is-invalid' : '' ?>"
                        placeholder="1. In a large mixing bowl, combine the dry ingredients.&#10;2. Whisk wet ingredients in a separate bowl.&#10;3. Fold wet ingredients into dry and cook on griddle."
                        required><?= htmlspecialchars($_POST['instructions'] ?? '') ?></textarea>
              <?php if (isset($errors['instructions'])): ?>
                <div class="form-feedback error d-block"><?= htmlspecialchars($errors['instructions']) ?></div>
              <?php endif; ?>
            </div>

            <div class="col-12 mt-4">
              <button type="submit" class="btn-send px-4 py-2">
                <i class="bi bi-cloud-upload me-1"></i> Submit Recipe
              </button>
              <button type="reset" class="btn btn-outline-secondary ms-2 px-3 py-2" style="border-radius:10px;">
                Reset
              </button>
            </div>

          </div>
        </form>
      </div>

    </div>
  </div>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
