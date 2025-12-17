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

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

// Basic validation
if (!$username || !$password) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Missing username or password']);
    exit;
}

try {
    $table = new UsersTable(new Mysql());
    $user = $table->findByUsernameAndPassword($username, $password);
    
    if ($user) {
        http_response_code(200); // OK
        echo json_encode([
            'message' => 'Login successful',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'address' => $user->address,
                'role_id' => $user->role_id
            ]
        ]);
    } else {
        http_response_code(401); // Unauthorized
        echo json_encode(['error' => 'Incorrect username or password']);
    }

} catch (Exception $e) {
    http_response_code(500);
    error_log("Database Error: " . $e->getMessage());
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}

