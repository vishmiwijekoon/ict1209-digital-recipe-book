<?php
// ============================================================
// Contact Page
// Project: Diary of Taste - Digital Recipe Book
// Requirement: Store submissions in the messages table
// ============================================================

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$errors = [];
$name = '';
$email = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    // Server-side validation
    if (empty($name)) {
        $errors['name'] = 'Please enter your name.';
    }

    if (empty($email)) {
        $errors['email'] = 'Please enter your email address.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }

    if (empty($message)) {
        $errors['message'] = 'Please enter your message.';
    } elseif (strlen($message) < 5) {
        $errors['message'] = 'Message must be at least 5 characters long.';
    }

    // Process submission if no errors
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO messages (name, email, message, created_at)
                VALUES (:name, :email, :message, NOW())
            ");
            $stmt->execute([
                ':name'    => $name,
                ':email'   => $email,
                ':message' => $message
            ]);

            set_flash('success', 'Thank you, ' . htmlspecialchars($name) . '! Your message has been sent successfully. We will get back to you soon.');
            redirect('contact.php');
        } catch (PDOException $e) {
            $errors['general'] = 'Failed to submit your message due to a database error. Please try again.';
        }
    }
}

$page_title = 'Contact Us';
$active_page = 'contact';
$base_path = '';

require_once __DIR__ . '/includes/header.php';
?>

<main class="container my-5">

  <!-- HERO BAND -->
  <section class="contact-hero mb-5">
    <h1>Contact Us</h1>
    <p>Have questions, recipe suggestions, or collaboration ideas? We'd love to hear from you.</p>
    <p class="text-muted small">Fill out the form below or reach out via our contact details.</p>
  </section>

  <!-- FORM + CONTACT INFO -->
  <div class="row g-4 mb-5">

    <div class="col-lg-8">
      <div class="contact-panel shadow-sm">
        <h2>Send a Message</h2>

        <?php if (!empty($errors['general'])): ?>
          <div class="alert alert-danger"><?= htmlspecialchars($errors['general']) ?></div>
        <?php endif; ?>

        <form id="contact-form" action="contact.php" method="POST" novalidate>

          <div class="mb-3">
            <label class="form-label-custom" for="contact-name">Name</label>
            <input type="text"
                   name="name"
                   id="contact-name"
                   class="form-control-custom <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
                   placeholder="Enter your full name"
                   value="<?= htmlspecialchars($name) ?>"
                   required>
            <div class="form-feedback <?= isset($errors['name']) ? 'error d-block' : '' ?>" id="name-feedback">
              <?= $errors['name'] ?? '' ?>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label-custom" for="contact-email">Email</label>
            <input type="email"
                   name="email"
                   id="contact-email"
                   class="form-control-custom <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                   placeholder="Enter your email address"
                   value="<?= htmlspecialchars($email) ?>"
                   required>
            <div class="form-feedback <?= isset($errors['email']) ? 'error d-block' : '' ?>" id="email-feedback">
              <?= $errors['email'] ?? '' ?>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label-custom" for="contact-message">Message</label>
            <textarea name="message"
                      id="contact-message"
                      class="form-control-custom <?= isset($errors['message']) ? 'is-invalid' : '' ?>"
                      placeholder="Write your message here..."
                      rows="5"
                      required><?= htmlspecialchars($message) ?></textarea>
            <div class="form-feedback <?= isset($errors['message']) ? 'error d-block' : '' ?>" id="message-feedback">
              <?= $errors['message'] ?? '' ?>
            </div>
          </div>

          <button type="submit" class="btn-send" id="contact-submit">
            <i class="bi bi-send me-1"></i> Send Message
          </button>
          <div class="form-feedback mt-2" id="form-status" role="status" aria-live="polite"></div>
        </form>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="contact-info-card shadow-sm">
        <h2>Contact Information</h2>

        <div class="contact-info-item">
          <span class="contact-info-icon"><i class="bi bi-envelope"></i></span>
          <div>
            <h6>Email</h6>
            <p>hello@diaryoftaste.com</p>
          </div>
        </div>

        <div class="contact-info-item">
          <span class="contact-info-icon"><i class="bi bi-telephone"></i></span>
          <div>
            <h6>Phone</h6>
            <p>+94 715 655 2174</p>
          </div>
        </div>

        <div class="contact-info-item">
          <span class="contact-info-icon"><i class="bi bi-geo-alt"></i></span>
          <div>
            <h6>Location</h6>
            <p>Department of ICT, Rajarata University of Sri Lanka, Mihinthale</p>
          </div>
        </div>

        <div class="contact-info-item">
          <span class="contact-info-icon"><i class="bi bi-clock"></i></span>
          <div>
            <h6>Operating Hours</h6>
            <p>Mon – Fri: 9:00 AM – 5:00 PM</p>
          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- COMMON QUESTIONS -->
  <section class="faq-panel shadow-sm">
    <div class="section-heading">
      <h2>Common Questions</h2>
    </div>
    <div class="row g-3">

      <div class="col-md-4">
        <div class="faq-item">
          <button class="faq-question" type="button" data-bs-toggle="collapse" data-bs-target="#faq1" aria-expanded="true" aria-controls="faq1">
            How can I submit a recipe?
            <i class="bi bi-chevron-down"></i>
          </button>
          <div class="collapse show" id="faq1">
            <p class="faq-answer">Simply create an account or log in, then navigate to your <strong>Dashboard</strong> to add, publish, and manage your recipes.</p>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="faq-item">
          <button class="faq-question" type="button" data-bs-toggle="collapse" data-bs-target="#faq2" aria-expanded="false" aria-controls="faq2">
            Do you cater for allergies?
            <i class="bi bi-chevron-down"></i>
          </button>
          <div class="collapse" id="faq2">
            <p class="faq-answer">Most recipes list common allergens, and many include suggested substitutions for dairy, gluten, and nuts.</p>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="faq-item">
          <button class="faq-question" type="button" data-bs-toggle="collapse" data-bs-target="#faq3" aria-expanded="false" aria-controls="faq3">
            Can I print recipes?
            <i class="bi bi-chevron-down"></i>
          </button>
          <div class="collapse" id="faq3">
            <p class="faq-answer">Yes! Every recipe detail page features a "Print Recipe" button formatted cleanly for physical paper or PDF export.</p>
          </div>
        </div>
      </div>

    </div>
  </section>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
