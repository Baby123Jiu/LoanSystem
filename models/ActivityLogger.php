<?php

class ActivityLogger
{
    private PDO $conn;

    public function __construct(PDO $conn)
    {
        $this->conn = $conn;
    }

    public function log(int $userId, string $action, string $details = ""): void
    {
        $stmt = $this->conn->prepare("
            INSERT INTO activity_logs (user_id, action, details)
            VALUES (:user_id, :action, :details)
        ");

        $stmt->execute([
            "user_id" => $userId,
            "action" => $action,
            "details" => $details
        ]);
    }
}