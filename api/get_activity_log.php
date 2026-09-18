<?php

require_once "../middleware/auth.php";
header("Content-Type: application/json");
requireRole(["admin"]);

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

// Optional filters, e.g. get_activity_log.php?user_id=3&date=2026-09-17
$userId = isset($_GET["user_id"]) ? (int) $_GET["user_id"] : null;
$date = isset($_GET["date"]) ? $_GET["date"] : null;

$sql = "
    SELECT activity_logs.log_id, activity_logs.user_id, users.username,
           activity_logs.action, activity_logs.details, activity_logs.created_at
    FROM activity_logs
    JOIN users ON users.user_id = activity_logs.user_id
    WHERE 1 = 1
";
$params = [];

if ($userId !== null) {
    $sql .= " AND activity_logs.user_id = :user_id";
    $params["user_id"] = $userId;
}

if ($date !== null) {
    $sql .= " AND DATE(activity_logs.created_at) = :date";
    $params["date"] = $date;
}

$sql .= " ORDER BY activity_logs.created_at DESC LIMIT 200";

$stmt = $conn->prepare($sql);
$stmt->execute($params);

echo json_encode([
    "success" => true,
    "logs" => $stmt->fetchAll(PDO::FETCH_ASSOC)
], JSON_PRETTY_PRINT);