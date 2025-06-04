
<?php
session_start();

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (isset($input['action']) && $input['action'] == 'clear_completed_reservation') {
        // Clear the completed reservation from session
        unset($_SESSION['completed_reservation']);
        
        http_response_code(200);
        echo json_encode(['status' => 'success', 'message' => 'Session cleared']);
    } else {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
    }
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
}
?>