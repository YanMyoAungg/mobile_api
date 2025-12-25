<?php

include("../vendor/autoload.php");

use Libs\Database\Mysql;
use Libs\Database\WeeklyGoalTable;
use Libs\Database\ActivityTable;

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    // Create or update weekly goal
    $user_id = $_POST['user_id'] ?? $_POST['userId'] ?? null;
    $target_calories = $_POST['target_calories'] ?? null;

    if (!$user_id || !$target_calories || !is_numeric($target_calories) || $target_calories <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Bad request', 'error' => 'Missing or invalid user_id or target_calories']);
        exit;
    }

    try {
        $table = new WeeklyGoalTable(new Mysql());
        $table->createOrUpdate($user_id, $target_calories);

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Weekly goal set successfully',
            'data' => [
                'target_calories' => (int)$target_calories
            ]
        ]);

    } catch (Exception $e) {
        http_response_code(500);    
        echo json_encode(['success' => false, 'message' => 'Database error', 'error' => $e->getMessage()]);
    }

} elseif ($method === 'GET') {
    // Get current goal and progress
    $user_id = $_GET['user_id'] ?? $_GET['userId'] ?? null;

    if (!$user_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Bad request', 'error' => 'Missing user_id']);
        exit;
    }

    try {
        $goalTable = new WeeklyGoalTable(new Mysql());
        $goal = $goalTable->getByUserId($user_id);

        if (!$goal) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'No weekly goal set', 'data' => null]);
            exit;
        }

        $target_calories = (int)$goal->target_calories;

        // Calculate progress for current week (Monday to Sunday)
        $start_date = date('Y-m-d', strtotime('monday this week'));
        $end_date = date('Y-m-d', strtotime('sunday this week'));

        $activityTable = new ActivityTable(new Mysql());
        $stats = $activityTable->getStats($user_id, $start_date, $end_date);

        $current_calories = (int)($stats->total_calories ?? 0);
        
        $progress_percent = 0;
        if ($target_calories > 0) {
            $progress_percent = ($current_calories / $target_calories) * 100;
        }
        
        echo json_encode([
            "success" => true,
            "message" => "Weekly goal and progress retrieved successfully",
            "data" => [
                "target_calories" => $target_calories,
                "current_calories" => $current_calories,
                "progress_percent" => round($progress_percent, 1)
            ]
        ]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error', 'error' => $e->getMessage()]);
    }

} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
