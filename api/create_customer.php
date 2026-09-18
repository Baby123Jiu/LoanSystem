<?php

require_once "../middleware/auth.php";
header("Content-Type: application/json");
$user = requireLogin();

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

$data = json_decode(file_get_contents("php://input"), true);

$required = ["ghana_card_number", "first_name", "last_name", "date_of_birth", "phone"];
foreach ($required as $field) {
    if ($data === null || !isset($data[$field]) || trim((string) $data[$field]) === "") {
        respondError("Please provide ghana_card_number, first_name, last_name, date_of_birth and phone.");
    }
}

$ghanaCardNumber = (string) $data["ghana_card_number"];
$firstName = (string) $data["first_name"];
$lastName = (string) $data["last_name"];
$dateOfBirth = (string) $data["date_of_birth"];
$phone = (string) $data["phone"];
$email = isset($data["email"]) ? (string) $data["email"] : null;
$address = isset($data["address"]) ? (string) $data["address"] : null;

$dob = DateTime::createFromFormat("Y-m-d", $dateOfBirth);
if (!$dob || $dob->format("Y-m-d") !== $dateOfBirth) {
    respondError("date_of_birth must be a valid date in YYYY-MM-DD format.");
}

if (!preg_match('/^GHA-\d{9}-\d$/', $ghanaCardNumber)) {
    respondError("Ghana card number must be in the format GHA-XXXXXXXXX-X.");
}

if (!preg_match('/^0\d{9}$/', $phone)) {
    respondError("Phone number must be 10 digits starting with 0 (e.g. 0244000000).");
}

if (!preg_match("/^[A-Za-z' -]{2,}$/", $firstName)) {
    respondError("First name must contain only letters and be at least 2 characters.");
}

if (!preg_match("/^[A-Za-z' -]{2,}$/", $lastName)) {
    respondError("Last name must contain only letters and be at least 2 characters.");
}

$database = new Database();
$conn = $database->getConnection();

if ($conn === null) {
    respondError("Could not connect to the database.", 500);
}

try {
    $stmt = $conn->prepare("
        INSERT INTO customers (ghana_card_number, first_name, last_name, date_of_birth, phone, email, address)
        VALUES (:ghana_card_number, :first_name, :last_name, :date_of_birth, :phone, :email, :address)
    ");

    $stmt->execute([
        "ghana_card_number" => $ghanaCardNumber,
        "first_name" => $firstName,
        "last_name" => $lastName,
        "date_of_birth" => $dateOfBirth,
        "phone" => $phone,
        "email" => $email,
        "address" => $address
    ]);

    $customerId = (int) $conn->lastInsertId();

    (new ActivityLogger($conn))->log(
        $user["user_id"],
        "create_customer",
        "Created customer_id={$customerId} name={$firstName} {$lastName} ghana_card={$ghanaCardNumber} phone={$phone}"
    );

    echo json_encode([
        "success" => true,
        "customer_id" => $customerId,
        "message" => "Customer created successfully."
    ]);

} catch (PDOException $e) {
    respondError("Could not create customer - that Ghana card number or phone may already be registered.", 409);
}