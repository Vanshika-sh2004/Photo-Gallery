<?php
session_start();

// Unset all session variables
$_SESSION = [];

// Destroy the session
session_unset();
session_destroy();

// Delete the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 3600,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Prevent back-button showing old data
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

// Redirect to index (or login page)
header("Location: index.php");
exit();
?>
