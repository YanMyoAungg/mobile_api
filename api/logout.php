<?php

header('Content-Type: application/json');

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "Method Not Allowed";
    http_response_code(405);
    exit;
}

try {
    // In a real application, you might want to:
    // - Invalidate a token stored in a database
    // - Clear any session data
    // - Log the logout event
    
    http_response_code(200); // OK
    echo json_encode([
        'success' => true,
        'message' => 'Logout successful',
        'data' => []
    ]);

} catch (Exception $e) {
    http_response_code(500);
    error_log("Logout Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Logout failed', 'error' => $e->getMessage()]);
}
