<?php
session_start();

// Get redirect URL from query parameter
$redirect = 'index.php'; // Default redirect to home page

// Check if there's a redirect in the URL parameters
if (isset($_GET['redirect'])) {
    $redirect = urldecode($_GET['redirect']);
}

// Clear all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to the specified page
header("Location: $redirect");
exit;
?>
