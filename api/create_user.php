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

if ($data === null || !isset($data["username"]) || !isset($data["password"]) || !isset($data["role"])) {
    respondError("Please provide username, password and role.");
}

$username = (string) $data["username"];
$password = (string) $data["password"];
$role = (string) $data["role"];

if (!in_array($role, ["admin", "staff"], true)) {
    respondError("Role must be 'admin' or 'staff'.");
}

if (strlen($password) < 8) {
    respondError("Password must be at least 8 characters.");
}

$database = new Database();
$conn = $database->getConnection();

if ($conn === null) {
    respondError("Could not connect to the database.", 500);
}

try {
    $stmt = $conn->prepare("
        INSERT INTO users (username, password_hash, role)
        VALUES (:username, :password_hash, :role)
    ");

    $stmt->execute([
        "username" => $username,
        "password_hash" => password_hash($password, PASSWORD_DEFAULT),
        "role" => $role
    ]);

    $newUserId = (int) $conn->lastInsertId();

    (new ActivityLogger($conn))->log(
        $admin["user_id"],
        "create_user",
        "Created user_id={$newUserId} username={$username} role={$role}"
    );

    echo json_encode([
        "success" => true,
        "user_id" => $newUserId,
        "message" => "User created successfully."
    ]);

} catch (PDOException $e) {
    respondError("Could not create user - that username may already be taken.", 409);
}