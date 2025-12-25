<?php

include("../vendor/autoload.php");

use Libs\Database\Mysql;
use Libs\Database\UsersTable;

// Set content type to JSON
header('Content-Type: application/json');


// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "Method Not Allowed";
    http_response_code(405);
    exit;
}

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

// Basic validation
if (!$email || !$password) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Missing email or password']);
    exit;
}

try {
    $table = new UsersTable(new Mysql());
    $user = $table->findByEmailAndPassword($email, $password);
    
    if ($user) {
        http_response_code(200); // OK
        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'address' => $user->address
                ]
            ]
        ]);
    } else {
        http_response_code(401); // Unauthorized
        echo json_encode(['success' => false, 'message' => 'Login failed', 'error' => 'Incorrect email or password']);
    }

} catch (Exception $e) {
    http_response_code(500);
    error_log("Database Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Login failed', 'error' => 'Database error: ' . $e->getMessage()]);
}

