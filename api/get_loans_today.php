<?php

require_once "../middleware/auth.php";
header("Content-Type: application/json");
$admin = requireRole(["admin"]);

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
        SELECT l.loan_id, l.customer_id, c.first_name, c.last_name,
               l.loan_amount, l.duration, l.duration_unit, l.interest_rate,
               l.interest_method, l.repayment_frequency, l.total_interest,
               l.total_payable, l.installment_amount, l.remaining_balance,
               l.status, l.created_at
        FROM loans l
        JOIN customers c ON c.customer_id = l.customer_id
        WHERE DATE(l.created_at) = CURDATE()
        ORDER BY l.created_at DESC
    ");

    $loans = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "loans" => $loans
    ]);

} catch (PDOException $e) {
    respondError("Could not fetch today's loans.", 500);
}