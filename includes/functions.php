<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Check if current user is an administrator
function is_admin() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Enforce login on protected pages
function require_login($login_page = 'auth/login.php') {
    if (!is_logged_in()) {
        header("Location: $login_page");
        exit();
    }
}

// Set a simple alert message
function set_flash($type, $message) {
    $_SESSION['flash_type'] = ($type == 'error') ? 'danger' : $type;
    $_SESSION['flash_msg']  = $message;
}

// Display alert message if exists
function display_flash() {
    if (isset($_SESSION['flash_msg'])) {
        $type = $_SESSION['flash_type'];
        $msg  = htmlspecialchars($_SESSION['flash_msg']);
        unset($_SESSION['flash_type']);
        unset($_SESSION['flash_msg']);

        echo "<div class='alert alert-{$type} alert-dismissible fade show my-3' role='alert'>
                <span>{$msg}</span>
                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
              </div>";
    }
}

// Simple redirect helper
function redirect($url) {
    header("Location: $url");
    exit();
}

// Get featured recipes for homepage (approved and live only)
function get_featured_recipes($pdo, $limit = 3) {
    $stmt = $pdo->prepare("SELECT * FROM recipes WHERE status = 'approved' ORDER BY id DESC LIMIT ?");
    $stmt->bindValue(1, (int)$limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

// Get all recipes for recipes page (hardened secure search, approved and live only)
function get_all_recipes($pdo, $category = null, $search = null) {
    $sql = "SELECT * FROM recipes WHERE status = 'approved'";
    $params = [];

    if (!empty($category) && $category !== 'all') {
        $sql .= " AND category = ?";
        $params[] = $category;
    }

    if (!empty($search)) {
        // Bound search input length to 100 characters to prevent DOS / buffer abuse
        $clean_search = mb_substr(trim($search), 0, 100);
        // Escape SQL LIKE wildcards (% and _) to prevent wildcard search attacks
        $escaped_search = addcslashes($clean_search, '%_');
        $sql .= " AND (title LIKE ? OR description LIKE ? OR ingredients LIKE ?)";
        $term = "%$escaped_search%";
        $params[] = $term;
        $params[] = $term;
        $params[] = $term;
    }

    $sql .= " ORDER BY id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// Get single recipe by ID
function get_recipe_by_id($pdo, $id) {
    $stmt = $pdo->prepare("SELECT r.*, u.username AS author FROM recipes r LEFT JOIN users u ON r.user_id = u.id WHERE r.id = ?");
    $stmt->execute([(int)$id]);
    return $stmt->fetch();
}

// Get related recipes (same category, approved only)
function get_related_recipes($pdo, $category, $exclude_id, $limit = 3) {
    $stmt = $pdo->prepare("SELECT * FROM recipes WHERE category = ? AND id != ? AND status = 'approved' LIMIT ?");
    $stmt->bindValue(1, $category, PDO::PARAM_STR);
    $stmt->bindValue(2, (int)$exclude_id, PDO::PARAM_INT);
    $stmt->bindValue(3, (int)$limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

// Get recipes created by a specific user (shows all their recipes with statuses)
function get_user_recipes($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT * FROM recipes WHERE user_id = ? ORDER BY id DESC");
    $stmt->execute([(int)$user_id]);
    return $stmt->fetchAll();
}

// Get pending recipes awaiting admin review
function get_pending_recipes($pdo) {
    $stmt = $pdo->query("SELECT r.*, u.username AS author FROM recipes r LEFT JOIN users u ON r.user_id = u.id WHERE r.status = 'pending' ORDER BY r.id DESC");
    return $stmt->fetchAll();
}

// Count pending recipes for admin notifications
function count_pending_recipes($pdo) {
    $stmt = $pdo->query("SELECT COUNT(*) FROM recipes WHERE status = 'pending'");
    return (int)$stmt->fetchColumn();
}

// Get all recipes across the system for Admin management (approved, pending, hidden, rejected)
function get_all_system_recipes($pdo) {
    $stmt = $pdo->query("SELECT r.*, u.username AS author FROM recipes r LEFT JOIN users u ON r.user_id = u.id ORDER BY r.id DESC");
    return $stmt->fetchAll();
}

// Count total recipes in system
function count_all_recipes($pdo) {
    $stmt = $pdo->query("SELECT COUNT(*) FROM recipes");
    return (int)$stmt->fetchColumn();
}

// Count approved/published recipes
function count_approved_recipes($pdo) {
    $stmt = $pdo->query("SELECT COUNT(*) FROM recipes WHERE status = 'approved'");
    return (int)$stmt->fetchColumn();
}

// Get all contact messages for admin
function get_all_messages($pdo) {
    $stmt = $pdo->query("SELECT * FROM messages ORDER BY id DESC");
    return $stmt->fetchAll();
}

// Count contact messages
function count_messages($pdo) {
    $stmt = $pdo->query("SELECT COUNT(*) FROM messages");
    return (int)$stmt->fetchColumn();
}