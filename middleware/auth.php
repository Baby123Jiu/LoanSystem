<?php

// Include this at the top of any endpoint that should only work
// for a logged-in user. It stops execution immediately with a
// clean JSON 401 if nobody is logged in.

function requireLogin(): array
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION["user_id"])) {
        http_response_code(401);
        header("Content-Type: application/json");
        echo json_encode([
            "success" => false,
            "message" => "You must be logged in to do that."
        ]);
        exit;
    }

    return [
        "user_id" => $_SESSION["user_id"],
        "username" => $_SESSION["username"],
        "role" => $_SESSION["role"]
    ];
}