<?php

/**
 * DB management script for this project.
 *
 * Usage:
 *  php scripts/manage_db.php create   # create database and tables (idempotent)
 *  php scripts/manage_db.php reset    # reset tables (truncate + reseed roles)
 *  php scripts/manage_db.php status   # show status
 *
 * Environment variables (optional): DB_HOST, DB_USER, DB_PASS, DB_NAME
 */

$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: 'zenith172421';
$dbname = getenv('DB_NAME') ?: 'project';

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
    // roles table
    if (!tableExists($pdo, $dbname, 'roles')) {
        $sql = "CREATE TABLE roles (
          id INT AUTO_INCREMENT PRIMARY KEY,
          name VARCHAR(100),
          value VARCHAR(50)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $pdo->exec($sql);
        $created[] = 'roles';
    }

    // users table
    if (!tableExists($pdo, $dbname, 'users')) {
        $sql = "CREATE TABLE users (
          id INT AUTO_INCREMENT PRIMARY KEY,
          name VARCHAR(255),
          email VARCHAR(255) UNIQUE,
          phone VARCHAR(100),
          address TEXT,
          password VARCHAR(255),
          role_id INT,
          photo VARCHAR(255) DEFAULT NULL,
          suspended TINYINT(1) DEFAULT 0,
          created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
          updated_at DATETIME DEFAULT NULL,
          FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $pdo->exec($sql);
        $created[] = 'users';
    }

    return $created;
}

function seedRoles($pdo, $dbname)
{
    // insert default roles if table empty
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM roles');
    $stmt->execute();
    $count = (int) $stmt->fetchColumn();
    if ($count === 0) {
        $ins = $pdo->prepare('INSERT INTO roles (id, name, value) VALUES (:id, :name, :value)');
        $defaults = [
            ['id' => 1, 'name' => 'User', 'value' => '11'],
            ['id' => 2, 'name' => 'Manager', 'value' => '22'],
            ['id' => 3, 'name' => 'Admin', 'value' => '33'],
        ];
        foreach ($defaults as $r) {
            $ins->execute($r);
        }
        return true;
    }
    return false;
}

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

    $seeded = seedRoles($pdoDb, $dbname);
    if ($seeded) {
        echo "Inserted default roles.\n";
    } else {
        echo "Roles table already has data; skipping seed.\n";
    }

    exit(0);
} elseif ($action === 'reset') {
    // truncate tables (if exist) and reseed roles
    $pdo = connectPDO($host, $user, $pass, true, $dbname);
    if (!dbExists(connectPDO($host, $user, $pass, false), $dbname)) {
        echo "Database '$dbname' does not exist. Nothing to reset.\n";
        exit(1);
    }

    $tables = ['users', 'roles'];
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

    // reseed roles defaults
    $seeded = seedRoles($pdo, $dbname);
    if ($seeded) {
        echo "Re-seeded default roles.\n";
    } else {
        echo "Roles table already has data after reset (unexpected).\n";
    }

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
