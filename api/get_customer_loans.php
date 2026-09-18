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

if (!isset($_GET["customer_id"]) || !ctype_digit($_GET["customer_id"])) {
    respondError("A valid customer_id is required.");
}

$customerId = (int) $_GET["customer_id"];

$database = new Database();
$conn = $database->getConnection();

if ($conn === null) {
    respondError("Could not connect to the database.", 500);
}

try {
    $loanStmt = $conn->prepare("
        SELECT loan_id, loan_amount, duration, duration_unit, interest_rate,
               interest_method, repayment_frequency, total_interest,
               total_payable, installment_amount, remaining_balance,
               status, created_at
        FROM loans
        WHERE customer_id = :customer_id
        ORDER BY created_at DESC
    ");
    $loanStmt->execute(["customer_id" => $customerId]);
    $loans = $loanStmt->fetchAll(PDO::FETCH_ASSOC);

    $scheduleStmt = $conn->prepare("
        SELECT period, due_date, installment_amount, principal_component,
               interest_component, remaining_balance, status
        FROM loan_schedule
        WHERE loan_id = :loan_id
        ORDER BY period ASC
    ");

    foreach ($loans as &$loan) {
        $scheduleStmt->execute(["loan_id" => $loan["loan_id"]]);
        $loan["schedule"] = $scheduleStmt->fetchAll(PDO::FETCH_ASSOC);
    }
    unset($loan);

    echo json_encode([
        "success" => true,
        "loans" => $loans
    ]);

} catch (PDOException $e) {
    respondError("Could not fetch loan history.", 500);
}