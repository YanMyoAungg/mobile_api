<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Load .env
use Helpers\DotenvLoader;
DotenvLoader::load(__DIR__ . '/../');

$host = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: 'localhost';
$user = $_ENV['DB_USER'] ?? getenv('DB_USER') ?: 'root';
$pass = $_ENV['DB_PASS'] ?? getenv('DB_PASS') ?: '';
$dbname = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'fitness';

try {
    $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    
    // Disable FK checks
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');

    $tables = [
        'users', 
        'roles', 
        'user_profiles', 
        'activities', 
        'daily_steps', 
        'weight_records', 
        'meals', 
        'water_intake', 
        'fitness_goals', 
        'achievements',
        'sleep_records',
    ];

    foreach ($tables as $t) {
        $pdo->exec("DROP TABLE IF EXISTS `$t`");
        echo "Dropped table $t\n";
    }

    $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
    echo "All tables dropped successfully.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
