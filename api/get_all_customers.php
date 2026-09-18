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
    $stmt = $conn->query("
        SELECT customer_id, ghana_card_number, first_name, last_name,
               date_of_birth, phone, email, address, created_at
        FROM customers
        ORDER BY created_at DESC
    ");

    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "customers" => $customers
    ]);

} catch (PDOException $e) {
    respondError("Could not fetch customers.", 500);
}