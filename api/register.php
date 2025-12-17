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

$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$phone = $_POST['phone'] ?? '';
$address = $_POST['address'] ?? '';
$password = $_POST['password'] ?? '';

// Basic validation
if (!$name || !$email || !$password) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Missing required fields (name, email, password)']);
    exit;
}
echo "name". $name;
echo "email". $email;
echo "phone". $phone;
echo "address". $address;
echo "password". $password;
$data = [
    'name' => $name,
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
            'message' => 'User registered successfully',
            'user_id' => $id
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to register user']);
    }

} catch (Exception $e) {
    http_response_code(500);
    // In a real production app, handle specific SQL errors (like duplicate email) more gracefully
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
