<?php
/**
 * Redirects to the HTML version of the conference detail page
 * Part of the refactoring to separate HTML (UI) and PHP (API)
 */

// Get conference ID from URL parameters
$conferenceId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Build the redirect URL
$redirectUrl = 'conference-detail.html';
if ($conferenceId > 0) {
    $redirectUrl .= '?id=' . $conferenceId;
} else {
    // If no ID provided, redirect to conferences list
    $redirectUrl = 'conferences.html';
}

// Redirect to HTML version
header('Location: ' . $redirectUrl);
exit;
?>
