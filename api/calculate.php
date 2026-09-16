<?php

header("Content-Type: application/json");

require_once "../models/LoanCalculator.php";

function respondError(string $message): void
{
    http_response_code(400);
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

$required = ["amount", "rate", "years", "frequency"];
foreach ($required as $field) {
    if (!isset($data[$field])) {
        respondError("Please provide amount, rate, years and frequency.");
    }
}

foreach (["amount", "rate", "years"] as $numericField) {
    if (!is_numeric($data[$numericField])) {
        respondError(ucfirst($numericField) . " must be a number.");
    }
}

$amount = (float) $data["amount"];
$rate = (float) $data["rate"];
$years = (int) $data["years"];
$frequency = (string) $data["frequency"];
$method = isset($data["method"]) ? (string) $data["method"] : "flat";

$calculator = new LoanCalculator();

try {
    if ($method === "reducing") {
        $result = $calculator->calculateReducing($amount, $rate, $years, $frequency);
    } elseif ($method === "flat") {
        $result = $calculator->calculateFlat($amount, $rate, $years, $frequency);
    } else {
        respondError("Method must be 'flat' or 'reducing'.");
    }
} catch (InvalidArgumentException $e) {
    respondError($e->getMessage());
}

echo json_encode([
    "success" => true,
    "data" => $result
], JSON_PRETTY_PRINT);