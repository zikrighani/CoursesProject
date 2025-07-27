<?php 
// Start session to access session variables 
session_start(); 

// Clear all session variables 
session_unset(); 

// Destroy the session 
session_destroy(); 

// Delete the session cookie 
if (isset($_COOKIE[session_name()])) { 
   setcookie(session_name(), '', time() - 3600, '/'); 
} 

// Set cache control headers to prevent browser caching 
header("Cache-Control: no-cache, no-store, must-revalidate, max-age=0"); 
header("Pragma: no-cache"); 
header("Expires: Thu, 01 Jan 1970 00:00:00 GMT"); 

// Additional security headers 
header("Clear-Site-Data: \"cache\", \"cookies\", \"storage\""); 

// Redirect to login page with logout success message
header("Location: login.php?logout=success"); 
exit(); 
?>