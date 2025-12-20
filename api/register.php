<?php

include("../vendor/autoload.php");

use Libs\Database\Mysql;
use Libs\Database\UsersTable;

// Set content type to JSON
header('Content-Type: application/json');

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

$username = $_POST['username'] ?? '';
$email = $_POST['email'] ?? '';
$phone = $_POST['phone'] ?? '';
$address = $_POST['address'] ?? '';
$password = $_POST['password'] ?? '';

// Basic validation
if (!$username || !$email || !$password) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'Validation error', 'error' => 'Missing required fields (username, email, password)']);
    exit;
}
$data = [
    'username' => $username,
    'email' => $email,
    'phone' => $phone,
    'address' => $address,
    'password' => $password,
];

try {
    $table = new UsersTable(new Mysql());
    $id = $table->insert($data);
    
    if ($id) {
        http_response_code(201); // Created
        echo json_encode([
            'success' => true,
            'message' => 'User registered successfully',
            'data' => ['user_id' => $id]
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Registration failed', 'error' => 'Failed to register user']);
    }

} catch (Exception $e) {
    http_response_code(500);
    // In a real production app, handle specific SQL errors (like duplicate email) more gracefully
    echo json_encode(['success' => false, 'message' => 'Registration error', 'error' => 'Database error: ' . $e->getMessage()]);
}
