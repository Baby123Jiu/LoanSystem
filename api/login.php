<?php

session_start();
header("Content-Type: application/json");

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

if ($data === null || !isset($data["username"]) || !isset($data["password"])) {
    respondError("Please provide username and password.");
}

$username = (string) $data["username"];
$password = (string) $data["password"];

$database = new Database();
$conn = $database->getConnection();

if ($conn === null) {
    respondError("Could not connect to the database.", 500);
}

$stmt = $conn->prepare(
    "SELECT user_id, username, password_hash, role, status FROM users WHERE username = :username"
);
$stmt->execute(["username" => $username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Deliberately vague error - never reveal whether it was the
// username or the password that was wrong, that helps attackers
// guess valid usernames.
if ($user === false || !password_verify($password, $user["password_hash"])) {
    respondError("Invalid username or password.", 401);
}

if ($user["status"] === "blocked") {
    (new ActivityLogger($conn))->log($user["user_id"], "login_blocked", "Blocked account attempted login.");
    respondError("This account has been blocked. Contact an administrator.", 403);
}

// Prevents session fixation: issue a fresh session ID on login
session_regenerate_id(true);

$_SESSION["user_id"] = $user["user_id"];
$_SESSION["username"] = $user["username"];
$_SESSION["role"] = $user["role"];

(new ActivityLogger($conn))->log($user["user_id"], "login", "Logged in.");

echo json_encode([
    "success" => true,
    "message" => "Logged in successfully.",
    "user" => [
        "user_id" => $user["user_id"],
        "username" => $user["username"],
        "role" => $user["role"]
    ]
]);