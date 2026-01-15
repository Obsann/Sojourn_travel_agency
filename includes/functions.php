<?php
// includes/functions.php - Helper Functions

session_start();

// Get database connection
function db() {
    require_once __DIR__ . '/../config/database.php';
    return Database::getInstance()->getConnection();
}

// Redirect helper
function redirect($path) {
    header("Location: $path");
    exit;
}

// Check if logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Get current user
function currentUser() {
    if (!isLoggedIn()) return null;
    return [
        'id' => $_SESSION['user_id'],
        'email' => $_SESSION['email'],
        'name' => $_SESSION['name'],
        'role' => $_SESSION['role']
    ];
}

// Require login
function requireLogin() {
    if (!isLoggedIn()) {
        redirect('login.php');
    }
}

// Require specific role
function requireRole($role) {
    requireLogin();
    if ($_SESSION['role'] !== $role) {
        redirect('index.php');
    }
}

// Escape output
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Flash messages
function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
