<?php

class Database
{
    private $host = "localhost";
    private $db_name = "loan_system_db";
    private $username = "root";
    private $password = "";

    public $conn;

    public function getConnection()
    {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );

            $this->conn->setAttribute(
                PDO::ATTR_ERRMODE,
                PDO::ERRMODE_EXCEPTION
            );

        } catch (PDOException $exception) {
            // Don't echo here - this class may be used inside a JSON API,
            // and printing raw text would corrupt the response body.
            // Log it instead and let the caller decide how to respond.
            error_log("Database connection error: " . $exception->getMessage());
            $this->conn = null;
        }

        return $this->conn;
    }
}