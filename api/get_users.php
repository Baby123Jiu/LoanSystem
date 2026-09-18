<?php

require_once "../middleware/auth.php";
header("Content-Type: application/json");
$admin = requireRole(["admin"]);

require_once "../config/Database.php";

function respondError(string $message, int $status = 400): void
{
    http_response_code($status);
    echo json_encode([
        "success" => false,
        "message" => $message
    ]);
    exit;
}

$database = new Database();
$conn = $database->getConnection();

if ($conn === null) {
    respondError("Could not connect to the database.", 500);
}

try {
    $stmt = $conn->query("
        SELECT user_id, username, role, status, created_at
        FROM users
        ORDER BY username ASC
    ");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // For each user, find their most recent login/logout event
    $lastEventStmt = $conn->prepare("
        SELECT action, created_at
        FROM activity_logs
        WHERE user_id = :user_id AND action IN ('login', 'logout')
        ORDER BY created_at DESC
        LIMIT 1
    ");

    foreach ($users as &$user) {
        $lastEventStmt->execute(["user_id" => $user["user_id"]]);
        $lastEvent = $lastEventStmt->fetch(PDO::FETCH_ASSOC);

        if ($lastEvent === false) {
            $user["session_state"] = "out";
            $user["last_event_at"] = null;
        } else {
            $user["session_state"] = $lastEvent["action"] === "login" ? "in" : "out";
            $user["last_event_at"] = $lastEvent["created_at"];
        }
    }
    unset($user);

    echo json_encode(["success" => true, "users" => $users]);

} catch (PDOException $e) {
    respondError("Could not fetch users.", 500);
}