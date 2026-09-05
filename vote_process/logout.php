<?php
// logout.php - User logout page
// Destroys session and logs out the current user

// Start session to access session variables
session_start();

// Destroy all session data
// unset() removes specific variables, but session_destroy() completely removes the session
session_destroy();

// Redirect to login page after logout
header("Location: login.php");
exit();
?>
