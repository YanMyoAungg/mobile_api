<?php

namespace Libs\Database;

use PDO;
use PDOException;
use Helpers\DotenvLoader;
class Mysql
{
    private $db = null;
    private $dbhost;
    private $dbuser;
    private $dbpass;
    private $dbname;

    public function __construct(
        $dbhost = null,
        $dbuser = null,
        $dbpass = null,
        $dbname = null
    ) {
        DotenvLoader::load(__DIR__ . '/../../../');

        $this->dbhost = $dbhost ?: ($_ENV['DB_HOST'] ?? "localhost");
        $this->dbuser = $dbuser ?: ($_ENV['DB_USER'] ?? "root");
        $this->dbpass = $dbpass ?: ($_ENV['DB_PASS'] ?? "");
        $this->dbname = $dbname ?: ($_ENV['DB_NAME'] ?? "fitness");
    }
    public function connect()
    {
        try {
            $this->db = new PDO(
                "mysql:host=$this->dbhost;dbname=$this->dbname",
                $this->dbuser,
                $this->dbpass,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
                ]
            );
            return $this->db;
        } catch (PDOException $e) {
            echo $e->getMessage();
            exit();
        }
    }
}
