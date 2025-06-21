<?php
/**
 * Simple PHP redirect script
 * Redirects any direct PHP file access to its HTML equivalent (except for API endpoints)
 */

// Check if this is not an API endpoint (simplest possible check)
if (strpos($_SERVER['REQUEST_URI'], '/api/') === false) {
    // Get current file name without extension and redirect to HTML version
    $html_file = basename($_SERVER['SCRIPT_NAME'], '.php') . '.html';
    header("Location: $html_file");
    exit;
}
?>
