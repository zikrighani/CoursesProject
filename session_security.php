<?php
// Universal Session Security Handler
// Include this file at the top of ALL protected pages

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cache prevention headers - prevents back button access after logout
header("Cache-Control: no-cache, no-store, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['userID']) && !empty($_SESSION['userID']);
}

// Function to check if user is admin
function isAdmin() {
    return isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Function to check if user is regular user
function isUser() {
    return isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'user';
}

// Function to validate session security
function validateSessionSecurity() {
    if (!isLoggedIn()) {
        return false;
    }
    
    // Check if session variables are properly set
    if (!isset($_SESSION['login_time']) || !isset($_SESSION['user_ip']) || !isset($_SESSION['user_agent'])) {
        return false;
    }
    
    // Verify IP address (optional - can be disabled if users have dynamic IPs)
    /*
    if ($_SESSION['user_ip'] !== $_SERVER['REMOTE_ADDR']) {
        return false;
    }
    */
    
    // Verify user agent
    if ($_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
        return false;
    }
    
    // Check session timeout (24 hours)
    if (time() - $_SESSION['login_time'] > 86400) {
        return false;
    }
    
    return true;
}

// Function to require login (redirect if not logged in)
function requireLogin($redirect_to = 'login.php') {
    if (!isLoggedIn() || !validateSessionSecurity()) {
        // Destroy invalid session
        session_unset();
        session_destroy();
        
        // Redirect to login
        header("Location: $redirect_to");
        exit();
    }
}

// Function to require admin access
function requireAdmin($redirect_to = 'login.php') {
    requireLogin($redirect_to);
    
    if (!isAdmin()) {
        header("Location: unauthorized.php"); // or back to user dashboard
        exit();
    }
}

// Function to require user access
function requireUser($redirect_to = 'login.php') {
    requireLogin($redirect_to);
    
    if (!isUser()) {
        header("Location: unauthorized.php"); // or back to admin dashboard
        exit();
    }
}

// Function to regenerate session ID periodically for security
function regenerateSessionId() {
    if (!isset($_SESSION['last_regeneration'])) {
        $_SESSION['last_regeneration'] = time();
    } elseif (time() - $_SESSION['last_regeneration'] > 1800) { // Every 30 minutes
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
}

// Auto-regenerate session ID
regenerateSessionId();

// JavaScript for client-side security
function outputSecurityScript() {
    echo '<script>
    // Prevent back button access after logout
    window.history.pushState(null, "", window.location.href);
    window.onpopstate = function() {
        window.history.pushState(null, "", window.location.href);
    };
    
    // Disable right-click context menu on sensitive pages (optional)
    document.addEventListener("contextmenu", function(e) {
        e.preventDefault();
    });
    
    // Disable F12, Ctrl+Shift+I, Ctrl+Shift+J, Ctrl+U (optional - for sensitive admin pages)
    document.addEventListener("keydown", function(e) {
        // F12
        if (e.keyCode === 123) {
            e.preventDefault();
        }
        // Ctrl+Shift+I
        if (e.ctrlKey && e.shiftKey && e.keyCode === 73) {
            e.preventDefault();
        }
        // Ctrl+Shift+J
        if (e.ctrlKey && e.shiftKey && e.keyCode === 74) {
            e.preventDefault();
        }
        // Ctrl+U
        if (e.ctrlKey && e.keyCode === 85) {
            e.preventDefault();
        }
    });
    
    // Check session status periodically
    setInterval(function() {
        fetch("check_session.php")
            .then(response => response.json())
            .then(data => {
                if (!data.valid) {
                    alert("Your session has expired. Please login again.");
                    window.location.href = "login.php";
                }
            })
            .catch(error => {
                console.log("Session check failed");
            });
    }, 300000); // Check every 5 minutes
    </script>';
}
?>