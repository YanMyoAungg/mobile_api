<?php

include("../vendor/autoload.php");

use Libs\Database\Mysql;
use Libs\Database\ActivityTable;

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Get activities
    $user_id = $_GET['user_id'] ?? $_GET['userId'] ?? null;
    $activity_id = $_GET['activity_id'] ?? null;
    $activity_type = $_GET['activity_type'] ?? null;
    $start_date = $_GET['start_date'] ?? null;
    $end_date = $_GET['end_date'] ?? null;
    $get_stats = $_GET['stats'] ?? false;
    
    if (!$user_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Bad request', 'error' => 'Missing user_id']);
        exit;
    }
    
    try {
        $table = new ActivityTable(new Mysql());
        
        if ($get_stats) {
            // Get statistics
            $stats = $table->getStats($user_id, $start_date, $end_date);
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Statistics retrieved successfully',
                'data' => ['stats' => $stats]
            ]);
        } elseif ($activity_id) {
            // Get specific activity
            $activity = $table->getById($activity_id, $user_id);
            if ($activity) {
                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'message' => 'Activity retrieved successfully',
                    'data' => ['activity' => $activity]
                ]);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Not found', 'error' => 'Activity not found']);
            }
        } elseif ($activity_type) {
            // Get by type
            $activities = $table->getByType($user_id, $activity_type);
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Activities retrieved successfully',
                'data' => [
                    'count' => count($activities),
                    'activities' => $activities
                ]
            ]);
        } elseif ($start_date && $end_date) {
            // Get by date range
            // Ensure strictly start <= end for MySQL BETWEEN
            if ($start_date > $end_date) {
                $temp = $start_date;
                $start_date = $end_date;
                $end_date = $temp;
            }
            $activities = $table->getByDateRange($user_id, $start_date, $end_date);
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Activities retrieved successfully',
                'data' => [
                    'count' => count($activities),
                    'activities' => $activities
                ]
            ]);
        } else {
            // Get all activities
            $limit = $_GET['limit'] ?? 100;
            $offset = $_GET['offset'] ?? 0;
            $activities = $table->getAllByUserId($user_id, $limit, $offset);
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Activities retrieved successfully',
                'data' => [
                    'count' => count($activities),
                    'activities' => $activities
                ]
            ]);
        }
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error', 'error' => 'Database error: ' . $e->getMessage()]);
    }
    
} elseif ($method === 'POST') {
    // Create new activity
    $user_id = $_POST['user_id'] ?? $_POST['userId'] ?? null;
    $activity_type = $_POST['activity_type'] ?? null;
    $duration = $_POST['duration'] ?? null;
    
    if (!$user_id || !$activity_type || !$duration) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Bad request', 'error' => 'Missing required fields: user_id, activity_type, duration']);
        exit;
    }
    
    try {
        $data = [
            'user_id' => $user_id,
            'activity_type' => $activity_type,
            'duration' => $duration,
            'calories_burned' => $_POST['calories_burned'] ?? 0,
            'activity_date' => $_POST['activity_date'] ?? date('Y-m-d H:i:s')
        ];
        
        $table = new ActivityTable(new Mysql());
        $id = $table->create($data);
        
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Activity created successfully',
            'data' => [
                'activity_id' => $id,
                'activity' => $table->getById($id, $user_id)
            ]
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error', 'error' => 'Database error: ' . $e->getMessage()]);
    }
    
} elseif ($method === 'PUT') {
    // Update activity
    parse_str(file_get_contents("php://input"), $_PUT);
    
    $activity_id = $_PUT['activity_id'] ?? null;
    $user_id = $_PUT['user_id'] ?? $_PUT['userId'] ?? null;
    
    if (!$activity_id || !$user_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Bad request', 'error' => 'Missing activity_id or user_id']);
        exit;
    }
    
    try {
        $data = [
            'activity_type' => $_PUT['activity_type'],
            'duration' => $_PUT['duration'],
            'calories_burned' => $_PUT['calories_burned'] ?? 0,
            'activity_date' => $_PUT['activity_date']
        ];
        
        $table = new ActivityTable(new Mysql());
        $result = $table->update($activity_id, $user_id, $data);
        
        if ($result > 0) {
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Activity updated successfully',
                'data' => [
                    'activity' => $table->getById($activity_id, $user_id)
                ]
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Not found', 'error' => 'Activity not found']);
        }
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error', 'error' => 'Database error: ' . $e->getMessage()]);
    }
    
} elseif ($method === 'DELETE') {
    // Delete activity
    parse_str(file_get_contents("php://input"), $_DELETE);
    
    $activity_id = $_DELETE['activity_id'] ?? null;
    $user_id = $_DELETE['user_id'] ?? $_DELETE['userId'] ?? null;
    
    if (!$activity_id || !$user_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Bad request', 'error' => 'Missing activity_id or user_id']);
        exit;
    }
    
    try {
        $table = new ActivityTable(new Mysql());
        $result = $table->delete($activity_id, $user_id);
        
        if ($result > 0) {
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Activity deleted successfully',
                'data' => []
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Not found', 'error' => 'Activity not found']);
        }
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error', 'error' => 'Database error: ' . $e->getMessage()]);
    }
    
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed', 'error' => 'Method not allowed']);
}
