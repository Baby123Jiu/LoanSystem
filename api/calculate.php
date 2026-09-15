<?php

header("Content-Type: application/json");

require_once "../models/LoanCalculator.php";

$data = json_decode(file_get_contents("php://input"), true);

if (
    !isset($data["amount"]) ||
    !isset($data["rate"]) ||
    !isset($data["years"]) ||
    !isset($data["frequency"])
) {
    echo json_encode([
        "success" => false,
        "message" => "Please provide amount, rate, years and frequency."
    ]);

    exit;
}

$amount = $data["amount"];
$rate = $data["rate"];
$years = $data["years"];
$frequency = $data["frequency"];

$calculator = new LoanCalculator();

$result = $calculator->calculateFlat(
    $amount,
    $rate,
    $years,
    $frequency
);

echo json_encode([
    "success" => true,
    "data" => $result
], JSON_PRETTY_PRINT);