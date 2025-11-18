<?php
/**
 * Core utility functions for Virtual Kitchen
 */

// Sanitize user input
function sanitize_input($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Generate CSRF token
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Validate CSRF token
function validate_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && 
           hash_equals($_SESSION['csrf_token'], $token);
}

// Redirect with optional status code
function redirect($url, $statusCode = 303) {
    header('Location: ' . $url, true, $statusCode);
    exit();
}

// Check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Get current user ID
function current_user_id() {
    return $_SESSION['user_id'] ?? null;
}

// Format cooking time
function format_cooking_time($minutes) {
    if ($minutes < 60) {
        return $minutes . ' mins';
    }
    $hours = floor($minutes / 60);
    $mins = $minutes % 60;
    return $hours . ' hr' . ($hours > 1 ? 's' : '') . 
           ($mins > 0 ? ' ' . $mins . ' min' . ($mins > 1 ? 's' : '') : '');
}

// Display error messages
function display_errors($errors) {
    if (!empty($errors)) {
        echo '<div class="alert alert-danger"><ul>';
        foreach ($errors as $error) {
            echo '<li>' . htmlspecialchars($error) . '</li>';
        }
        echo '</ul></div>';
    }
}

// Safe file upload handler
function handle_file_upload($field, $target_dir) {
    // Implementation for image uploads
}