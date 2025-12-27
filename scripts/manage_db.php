<?php



require_once __DIR__ . '/../vendor/autoload.php';

// Load .env
\Helpers\DotenvLoader::load(__DIR__ . '/../');

$host = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: 'localhost'; 
$user = $_ENV['DB_USER'] ?? getenv('DB_USER') ?: 'root';
$pass = $_ENV['DB_PASS'] ?? getenv('DB_PASS') ?: '';
$dbname = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'fitness';

$action = $argv[1] ?? 'status';

function connectPDO($host, $user, $pass, $useDb = false, $dbname = '')
{
    $dsn = "mysql:host={$host};charset=utf8mb4";
    if ($useDb && $dbname) {
        $dsn .= ";dbname={$dbname}";
    }
    try {
        return new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    } catch (PDOException $e) {
        fwrite(STDERR, "PDO connection failed: " . $e->getMessage() . PHP_EOL);
        exit(1);
    }
}

function dbExists($pdo, $dbname)
{
    $stmt = $pdo->prepare('SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = :db');
    $stmt->execute(['db' => $dbname]);
    return (bool) $stmt->fetchColumn();
}

function tableExists($pdo, $dbname, $table)
{
    $stmt = $pdo->prepare('SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = :db AND TABLE_NAME = :table');
    $stmt->execute(['db' => $dbname, 'table' => $table]);
    return (bool) $stmt->fetchColumn();
}

function createSchema($pdo, $dbname)
{
    $created = [];

    if (!tableExists($pdo, $dbname, 'users')) {
        $sql = "CREATE TABLE users (
          id INT AUTO_INCREMENT PRIMARY KEY,
          username VARCHAR(255),
          email VARCHAR(255) UNIQUE,
          phone VARCHAR(100),
          address TEXT,
          password VARCHAR(255),
          height DECIMAL(5,2) DEFAULT NULL,
          current_weight DECIMAL(5,2) DEFAULT NULL,
          date_of_birth DATE DEFAULT NULL,
          gender ENUM('male', 'female', 'other') DEFAULT NULL,
          photo VARCHAR(255) DEFAULT NULL,
          suspended TINYINT(1) DEFAULT 0,
          created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
          updated_at DATETIME DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $pdo->exec($sql);
        $created[] = 'users';
    }

    if (!tableExists($pdo, $dbname, 'activities')) {
        $sql = "CREATE TABLE activities (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            activity_type ENUM('running', 'walking', 'cycling', 'swimming', 'jumping_rope', 'weight_lifting') NOT NULL,
            duration INT NOT NULL COMMENT 'Duration in minutes',
            calories_burned INT NOT NULL COMMENT 'Calories burned',
            latitude DECIMAL(10, 8) DEFAULT NULL,
            longitude DECIMAL(11, 8) DEFAULT NULL,
            location_name VARCHAR(255) DEFAULT NULL,
            activity_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_user_date (user_id, activity_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $pdo->exec($sql);
        $created[] = 'activities';
    }

    if (!tableExists($pdo, $dbname, 'weekly_goals')) {
        $sql = "CREATE TABLE weekly_goals (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL UNIQUE,
            target_calories INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $pdo->exec($sql);
        $created[] = 'weekly_goals';
    }

    return $created;
}

// Remove seedRoles function completely as roles are no longer used.

if ($action === 'create') {
    // connect without db to create database if needed
    $pdo = connectPDO($host, $user, $pass, false);
    if (dbExists($pdo, $dbname)) {
        echo "Database '$dbname' already exists.\n";
    } else {
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbname}` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
        echo "Database '$dbname' created.\n";
    }

    // connect to the database and create tables if missing
    $pdoDb = connectPDO($host, $user, $pass, true, $dbname);
    $created = createSchema($pdoDb, $dbname);
    if ($created) {
        echo "Created tables: " . implode(', ', $created) . "\n";
    } else {
        echo "All tables already exist.\n";
    }
    exit(0);
} elseif ($action === 'reset') {
    // truncate tables (if exist)
    $pdo = connectPDO($host, $user, $pass, true, $dbname);
    if (!dbExists(connectPDO($host, $user, $pass, false), $dbname)) {
        echo "Database '$dbname' does not exist. Nothing to reset.\n";
        exit(1);
    }

    $tables = ['users', 'activities'];
    try {
        // Disable foreign key checks so we can truncate referenced tables.
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
        foreach ($tables as $t) {
            if (tableExists($pdo, $dbname, $t)) {
                $pdo->exec("TRUNCATE TABLE `{$t}`");
                $pdo->exec("ALTER TABLE `{$t}` AUTO_INCREMENT = 1");
                echo "Truncated and reset '$t'.\n";
            } else {
                echo "Table '$t' does not exist; skipped.\n";
            }
        }
    } finally {
        // Always re-enable foreign key checks
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
    }

    // reseed roles logic removed

    exit(0);
} elseif ($action === 'status') {
    $pdo = connectPDO($host, $user, $pass, false);
    if (dbExists($pdo, $dbname)) {
        echo "Database '$dbname' exists.\n";
        $pdoDb = connectPDO($host, $user, $pass, true, $dbname);
        $tables = ['roles', 'users'];
        foreach ($tables as $t) {
            echo sprintf("Table '%s': %s\n", $t, tableExists($pdoDb, $dbname, $t) ? 'present' : 'missing');
        }
    } else {
        echo "Database '$dbname' does not exist.\n";
    }
    exit(0);
} else {
    fwrite(STDERR, "Unknown action: {$action}\n");
    fwrite(STDERR, "Usage: php scripts/manage_db.php create|reset|status\n");
    exit(1);
}
