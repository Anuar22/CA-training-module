<?php
// app/config/database.php

class Database
{
    private $host = "localhost";
    private $port = "5433";
    private $dbname = "ca_training_module";
    private $username = "postgres";
    private $password = "Latifasaid@1970";

    private $conn;

    /**
     * Establishes a secure connection to the PostgreSQL instance
     */
    public function connect()
    {
        if ($this->conn == null) {
            try {
                // The explicit connection string (DSN)
                $dsn = "pgsql:host={$this->host};port={$this->port};dbname={$this->dbname}";

                // Passing username and password as separate parameters handles special characters like '@' natively
                $this->conn = new PDO(
                    $dsn,
                    $this->username,
                    $this->password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ]
                );

            } catch (PDOException $e) {
                die("Database Connection Failed: " . $e->getMessage());
            }
        }

        return $this->conn;
    }

    /**
     * Helper proxy to quickly prepare database statements across models
     */
    public function prepare($sql) {
        return $this->connect()->prepare($sql);
    }
}