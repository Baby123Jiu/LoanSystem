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

$query = isset($_GET["q"]) ? trim((string) $_GET["q"]) : "";

if ($query === "") {
    respondError("Please provide a search term.");
}

$database = new Database();
$conn = $database->getConnection();

if ($conn === null) {
    respondError("Could not connect to the database.", 500);
}

$stmt = $conn->prepare("
    SELECT customer_id, ghana_card_number, first_name, last_name, date_of_birth, phone, email, address
    FROM customers
    WHERE first_name LIKE :term
       OR last_name LIKE :term
       OR phone LIKE :term
       OR ghana_card_number LIKE :term
    ORDER BY first_name, last_name
    LIMIT 20
");
$stmt->execute(["term" => "%{$query}%"]);

echo json_encode([
    "success" => true,
    "customers" => $stmt->fetchAll(PDO::FETCH_ASSOC)
], JSON_PRETTY_PRINT);