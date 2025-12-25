<?php

include("../vendor/autoload.php");

use Libs\Database\Mysql;
use Libs\Database\UsersTable;

header('Content-Type: application/json');

// Check if request method is POST or GET
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Get user profile (which is part of user record now)
    $user_id = $_GET['user_id'] ?? null;
    
    if (!$user_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Bad request', 'error' => 'Missing user_id']);
        exit;
    }
    
    try {
        $table = new UsersTable(new Mysql());
        $user = $table->getById($user_id);
        
        if ($user) {
            $age = null;
            if ($user->date_of_birth) {
                $dob = new DateTime($user->date_of_birth);
                $now = new DateTime();
                $age = $now->diff($dob)->y;
            }

            $profileData = [
                'user_id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'phone' => $user->phone,
                'height' => $user->height,
                'current_weight' => $user->current_weight,
                'date_of_birth' => $user->date_of_birth,
                'age' => $age,
                'gender' => $user->gender,
                'photo' => $user->photo,
                'created_at' => $user->created_at
            ];

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Profile retrieved successfully',
                'data' => [
                    'profile' => $profileData
                ]
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Not found', 'error' => 'User not found']);
        }
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error', 'error' => 'Database error: ' . $e->getMessage()]);
    }
    
} elseif ($method === 'POST') {
    // Update profile
    $user_id = $_POST['user_id'] ?? null;
    $phone = $_POST['phone'] ?? null;
    $height = $_POST['height'] ?? null;
    $current_weight = $_POST['current_weight'] ?? null;
    $date_of_birth = $_POST['date_of_birth'] ?? null;
    $gender = $_POST['gender'] ?? null;
    
    if (!$user_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Bad request', 'error' => 'Missing user_id']);
        exit;
    }
    
    try {
        $table = new UsersTable(new Mysql());
        $user = $table->getById($user_id);
        
        if (!$user) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Not found', 'error' => 'User not found']);
            exit;
        }

        $data = [
            'phone' => $phone,
            'height' => $height,
            'current_weight' => $current_weight,
            'date_of_birth' => $date_of_birth,
            'gender' => $gender
        ];
        
        $table->updateProfile($user_id, $data);
        
        // Fetch updated user to return
        $updatedUser = $table->getById($user_id);
        
        // Calculate age
        $age = null;
        if ($updatedUser->date_of_birth) {
            $dob = new DateTime($updatedUser->date_of_birth);
            $now = new DateTime();
            $age = $now->diff($dob)->y;
        }

        $profileData = [
            'user_id' => $updatedUser->id,
            'username' => $updatedUser->username,
            'email' => $updatedUser->email,
            'phone' => $updatedUser->phone,
            'height' => $updatedUser->height,
            'current_weight' => $updatedUser->current_weight,
            'date_of_birth' => $updatedUser->date_of_birth,
            'age' => $age,
            'gender' => $updatedUser->gender,
            'photo' => $updatedUser->photo
        ];

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => [
                'profile' => $profileData
            ]
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error', 'error' => 'Database error: ' . $e->getMessage()]);
    }
    
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed', 'error' => 'Method not allowed']);
}
