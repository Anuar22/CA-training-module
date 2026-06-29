<?php

class Database
{
    private $host = "localhost";
    private $port = "5432";
    private $dbname = "ca_training_module";
    private $username = "postgres";
    private $password = "Latifasaid@1970";

    private $conn;

    public function connect()
    {
        if ($this->conn == null) {
            try {
                $dsn = "pgsql:host={$this->host};port={$this->port};dbname={$this->dbname}";

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
}