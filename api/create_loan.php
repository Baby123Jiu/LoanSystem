<?php

header("Content-Type: application/json");

require_once "../models/LoanCalculator.php";
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

$rawInput = file_get_contents("php://input");
$data = json_decode($rawInput, true);

if ($data === null) {
    respondError("Invalid JSON payload.");
}

$required = ["customer_id", "amount", "rate", "years", "frequency", "method"];
foreach ($required as $field) {
    if (!isset($data[$field])) {
        respondError("Please provide customer_id, amount, rate, years, frequency and method.");
    }
}

foreach (["customer_id", "amount", "rate", "years"] as $numericField) {
    if (!is_numeric($data[$numericField])) {
        respondError(ucfirst(str_replace('_', ' ', $numericField)) . " must be a number.");
    }
}

$customerId = (int) $data["customer_id"];
$amount = (float) $data["amount"];
$rate = (float) $data["rate"];
$years = (int) $data["years"];
$frequency = (string) $data["frequency"];
$method = (string) $data["method"];

if (!in_array($method, ["flat", "reducing"], true)) {
    respondError("Method must be 'flat' or 'reducing'.");
}

$database = new Database();
$conn = $database->getConnection();

if ($conn === null) {
    respondError("Could not connect to the database. Please try again later.", 500);
}

try {
    // Confirm the customer actually exists before creating a loan for them
    $check = $conn->prepare("SELECT customer_id FROM customers WHERE customer_id = :customer_id");
    $check->execute(["customer_id" => $customerId]);

    if ($check->fetch() === false) {
        respondError("No customer found with that customer_id.", 404);
    }

    $calculator = new LoanCalculator();

    try {
        $result = $method === "reducing"
            ? $calculator->calculateReducing($amount, $rate, $years, $frequency)
            : $calculator->calculateFlat($amount, $rate, $years, $frequency);
    } catch (InvalidArgumentException $e) {
        respondError($e->getMessage());
    }

    $stmt = $conn->prepare("
        INSERT INTO loans (
            customer_id, loan_amount, duration, duration_unit,
            interest_rate, interest_method, repayment_frequency,
            total_interest, total_payable, installment_amount,
            remaining_balance, status
        ) VALUES (
            :customer_id, :loan_amount, :duration, :duration_unit,
            :interest_rate, :interest_method, :repayment_frequency,
            :total_interest, :total_payable, :installment_amount,
            :remaining_balance, 'pending'
        )
    ");

    $stmt->execute([
        "customer_id" => $customerId,
        "loan_amount" => $amount,
        "duration" => $years,
        "duration_unit" => "years",
        "interest_rate" => $rate,
        "interest_method" => $method,
        "repayment_frequency" => $frequency,
        "total_interest" => $result["interest"],
        "total_payable" => $result["total_payable"],
        "installment_amount" => $result["installment_amount"],
        "remaining_balance" => $result["total_payable"]
    ]);

    echo json_encode([
        "success" => true,
        "loan_id" => (int) $conn->lastInsertId(),
        "data" => $result
    ], JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    respondError("A database error occurred while saving the loan.", 500);
}