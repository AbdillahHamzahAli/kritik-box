<?php

require __DIR__ . "/../vendor/autoload.php";

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../");
$dotenv->load();

use PDO;
use PDOException;

class Database
{
    private $DB_HOST;
    private $DB_USER;
    private $DB_PASS;
    private $DB_NAME;
    public $conn;

    public function __construct()
    {
        $this->DB_HOST = $_ENV["DB_HOST"];
        $this->DB_USER = $_ENV["DB_USER"];
        $this->DB_PASS = $_ENV["DB_PASS"];
        $this->DB_NAME = $_ENV["DB_NAME"];
    }

    public function connect(): ?PDO
    {
        $this->conn = null;

        try {
            $dsn = "mysql:host={$this->DB_HOST};dbname={$this->DB_NAME};charset=utf8";
            $this->conn = new \PDO($dsn, $this->DB_USER, $this->DB_PASS);
            $this->conn->setAttribute(
                PDO::ATTR_ERRMODE,
                PDO::ERRMODE_EXCEPTION,
            );
        } catch (PDOException $e) {
            error_log(
                "[" .
                    date("Y-m-d H:i:s") .
                    "] Connection failed: " .
                    $e->getMessage() .
                    "\r\n",
                3,
                "../logs/error.log",
            );
        }

        return $this->conn;
    }
}
