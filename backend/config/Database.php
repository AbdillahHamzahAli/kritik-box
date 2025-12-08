<?php

use PDO;
use PDOException;

if (file_exists(__DIR__ . "/../.env")) {
    $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . "/../");
    $dotenv->load();
}

class Database
{
    private $conn;

    public function connect()
    {
        $this->conn = null;

        try {
            $host = $_ENV["DB_HOST"] ?? getenv("DB_HOST");
            $db_name = $_ENV["DB_NAME"] ?? getenv("DB_NAME");
            $username = $_ENV["DB_USER"] ?? getenv("DB_USER");
            $password = $_ENV["DB_PASS"] ?? getenv("DB_PASS");
            $port = $_ENV["DB_PORT"] ?? "3306";

            $dsn =
                "mysql:host=" .
                $host .
                ";dbname=" .
                $db_name .
                ";port=" .
                $port;

            $this->conn = new PDO($dsn, $username, $password);
            $this->conn->setAttribute(
                PDO::ATTR_ERRMODE,
                PDO::ERRMODE_EXCEPTION,
            );
        } catch (\PDOException $e) {
            error_log("Connection Error: " . $e->getMessage());
        }

        return $this->conn;
    }
}
