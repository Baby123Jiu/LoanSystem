<?php

require_once "../middleware/auth.php";
header("Content-Type: application/json");
$user = requireLogin();

require_once "../models/LoanCalculator.php";
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

$logger = new ActivityLogger($conn);
$logDetails = "customer_id={$customerId} amount={$amount} rate={$rate} years={$years} frequency={$frequency} method={$method}";

try {
    $check = $conn->prepare("SELECT customer_id FROM customers WHERE customer_id = :customer_id");
    $check->execute(["customer_id" => $customerId]);

    if ($check->fetch() === false) {
        $logger->log($user["user_id"], "create_loan_failed", $logDetails . " - customer not found");
        respondError("No customer found with that customer_id.", 404);
    }

      $calculator = new LoanCalculator();

    try {
        $result = $method === "reducing"
            ? $calculator->calculateReducing($amount, $rate, $years, $frequency)
            : $calculator->calculateFlat($amount, $rate, $years, $frequency);
        $schedule = $calculator->generateSchedule($amount, $rate, $years, $frequency, $method);
    } catch (InvalidArgumentException $e) {
        $logger->log($user["user_id"], "create_loan_failed", $logDetails . " - " . $e->getMessage());
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

       $loanId = (int) $conn->lastInsertId();

    $scheduleStmt = $conn->prepare("
        INSERT INTO loan_schedule (
            loan_id, period, due_date, installment_amount,
            principal_component, interest_component, remaining_balance
        ) VALUES (
            :loan_id, :period, :due_date, :installment_amount,
            :principal_component, :interest_component, :remaining_balance
        )
    ");

    $monthsPerPeriod = $frequency === "monthly" ? 1 : 12;

    foreach ($schedule as $row) {
        $dueDate = (new DateTime())->modify("+" . ($row["period"] * $monthsPerPeriod) . " months")->format("Y-m-d");

        $scheduleStmt->execute([
            "loan_id" => $loanId,
            "period" => $row["period"],
            "due_date" => $dueDate,
            "installment_amount" => $row["installment_amount"],
            "principal_component" => $row["principal_component"] ?? ($row["installment_amount"] - ($row["interest_component"] ?? 0)),
            "interest_component" => $row["interest_component"] ?? 0,
            "remaining_balance" => $row["remaining_balance"]
        ]);
    }

    $logger->log($user["user_id"], "create_loan", $logDetails . " - loan_id={$loanId}");

    echo json_encode([
        "success" => true,
        "loan_id" => $loanId,
        "data" => $result,
        "schedule" => $schedule
    ], JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    $logger->log($user["user_id"], "create_loan_failed", $logDetails . " - database error");
    respondError("A database error occurred while saving the loan.", 500);
}