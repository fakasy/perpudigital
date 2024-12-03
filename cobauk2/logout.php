<?php
session_start();

// Clear session variables
$_SESSION['session_username'] = "";
$_SESSION["session_password"] = "";

// Destroy the session
session_destroy();

// Redirect to the login page
header("Location: login.php");
exit(); // Make sure to exit after the redirect
?>
