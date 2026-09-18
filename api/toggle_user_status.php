<?php

require_once "../middleware/auth.php";
header("Content-Type: application/json");
$admin = requireRole(["admin"]);

require_once "../config/Database.php";
require_once "../models/ActivityLogger.php";

function respondError(string $message, int $status = 400): void
{
    http_response_code($status);
    echo json_encode([
        "success" => false,
        "message" => $message
    ]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if ($data === null || !isset($data["user_id"]) || !isset($data["status"])) {
    respondError("Please provide user_id and status.");
}

$userId = (int) $data["user_id"];
$status = (string) $data["status"];

if (!in_array($status, ["active", "blocked"], true)) {
    respondError("Status must be 'active' or 'blocked'.");
}

if ($userId === (int) $admin["user_id"] && $status === "blocked") {
    respondError("You cannot block your own account.");
}

$database = new Database();
$conn = $database->getConnection();

if ($conn === null) {
    respondError("Could not connect to the database.", 500);
}

try {
    $stmt = $conn->prepare("UPDATE users SET status = :status WHERE user_id = :user_id");
    $stmt->execute([
        "status" => $status,
        "user_id" => $userId
    ]);

    if ($stmt->rowCount() === 0) {
        respondError("User not found.", 404);
    }

    (new ActivityLogger($conn))->log(
        $admin["user_id"],
        $status === "blocked" ? "block_user" : "unblock_user",
        "Set user_id={$userId} status={$status}"
    );

    echo json_encode([
        "success" => true,
        "message" => "User status updated."
    ]);

} catch (PDOException $e) {
    respondError("Could not update user status.", 500);
}