<?php
/**
 * API Endpoint for user logout
 * Handles server-side session termination and cookie removal
 */
require_once '../includes/config.php';
header('Content-Type: application/json');

// Set up CORS headers if needed
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Process logout
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Start session if not started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Clear session data
    session_unset();
    session_destroy();
    
    // Clear cookies
    if (isset($_COOKIE['auth_token'])) {
        setcookie('auth_token', '', time() - 3600, '/');
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Logout successful'
    ]);
} else {
    // Return error for any other method
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
}
