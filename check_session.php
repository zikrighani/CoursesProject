<?php
// Universal Session Checker - works for both admin and user roles
header('Content-Type: application/json');
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

session_start();

$response = array();

// Check if user is logged in
if (!isset($_SESSION['userID']) || empty($_SESSION['userID'])) {
    $response['valid'] = false;
    $response['message'] = 'Not logged in';
    echo json_encode($response);
    exit();
}

// Check if role is set
if (!isset($_SESSION['role']) || empty($_SESSION['role'])) {
    $response['valid'] = false;
    $response['message'] = 'No role assigned';
    echo json_encode($response);
    exit();
}

// Validate session security
$valid_session = true;

// Check login time (24-hour timeout)
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > 86400)) {
    $valid_session = false;
}

// Check user agent (prevent session hijacking)
if (isset($_SESSION['user_agent']) && $_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
    $valid_session = false;
}

// Optional: Check IP address (uncomment if needed, but may cause issues with dynamic IPs)
/*
if (isset($_SESSION['user_ip']) && $_SESSION['user_ip'] !== $_SERVER['REMOTE_ADDR']) {
    $valid_session = false;
}
*/

if ($valid_session) {
    $response['valid'] = true;
    $response['role'] = $_SESSION['role'];
    $response['userID'] = $_SESSION['userID'];
    $response['message'] = 'Session valid';
} else {
    // Invalid session - destroy it
    session_unset();
    session_destroy();
    
    $response['valid'] = false;
    $response['message'] = 'Session expired or invalid';
}

echo json_encode($response);
exit();
?>