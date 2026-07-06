<?php

session_start();

// Remove all session variables
$_SESSION = [];

// Destroy the session
session_destroy();

// Prevent browser from caching logged-in pages
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");

// Redirect to homepage
header("Location: index.php");
exit();

?>