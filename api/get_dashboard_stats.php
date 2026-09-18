<?php

require_once "../middleware/auth.php";
header("Content-Type: application/json");
$user = requireLogin();

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
    $customerCount = (int) $conn->query("SELECT COUNT(*) FROM customers")->fetchColumn();
    $staffCount = (int) $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $loansToday = (int) $conn->query("SELECT COUNT(*) FROM loans WHERE DATE(created_at) = CURDATE()")->fetchColumn();

    echo json_encode([
        "success" => true,
        "customers" => $customerCount,
        "staff" => $staffCount,
        "loans_today" => $loansToday
    ]);

} catch (PDOException $e) {
    respondError("Could not fetch dashboard stats.", 500);
}