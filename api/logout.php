<?php

session_start();
header("Content-Type: application/json");

require_once "../config/Database.php";
require_once "../models/ActivityLogger.php";

if (isset($_SESSION["user_id"])) {
    $database = new Database();
    $conn = $database->getConnection();

    if ($conn !== null) {
        (new ActivityLogger($conn))->log($_SESSION["user_id"], "logout", "Logged out.");
    }
}

$_SESSION = [];
session_destroy();

echo json_encode([
    "success" => true,
    "message" => "Logged out successfully."
]);